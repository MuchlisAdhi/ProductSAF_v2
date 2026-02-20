# ProductSAF Laravel 12

Migrasi penuh aplikasi `MuchlisAdhi/ProductSAF` dari Next.js + Prisma ke Laravel 12 (PHP + MySQL/SQLite).

## Fitur

- Katalog publik:
  - Home kategori
  - List produk + filter + pagination
  - Detail produk + tabel nutrisi
- Auth admin:
  - Login/logout session-based
  - API auth (`/api/auth/login`, `/api/auth/logout`, `/api/auth/signup`)
- Admin dashboard:
  - CRUD kategori
  - CRUD produk (termasuk nutrisi dinamis)
  - Upload gambar aset
  - CRUD user + role (`SUPERADMIN`, `ADMIN`, `USER`) untuk SUPERADMIN
- API endpoint kompatibel struktur lama:
  - `/api/products`, `/api/categories`, `/api/users`, `/api/assets/upload`

## Struktur Data

Tabel utama:

- `users`
- `sessions` (untuk session driver Laravel)
- `auth_sessions` (mapping dari model session aplikasi lama)
- `categories`
- `assets`
- `products`
- `nutritions`

ID pada entitas domain menggunakan string ULID.

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

3. Jalankan migrasi + seed:

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

## Catatan Konfigurasi `.env`

- Untuk SQLite, `DB_DATABASE` harus menunjuk file yang ada.
- Jika memakai MySQL/MariaDB di cPanel, set:
  - `DB_CONNECTION=mysql`
  - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_DRIVER` bisa `file` atau `database` (proyek ini kompatibel keduanya).

## Verifikasi

```bash
php artisan test
```

Status saat implementasi ini:
- Test unit/feature default lulus.
