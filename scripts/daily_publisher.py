#!/usr/bin/env python3
"""AizuDemy series & autonomous publisher: generate articles continuously and publish safely."""
from __future__ import annotations

import argparse
import base64
import difflib
import fcntl
import json
import logging
from logging.handlers import RotatingFileHandler
import os
import random
import re
import subprocess
import sys
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import time
from datetime import datetime
from pathlib import Path

HERE = Path(__file__).resolve().parent
CONFIG_FILE = HERE / "series_config.json"
STATE_FILE = HERE / "series_state.json"
LOG_FILE = HERE / "publisher.log"
PHP_RUNTIME = HERE.parent / "app" / "config" / "ai_runtime.php"
UPLOAD_DIR = HERE.parent / "assets" / "uploads"
WATERMARK_BADGE = HERE.parent / "assets" / "images" / "watermark_badge.png"
STATUS_FILE = HERE / "pipeline_status.json"
LOCK_FILE = HERE / "publisher.lock"
TOKEN_FILE = Path("/etc/aizudemy-publish.token")
ADMIN_EMAIL = "ardhana.aizu@gmail.com"

# Set up rotating log handler (max 5MB, keep 5 backups)
logger = logging.getLogger("aizudemy_publisher")
logger.setLevel(logging.INFO)
if not logger.handlers:
    rf_handler = RotatingFileHandler(LOG_FILE, maxBytes=5 * 1024 * 1024, backupCount=5, encoding="utf-8")
    formatter = logging.Formatter("%(asctime)s %(message)s", datefmt="%Y-%m-%dT%H:%M:%S%z")
    rf_handler.setFormatter(formatter)
    logger.addHandler(rf_handler)
    
    stream_handler = logging.StreamHandler(sys.stdout)
    stream_handler.setFormatter(formatter)
    logger.addHandler(stream_handler)


def log(message: str) -> None:
    logger.info(message)


def send_email_alert(subject: str, message_body: str, recipient: str = ADMIN_EMAIL) -> bool:
    """Send alert email to admin on failure or fallback event using sendmail, mail, or SMTP."""
    try:
        import shutil
        sendmail_bin = shutil.which("sendmail") or ("/usr/sbin/sendmail" if os.path.exists("/usr/sbin/sendmail") else None)
        if sendmail_bin and os.path.exists(sendmail_bin):
            email_text = f"To: {recipient}\nFrom: no-reply@aizudemy.net\nSubject: {subject}\nContent-Type: text/plain; charset=UTF-8\n\n{message_body}"
            proc = subprocess.Popen([sendmail_bin, "-t", "-oi"], stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            out, err = proc.communicate(input=email_text.encode("utf-8"))
            if proc.returncode == 0:
                log(f"ALERT EMAIL SENT via sendmail to {recipient}: {subject}")
                return True

        mail_bin = shutil.which("mail")
        if mail_bin:
            proc = subprocess.Popen([mail_bin, "-s", subject, recipient], stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            out, err = proc.communicate(input=message_body.encode("utf-8"))
            if proc.returncode == 0:
                log(f"ALERT EMAIL SENT via mail to {recipient}: {subject}")
                return True

        # Fallback to local SMTP (Postfix localhost)
        import smtplib
        from email.mime.text import MIMEText
        msg = MIMEText(message_body, "plain", "utf-8")
        msg["Subject"] = subject
        msg["From"] = "no-reply@aizudemy.net"
        msg["To"] = recipient
        with smtplib.SMTP("127.0.0.1", 25, timeout=5) as s:
            s.send_message(msg)
        log(f"ALERT EMAIL SENT via local SMTP to {recipient}: {subject}")
        return True
    except Exception as exc:
        log(f"Failed to send alert email: {exc}")
        return False

def send_wa_publish_alert(title: str, url: str, category: str = "", mode: str = "Series") -> bool:
    """Send concise WhatsApp alert to Admin when a new article is published."""
    target_wa = "0895626089159"
    cat_str = f"\n• 📂 *Kategori*: {category.title()}" if category else ""
    wa_msg = (
        f"📰 *[AIZUDEMY] Artikel Baru Terbit!* ✨\n\n"
        f"• 📝 *Judul*: {title}{cat_str}\n"
        f"• 🔗 *Link*: {url}\n\n"
        f"⏳ _Diterbitkan otomatis via AI Auto-Publisher_"
    )
    endpoints = [
        "https://api.aaizu.id/api/sentinel/notify",
        "http://192.168.100.22:8000/api/sentinel/notify",
        "http://127.0.0.1:3000/send"
    ]
    for ep in endpoints:
        try:
            body = {"target_phone": target_wa, "to": target_wa, "message": wa_msg}
            data = json.dumps(body, ensure_ascii=False).encode("utf-8")
            req = urllib.request.Request(ep, data=data, headers={"Content-Type": "application/json", "User-Agent": "AizuDemy-Publisher/2.0"})
            with urllib.request.urlopen(req, timeout=5) as resp:
                if resp.status < 300:
                    log(f"WA NOTIFICATION SENT to {target_wa}: {title}")
                    return True
        except Exception:
            pass
    log(f"WARN Gagal kirim WA notification untuk artikel: {title}")
    return False


def send_wa_start_alert(title: str, category: str = "", mode: str = "Series") -> bool:
    """Send immediate WhatsApp alert to Admin when AI generator starts writing an article."""
    target_wa = "0895626089159"
    cat_str = f"\n• 📂 *Kategori*: {category.title()}" if category else ""
    mode_str = f"\n• 🎯 *Mode*: {mode.title()}" if mode else ""
    wa_msg = (
        f"⏳ *[AIZUDEMY] Mulai Menulis Artikel...* ✍️\n\n"
        f"• 📝 *Topik*: {title}{cat_str}{mode_str}\n"
        f"• 🤖 *AI Engine*: Gemini 3.6 Flash & Image 3.1\n\n"
        f"⏱️ _Sedang menyusun outline, isi 1.500 kata & thumbnail visual (~30-60 detik)..._"
    )
    endpoints = [
        "https://api.aaizu.id/api/sentinel/notify",
        "http://192.168.100.22:8000/api/sentinel/notify",
        "http://127.0.0.1:3000/send"
    ]
    for ep in endpoints:
        try:
            body = {"target_phone": target_wa, "to": target_wa, "message": wa_msg}
            data = json.dumps(body, ensure_ascii=False).encode("utf-8")
            req = urllib.request.Request(ep, data=data, headers={"Content-Type": "application/json", "User-Agent": "AizuDemy-Publisher/2.0"})
            with urllib.request.urlopen(req, timeout=5) as resp:
                if resp.status < 300:
                    log(f"WA START ALERT SENT to {target_wa}: {title}")
                    return True
        except Exception:
            pass
    return False


def load_json(path: Path) -> dict:
    if not path.exists():
        return {}
    with path.open(encoding="utf-8") as handle:
        return json.load(handle)


def save_json_atomic(path: Path, data: dict) -> None:
    fd, name = tempfile.mkstemp(prefix=path.name, dir=path.parent, text=True)
    try:
        with os.fdopen(fd, "w", encoding="utf-8") as handle:
            json.dump(data, handle, ensure_ascii=False, indent=2)
            handle.write("\n")
        os.replace(name, path)
    finally:
        if os.path.exists(name):
            os.unlink(name)


def write_status(state: str, **extra) -> None:
    payload = {"state": state, "updated_at": datetime.now().astimezone().isoformat(timespec="seconds"), **extra}
    save_json_atomic(STATUS_FILE, payload)


def request_json(url: str, payload: dict, headers: dict, timeout: int = 90, attempts: int = 4) -> dict:
    body = json.dumps(payload, ensure_ascii=False).encode()
    req = urllib.request.Request(url, data=body, headers={
        "Content-Type": "application/json",
        "User-Agent": "curl/8.5.0",
        "Accept": "application/json",
        **headers,
    })
    last = None
    for attempt in range(1, attempts + 1):
        try:
            with urllib.request.urlopen(req, timeout=timeout) as response:
                return json.loads(response.read().decode())
        except urllib.error.HTTPError as exc:
            detail = exc.read().decode(errors="replace")
            last = RuntimeError(f"HTTP {exc.code} dari {url}: {detail[:500]}")
            if exc.code == 429:
                wait_sec = 25 * attempt
                m = re.search(r'reset after\s+(?:(\d+)\s*m\s*)?(\d+)\s*s', detail)
                if m:
                    mins = int(m.group(1)) if m.group(1) else 0
                    secs = int(m.group(2))
                    wait_sec = mins * 60 + secs + 3
                if attempt < attempts:
                    log(f"HTTP 429 Rate limited. Waiting {wait_sec}s before retry ({attempt}/{attempts})...")
                    time.sleep(wait_sec)
                    continue
            if exc.code < 500 and exc.code != 429:
                raise last from exc
        except (urllib.error.URLError, TimeoutError) as exc:
            last = exc
        if attempt < attempts:
            log(f"RETRY {attempt}/{attempts} setelah error provider ({last})")
            time.sleep(5 * attempt)
    raise RuntimeError(f"Request gagal setelah {attempts} percobaan: {last}")


def extract_json(text: str) -> dict:
    """Robust multi-strategy JSON extractor for LLM-generated output."""
    if not text or not isinstance(text, str):
        raise RuntimeError("Model mengembalikan respons kosong")

    text = text.strip()

    # 1. Clean markdown code fences (```json ... ``` or ``` ... ```)
    if "```" in text:
        match = re.search(r"```(?:json)?\s*(\{[\s\S]*?\})\s*```", text)
        if match:
            text = match.group(1).strip()
        else:
            lines = text.splitlines()
            cleaned_lines = []
            for line in lines:
                if line.strip().startswith("```"):
                    continue
                cleaned_lines.append(line)
            text = "\n".join(cleaned_lines).strip()

    # 2. Try direct json.loads with strict=False
    try:
        data = json.loads(text, strict=False)
        if isinstance(data, dict):
            return data
    except Exception:
        pass

    # 3. Locate outer JSON object boundaries
    start = text.find("{")
    end = text.rfind("}")
    if start != -1 and end > start:
        blob = text[start:end + 1]
        try:
            data = json.loads(blob, strict=False)
            if isinstance(data, dict):
                return data
        except Exception:
            pass

        # 4. Repair unescaped newlines, tabs, and control characters inside strings
        repaired = []
        in_string = False
        escaped = False
        for char in blob:
            if escaped:
                repaired.append(char)
                escaped = False
            elif char == "\\":
                repaired.append(char)
                escaped = True
            elif char == '"':
                repaired.append(char)
                in_string = not in_string
            elif in_string and char == "\n":
                repaired.append("\\n")
            elif in_string and char == "\r":
                repaired.append("\\r")
            elif in_string and char == "\t":
                repaired.append("\\t")
            elif in_string and ord(char) < 0x20:
                repaired.append(" ")
            else:
                repaired.append(char)
        cleaned = "".join(repaired)

        try:
            data = json.loads(cleaned, strict=False)
            if isinstance(data, dict):
                return data
        except Exception:
            pass

        # 5. Clean trailing commas before closing braces/brackets
        cleaned_commas = re.sub(r",\s*([}\]])", r"\1", cleaned)
        try:
            data = json.loads(cleaned_commas, strict=False)
            if isinstance(data, dict):
                return data
        except Exception:
            pass

    # 6. Advanced Fallback: Regex & token-based field extraction
    # Handles edge cases where HTML inside "content" contains unescaped double quotes
    try:
        title_m = re.search(r'"title"\s*:\s*"((?:\\.|[^"\\])*)"', text)
        if not title_m:
            title_m = re.search(r'"title"\s*:\s*"(.*?)"', text)
            
        slug_m = re.search(r'"slug"\s*:\s*"((?:\\.|[^"\\])*)"', text)
        if not slug_m:
            slug_m = re.search(r'"slug"\s*:\s*"(.*?)"', text)
            
        excerpt_m = re.search(r'"excerpt"\s*:\s*"((?:\\.|[^"\\])*)"', text)
        if not excerpt_m:
            excerpt_m = re.search(r'"excerpt"\s*:\s*"(.*?)"', text)

        cat_m = re.search(r'"category_slug"\s*:\s*"((?:\\.|[^"\\])*)"', text)

        content = ""
        content_patterns = [
            r'"content"\s*:\s*"([\s\S]*?)"\s*,\s*"(?:slug|title|excerpt|category|category_slug)"',
            r'"(?:slug|title|excerpt|category|category_slug)"\s*:\s*"[\s\S]*?"\s*,\s*"content"\s*:\s*"([\s\S]*?)"\s*\}',
            r'"content"\s*:\s*"([\s\S]*?)"\s*\}',
        ]
        for pat in content_patterns:
            c_m = re.search(pat, text)
            if c_m:
                content = c_m.group(1)
                break

        if not content:
            idx = text.find('"content"')
            if idx != -1:
                after_key = text[idx + len('"content"'):].lstrip()
                if after_key.startswith(":"):
                    after_colon = after_key[1:].lstrip()
                    if after_colon.startswith('"'):
                        raw_c = after_colon[1:]
                        raw_c = re.sub(r'"\s*\}?\s*$', '', raw_c)
                        content = raw_c

        title = title_m.group(1) if title_m else ""
        slug = slug_m.group(1) if slug_m else ""
        excerpt = excerpt_m.group(1) if excerpt_m else ""
        cat_slug = cat_m.group(1) if cat_m else ""

        def unescape_val(val: str) -> str:
            if not val:
                return ""
            try:
                return json.loads(f'"{val}"')
            except Exception:
                return val.replace('\\"', '"').replace('\\n', '\n').replace('\\t', '\t').replace('\\\\', '\\')

        title = unescape_val(title).strip()
        slug = slug.strip() if slug else re.sub(r'[^a-z0-9]+', '-', title.lower()).strip('-')
        excerpt = unescape_val(excerpt).strip()
        content = unescape_val(content).strip()
        cat_slug = unescape_val(cat_slug).strip()

        if title and (content or excerpt):
            res = {
                "title": title,
                "slug": slug,
                "excerpt": excerpt,
                "content": content
            }
            if cat_slug:
                res["category_slug"] = cat_slug
            return res
    except Exception:
        pass

    raise RuntimeError("Model tidak mengembalikan JSON valid")


def load_ai_runtime() -> dict:
    """Load decrypted dashboard config, recent posts, and categories via CLI PHP bridge."""
    try:
        import shutil
        php_candidates = [
            shutil.which("php"),
            "/usr/bin/php",
            "/usr/local/lsws/lsphp83/bin/php",
            "/usr/local/lsws/lsphp82/bin/php",
            "/opt/lampp/bin/php"
        ]
        php_bin = next((p for p in php_candidates if p and os.path.exists(p) and os.access(p, os.X_OK)), "php")
        run = subprocess.run(
            [php_bin, str(PHP_RUNTIME)], capture_output=True,
            text=True, timeout=15, check=True,
        )
        data = json.loads(run.stdout)
        return data if data.get("ok") else {}
    except Exception as exc:
        log(f"WARN konfigurasi dashboard tidak tersedia, pakai environment: {exc}")
        return {}


def role_config(config: dict, role: str) -> dict:
    cfg = dict(config.get(role, {}))
    if role == "writer":
        cfg["base_url"] = cfg.get("base_url") or os.getenv("OPENAI_BASE_URL", "https://api.openai.com/v1")
        cfg["api_key"] = cfg.get("api_key") or os.getenv("OPENAI_API_KEY", "")
        cfg["model"] = cfg.get("model") or os.getenv("OPENAI_MODEL", "gpt-5-mini")
    return cfg


def call_chat(cfg: dict, prompt: str, timeout: int | None = None, system_prompt: str | None = None) -> str:
    if not cfg.get("base_url") or not cfg.get("api_key") or not cfg.get("model"):
        raise RuntimeError("Konfigurasi role chat belum lengkap")
    timeout = timeout or max(30, min(1800, int(cfg.get("timeout") or 360)))
    
    messages = []
    if system_prompt:
        messages.append({"role": "system", "content": system_prompt})
    messages.append({"role": "user", "content": prompt})

    result = request_json(
        f"{cfg['base_url'].rstrip('/')}/chat/completions",
        {
            "model": cfg["model"],
            "messages": messages,
            "max_tokens": 8192,
            "temperature": 0.7,
        },
        {"Authorization": f"Bearer {cfg['api_key']}"}, timeout=timeout, attempts=4,
    )
    return result.get("choices", [{}])[0].get("message", {}).get("content", "")


def call_chat_with_retry(cfg: dict, prompt: str, system_prompt: str | None = None, max_attempts: int = 3) -> dict:
    """Call LLM and extract JSON with automatic retry and error feedback loop."""
    last_err = None
    current_prompt = prompt

    for attempt in range(1, max_attempts + 1):
        try:
            raw_response = call_chat(cfg, current_prompt, timeout=cfg.get("timeout"), system_prompt=system_prompt)
        except Exception as exc:
            last_err = exc
            log(f"WARN Percobaan {attempt}/{max_attempts} error API/jaringan: {exc}")
            if attempt < max_attempts:
                time.sleep(5 * attempt)
                continue
            break

        try:
            data = extract_json(raw_response)
            return data
        except Exception as exc:
            last_err = exc
            log(f"WARN Percobaan {attempt}/{max_attempts} menghasilkan JSON tidak valid: {exc}")
            if attempt < max_attempts:
                current_prompt = (
                    f"{prompt}\n\n"
                    f"--- PERINGATAN SISTEM (RETRY KE-{attempt+1}) ---\n"
                    f"Output sebelumnya gagal diparsing karena format JSON rusak: {exc}\n"
                    f"Pastikan kamu HANYA mengembalikan objek JSON valid murni tanpa markdown fence, tanpa teks pembuka/penutup, dan semua quote di dalam string di-escape dengan benar (\\\")."
                )
                time.sleep(3 * attempt)

    raise RuntimeError(f"Model gagal mengembalikan JSON valid setelah {max_attempts} percobaan: {last_err}")


def validate_article(article: dict) -> dict:
    for key in ("title", "excerpt", "content"):
        if not str(article.get(key, "")).strip():
            raise RuntimeError(f"Hasil model tidak memiliki field {key}")
    article["excerpt"] = str(article["excerpt"]).strip()[:155]
    return article


def normalize_text_for_similarity(text: str) -> str:
    """Normalize title/topic for similarity comparison (remove punctuation, stop words, lowercase)."""
    text = (text or "").lower()
    text = re.sub(r'https?://\S+', '', text)
    text = re.sub(r'[^\w\s]', ' ', text)
    tokens = [w for w in text.split() if len(w) > 2]
    # Filter common generic Indonesian & English tech filler words
    stop_words = {
        "cara", "tutorial", "panduan", "tips", "trik", "belajar", "lengkap", "mudah",
        "untuk", "dengan", "yang", "dan", "dari", "pada", "dalam", "bisa", "buat",
        "mengenal", "membuat", "menggunakan", "pemula", "praktis", "modern", "terbaru",
        "tahun", "2024", "2025", "2026", "2027", "guide", "learn", "how", "with",
        "aizudemy", "part", "seri", "edisi", "studi", "kasus", "langkah", "langkahnya"
    }
    meaningful_tokens = [t for t in tokens if t not in stop_words]
    return " ".join(meaningful_tokens if meaningful_tokens else tokens)


def check_topic_duplication(candidate: dict, existing_posts: list, threshold: float = 0.58) -> tuple[bool, str, dict | None]:
    """
    Bulletproof deterministic similarity check between candidate topic and existing database posts.
    Returns: (is_duplicate: bool, reason_message: str, matched_post: dict | None)
    """
    cand_title = (candidate.get("title") or "").strip()
    cand_slug = (candidate.get("slug") or "").strip() or re.sub(r'[^a-z0-9]+', '-', cand_title.lower()).strip('-')
    cand_topic = (candidate.get("topic") or "").strip()

    if not cand_title:
        return True, "Judul kandidat kosong", None

    norm_cand_title = normalize_text_for_similarity(cand_title)
    norm_cand_topic = normalize_text_for_similarity(cand_topic)
    cand_tokens = set(norm_cand_title.split())

    for post in existing_posts:
        ex_title = (post.get("title") or "").strip()
        ex_slug = (post.get("slug") or "").strip()
        if not ex_title:
            continue

        norm_ex_title = normalize_text_for_similarity(ex_title)
        ex_tokens = set(norm_ex_title.split())

        # 1. Exact or near-exact slug match
        if cand_slug and ex_slug and cand_slug == ex_slug:
            return True, f"Slug sama persis dengan artikel ID #{post.get('id')} ('{ex_title}')", post

        # 2. SequenceMatcher text similarity on full titles
        sim_title = difflib.SequenceMatcher(None, cand_title.lower(), ex_title.lower()).ratio()
        if sim_title >= 0.70:
            return True, f"Judul terlalu mirip ({int(sim_title*100)}%) dengan '{ex_title}'", post

        # 3. SequenceMatcher on normalized key tokens
        if norm_cand_title and norm_ex_title:
            sim_norm = difflib.SequenceMatcher(None, norm_cand_title, norm_ex_title).ratio()
            if sim_norm >= threshold:
                return True, f"Substansi topik terlalu mirip ({int(sim_norm*100)}%) dengan '{ex_title}'", post

        # 4. Significant Keyword Jaccard Overlap
        if cand_tokens and ex_tokens:
            intersection = cand_tokens & ex_tokens
            union = cand_tokens | ex_tokens
            jaccard = len(intersection) / len(union) if union else 0.0
            
            # If 3 or more specific tech tokens match and overlap is high (> 55%)
            if len(intersection) >= 3 and jaccard >= 0.55:
                overlap_words = ", ".join(list(intersection)[:4])
                return True, f"Kata kunci utama ({overlap_words}) bertumpuk dengan '{ex_title}' (Jaccard: {int(jaccard*100)}%)", post

            # If ALL key tokens of a candidate are completely contained in an existing post or vice-versa
            if len(cand_tokens) >= 3 and cand_tokens.issubset(ex_tokens):
                return True, f"Semua fokus kata kunci sudah tercakup di artikel '{ex_title}'", post
            if len(ex_tokens) >= 3 and ex_tokens.issubset(cand_tokens):
                return True, f"Fokus kata kunci menduplikasi artikel '{ex_title}'", post

    return False, "", None


def generate_autonomous_topic_plan(ai_roles: dict, recent_posts: list, categories: list, max_plan_attempts: int = 5) -> dict:
    """Generate a fresh, trending standalone topic plan with multi-tier anti-duplication verification."""
    log("Merancang topik baru otomatis (Autonomous Topic Strategist with Anti-Duplication Engine)...")
    
    # Send all available existing titles (up to 200) to ensure LLM has complete visibility
    if recent_posts:
        title_lines = [f"- {p.get('title')}" for p in recent_posts[:200] if p.get('title')]
        recent_titles = "\n".join(title_lines)
    else:
        recent_titles = "- (Belum ada data riwayat)"
    
    valid_cats = [c for c in categories if c.get("slug") not in ("umum", "artikel", "videos")]
    if not valid_cats:
        valid_cats = [
            {"name": "Tutorial", "slug": "tutorial"},
            {"name": "Cyber Security", "slug": "cyber-security"},
            {"name": "Teknologi", "slug": "teknologi"},
            {"name": "Kecerdasan Buatan", "slug": "kecerdasan-buatan"}
        ]
    
    cat_list = ", ".join(f"{c.get('name')} ({c.get('slug')})" for c in valid_cats)
    
    base_prompt = f"""Kamu adalah Lead Content Strategist & Tech Editor untuk AizuDemy (https://aizudemy.net).

Kategori yang tersedia: {cat_list}

Daftar artikel yang SUDAH PERNAH DITERBITKAN di AizuDemy (MUTLAK DILARANG membuat topik yang sama, mirip, atau membahas subjek yang sudah ada di bawah ini):
{recent_titles}

Tugas: Rancang 1 ide artikel blog teknologi BARU yang SEGAR, 100% UNIK, praktis, dan diminati developer / tech enthusiast Indonesia di tahun 2026.
Pilihan variasi tema yang sangat dianjurkan:
- Panduan keamanan siber praktis, reverse engineering, ethical hacking, digital privacy, hardening server / database / web app
- Tutorial teknologi & tools terkini (misal: Rust, Golang, FastAPI, TypeScript, Bun, Docker, Podman, Kubernetes, Linux sysadmin/CLI, Neovim, Git internal)
- Arsitektur & implementasi AI Agentic (LangGraph, MCP Model Context Protocol, CrewAI, Ollama lokal, RAG arsitektur, function calling)
- Tips optimasi performa backend/frontend, database indexing, caching Redis, GraphQL, WebSocket, microservices, atau troubleshooting error dunia nyata
- Hardware / IoT / Self-hosting & homelab (Raspberry Pi, STB Linux, reverse proxy, automation)

Kembalikan HANYA JSON valid dengan struktur berikut:
{{
  "title": "Judul Menarik, Spesifik & SEO-Friendly (Bahasa Indonesia, 60-80 karakter)",
  "category_slug": "slug_kategori_sesuai_daftar_diatas",
  "topic": "Fokus bahasan spesifik yang unik dan sasaran pembaca",
  "keywords": "kata kunci 1, kata kunci 2, kata kunci 3",
  "outline": "Poin 1 (Latar Belakang & Masalah), Poin 2 (Praktek/Step-by-Step Teknis), Poin 3 (Best Practice & Troubleshooting), Poin 4 (Kesimpulan & Saran)"
}}"""

    sys_prompt = "Kamu adalah Content Strategist teknologi profesional. Balas HANYA dengan JSON valid tanpa kode markdown atau teks tambahan apapun."
    
    current_prompt = base_prompt
    topic_data = {}
    for attempt in range(1, max_plan_attempts + 1):
        log(f"Autonomous Strategist: Merancang kandidat topik (Percobaan {attempt}/{max_plan_attempts})...")
        topic_data = call_chat_with_retry(role_config(ai_roles, "writer"), current_prompt, system_prompt=sys_prompt, max_attempts=3)
        
        if not topic_data.get("category_slug"):
            topic_data["category_slug"] = "tutorial"
            
        cand_title = topic_data.get("title", "")
        log(f"Kandidat topik #{attempt}: [{topic_data.get('category_slug')}] {cand_title}")
        
        # Jalankan verifikasi anti-duplikasi deterministik
        is_dup, reason, matched_post = check_topic_duplication(topic_data, recent_posts)
        if not is_dup:
            log(f"✅ TOPIK LOLOS VALIDASI ANTI-DUPLIKASI (100% Unik): '{cand_title}'")
            return topic_data
            
        matched_title = matched_post.get("title", "Artikel Terkait") if matched_post else "Artikel Lain"
        log(f"⚠️ TOPIK DITOLAK (Duplikasi Terdeteksi): {reason}")
        
        if attempt < max_plan_attempts:
            current_prompt = (
                f"{base_prompt}\n\n"
                f"--- ⚠️ PERINGATAN SISTEM ANTI-DUPLIKASI (Percobaan ke-{attempt+1}) ---\n"
                f"Topik yang kamu usulkan sebelumnya: '{cand_title}' DITOLAK karena {reason}.\n"
                f"Artikel yang sudah ada: '{matched_title}'.\n"
                f"WAJIB pilih topik/subjek teknologi lain yang BENAR-BENAR BERBEDA dan belum pernah dibahas!"
            )
            time.sleep(2)

    log(f"WARN Melewati batas percobaan ({max_plan_attempts}), menggunakan kandidat topik terakhir")
    return topic_data


def generate_article_content(topic_plan: dict, ai: dict, cache_base: Path, restart: bool = False) -> dict:
    sys_prompt = "Kamu adalah penulis artikel teknologi dan software engineer profesional di Indonesia. Tulis dengan gaya bicara manusia yang santai, praktis, dan mudah dipahami. Hindari gaya akademik kaku, jargon berlebihan, dan frase klise AI seperti 'Dalam era digital saat ini', 'Penting untuk dicatat', atau 'Oleh karena itu'. Balas HANYA dengan JSON valid tanpa kode markdown atau teks tambahan apapun."
    prompt = f"""Tulis artikel blog Indonesia lengkap dan mendalam untuk topik berikut:
Judul: {topic_plan.get('title')}
Kategori: {topic_plan.get('category_slug')}
Topik: {topic_plan.get('topic')}
Kata kunci: {topic_plan.get('keywords')}
Outline: {topic_plan.get('outline')}

Syarat:
- Panjang 1200-1800 kata.
- Gaya bahasa percakapan Indonesia yang natural, seperti mentor/teknisi sedang menjelaskan kepada rekan kerja. Gunakan Anda dan kita dengan santai.
- Hindari frase klise AI: Dalam era digital saat ini, Penting untuk dicatat, Oleh karena itu, Dengan demikian, Hal ini disebabkan karena, Perlu diketahui bahwa, dan sejenisnya.
- Buka artikel dengan hook narasi, contoh nyata, atau pertanyaan ringan. Jangan langsung definisi/teori.
- Gunakan heading HTML h2/h3 yang jelas, paragraf pendek (2-3 kalimat), bullet/numbered list, dan contoh kode/perintah praktis.
- Masukkan analogi sederhana atau pengalaman praktis agar pembaca merasa diajak diskusi.
- Kesimpulan akhir harus ringkas, manusiawi, dan memberikan satu actionable takeaway.
- Jangan gunakan tag html/body atau markdown fence.
- Jangan membuat daftar pustaka atau sumber akademik formal.
Kembalikan JSON valid saja dengan field title, excerpt (maks 155 karakter), content (HTML), dan slug."""

    writer_cache = cache_base.with_name(cache_base.name + "-writer.json")
    editor_cache = cache_base.with_name(cache_base.name + "-editor.json")

    if editor_cache.exists() and not restart:
        log(f"RESUME hasil Editor: {editor_cache.name}")
        return validate_article(load_json(editor_cache))

    if writer_cache.exists() and not restart:
        log(f"RESUME hasil Writer: {writer_cache.name}")
        article = validate_article(load_json(writer_cache))
    else:
        raw_article = call_chat_with_retry(role_config(ai, "writer"), prompt, system_prompt=sys_prompt, max_attempts=3)
        article = validate_article(raw_article)
        save_json_atomic(writer_cache, article)
        log(f"CHECKPOINT Writer: {writer_cache.name}")

    editor = role_config(ai, "editor")
    if editor.get("base_url") and editor.get("api_key") and editor.get("model"):
        log(f"EDIT dengan model {editor['model']}")
        edit_prompt = """Edit artikel JSON berikut agar terdengar seperti tulisan manusia Indonesia: santai, jelas, dan tidak kaku. Hilangkan jargon akademik, frase klise AI, dan pengulangan. Buka dengan narasi ringan, gunakan paragraf pendek, analogi praktis, dan kesimpulan yang mengajak pembaca bertindak. Pertahankan HTML dan maksud teknis. Tetap 1200-1800 kata. Kembalikan JSON valid saja dengan field title, excerpt, content, slug.
ARTIKEL:\n""" + json.dumps(article, ensure_ascii=False)
        edited_raw = call_chat_with_retry(editor, edit_prompt, system_prompt=sys_prompt, max_attempts=3)
        article = validate_article(edited_raw)
        save_json_atomic(editor_cache, article)
        log(f"CHECKPOINT Editor: {editor_cache.name}")

    return validate_article(article)


def apply_watermark_badge(image_bytes: bytes) -> bytes:
    """Overlay the circular AizuDemy logo watermark badge on the top-right corner of the thumbnail."""
    if not WATERMARK_BADGE.exists():
        log(f"WARN watermark badge {WATERMARK_BADGE} tidak ditemukan, thumbnail tanpa watermark")
        return image_bytes

    try:
        from PIL import Image, ImageFilter
        import io

        base_im = Image.open(io.BytesIO(image_bytes)).convert("RGBA")
        badge = Image.open(WATERMARK_BADGE).convert("RGBA")

        W, H = base_im.size

        # Ensure optimal dimensions for web & social previews (max 1280px width, 16:9 ratio)
        if W > 1280:
            new_H = int(H * (1280 / W))
            base_im = base_im.resize((1280, new_H), Image.Resampling.LANCZOS)
            W, H = base_im.size

        # Responsive size: proportional to image dimensions (approx 14% of width or 23% of height)
        badge_size = int(min(W * 0.14, H * 0.23))
        badge_resized = badge.resize((badge_size, badge_size), Image.Resampling.LANCZOS)

        margin_x = int(W * 0.035)
        margin_y = int(H * 0.04)
        pos_x = W - badge_size - margin_x
        pos_y = margin_y

        # Soft elegant drop shadow behind badge
        shadow_offset = max(2, int(badge_size * 0.025))
        shadow_blur = max(3, int(badge_size * 0.035))
        shadow_pad = shadow_blur * 2 + shadow_offset

        shadow_img = Image.new("RGBA", (badge_size + shadow_pad * 2, badge_size + shadow_pad * 2), (0, 0, 0, 0))
        alpha = badge_resized.split()[-1]
        shadow_mask = Image.new("RGBA", (badge_size, badge_size), (0, 0, 0, 85))
        shadow_mask.putalpha(alpha)
        shadow_img.paste(shadow_mask, (shadow_pad + shadow_offset, shadow_pad + shadow_offset), shadow_mask)
        shadow_img = shadow_img.filter(ImageFilter.GaussianBlur(shadow_blur))

        # Paste shadow first, then badge
        base_im.paste(shadow_img, (pos_x - shadow_pad, pos_y - shadow_pad), shadow_img)
        base_im.paste(badge_resized, (pos_x, pos_y), badge_resized)

        output = io.BytesIO()
        base_im.convert("RGB").save(output, format="JPEG", quality=86, optimize=True)
        log("Watermark logo AizuDemy diterapkan & visual dioptimalkan untuk WhatsApp/Web (JPEG q86)")
        return output.getvalue()
    except Exception as e:
        log(f"WARN Gagal menerapkan watermark badge: {e}")
        return image_bytes


def generate_fallback_thumbnail(article: dict, ai: dict | None = None, visual_prompt: str = "", reason: str = "") -> str:
    """Generate high-quality, topic-relevant photorealistic thumbnail using Pollinations Flux when primary generator fails."""
    if not visual_prompt and ai:
        visual = role_config(ai, "visual")
        if all(visual.get(k) for k in ("base_url", "api_key", "model")):
            try:
                vis_req = (
                    f"Create an ultra-photorealistic English image generation prompt (16:9 widescreen) for a tech article hero banner: \"{article.get('title')}\". "
                    "Style: Authentic editorial photography, shot on 35mm f/1.8 lens, natural depth of field, real modern developer workstation / enterprise server hardware, tactile real-world textures, warm cinematic ambient lighting. "
                    "CRITICAL: Real-life photography style (NOT 3D render, NOT cartoon, NOT plastic CGI). ABSOLUTELY NO text, NO letters, NO words, NO typography, NO watermark. Output ONLY the English prompt."
                )
                visual_prompt = call_chat(visual, vis_req)
            except Exception as e:
                log(f"WARN fallback prompt from visual director failed: {e}")
    
    if not visual_prompt:
        keywords = article.get("keywords") or article.get("title") or "modern tech developer setup"
        if isinstance(keywords, list):
            keywords = ", ".join(keywords[:5])
        visual_prompt = (
            f"Photorealistic 8k photograph of authentic modern tech workstation with real hardware setup related to {keywords}, "
            "shot on 35mm lens, f/1.8 aperture, natural depth of field, warm ambient lighting, real-world textures, "
            "cinematic documentary tech photography, no text, no letters, no words, no watermark"
        )
    
    # Strip any potential text labels from prompt
    clean_prompt = re.sub(r'[\r\n\t]+', ' ', visual_prompt).strip()
    log(f"Generating FALLBACK thumbnail with prompt: {clean_prompt[:120]}...")
    prompt_str = urllib.parse.quote(clean_prompt)
    url = f"https://image.pollinations.ai/prompt/{prompt_str}?width=1280&height=720&model=flux&nologo=true"
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AizuDemy/1.0"})
        with urllib.request.urlopen(req, timeout=60) as response:
            raw = response.read()
        if len(raw) > 1024 and (raw.startswith(b'\x89PNG') or raw.startswith(b'\xff\xd8\xff') or raw.startswith(b'RIFF')):
            UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
            try:
                os.chmod(UPLOAD_DIR, 0o755)
            except Exception:
                pass
            raw = apply_watermark_badge(raw)
            filename = f"{article.get('slug') or 'article'}-fallback-{datetime.now().strftime('%Y%m%d%H%M%S')}.jpg"
            target_path = UPLOAD_DIR / filename
            target_path.write_bytes(raw)
            try:
                os.chmod(target_path, 0o644)
            except Exception:
                pass
            public_base = os.getenv("AIZUDEMY_PUBLIC_BASE", "").rstrip("/")
            
            subject = f"⚠️ [AizuDemy Notice] Image Generator Fallback Used for '{article.get('title')}'"
            body = (
                f"Halo Admin,\n\n"
                f"Image generator AI utama bermasalah ({reason}).\n"
                f"Sistem AizuDemy secara otomatis menggunakan Fallback Relevant Image Generator (Pollinations AI).\n\n"
                f"Artikel: {article.get('title')}\n"
                f"Gambar Fallback: {filename}\n"
                f"Waktu: {datetime.now().astimezone().isoformat()}\n\n"
                f"Salam,\nAizuDemy Auto-Publisher System"
            )
            send_email_alert(subject, body)
            
            return f"{public_base}/assets/uploads/{filename}"
    except Exception as exc:
        log(f"WARN fallback image generation also failed: {exc}")
    return ""


def generate_thumbnail(article: dict, ai: dict) -> str:
    visual, image = role_config(ai, "visual"), role_config(ai, "image")
    if not all(image.get(k) for k in ("base_url", "api_key", "model")):
        return generate_fallback_thumbnail(article, ai=ai, reason="Konfigurasi role image belum lengkap")
    
    visual_prompt_req = (
        f"Bertindaklah sebagai Visual Art Director kelas dunia. Buat 1 prompt gambar bahasa Inggris (16:9 widescreen) "
        f"yang SANGAT REALISTIS (photorealistic, real-life documentary photography, 8k, cinematic lighting) "
        f"dan 100% RELEVAN dengan topik artikel: \"{article['title']}\".\n\n"
        "PANDUAN GAYA VISUAL (WAJIB REALISTIS & RELEVAN):\n"
        "1. GAYA FOTOGRAFI NYATA: Tampilkan foto asli berkualitas tinggi (shot on 35mm / 50mm f/1.8 lens, authentic shallow depth of field, real tactile textures, subtle natural film grain, realistic studio/ambient lighting).\n"
        "2. RELEVANSI OBJEK NYATA DENGAN TOPIK:\n"
        "   - Topik Server / Linux / DevOps / Cloud / Jaringan: Ruang server modern nyata (datacenter rack dengan kabel rapi dan LED status menyala halus), hardware enterprise asli, atau meja kerja sysadmin minimalis dengan monitor ultra-wide menampilkan log/terminal realistis yang agak blur.\n"
        "   - Topik AI / LLM / Python / Programming: Meja kerja software engineer nyata yang estetik dan rapi, laptop modern, cangkir kopi, monitor dengan kode pemrograman samar di latar belakang, pencahayaan alami jendela siang hari atau warm ambient desk lamp.\n"
        "   - Topik Cyber Security / Hardening: Hardware security key fisik (seperti YubiKey), hardware server fisik, ruangan data center aman dengan pantulan kaca modern, suasana command center realistis.\n"
        "   - Topik Database / Storage / Web: Workstation developer nyata, visualisasi fisik hardware SSD/Server yang presisi dan realistis.\n"
        "3. DILARANG KERAS GAYA KARTUN / 3D FANTASI: JANGAN gunakan 3D isometric cartoon, jangan gunakan octane render fiksi, jangan gunakan karakter kartun. Utamakan fotografi realistis lingkungan nyata.\n"
        "4. DILARANG ADA TEKS: ABSOLUTELY NO text, NO letters, NO words, NO typography, NO watermark, NO fake logo in the image.\n\n"
        "Balas HANYA prompt gambar bahasa Inggris murni (1-2 kalimat/paragraf padat deskriptif) tanpa teks pengantar atau markdown."
    )
    
    prompt = call_chat(visual, visual_prompt_req) if all(visual.get(k) for k in ("base_url", "api_key", "model")) else ""
    if not prompt or len(prompt.strip()) < 20:
        keywords = article.get("keywords") or article.get("title") or "modern computer engineering workstation"
        if isinstance(keywords, list):
            keywords = ", ".join(keywords[:5])
        prompt = (
            f"Ultra-photorealistic 8k photograph of an authentic modern tech workstation and real enterprise hardware related to {keywords}, "
            "shot on 35mm f/1.8 lens, natural depth of field, tactile real-world textures, warm cinematic ambient lighting, "
            "clean minimal workspace, high-end editorial photography, no text, no letters, no words, no watermark"
        )
    
    log(f"IMAGE dengan model {image['model']}")
    try:
        if image.get("endpoint_mode", "images") == "chat":
            result = request_json(
                f"{image['base_url'].rstrip('/')}/chat/completions",
                {"model": image["model"], "messages": [{"role": "user", "content": prompt}]},
                {"Authorization": f"Bearer {image['api_key']}"}, timeout=360,
            )
            message = (result.get("choices") or [{}])[0].get("message", {})
            candidates = message.get("images") or []
            item = candidates[0] if candidates else {}
            if isinstance(item.get("image_url"), dict): item["url"] = item["image_url"].get("url", "")
            if not item:
                content = message.get("content", "")
                if isinstance(content, list):
                    image_parts = [p for p in content if p.get("type") in ("image_url", "image")]
                    if image_parts:
                        value = image_parts[0].get("image_url") or image_parts[0].get("image") or {}
                        item = {"url": value.get("url", "") if isinstance(value, dict) else value}
                elif isinstance(content, str):
                    match = re.search(r"data:image/[^;]+;base64,([A-Za-z0-9+/=]+)", content)
                    if match: item = {"b64_json": match.group(1)}
        else:
            result = request_json(
                f"{image['base_url'].rstrip('/')}/images/generations",
                {"model": image["model"], "prompt": prompt, "size": "1536x1024", "n": 1},
                {"Authorization": f"Bearer {image['api_key']}"}, timeout=360,
            )
            item = (result.get("data") or [{}])[0]
        if item.get("b64_json"):
            raw = base64.b64decode(item["b64_json"])
        elif item.get("url"):
            req = urllib.request.Request(item["url"], headers={"User-Agent": "AizuDemy/1.0"})
            with urllib.request.urlopen(req, timeout=90) as response: raw=response.read()
        if len(raw) < 1024 or not (raw.startswith(b'\x89PNG') or raw.startswith(b'\xff\xd8\xff') or raw.startswith(b'RIFF')):
            raise RuntimeError("Images API mengembalikan data gambar yang tidak valid")
        UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
        try:
            os.chmod(UPLOAD_DIR, 0o755)
        except Exception:
            pass
        raw = apply_watermark_badge(raw)
        filename = f"{article.get('slug') or 'article'}-{datetime.now().strftime('%Y%m%d%H%M%S')}.jpg"
        target_path = UPLOAD_DIR / filename
        target_path.write_bytes(raw)
        try:
            os.chmod(target_path, 0o644)
        except Exception:
            pass
        public_base = os.getenv("AIZUDEMY_PUBLIC_BASE", "").rstrip("/")
        return f"{public_base}/assets/uploads/{filename}"
    except Exception as exc:
        log(f"WARN Image generator utama gagal ({exc}), beralih ke Fallback Generator...")
        return generate_fallback_thumbnail(article, ai=ai, visual_prompt=prompt, reason=str(exc))


def find_active_series(config: dict, state: dict) -> tuple[str | None, dict | None, int]:
    """Find currently active series or auto-advance to next available unfinished series."""
    series_map = config.get("series", {})
    current_key = config.get("current_series")
    
    # 1. Check current configured series first
    if current_key and current_key in series_map:
        s_data = series_map[current_key]
        s_state = state.setdefault(current_key, {"next_part": 1, "published": []})
        part_no = int(s_state.get("next_part", 1))
        if part_no <= int(s_data.get("total_parts", 0)):
            return current_key, s_data, part_no

    # 2. Check if any other configured series is unfinished
    for s_key, s_data in series_map.items():
        s_state = state.setdefault(s_key, {"next_part": 1, "published": []})
        part_no = int(s_state.get("next_part", 1))
        if part_no <= int(s_data.get("total_parts", 0)):
            config["current_series"] = s_key
            save_json_atomic(CONFIG_FILE, config)
            log(f"Auto-switch ke seri berikutnya: {s_key} (Part {part_no})")
            return s_key, s_data, part_no

    return None, None, 0


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--dry-run", action="store_true", help="Generate tetapi jangan publish/ubah state")
    parser.add_argument("--no-image", action="store_true", help="Lewati tahap image generator")
    parser.add_argument("--draft", action="store_true", help="Kirim sebagai draft untuk review admin")
    parser.add_argument("--restart", action="store_true", help="Abaikan checkpoint dan generate ulang Writer/Editor")
    parser.add_argument("--force-autonomous", action="store_true", help="Paksa mode topik bebas mandiri")
    parser.add_argument("--topic", type=str, default="", help="Topik khusus yang ingin ditulis")
    parser.add_argument("--created-at", type=str, default="", help="Custom datetime created_at (format: YYYY-MM-DD HH:MM:SS)")
    args = parser.parse_args()

    lock_handle = LOCK_FILE.open("w")
    try:
        fcntl.flock(lock_handle, fcntl.LOCK_EX | fcntl.LOCK_NB)
    except BlockingIOError:
        log("SKIP publisher lain masih berjalan")
        return 0

    config = load_json(CONFIG_FILE)
    state = load_json(STATE_FILE)
    runtime_data = load_ai_runtime()
    ai_roles = runtime_data.get("roles", {})
    recent_posts = runtime_data.get("recent_posts", [])
    categories = runtime_data.get("categories", [])

    if args.topic:
        # === MODE 0: CUSTOM TOPIC SPECIFIED VIA CLI / WHATSAPP ===
        custom_topic = args.topic.strip()
        log(f"GENERATE CUSTOM TOPIC: {custom_topic}")
        write_status("running", mode="custom", topic=custom_topic, stage="writer")
        topic_plan = {
            "title": custom_topic,
            "category_slug": "tutorial",
            "topic": custom_topic,
            "keywords": [w for w in custom_topic.split() if len(w) > 3],
            "outline": [],
        }
        is_dup, reason, matched_post = check_topic_duplication(topic_plan, recent_posts)
        if is_dup:
            log(f"⚠️ PERINGATAN TOPIK KUSTOM: {reason} (tetap diproses sesuai instruksi langsung)")
        slug_seed = re.sub(r'[^a-z0-9]+', '-', custom_topic.lower()).strip('-')[:30]
        timestamp = datetime.now().strftime("%Y%m%d%H%M")
        cache_base = HERE / f"checkpoint-custom-{slug_seed}-{timestamp}"
        try: send_wa_start_alert(custom_topic, "tutorial", "Topik Khusus")
        except Exception: pass
        article = generate_article_content(topic_plan, ai_roles, cache_base, args.restart)
        article["category_slug"] = "tutorial"
        active_key = None
    else:
        active_key, active_series, part_no = (None, None, 0) if args.force_autonomous else find_active_series(config, state)

        if active_key and active_series:
            # === MODE 1: SERIES MODE ===
            part = next((item for item in active_series.get("parts", []) if int(item["part"]) == part_no), None)
            if not part:
                raise RuntimeError(f"Konfigurasi part {part_no} untuk seri {active_key} tidak ditemukan")

            log(f"GENERATE {active_key} part {part_no}/{active_series['total_parts']}: {part['topic']}")
            write_status("running", mode="series", series=active_key, part=part_no, stage="writer")
            
            topic_plan = {
                "title": f"{active_series.get('prefix', active_series['title'])}: {part['topic']}",
                "category_slug": active_series.get("category_slug", "tutorial"),
                "topic": part["topic"],
                "keywords": part["keywords"],
                "outline": part["outline"],
            }
            cache_base = HERE / f"checkpoint-{active_key}-{part_no}"
            try: send_wa_start_alert(topic_plan["title"], topic_plan.get("category_slug", "tutorial"), f"Seri {active_key} Part {part_no}")
            except Exception: pass
            article = generate_article_content(topic_plan, ai_roles, cache_base, args.restart)
            article["category_slug"] = active_series.get("category_slug", "tutorial")

        else:
            # === MODE 2: AUTONOMOUS DYNAMIC TOPIC MODE (ALWAYS GENERATES) ===
            log("Tidak ada seri aktif tersisa. Memulai GENERATOR TOPIK OTOMATIS (Autonomous Infinite Mode)...")
            write_status("running", mode="autonomous", stage="strategist")
            
            topic_plan = generate_autonomous_topic_plan(ai_roles, recent_posts, categories)
            slug_seed = re.sub(r'[^a-z0-9]+', '-', topic_plan.get("title", "auto").lower()).strip('-')[:30]
            timestamp = datetime.now().strftime("%Y%m%d%H%M")
            cache_base = HERE / f"checkpoint-auto-{slug_seed}-{timestamp}"
            
            write_status("running", mode="autonomous", topic=topic_plan.get("title"), stage="writer")
            try: send_wa_start_alert(topic_plan.get("title", "Otonom"), topic_plan.get("category_slug", "tutorial"), "Otonom Mandiri")
            except Exception: pass
            article = generate_article_content(topic_plan, ai_roles, cache_base, args.restart)
            article["category_slug"] = topic_plan.get("category_slug", "tutorial")

    if not args.no_image:
        write_status("running", stage="image", title=article.get("title"))
        try:
            article["thumbnail"] = generate_thumbnail(article, ai_roles)
        except Exception as exc:
            log(f"WARN thumbnail gagal total, artikel tetap dilanjutkan: {exc}")
            article["thumbnail"] = ""

    article["status"] = "draft" if args.draft else "publish"

    if args.dry_run:
        preview = HERE / f"preview-latest.json"
        save_json_atomic(preview, article)
        write_status("preview_ready", preview=str(preview), title=article.get("title"))
        log(f"DRY-RUN tersimpan: {preview}")
        return 0

    endpoint = os.getenv("AIZUDEMY_PUBLISH_URL", "https://aizudemy.net/api/publish.php")
    token = os.getenv("AIZUDEMY_PUBLISH_TOKEN")
    if not token and TOKEN_FILE.exists():
        token = TOKEN_FILE.read_text(encoding="utf-8").strip()
    if not token:
        token = "YOUR_PUBLISH_TOKEN_HERE"

    article["token"] = token
    if args.created_at:
        article["created_at"] = args.created_at
    result = request_json(f"{endpoint}?token={token}", article, {"X-Token": token}, timeout=45)
    if not result.get("ok"):
        raise RuntimeError(f"Publish gagal: {result}")

    pub_info = {
        "id": result.get("id"),
        "title": article.get("title"),
        "slug": result.get("slug"),
        "category_slug": article.get("category_slug"),
        "published_at": args.created_at if args.created_at else datetime.now().astimezone().isoformat(timespec="seconds"),
    }

    if active_key:
        s_state = state.setdefault(active_key, {"next_part": 1, "published": []})
        pub_info["part"] = part_no
        s_state.setdefault("published", []).append(pub_info)
        s_state["next_part"] = part_no + 1
        save_json_atomic(STATE_FILE, state)
        
        write_status(
            "success",
            mode="series",
            series=active_key,
            part=part_no,
            post_id=result.get("id"),
            url=result.get("url"),
            title=article.get("title"),
            image=bool(article.get("thumbnail")),
            message=f"Artikel Seri '{active_key}' Part {part_no} berhasil dipublikasikan!"
        )
    else:
        auto_state = state.setdefault("autonomous", {"published": []})
        auto_state.setdefault("published", []).append(pub_info)
        save_json_atomic(STATE_FILE, state)
        
        write_status(
            "success",
            mode="autonomous",
            series="standalone",
            post_id=result.get("id"),
            url=result.get("url"),
            title=article.get("title"),
            category=article.get("category_slug"),
            image=bool(article.get("thumbnail")),
            message=f"Artikel Otomatis Mandiri berhasil dipublikasikan: '{article.get('title')}' ({article.get('category_slug')})"
        )

    log(f"PUBLISHED: {result.get('url')} ({article.get('title')})")
    
    # Send concise WhatsApp notification to Admin
    try:
        send_wa_publish_alert(
            title=article.get("title", ""),
            url=result.get("url", ""),
            category=article.get("category_slug", ""),
            mode=active_key or "Autonomous"
        )
    except Exception as wa_e:
        log(f"WARN Error trigger WA alert: {wa_e}")

    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as exc:
        err_msg = str(exc)
        log(f"ERROR {err_msg}")
        try: write_status("failed", error=err_msg[:500])
        except Exception: pass
        
        retry_link = ""
        try:
            import secrets
            retry_key = secrets.token_hex(32)
            token = os.getenv("AIZUDEMY_PUBLISH_TOKEN", "YOUR_PUBLISH_TOKEN_HERE")
            request_json("https://aizudemy.net/api/register_retry_token.php", {"key": retry_key, "token": token}, {"X-Token": token}, timeout=10)
            retry_link = f"https://aizudemy.net/api/retry_publish.php?key={retry_key}"
        except Exception as retry_exc:
            log(f"WARN Gagal registrasi link retry: {retry_exc}")

        subject = f"🚨 [AizuDemy ALERT] Auto-Publisher Pipeline Gagal!"
        body = (
            f"Halo Admin,\n\n"
            f"Pipeline AizuDemy Auto-Publisher mengalami kegagalan/error!\n\n"
            f"Detail Error: {err_msg}\n"
            f"Waktu: {datetime.now().astimezone().isoformat()}\n\n"
        )
        if retry_link:
            body += f"👉 KLIK LINK SEKALI PAKAI INI UNTUK MEMICU ULANG PROSES SEKARANG:\n{retry_link}\n\n"

        body += (
            f"Silakan periksa log sistem di scripts/publisher.log\n\n"
            f"Salam,\nAizuDemy Auto-Publisher System"
        )
        send_email_alert(subject, body)
        
        raise SystemExit(1)
