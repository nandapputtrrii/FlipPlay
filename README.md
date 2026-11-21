# FlipPlay API (Laravel 12)

Lanjutan FlipPlay Modul 3 (simulasi OOP di terminal) menjadi REST API: entitas **Media** dan **Pengguna**
kini berupa Model Eloquent di database, dengan relasi many-to-many untuk **koleksi media pengguna**.

## Menjalankan

Butuh PHP >= 8.2 dan Composer. Default memakai SQLite, jadi tidak perlu setup database.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # Windows: type nul > database\database.sqlite
php artisan migrate:fresh --seed
php artisan serve                       # http://127.0.0.1:8000
```

Untuk MySQL, ubah `DB_CONNECTION=mysql` (+ `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) di `.env`, lalu jalankan migrate lagi.

Data seeder: media dari Modul 3 (Jujutsu Kaisen, Violet Evergarden, A Silent Voice, Blue Lock), pengguna
**Ani (id 1)** dan **Budi (id 2)**; Ani sudah punya Jujutsu Kaisen di koleksinya.

## Endpoint

Cek lewat `php artisan route:list --path=api`.

| Method | URI | Fungsi |
|---|---|---|
| GET | `/api/media?search=&type=&genre=&per_page=` | Daftar media + cari judul + filter |
| POST | `/api/media` | Tambah media (Form Request) |
| GET | `/api/media/{media}` | Detail media + pengguna yang mengoleksi (`whenLoaded`) |
| PUT/PATCH | `/api/media/{media}` | Ubah media (Form Request) |
| DELETE | `/api/media/{media}` | Hapus media (baris pivot ikut terhapus) |
| GET | `/api/users` | Daftar pengguna |
| POST | `/api/users` | Tambah pengguna |
| GET | `/api/users/{user}` | Profil + koleksi media |
| GET | `/api/users/{user}/collection?search=` | **Custom:** koleksi media milik pengguna |
| POST | `/api/users/{user}/collection` | **Custom:** tambah media ke koleksi `{"media_id": 1}` |
| DELETE | `/api/users/{user}/collection/{media}` | **Custom:** hapus media dari koleksi |

### Aturan validasi media

| Field | Aturan |
|---|---|
| `title` | wajib, string, maks 255, **unik** (saat update, boleh sama dengan miliknya sendiri) |
| `type` | wajib, `anime` atau `manga` (tidak bisa diubah saat update) |
| `genre` | wajib |
| `release_year` | wajib, integer 1900 s.d. tahun ini + 2 |
| `episodes` | wajib untuk `anime`, dilarang untuk `manga` |
| `chapters` | wajib untuk `manga`, dilarang untuk `anime` |

### Kode respons

| Kode | Kapan |
|---|---|
| 200 / 201 | Berhasil / berhasil dibuat |
| 404 | Data tidak ada, atau media tidak ada di koleksi saat dihapus dari koleksi |
| 409 | Media sudah ada di koleksi pengguna yang sama |
| 422 | Validasi gagal (format Laravel: `message` + `errors`) |

## Pemetaan Modul 3 ke Laravel

| Modul 3 | Laravel |
|---|---|
| `Media` (abstract) + `Anime` / `Manga` | Model `Media` dengan kolom `type`; `episodes` khusus anime, `chapters` khusus manga |
| `Media::getDetails()` | accessor `details` di model `Media` |
| `User::$collection` + `addToCollection()` | relasi `User::collection()` (belongsToMany) + `UserCollectionController@store` |
| `MediaLibrary::searchByTitle()` | scope `Media::searchTitle()` dipakai di `GET /api/media?search=` |
| `MediaPrinter` | `MediaResource` / `UserResource` |
| validasi `promptRequired()` dll. | `StoreMediaRequest`, `UpdateMediaRequest`, `AddToCollectionRequest` |

## Struktur utama

```
app/Models/            Media.php, User.php
app/Http/Controllers/  MediaController, UserController, UserCollectionController
app/Http/Requests/     BaseMediaRequest, StoreMediaRequest, UpdateMediaRequest, AddToCollectionRequest, StoreUserRequest
app/Http/Resources/    MediaResource, UserResource
database/migrations/   create_media_table, create_media_user_table (pivot, unique user_id+media_id)
routes/api.php
postman/               FlipPlay-API.postman_collection.json
tests/                 Feature + Unit (php artisan test)
```

## Pengujian

**Postman:** import `postman/FlipPlay-API.postman_collection.json`, lalu jalankan lewat *Collection Runner*
secara berurutan (folder 1 sampai 4). Isinya 30 request, masing-masing dengan test, mencakup happy path dan
skenario gagal (judul kosong, judul duplikat, media sama dua kali ke koleksi, media/user tidak ada, dsb.).

**Otomatis:** `php artisan test`

## Push ke GitHub

```bash
git init && git add . && git commit -m "FlipPlay REST API with Laravel 12"
git branch -M main
git remote add origin https://github.com/<username>/<repo>.git
git push -u origin main
```
