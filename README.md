# NARA — Naive Bayes Sentiment Analysis

NARA adalah aplikasi web untuk melakukan analisis sentimen berbasis metode Naive Bayes dari data ulasan (review) dalam format CSV. Aplikasi ini menyediakan antarmuka modern untuk mengunggah data, meninjau isi file, menjalankan analisis, dan menampilkan ringkasan hasil dalam bentuk statistik, tabel, serta visualisasi grafik.

## Fitur Utama

- **Unggah CSV** dengan drag & drop atau pilih file.
- **Preview data** sebelum diimpor (mendeteksi kolom `review`).
- **Simulasi import & analisis** dengan indikator progres.
- **Ringkasan hasil** (positif, negatif, netral) plus metrik akurasi.
- **Visualisasi grafik** doughnut dengan Chart.js.
- **Tabel hasil** yang merangkum contoh review dan label sentimen.

## Teknologi

- **Laravel 12** (PHP 8.2+)
- **Vite** untuk asset bundling
- **Bootstrap 5** + **Font Awesome** (CDN)
- **Chart.js** untuk grafik

## Persyaratan

- PHP **8.2+**
- Composer
- Node.js **18+** & npm

## Instalasi

1. **Clone repo**
   ```bash
   git clone <repo-url>
   cd sistem-nara
   ```

2. **Instal dependency PHP**
   ```bash
   composer install
   ```

3. **Setup file environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Instal dependency frontend & build asset**
   ```bash
   npm install
   npm run build
   ```

## Menjalankan Aplikasi (Development)

Gunakan salah satu cara berikut:

**A. Jalankan server Laravel dan Vite terpisah**
```bash
php artisan serve
npm run dev
```

**B. Jalankan dengan script `composer dev` (server + queue + vite)**
```bash
composer dev
```

Lalu buka: `http://localhost:8000`

## Format CSV

Minimal memiliki kolom bernama `review`. Contoh:

```csv
id,review
1,"Aplikasinya cepat dan mudah dipakai"
2,"Sering error saat login"
3,"Tersedia fitur tambahan yang berguna"
```

## Struktur Halaman

- **/ (Landing)** — Halaman utama analisis sentimen dengan fitur unggah, ringkasan, grafik, dan tabel data.

## Skrip yang Tersedia

- `composer setup` — instalasi lengkap (composer, env, key, migrate, npm build).
- `composer dev` — jalankan server + queue listener + vite.
- `composer test` — jalankan tes Laravel.
- `npm run dev` — dev server Vite.
- `npm run build` — build asset.

## Catatan

- UI menggunakan **Bootstrap 5** dan CSS kustom yang tertanam di `resources/views/index.blade.php`.
- Proses import & analisis pada versi ini adalah simulasi UI (belum terhubung ke proses backend klasifikasi).

---

Jika Anda ingin menghubungkan proses analisis ke backend (misalnya menggunakan model Naive Bayes nyata), Anda dapat menambahkan endpoint baru di `routes/web.php` atau `routes/api.php` dan mengganti simulasi di JavaScript dengan request ke server.
