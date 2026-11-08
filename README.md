# SoundKu

Web streaming musik sederhana yang mengambil katalog publik dari **SoundCloud** (scraping API v2), dibangun dengan **Laravel 12**. Dulu proyek ini adalah situs baca manga; sekarang seluruh antarmuka dan datanya sudah diganti menjadi pemutar musik.

![SoundKu](https://img.shields.io/badge/Laravel-12-ff2d20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-777bb4?logo=php&logoColor=white)

## Fitur

- **Beranda** dengan hero track pilihan, chart trending SoundCloud, dan section chill/lo-fi.
- **Pencarian** lagu, artis, dan playlist langsung ke API SoundCloud (dengan saran pencarian realtime di topbar).
- **Halaman detail** track, artis, dan playlist.
- **Genre** — chart per genre dengan fallback otomatis ke pencarian bila chart kosong.
- **Pemutar audio** dengan antrean, acak, ulangi (semua/satu), seek, volume, Media Session, dan dukungan HLS via hls.js.
- **Favorit** dan **riwayat putar** disimpan di `localStorage` (tanpa akun).
- **Panel admin** untuk mengkurasi "Pilihan editor" dengan menempel URL track atau playlist SoundCloud. Metadata diambil otomatis.
- **Proxy audio** di server untuk segmen HLS SoundCloud yang tidak mengizinkan CORS.

## Cara kerja pengambilan data

`App\Services\SoundCloudService` memanggil `https://api-v2.soundcloud.com` memakai `client_id`. Karena SoundCloud tidak lagi membagikan client ID secara publik:

1. Kalau `SOUNDCLOUD_CLIENT_ID` di `.env` terisi, nilai itu yang dipakai.
2. Kalau kosong, service mencari client ID dari bundle JavaScript `soundcloud.com` lalu menyimpannya di cache selama 6 jam.

Endpoint yang dipakai: `/charts`, `/search/tracks`, `/search/users`, `/search/playlists`, `/tracks/{id}`, `/tracks/{id}/related`, `/users/{id}`, `/users/{id}/tracks`, `/playlists/{id}`, dan `/resolve`.

### Streaming

- Track `policy=ALLOW` umumnya hanya tersedia sebagai **HLS**. Playlist `.m3u8` diambil server, URL segmennya ditulis ulang agar lewat `/api/segment`, lalu diputar browser dengan hls.js.
- Track `policy=SNIP` mendapat **preview 30 detik** (progressive MP3). UI menandainya dengan badge "30 detik".
- URL audio ditandatangani dan kedaluwarsa, jadi semuanya di-cache singkat (2–15 menit) di server.

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Atur database (default SQLite sudah siap):

```env
DB_CONNECTION=sqlite
```

Lalu:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

Buka <http://localhost:8000>.

Seeder membuat akun admin dan (kalau internet tersedia) 8 track trending sebagai pilihan editor:

- Email: `admin@soundku.test`
- Password: `password`

Ganti password lewat `php artisan tinker` sebelum dipakai di publik.

### Variabel opsional

```env
# Isi kalau punya client_id resmi. Kosongkan untuk auto-detect.
SOUNDCLOUD_CLIENT_ID=
```

## Struktur penting

```
app/
├── Exceptions/SoundCloudException.php
├── Http/Controllers/
│   ├── HomeController.php, SearchController.php, GenreController.php
│   ├── TrackController.php, ArtistController.php, PlaylistController.php
│   ├── Api/MusicController.php        # search, stream, hls, segment
│   └── Admin/                         # auth, dashboard, featured
├── Models/FeaturedTrack.php
├── Services/SoundCloudService.php     # client API + stream resolver
└── Support/helpers.php                # format_duration, format_count, track_json
config/music.php                       # daftar genre & section beranda
public/css/app.css                     # tema gelap, player, layout
public/js/player.js                    # audio player + HLS
public/js/app.js                       # favorit, saran pencarian, layout
```

## Endpoint internal

| Method | URL | Fungsi |
| --- | --- | --- |
| GET | `/api/search?q=` | Pencarian track (untuk saran pencarian) |
| GET | `/api/stream/{id}` | Resolve URL streaming (JSON) |
| GET | `/api/hls/{id}` | Playlist HLS yang sudah diproksi |
| GET | `/api/segment?u=` | Proxy segmen audio |

## Catatan

- Proyek ini hanya memutar audio dari katalog publik SoundCloud dan tidak mengunduh/menyimpan lagu.
- Chart per genre milik SoundCloud sering mengembalikan 404. `SoundCloudService::chart()` otomatis fallback ke hasil pencarian genre tersebut.
- Kalau SoundCloud mengubah struktur bundle atau API-nya, isi `SOUNDCLOUD_CLIENT_ID` manual agar situs tetap jalan.
