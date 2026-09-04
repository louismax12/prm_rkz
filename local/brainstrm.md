# Hasil Brainstorming Sistem Paket Rumah Sakit

## 1. Gambaran Umum Proyek

Sistem yang sedang dikembangkan adalah sistem untuk rumah sakit, khususnya untuk proses pengambilan paket atau sesi terapi oleh pasien.

Contoh alur bisnis:

1. Kasir atau petugas login ke sistem.
2. Petugas melihat data paket atau antrean.
3. Petugas mengambil atau memproses paket untuk pasien yang akan menjalani terapi.
4. Sistem menyimpan transaksi dan menyediakan riwayat pengambilan paket.
5. Petugas dapat melihat riwayat berdasarkan tanggal, misalnya seluruh pengambilan paket pada hari ini.

## 2. Teknologi yang Digunakan

### Backend

- PHP Native
- Versi PHP yang digunakan pada lingkungan pengembangan: PHP 5.4.20
- Pola arsitektur: MVC sederhana buatan sendiri
- REST API menggunakan JavaScript sebagai client/request layer

### Database

- MySQL
- Lingkungan server rumah sakit masih menggunakan teknologi lama.
- Engine tabel yang digunakan pada server produksi diketahui menggunakan MyISAM.

### Frontend

- HTML
- CSS
- Tailwind CSS

### Autentikasi

- JWT (JSON Web Token)

## 3. Kondisi Sistem Saat Ini

Sistem sudah berjalan dengan baik pada:

- Komputer lokal/development
- Server playground atau server uji coba

Namun, ketika sistem dipindahkan atau diuji pada server rumah sakit yang lebih lama, performanya menurun.

Kondisi yang ditemukan:

- Server rumah sakit diperkirakan menggunakan teknologi atau spesifikasi lama, kemungkinan sekitar tahun 2010.
- Dukungan atau konfigurasi performa PHP mungkin terbatas.
- Proses load balance atau traffic terasa lambat.
- Query tertentu membutuhkan sekitar 15 detik.
- Query tersebut mengambil sekitar 14.000 baris data.
- Query menggunakan `INNER JOIN`.
- Query juga menggunakan `GROUP BY`.

Masalah utama yang perlu ditelusuri adalah bottleneck query dan performa database, bukan langsung menyimpulkan bahwa seluruh struktur tabel harus dibuat ulang.

## 4. Masalah Utama

Contoh masalah:

> Ketika petugas login dan membuka fitur pengambilan paket, sistem membutuhkan sekitar 15 detik untuk menampilkan seluruh data. Query mengambil sekitar 14.000 baris.

Kemungkinan penyebab:

- Query mengambil terlalu banyak data sekaligus.
- Index belum sesuai dengan kebutuhan query.
- Kolom pada `JOIN`, `WHERE`, `GROUP BY`, atau `ORDER BY` belum memiliki index yang tepat.
- Query melakukan full table scan.
- Penggunaan `GROUP BY` menyebabkan proses pengurutan atau pengelompokan yang berat.
- Terlalu banyak tabel yang di-join.
- Data yang ditampilkan sebenarnya tidak perlu diambil seluruhnya dalam satu request.
- Spesifikasi server produksi jauh lebih rendah daripada server development.
- Konfigurasi PHP atau MySQL pada server lama belum optimal.
- Tidak ada pagination atau pembatasan jumlah data.
- Tidak ada caching untuk data yang sering diminta.

## 5. Kesimpulan Awal tentang MyISAM dan InnoDB

### Jangan langsung menghapus semua index

Menghapus semua index dan hanya menyisakan primary key bukan solusi yang aman.

Index justru diperlukan untuk mempercepat:

- Pencarian berdasarkan kolom tertentu.
- Kondisi `WHERE`.
- Relasi pada `JOIN`.
- Pengurutan `ORDER BY`.
- Pengelompokan tertentu pada `GROUP BY`.

Jika semua index dihapus, query berpotensi menjadi lebih lambat karena database harus membaca lebih banyak baris.

### Jangan langsung mengganti seluruh tabel menjadi MyISAM

Mengganti engine database bukan langkah pertama yang sebaiknya dilakukan.

MyISAM memiliki beberapa keterbatasan:

- Tidak mendukung transaksi seperti InnoDB.
- Tidak mendukung foreign key secara native.
- Lebih berisiko ketika terjadi crash atau server mati mendadak.
- Kurang ideal untuk sistem rumah sakit yang memiliki banyak user dan transaksi.
- Tidak memberikan jaminan integritas relasi data seperti foreign key pada InnoDB.

Namun, karena server produksi memang menggunakan MyISAM, keputusan akhir harus mempertimbangkan:

- Versi MySQL yang tersedia.
- Dukungan engine pada server.
- Struktur database yang sudah berjalan.
- Kebutuhan transaksi.
- Risiko perubahan pada sistem produksi.
- Kebijakan dan keterbatasan server rumah sakit.

### Rekomendasi

Prioritas utama:

1. Backup database.
2. Analisis query.
3. Jalankan `EXPLAIN`.
4. Periksa index.
5. Terapkan pagination.
6. Kurangi data yang diambil.
7. Evaluasi spesifikasi dan konfigurasi server.
8. Pertimbangkan caching jika memang diperlukan.
9. Baru evaluasi perubahan engine atau struktur tabel jika ada bukti teknis yang kuat.

## 6. Analisis Query dengan EXPLAIN

Gunakan `EXPLAIN` sebelum query untuk melihat bagaimana MySQL mengeksekusi query.

Contoh:

```sql
EXPLAIN
SELECT
    ...
FROM tabel_paket p
INNER JOIN tabel_pasien ps
    ON ps.id = p.pasien_id
WHERE p.tanggal_pengambilan = '2026-09-04'
GROUP BY p.id;
```

Hal yang perlu diperhatikan dari hasil `EXPLAIN`:

- Apakah query menggunakan index?
- Apakah ada `type` bernilai `ALL` yang menunjukkan full table scan?
- Berapa banyak baris yang diperkirakan dibaca?
- Apakah ada `Using temporary`?
- Apakah ada `Using filesort`?
- Apakah index yang digunakan sudah sesuai?
- Apakah urutan join sudah efisien?

Contoh informasi penting:

- `key`: index yang digunakan.
- `rows`: perkiraan jumlah baris yang dibaca.
- `Extra`: informasi tambahan seperti `Using temporary` atau `Using filesort`.

## 7. Index yang Perlu Dievaluasi

Periksa kolom yang digunakan pada:

### `WHERE`

Contoh:

```sql
WHERE tanggal_pengambilan = '2026-09-04'
```

Kolom `tanggal_pengambilan` mungkin membutuhkan index, tergantung pola query dan jumlah data.

### `JOIN`

Contoh:

```sql
INNER JOIN pasien
    ON pasien.id = paket.pasien_id
```

Kolom relasi seperti `pasien_id` perlu dievaluasi agar proses join tidak membaca seluruh tabel.

### `GROUP BY`

Contoh:

```sql
GROUP BY paket.id
```

Periksa apakah pengelompokan tersebut memang diperlukan. Jika query menghasilkan duplikasi karena join, mungkin struktur query dapat diperbaiki tanpa `GROUP BY`.

### `ORDER BY`

Contoh:

```sql
ORDER BY tanggal_pengambilan DESC
```

Index dapat membantu pengurutan pada kondisi tertentu.

### Catatan

Jangan menambahkan index secara sembarangan pada semua kolom. Index juga menambah kebutuhan storage dan dapat memperlambat proses `INSERT`, `UPDATE`, serta `DELETE`.

Index harus ditentukan berdasarkan query yang benar-benar digunakan.

## 8. Pagination

### Pengertian

Pagination adalah teknik mengambil data secara bertahap, bukan mengambil seluruh data sekaligus.

Daripada mengambil 14.000 baris, sistem dapat mengambil:

- 20 baris per halaman
- 50 baris per halaman
- 100 baris per halaman

Contoh:

```sql
SELECT
    ...
FROM paket
ORDER BY tanggal_pengambilan DESC
LIMIT 50 OFFSET 0;
```

Halaman kedua:

```sql
SELECT
    ...
FROM paket
ORDER BY tanggal_pengambilan DESC
LIMIT 50 OFFSET 50;
```

### Manfaat

- Mengurangi jumlah data yang dikirim dari database.
- Mengurangi penggunaan memory PHP.
- Mengurangi ukuran response API.
- Mempercepat proses loading halaman.
- Mengurangi beban server lama.
- Membuat tampilan lebih responsif.

### Catatan tentang OFFSET

Untuk data yang sangat besar, `OFFSET` yang terlalu jauh bisa tetap menjadi lambat karena database harus melewati banyak baris.

Jika nanti jumlah data sangat besar, dapat dipertimbangkan metode cursor pagination atau keyset pagination, misalnya berdasarkan ID atau timestamp terakhir yang sudah ditampilkan.

## 9. Pagination dalam Arsitektur MVC PHP Native

Pagination sebaiknya dibagi ke beberapa layer.

### Controller

Tugas controller:

- Membaca parameter request.
- Memvalidasi parameter.
- Menentukan nilai default.
- Menghitung `OFFSET`.
- Memanggil model.
- Mengembalikan response JSON.

Contoh konsep:

```php
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;

if ($page < 1) {
    $page = 1;
}

if ($limit < 1 || $limit > 100) {
    $limit = 50;
}

$offset = ($page - 1) * $limit;

$data = $paketModel->getHistory($limit, $offset);
```

### Model

Tugas model:

- Menjalankan query database.
- Menerima nilai `limit` dan `offset`.
- Menggunakan query yang sudah dioptimalkan.
- Tidak mengurus tampilan atau HTML.

Contoh konsep:

```php
public function getHistory($limit, $offset)
{
    $sql = "
        SELECT
            ...
        FROM paket
        INNER JOIN pasien
            ON pasien.id = paket.pasien_id
        ORDER BY paket.tanggal_pengambilan DESC
        LIMIT " . (int) $limit . "
        OFFSET " . (int) $offset;

    return $this->db->query($sql);
}
```

Catatan keamanan:

- Pastikan nilai `limit` dan `offset` sudah divalidasi sebagai integer.
- Jangan memasukkan input mentah user langsung ke query.
- Untuk nilai string seperti tanggal atau keyword pencarian, gunakan mekanisme escaping atau prepared statement jika tersedia pada versi PHP/MySQL yang digunakan.

### View atau Frontend

Tugas frontend:

- Menampilkan data yang diterima.
- Menampilkan tombol halaman.
- Mengirim request untuk halaman berikutnya.
- Menampilkan informasi jumlah data atau halaman.

Contoh request:

```text
GET /api/paket/history?page=1&limit=50
```

## 10. Query Parameter dan Request Body

Penempatan data pada REST API bergantung pada tujuan request.

### GET

GET biasanya digunakan untuk mengambil data.

Parameter umum dikirim melalui query string.

Contoh:

```text
GET /api/paket/history?date=2026-09-04&page=1&limit=50
```

Parameter dapat digunakan untuk:

- Filter tanggal.
- Filter status.
- Pencarian.
- Sorting.
- Pagination.
- ID referensi tertentu.

Contoh:

```text
GET /api/paket/history?date=2026-09-04
```

### POST

POST biasanya digunakan untuk membuat data baru atau menjalankan suatu proses.

Data utama dikirim melalui request body.

Contoh:

```http
POST /api/paket/ambil
Content-Type: application/json
Authorization: Bearer <JWT>
```

Body:

```json
{
  "pasien_id": 123,
  "paket_id": 456,
  "tanggal_pengambilan": "2026-09-04",
  "keterangan": "Pengambilan paket terapi"
}
```

### PUT atau PATCH

PUT atau PATCH biasanya digunakan untuk mengubah data.

Data yang ingin diubah dikirim melalui request body.

Contoh:

```http
PATCH /api/paket/456
Content-Type: application/json
Authorization: Bearer <JWT>
```

Body:

```json
{
  "status": "selesai"
}
```

### DELETE

DELETE digunakan untuk menghapus data atau menandai data sebagai dihapus, sesuai desain sistem.

Contoh:

```http
DELETE /api/paket/456
Authorization: Bearer <JWT>
```

## 11. JWT Authentication

### Saat Login

Username dan password dikirim melalui request body.

Contoh:

```http
POST /api/login
Content-Type: application/json
```

Body:

```json
{
  "username": "kasir01",
  "password": "password-user"
}
```

Jika berhasil, server mengembalikan JWT.

Contoh response:

```json
{
  "success": true,
  "token": "<JWT_TOKEN>",
  "expires_in": 3600
}
```

### Setelah Login

JWT tidak perlu dikirim ulang di body setiap request.

JWT sebaiknya dikirim melalui HTTP header:

```http
Authorization: Bearer <JWT_TOKEN>
```

Contoh request mengambil history:

```http
GET /api/paket/history?date=2026-09-04&page=1&limit=50
Authorization: Bearer <JWT_TOKEN>
```

Contoh request mengambil paket:

```http
POST /api/paket/ambil
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json
```

Body:

```json
{
  "pasien_id": 123,
  "paket_id": 456
}
```

### Kesimpulan JWT

- Username dan password saat login: request body.
- JWT setelah login: `Authorization` header.
- Filter dan pagination pada GET: query parameter.
- Data yang dibuat atau diubah: request body.
- Jangan menaruh JWT di URL karena URL dapat tercatat pada log, history, atau sistem monitoring.

## 12. Contoh Struktur Endpoint

```text
POST   /api/login
GET    /api/paket/history
GET    /api/paket/history?date=2026-09-04&page=1&limit=50
GET    /api/paket/{id}
POST   /api/paket/ambil
PATCH  /api/paket/{id}
DELETE /api/paket/{id}
```

Semua endpoint selain login yang membutuhkan autentikasi harus memeriksa JWT pada header.

## 13. Contoh Alur Request

### Login

```text
Frontend
   |
   | POST /api/login
   | Body: username dan password
   v
Controller
   |
   v
Model
   |
   v
Database
   |
   v
Controller mengembalikan JWT
   |
   v
Frontend menyimpan token sesuai mekanisme keamanan aplikasi
```

### Mengambil History Paket

```text
Frontend
   |
   | GET /api/paket/history?page=1&limit=50
   | Header: Authorization: Bearer JWT
   v
Controller
   |
   | Validasi JWT
   | Validasi page dan limit
   | Hitung offset
   v
Model
   |
   | Query dengan LIMIT dan OFFSET
   v
Database
   |
   v
Response JSON maksimal 50 data
   |
   v
Frontend menampilkan data dan pagination
```

### Mengambil Paket

```text
Frontend
   |
   | POST /api/paket/ambil
   | Header: Authorization: Bearer JWT
   | Body: data paket/pasien
   v
Controller
   |
   | Validasi JWT
   | Validasi body
   | Validasi hak akses
   v
Model
   |
   | INSERT atau UPDATE
   v
Database
   |
   v
Response sukses atau gagal
```

## 14. Rencana Perbaikan yang Disarankan

### Tahap 1: Backup dan Dokumentasi

- Backup database produksi.
- Catat versi PHP dan MySQL.
- Catat engine setiap tabel.
- Catat struktur tabel dan index.
- Catat query yang membutuhkan waktu lama.
- Jangan melakukan perubahan langsung tanpa backup.

### Tahap 2: Ukur Performa

- Jalankan query secara langsung di database.
- Ukur waktu eksekusi query.
- Jalankan `EXPLAIN`.
- Bandingkan performa lokal, playground, dan server rumah sakit.
- Periksa penggunaan CPU, RAM, disk, dan koneksi database jika akses tersedia.

### Tahap 3: Optimasi Query

- Kurangi kolom pada `SELECT`; hindari `SELECT *` jika tidak diperlukan.
- Kurangi jumlah join jika ada join yang tidak diperlukan.
- Evaluasi penggunaan `GROUP BY`.
- Pastikan kondisi `WHERE` tepat.
- Tambahkan index yang sesuai.
- Evaluasi `ORDER BY`.
- Gunakan filter tanggal atau status agar data tidak terlalu besar.

### Tahap 4: Terapkan Pagination

- Tentukan jumlah data per halaman, misalnya 50.
- Gunakan `LIMIT` dan `OFFSET`.
- Tambahkan parameter `page` dan `limit`.
- Tampilkan navigasi halaman pada frontend.
- Jangan mengambil 14.000 baris sekaligus jika user hanya membutuhkan sebagian data.

### Tahap 5: Evaluasi Caching

Caching bukan berarti memecah query.

Caching berarti menyimpan hasil query atau data yang sering digunakan untuk sementara agar request berikutnya tidak perlu selalu menjalankan query berat.

Caching dapat dipertimbangkan untuk:

- Daftar referensi yang jarang berubah.
- Data dashboard.
- Rekapitulasi yang tidak perlu real-time.
- Data yang sering diminta oleh banyak user.

Untuk data transaksi rumah sakit yang harus selalu akurat, caching perlu dirancang dengan hati-hati.

### Tahap 6: Evaluasi Infrastruktur

Jika query sudah cukup optimal tetapi masih lambat:

- Evaluasi spesifikasi server.
- Evaluasi versi PHP dan MySQL.
- Periksa konfigurasi database.
- Periksa koneksi jaringan.
- Periksa konfigurasi load balancer.
- Pertimbangkan upgrade server jika memungkinkan.

### Tahap 7: Evaluasi Perubahan Engine

Perubahan dari MyISAM ke InnoDB atau sebaliknya tidak boleh dilakukan hanya berdasarkan dugaan.

Sebelum mengubah engine:

- Backup database.
- Uji di server staging.
- Periksa kompatibilitas versi MySQL.
- Periksa foreign key dan relasi tabel.
- Periksa kebutuhan transaksi.
- Uji performa dengan data yang menyerupai produksi.
- Siapkan rencana rollback.

## 15. Hal yang Perlu Ditanyakan kepada Atasan atau Admin Server

1. Versi PHP yang benar-benar aktif di server produksi berapa?
2. Versi MySQL yang digunakan berapa?
3. Apakah server mendukung InnoDB?
4. Apakah seluruh tabel memang menggunakan MyISAM?
5. Apakah ada batasan konfigurasi PHP seperti `memory_limit`, `max_execution_time`, atau `post_max_size`?
6. Apakah ada load balancer atau reverse proxy?
7. Berapa kapasitas CPU dan RAM server?
8. Apakah database berada di server yang sama dengan aplikasi?
9. Apakah ada batasan koneksi database?
10. Apakah boleh menambahkan index pada database produksi?
11. Apakah tersedia server staging untuk pengujian?
12. Apakah perubahan struktur database harus melalui prosedur tertentu?

## 16. Prinsip Penting

- Jangan langsung drop dan membuat ulang seluruh tabel.
- Jangan menghapus semua index.
- Jangan menganggap MyISAM pasti lebih cepat untuk semua kasus.
- Jangan mengirim ribuan baris jika frontend hanya membutuhkan puluhan baris.
- Gunakan `EXPLAIN` sebelum mengubah struktur database.
- Pisahkan tanggung jawab Controller, Model, dan View.
- Gunakan query parameter untuk filter dan pagination pada GET.
- Gunakan request body untuk data POST, PUT, dan PATCH.
- Gunakan JWT pada header `Authorization`.
- Selalu lakukan backup sebelum perubahan database produksi.
- Uji perubahan pada staging atau playground terlebih dahulu.
- Utamakan pengukuran dan bukti teknis daripada asumsi.

## 17. Kesimpulan Akhir

Masalah loading 15 detik untuk mengambil sekitar 14.000 baris belum cukup menjadi alasan untuk menghapus semua index atau membuat ulang seluruh tabel dengan MyISAM.

Langkah paling masuk akal adalah:

1. Identifikasi query yang lambat.
2. Jalankan `EXPLAIN`.
3. Periksa index pada `JOIN`, `WHERE`, `GROUP BY`, dan `ORDER BY`.
4. Kurangi data yang diambil.
5. Terapkan pagination di model/backend dan kontrol halaman di frontend.
6. Gunakan JWT melalui header.
7. Gunakan query parameter untuk filter dan pagination.
8. Gunakan body untuk data yang dibuat atau diubah.
9. Evaluasi caching jika diperlukan.
10. Evaluasi server dan engine database berdasarkan hasil pengujian.
11. Lakukan perubahan database hanya setelah backup dan pengujian.

Dokumen ini merupakan hasil brainstorming awal dan perlu disesuaikan lagi dengan struktur tabel, query asli, versi MySQL, serta keterbatasan server rumah sakit.
