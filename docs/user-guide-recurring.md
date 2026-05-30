# Panduan Pengguna: Fitur Aktivitas Berulang (Recurring Activities)

Selamat datang di Panduan Pengguna fitur **Aktivitas Berulang (Recurring Activities)** pada aplikasi Daily Planner Anda. Fitur ini dirancang untuk memudahkan Anda menjadwalkan kegiatan rutin tanpa harus memasukkannya secara manual setiap hari.

---

## 🌟 Apa itu Aktivitas Berulang?

Aktivitas Berulang adalah sebuah **template jadwal kegiatan** yang otomatis disalin menjadi rencana aktivitas harian Anda (**Daily Activity**). 

Sistem secara otomatis akan memproyeksikan aktivitas berulang Anda untuk **14 hari ke depan**. Anda cukup membuat polanya satu kali, dan biarkan aplikasi yang bekerja untuk Anda.

---

## 🛠️ Cara Mengakses Fitur

1. Buka dashboard aplikasi Anda.
2. Pada menu navigasi sebelah kiri, cari kategori **Daily Planner**.
3. Klik menu **Recurring Activity**.
4. Anda akan diarahkan ke halaman utama pengelolaan aktivitas berulang dengan tampilan modern, lengkap dengan kartu statistik dan daftar jadwal Anda.

---

## ➕ 1. Membuat Aktivitas Berulang Baru

Untuk menambahkan rutinitas baru, ikuti langkah-langkah berikut:

1. Di halaman utama **Recurring Activity**, klik tombol **Tambah Aktivitas** di pojok kanan atas.
2. Isi formulir pendaftaran kegiatan:
    *   **Nama Aktivitas**: Tuliskan nama kegiatan Anda (contoh: *Olahraga Pagi*, *Weekly Sync*, *Bayar Kos*).
    *   **Frekuensi**: Pilih seberapa sering kegiatan ini berulang:
        *   `Setiap Hari`: Kegiatan akan muncul setiap hari tanpa kecuali.
        *   `Mingguan`: Anda dapat memilih hari-hari tertentu dalam seminggu (misal: *Senin, Rabu, dan Jumat*).
        *   `Bulanan`: Kegiatan akan berulang pada tanggal tertentu di setiap bulannya (misal: *Tanggal 25* untuk gajian/bayar kos).
    *   **Status**: Pilih **Aktif** agar sistem langsung membuat jadwalnya secara otomatis ke Daily Activity Anda.
    *   **Tanggal Mulai**: Tanggal awal kapan sistem harus mulai membuat kegiatan ini.
    *   **Tanggal Berakhir (Opsional)**: Tanggal kapan kegiatan ini resmi selesai. Kosongkan jika aktivitas ini bersifat permanen tanpa batas waktu.
    *   **Jam Mulai & Durasi**: Tentukan jam mulai aktivitas dan durasi pengerjaannya dalam hitungan menit.
3. Klik **Simpan**. 
4. **Selesai!** Rencana kegiatan untuk 14 hari ke depan akan otomatis terbuat di menu Daily Activity Anda.

---

## ✏️ 2. Mengubah (Edit) Aktivitas Berulang

Jika ada perubahan jadwal atau jam pelaksanaan:

1. Temukan aktivitas berulang yang ingin Anda ubah di tabel daftar kegiatan.
2. Klik tombol **Pencil (Edit)** pada kolom aksi sebelah kanan.
3. Ubah kolom yang diperlukan (misal: memindahkan hari mingguan atau mengubah jam mulai).
4. Klik **Simpan Perubahan**.
5. Sistem akan memperbarui detail jadwal di database, dan memproyeksikan ulang aktivitas tersebut ke jadwal harian Anda yang belum dikerjakan.

---

## ⏸️ 3. Menonaktifkan Sementara (Pause) Aktivitas

Apabila Anda sedang berlibur atau ingin menghentikan sementara rutinitas tanpa menghapusnya:

1. Klik tombol **Pencil (Edit)** pada aktivitas yang ingin dihentikan.
2. Pada bagian kolom **Status**, ubah pilihan menjadi: 
   `⏸️ Nonaktif – Hentikan sementara`.
3. Klik **Simpan Perubahan**.
4. Sistem akan berhenti membuat jadwal baru untuk aktivitas tersebut ke Daily Activity Anda sampai Anda mengaktifkannya kembali.

---

## ❌ 4. Menghapus Aktivitas Berulang

Jika aktivitas berulang tersebut sudah tidak Anda lakukan lagi:

1. Klik tombol **Tempat Sampah (Hapus)** pada kolom aksi di tabel kegiatan.
2. Akan muncul jendela konfirmasi.
3. Klik **Ya, hapus!**.
4. Aktivitas berulang akan terhapus. Jadwal harian yang sudah terbuat sebelumnya dan sudah selesai tidak akan terpengaruh, namun jadwal masa depan yang belum dikerjakan akan dihapus agar agenda harian Anda bersih kembali.

---

## 🔄 5. Sinkronisasi Manual (Sync Sekarang)

Secara default, sistem akan otomatis memperbarui agenda harian Anda:
*   Setiap kali Anda membuka halaman Daily Planner Dashboard atau Daftar Kegiatan.
*   Secara otomatis pada tengah malam (`00:00`) melalui sistem latar belakang server.

Namun, jika Anda ingin memastikan bahwa jadwal Anda langsung diperbarui setelah membuat/mengedit aktivitas baru:
*   Cukup klik tombol **Sync Sekarang** (ikon panah melingkar putih di pojok kanan atas halaman).
*   Sistem akan memindai ulang dan memasukkan seluruh aktivitas ke Daily Planner Anda secara instan dengan notifikasi sukses.

---

## 💡 Tips & Trik Penggunaan
*   **Preview Tanggal Terdekat**: Di tabel aktivitas berulang, perhatikan kolom **Jadwal Berikutnya**. Aplikasi menampilkan 3 tanggal terdekat kapan aktivitas tersebut akan muncul di jadwal harian Anda sehingga Anda dapat bersiap-siap terlebih dahulu.
*   **Pencarian Cepat**: Gunakan kolom pencarian di sebelah kanan atas tabel untuk mencari aktivitas Anda dengan mengetikkan nama kegiatan secara langsung tanpa memuat ulang halaman.
