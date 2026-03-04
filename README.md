# ProductSAF Laravel 12

Migrasi aplikasi `MuchlisAdhi/ProductSAF` dari Next.js + Prisma ke Laravel 12 (PHP + MySQL/SQLite), dengan dashboard admin berbasis Volt Bootstrap 5.

## Fitur Utama

### 1) Katalog Publik
- Home kategori produk.
- Daftar produk dengan filter + pagination.
- Detail produk + tabel nutrisi.
- Optimasi loading gambar (thumbnail + kompresi untuk upload baru/legacy).

### 2) Admin Panel (Volt Bootstrap 5)
- Login/logout berbasis session.
- Layout admin sudah dimigrasikan ke gaya Volt:
  - Sidebar + topbar account dropdown.
  - Form, table, modal, chart, dan komponen Bootstrap 5.
- CRUD:
  - Produk (termasuk nutrisi dinamis + upload gambar).
  - Kategori.
  - Pengguna (khusus role `SUPERADMIN`).
- Waktu pada list admin/tracker ditampilkan dalam zona `Asia/Jakarta`.

### 3) Tracker Kunjungan Publik
- Middleware `track.public` untuk mencatat kunjungan halaman publik.
- Menu tracker di admin:
  - `Summary` (chart + top pages),
  - `Visits`,
  - `Users (Guest)`.
- Konfigurasi: `config/public_tracker.php`.

### 4) Maintenance Mode Tanpa Terminal (cPanel Friendly)
- Toggle maintenance dari web admin (khusus `SUPERADMIN`) via dropdown account.
- Route:
  - `POST /admin/maintenance/enable`
  - `POST /admin/maintenance/disable`
- Mekanisme:
  - Membuat/menghapus file `public/maintenance.enable`.
  - Rule Apache di `public/.htaccess` akan redirect publik ke `public/maintenance.html`.
  - `ErrorDocument 500/503` juga diarahkan ke `maintenance.html`.
  - `/admin` dan `/login` tetap bisa diakses saat maintenance aktif.

### 5) Progressive Web App (PWA) + Offline Sync Admin
- Aplikasi sudah didaftarkan sebagai PWA (`manifest.webmanifest` + `service-worker.js`).
- Start URL PWA diarahkan ke splash page native: `/splash-screen` (porting desain dari `SplashScreen.tsx`, tanpa `App.tsx`).
- Font splash sudah self-host (`public/fonts/pwa`) sehingga tidak lagi bergantung ke `fonts.googleapis.com`/`fonts.gstatic.com` saat offline.
- Disediakan tombol login admin tersembunyi di halaman publik (muncul setelah tap logo brand 7x dalam 8 detik).
- Saat install PWA, service worker otomatis melakukan bootstrap cache dari `pwa/bootstrap-data.json` berisi:
  - data kategori, produk, nutrisi, dan asset gambar dari database,
  - daftar URL halaman/detail publik dan URL gambar untuk diprefetch.
- Halaman publik dan aset utama diprecache tanpa user perlu membuka halaman satu per satu.
- Runtime cache untuk request image publik agar gambar yang sudah pernah dibuka bisa tetap ditampilkan saat offline.
- Halaman fallback offline: `/offline`.
- Admin offline queue (IndexedDB) untuk:
  - Tambah, ubah, dan hapus Kategori.
  - Tambah, ubah, dan hapus Produk (termasuk upload gambar saat create/update).
  - Bulk delete Produk (disimpan sebagai antrean delete per item produk).
- Saat internet kembali aktif, antrean otomatis sinkron ke server.
- Status sinkronisasi tampil sebagai badge di kanan bawah halaman admin.

## API Endpoint

- Auth:
  - `POST /api/auth/login`
  - `POST /api/auth/logout`
  - `POST /api/auth/signup`
- Produk:
  - `GET /api/products`
  - `GET /api/products/{id}`
  - `POST /api/products` (admin)
  - `PUT /api/products/{id}` (admin)
  - `DELETE /api/products/{id}` (admin)
  - `POST /api/products/bulk-delete` (admin)
- Kategori:
  - `GET /api/categories`
  - `POST /api/categories` (admin)
  - `PUT /api/categories/{id}` (admin)
  - `DELETE /api/categories/{id}` (admin)
- User:
  - `GET /api/users` (superadmin)
  - `POST /api/users` (superadmin)
  - `PUT /api/users/{id}` (superadmin)
  - `DELETE /api/users/{id}` (superadmin)
- Aset:
  - `POST /api/assets/upload` (admin)
- Offline Sync Admin (web session auth):
  - `POST /admin/offline-sync/categories` (admin)
  - `POST /admin/offline-sync/categories/{id}` (admin)
  - `POST /admin/offline-sync/categories/{id}/delete` (admin)
  - `POST /admin/offline-sync/products` (admin)
  - `POST /admin/offline-sync/products/{id}` (admin)
  - `POST /admin/offline-sync/products/{id}/delete` (admin)
- PWA Bootstrap Data:
  - `GET /pwa/bootstrap-data.json`
- PWA Splash:
  - `GET /splash-screen`

## Struktur Data

Tabel utama:
- `users`
- `sessions`
- `auth_sessions`
- `categories`
- `assets`
- `products`
- `nutritions`
- `tracker_visits`

Migrasi tambahan performa:
- `2026_02_25_000020_add_thumbnail_path_to_assets_table.php`
- `2026_02_25_000030_add_performance_indexes.php`

ID entitas domain menggunakan string ULID.

## Setup Lokal

1. Install dependency:
```bash
composer install
npm install
```

2. Generate app key:
```bash
php artisan key:generate
```

3. Migrasi + seed:
```bash
php artisan migrate:fresh --seed
```

4. Build asset frontend:
```bash
npm run build
```

5. Jalankan aplikasi:
```bash
php artisan serve
```

## Akun Default Seed

- Email: `admin@sidoagung.com`
- Password: `password123`
- Role: `SUPERADMIN`

## Konfigurasi `.env`

- Untuk SQLite: `DB_DATABASE` harus menunjuk file yang ada.
- Untuk MySQL/MariaDB (cPanel):
  - `DB_CONNECTION=mysql`
  - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Session:
  - `SESSION_DRIVER=file` atau `SESSION_DRIVER=database`
- Tracker publik:
  - `PUBLIC_TRACKER_ENABLED=true|false`

## One-Time Script Gambar Legacy

Tersedia command untuk generate thumbnail + kompres gambar lama:

```bash
php artisan assets:backfill-legacy-images --source="C:\Users\Lenovo\Downloads\uploads productsaf"
```

Contoh mode aman (simulasi):

```bash
php artisan assets:backfill-legacy-images --source="C:\Users\Lenovo\Downloads\uploads productsaf" --dry-run
```

## Maintenance Mode Operasional

### Via Web (disarankan)
1. Login sebagai `SUPERADMIN`.
2. Klik avatar account di topbar admin.
3. Gunakan tombol `Turn On` / `Turn Off` di bagian Maintenance Mode.

### Fallback manual cPanel (tanpa route)
- Aktifkan: buat file `public/maintenance.enable`.
- Nonaktifkan: hapus file `public/maintenance.enable`.

## Cara Penggunaan PWA

### 1) Aktivasi / Instalasi PWA
1. Deploy aplikasi menggunakan HTTPS (contoh produksi: `https://product.sidoagungfarm.com`).
2. Buka website dari browser modern (Chrome/Edge Android/Desktop).
3. Pilih menu `Install App` / `Add to Home Screen`.
4. Jalankan aplikasi dari shortcut yang terpasang.
5. Saat aplikasi dibuka dari shortcut, splash `/splash-screen` tampil lebih dulu lalu otomatis lanjut ke beranda.

### 2) Penggunaan Offline Halaman Publik
1. Saat install/pertama kali membuka PWA dalam kondisi online, service worker otomatis bootstrap cache data publik dari server.
2. Data kategori, produk, detail produk, dan image asset terkait langsung diprefetch ke cache offline.
3. Saat tanpa sinyal, user bisa langsung mengakses data publik yang sudah dibootstrap.
4. Jika ada rute di luar daftar bootstrap, aplikasi tampilkan fallback `/offline`.
5. Ketika koneksi kembali online, service worker otomatis refresh cache dinamis untuk menangkap data kategori/produk terbaru.

### 3) Penggunaan Offline Halaman Admin (Tambah/Ubah/Hapus Kategori & Produk)
1. Login admin seperti biasa.
   - Opsi cepat dari halaman publik: tap logo brand (header kiri atas) sebanyak `7x` dalam `8 detik`, lalu tombol `Admin Login` akan muncul selama `15 detik`.
2. Ketika offline, aksi berikut otomatis masuk antrean lokal browser (IndexedDB):
   - `Tambah Kategori`
   - `Ubah Kategori`
   - `Hapus Kategori`
   - `Tambah Produk`
   - `Ubah Produk`
   - `Hapus Produk`
   - `Bulk Delete Produk` (disimpan sebagai beberapa antrean hapus produk)
3. Saat submit/konfirmasi delete ditekan, data tidak dikirim ke server saat itu juga, tetapi aman tersimpan di antrean lokal.
4. Badge status di kanan bawah menampilkan jumlah antrean offline.
5. Saat koneksi internet kembali, antrean otomatis disinkronkan ke server.

### 4) Catatan Operasional
- Offline queue saat ini mendukung proses **create/update/delete** untuk kategori dan produk.
- Untuk aksi delete, jika data sudah tidak ada di server saat sinkronisasi maka antrean akan dianggap selesai (dihapus dari queue).
- Untuk kategori yang masih dipakai produk, delete dapat ditolak server saat sinkronisasi.
- Browser data (cache + IndexedDB) bersifat per-device dan per-browser.
- Jangan membersihkan cache/storage browser jika antrean offline belum tersinkron.

## Build APK dengan PWABuilder Studio (VSCode)

Prasyarat:
1. Domain produksi sudah HTTPS dan bisa diakses publik (contoh: `https://product.sidoagungfarm.com`).
2. Endpoint PWA valid:
   - `/manifest.webmanifest`
   - `/service-worker.js`
   - `/pwa/bootstrap-data.json`
3. Icon PWA tersedia:
   - `/icons/icon-192.png`
   - `/icons/icon-512.png`

Langkah step-by-step:
1. Pastikan kode terbaru sudah ter-deploy ke server produksi.
2. Buka VSCode di project ini.
3. Buka panel PWABuilder Studio:
   - `View` -> `Extensions` (pastikan extension PWABuilder Studio aktif).
   - Buka `Command Palette` (`Ctrl + Shift + P`) -> jalankan command PWABuilder Studio (mis. `PWABuilder: Open Studio` / `PWABuilder Studio: Open`).
4. Masukkan URL produksi PWA:
   - `https://product.sidoagungfarm.com`
5. Jalankan proses analisis dan perbaiki warning yang muncul jika ada (manifest, icons, service worker, HTTPS, dsb).
6. Pilih target package Android (`Android` / `Trusted Web Activity` dari panel PWABuilder Studio).
7. Isi metadata paket:
   - `Application Name`
   - `Package ID` (contoh `com.sidoagung.productsaf`)
   - `Version Code` dan `Version Name`
   - Splash screen/icon sesuai kebutuhan.
8. Generate package Android dari panel PWABuilder Studio.
9. Pilih mode signing:
   - Debug signing untuk uji internal, atau
   - Release signing dengan keystore produksi.
10. Build dan simpan output APK/AAB ke folder rilis.
11. Install APK ke perangkat Android dan lakukan uji:
   - buka aplikasi tanpa internet (data publik tetap muncul),
   - login admin (termasuk tombol login tersembunyi),
   - tambah/ubah/hapus kategori/produk saat offline,
   - aktifkan internet dan pastikan antrean sinkron ke database `product.sidoagungfarm.com`.

Catatan penting untuk Play Store:
1. Jika menggunakan TWA, siapkan Digital Asset Links (`.well-known/assetlinks.json`) sesuai certificate signing release.
2. Saat ganti signing key atau package ID, update konfigurasi TWA dan asset links sebelum publish.

Referensi:
- https://marketplace.visualstudio.com/items?itemName=pwabuilder.pwa-studio
- https://docs.pwabuilder.com/

## Catatan ReportCard PWABuilder

Perbaikan yang sudah diterapkan agar warning utama PWABuilder berkurang:
- Manifest sudah memiliki `id` stabil: `/?source=pwa`.
- Manifest sudah menambahkan `screenshots`:
  - `/images/pwa/screenshot-home.jpeg`
  - `/images/pwa/screenshot-products.jpeg`
- Service worker didaftarkan dari halaman publik dan juga halaman start URL `/splash-screen`.
- Endpoint PWA penting tetap di-allow saat maintenance aktif melalui `public/.htaccess`:
  - `/manifest.webmanifest`
  - `/service-worker.js`
  - `/pwa/bootstrap-data.json`
  - `/js/pwa-register.js`
  - `/js/admin-offline-sync.js`

Langkah re-test reportcard:
1. Pastikan maintenance mode nonaktif (`Turn Off`) sebelum scan.
2. Deploy perubahan terbaru ke server produksi.
3. Buka ulang `https://www.pwabuilder.com/reportcard?site=https://www.product.sidoagungfarm.com/`.
4. Klik `View log` untuk memastikan item warning terbaru.
5. Jika log menampilkan `Fetching from web string cache`, jalankan scan dengan URL cache-buster:
   - `https://www.pwabuilder.com/reportcard?site=https://www.product.sidoagungfarm.com/?v=20260304`
6. Untuk kompatibilitas analyzer PWABuilder, registrasi service worker juga ditulis inline di HTML publik dan splash, selain di file `public/js/pwa-register.js`.

Catatan:
- Banyak item pada ReportCard berstatus `optional/info` (misalnya `shortcuts`, `protocol_handlers`, `share_target`, `display_override`, dst) dan tidak wajib untuk publish APK.
- Fokus minimal agar lolos packaging biasanya: HTTPS valid, manifest valid, icon lengkap, service worker terdeteksi, dan halaman dapat dibuka offline.

## Verifikasi

```bash
php artisan test
```

Status saat ini:
- Test default unit/feature lulus.
