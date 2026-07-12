# Sistem Pengajuan Transaksi Pengeluaran

Aplikasi web workflow approval pengajuan transaksi pengeluaran, dibangun dengan Laravel + MySQL + Bootstrap 5.

## 1. Cara Instalasi

```bash
git clone <url-repo-ini>
cd lavanaya-expense-web

composer install

cp .env.example .env
php artisan key:generate
```

Set koneksi database di `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_approval
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:
```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser.

## 2. Role-Based Access Control

Akses per role diatur lewat middleware `role:...` (`App\Http\Middleware\CheckRole`), didaftarkan sebagai alias `role` dan dipakai di `routes/web.php`, contoh:

```php
Route::middleware('role:staff')->group(...);
Route::middleware('role:spv,manager,direktur')->group(...);
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
- Tampilan custom (bukan default Bootstrap) dengan identitas visual sendiri —
  badge status berbentuk stempel resmi (komponen `<x-stamp>`), tipografi
  serif formal untuk judul, dan monospace untuk nomor pengajuan & nominal
  uang. Lihat `public/css/app-custom.css`.

## 7. Fitur Tambahan yang Belum Diimplementasikan (bisa dikembangkan)

Sesuai poin "Nilai Plus" di soal, hal berikut belum dibuat dan bisa jadi pengembangan lanjutan:
- Email notification saat status berubah
- Export PDF / Excel
- Activity log / audit trail terpisah
- API endpoint (REST)
- Multi file upload

## 8. Catatan Teknis

- Semua field tanggal/waktu (`tanggal_pengajuan`, `submitted_at`, `acted_at`, `paid_at`) dinormalisasi lewat `Carbon::parse()` di sisi view sebelum diformat, sebagai lapisan keamanan tambahan terhadap perbedaan hasil cast antar versi Laravel.
- Validasi file upload: `mimes:pdf,jpg,jpeg,png`, `max:5120` (KB) → 5MB.
- Semua perubahan status yang melibatkan lebih dari satu tabel (submit, approve/reject, proses finance) dibungkus `DB::transaction()` di `WorkflowService` agar konsisten.
- Jangan lupa `php artisan storage:link` agar file di `storage/app/public/lampiran` bisa diakses dari `public/storage/lampiran`.
