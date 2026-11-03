# Project Duo Developer: Website To-Do List Pribadi

> **Tugas Kolaborasi XI RPL SMK PESAT IT XPRO**  
> **Tema**: *Kontrak dulu, ngoding bareng! (BE + Swagger × FE + Mock Data)*

---

## 👥 Identitas Pengembang

| Peran | Nama Siswa | Kelas | Tanggung Jawab Utama |
|:---|:---|:---:|:---|
| **Back-End (BE)** | *(Isi Nama Kamu)* | XI RPL | API Laravel + Sanctum, L5-Swagger, Migrasi DB, Kontrak API |
| **Front-End (FE)** | *(Isi Nama Pasangan)* | XI RPL | Tampilan Blade, JavaScript Fetch, Integrasi Mock Data ke API |

---

## 📌 Deskripsi Proyek

Aplikasi **To-Do List Pribadi** memungkinkan pengguna untuk mendaftar akun, masuk (login), melihat profil, mengelola tugas harian (tambah, lihat, ubah judul/tenggat, tandai selesai/belum selesai, dan hapus tugas). Sistem mengisolasi data secara ketat: **setiap akun hanya dapat melihat dan mengelola tugas miliknya sendiri**.

Pengembangan dilakukan secara paralel:
1. **BE** merancang kontrak API awal, menulis dokumentasi Swagger, dan menyediakan API berbasis Laravel Sanctum.
2. **FE** merancang antarmuka menggunakan Blade dan JavaScript dengan data Mock JSON sesuai kontrak sebelum API selesai.
3. Kedua pihak mengintegrasikan sistem dan menguji alur secara menyeluruh.

---

## 🌿 Struktur Percabangan (Git Branch)

- `main` : Branch utama hasil integrasi akhir.
- `feat/backend` : Branch kerja Back-End (API, Sanctum, Swagger, Database).
- `feat/frontend` : Branch kerja Front-End (Blade Views, CSS/Tailwind, JavaScript, Mock Data).

---

## 🚀 Panduan Instalasi & Menjalankan Project

### 1. Prasyarat
- PHP >= 8.3 / 8.5
- Composer
- SQLite / MySQL
- Web Browser

### 2. Langkah Instalasi

1. **Clone repositori**:
   ```bash
   git clone <URL_REPOSITORY>
   cd kolaborasi
   ```

2. **Checkout ke branch kerja**:
   ```bash
   git checkout feat/backend
   ```

3. **Install Dependensi Composer**:
   ```bash
   composer install
   ```

4. **Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env` lalu generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database**:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Generate Dokumentasi Swagger (OpenAPI)**:
   ```bash
   php artisan l5-swagger:generate
   ```

7. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser di: `http://localhost:8000`

---

## 📖 Dokumentasi API & Swagger

Dokumentasi API interaktif dapat dibuka langsung melalui web browser:
🔗 **[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)**

Dokumen spesifikasi untuk Front-End:
- **Kontrak API Lengkap**: [docs/kontrak-api.md](docs/kontrak-api.md)
- **Spesifikasi OpenAPI JSON**: [docs/openapi.json](docs/openapi.json)

---

## 🧪 Pengujian Otomatis (Automated Tests)

Untuk memvalidasi bahwa seluruh endpoint autentikasi, isolasi data antar user, dan fitur to-do list berjalan tanpa cacat:
```bash
php artisan test
```

---

## 💡 Refleksi Integrasi (Sesuai LKPD Tahap 04)

### 1. Apa perbedaan mock dan API nyata?
- **Mock Data**: Merupakan tiruan data statis (biasanya berupa file JSON atau array JavaScript lokal) yang digunakan oleh Front-End agar pengembangan tampilan (UI/UX), tata letak form, dan penanganan status visual (loading, data kosong, status selesai) dapat dikerjakan secara langsung tanpa harus menunggu server Back-End selesai dibuat. Mock tidak menyimpan data ke database dan tidak memiliki sistem autentikasi nyata.
- **API Nyata**: Merupakan antarmuka pemrograman sebenarnya di sisi Back-End yang memproses logika bisnis nyata, memvalidasi format input dari user, melakukan hashing password, memverifikasi token Sanctum, serta menyimpan dan memperbarui data secara permanen di database. API nyata juga menjamin keamanan seperti **isolasi data** (User A tidak bisa melihat atau mengedit tugas User B).

### 2. Perbedaan kontrak apa yang kalian temukan, dan bagaimana menyelesaikannya?
- **Temuan**: Pada tahap awal, terdapat perbedaan ekspektasi field status tugas antara format boolean (`is_done: true/false`) dan format string (`status: 'todo' / 'done'`), serta penamaan field tanggal (`deadline` vs `due_date`). Selain itu, format response error validasi perlu disepakati agar FE dapat menampilkan pesan error di bawah input field yang sesuai.
- **Penyelesaian**: Kedua pihak mengacu pada dokumen `docs/kontrak-api.md` yang disepakati sejak awal. Kami menetapkan field `status` menggunakan enum string `['todo', 'done']` dan tanggal menggunakan field `deadline` dengan format `YYYY-MM-DD`. Semua respon dibungkus dalam format standar `{ "message": "...", "data": ... }` sehingga FE dapat membuat fungsi fetch terpusat tanpa mengalami error parsing data.
