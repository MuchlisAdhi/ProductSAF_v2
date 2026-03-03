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
- Halaman publik dan aset utama diprecache, termasuk image produk dari database.
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

### 2) Penggunaan Offline Halaman Publik
1. Saat online, buka dulu halaman publik utama:
   - `/`
   - `/products`
   - detail produk/kategori yang sering dipakai.
2. Service worker akan menyimpan cache halaman dan image yang dibuka.
3. Saat tanpa sinyal, user tetap bisa membuka konten yang sudah tercache.
4. Jika halaman belum pernah tercache, aplikasi tampilkan fallback `/offline`.

### 3) Penggunaan Offline Halaman Admin (Tambah/Ubah/Hapus Kategori & Produk)
1. Login admin seperti biasa.
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

## Verifikasi

```bash
php artisan test
```

Status saat ini:
- Test default unit/feature lulus.
