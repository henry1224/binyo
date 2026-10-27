---
title: "Product Requirements"
subtitle: "Visi, pengguna, ruang lingkup, dan ukuran keberhasilan"
author: "Binyo POS"
date: "2026-10-27"
status: "Draft"
---

# Product Requirements

## 1. Visi

Binyo membantu pemilik usaha mengetahui apa yang terjual, uang yang diterima, dan stok yang berubah tanpa menu berlapis atau istilah teknis yang tidak perlu.

Prinsip produk:

- Cepat saat dipakai kasir.
- Jelas saat dipantau pemilik.
- Aman saat koneksi buruk.
- Mudah dipelajari tanpa pelatihan panjang.
- Cukup fleksibel untuk retail dan F&B ringan, tanpa menjadi ERP penuh.

## 2. Pengguna

### Pemilik

Membutuhkan ringkasan penjualan, laba kotor perkiraan, stok, kas, performa outlet, dan kontrol akses.

### Manajer outlet

Mengelola produk tersedia, harga yang diizinkan, staf, stok, pembelian, koreksi, dan penutupan shift.

### Kasir

Membuka shift, membuat pesanan, menerima pembayaran, mencetak atau membagikan struk, serta melihat transaksi sendiri.

### Staf gudang

Menerima barang, menghitung stok, melakukan transfer, dan mencatat penyesuaian dengan alasan.

## 3. Sasaran MVP

- Penyiapan outlet, perangkat, pengguna, dan hak akses.
- Katalog produk, kategori, varian/SKU, modifier, harga, dan ketersediaan outlet.
- Penjualan mobile dengan tunai dan metode non-tunai yang dicatat manual.
- Shift kasir, pergerakan kas, void, refund, dan audit tindakan sensitif.
- Stok masuk, keluar, penjualan, opname, penyesuaian, dan peringatan stok rendah.
- Pemasok, purchase order sederhana, dan penerimaan barang.
- Dashboard dan laporan operasional utama.
- Operasi offline sementara dengan sinkronisasi aman.

## 4. Bukan MVP

- General ledger dan laporan akunting lengkap.
- Payroll dan penjadwalan staf.
- Waralaba dan settlement antarbadan usaha.
- Komisi penjualan kompleks.
- Loyalty bertingkat, kupon kompleks, dan penukaran poin.
- Broadcast WhatsApp dan marketing automation.
- Integrasi langsung GrabFood/GoFood.
- Toko online, self-order kiosk, dan customer display.
- Split payment, tab pelanggan, dan pembayaran tempo.
- Manajemen meja, reservasi, kursus hidangan, dan kitchen display.

Fitur ditambahkan setelah kebutuhan nyata muncul pada pilot.

## 5. Kebutuhan fungsional

### Organisasi dan akses

- Satu bisnis memiliki satu atau lebih outlet.
- Pengguna dapat memiliki akses berbeda per outlet.
- Perangkat POS didaftarkan dan dapat dicabut aksesnya.
- Tindakan sensitif memakai izin terpisah: diskon, ubah harga, void, refund, koreksi stok, dan buka laci.

### Katalog

- Produk memiliki nama, kategori, gambar opsional, pajak, status, dan urutan tampil.
- Varian/SKU adalah unit yang dijual dan dapat memiliki barcode, harga, biaya, serta stok.
- Produk tanpa pilihan tetap memiliki satu varian default agar model transaksi konsisten.
- Modifier dapat wajib atau opsional dan dapat menambah harga.
- Produk dapat dibatasi untuk outlet tertentu.
- Produk resep dapat mengurangi beberapa bahan baku ketika terjual.

### Penjualan

- Kasir dapat mencari produk, memilih kategori, memindai barcode, dan memakai favorit.
- Keranjang mendukung jumlah, catatan, modifier, diskon berizin, pajak, dan pelanggan opsional.
- Harga, pajak, nama produk, dan biaya disalin ke baris transaksi sebagai snapshot.
- Nomor struk unik per outlet dan tetap dapat dibuat saat offline.
- Transaksi selesai tidak dapat diedit diam-diam; koreksi memakai void atau refund dengan alasan.

### Pembayaran

- MVP mencatat tunai dan metode non-tunai manual.
- Kembalian dihitung hanya untuk tunai.
- Pembayaran gagal atau belum terkonfirmasi tidak boleh menyelesaikan transaksi.
- Binyo tidak menyimpan data kartu.
- Integrasi QRIS dinamis memakai penyedia pembayaran pada fase terpisah.

### Shift dan kas

- Kasir membuka shift dengan saldo awal.
- Kas masuk/keluar non-penjualan wajib memiliki kategori, nominal, dan catatan.
- Penutupan shift membandingkan kas sistem dan kas fisik.
- Selisih kas disimpan, bukan ditimpa.

### Persediaan

- Semua perubahan stok menghasilkan pergerakan dengan sumber dan pelaku.
- Stok tidak boleh diubah langsung tanpa histori.
- Koreksi stok membutuhkan alasan.
- Produk dapat mengizinkan atau melarang stok minus sesuai pengaturan bisnis.
- Stok opname membandingkan jumlah sistem dan fisik sebelum menghasilkan penyesuaian.

### Pembelian

- Purchase order memiliki status draft, dikirim, diterima sebagian, selesai, atau dibatalkan.
- Penerimaan dapat dilakukan sebagian.
- Biaya pembelian terakhir dan rata-rata bergerak dapat dihitung dari penerimaan.
- Pembatalan penerimaan tidak menghapus histori; sistem membuat pembalik yang terlacak.

### Pelanggan

- Pelanggan bersifat opsional pada transaksi.
- Data minimum: nama dan salah satu kontak bila memang dibutuhkan.
- Pengumpulan data pelanggan harus memiliki tujuan jelas dan dapat dibatasi.
- Riwayat pembelian hanya dapat dilihat oleh peran berizin.

### Laporan

- Ringkasan penjualan, transaksi, rata-rata transaksi, pajak, diskon, void, dan refund.
- Penjualan per produk, kategori, outlet, kasir, jam, dan metode pembayaran.
- Perkiraan laba kotor berdasarkan snapshot biaya.
- Posisi stok, kartu stok, stok rendah, dan hasil opname.
- Pembelian per pemasok dan penerimaan belum selesai.
- Rekonsiliasi shift dan selisih kas.

## 6. Aturan pengalaman pengguna

- Satu tugas utama per halaman.
- Nama menu memakai istilah pengguna, bukan nama tabel atau proses internal.
- Filter outlet dan tanggal selalu terlihat pada laporan.
- Status offline, sinkronisasi, shift, dan outlet selalu terlihat pada POS.
- Error menjelaskan penyebab dan tindakan berikutnya.
- Tombol berisiko menyebut tindakan dengan jelas, misalnya `Batalkan transaksi`, bukan `OK`.
- Semua alur utama dapat dipakai dengan sentuhan dan ukuran target minimum 44 x 44 px.

## 7. Ukuran keberhasilan pilot

- Kasir baru dapat menyelesaikan penjualan dasar dalam kurang dari 2 menit tanpa bantuan.
- Transaksi reguler dapat diselesaikan dalam maksimal 5 interaksi setelah produk dipilih.
- Tidak ada transaksi ganda akibat retry sinkronisasi.
- Selisih total penjualan POS dan backoffice adalah nol setelah sinkronisasi selesai.
- Setiap perubahan stok dapat dilacak ke sumbernya.
- Penutupan shift dapat direkonsiliasi tanpa spreadsheet tambahan.
