<div align="center">
  <h1>🏥 Sistem Manajemen Paket Rehabilitasi Medis (PRM) 🌟</h1>
  <p><i>Membuat pengelolaan terapi menjadi semudah bermain game! 🎮</i></p>
</div>

---

## 👨‍👩‍👧‍👦 Apa itu PRM? (Penjelasan untuk Semua Usia!)

Bayangkan kamu membeli **Tiket Terusan Wahana Bermain** 🎢 untuk 10 kali main. Setiap kali kamu naik wahana, tiketmu akan dicap dan jumlahnya berkurang satu. 

Nah, **Sistem PRM** ini mirip seperti itu, tapi untuk **Rumah Sakit**! 🏥
Ketika seorang pasien membeli **"Paket Terapi"** (misalnya paket pijat kesehatan atau latihan wicara untuk 10 kali pertemuan), sistem ini akan membantu dokter dan suster untuk:
1. 🎟️ Mencatat bahwa pasien punya tiket 10 kali.
2. ✂️ Memotong tiket secara otomatis setiap kali pasien datang berobat.
3. 🕵️‍♂️ Mengawasi sisa tiket agar tidak ada yang kelupaan atau hilang! (Selamat tinggal kartu kertas yang gampang sobek! 📄❌)

---

## ✨ Fitur Keren di Dalamnya

Aplikasi ini sangat pintar karena dilengkapi dengan berbagai kekuatan super:

- 💸 **Integrasi Kasir Otomatis**: Kalau pasien bayar paket di kasir, paketnya langsung masuk ke sistem tanpa perlu diketik ulang! Ajaib kan? 🪄
- 🗂️ **Data Master yang Rapi**: Kita bisa membuat jenis-jenis paket baru seperti *Paket Fisioterapi*, *Hidroterapi*, sampai *Terapi Okupasi*.
- 📊 **Laporan & Audit**: Bos dan manajer rumah sakit bisa melihat grafik dan angka-angka berapa banyak pasien yang sedang aktif dan sisa tiket yang belum dipakai.
- 🎨 **Tampilan Indah & Modern**: Dibuat dengan gaya modern (TailwindCSS) sehingga perawat dan dokter tidak akan bosan memandangi layarnya! Tersedia juga *Dark Mode* 🌙 untuk mata yang sensitif cahaya!

---

## 🛠️ Teknologi yang Digunakan (Untuk yang Suka Ngoding!)

Bagi teman-teman programmer atau mahasiswa IT, ini dia mesin di balik layarnya:
* **Frontend**: HTML5 murni, Vanilla JavaScript, dan Tailwind CSS (via CDN) agar super kencang! ⚡
* **Backend**: PHP murni berpadu dengan arsitektur sederhana untuk merespons API.
* **Database**: MySQL / MariaDB (Terhubung langsung dengan sistem HIS/Billing lama rumah sakit via *Query Join* super canggih!).

---

## 🚀 Cara Menjalankan Sistem Ini

Gampang banget! Kamu hanya perlu:
1. Pasang server lokal seperti **XAMPP**, **MAMP**, atau **Laragon**.
2. *Clone* atau *Download* repository ini ke dalam folder `htdocs` (atau folder `www` kamu).
   ```bash
   git clone https://github.com/louismax12/prm_rkz.git
   ```
3. Sesuaikan pengaturan *database* kamu di dalam folder `api/config/database.php`.
4. Buka di browsermu: `http://localhost/prm_rkz/local/index.html`.
5. *Taraa!* 🎉 Aplikasi siap dimainkan!

---

## 🤝 Kontribusi & Bantuan

Ada ide untuk membuat sistem ini lebih keren lagi? Atau menemukan *bug* 🐛 yang bersembunyi? 
Silakan buat **Pull Request** atau tuliskan di **Issues**! Segala bantuan, mulai dari membetulkan satu huruf sampai membuat fitur baru, sangat kami hargai! ❤️

<br>
<div align="center">
  <b>Dibuat dengan 💻 dan ☕ oleh Louis Maximillian</b> <br>
  <i>Mari bersama-sama membuat dunia medis menjadi lebih ramah teknologi!</i>
</div>
