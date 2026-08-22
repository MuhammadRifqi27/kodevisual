# Plan: Mobile App Offline-First (Travel Planner & Money Management) + Sinkronisasi ke Web

Dokumen ini adalah rencana teknis untuk membangun aplikasi mobile yang **berjalan offline/local** (data utama tersimpan di device, misal SQLite), khusus untuk dua modul yang sudah ada di aplikasi web ini: **Travel Planner** dan **Money Management**. Mobile app akan sinkron dua arah dengan backend Laravel ini melalui API baru.

Status: **draft rencana — belum ada implementasi.**

---

## 1. Kondisi Saat Ini (Baseline)

Hasil audit codebase:

- **Auth**: session-based (`web` guard), tidak ada API auth aktif selain skeleton default. **`laravel/sanctum` ^3.3 sudah terinstall** tapi hanya route default `GET /user`. Ini artinya kita bisa langsung pakai Sanctum Personal Access Token untuk mobile tanpa nambah dependency baru.
- **routes/api.php**: kosong (default Laravel). Belum ada namespace `api/v1`, belum ada endpoint travel/money-management.
- **Controller yang ada**: semua di `app/Http/Controllers/Travel/*` dan `app/Http/Controllers/MoneyManagement/*` adalah web controller (return Blade view), sebagian endpoint AJAX (datatable, store, destroy) sudah `response()->json()` tapi formatnya ad-hoc (Yajra DataTables style), bukan REST resource yang konsisten.
- **Model tidak punya kolom yang dibutuhkan untuk sync**: tidak ada `uuid`, tidak ada `deleted_at` (soft delete), tidak ada `sync_status`/`last_synced_at`. Semua pakai auto-increment `id` + hard delete. Beberapa relasi pakai **cascade delete di level DB** (misal hapus `travel_trips` otomatis hapus itineraries/budgets/expenses/images).
- Struktur data travel-planner (`travel_trips`, `travel_itineraries`, `travel_budgets`, `travel_expenses`, `travel_trip_images`, `daily_planner_activities`, `daily_planner_recurring_activities`) dan money-management (`finance_categories`, `finance_transactions`, `finance_budgets`, `finance_investments`, `finance_portfolios`, `finance_investment_transactions`, `finance_ipo_orders`, `finance_emiten_prices`, `finance_emiten_trades`, `finance_net_worth_snapshots`, `finance_recurring_transactions`, `finance_settings`) sudah cukup matang, tapi didesain untuk single-writer (web only).

Implikasi: sebelum bisa sinkron, tabel-tabel ini **wajib** ditambah kolom pendukung sync. Tidak bisa langsung "buat API di atas skema sekarang".

---

## 2. Prinsip Desain yang Direkomendasikan

1. **Server = source of truth, tapi mobile boleh kerja penuh offline.** Konflik diselesaikan pakai strategi berbeda per tipe data (lihat §6) — bukan satu aturan global.
2. **Client-generated ID (UUID)**, bukan auto-increment, untuk semua tabel yang bisa dibuat dari mobile saat offline. Ini menghindari collision saat dua device (atau device+web) membuat record baru di waktu yang sama tanpa koneksi.
3. **Soft delete di semua tabel yang ikut sync** (`deleted_at`). Hard delete tidak bisa disinkronkan — device lain tidak akan pernah tahu row itu "pernah ada lalu dihapus".
4. **Sinkronisasi berbasis delta (incremental), bukan full-dump tiap kali.** Pakai `updated_at` + cursor/timestamp terakhir sync per device.
5. **Idempotent push.** Client boleh retry kirim data yang sama tanpa menyebabkan duplikat (karena PK adalah UUID yang sudah digenerate di client, `upsert` otomatis idempotent).
6. **Scope sync = per user.** Semua query sync difilter `user_id` dari token yang login, tidak boleh percaya `user_id` yang dikirim dari body request.

---

## 3. Perubahan Skema Database (butuh migration baru)

Untuk setiap tabel yang termasuk scope (daftar lengkap di §4), tambahkan:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `uuid` | `char(36)`, unique, indexed | PK pengganti untuk keperluan sync. `id` auto-increment tetap dipertahankan untuk internal FK & performa join, tapi API expose `uuid`. |
| `deleted_at` | nullable timestamp | Soft delete (`SoftDeletes` trait). |
| `updated_at` | sudah ada | Dipakai sebagai cursor delta sync — pastikan **selalu** ke-update tiap perubahan (termasuk saat sync itu sendiri). |
| `dirty`/`sync_version` *(opsional, lihat §6.2)* | integer, default 0 | Hanya untuk tabel finance yang butuh optimistic locking ketat (transaksi, portfolio). |

**Foreign key**: relasi antar tabel (misal `travel_itineraries.trip_id`) tetap pakai `id` internal (bukan uuid) untuk efisiensi, tapi payload API translate ke/dari `uuid` di boundary.

**Cascade delete**: ganti `onDelete('cascade')` di DB level menjadi **soft-cascade di application layer** (saat parent di-soft-delete, child ikut di-soft-delete via model event/observer), supaya history perubahan tetap bisa disinkronkan ke device lain sebelum benar-benar hilang.

---

## 4. Ruang Lingkup Modul

### Travel Planner (7 tabel)
`travel_trips`, `travel_itineraries`, `travel_budgets`, `travel_expenses`, `travel_trip_images`, `daily_planner_activities`, `daily_planner_recurring_activities`

Catatan: `travel_trip_images` — file gambar tidak bisa "disinkronkan" sebagai JSON. Perlu strategi terpisah: upload/download by URL, mobile hanya simpan referensi + cache lokal (lihat §8).

### Money Management (12 tabel)
`finance_categories`, `finance_transactions`, `finance_budgets`, `finance_investments`, `finance_portfolios`, `finance_investment_transactions`, `finance_ipo_orders`, `finance_emiten_prices`, `finance_emiten_trades`, `finance_net_worth_snapshots`, `finance_recurring_transactions`, `finance_settings`

**Rekomendasi untuk fase 1**: jangan langsung include semua 12 tabel. `finance_ipo_orders`, `finance_emiten_trades`, `finance_emiten_prices` punya rantai FK self-referencing yang kompleks (order → release/holding/offset transaction) dan kemungkinan besar dipakai jarang dari mobile. Sarankan fase 1 fokus ke: `finance_categories`, `finance_transactions`, `finance_budgets`, `finance_portfolios`, `finance_investment_transactions`, `finance_settings` — ini yang paling sering dipakai sehari-hari (catat transaksi, cek saldo, cek budget). Modul IPO/emiten/net-worth-snapshot masuk fase 2.

---

## 5. Desain API

### 5.1 Autentikasi
- `POST /api/v1/auth/login` (email+password) → Sanctum token, scoped per device (`createToken('mobile-<device_id>')`).
- `POST /api/v1/auth/logout` → revoke token.
- Middleware: `auth:sanctum` untuk semua route `/api/v1/*`.
- Mobile menyimpan token di secure storage (Keychain/Keystore), bukan plaintext.

### 5.2 Endpoint gaya "sync", bukan CRUD granular per field
Daripada bikin REST CRUD lengkap (`GET/POST/PUT/DELETE`) untuk tiap 19 tabel — yang akan sangat verbose untuk kebutuhan sync — desain **dua endpoint generik per modul**:

```
GET  /api/v1/sync/{module}/pull?since={timestamp|null}
POST /api/v1/sync/{module}/push
```

`{module}` = `travel-planner` atau `money-management`.

**Pull** — server balas semua row yang `updated_at > since` (termasuk yang soft-deleted, supaya client tahu harus hapus juga), dikelompokkan per tabel:
```json
{
  "server_time": "2026-07-30T10:00:00Z",
  "data": {
    "travel_trips": [ {...}, {...} ],
    "travel_itineraries": [ {...} ],
    "...": []
  }
}
```
`server_time` dari response dipakai sebagai `since` untuk pull berikutnya (bukan `now()` di client, untuk hindari clock skew).

**Push** — client kirim batch perubahan (create/update/delete) sejak sync terakhir:
```json
{
  "data": {
    "travel_trips": [ { "uuid": "...", "op": "upsert", ... }, { "uuid": "...", "op": "delete" } ]
  }
}
```
Server balas per-item status (`applied`, `conflict`, `rejected`) supaya client tahu mana yang perlu ditangani manual.

Kenapa endpoint generik, bukan REST per resource: karena kebutuhan mobile bukan "ambil 1 trip", tapi "samakan seluruh state sejak terakhir online". Pattern ini juga yang dipakai aplikasi offline-first populer (mis. PouchDB/CouchDB replication, WatermelonDB sync protocol) — 2 endpoint per domain jauh lebih gampang dikelola daripada 19 endpoint CRUD + logic konflik yang tersebar.

*(Opsional, tetap bisa ditambah endpoint REST read-only biasa seperti `GET /api/v1/travel-planner/trips` untuk kebutuhan lain di luar sync, misal jika nanti mau ada fitur "web view" ringan dari data mobile. Tidak wajib untuk MVP.)*

### 5.3 Urutan proses push per module
Push harus diproses **dalam satu DB transaction per module**, dan urut sesuai dependency (parent dulu baru child): trips → itineraries/budgets/expenses/images. Untuk finance: portfolios/categories dulu, baru transactions.

---

## 6. Resolusi Konflik

Ini bagian paling kritis, terutama untuk data finansial. Rekomendasi: **jangan pakai satu aturan global "last write wins" untuk semua tabel.**

### 6.1 Data append-mostly (aman pakai Last-Write-Wins by timestamp)
Cocok untuk: `travel_itineraries`, `travel_budgets`, `travel_expenses`, `daily_planner_activities`, `finance_categories`, `finance_settings`.
- Konflik jarang terjadi (jarang ada 2 device edit row yang sama persis di waktu bersamaan).
- Aturan: row dengan `updated_at` paling baru menang. Server yang menentukan (bandingkan `updated_at` yang dikirim client vs yang ada di server).

### 6.2 Data finansial (transaksi, saldo) — butuh proteksi lebih ketat
Cocok untuk: `finance_transactions`, `finance_investment_transactions`, `finance_portfolios`, `finance_budgets`.
- **Jangan** biarkan LWW menimpa transaksi begitu saja — bisa bikin "uang hilang" secara diam-diam kalau 2 device sama-sama mencatat transaksi berbeda dengan `id/uuid` yang somehow sama (harusnya tidak terjadi kalau UUID benar), atau kalau ada edit-vs-edit di row yang sama.
- Rekomendasi: **transaksi baru = selalu create (append), nyaris tidak pernah ada "conflict" karena tiap transaksi unik by UUID.** Konflik nyata hanya muncul saat **edit** atau **delete** transaksi lama yang sudah kepakai di device lain.
- Untuk edit/delete: pakai `sync_version` (optimistic lock). Client kirim `sync_version` yang terakhir dia tahu; kalau tidak cocok dengan server → response `conflict`, jangan auto-overwrite. Kembalikan versi server ke client, biarkan **user yang memutuskan** (tampilkan dialog "data ini berubah di device lain, mana yang dipakai?"). Untuk kasus finance, "diam-diam pilih salah satu" lebih berbahaya daripada nanya ke user.
- `finance_portfolios.balance` **tidak pernah disinkronkan langsung** — ini computed value (sudah begitu di kode existing, `appends`). Balance selalu dihitung ulang dari transaksi yang sudah tersinkron, baik di server maupun di mobile. Ini otomatis menghindari seluruh kelas bug "saldo device A beda dengan saldo device B".

### 6.3 Delete
- Delete = soft delete + sync seperti update biasa (row dengan `deleted_at` terisi).
- Kalau device A delete, device B masih punya row itu di cache lokal: saat pull, device B lihat `deleted_at` terisi → hapus dari local DB.
- Kalau device B **mengedit** row yang sama sebelum tahu row itu dihapus di device A → treated sebagai conflict (§6.2 policy), bukan auto-resurrect atau auto-delete.

---

## 7. Struktur Database Mobile (Local)

- Mobile pakai SQLite (atau embedded DB sejenis — pilihan spesifik tergantung stack mobile: Flutter → `drift`/`sqflite`, React Native → `WatermelonDB`/`op-sqlite`, native → Room/CoreData).
- Skema lokal **mirror** skema server untuk 2 modul ini (nama tabel & kolom idealnya sama persis, biar mapping push/pull sederhana).
- Tambahan kolom khusus lokal (tidak dikirim ke server): `_dirty` (bool, belum ke-push), `_sync_error` (kalau push terakhir gagal/conflict).
- Gambar (`travel_trip_images`): mobile simpan file lokal + `uuid` sebagai key; upload terpisah dari sync JSON (`POST /api/v1/sync/travel-planner/trips/{uuid}/images`, multipart), background upload dengan retry.

---

## 8. Keamanan

- Semua endpoint `/api/v1/sync/*` wajib `auth:sanctum` + scoping `user_id` dari token (bukan dari payload).
- Rate limit endpoint sync (`throttle:sync`) untuk cegah abuse — data finansial personal sensitif.
- Token per-device, bukan 1 token dipakai banyak device — memudahkan revoke ("logout device ini saja") dan audit device mana yang terakhir push data apa (tambah kolom `last_used_at`, `device_name` di `personal_access_tokens`, Sanctum sudah sediakan sebagian ini).
- HTTPS wajib (biasanya sudah, tapi tegaskan untuk endpoint auth & sync).
- Pertimbangkan field-level: `finance_settings` mungkin berisi data sensitif (API key exchange, dll) — cek isinya sebelum di-expose ke sync payload mentah-mentah.

---

## 9. Roadmap Implementasi (bertahap)

**Fase 0 — Persiapan skema (backend only, tidak break existing web app)**
- Migration: tambah `uuid`, `deleted_at` (+ `sync_version` untuk tabel finance kritis) ke semua tabel scope.
- Backfill `uuid` untuk row existing (migration data, generate UUID v4 untuk semua row lama).
- Ganti cascade-delete DB-level → soft-delete cascade via model observer.
- Web app tetap jalan seperti biasa (kolom baru tidak dipakai UI existing).

**Fase 1 — Auth API**
- Route `api/v1/auth/*` pakai Sanctum, test login/logout dari Postman/mobile skeleton.

**Fase 2 — Sync API modul Money Management (subset prioritas di §4)**
- Implement `pull`/`push` untuk `finance_categories`, `finance_portfolios`, `finance_transactions`, `finance_investment_transactions`, `finance_budgets`, `finance_settings`.
- Unit test khusus untuk skenario konflik (§6.2).

**Fase 3 — Sync API modul Travel Planner**
- Implement `pull`/`push` untuk 7 tabel travel (tanpa image dulu).
- Endpoint upload/download image terpisah.

**Fase 4 — Mobile app skeleton**
- Setup local DB + auth flow + sync engine (pull on foreground/interval, push on change with debounce, retry queue).

**Fase 5 — Fase 2 finance (IPO, emiten trade, net worth snapshot)**
- Setelah pola sync inti terbukti stabil.

**Fase 6 — Hardening**
- Monitoring (log sync error per user), rate limiting, device management UI di web ("device yang terkoneksi"), payload compression kalau data besar.

---

## 10. Hal yang Perlu Diputuskan Sebelum Mulai Coding

1. **Stack mobile**: Flutter, React Native, atau native? Ini menentukan pilihan local DB & sync library.
2. **Kebijakan konflik finance**: setuju dengan "tanya user saat conflict" (§6.2), atau ada preferensi lain (misal server selalu menang untuk data finance)?
3. **Scope fase 1 money-management**: setuju exclude IPO/emiten/net-worth dulu di MVP?
4. **Multi-device**: apakah 1 user boleh login di banyak device mobile sekaligus (butuh device management), atau cukup 1 device aktif per user untuk awal?
5. **Retensi data terhapus**: soft-deleted row disimpan berapa lama sebelum benar-benar dibersihkan (hard delete permanen via scheduled job)? Ini perlu supaya tabel tidak membengkak selamanya.

---

*Dokumen ini adalah rencana awal — sebelum implementasi, sebaiknya jawaban §10 dikonfirmasi dulu supaya desain migration & API tidak perlu diubah di tengah jalan.*
