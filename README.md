# Sistem Pengajuan Transaksi Pengeluaran

Aplikasi web workflow approval pengajuan transaksi pengeluaran, dibangun dengan Laravel + MySQL + Bootstrap 5.

> Catatan: file-file di sini adalah **kode aplikasi**, bukan skeleton Laravel penuh. Ikuti langkah instalasi di bawah untuk menggabungkannya ke project Laravel baru.

## 1. Cara Instalasi

```bash
# 1. Buat project Laravel baru (sesuaikan versi Laravel dengan yang kamu pakai)
composer create-project laravel/laravel lavanaya-expense-web
cd lavanaya-expense-web

# 2. Salin seluruh folder app/, database/, resources/, routes/ dari paket ini,
#    TIMPA file yang sama namanya (app/Http/Kernel.php, routes/web.php, dst)

# 3. Set koneksi database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_approval
DB_USERNAME=root
DB_PASSWORD=

# 4. Daftarkan middleware 'role' (lihat bagian "Integrasi Kernel/Middleware" di bawah)

# 5. Jalankan migrasi + seeder
php artisan migrate --seed

# 6. Buat symlink storage supaya file lampiran bisa diakses publik
php artisan storage:link

# 7. Jalankan server
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser.

## 2. Integrasi Kernel / Middleware (PENTING)

Aplikasi ini memakai middleware custom `CheckRole` (alias `role`) untuk RBAC, dipakai di `routes/web.php` seperti:

```php
Route::middleware('role:staff')->group(...);
Route::middleware('role:spv,manager,direktur')->group(...);
```

Cara mendaftarkannya tergantung versi Laravel kamu:

**Laravel 8/9/10 (ada `app/Http/Kernel.php`):**
Tambahkan satu baris ini ke dalam array `$middlewareAliases` (Laravel 9 akhir/10) atau `$routeMiddleware` (Laravel 8/9 awal):
```php
'role' => \App\Http\Middleware\CheckRole::class,
```
Jangan timpa seluruh Kernel.php — cukup tambahkan baris ini ke array middleware yang sudah ada, supaya middleware bawaan Laravel lainnya tidak hilang.

**Laravel 11/12 (tidak ada Kernel.php):**
Tambahkan di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

## 3. Akun Login Testing

Semua password: `password`

| Role       | Email               |
|------------|----------------------|
| Staff      | staff@test.com       |
| SPV        | spv@test.com          |
| Manager    | manager@test.com     |
| Direktur   | direktur@test.com    |
| Finance    | finance@test.com     |

## 4. Struktur Database

- **roles** — daftar role (staff, spv, manager, direktur, finance)
- **users** — akun login, punya `role_id`
- **categories** — kategori pengajuan (mis. "PO Produk", "ATK", dll)
- **budgets** — alokasi & pemakaian budget per kategori per bulan/tahun
- **submissions** — data pengajuan (nomor, tanggal, pengaju, kategori, nilai, deskripsi, lampiran, status)
- **approvals** — setiap baris = satu tahap approval (sequence, role approver, status, catatan)
- **payments** — hasil proses Finance (status paid/rejected, jumlah, catatan)

### Relasi
- `users.role_id` → `roles.id`
- `submissions.user_id` → `users.id` (pengaju)
- `submissions.category_id` → `categories.id`
- `approvals.submission_id` → `submissions.id`
- `approvals.approver_user_id` → `users.id` (nullable, terisi setelah approve/reject)
- `payments.submission_id` → `submissions.id`
- `payments.processed_by` → `users.id`
- `budgets.category_id` → `categories.id`

## 5. Logika Workflow Approval

Diimplementasikan di `app/Services/WorkflowService.php`:

1. **Kategori = "PO Produk"** → rantai approval langsung `['direktur']` saja.
2. **Bukan PO Produk & nilai > Rp 5.000.000** → rantai `['spv', 'manager']`.
3. **Nilai > Rp 10.000.000** → rantai `['spv', 'manager', 'direktur']`.
4. **Asumsi tambahan**: nilai ≤ Rp 5.000.000 & bukan PO Produk → cukup approval SPV saja (`['spv']`), karena soal tidak menyebutkan jalur untuk nilai kecil.
5. **Budget kategori tidak cukup** → status langsung `Rejected` saat submit, sebelum masuk ke approval manapun.
6. **Reject di tahap manapun** → status langsung `Rejected`.
7. **Semua approval selesai** → status `Waiting Finance`.
8. **Finance cek saldo** → jika cukup: status `Paid` + budget `used_amount` bertambah. Jika tidak cukup: status `Rejected`.

## 6. Fitur yang Diimplementasikan

- Login berbasis role (custom, tanpa Breeze) dengan Laravel session auth
- RBAC lewat middleware `role:...`
- Upload lampiran (PDF/JPG/JPEG/PNG, maks 5MB) via Laravel Storage (disk `public`)
- Nomor pengajuan auto-generate format `PGJ-YYYYMM-XXXX`
- Dashboard statistik ringkas per role
- Riwayat & detail approval per pengajuan (timeline)

## 7. Fitur Tambahan yang Belum Diimplementasikan (bisa dikembangkan)

Sesuai poin "Nilai Plus" di soal, hal berikut belum dibuat dan bisa jadi pengembangan lanjutan:
- Email notification saat status berubah
- Export PDF / Excel
- Activity log / audit trail terpisah
- API endpoint (REST)
- Multi file upload

## 8. Catatan Teknis

- Validasi file upload: `mimes:pdf,jpg,jpeg,png`, `max:5120` (KB) → 5MB.
- Semua perubahan status yang melibatkan lebih dari satu tabel (submit, approve/reject, proses finance) dibungkus `DB::transaction()` di `WorkflowService` agar konsisten.
- Jangan lupa `php artisan storage:link` agar file di `storage/app/public/lampiran` bisa diakses dari `public/storage/lampiran`.
