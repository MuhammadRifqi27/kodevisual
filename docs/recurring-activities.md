# Dokumentasi Fitur: Recurring Activities (Aktivitas Berulang)

Fitur **Recurring Activities** memungkinkan pengguna untuk mendefinisikan tugas atau kegiatan berulang (Harian, Mingguan, atau Bulanan) yang akan diproyeksikan secara otomatis ke dalam **Daily Activity** (tabel aktivitas harian) dengan horizon proyeksi 14 hari ke depan.

---

##  Arsitektur & Hubungan Data

Sistem ini didesain menggunakan pola **Template-Instance**:
*   **DailyPlannerRecurringActivity (Template):** Menyimpan aturan perulangan seperti nama aktivitas, frekuensi (harian, mingguan, bulanan), hari dalam seminggu, tanggal bulanan, rentang aktif, jam mulai, dan durasi.
*   **DailyPlannerActivity (Instance):** Menyimpan entri harian riil yang digenerate oleh template untuk tanggal spesifik.

```
+-----------------------------------+             +-----------------------------+
| DailyPlannerRecurringActivity     |             | DailyPlannerActivity        |
| (Template)                        |             | (Instance)                  |
+-----------------------------------+             +-----------------------------+
| - id                              |             | - id                        |
| - user_id                         |             | - user_id                   |
| - activity                        |             | - recurring_activity_id (FK)|
| - frequency (daily/weekly/monthly)| ----------> | - recurring_date            |
| - day_of_week (JSON array)        |             | - activity                  |
| - day_of_month                    |             | - status                    |
| - start_date / end_date           |             | - start_datetime            |
| - start_time                      |             | - end_datetime              |
| - duration_minutes                |             +-----------------------------+
| - is_active                       |
+-----------------------------------+
```

---

## Skema Database & Migrasi

### 1. Tabel `daily_planner_recurring_activities`
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment primary key |
| `user_id` | Foreign Key | Relasi ke tabel `users` |
| `activity` | String | Nama aktivitas |
| `frequency` | Enum | Pilihan: `daily`, `weekly`, `monthly` |
| `day_of_week` | JSON (Nullable) | Array hari untuk mingguan (misal: `["Monday", "Wednesday"]`) |
| `day_of_month` | Integer (Nullable)| Tanggal untuk bulanan (1 - 31) |
| `start_date` | Date | Batas awal berlakunya aktivitas |
| `end_date` | Date (Nullable) | Batas akhir berlakunya aktivitas |
| `start_time` | Time | Jam mulai aktivitas (format `H:i`) |
| `duration_minutes` | Integer | Durasi aktivitas dalam satuan menit |
| `is_active` | Boolean | Status aktif/tidaknya aturan berulang |

### 2. Kolom Tambahan pada `daily_planner_activities`
Untuk mengaitkan instansiasi harian dengan template berulang, kolom berikut telah ditambahkan:
*   `recurring_activity_id` (Nullable Foreign Key): Mengaitkan ke tabel recurring template.
*   `recurring_date` (Nullable Date): Tanggal khusus instansiasi perulangan untuk mencegah duplikasi entri pada hari yang sama.

---

## Mekanisme Proyeksi Otomatis

Proyeksi entri berulang ke aktivitas harian dilakukan secara **idempoten** (aman dipanggil berkali-kali tanpa memicu duplikasi data).

### Alur Kerja Engine (`processRecurringActivities`)
1. Mengambil seluruh aturan berulang milik user yang berstatus `is_active = true`.
2. Melakukan *day-by-day scanning* dari hari ini (`today`) hingga 13 hari ke depan (horizon 14 hari).
3. Untuk setiap tanggal scan:
    *   Memeriksa apakah aturan frekuensi cocok dengan hari/tanggal tersebut menggunakan fungsi `matchesDate($date)`.
    *   Jika cocok, sistem memeriksa apakah sudah ada entri `DailyPlannerActivity` yang memiliki `recurring_activity_id` tersebut untuk `recurring_date` terkait.
    *   Jika belum ada, sistem mengonstruksi waktu mulai (`start_datetime`) dan selesai (`end_datetime`), lalu meng-insert entri baru dengan status `not started`.

### Pemicu Sinkronisasi (Triggers)
Ada tiga cara sistem memicu generasi otomatis ini:
1. **On Load Dashboard & Activities:** Dipanggil secara pasif di controller saat pengguna membuka halaman Daily Planner Dashboard atau Daftar Aktivitas Harian.
2. **AJAX Button ("Sync Sekarang"):** Pengguna dapat menekan tombol sync di pojok kanan atas halaman Recurring Activities untuk memaksa sistem memproyeksikan jadwal instan.
3. **Server-Side Background Scheduler:** 
    *   Command Konsol: `php artisan daily-planner:generate-recurring`
    *   Telah diregistrasikan di `app/Console/Kernel.php` untuk berjalan otomatis setiap hari pada tengah malam (`00:00`).

---

## Penanganan Masalah & Validasi (Error 422)

> **Catatan Penting:**
> Laravel secara bawaan mengevaluasi aturan validasi tambahan seperti `integer|min:1|max:31` pada field `day_of_month` meskipun kondisi `required_if:frequency,monthly` tidak terpenuhi (false). Karena browser mengirimkan field kosong (`null`), validator menolaknya karena `null` dianggap bukan `integer` yang valid.

### Solusi yang Diterapkan:
1. **Rule `nullable`**: Menambahkan deklarasi `nullable` pada kolom kondisional di controller (`DailyPlannerController`).
   ```php
   'day_of_week'  => 'required_if:frequency,weekly|nullable|array',
   'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
   ```
2. **Data Sanitization**: Di dalam controller, sebelum proses pembuatan (`create`) or pembaruan (`update`), kolom yang tidak relevan dengan tipe frekuensi terpilih akan disanitasi secara paksa menjadi `null` untuk menjaga konsistensi data di database.

---

## Panduan Antarmuka Pengguna (UI/UX)

Halaman manajemen memiliki antarmuka premium berbasis Metronic 8:
*   **Hero Section**: Desain gradien ungu modern dengan tombol Sync Cepat dan Tambah Aktivitas.
*   **Stat Cards**: Glassmorphism card yang menyajikan statistik total, aktif, mingguan, dan bulanan secara interaktif.
*   **Day Selection (Weekly)**: Checkbox modern berbentuk kotak inisial hari (Sen, Sel, Rab, dsb.) yang berganti warna aktif secara dinamis.
*   **Next Occurrences Preview**: Menampilkan pill penanda 3 tanggal proyeksi terdekat dari masing-masing aktivitas agar pengguna tahu persis kapan aktivitas berulang tersebut akan muncul di jadwal harian mereka.
