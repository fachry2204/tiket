# MASTER PROMPT ANTIGRAVITY
## Sistem Pemesanan Tiket Online Masivers — The Sounds Project 2026

Bertindaklah sebagai **Senior Full-Stack Developer, Software Architect, Database Engineer, UI/UX Designer, dan Security Engineer**.

Bangun aplikasi web lengkap bernama:

**Masivers Ticketing — The Sounds Project 2026**

Aplikasi digunakan untuk pemesanan tiket khusus komunitas Masivers yang ingin menghadiri The Sounds Project 2026. Sistem harus benar-benar berfungsi dan siap dikembangkan ke production, bukan hanya mockup atau halaman statis.

---

# 1. STACK TEKNOLOGI

## Backend

Gunakan:

- Laravel versi stabil terbaru
- PHP 8.3 atau versi stabil yang kompatibel
- Laravel REST API
- Laravel Sanctum
- Laravel Form Request
- Laravel Service Layer
- Repository Pattern
- Laravel Policy dan Gate
- Laravel Queue
- Laravel Scheduler
- Laravel Storage
- DomPDF atau library PDF Laravel yang stabil
- Simple QR Code atau library QR yang stabil

## Frontend

Gunakan:

- Vue 3
- TypeScript
- Composition API
- Vite
- Vue Router
- Pinia
- Axios
- Tailwind CSS
- Chart.js atau ApexCharts
- Headless UI atau komponen Vue yang ringan

## Database

Gunakan:

- MySQL 8+
- Engine InnoDB
- Charset utf8mb4
- Laravel Eloquent ORM
- Laravel Migration
- Laravel Seeder

## Larangan

- Jangan gunakan Prisma
- Jangan gunakan Next.js
- Jangan gunakan React
- Jangan gunakan MongoDB
- Jangan gunakan Firebase sebagai database utama
- Jangan menyimpan password dalam plain text
- Jangan menaruh seluruh business logic di controller
- Jangan membuat aplikasi hanya berupa frontend
- Jangan menyalin source code atau aset The Sounds Project
- Jangan meninggalkan placeholder yang tidak berfungsi

---

# 2. ARSITEKTUR APLIKASI

Gunakan arsitektur terpisah:

```text
/backend
  Laravel REST API

/frontend
  Vue 3 + TypeScript + Vite
```

Frontend dan backend berjalan terpisah dan berkomunikasi melalui REST API menggunakan Axios.

Gunakan pola:

```text
Controller
→ Form Request
→ Service
→ Repository
→ Model
→ Database
```

Ketentuan:

- Controller hanya menangani request dan response.
- Validasi menggunakan Form Request.
- Business logic berada di Service Layer.
- Query kompleks berada di Repository Layer.
- Response API menggunakan API Resource.
- Gunakan transaction untuk proses kritis.

---

# 3. TUJUAN SISTEM

Sistem harus dapat:

1. Menampilkan landing page festival musik.
2. Menampilkan informasi acara.
3. Menampilkan produk dan harga tiket.
4. Menerima order tanpa akun user.
5. Membuat kode order unik.
6. Menghitung total pembayaran otomatis.
7. Menampilkan rekening tujuan transfer.
8. Mencari order melalui email atau nomor HP.
9. Mengunggah bukti pembayaran.
10. Memverifikasi pembayaran oleh admin.
11. Menolak pembayaran dengan alasan.
12. Membuat invoice.
13. Membuat e-ticket.
14. Membuat QR Code unik.
15. Melakukan check-in tiket.
16. Mencegah QR digunakan dua kali.
17. Mengelola kuota tiket.
18. Mencegah overselling.
19. Mengelola laporan penjualan.
20. Mengelola role dan permission admin.
21. Mencatat audit log.
22. Mengirim email notifikasi.
23. Mengelola konten landing page melalui admin.

---

# 4. INFORMASI ACARA AWAL

Gunakan data awal:

- Nama acara: Gathering Masivers & The Sounds Project 2026
- Tanggal utama: 8 Agustus 2026
- Lokasi: Ecopark Ancol, Jakarta
- Peserta: Masivers
- Deskripsi: Gathering Masivers bersama D’MASIV sekaligus menghadiri The Sounds Project 2026
- Zona waktu: Asia/Jakarta
- Status penjualan awal: Draft

Seluruh data harus dapat diubah dari halaman admin dan tidak boleh di-hard-code permanen.

---

# 5. DESAIN LANDING PAGE

Buat landing page festival musik modern yang terinspirasi dari pola tampilan https://thesoundsproject.com/, tetapi jangan menyalin source code, layout identik, gambar, logo, ilustrasi, atau aset mereka.

Gunakan identitas visual Masivers.

## Konsep Visual

- Hero banner besar
- Nuansa festival musik
- Background gelap
- Gradient biru dan ungu
- Aksen neon
- Typography modern
- Card dengan glass effect ringan
- Animasi ringan
- Mobile-first
- Responsive
- CTA jelas
- Performa tetap cepat

## Warna

- Primary: biru elektrik
- Secondary: ungu
- Accent: kuning neon
- Background: biru gelap atau hitam
- Text utama: putih
- Text sekunder: abu terang

---

# 6. HALAMAN PUBLIK

Buat route frontend:

```text
/
 /order-ticket
 /order-success/:orderCode
 /konfirmasi-bayar
 /status-order/:orderCode
 /ticket/:ticketCode
 /invoice/:orderCode
 /syarat-ketentuan
 /kebijakan-privasi
 /faq
```

---

# 7. LANDING PAGE

URL:

```text
/
```

## Navbar

Menu:

- Beranda
- Informasi Acara
- Tiket
- Cara Pemesanan
- FAQ
- Kontak
- Order Tiket
- Konfirmasi Bayar

Navbar harus:

- Sticky
- Responsive
- Memiliki mobile menu
- Memiliki logo Masivers
- Memiliki tombol CTA

## Hero Section

Tampilkan:

- Banner desktop dan mobile
- Logo Masivers
- Nama acara
- Tanggal
- Lokasi
- Countdown
- Deskripsi singkat
- Tombol Order Tiket
- Tombol Konfirmasi Bayar

Banner harus dapat diunggah melalui admin.

## Dua Card Utama

Tampilkan dua card besar di bagian depan.

### Card Order Tiket

Isi:

- Icon tiket
- Judul Order Tiket
- Deskripsi singkat
- Harga tiket aktif
- Sisa kuota
- Tombol Pesan Sekarang

### Card Konfirmasi Bayar

Isi:

- Icon pembayaran
- Judul Konfirmasi Bayar
- Deskripsi singkat
- Tombol Cek Status Pembayaran

## Section Tiket

Tampilkan:

- Nama tiket
- Tanggal berlaku
- Harga normal
- Harga promo
- Kuota tersisa
- Maksimal pembelian
- Status tiket
- Tombol beli

Status tiket:

- DRAFT
- COMING_SOON
- AVAILABLE
- SOLD_OUT
- CLOSED

## Informasi Acara

Tampilkan:

- Nama acara
- Tanggal
- Lokasi
- Peta
- Deskripsi gathering
- Informasi D’MASIV
- Ketentuan peserta
- Kontak panitia

## Cara Pemesanan

1. Pilih tiket.
2. Isi data pemesan.
3. Dapatkan kode order.
4. Transfer pembayaran.
5. Cari order melalui email atau nomor HP.
6. Upload bukti pembayaran.
7. Tunggu verifikasi admin.
8. Download e-ticket.

## FAQ

Data FAQ dikelola admin.

## Footer

Tampilkan:

- Logo
- Alamat
- WhatsApp
- Email
- Instagram
- Syarat dan ketentuan
- Kebijakan privasi
- Copyright

---

# 8. HALAMAN ORDER TIKET

URL:

```text
/order-ticket
```

## Data Pemesan

Field wajib:

- Nama lengkap
- Nomor handphone
- Email
- Alamat lengkap
- Provinsi
- Kota atau kabupaten
- Jenis tiket
- Jumlah tiket

## Validasi

### Nama

- Wajib
- Minimum 3 karakter
- Maksimum 150 karakter

### Nomor Handphone

- Wajib
- Mendukung format 08, 628, dan +628
- Normalisasi menjadi 628xxxxxxxxx
- Hanya angka setelah normalisasi

### Email

- Wajib
- Format valid
- Lowercase
- Trim

### Alamat

- Wajib
- Minimum 10 karakter
- Maksimum 1.000 karakter

### Provinsi dan Kota

- Gunakan dependent dropdown
- Kota berubah berdasarkan provinsi
- Data wilayah disimpan pada MySQL

### Jumlah Tiket

Gunakan komponen:

```text
[-] 1 [+]
```

Aturan:

- Minimum 1
- Maksimum mengikuti admin
- Tidak boleh melebihi kuota
- Tombol minus nonaktif saat jumlah 1
- Tombol plus nonaktif saat batas tercapai
- Total berubah realtime

---

# 9. RINGKASAN ORDER

Tampilkan:

- Nama tiket
- Harga per tiket
- Jumlah
- Subtotal
- Diskon
- Biaya admin
- Kode unik jika digunakan
- Grand total

Gunakan format rupiah Indonesia.

Seluruh harga wajib dihitung ulang oleh backend.

Jangan percaya harga, subtotal, atau grand total yang dikirim frontend.

User wajib menyetujui:

- Data yang dimasukkan benar
- Syarat dan ketentuan
- Batas waktu pembayaran
- Kebijakan privasi

---

# 10. PROSES ORDER

Saat order dibuat:

1. Validasi input.
2. Ambil harga dari database.
3. Validasi tiket aktif.
4. Validasi kuota.
5. Mulai database transaction.
6. Lock produk tiket menggunakan `lockForUpdate()`.
7. Hitung sisa kuota.
8. Simpan customer.
9. Buat order.
10. Buat order item.
11. Tambah reserved quantity.
12. Buat kode order.
13. Tentukan batas pembayaran.
14. Simpan snapshot harga.
15. Simpan rekening bank tujuan.
16. Commit.
17. Kirim email.
18. Arahkan ke halaman sukses.

Format order:

```text
MSV-TSP-2026-000001
```

Gunakan tabel sequence dan transaction agar nomor tidak ganda.

---

# 11. HALAMAN SUKSES ORDER

URL:

```text
/order-success/:orderCode
```

Tampilkan:

- Pesanan berhasil
- Kode order
- Nama pemesan
- Email
- Nomor HP
- Produk tiket
- Jumlah tiket
- Harga satuan
- Total
- Batas pembayaran
- Status
- Rekening tujuan
- Tombol salin rekening
- Tombol salin nominal
- Tombol download invoice
- Tombol konfirmasi pembayaran

---

# 12. KONFIRMASI BAYAR

URL:

```text
/konfirmasi-bayar
```

User dapat mencari order menggunakan:

- Nomor handphone
- Email

Tambahkan:

- Pilihan metode pencarian
- Rate limiting
- CAPTCHA
- Data masking

Jika hasil ditemukan, tampilkan:

- Kode order yang disamarkan
- Tanggal order
- Nama tiket
- Jumlah
- Total
- Status
- Tombol lihat

Untuk membuka detail, minta kode order lengkap.

Jangan tampilkan alamat lengkap atau bukti transfer pada pencarian publik.

---

# 13. STATUS ORDER

Gunakan status:

```text
PENDING_PAYMENT
WAITING_VERIFICATION
PAID
PAYMENT_REJECTED
EXPIRED
CANCELLED
REFUNDED
```

Label Indonesia:

- Menunggu Pembayaran
- Menunggu Verifikasi
- Lunas
- Pembayaran Ditolak
- Kedaluwarsa
- Dibatalkan
- Dikembalikan

---

# 14. JIKA BELUM DIBAYAR

Tampilkan:

- Nama bank
- Nomor rekening
- Nama pemilik rekening
- Total transfer
- Batas waktu
- Tombol salin rekening
- Tombol salin nominal
- Tombol Saya Sudah Bayar

---

# 15. UPLOAD BUKTI PEMBAYARAN

Field:

- Nama pemilik rekening pengirim
- Bank pengirim
- Tanggal transfer
- Nominal transfer
- Upload bukti
- Catatan opsional

Format:

- JPG
- JPEG
- PNG
- PDF

Maksimum:

```text
5 MB
```

Keamanan:

- Validasi MIME type
- Validasi extension
- Validasi ukuran
- Nama file UUID
- Simpan di private storage
- Jangan simpan di public
- Jangan izinkan executable file

Lokasi:

```text
storage/app/private/payment-proofs
```

Setelah upload:

1. Simpan payment confirmation.
2. Ubah order menjadi WAITING_VERIFICATION.
3. Catat waktu.
4. Simpan histori.
5. Kirim email.
6. Beri notifikasi admin.
7. Nonaktifkan upload baru selama menunggu verifikasi.

---

# 16. PEMBAYARAN DITOLAK

Tampilkan:

- Status ditolak
- Alasan penolakan
- Tombol upload ulang
- Batas waktu

Simpan seluruh histori bukti pembayaran.

Jangan hapus bukti pembayaran lama.

---

# 17. PEMBAYARAN LUNAS

Tampilkan:

- Status pembayaran diterima
- Nomor invoice
- Tanggal pembayaran
- Total
- Daftar tiket
- Download invoice
- Download e-ticket
- QR Code

---

# 18. E-TICKET

Setiap jumlah tiket menghasilkan tiket terpisah.

Jika membeli 4 tiket, buat 4 tiket unik.

Setiap tiket berisi:

- Kode tiket
- QR Code
- Nama acara
- Nama pemesan
- Jenis tiket
- Nomor urut
- Tanggal
- Lokasi
- Status
- Informasi QR sekali pakai

Format:

```text
TSP-MSV-A1B2C3D4
```

Status:

```text
ACTIVE
USED
CANCELLED
REFUNDED
```

QR tidak boleh berisi ID database mentah.

Gunakan random token atau signed token.

---

# 19. LOGIN ADMIN

URL:

```text
/admin/login
```

Field:

- Email
- Password
- Ingat saya
- Login
- Lupa password

Gunakan:

- Laravel Sanctum
- Bcrypt atau Argon2id
- Rate limiting
- Lock akun sementara
- Session regeneration
- Catat IP
- Catat user agent
- Catat login terakhir
- Logout
- Forgot password
- Reset password

Tidak ada registrasi admin publik.

---

# 20. ROLE DAN PERMISSION

## Super Admin

Akses penuh.

## Admin Finance

Dapat:

- Melihat order
- Memverifikasi pembayaran
- Menolak pembayaran
- Melihat laporan

Tidak dapat:

- Menghapus super admin
- Mengubah keamanan inti

## Admin Ticketing

Dapat:

- Melihat tiket
- Check-in
- Melihat peserta
- Cetak peserta

Tidak dapat:

- Memverifikasi pembayaran
- Mengubah rekening

## Viewer

Hanya melihat dashboard dan laporan.

---

# 21. MENU ADMIN

1. Dashboard
2. Pesanan
3. Verifikasi Pembayaran
4. Tiket
5. Check-in
6. Produk Tiket
7. Data Acara
8. Rekening Bank
9. FAQ
10. Laporan
11. Admin
12. Role dan Permission
13. Pengaturan
14. Audit Log

Gunakan:

- Sidebar
- Header
- Breadcrumb
- Profil admin
- Logout
- Responsive layout

---

# 22. DASHBOARD ADMIN

Tampilkan card:

- Total order
- Order hari ini
- Menunggu pembayaran
- Menunggu verifikasi
- Pembayaran diterima
- Pembayaran ditolak
- Order expired
- Tiket terjual
- Tiket tersedia
- Total pendapatan
- Total check-in

Grafik:

- Penjualan harian
- Pendapatan harian
- Status order
- Penjualan per tiket
- Order per provinsi
- Check-in per jam

Tabel:

- Order terbaru
- Pembayaran terbaru
- Pembayaran menunggu verifikasi

---

# 23. HALAMAN PESANAN

Kolom:

- Kode order
- Tanggal
- Nama
- Nomor HP
- Email
- Provinsi
- Kota
- Tiket
- Jumlah
- Total
- Status
- Batas pembayaran
- Aksi

Fitur:

- Search
- Filter
- Sorting
- Pagination server-side
- Export Excel
- Export CSV
- Export PDF
- Print
- Detail
- Batalkan
- Perpanjang waktu
- Kirim ulang email
- Catatan admin

---

# 24. VERIFIKASI PEMBAYARAN ADMIN

Tampilkan:

- Kode order
- Nama
- Tanggal upload
- Nama pengirim
- Bank pengirim
- Nominal transfer
- Total order
- Selisih
- Bukti bayar
- Status
- Aksi

Saat approve:

1. Mulai transaction.
2. Lock order.
3. Pastikan belum paid.
4. Pastikan payment belum verified.
5. Ubah payment menjadi VERIFIED.
6. Ubah order menjadi PAID.
7. Simpan admin pemeriksa.
8. Simpan waktu.
9. Finalisasi kuota.
10. Buat invoice.
11. Buat tiket.
12. Buat QR.
13. Catat histori.
14. Catat audit.
15. Commit.
16. Kirim email.

Proses harus idempotent dan tidak membuat tiket ganda.

Saat reject:

- Admin wajib mengisi alasan
- Payment menjadi REJECTED
- Order menjadi PAYMENT_REJECTED
- User dapat upload ulang
- Kirim email
- Simpan histori

---

# 25. PRODUK TIKET

Field:

- Nama
- Slug
- Deskripsi
- Tanggal berlaku
- Harga normal
- Harga promo
- Jadwal promo
- Kuota
- Maksimal per order
- Biaya admin
- Gambar
- Status
- Aktif
- Urutan

Harga order lama tidak boleh berubah saat harga baru diubah.

Simpan snapshot harga dalam order item.

---

# 26. REKENING BANK

Field:

- Nama bank
- Nomor rekening
- Nama pemilik
- Cabang
- Logo
- Instruksi
- Rekening utama
- Aktif

Sistem dapat memiliki beberapa rekening.

Satu rekening menjadi default.

Order menyimpan rekening yang digunakan saat order dibuat.

---

# 27. CHECK-IN

URL:

```text
/admin/check-in
```

Fitur:

- Scan QR kamera
- Input manual kode
- Validasi tiket
- Tampilkan nama
- Tampilkan jenis tiket
- Tampilkan status
- Tombol check-in

Saat check-in:

1. Mulai transaction.
2. Lock tiket.
3. Pastikan ACTIVE.
4. Ubah menjadi USED.
5. Simpan waktu.
6. Simpan petugas.
7. Simpan gate.
8. Simpan IP.
9. Commit.

Cegah check-in ganda.

---

# 28. DATABASE MYSQL

Buat migration untuk tabel:

- users
- roles
- permissions
- permission_role
- events
- ticket_products
- customers
- orders
- order_items
- bank_accounts
- payment_confirmations
- tickets
- ticket_checkins
- order_status_histories
- faqs
- settings
- email_logs
- audit_logs
- number_sequences
- provinces
- cities

Gunakan:

- BIGINT UNSIGNED
- Foreign key
- Unique index
- Index
- DECIMAL untuk uang
- Soft delete
- InnoDB
- utf8mb4
- Transaction

## Kolom Penting Orders

```text
id
order_code
invoice_number
event_id
customer_id
bank_account_id
order_status
subtotal
discount_amount
admin_fee
unique_code
grand_total
currency
expires_at
paid_at
verified_at
verified_by
cancelled_at
notes
admin_notes
terms_accepted_at
privacy_accepted_at
created_ip
user_agent
created_at
updated_at
deleted_at
```

## Kolom Payment Confirmations

```text
id
order_id
sender_name
sender_bank
transfer_date
transferred_amount
proof_file_path
proof_original_name
proof_mime_type
proof_size
status
rejection_reason
customer_notes
admin_notes
submitted_at
verified_at
verified_by
created_at
updated_at
```

## Kolom Tickets

```text
id
order_id
order_item_id
ticket_code
qr_token_hash
sequence_number
holder_name
status
issued_at
used_at
checked_in_by
created_at
updated_at
```

---

# 29. API

Prefix:

```text
/api/v1
```

## Public

```text
GET  /api/v1/public/event
GET  /api/v1/public/ticket-products
GET  /api/v1/public/faqs
GET  /api/v1/public/provinces
GET  /api/v1/public/provinces/{province}/cities
POST /api/v1/orders
POST /api/v1/orders/search
POST /api/v1/orders/verify
GET  /api/v1/orders/{orderCode}
POST /api/v1/orders/{orderCode}/payment-confirmations
GET  /api/v1/orders/{orderCode}/invoice
GET  /api/v1/orders/{orderCode}/tickets
GET  /api/v1/tickets/{ticketCode}/download
```

## Auth

```text
POST /api/v1/admin/auth/login
POST /api/v1/admin/auth/logout
GET  /api/v1/admin/auth/me
POST /api/v1/admin/auth/forgot-password
POST /api/v1/admin/auth/reset-password
```

## Admin

```text
GET  /api/v1/admin/dashboard
GET  /api/v1/admin/orders
GET  /api/v1/admin/orders/{order}
PATCH /api/v1/admin/orders/{order}
POST /api/v1/admin/orders/{order}/cancel
POST /api/v1/admin/orders/{order}/extend-expiry
GET  /api/v1/admin/payments
GET  /api/v1/admin/payments/{payment}
POST /api/v1/admin/payments/{payment}/approve
POST /api/v1/admin/payments/{payment}/reject
POST /api/v1/admin/check-in
GET  /api/v1/admin/reports/sales
GET  /api/v1/admin/reports/payments
GET  /api/v1/admin/reports/tickets
GET  /api/v1/admin/reports/export
GET  /api/v1/admin/audit-logs
```

Gunakan API Resource untuk CRUD:

- events
- ticket-products
- bank-accounts
- faqs
- users
- roles
- permissions

---

# 30. STRUKTUR BACKEND

```text
backend/
├── app/
│   ├── Enums/
│   ├── Events/
│   ├── Exports/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/
│   ├── Mail/
│   ├── Models/
│   ├── Notifications/
│   ├── Policies/
│   ├── Repositories/
│   ├── Services/
│   └── Support/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
└── tests/
```

Service:

- OrderService
- PaymentService
- TicketService
- CheckinService
- InvoiceService
- EmailService
- ReportService
- NumberSequenceService
- QuotaService
- UploadService
- AuditLogService

---

# 31. STRUKTUR FRONTEND

```text
frontend/
├── src/
│   ├── api/
│   ├── assets/
│   ├── components/
│   ├── composables/
│   ├── layouts/
│   ├── router/
│   ├── stores/
│   ├── types/
│   ├── utils/
│   ├── validations/
│   └── views/
├── public/
├── vite.config.ts
└── package.json
```

Pinia stores:

- authStore
- eventStore
- ticketProductStore
- orderStore
- paymentStore
- notificationStore
- dashboardStore

---

# 32. KEAMANAN

Implementasikan:

- Laravel Sanctum
- CSRF
- CORS whitelist
- Rate limiting
- Form Request validation
- Policy
- Gate
- SQL injection protection
- XSS protection
- Secure headers
- Password hashing
- Session regeneration
- Brute force protection
- Private file storage
- Signed URL
- Audit log
- IDOR protection
- Data masking
- CAPTCHA
- HTTPS

---

# 33. KUOTA TIKET

Saat order:

```text
available = quota - reserved_quantity - sold_quantity
```

Gunakan `lockForUpdate()`.

Saat order expired:

- Kurangi reserved quantity
- Ubah status expired
- Simpan histori

Saat paid:

- Kurangi reserved quantity
- Tambah sold quantity
- Buat tiket
- Ubah status paid

Jangan izinkan nilai negatif.

---

# 34. SCHEDULER

Buat command:

```text
php artisan orders:expire
```

Jalankan setiap 5 menit.

Fungsi:

- Cari PENDING_PAYMENT
- Lewat expires_at
- Lock order
- Ubah EXPIRED
- Lepaskan kuota
- Catat histori
- Kirim email
- Idempotent

---

# 35. EMAIL

Buat template email:

1. Order berhasil
2. Pengingat pembayaran
3. Order hampir expired
4. Order expired
5. Bukti pembayaran diterima
6. Pembayaran disetujui
7. Pembayaran ditolak
8. E-ticket diterbitkan
9. Order dibatalkan
10. Refund

Gunakan Queue.

---

# 36. LAPORAN

Sediakan:

- Penjualan harian
- Mingguan
- Bulanan
- Per tiket
- Per provinsi
- Per kota
- Per status
- Pembayaran diterima
- Pembayaran ditolak
- Selisih nominal
- Tiket aktif
- Tiket digunakan
- Check-in per jam

Export:

- Excel
- CSV
- PDF
- Print

---

# 37. TESTING

Gunakan:

- Pest atau PHPUnit
- Vitest
- Vue Test Utils
- Playwright

Test wajib:

- Order
- Perhitungan harga
- Kuota
- Overselling
- Expired order
- Upload bukti
- Approve payment
- Duplicate approval
- Reject payment
- Generate ticket
- QR check-in
- Duplicate check-in
- Login admin
- Authorization
- Upload berbahaya
- Pencarian order
- Data masking

---

# 38. DEPLOYMENT

Sediakan dokumentasi:

- VPS Ubuntu atau AlmaLinux
- Plesk
- Nginx
- PHP-FPM
- MySQL
- Supervisor Queue
- Laravel Scheduler
- Node build Vue
- SSL
- Environment production
- Permission storage
- Migration
- Seeder
- Backup database
- Backup file pembayaran

---

# 39. ENVIRONMENT

Backend `.env.example`:

```env
APP_NAME="Masivers Ticketing"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=masivers_ticketing
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Masivers Ticketing"

QUEUE_CONNECTION=database
UPLOAD_MAX_SIZE_MB=5

SEED_ADMIN_NAME=
SEED_ADMIN_EMAIL=
SEED_ADMIN_PASSWORD=
```

---

# 40. OUTPUT WAJIB

Hasilkan:

1. Backend Laravel lengkap
2. Frontend Vue 3 lengkap
3. Migration
4. Seeder
5. Model
6. Relationship
7. Repository
8. Service
9. Controller
10. Form Request
11. API Resource
12. Policy
13. Middleware
14. Queue dan Job
15. Scheduler
16. Email
17. PDF invoice
18. PDF e-ticket
19. QR Code
20. Landing page
21. Order page
22. Konfirmasi pembayaran
23. Dashboard admin
24. Verifikasi pembayaran
25. Check-in
26. Laporan
27. Audit log
28. Role dan permission
29. `.env.example`
30. README
31. API documentation
32. Deployment documentation
33. Backup documentation
34. Testing

---

# 41. ACCEPTANCE CRITERIA

Aplikasi selesai jika:

- Landing profesional dan responsive
- Card Order Tiket dan Konfirmasi Bayar tampil
- Harga dapat diatur admin
- User dapat order tanpa akun
- Jumlah menggunakan plus dan minus
- Harga dihitung backend
- Kode order unik
- User dapat mencari dengan HP atau email
- Rekening dan nominal tampil
- User dapat upload bukti
- Admin dapat approve dan reject
- Invoice dan e-ticket dibuat
- QR unik
- QR tidak dapat digunakan dua kali
- Kuota aman
- Order expired melepaskan kuota
- Login aman
- Role berfungsi
- Bukti bayar private
- Audit log aktif
- Export laporan berjalan
- Laravel, Vue 3, dan MySQL digunakan
- Prisma tidak digunakan
- Semua tombol berfungsi
- Tidak ada placeholder kosong
- Dokumentasi lengkap

---

# 42. PERINTAH AKHIR

Bangun aplikasi ini secara lengkap dan bertahap.

Jangan hanya memberikan penjelasan.

Buat source code yang dapat dijalankan.

Setelah setiap modul:

1. Periksa error.
2. Jalankan migration.
3. Jalankan test.
4. Pastikan route berfungsi.
5. Pastikan TypeScript tidak error.
6. Pastikan build Vue berhasil.
7. Pastikan API terhubung.
8. Pastikan responsive.
9. Dokumentasikan perubahan.
10. Lanjutkan ke modul berikutnya.

Prioritaskan:

- Keamanan
- Konsistensi transaksi
- Kualitas kode
- Kecepatan
- Kemudahan maintenance
- Tampilan profesional
- Stabilitas production

**Jangan gunakan Prisma dalam bagian mana pun dari proyek.**
