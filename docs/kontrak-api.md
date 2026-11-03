# Kontrak API: Aplikasi To-Do List (LKPD Duo Developer)

Dokumen ini merupakan kontrak API resmi yang telah disepakati bersama antara pengembang **Back-End (BE)** dan **Front-End (FE)** sebagai acuan kerja paralel.

---

## 1. Aturan Umum (General Rules)

- **Base URL**: `http://localhost:8000`
- **Prefix API**: `/api`
- **Dokumentasi Interaktif (Swagger UI)**: `http://localhost:8000/api/documentation`
- **Spesifikasi OpenAPI JSON**: Tersedia di file [docs/openapi.json](openapi.json)
- **Header Wajib Request**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Header Khusus Endpoint Terproteksi**:
  ```http
  Authorization: Bearer {token_dari_login}
  ```

### Standar Format Response JSON

#### Respon Sukses (200 OK / 201 Created)
```json
{
  "message": "Pesan keberhasilan operasi",
  "data": { ... } atau [ ... ]
}
```

#### Respon Error Validasi (422 Unprocessable Content)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Pesan error spesifik untuk field ini"
    ]
  }
}
```

#### Respon Error Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

#### Respon Error Hak Akses / Bukan Milikmu (403 Forbidden)
```json
{
  "message": "Bukan tugas milikmu"
}
```

---

## 2. Ringkasan Endpoint

| No | Modul | Method | Endpoint | Autentikasi | Deskripsi Singkat |
|:---|:---|:---:|:---|:---:|:---|
| 1 | Auth | `POST` | `/api/register` | Publik | Registrasi akun baru |
| 2 | Auth | `POST` | `/api/login` | Publik | Login & mendapatkan token Sanctum |
| 3 | Auth | `GET` | `/api/profile` | Bearer Token | Mengambil data akun yang sedang login |
| 4 | Auth | `POST` | `/api/logout` | Bearer Token | Logout & menghapus token sesi |
| 5 | Tugas | `GET` | `/api/tasks` | Bearer Token | Mengambil semua tugas milik user login |
| 6 | Tugas | `POST` | `/api/tasks` | Bearer Token | Membuat tugas baru (default status: `todo`) |
| 7 | Tugas | `GET` | `/api/tasks/{id}` | Bearer Token | Mengambil detail 1 tugas berdasarkan ID |
| 8 | Tugas | `PUT` | `/api/tasks/{id}` | Bearer Token | Mengubah judul, tenggat, atau status tugas |
| 9 | Tugas | `PATCH` | `/api/tasks/{id}/status` | Bearer Token | Mengubah status tugas (`todo` / `done`) |
| 10 | Tugas | `DELETE` | `/api/tasks/{id}` | Bearer Token | Menghapus tugas |

---

## 3. Rincian Spesifikasi Endpoint

### A. Autentikasi

#### 1. Register Akun Baru
- **Method**: `POST`
- **URL**: `/api/register`
- **Headers**: `Accept: application/json`, `Content-Type: application/json`
- **Request Body**:
  | Field | Tipe Data | Wajib? | Aturan & Keterangan |
  |:---|:---|:---:|:---|
  | `name` | string | Ya | Nama lengkap user (maks 255 karakter) |
  | `email` | string (email) | Ya | Email unik pengguna |
  | `password` | string | Ya | Minimal 8 karakter |
  | `password_confirmation` | string | Ya | Harus sama dengan field `password` |

- **Contoh Request Body**:
  ```json
  {
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
- **Contoh Respon Sukses (Status: 201 Created)**:
  ```json
  {
    "message": "Register berhasil",
    "data": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "created_at": "2026-11-03T00:00:00.000000Z",
      "updated_at": "2026-11-03T00:00:00.000000Z"
    }
  }
  ```

---

#### 2. Login Akun
- **Method**: `POST`
- **URL**: `/api/login`
- **Request Body**:
  | Field | Tipe Data | Wajib? | Keterangan |
  |:---|:---|:---:|:---|
  | `email` | string (email) | Ya | Email terdaftar |
  | `password` | string | Ya | Password akun |

- **Contoh Request Body**:
  ```json
  {
    "email": "budi@example.com",
    "password": "password123"
  }
  ```
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Login berhasil",
    "data": {
      "token": "1|2abcde345fghij678klmnop...",
      "user": {
        "id": 1,
        "name": "Budi Santoso",
        "email": "budi@example.com"
      }
    }
  }
  ```
- **Contoh Respon Gagal (Status: 401 Unauthorized)**:
  ```json
  {
    "message": "Email atau password salah"
  }
  ```

---

#### 3. Profil Pengguna
- **Method**: `GET`
- **URL**: `/api/profile`
- **Headers**: `Authorization: Bearer {token}`
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Data profil berhasil diambil",
    "data": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "created_at": "2026-11-03T00:00:00.000000Z",
      "updated_at": "2026-11-03T00:00:00.000000Z"
    }
  }
  ```

---

#### 4. Logout
- **Method**: `POST`
- **URL**: `/api/logout`
- **Headers**: `Authorization: Bearer {token}`
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Logout berhasil"
  }
  ```

---

### B. Modul Tugas (To-Do List)

#### 5. Ambil Daftar Tugas Milik Sendiri
- **Method**: `GET`
- **URL**: `/api/tasks`
- **Headers**: `Authorization: Bearer {token}`
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Daftar tugas berhasil diambil",
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "title": "Mengerjakan LKPD Duo Developer",
        "deadline": "2026-11-05",
        "status": "todo",
        "created_at": "2026-11-03T00:00:00.000000Z",
        "updated_at": "2026-11-03T00:00:00.000000Z"
      },
      {
        "id": 2,
        "user_id": 1,
        "title": "Setup Mock JSON di Frontend",
        "deadline": "2026-11-04",
        "status": "done",
        "created_at": "2026-11-03T00:00:00.000000Z",
        "updated_at": "2026-11-03T00:00:00.000000Z"
      }
    ]
  }
  ```

---

#### 6. Tambah Tugas Baru
- **Method**: `POST`
- **URL**: `/api/tasks`
- **Headers**: `Authorization: Bearer {token}`
- **Request Body**:
  | Field | Tipe Data | Wajib? | Keterangan |
  |:---|:---|:---:|:---|
  | `title` | string | Ya | Judul tugas (maks 255 karakter) |
  | `deadline` | string (date: YYYY-MM-DD) | Tidak | Tenggat tugas, misal `2026-11-10` |

- **Contoh Request Body**:
  ```json
  {
    "title": "Membuat slide presentasi tugas",
    "deadline": "2026-11-08"
  }
  ```
- **Contoh Respon Sukses (Status: 201 Created)**:
  ```json
  {
    "message": "Tugas berhasil dibuat",
    "data": {
      "id": 3,
      "user_id": 1,
      "title": "Membuat slide presentasi tugas",
      "deadline": "2026-11-08",
      "status": "todo",
      "created_at": "2026-11-03T00:00:00.000000Z",
      "updated_at": "2026-11-03T00:00:00.000000Z"
    }
  }
  ```

---

#### 7. Detail Tugas
- **Method**: `GET`
- **URL**: `/api/tasks/{id}`
- **Headers**: `Authorization: Bearer {token}`
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Detail tugas berhasil diambil",
    "data": {
      "id": 1,
      "user_id": 1,
      "title": "Mengerjakan LKPD Duo Developer",
      "deadline": "2026-11-05",
      "status": "todo",
      "created_at": "2026-11-03T00:00:00.000000Z",
      "updated_at": "2026-11-03T00:00:00.000000Z"
    }
  }
  ```

---

#### 8. Edit Judul / Tenggat / Status Tugas
- **Method**: `PUT`
- **URL**: `/api/tasks/{id}`
- **Headers**: `Authorization: Bearer {token}`
- **Request Body**:
  | Field | Tipe Data | Wajib? | Keterangan |
  |:---|:---|:---:|:---|
  | `title` | string | Opsional | Judul baru tugas |
  | `deadline` | string (date) | Opsional | Tenggat baru |
  | `status` | string (`todo` / `done`) | Opsional | Status tugas |

- **Contoh Request Body**:
  ```json
  {
    "title": "Mengerjakan LKPD Duo Developer (Revisi)",
    "deadline": "2026-11-06"
  }
  ```
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Tugas berhasil diperbarui",
    "data": {
      "id": 1,
      "user_id": 1,
      "title": "Mengerjakan LKPD Duo Developer (Revisi)",
      "deadline": "2026-11-06",
      "status": "todo",
      "created_at": "2026-11-03T00:00:00.000000Z",
      "updated_at": "2026-11-03T01:00:00.000000Z"
    }
  }
  ```

---

#### 9. Ubah Cepat Status Tugas (Todo / Done)
- **Method**: `PATCH`
- **URL**: `/api/tasks/{id}/status`
- **Headers**: `Authorization: Bearer {token}`
- **Request Body**:
  | Field | Tipe Data | Wajib? | Keterangan |
  |:---|:---|:---:|:---|
  | `status` | string | Ya | Harus bernilai `"todo"` atau `"done"` |

- **Contoh Request Body**:
  ```json
  {
    "status": "done"
  }
  ```
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Status tugas berhasil diperbarui",
    "data": {
      "id": 1,
      "user_id": 1,
      "title": "Mengerjakan LKPD Duo Developer (Revisi)",
      "deadline": "2026-11-06",
      "status": "done",
      "created_at": "2026-11-03T00:00:00.000000Z",
      "updated_at": "2026-11-03T01:30:00.000000Z"
    }
  }
  ```

---

#### 10. Hapus Tugas
- **Method**: `DELETE`
- **URL**: `/api/tasks/{id}`
- **Headers**: `Authorization: Bearer {token}`
- **Contoh Respon Sukses (Status: 200 OK)**:
  ```json
  {
    "message": "Tugas berhasil dihapus"
  }
  ```

---

## 4. Panduan untuk Front-End (Mock Data Siap Pakai)

Agar kamu (Front-End) bisa langsung mengerjakan tampilan Blade & JavaScript Fetch tanpa menunggu backend menyala, kamu dapat menggunakan objek mock data JavaScript terpusat seperti contoh di bawah ini:

```javascript
// Contoh mock API service untuk Front-End (bisa disimpan di resources/js/api.js atau script blade)
const USE_MOCK = true; // Ganti false jika API Back-End sudah siap dihubungkan

const mockTasks = [
  { id: 1, user_id: 1, title: "Belajar membuat Mock Data FE", deadline: "2026-11-04", status: "done" },
  { id: 2, user_id: 1, title: "Integrasikan fetch() ke API Laravel", deadline: "2026-11-05", status: "todo" }
];

export async function fetchTasks() {
  if (USE_MOCK) {
    return { message: "Daftar tugas berhasil diambil", data: mockTasks };
  }
  const token = localStorage.getItem('token');
  const res = await fetch('/api/tasks', {
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
  });
  return await res.json();
}
```
