# Standar Arsitektur: Repository + Service Layer

Dokumen ini adalah versi konkret/executable dari [standart-developmenmt/standart-dev.md](../standart-developmenmt/standart-dev.md), berdasarkan pola yang sudah dipakai sejak awal oleh entity `Auth`, `Role`, `MasterCategory`, dan `DetailExpensesRifqi`, lalu diterapkan penuh ke modul **Money Management** sebagai referensi (lihat [money-management-refactor.md](money-management-refactor.md)).

Tujuannya bukan cuma "ikut standar" — di modul Money Management, controller web dan API ternyata menduplikasi logika bisnis yang sama persis (query Eloquent, validasi, `DB::transaction`), hanya beda bentuk response. Repository + Service menghilangkan duplikasi itu: satu method Service dipanggil oleh controller web *dan* API.

## Alur Wajib

```
Route -> Controller -> (FormRequest, belum diterapkan) -> Service -> Repository -> Model -> Database
```

- Controller: terima request, panggil Service, format response. **Tidak boleh** query Eloquent langsung atau menaruh logika bisnis.
- Service: orkestrasi bisnis, `DB::transaction()` untuk operasi multi-tabel. **Tidak boleh** menyentuh `Request`/`response()->json()`.
- Repository: query database saja. **Tidak boleh** membangun response HTTP.

> Catatan: ekstraksi validasi ke `FormRequest` class **belum termasuk** di refactor ini (keputusan sadar, validasi masih inline `$request->validate()` di controller). Ini murni ekstraksi Repository+Service.

## Package yang dipakai

`yaza/laravel-repository-service` (namespace `LaravelEasyRepository`), sudah ada di `composer.json` sebelum refactor ini.

| Class | Isi |
|---|---|
| `LaravelEasyRepository\Repository` | interface: `find`, `findOrFail`, `all`, `create`, `update`, `delete`, `destroy` |
| `LaravelEasyRepository\Implementations\Eloquent` | implementasi default di atas, pakai `$this->model` |
| `LaravelEasyRepository\BaseService` | interface Service, method sama seperti `Repository` |
| `LaravelEasyRepository\Service` | implementasi default, pakai `$this->mainRepository` |

## Struktur folder & penamaan

```
app/Repositories/<Entity>/<Entity>Repository.php            interface extends Repository
app/Repositories/<Entity>/<Entity>RepositoryImplement.php   extends Eloquent, implements <Entity>Repository
app/Services/<Entity>/<Entity>Service.php                   interface extends BaseService
app/Services/<Entity>/<Entity>ServiceImplement.php          extends Service, implements <Entity>Service
```

- `<Entity>` mengikuti nama Model (mis. `FinanceTransaction`, bukan `Transaction`) supaya tidak ambigu ketika banyak modul dipakai bersamaan.
- Kalau Model tidak butuh query kustom apa pun, interface & implement-nya boleh kosong (lihat `MasterCategoryRepository` — cukup `find/create/update/delete` bawaan).
- Method kustom (business query) ditaruh di Repository, method kustom (business logic/orkestrasi) ditaruh di Service.

### Service yang bukan model-backed

Dua kasus di Money Management tidak mewakili satu Model (jadi **tidak** extends `BaseService`/`Service` bawaan package):
- `FinanceCycleService` — kalkulator tanggal siklus payroll, pure function.
- `FinancialAdviceService` — generator saran finansial, pure function.

Keduanya tetap dibuat sebagai interface + implement lalu di-bind ke container (supaya bisa di-inject & di-mock saat testing), hanya saja implement-nya `implements <Interface>` langsung, bukan `extends Service`.

Ada juga *plain support class* tanpa interface/binding sama sekali — `App\Services\Support\AssetBalanceAggregator` — dipakai lewat constructor injection biasa karena tidak butuh polymorphism/mocking.

## Exception untuk business rule

`App\Exceptions\FinanceDomainException extends \DomainException` dipakai Service untuk melempar pelanggaran business rule (saldo tidak cukup, status tidak valid, dst). Controller (web & API) menangkapnya dan memetakan ke response masing-masing:

```php
try {
    $this->ipoOrderService->confirmAllotment(auth()->id(), $id, $validated);
} catch (FinanceDomainException $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
```

## Binding provider

Binding baru (khusus modul yang direfactor) **tidak** ditaruh di `AppServiceProvider` (itu tempat binding lama `Auth`/`Role`) — supaya satu file tidak jadi tempat sampah semua modul. Setiap modul besar dapat provider sendiri, contoh: `App\Providers\RepositoryServiceProvider` untuk Money Management, didaftarkan di `config/app.php` (proyek ini Laravel 10, belum ada `bootstrap/providers.php`).

## Catatan: override `create`/`update` di Service

`LaravelEasyRepository\Service::create()` dan `::update()` bawaan **tidak return apa-apa** (cuma manggil `$this->mainRepository->create($data)` tanpa `return`). Kalau controller (khususnya API) butuh model yang baru dibuat/diupdate untuk dikembalikan di response, override kedua method itu di `*ServiceImplement`:

```php
public function create($data)
{
    return $this->mainRepository->create($data);
}

public function update($id, array $data)
{
    $this->mainRepository->update($id, $data);
    return $this->mainRepository->find($id);
}
```

Lihat `FinanceCategoryServiceImplement`/`FinanceInvestmentServiceImplement` untuk contohnya.

## Kapan Repository butuh method kustom?

Kalau controller sekarang melakukan query yang lebih dari `find/create/update/delete` biasa — filter, agregasi (`SUM`, `groupBy`), cek kepemilikan (`where('user_id', ...)->findOrFail(...)`) — itu jadi method Repository, bukan ditaruh di Service. Service memanggil Repository, mengorkestrasi beberapa Repository sekaligus kalau perlu, dan membungkus `DB::transaction()`.

## Pola untuk modul berikutnya

1. Inventarisasi semua controller modul tsb (web + API kalau ada) — cari logika yang terduplikasi.
2. Identifikasi Model apa saja yang dipakai → itu draft daftar Repository.
3. Identifikasi kapabilitas bisnis (bukan 1:1 dengan controller method) → itu draft daftar Service.
4. Kerjakan per-batch kecil (bukan sekali refactor semua controller) — tiap batch harus meninggalkan aplikasi dalam keadaan berjalan penuh, lihat urutan batch di [money-management-refactor.md](money-management-refactor.md) sebagai contoh.
5. Jalankan test setelah tiap batch; kalau menemukan bug/divergensi perilaku selama ekstraksi (seperti 3 bug di Money Management), jangan disatukan diam-diam — dokumentasikan & buat test regresi.
