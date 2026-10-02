# TugasWeb-Pertemuan8-CRUD Inventaris Barang (PHP Native + PDO + MySQL)

Aplikasi manajemen inventaris barang berbasis **PHP Native**, **PDO**, dan **MySQL/MariaDB**.
Dibuat untuk **Tugas Rutin 8** (Pertemuan 8 - CRUD).

## Fitur

- **Create**: tambah produk dengan dropdown Kategori dan Supplier
- **Read**: daftar produk dengan `JOIN` ke tabel `categories` dan `suppliers`
- **Update**: form edit yang sudah terisi data lama (pre-filled)
- **Delete**: hapus produk dengan konfirmasi, memakai **database transaction** dan dicatat di tabel `stock_logs`
- **Pencarian**: cari berdasarkan nama produk, kategori, atau supplier
- **Flash message** berbasis session (pesan sukses/gagal muncul sekali setelah redirect)

## Teknologi

| Komponen | Keterangan |
|---|---|
| Bahasa | PHP 8.x (native, tanpa framework) |
| Database | MySQL / MariaDB (via XAMPP) |
| Koneksi | PDO dengan **Singleton Pattern** |
| Tampilan | HTML + CSS (`assets/style.css`) |

## Struktur Folder

```
inventaris/
├── config/
│   ├── database.php    # Koneksi PDO (Singleton)
│   └── flash.php       # Helper flash message (session)
├── assets/
│   └── style.css       # Styling
├── index.php           # Daftar produk + pencarian
├── create.php          # Form tambah produk
├── edit.php            # Form edit produk
├── delete.php          # Proses hapus (transaction + log)
├── schema.sql          # Struktur database + data awal
└── README.md
```

## Desain Database

Database: `inventaris_db`

| Tabel | Fungsi | Seed |
|---|---|---|
| `categories` | Kategori barang | 5 data |
| `suppliers` | Data pemasok | 5 data |
| `products` | Data produk (FK ke `categories` dan `suppliers`) | 5 data |
| `stock_logs` | Log aktivitas penghapusan produk | 5 data |

**Relasi (Foreign Key):**

- `products.category_id` → `categories.id` (`ON DELETE RESTRICT`, `ON UPDATE CASCADE`)
- `products.supplier_id` → `suppliers.id` (`ON DELETE RESTRICT`, `ON UPDATE CASCADE`)

`RESTRICT` mencegah kategori atau supplier dihapus selama masih dipakai produk, sehingga integritas data terjaga.

**Catatan desain `stock_logs`:** tabel ini sengaja menyimpan `product_name` (teks) dan **tidak** memakai foreign key ke `products`. Alasannya, log harus tetap ada setelah produk dihapus. Jika memakai FK, riwayat penghapusan justru akan hilang atau menghalangi proses hapus. Ini keputusan desain yang disengaja untuk tabel audit, bukan pelanggaran normalisasi (3NF).

## Cara Menjalankan dengan XAMPP

### Prasyarat

- [XAMPP](https://www.apachefriends.org/) dengan PHP 8.0 atau lebih baru
- Browser web

### Langkah 1: Salin proyek ke `htdocs`

Clone atau download repository ini, lalu letakkan foldernya di:

```
C:\xampp\htdocs\inventaris\
```

(Pastikan nama foldernya `inventaris`, dan file `index.php` berada langsung di dalamnya.)

```bash
cd C:\xampp\htdocs
git clone https://github.com/<username>/TugasWeb-Pertemuan8-CRUD.git inventaris
```

### Langkah 2: Jalankan Apache dan MySQL

1. Buka **XAMPP Control Panel**.
2. Klik **Start** pada **Apache** dan **MySQL**.
3. Pastikan keduanya berwarna hijau.

### Langkah 3: Import database

1. Buka `http://localhost/phpmyadmin`.
2. Klik tab **Import** pada halaman utama (jangan masuk ke database tertentu).
3. Klik **Choose File**, pilih `schema.sql` dari folder proyek.
4. Klik **Import** di bagian bawah halaman.
5. Database `inventaris_db` akan muncul di panel kiri berisi 4 tabel.

> `schema.sql` aman di-import berulang kali (memakai `IF NOT EXISTS` dan `INSERT IGNORE`).
> Untuk mengulang dari awal, hapus database `inventaris_db` terlebih dahulu (Operations → Drop the database), lalu import lagi.

### Langkah 4: Cek konfigurasi koneksi

Pengaturan default di `config/database.php` sesuai bawaan XAMPP:

```php
$host = 'localhost';
$db   = 'inventaris_db';
$user = 'root';
$pass = '';
```

Jika password MySQL Anda berbeda, ubah nilai `$pass` di file tersebut.

### Langkah 5: Buka aplikasi

```
http://localhost/inventaris/
```

## Panduan Pengujian untuk Dosen

| No | Skenario | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| 1 | **Read + JOIN** | Buka `http://localhost/inventaris/` | 5 produk tampil lengkap dengan nama kategori dan supplier |
| 2 | **Create** | Klik **+ Tambah Produk**, isi semua kolom, klik **Simpan Produk** | Kembali ke daftar, muncul pesan hijau, produk baru ada di daftar |
| 3 | **Validasi create** | Kosongkan nama atau isi harga 0, lalu simpan | Muncul pesan error merah, isian lain tidak hilang |
| 4 | **Update** | Klik **Edit** pada satu produk | Form sudah terisi data lama |
| 5 | | Ubah data, klik **Perbarui Produk** | Muncul pesan hijau, data berubah di daftar |
| 6 | **Delete** | Klik **Hapus** | Muncul dialog konfirmasi |
| 7 | | Klik **OK** | Produk terhapus, muncul pesan sukses |
| 8 | **Transaction + log** | Buka phpMyAdmin → `inventaris_db` → `stock_logs` | Ada baris baru berisi nama produk yang dihapus (`DELETE_PRODUCT`) |
| 9 | **Flash message** | Setelah aksi sukses, tekan **F5** | Pesan tidak muncul lagi |
| 10 | **Pencarian** | Ketik "Logitech" atau "Medan" lalu klik **Cari** | Daftar tersaring, tombol **Reset** muncul |
| 11 | **Keamanan XSS** | Tambah produk bernama `<script>alert(1)</script>` | Tampil sebagai teks biasa, tidak dieksekusi |
| 12 | **SQL Injection** | Cari dengan `' OR '1'='1` | Tidak ada error dan tidak ada data bocor (hasil kosong) |

## Penerapan Requirement Tugas

| # | Requirement | Implementasi |
|---|---|---|
| 1 | Database `inventaris_db`, 3 tabel + FK | `schema.sql`: `categories`, `suppliers`, `products` (2 FK) + `stock_logs` |
| 2 | Minimal 5 seed per tabel | Masing-masing tabel berisi 5 data awal |
| 3 | PDO + Singleton | `config/database.php` (konstruktor private, `getInstance()`) |
| 4 | List produk dengan JOIN | `index.php` melakukan JOIN ke `categories` dan `suppliers` |
| 5 | Form create dengan dropdown | `create.php` (dropdown kategori dan supplier dari database) |
| 6 | Update pre-filled dan delete konfirmasi | `edit.php` dan `delete.php` (dialog `confirm()` + method POST) |
| 7 | Semua query input memakai prepared statement | Semua `INSERT`, `UPDATE`, `DELETE`, `SELECT` berparameter memakai `?` |
| 8 | Output memakai `htmlspecialchars()` | Semua data yang ditampilkan di-escape |
| 9 | Flash message (redirect pattern) | `config/flash.php` (session) + redirect Post/Redirect/Get |
| 10 | UI rapi | `assets/style.css` (responsif) |
| Bonus | Transaction pada delete, pencarian | `delete.php` (`beginTransaction`/`commit`/`rollBack`) + log, pencarian di `index.php` |

## Keamanan

- **Prepared statements** dengan `PDO::ATTR_EMULATE_PREPARES = false` (prepared statement asli dari server)
- **Output escaping** dengan `htmlspecialchars()` untuk mencegah XSS
- **Delete via POST**, bukan GET, agar tidak bisa terpicu lewat link
- **Validasi input** di sisi server (nama, kategori, supplier, harga, stok)
- **Error database tidak ditampilkan ke pengguna**, hanya dicatat lewat `error_log()`
- Konfigurasi `root` tanpa password adalah **pengaturan default XAMPP** untuk keperluan pembelajaran lokal, bukan untuk produksi

## Pemecahan Masalah

| Masalah | Penyebab dan solusi |
|---|---|
| "Koneksi database gagal" | MySQL belum menyala di XAMPP, atau database belum di-import |
| `Failed to open stream: config/flash.php` | File `flash.php` belum ada atau salah letak (harus di folder `config/`) |
| Tampilan tanpa style | Pastikan `assets/style.css` ada dan URL memakai `localhost/inventaris/` |
| "headers already sent" | Ada spasi atau baris kosong sebelum `<?php` di salah satu file |
| Port 80 bentrok | Ubah port Apache di XAMPP, lalu akses `localhost:<port>/inventaris/` |

## Pengembang

- Nama: Felipe Maranatha Lumbanraja
- NIM: 4253250014
- Mata Kuliah: Pemrograman Web
