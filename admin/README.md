# E-Sim Store Admin Panel

Admin panel untuk mengelola produk E-Sim Store dengan fitur login, dashboard, dan manajemen produk.

## Fitur

1. **Sistem Login Aman**
   - Autentikasi admin dengan username dan password
   - Proteksi halaman dengan session management
   - CSRF protection untuk keamanan form

2. **Dashboard Admin**
   - Tampilan ringkasan data produk
   - Navigasi mudah ke semua fitur admin
   - Akses cepat ke fungsi-fungsi utama

3. **Manajemen Produk**
   - Tambah produk baru dengan gambar
   - Edit produk yang sudah ada
   - Hapus produk yang tidak diperlukan
   - Validasi input untuk memastikan data yang valid

## Persyaratan Sistem

- PHP 7.4 atau lebih baru
- SQLite3 (digunakan dalam konfigurasi saat ini)
- Web server (Apache/Nginx) atau PHP built-in server
- Browser modern dengan JavaScript enabled

## Instalasi

1. **Siapkan Database**
   - Database SQLite akan dibuat otomatis di `admin/database/esim_store.db`
   - Pastikan direktori `admin/database/` memiliki permission yang benar:
   ```
   chmod 755 admin/database/
   ```

2. **Konfigurasi Koneksi Database**
   - File `config.php` sudah dikonfigurasi menggunakan SQLite
   - Tidak perlu konfigurasi tambahan untuk koneksi database

3. **Siapkan Direktori Upload**
   - Pastikan direktori `uploads/` memiliki permission yang benar:
   ```
   chmod 755 uploads/
   ```

4. **Jalankan Server PHP**
   - Gunakan built-in PHP server untuk pengujian lokal:
   ```
   php -S localhost:8000 -t /path/to/project/root
   ```
   - Akses website di `http://localhost:8000/`

5. **Akses Admin Panel**
   - Buka browser dan akses URL admin panel:
     `http://localhost:8000/admin/login.php`
   - Login dengan kredensial default:
     - Username: `admin`
     - Password: `admin123`
   - **PENTING**: Segera ubah password default setelah login pertama kali!

## Struktur File

```
admin/
├── config.php                # Konfigurasi database dan fungsi utilitas
├── login.php                 # Halaman login admin
├── dashboard.php             # Dashboard utama admin
├── add_product.php           # Form tambah produk baru
├── manage_products.php       # Halaman kelola produk (list, edit, hapus)
├── edit_product.php          # Form edit produk
├── process_add_product.php   # Proses form tambah produk
├── logout.php                # Script logout
├── database.sql              # SQL untuk setup database (jika menggunakan MySQL)
├── database/                 # Direktori database SQLite
│   └── esim_store.db         # File database SQLite
├── uploads/                  # Direktori untuk menyimpan gambar produk
└── README.md                 # Dokumentasi (file ini)
```

## Keamanan

Admin panel ini dilengkapi dengan beberapa fitur keamanan:

1. Password hashing menggunakan algoritma bcrypt
2. Proteksi CSRF untuk semua form
3. Validasi dan sanitasi input untuk mencegah SQL Injection dan XSS
4. Session management yang aman
5. Pembatasan akses file upload (hanya gambar)

## Kredensial Default

**PERINGATAN**: Kredensial ini hanya untuk setup awal. Segera ubah setelah instalasi!

- **Username**: admin
- **Password**: admin123

## Pengembangan Lanjutan

Beberapa ide untuk pengembangan lebih lanjut:

1. Tambahkan fitur manajemen kategori produk
2. Implementasikan sistem manajemen pesanan
3. Tambahkan dashboard analitik dengan grafik
4. Buat sistem notifikasi untuk admin
5. Tambahkan fitur pencarian dan filter produk
