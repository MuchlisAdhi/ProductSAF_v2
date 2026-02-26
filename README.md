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

## Verifikasi

```bash
php artisan test
```

Status saat ini:
- Test default unit/feature lulus.
