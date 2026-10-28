---
title: "POS Mobile"
subtitle: "Alur kasir, pembayaran, shift, offline, dan perangkat"
author: "Binyo POS"
date: "2026-10-28"
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
- Tab, menu, dan tombol tindakan mengikuti permission pengguna pada outlet aktif.
- Status outlet, shift, koneksi, dan antrean sinkronisasi tetap terlihat tetapi tidak menutup area jual.

Role dapat dibuat dinamis dari backoffice. Nama seperti Pemilik, Manajer, Kasir, dan Gudang hanya preset; permission menentukan kemampuan sebenarnya. Void transaksi, pembalikan pembayaran, dan tindakan sensitif lain meminta PIN penyetuju bila pengguna aktif tidak memiliki izin langsung.

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
9. Jika bill dibayar bersama, pilih item atau sebagian jumlah item untuk pembayaran pertama lalu ulangi sampai lunas.
10. Simpan transaksi secara lokal lebih dulu, lalu antrekan sinkronisasi.
11. Cetak kitchen ticket per tujuan bila dikonfigurasi.
12. Cetak atau bagikan struk.

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
- Refund langsung ke penyedia pembayaran.

Nominal pembayaran dan total wajib diverifikasi kembali di server. Aplikasi tidak menjadi satu-satunya sumber kebenaran.

### Split bill

- Satu transaksi tetap menjadi bill induk; sistem tidak membuat transaksi batal atau salinan bill.
- Kasir memilih item atau sebagian jumlah item yang akan dibayar oleh satu pelanggan.
- Setiap pembayaran menyimpan alokasi ke item terpilih dan dapat memakai tunai, kartu manual, transfer manual, atau QRIS manual.
- Diskon transaksi, service charge, pajak, dan pembulatan dibagikan proporsional ke item sebelum split; sisa pembulatan masuk item terakhir.
- Item yang sudah dialokasikan tidak dapat dipilih lagi. Sisa item dan sisa nominal selalu terlihat.
- Bill berstatus `partially_paid` sampai seluruh total pembayaran sama dengan grand total.
- Pembayaran per orang dapat menghasilkan bukti pembayaran; struk final tetap menunjukkan seluruh bill dan semua metode pembayaran.
- Selisih pembulatan dialokasikan ke pembayaran terakhir agar jumlah pembayaran tepat sama dengan grand total.

### Salah pembayaran

- Sebelum konfirmasi, pilihan item dan metode pembayaran dapat diubah tanpa histori koreksi.
- Setelah pembayaran tercatat, kasir tidak dapat menghapusnya.
- Pemilik atau manajer membalikkan pembayaran memakai PIN dan alasan, lalu mencatat pembayaran pengganti.
- Pembalikan dan penggantian dilakukan sebagai satu alur audit agar bill tidak tertinggal dalam status lunas palsu.
- Setelah shift ditutup atau settlement direkonsiliasi, koreksi memakai refund atau penyesuaian beralasan, bukan mengubah pembayaran lama.

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

Target offline pilot adalah satu shift atau delapan jam. Setelah batas terlewati, aplikasi menampilkan peringatan kritis dan tetap menjaga command lokal; shift berikutnya tidak dapat dibuka sebelum sinkronisasi berhasil.

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
- Sebelum penutupan, sistem menampilkan bill `open`, `partially_paid`, pembayaran draft, dan transaksi gagal sinkron.
- Penutupan ditolak sampai seluruh bill dibayar penuh atau di-void dengan persetujuan pemilik/manajer.
- Shift dapat masuk status `Menunggu sinkronisasi` bila seluruh transaksi sudah tersimpan aman di perangkat tetapi belum diterima server.
- Penutupan server baru final setelah seluruh urutan transaksi perangkat sampai nomor penutupan diterima.
- Kasir tidak dapat menghapus antrean transaksi atau membuka shift baru pada perangkat yang sama sebelum status penutupan aman.

## 10. Perangkat dan aksesori

### Profil perangkat Android

- UI responsif untuk phone, tablet 8-12 inci, dan terminal POS 10-16 inci; tidak bergantung pada satu merek.
- Target pembelian baru: Android 13 atau lebih baru, RAM minimal 3 GB dan disarankan 4 GB, penyimpanan minimal 32 GB.
- Android 11-12 hanya diterima bila perangkat masih mendapat patch keamanan dan seluruh aksesori lulus uji.
- Flutter 3.47 secara teknis mendukung Android API 24-37, tetapi dukungan framework bukan pengganti dukungan keamanan vendor perangkat.
- Kandidat all-in-one pilot: SUNMI D3 MINI, Android 13, layar 10,1 inci, dengan varian printer 58 atau 80 mm.
- Alternatif: tablet Android standar yang memenuhi profil di atas dengan printer terpisah.

### Printer

- Template struk mendukung kertas 58 dan 80 mm.
- Rekomendasi kasir tetap: 80 mm melalui LAN atau USB; ruang lebih lebar, pemotong otomatis, dan koneksi lebih stabil.
- Rekomendasi portabel/cadangan: 58 mm melalui Bluetooth.
- Kandidat 80 mm: Epson TM-T82IV/TM-T82III. Kandidat portabel 58 mm: Epson TM-P20II.
- Bluetooth, USB, dan LAN dibuat sebagai profil koneksi; dukungan nyata hanya diberikan untuk model yang lulus proof-of-concept.
- Cash drawer dikontrol melalui port printer bila printer dan drawer mendukungnya.

### Scanner

- Scanner USB/Bluetooth yang bertindak sebagai keyboard HID menjadi pilihan utama.
- Kamera perangkat menjadi fallback; alur jual tetap dapat dipakai tanpa scanner.

### Dapur dan customer display

- Kitchen display dan customer display bukan MVP dan tidak ditampilkan sebagai menu kosong.
- Jika pilot membutuhkan keluaran dapur, jalur minimum adalah kitchen printer yang mencetak hanya item yang perlu disiapkan.
- Produk diarahkan per outlet ke tujuan seperti `Bar` atau `Dapur`; item tanpa tujuan tidak mencetak kitchen ticket.
- Perubahan atau pembatalan setelah ticket tercetak menghasilkan ticket koreksi, bukan menghapus ticket sebelumnya.
- Kitchen display ditambahkan setelah pilot sebagai layar antrean dengan status `Baru`, `Diproses`, dan `Selesai`.
- Customer display kelak membaca keranjang aktif dari perangkat kasir; MVP tidak membuat aplikasi atau konfigurasi palsu untuk fitur tersebut.

Referensi perangkat:

- Flutter supported platforms: <https://docs.flutter.dev/reference/supported-platforms>
- SUNMI D3 MINI: <https://www.sunmi.com/en/d3-mini/>
- Epson TM-T82III: <https://www.epson.co.id/For-Work/Printers/POS-Printers/Epson-TM-T82III-POS-Printer/p/C31CH51542>
- Epson TM-P20II: <https://www.epson.co.id/For-Work/Printers/POS-Printers/Epson-TM-P20II-Printer-Resi-Nirkabel-yang-Portabel-/p/C31CJ99401>

Kalibrasi perangkat nyata wajib dilakukan sebelum pilot. Simulator tidak cukup untuk printer, scanner, cash drawer, dan perilaku offline.

## 11. Kriteria penerimaan

- Penjualan tunai dapat selesai tanpa internet setelah bootstrap berhasil.
- Restart aplikasi tidak menghilangkan transaksi yang belum tersinkron.
- Menekan tombol bayar berulang tidak membuat pembayaran ganda.
- Split bill tidak menggandakan item, nominal, pajak, diskon, atau pembayaran.
- Shift tidak dapat ditutup selama masih ada bill terbuka atau dibayar sebagian.
- Struk menampilkan bisnis, outlet, nomor, waktu, kasir, item, diskon, pajak, total, pembayaran, dan kembalian.
- Status offline dan antrean sinkronisasi selalu terlihat.
- Void, refund, dan diskon berizin tercatat pada audit log.
- Shift offline tidak berstatus final di server sebelum seluruh transaksi sampai batas urutan penutupan tersinkron.
