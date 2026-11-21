#!/usr/bin/env python3
"""Batch catch-up publisher for AizuDemy missed slots."""
import subprocess
import sys
import time
from pathlib import Path

MISSED_SLOTS = [
    "2026-09-02 17:00:00",
    "2026-09-03 07:00:00",
    "2026-09-03 17:00:00",
    "2026-09-04 07:00:00",
    "2026-09-04 17:00:00",
    "2026-09-05 07:00:00",
    "2026-09-05 17:00:00",
    "2026-09-06 07:00:00",
    "2026-09-06 17:00:00",
    "2026-09-07 07:00:00",
]

HERE = Path(__file__).resolve().parent
PUBLISHER_SCRIPT = HERE / "daily_publisher.py"

def main():
    print(f"=== Memulai Batch Catchup Auto-Publisher AizuDemy ({len(MISSED_SLOTS)} slot) ===")
    success_count = 0
    
    for idx, slot in enumerate(MISSED_SLOTS, 1):
        print(f"\n[{idx}/{len(MISSED_SLOTS)}] Memproses slot: {slot}...")
        cmd = [
            sys.executable,
            str(PUBLISHER_SCRIPT),
            "--force-autonomous",
            "--created-at", slot,
        ]
        
        attempt = 1
        max_attempts = 3
        slot_ok = False
        
        while attempt <= max_attempts:
            try:
                res = subprocess.run(cmd, check=True)
                if res.returncode == 0:
                    slot_ok = True
                    success_count += 1
                    print(f"✅ Slot {slot} berhasil dipublikasikan!")
                    break
            except subprocess.CalledProcessError as e:
                print(f"⚠️ Percobaan {attempt}/{max_attempts} gagal untuk slot {slot}: {e}")
                attempt += 1
                time.sleep(15)
                
        if not slot_ok:
            print(f"❌ Slot {slot} gagal setelah {max_attempts} percobaan. Melanjutkan ke slot berikutnya...")
            
        time.sleep(8)

    print(f"\n=== Batch Catchup Selesai! {success_count}/{len(MISSED_SLOTS)} artikel berhasil diterbitkan. ===")

if __name__ == "__main__":
    main()
