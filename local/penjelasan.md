# Dokumentasi Teknis Sistem PRM RKZ
## Paket Rehabilitasi Medis — RS Katolik RKZ Surabaya

---

## 1. Gambaran Umum Sistem

Sistem **PRM RKZ** (Paket Rehabilitasi Medis — Rumah Sakit Katolik RKZ Surabaya) adalah aplikasi web untuk mengelola siklus hidup paket terapi rehabilitasi medis pasien, mulai dari **pembelian paket** oleh kasir, **pemotongan sesi** oleh petugas pelayanan, hingga **pencatatan riwayat** penggunaan sesi.

### Fitur Utama

| No | Modul | Fungsi | Status |
|----|-------|--------|--------|
| 1 | **Login & Autentikasi** | Login multi-role dengan JWT token | ✅ Aktif |
| 2 | **Pelayanan (ERM)** | Cari pasien, lihat paket aktif, potong sesi, lihat riwayat | ✅ Aktif |
| 3 | **Kasir / Pembelian** | Beli paket baru untuk pasien berdasarkan No. ERM | ✅ Aktif |
| 4 | **Master Data** | Kelola data master (paket, tindakan) | 🔲 Placeholder |
| 5 | **Laporan & Audit** | Laporan rekapitulasi dan audit trail | 🔲 Placeholder |

### Tech Stack

| Layer | Teknologi | Versi/Catatan |
|-------|-----------|---------------|
| **Frontend** | HTML5 + Vanilla JavaScript | Single Page Application (SPA) |
| **CSS Framework** | Tailwind CSS (via CDN) | Dengan konfigurasi design token Material 3 |
| **Font** | Plus Jakarta Sans + Material Symbols | Google Fonts CDN |
| **Backend API** | PHP (Native, tanpa framework) | Kompatibel PHP 5.4+ |
| **Database** | MySQL 5.7 | Engine MyISAM |
| **Web Server** | PHP Built-in Development Server | `php -S localhost:8002` |
| **Autentikasi** | JWT (JSON Web Token) | Implementasi custom tanpa library |

---

## 2. Arsitektur MVC

Sistem ini menggunakan pola arsitektur **MVC (Model-View-Controller)** yang diimplementasikan secara manual tanpa framework PHP.

### Diagram Arsitektur

```
┌─────────────────────────────────────────────────────────────────────┐
│                        BROWSER (CLIENT)                             │
│  ┌───────────────────┐    ┌──────────────────────────────────────┐  │
│  │   index.html       │    │            app.js                    │  │
│  │   (View Layer)     │◄──►│   (Client-Side Controller)           │  │
│  │   Tailwind CSS     │    │   fetch() → API_BASE                 │  │
│  └───────────────────┘    └──────────────┬───────────────────────┘  │
└──────────────────────────────────────────┼──────────────────────────┘
                                           │ HTTP (JSON)
                                           │ Authorization: Bearer <JWT>
                                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     SERVER (PHP Backend)                             │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │              api/index.php  (ROUTER + JWT Middleware)        │    │
│  │  URI Parsing → Endpoint Matching → Action Dispatch          │    │
│  └──────┬──────────┬──────────────┬──────────────┬─────────────┘    │
│         │          │              │              │                   │
│         ▼          ▼              ▼              ▼                   │
│  ┌──────────┐ ┌──────────┐ ┌───────────┐ ┌──────────┐              │
│  │  Auth    │ │  Paket   │ │  Pasien   │ │  Kasir   │  Controllers │
│  │Controller│ │Controller│ │ Controller│ │Controller│              │
│  └────┬─────┘ └────┬─────┘ └─────┬─────┘ └────┬─────┘              │
│       │            │             │             │                    │
│       ▼            ▼             ▼             ▼                    │
│  ┌──────┐    ┌──────┐    ┌──────────┐    ┌──────────┐   Models     │
│  │ User │    │ Paket│    │Kapasitas │    │Kapasitas │              │
│  └──────┘    └──────┘    │Catatan   │    │Paket     │              │
│                          └──────────┘    └──────────┘              │
│                                                                     │
│  ┌──────────────────────┐  ┌───────────────────────┐               │
│  │ config/database.php  │  │   helpers/JWT.php      │  Helpers     │
│  │ (PDO Connection)     │  │   (encode/decode)      │              │
│  └──────────┬───────────┘  └───────────────────────┘               │
└─────────────┼───────────────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     MySQL Database: prm_rkz                         │
│                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────────┐         │
│  │ prm_users    │  │prm_kapasitas │  │  prm_catatan      │         │
│  └──────────────┘  └──────┬───────┘  └───────────────────┘         │
│                           │ FK                                      │
│  ┌──────────────────┐     │          ┌───────────────────┐         │
│  │ prm_master_paket │◄────┘          │prm_master_tindakan│         │
│  └──────────────────┘                └───────────────────┘         │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.1 Layer Model (`api/models/`)

Model bertanggung jawab untuk **interaksi langsung dengan database** melalui PDO (PHP Data Objects). Setiap model merepresentasikan satu tabel database.

| File | Tabel | Fungsi Utama |
|------|-------|-------------|
| [User.php](file:///c:/Users/louis/Documents/prm_rkz/api/models/User.php) | `prm_users` | `login($username, $password)` — Autentikasi user dengan MD5 hash comparison |
| [Paket.php](file:///c:/Users/louis/Documents/prm_rkz/api/models/Paket.php) | `prm_master_paket` | `read()` — Ambil semua master paket; `readOne()` — Ambil 1 paket berdasarkan ID |
| [Kapasitas.php](file:///c:/Users/louis/Documents/prm_rkz/api/models/Kapasitas.php) | `prm_kapasitas` | `create()` — Insert kapasitas baru (beli paket); `getActiveByErm($no_erm)` — Ambil paket aktif pasien; `updateSisa()` — Kurangi sisa sesi |
| [Catatan.php](file:///c:/Users/louis/Documents/prm_rkz/api/models/Catatan.php) | `prm_catatan` | `create()` — Simpan log penggunaan sesi; `getHistoryByErm($no_erm)` — Ambil riwayat sesi pasien |
| [Tindakan.php](file:///c:/Users/louis/Documents/prm_rkz/api/models/Tindakan.php) | `prm_master_tindakan` | `read()` — Ambil daftar master tindakan medis |

**Pola umum pada setiap Model:**
```php
class NamaModel {
    private $conn;                        // Koneksi PDO
    private $table_name = "nama_tabel";   // Nama tabel MySQL

    public $properti1;                    // Mapping ke kolom tabel
    public $properti2;

    public function __construct($db) {
        $this->conn = $db;                // Terima koneksi dari Controller
    }

    function create() {
        // Prepared statement INSERT dengan bindParam
    }

    function read() {
        // Prepared statement SELECT
    }
}
```

**Catatan keamanan:** Semua input di-sanitize dengan `htmlspecialchars(strip_tags(...))` sebelum bind, dan seluruh query menggunakan **prepared statements** untuk mencegah SQL injection.

### 2.2 Layer Controller (`api/controllers/`)

Controller bertugas menerima request yang diteruskan oleh router, memvalidasi input, memanggil Model yang tepat, dan mengembalikan response JSON.

| File | Endpoint | Method & Action | Deskripsi |
|------|----------|----------------|-----------|
| [AuthController.php](file:///c:/Users/louis/Documents/prm_rkz/api/controllers/AuthController.php) | `/auth` | `POST ?action=login` | Menerima `username` + `password`, memvalidasi via `User->login()`, mengembalikan JWT token |
| [PaketController.php](file:///c:/Users/louis/Documents/prm_rkz/api/controllers/PaketController.php) | `/paket` | `GET` | Mengembalikan seluruh daftar master paket dari `Paket->read()` |
| [PasienController.php](file:///c:/Users/louis/Documents/prm_rkz/api/controllers/PasienController.php) | `/pasien` | `GET ?action=kapasitas_aktif&no_erm=X` | Mengambil paket aktif pasien dari `Kapasitas->getActiveByErm()` |
| | | `GET ?action=riwayat_sesi&no_erm=X` | Mengambil riwayat sesi dari `Catatan->getHistoryByErm()` |
| | | `POST ?action=gunakan_sesi` | Memotong 1 sesi: insert `Catatan->create()` + update `Kapasitas->updateSisa()` dalam **transaksi database** |
| [KasirController.php](file:///c:/Users/louis/Documents/prm_rkz/api/controllers/KasirController.php) | `/kasir` | `POST ?action=beli_paket` | Membeli paket baru: validasi `Paket->readOne()`, lalu `Kapasitas->create()` dengan kalkulasi tanggal expired |

**Pola umum pada setiap Controller:**
```php
class NamaController {
    private $db;
    private $model;

    public function __construct() {
        include_once 'config/database.php';
        include_once 'models/NamaModel.php';

        $database = new Database();
        $this->db = $database->getConnection();   // Buka koneksi
        $this->model = new NamaModel($this->db);   // Inject ke model
    }

    public function aksi() {
        $data = json_decode(file_get_contents("php://input"));  // Baca JSON body
        // ... validasi, proses, dan kirim response JSON
        http_response_code(200);
        echo json_encode(array("message" => "Sukses"));
    }
}
```

### 2.3 Layer View (`index.html` + `app.js`)

Karena ini adalah **SPA (Single Page Application)**, layer View sepenuhnya di sisi klien:

- **`index.html`** — Struktur DOM lengkap untuk seluruh halaman (login, sidebar, 4 view modul, dan modal).
- **`app.js`** — Seluruh logika klien: autentikasi, navigasi, fetch API, render data, dan event handling.

> **Catatan:** Tidak ada server-side rendering. PHP hanya mengembalikan data JSON; seluruh rendering HTML dilakukan oleh JavaScript di browser.

---

## 3. API — Daftar Lengkap Endpoint

Semua endpoint tersedia melalui satu entry point: **`/api/index.php/{endpoint}?action={action}`**

### 3.1 Router (`api/index.php`)

File [api/index.php](file:///c:/Users/louis/Documents/prm_rkz/api/index.php) berfungsi sebagai:

1. **CORS Handler** — Mengizinkan semua origin (`Access-Control-Allow-Origin: *`)
2. **URI Parser** — Memecah URL untuk menemukan `endpoint` dan `action`
3. **JWT Middleware** — Memvalidasi token Bearer di setiap request (kecuali `/auth`)
4. **Dispatcher** — Mengarahkan request ke Controller yang tepat berdasarkan endpoint dan action

**Alur routing:**
```
REQUEST: POST /api/index.php/kasir?action=beli_paket
         Authorization: Bearer eyJ...

          ┌───────────────────────────────────┐
          │ 1. Parse URI → endpoint = "kasir" │
          │ 2. Parse GET → action = "beli..."  │
          │ 3. Validasi JWT → OK (admin)       │
          │ 4. Cek role → admin ∈ [admin,kasir]│
          │ 5. Dispatch → KasirController      │
          │    → beliPaket()                   │
          └───────────────────────────────────┘
```

### 3.2 Tabel Endpoint

| # | Method | URL | Auth | Role | Deskripsi | Request Body | Response |
|---|--------|-----|------|------|-----------|-------------|----------|
| 1 | `POST` | `/auth?action=login` | ❌ | Semua | Login | `{"username":"admin","password":"rkz123"}` | `{"token":"eyJ...","user":{...}}` |
| 2 | `GET` | `/paket` | ✅ | Semua | Daftar master paket | — | `{"records":[{"id":1,"nama":"...","total_sesi":10,...}]}` |
| 3 | `GET` | `/pasien?action=kapasitas_aktif&no_erm=ERM001` | ✅ | Semua | Paket aktif pasien | — | `{"records":[{"id":1,"sisa":8,"nama_paket":"..."}]}` |
| 4 | `GET` | `/pasien?action=riwayat_sesi&no_erm=ERM001` | ✅ | Semua | Riwayat sesi | — | `{"records":[{"tanggal_paket":"...","sesi_ke":1}]}` |
| 5 | `POST` | `/pasien?action=gunakan_sesi` | ✅ | admin, erm, kasir | Potong 1 sesi | `{"id_kapasitas":1,"no_erm":"ERM001","no_register_kunjungan":"REG-001","tanggal_paket":"2026-08-07 08:00:00","sesi_ke":1,"sisa_saat_ini":5}` | `{"message":"Sesi berhasil digunakan."}` |
| 6 | `POST` | `/kasir?action=beli_paket` | ✅ | admin, kasir | Beli paket baru | `{"no_erm":"ERM001","id_paket":1}` | `{"message":"Paket berhasil dibeli dan aktif."}` |

### 3.3 Autentikasi JWT

Implementasi JWT di file [JWT.php](file:///c:/Users/louis/Documents/prm_rkz/api/helpers/JWT.php):

- **Algoritma:** HMAC-SHA256
- **Secret Key:** `rkz_hospital_secret_key_2026` (hardcoded)
- **Masa Berlaku:** 24 jam (`exp = time() + 86400`)
- **Format Token:** `header.payload.signature` (base64url-encoded)

**Alur autentikasi:**
```
1. Client POST /auth?action=login  →  Server validasi MD5(password)
2. Server generate JWT  →  Client simpan di localStorage ('prm_token')
3. Setiap request berikutnya:
   Client kirim header "Authorization: Bearer <token>"
   →  Server decode & validasi signature + expiration
   →  Jika valid, lanjut ke Controller
   →  Jika tidak, return 401 Unauthorized
```

### 3.4 Role-Based Access Control (RBAC)

| Role | Deskripsi | Akses Modul |
|------|-----------|-------------|
| `admin` | Administrator | Semua modul |
| `kasir` | Kasir / Pendaftaran | Kasir, Pelayanan (ERM) |
| `erm` | Perawat Rehab Medis | Pelayanan (ERM) saja |
| `manajemen` | Manajer Operasional | Laporan & Audit |

Pengecekan role dilakukan di dua tempat:
1. **Server-side** — di `api/index.php` sebelum memanggil Controller (`in_array($userPayload['role'], [...])`)
2. **Client-side** — di `app.js` fungsi `setupRoleAccess()` untuk menyembunyikan/menampilkan menu sidebar

---

## 4. Skema Database

Database: **`prm_rkz`** pada MySQL 5.7

### 4.1 Entity Relationship Diagram (ERD)

```
┌───────────────────┐       ┌───────────────────────┐       ┌───────────────────────┐
│   prm_users       │       │  prm_master_paket     │       │ prm_master_tindakan   │
├───────────────────┤       ├───────────────────────┤       ├───────────────────────┤
│ PK id             │       │ PK id                 │       │ PK id                 │
│    username       │       │    nama               │       │    kode_tindakan      │
│    password_hash  │       │    tipe_paket (ENUM)  │       │    nama_tindakan      │
│    nama_lengkap   │       │    total_sesi         │       └───────────┬───────────┘
│    role (ENUM)    │       │    masa_berlaku_hari   │                   │
└───────────────────┘       └──────────┬────────────┘                   │
                                       │ 1                              │
                                       │                                │
                                       │ N                              │
                            ┌──────────┴────────────┐                   │
                            │   prm_kapasitas       │                   │
                            ├───────────────────────┤                   │
                            │ PK id                 │                   │
                            │    no_erm             │                   │
                            │    nomor_register     │                   │
                            │ FK id_paket ──────────┘                   │
                            │    sisa                                   │
                            │    tanggal_beli                           │
                            │    tanggal_expired                        │
                            │    status (ENUM)       1                  │
                            └──────────┬────────────┘                   │
                                       │                                │
                                       │ N                              │
                            ┌──────────┴────────────┐                   │
                            │   prm_catatan         │                   │
                            ├───────────────────────┤                   │
                            │ PK id                 │                   │
                            │ FK id_kapasitas ──────┘                   │
                            │ FK id_tindakan ───────────────────────────┘
                            │    no_erm             │
                            │    no_register_kunj.  │
                            │    tanggal_paket      │
                            │    sesi_ke            │
                            └───────────────────────┘
```

### 4.2 Detail Tabel

#### `prm_users` — Tabel Pengguna Sistem
| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|------------|
| `id` | INT(11) | PK, AUTO_INCREMENT | ID unik |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | Username login |
| `password_hash` | VARCHAR(255) | NOT NULL | Hash MD5 dari password |
| `nama_lengkap` | VARCHAR(100) | NOT NULL | Nama tampilan |
| `role` | ENUM('admin','kasir','manajemen','erm') | NOT NULL | Hak akses |

#### `prm_master_paket` — Master Data Paket Terapi
| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|------------|
| `id` | INT(11) | PK, AUTO_INCREMENT | ID unik |
| `nama` | VARCHAR(255) | NOT NULL | Nama paket (misal: "Paket Fisioterapi Intensif 10x") |
| `tipe_paket` | ENUM('SINGLE','MULTI') | DEFAULT 'SINGLE' | SINGLE = 1 sesi; MULTI = banyak sesi |
| `total_sesi` | INT(11) | NOT NULL | Jumlah total sesi dalam paket |
| `masa_berlaku_hari` | INT(11) | DEFAULT 30 | Masa berlaku paket dalam hari |

#### `prm_kapasitas` — Paket yang Dibeli Pasien (Instansi Paket)
| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|------------|
| `id` | INT(11) | PK, AUTO_INCREMENT | ID unik |
| `no_erm` | VARCHAR(50) | NOT NULL | Nomor Rekam Medis Elektronik pasien |
| `nomor_register` | VARCHAR(50) | NOT NULL | Nomor register pembelian (auto-generated) |
| `id_paket` | INT(11) | FK → `prm_master_paket.id` | Referensi ke master paket |
| `sisa` | INT(11) | NOT NULL | Jumlah sesi yang tersisa |
| `tanggal_beli` | DATETIME | NOT NULL | Kapan paket dibeli |
| `tanggal_expired` | DATETIME | NOT NULL | Kapan paket kedaluwarsa |
| `status` | ENUM('AKTIF','HABIS','EXPIRED') | DEFAULT 'AKTIF' | Status paket saat ini |

#### `prm_catatan` — Log Penggunaan Sesi (Audit Trail)
| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|------------|
| `id` | INT(11) | PK, AUTO_INCREMENT | ID unik |
| `id_kapasitas` | INT(11) | FK → `prm_kapasitas.id` | Paket mana yang dipotong |
| `id_tindakan` | INT(11) | FK → `prm_master_tindakan.id`, NULLABLE | Jenis tindakan medis |
| `no_erm` | VARCHAR(50) | NOT NULL | Nomor ERM pasien |
| `no_register_kunjungan` | VARCHAR(50) | NOT NULL | Nomor register kunjungan/pendaftaran |
| `tanggal_paket` | DATETIME | NOT NULL | Waktu sesi digunakan |
| `sesi_ke` | INT(11) | NOT NULL | Sesi keberapa (misal: sesi ke-3 dari 10) |

#### `prm_master_tindakan` — Master Data Jenis Tindakan Medis
| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|------------|
| `id` | INT(11) | PK, AUTO_INCREMENT | ID unik |
| `kode_tindakan` | VARCHAR(20) | NOT NULL | Kode internal (misal: "FT-001") |
| `nama_tindakan` | VARCHAR(100) | NOT NULL | Nama tindakan (misal: "Fisioterapi Manual") |

---

## 5. Frontend — UI Layer

### 5.1 Struktur File

| File | Peran | Ukuran |
|------|-------|--------|
| [index.html](file:///c:/Users/louis/Documents/prm_rkz/index.html) | Seluruh markup HTML (View) | ~22 KB, 336 baris |
| [app.js](file:///c:/Users/louis/Documents/prm_rkz/app.js) | Seluruh logika JavaScript (Client Controller) | ~19 KB, 468 baris |

### 5.2 Desain UI — Material Design 3

UI dibangun menggunakan **Tailwind CSS** dengan konfigurasi design token yang mengadopsi **Material Design 3 (M3)** color system:

```javascript
// Contoh design token pada tailwind.config
colors: {
    "primary": "#005196",              // Warna utama (biru RKZ)
    "on-primary": "#ffffff",           // Teks di atas primary
    "primary-container": "#0069c0",    // Container primary (lebih gelap)
    "surface": "#f6faff",              // Background utama
    "on-surface": "#171c20",           // Teks utama
    "error": "#ba1a1a",                // Warna error
    "outline-variant": "#c1c6d4",      // Border/garis pemisah
    // ... dan seterusnya
}
```

### 5.3 Struktur Halaman (Views)

Aplikasi adalah **SPA dengan client-side routing** — semua halaman ada di dalam satu file `index.html`, ditampilkan/disembunyikan menggunakan CSS class `hidden`:

```
index.html
├── #view-login              → Halaman login (overlay z-100)
├── #app-layout              → Layout utama (hidden saat belum login)
│   ├── <nav>                → Sidebar navigasi (fixed left, 240px)
│   │   ├── Brand (PRM RKZ)
│   │   ├── #menu-erm        → Link ke Pelayanan
│   │   ├── #menu-kasir      → Link ke Kasir
│   │   ├── #menu-master     → Link ke Master Data
│   │   ├── #menu-audit      → Link ke Laporan
│   │   ├── User Info        → Nama + Role
│   │   └── #btnLogout       → Tombol logout
│   │
│   └── Main Content (ml-240px)
│       ├── #view-erm        → Pelayanan Rehabilitasi Medis
│       │   ├── Search Form  → Input No. ERM + Tombol Cari
│       │   ├── #resultSection (2 kolom)
│       │   │   ├── Kolom Kiri: #cardsContainer  → Kartu paket aktif
│       │   │   └── Kolom Kanan: #historyContainer → Tabel riwayat sesi
│       │   └── #messageContainer → Pesan error/info
│       │
│       ├── #view-kasir      → Kasir / Pembelian Paket
│       │   ├── #kasirForm   → Form (No. ERM + Dropdown Paket)
│       │   ├── #kasirMessage → Pesan sukses/gagal
│       │   └── #btnBeliPaket → Tombol Beli
│       │
│       ├── #view-master     → Master Data (placeholder)
│       └── #view-audit      → Laporan & Audit (placeholder)
│
└── #useSessionModal         → Modal konfirmasi potong sesi (overlay z-200)
    ├── #modalPaketName      → Nama paket yang akan dipotong
    ├── #modalSisaSesiBaru   → Preview sisa sesi setelah dipotong
    ├── #modalNoKunjungan    → Input nomor register kunjungan
    └── #confirmUseBtn       → Tombol konfirmasi
```

### 5.4 Alur Logika di `app.js`

**1. Inisialisasi (`DOMContentLoaded`)**
```
checkAuth()
  ├── Ada token di localStorage? → showApp() → setupRoleAccess() → switchView(default)
  └── Tidak ada token?           → showLogin()
```

**2. Login Flow**
```
User submit form login
  → fetch POST /auth?action=login
  → Sukses: simpan token + user ke localStorage → checkAuth() → showApp()
  → Gagal: tampilkan pesan error
```

**3. Pencarian Pasien (ERM)**
```
User submit #searchForm
  → fetchKapasitas(noErm)
    → apiFetch GET /pasien?action=kapasitas_aktif&no_erm=X
    → Sukses: displayKapasitas() → render kartu paket
             fetchRiwayatSesi() → render tabel riwayat
    → Gagal: showMessage("Tidak ada paket aktif")
```

**4. Penggunaan Sesi (Potong Sesi)**
```
User klik "Gunakan 1 Sesi" pada kartu paket
  → openModal(record) → Tampilkan modal konfirmasi
  → User isi No. Register → Klik "Gunakan Sesi"
  → apiFetch POST /pasien?action=gunakan_sesi
    → Sukses: tutup modal, showMessage("Sukses"), refresh data (trigger #btnSearch.click())
    → Gagal: alert("Gagal memotong sesi")
```

**5. Pembelian Paket (Kasir)**
```
showApp() dipanggil
  → loadMasterPaket() → apiFetch GET /paket → isi dropdown #kasirPaket

User submit #kasirForm
  → apiFetch POST /kasir?action=beli_paket
  → Sukses: tampilkan pesan hijau, reset form
  → Gagal: tampilkan pesan merah
```

**6. Fungsi Helper: `apiFetch()`**
```javascript
function apiFetch(url, options = {}) {
    options.headers['Authorization'] = `Bearer ${currentToken}`;
    return fetch(url, options).then(res => {
        if(res.status === 401) {
            btnLogout.click();       // Auto-logout jika token expired
            throw new Error('Session Expired');
        }
        return res;
    });
}
```

Fungsi ini membungkus `fetch()` bawaan browser dengan **otomatis menyertakan JWT token** di setiap request dan **auto-logout** jika server mengembalikan 401.

---

## 6. Struktur Direktori Lengkap

```
prm_rkz/
├── index.html                  ← View: Seluruh UI (SPA)
├── app.js                      ← Client Controller: Logika frontend
│
├── api/                        ← Backend API
│   ├── index.php               ← Router + JWT Middleware
│   ├── config/
│   │   └── database.php        ← Konfigurasi koneksi PDO MySQL
│   ├── controllers/
│   │   ├── AuthController.php  ← Login + JWT generation
│   │   ├── PaketController.php ← CRUD Master Paket
│   │   ├── PasienController.php← Kapasitas + Riwayat + Gunakan Sesi
│   │   └── KasirController.php ← Pembelian paket baru
│   ├── models/
│   │   ├── User.php            ← Model tabel prm_users
│   │   ├── Paket.php           ← Model tabel prm_master_paket
│   │   ├── Kapasitas.php       ← Model tabel prm_kapasitas
│   │   ├── Catatan.php         ← Model tabel prm_catatan
│   │   └── Tindakan.php        ← Model tabel prm_master_tindakan
│   ├── helpers/
│   │   └── JWT.php             ← Implementasi JWT encode/decode
│   └── test.http               ← File pengujian HTTP request
│
├── db/                         ← SQL Scripts
│   ├── prm_rkz.sql             ← DDL utama (CREATE TABLE)
│   ├── db_updates.sql          ← Tabel users + seed data
│   └── dummy_data.sql          ← Data dummy untuk testing
│
├── img/                        ← Aset gambar (jika ada)
└── stitch UI/                  ← Referensi desain dari Stitch AI
```

---

## 7. Cara Menjalankan

### Prasyarat
- PHP 5.4+ (dengan ekstensi PDO MySQL)
- MySQL 5.7+
- Browser modern (Chrome, Firefox, Edge)

### Langkah
```bash
# 1. Import database
mysql -u root -p prm_rkz < db/prm_rkz.sql
mysql -u root -p prm_rkz < db/db_updates.sql
mysql -u root -p prm_rkz < db/dummy_data.sql

# 2. Sesuaikan kredensial database di api/config/database.php
#    DB_USER = 'root', DB_PASS = '123456789'

# 3. Jalankan PHP built-in server
php -S localhost:8002

# 4. Buka browser → http://localhost:8002
#    Login: admin / rkz123
```

### Akun Default

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| `admin` | `rkz123` | admin | Semua modul |
| `kasir1` | `rkz123` | kasir | Kasir + ERM |
| `perawat1` | `rkz123` | erm | Pelayanan (ERM) saja |
| `manajemen1` | `rkz123` | manajemen | Laporan & Audit |

---

## 8. Diagram Alur Bisnis

```
                    ┌─────────────────────┐
                    │  Pasien Datang ke RS │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │  KASIR: Beli Paket  │ ← Modul Kasir
                    │  (Pilih paket +     │
                    │   input No. ERM)    │
                    └──────────┬──────────┘
                               │
                         Paket tercatat di
                         tabel prm_kapasitas
                         (status = AKTIF)
                               │
                               ▼
            ┌──────────────────────────────────────┐
            │  PERAWAT: Pelayanan / Pemotongan Sesi│ ← Modul ERM
            │  1. Cari pasien via No. ERM          │
            │  2. Lihat paket aktif                │
            │  3. Klik "Gunakan 1 Sesi"            │
            │  4. Input No. Register Kunjungan     │
            │  5. Konfirmasi                       │
            └──────────────────┬───────────────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
                    ▼                     ▼
           prm_kapasitas.sisa    prm_catatan (INSERT)
           dikurangi 1           → log audit tersimpan
                    │
                    ▼
            ┌───────────────┐
            │ Sisa = 0?     │
            ├───── Ya ──────┤──► Status = 'HABIS'
            └───── Tidak ───┘──► Status tetap 'AKTIF'
                                  (bisa pakai sesi lagi)
```

---

*Dokumen ini digenerate secara otomatis berdasarkan analisis kode sumber pada tanggal 7 Agustus 2026.*
