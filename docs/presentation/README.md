# Presentasi SoundKu

Materi presentasi proyek **SoundKu** (web streaming musik dari SoundCloud).

| File | Keterangan |
| --- | --- |
| `SoundKu-Presentasi.pptx` | Deck utama, 20 slide 16:9, sudah berisi speaker notes |
| `SoundKu-Presentasi.pdf` | Versi PDF untuk melihat cepat / cadangan |
| `preview/Slide*.PNG` | Hasil render tiap slide |
| `assets/` | Screenshot asli yang dipakai di deck |

## Isi deck

1. Cover
2. Latar belakang (dari situs manga ke musik)
3. Tujuan & ruang lingkup
4. Teknologi yang dipakai
5. Arsitektur sistem
6. Mengambil data dari SoundCloud
7. Dua jenis stream: ALLOW vs SNIP
8. Fitur utama
9. **Pembagian jobdesk 5 orang**
10. **Timeline & alur kolaborasi**
11. Beranda
12. Pemutar musik
13. Pencarian & genre
14. Panel admin
15. Struktur proyek
16. **Database & penyimpanan data**
17. Menjalankan proyek
18. Pengujian & hasil
19. Tantangan & solusi
20. Penutup / demo

## Catatan

- Ganti nama di slide 1 (`Oleh: Alvin Yafiq Firas`) sesuai kebutuhan.
- Setiap slide punya **speaker notes** berisi poin yang bisa dibacakan saat presentasi.
- Tema mengikuti warna aplikasi: latar gelap, aksen ungu–pink.

## Regenerasi

```bash
python -m pip install python-pptx
python build_deck.py
```

Screenshot diambil ulang dengan `capture.py` (butuh Chrome, `websocket-client`,
dan server Laravel berjalan di `http://127.0.0.1:8000`).
