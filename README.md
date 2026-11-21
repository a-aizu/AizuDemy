# AizuDemy

Platform blog & belajar digital dengan pipeline publikasi artikel otomatis berbasis AI — [aizudemy.net](https://aizudemy.net)

Dibangun dan dioperasikan solo, end-to-end: kode, database, server, sampai deployment.

## Fitur utama
- Blog publik: beranda, kategori, pencarian, halaman artikel, feed & sitemap
- Admin panel: moderasi komentar, dashboard, manajemen konten
- Pipeline publisher otomatis: naskah AI → review → terbit terjadwal (Python + PHP CLI bridge)
- API publish dengan autentikasi token
- Integrasi SSO (Aizu Sphere)
- SEO: llms.txt, sitemap dinamis, thumbnail otomatis

## Stack
- **Backend:** PHP (tanpa framework), MySQL / MariaDB (PDO)
- **Publisher:** Python, PHP CLI
- **Frontend:** HTML, CSS, JavaScript
- **Server:** Linux, LiteSpeed, MariaDB

## Catatan keamanan repo publik
Repo ini adalah salinan kode untuk portofolio. Semua kredensial sudah dihapus:
- `app/config/database.php` membaca kredensial dari environment variable
- Token publish diganti placeholder `YOUR_PUBLISH_TOKEN_HERE`
- File runtime (log, checkpoint pipeline, status, uploads) tidak disertakan

## Lisensi
MIT
