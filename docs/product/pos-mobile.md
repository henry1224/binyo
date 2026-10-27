---
title: "POS Mobile"
subtitle: "Alur kasir, pembayaran, shift, offline, dan perangkat"
author: "Binyo POS"
date: "2026-10-27"
status: "Draft"
---

# POS Mobile

## 1. Sasaran

POS mobile harus mengutamakan kecepatan transaksi, keterbacaan nominal, dan kepastian status pembayaran. Pengaturan kompleks tetap berada di backoffice.

## 2. Navigasi

- Jual.
- Transaksi.
- Shift.
- Lainnya: cek stok, sinkronisasi, perangkat, bantuan, keluar.

Tidak ada salinan seluruh menu backoffice di aplikasi kasir.

Aturan navigasi:

- Halaman `Jual` selalu menjadi tujuan awal saat shift terbuka.
- Keranjang dan tombol bayar selalu dapat dijangkau tanpa membuka menu.
- Tindakan manajer muncul saat dibutuhkan, bukan sebagai menu kasir tetap.
- Status outlet, shift, koneksi, dan antrean sinkronisasi tetap terlihat tetapi tidak menutup area jual.

## 3. Alur mulai kerja

1. Pengguna masuk dengan akun, lalu memakai PIN untuk akses harian bila perangkat sudah dipercaya.
2. Sistem memeriksa outlet dan perangkat yang ditugaskan.
3. Sistem mengunduh perubahan katalog, harga, pajak, metode pembayaran, dan izin.
4. Kasir membuka shift dan memasukkan saldo awal.
5. Halaman jual terbuka dengan status outlet, shift, koneksi, dan sinkronisasi terlihat.

## 4. Alur penjualan

1. Pilih kategori, cari produk, scan barcode, atau pilih favorit.
2. Pilih varian dan modifier bila diperlukan.
3. Ubah jumlah atau tambahkan catatan.
4. Tambahkan pelanggan bila perlu.
5. Terapkan diskon bila memiliki izin; jika tidak, minta PIN penyetuju.
6. Buka pembayaran.
7. Pilih metode pembayaran dan masukkan nominal diterima untuk tunai.
8. Konfirmasi ringkasan akhir.
9. Simpan transaksi secara lokal lebih dulu, lalu antrekan sinkronisasi.
10. Cetak atau bagikan struk.

Target penjualan reguler setelah produk dipilih adalah maksimal lima interaksi: buka keranjang, bayar, pilih metode, konfirmasi, lalu selesai.

## 5. Layout

### Tablet landscape

```text
+------------------------------------------------+----------------------+
| Outlet | Shift | Online | Tersinkron           | Keranjang            |
|------------------------------------------------| Item                 |
| Cari / Scan                                    | Modifier             |
|------------------------------------------------| Qty x Harga          |
| Kategori                                       |----------------------|
|------------------------------------------------| Subtotal             |
| Produk                                         | Diskon               |
| [Produk] [Produk] [Produk] [Produk]            | Pajak                |
| [Produk] [Produk] [Produk] [Produk]            | Total                |
|                                                | [Bayar]              |
+------------------------------------------------+----------------------+
```

### Phone portrait

- Produk memenuhi layar.
- Ringkasan keranjang tetap terlihat di bawah.
- Keranjang lengkap dibuka sebagai panel.
- Tombol bayar tidak menutup total dan jumlah item.

## 6. Pembayaran

### MVP

- Tunai.
- Kartu sebagai pencatatan manual.
- Transfer sebagai pencatatan manual.
- QRIS sebagai pencatatan manual hanya setelah kasir memastikan pembayaran pada alat/aplikasi penyedia.

Untuk pembayaran manual non-tunai:

- Referensi pembayaran dapat diwajibkan per metode.
- Kasir melihat status `Tercatat`, bukan `Terverifikasi penyedia`.
- Backoffice merekonsiliasi total POS dengan settlement penyedia per outlet, tanggal, dan metode.
- Selisih tidak mengubah transaksi asli; pengguna membuat koreksi beralasan.

### Fase berikutnya

- QRIS dinamis dengan status otomatis.
- Split payment.
- Refund langsung ke penyedia pembayaran.

Nominal pembayaran dan total wajib diverifikasi kembali di server. Aplikasi tidak menjadi satu-satunya sumber kebenaran.

## 7. Offline dan sinkronisasi

- Katalog terakhir yang berhasil disinkron tersedia secara lokal.
- Transaksi memakai UUID yang dibuat perangkat dan `idempotency_key` yang sama saat retry.
- Nomor lokal tetap unik menggunakan outlet, perangkat, tanggal, dan urutan perangkat.
- Setiap transaksi menyimpan waktu perangkat dan waktu server.
- Perubahan harga dari server tidak mengubah transaksi yang sudah dibuat.
- Retry tidak boleh membuat transaksi atau pembayaran ganda.
- Konflik master data tidak diselesaikan kasir; perubahan backoffice menang untuk sinkronisasi berikutnya.
- Transaksi offline tidak boleh diedit di backoffice sebelum payload lengkap diterima.
- Aplikasi menunjukkan jumlah transaksi menunggu, gagal, dan waktu sinkronisasi terakhir.

Jika perangkat belum menyinkron melewati batas yang ditetapkan bisnis, aplikasi memberi peringatan. Pemblokiran penjualan hanya dipakai bila risiko bisnis menuntutnya.

## 8. Void, refund, dan koreksi

- `Void`: membatalkan transaksi sebelum atau segera setelah finalisasi sesuai aturan bisnis.
- `Refund`: mengembalikan sebagian atau seluruh transaksi selesai.
- Keduanya membutuhkan alasan.
- Hak akses dapat meminta PIN manajer.
- Riwayat asli tetap ada.
- Pengembalian barang menghasilkan pergerakan stok bila barang memang kembali layak jual.

## 9. Shift

- Buka shift dengan saldo awal.
- Catat kas masuk dan kas keluar non-penjualan.
- Lihat ringkasan metode pembayaran tanpa membuka laporan bisnis sensitif.
- Tutup shift dengan hitung kas fisik.
- Simpan selisih dan catatan.
- Shift dapat masuk status `Menunggu sinkronisasi` bila seluruh transaksi sudah tersimpan aman di perangkat tetapi belum diterima server.
- Penutupan server baru final setelah seluruh urutan transaksi perangkat sampai nomor penutupan diterima.
- Kasir tidak dapat menghapus antrean transaksi atau membuka shift baru pada perangkat yang sama sebelum status penutupan aman.

## 10. Perangkat dan aksesori

Prioritas validasi sebelum pengembangan UI final:

- Printer thermal 58/80 mm.
- Bluetooth, USB, atau LAN.
- Barcode scanner sebagai keyboard input atau kamera.
- Cash drawer melalui printer.
- Ukuran tablet/phone dan versi Android minimum.

Kalibrasi perangkat nyata wajib dilakukan sebelum pilot. Simulator tidak cukup untuk printer, scanner, dan perilaku offline.

## 11. Kriteria penerimaan

- Penjualan tunai dapat selesai tanpa internet setelah bootstrap berhasil.
- Restart aplikasi tidak menghilangkan transaksi yang belum tersinkron.
- Menekan tombol bayar berulang tidak membuat pembayaran ganda.
- Struk menampilkan bisnis, outlet, nomor, waktu, kasir, item, diskon, pajak, total, pembayaran, dan kembalian.
- Status offline dan antrean sinkronisasi selalu terlihat.
- Void, refund, dan diskon berizin tercatat pada audit log.
- Shift offline tidak berstatus final di server sebelum seluruh transaksi sampai batas urutan penutupan tersinkron.
