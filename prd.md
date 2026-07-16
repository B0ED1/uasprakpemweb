# Product Requirement Document (PRD) - Web Koleksi Figure (FiguSphere)

## 1. Deskripsi Proyek
**FiguSphere** adalah aplikasi manajemen koleksi *action figure* berbasis web yang dirancang untuk membantu para kolektor mendata, memantau, dan mengelola inventaris koleksi mereka secara digital. [cite_start]Aplikasi ini mengimplementasikan fungsi CRUD (Create, Read, Update, Delete) penuh menggunakan **Native PHP** [cite: 10, 16] [cite_start]dan dirancang dengan antarmuka yang responsif menggunakan **Bootstrap / Tailwind CSS** [cite: 13, 17] [cite_start]agar nyaman diakses melalui perangkat desktop maupun mobile[cite: 11].

---

## 2. Fitur Minimum & Ruang Lingkup (Scope)
[cite_start]Sesuai dengan ketentuan UAS, fitur utama yang diimplementasikan pada dashboard meliputi[cite: 14]:
* **Create (Tambah Koleksi):** Form untuk menambahkan data *figure* baru (Nama Figure, Karakter, Seri/Anime, Produsen/Brand, Ukuran, Harga, dan Foto Figure).
* [cite_start]**Read (Tampilkan Koleksi):** Halaman dashboard utama yang menampilkan daftar seluruh koleksi dalam bentuk tabel responsif atau *card layout* yang rapi[cite: 11, 14].
* [cite_start]**Update (Edit Data):** Fitur untuk memperbarui informasi detail *figure* jika ada kesalahan input atau perubahan status koleksi[cite: 14].
* [cite_start]**Delete (Hapus Koleksi):** Fitur untuk menghapus data *figure* dari sistem yang dilengkapi dengan konfirmasi pop-up demi keamanan data[cite: 14].

---

## 3. Spesifikasi Teknologi (Tech Stack)
[cite_start]Teknologi yang digunakan dalam pembangunan aplikasi ini memenuhi standar parameter ujian[cite: 15]:
* [cite_start]**Bahasa Pemrograman:** Native PHP [cite: 16]
* [cite_start]**Database:** MySQL (sebagai basis data utama) [cite: 18]
* [cite_start]**Desain Antarmuka:** Bootstrap atau Tailwind CSS (pilih salah satu) [cite: 17]
* **Server Lokal:** XAMPP / Laragon (Apache & MySQL)

---

## 4. Struktur Database (MySQL)
Database bernama `db_figusphere` dengan satu tabel utama yaitu `tb_figures`.

```sql
CREATE DATABASE db_figusphere;

USE db_figusphere;

CREATE TABLE tb_figures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_figure VARCHAR(255) NOT NULL,
    karakter VARCHAR(100) NOT NULL,
    seri_anime VARCHAR(150) NOT NULL,
    produsen VARCHAR(100) NOT NULL,
    skala_ukuran VARCHAR(50),
    harga INT,
    foto_figure VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);