---
title: "Audit Menu VireoPOS"
subtitle: "Pemetaan dashboard, master data, transaksi, laporan, HPP, akunting, dan akun"
author: "Binyo POS"
date: "2026-10-27"
status: "Draft"
---

# Audit Menu VireoPOS

Tanggal observasi: 2026-10-27

Referensi: <https://eretail.vireopos.com/#/vireodashboard.crux>

Audit dilakukan pada sesi yang disediakan pengguna. Dokumen ini tidak menyalin identitas akun, outlet, pelanggan, nomor dokumen, atau nilai transaksi nyata.

## 1. Kesimpulan

Vireo memiliki cakupan operasional luas, tetapi banyak pekerjaan dipecah menjadi menu teknis. Binyo mengambil kemampuan intinya lalu menyusun ulang berdasarkan tugas pengguna.

Keputusan:

- `Keep`: kemampuan inti dipertahankan.
- `Merge`: kemampuan dipertahankan, tetapi digabung ke halaman atau alur yang lebih sederhana.
- `Defer`: berguna untuk segmen tertentu, tetapi bukan MVP.
- `Remove`: tidak memberi manfaat cukup untuk Binyo saat ini.

Sidebar Binyo direkomendasikan hanya memiliki tujuh menu utama:

1. Beranda.
2. Penjualan.
3. Produk.
4. Persediaan.
5. Laporan.
6. Operasional.
7. Pengaturan.

## 2. Dashboard

### Temuan Vireo

- Penjualan kotor.
- Penjualan setelah pajak dan biaya platform.
- Jumlah nota.
- Rata-rata transaksi.
- Penjualan online.
- Grafik berdasarkan jenis dan interval.
- Penjualan tujuh hari terakhir.
- Produk terlaris.
- Peringatan stok minimum.
- Penjualan per kategori.

### Keputusan Binyo

Dashboard tidak menjadi galeri grafik. Setiap bagian harus membantu pengguna mengambil tindakan.

| Bagian | Keputusan | Tindakan utama |
|---|---|---|
| Penjualan bersih, transaksi, rata-rata transaksi | Keep | Buka transaksi periode terkait |
| Perbandingan periode | Keep | Temukan perubahan yang tidak biasa |
| Produk terlaris | Keep | Buka laporan produk |
| Stok rendah atau minus | Keep | Buat pembelian, transfer, atau penyesuaian |
| Shift belum ditutup | Add | Buka shift bermasalah |
| Pembayaran belum direkonsiliasi | Add | Cocokkan pembayaran non-tunai |
| Perangkat gagal sinkron | Add | Buka status perangkat dan antrean sync |
| Grafik tanpa tindakan lanjutan | Remove | Tidak ditampilkan |

## 3. Data Master

| Menu Vireo | Data utama | Keputusan | Lokasi Binyo |
|---|---|---|---|
| Kategori | Nama, urutan tampil | Keep | Produk > Katalog > Kategori |
| Grup Produk | Nama grup | Merge | Filter/tag produk; bukan menu utama |
| Merek | Nama merek | Merge | Field opsional pada produk |
| Atribut | Atribut dan pilihan | Merge | Pembentuk varian pada produk |
| Channel | Instore, delivery, web order | Defer | Pengaturan > Integrasi |
| Grup Modifier | Nama, minimum, maksimum pilihan | Keep | Produk > Modifier |
| Grup Fasilitas | Nama fasilitas | Remove | Ditambah hanya bila ada alur fasilitas nyata |
| Produk | Identitas, harga, stok, resep, modifier | Keep | Produk > Katalog |
| Gerai | Identitas, kontak, pajak, service charge | Keep | Operasional > Outlet |
| Promo | Periode, hari, cakupan produk | Merge | Produk > Promo sederhana |
| Pelanggan | Identitas, kontak, membership | Keep | Penjualan > Pelanggan |
| Kolom Tambahan Pelanggan | Field dinamis pelanggan | Defer | Gunakan field tetap sampai kebutuhan pilot terbukti |
| Pemasok | Nama, kontak, alamat | Keep | Persediaan > Pemasok |
| Tipe Pembayaran | Nama, jenis, diskon | Keep | Pengaturan > Metode pembayaran |
| Grup Harga | Nama grup harga | Merge | Harga per outlet atau channel |
| Katalog Penukaran Poin | Produk hadiah, poin, periode | Defer | Loyalty setelah pilot |
| Grup Kupon | Nilai, jenis, periode | Defer | Promo setelah pilot |
| Komisi | Jenis, nilai, periode | Defer | Bukan kebutuhan kasir inti |
| Program Loyalti | Periode dan status | Defer | Loyalty setelah pilot |
| Pengaturan Channel | Channel dan harga toko | Defer | Pengaturan > Integrasi |
| Tipe Item | Tipe waktu atau timer | Defer | Operasional dapur bila F&B membutuhkannya |
| Status | Status antrean pesanan | Defer | Operasional dapur bila KDS dibuat |
| Template Broadcast WhatsApp | Kategori, tipe, subject, status | Remove | Marketing automation bukan MVP |

### Produk

Vireo memisahkan produk ke tab `Utama`, `Komponen`, dan `Modifier`.

Field yang ditemukan:

- Produk induk, nama, kategori, grup produk, merek, kode, satuan, deskripsi, dan gambar.
- Ketersediaan per gerai.
- Pelacakan stok, larangan stok minus, nomor seri, HPP, dan stok minimum.
- Harga jual, pajak, favorit, serta urutan tampil pada POS.
- Komponen resep: produk komponen, jumlah, satuan, harga, dan persentase kontribusi.
- Modifier.

Penyederhanaan Binyo:

- Satu halaman produk dengan bagian `Info`, `Harga & Outlet`, `Stok`, dan `Resep & Modifier`.
- Field lanjutan disembunyikan sampai fitur terkait diaktifkan.
- Produk tanpa pilihan tetap memiliki satu SKU default, tetapi istilah SKU tidak dipaksakan kepada kasir.
- Tombol HPP menjelaskan sumber biaya dan waktu pembaruan, bukan hanya `Hitung Ulang HPP`.

## 4. Transaksi dan Persediaan

| Menu Vireo | Data atau alur | Keputusan | Lokasi Binyo |
|---|---|---|---|
| Purchase Order | Dokumen, nomor eksternal, outlet, pemasok, tanggal, status | Keep | Persediaan > Pembelian |
| Penerimaan Barang | PO, barang, jumlah diterima, sisa, tanggal | Merge | Tahap `Terima barang` pada pembelian |
| Stok Masuk | Outlet, barang, tanggal, catatan | Merge | Persediaan > Penyesuaian > Tambah stok |
| Stok Keluar | Outlet, barang, tanggal, catatan | Merge | Persediaan > Penyesuaian > Kurangi stok |
| Stok Transfer | Sumber, tujuan, barang, tanggal | Merge | Persediaan > Transfer |
| Transfer Order | Permintaan transfer dan status | Merge | Status awal pada satu dokumen transfer |
| Surat Jalan Transfer | Pengiriman dari sumber | Merge | Status `Dikirim` pada transfer |
| Penerimaan Transfer | Barang diterima tujuan | Merge | Status `Diterima` pada transfer |
| Stok Opname | Outlet, tanggal, status, catatan | Keep | Persediaan > Opname |
| Stock Adjustment | Outlet, barang, tanggal, catatan | Merge | Hasil opname atau penyesuaian langsung beralasan |
| Proses Komisi | Komisi, periode, status | Defer | Setelah kebutuhan komisi disetujui |
| Adjustment Deposito | Pelanggan, jumlah, alasan | Defer | Setelah saldo pelanggan dibuat |
| Adjustment Poin | Pelanggan, jumlah, alasan | Defer | Setelah loyalty dibuat |
| Produksi | Outlet, barang hasil, tanggal | Defer | Persediaan > Produksi bila pilot memakai batch production |
| Bongkar Produksi | Pembalikan hasil produksi | Defer | Reversal produksi pada dokumen asal |
| Pembatalan Penjualan | Pesanan, pelanggan, status, total | Keep | Penjualan > Refund & Void |

### Purchase order

Vireo memakai tab `Dokumen`, `Rincian`, dan `Rangkuman`. Header berisi nomor dokumen, nomor eksternal, gerai, pemasok, tanggal pembelian, catatan, dan status penerimaan.

Binyo memakai alur lebih pendek:

1. Pilih pemasok dan outlet.
2. Tambahkan item, satuan beli, jumlah, dan harga beli.
3. Periksa total lalu simpan draft atau pesan.
4. Terima seluruh atau sebagian barang dari dokumen yang sama.

MVP tidak memiliki rantai approval terpisah. Izin `purchase_order.post` membatasi siapa yang boleh mengubah draft menjadi `Dipesan`. Approval berdasarkan nominal ditambahkan hanya bila pilot membutuhkannya.

### Transfer

Empat menu transfer Vireo digabung menjadi satu dokumen dengan status:

```text
Draft -> Dikirim -> Diterima sebagian -> Selesai
                  -> Dibatalkan
```

Stok sumber berkurang saat `Dikirim`. Stok tujuan bertambah saat `Diterima`. Selisih wajib memiliki alasan dan jejak audit.

## 5. Laporan

### Laporan stok

| Laporan Vireo | Keputusan Binyo |
|---|---|
| Kartu Stok | Keep sebagai histori stok per item |
| Stok Terkini | Keep sebagai posisi stok |
| Riwayat Stok | Merge dengan Kartu Stok |
| Peringatan Stok Minimum | Keep sebagai filter dan kartu tindakan |
| Stok Nomor Seri | Defer sampai serial tracking dibutuhkan |
| Riwayat Stok Nomor Seri | Defer sampai serial tracking dibutuhkan |
| Laporan Stok Opname | Keep sebagai riwayat opname |
| Stok Transfer Menggantung | Keep sebagai pengecualian pada halaman transfer |

### Laporan pembelian

Vireo menyediakan pembelian per kategori, produk, pemasok, rincian, dan grup. Binyo memakai satu laporan pembelian dengan pilihan `Ringkas` atau `Rinci`, lalu pengguna dapat mengelompokkan berdasarkan pemasok, produk, kategori, atau outlet.

### Laporan penjualan

Vireo menyediakan penjualan per kategori, grup produk, merek, produk, gerai, pelanggan, metode pembayaran, perangkat, hari, promosi, staff, channel, varian, dan grup harga; ditambah laporan ringkas dan rinci.

Binyo memakai satu halaman laporan penjualan:

- Tampilan `Ringkas`, `Rinci`, atau `Tren`.
- Pengelompokan: produk, kategori, outlet, kasir, pelanggan, metode pembayaran, perangkat, hari/jam, promo, atau channel.
- Merek, grup produk, varian, dan grup harga menjadi filter tambahan bila datanya memang dipakai.

### Laporan laba

Vireo menyediakan laba per produk dan laba per hari. Binyo menggabungkannya menjadi laporan laba kotor dengan pengelompokan produk, kategori, outlet, atau hari.

Laporan wajib membedakan:

- Penjualan produk setelah diskon, tidak termasuk pajak dan service charge.
- HPP yang diambil dari snapshot transaksi.
- Laba kotor.
- Margin laba kotor.
- Baris dengan HPP belum tersedia atau masih perkiraan.

### Laporan lainnya

| Laporan Vireo | Keputusan Binyo |
|---|---|
| Rekapitulasi Kas | Keep sebagai Laporan Shift & Kas |
| Diskon | Merge ke laporan penjualan |
| Harga Pokok Penjualan | Keep sebagai rincian HPP |
| Modifier | Merge ke laporan produk |
| Pajak | Keep sebagai Laporan Pajak & Biaya |
| Komisi | Defer |
| Biaya Layanan | Merge ke Laporan Pajak & Biaya |
| Riwayat Poin | Defer |
| Penggunaan Kupon | Defer |
| Transaksi Fasilitas | Remove dari MVP |
| Transaksi Pelanggan | Merge ke laporan penjualan |
| Topup Deposito | Defer |
| Mutasi Deposito | Defer |
| Proses Komisi | Defer |
| Kombinasi Modifier | Defer; tambah hanya jika analisis menu membutuhkannya |
| Kas Masuk/Kas Keluar | Keep sebagai Laporan Shift & Kas |
| Sisa Kredit Kasbon | Defer |

## 6. HPP dan Laba

### Fakta yang diamati pada Vireo

- Bahan baku dapat memakai satuan dasar kecil, misalnya `ml`, `gr`, atau `pcs`.
- HPP bahan disimpan per satuan dasar.
- Komponen resep memiliki jumlah, satuan, harga, dan persentase kontribusi.
- Produk resep menampilkan total harga komponen.
- Tersedia tombol `Hitung Ulang HPP`.
- Penjualan membentuk jurnal debit `Cost of Goods Sold` dan kredit persediaan.
- Kredit persediaan dapat terpecah ke lebih dari satu akun berdasarkan kelompok bahan.

Belum dapat dipastikan dari UI:

- Waktu tepat HPP bahan diperbarui.
- Aturan pembulatan internal.
- Perlakuan transaksi lama setelah tombol hitung ulang digunakan.
- Penyebab persentase komponen tertentu tidak selalu berjumlah tepat 100 persen.

### Aturan HPP Binyo

#### Satuan

Setiap SKU memiliki satu `base_unit` sebagai sumber kebenaran stok dan resep. Satuan beli dikonversi saat penerimaan. Biaya rata-rata disimpan per SKU dan outlet.

Contoh:

```text
1 karton = 12 pcs
1 liter = 1.000 ml
```

Faktor konversi disimpan sebagai snapshot pada baris penerimaan agar perubahan master tidak mengubah histori.

Jumlah dan biaya per base unit memakai fixed decimal. Total dokumen dibulatkan ke rupiah saat posting agar biaya kecil per `ml` atau `gr` tidak hilang.

#### Biaya penerimaan

```text
base_quantity_received = purchase_quantity x conversion_factor
receipt_cost_per_base_unit = (line_cost + allocated_additional_cost) / base_quantity_received
```

Biaya tambahan bernilai nol pada MVP bila landed cost belum diaktifkan.

#### Rata-rata bergerak

```text
new_average_cost =
    ((stock_before x average_cost_before)
    + (base_quantity_received x receipt_cost_per_base_unit))
    / (stock_before + base_quantity_received)
```

Rumus berlaku saat stok sebelum penerimaan tidak negatif. Jika stok sebelumnya nol, biaya baru sama dengan biaya penerimaan. Biaya diperbarui saat penerimaan diposting, bukan saat PO masih draft. Model ini mengikuti pola weighted average yang juga digunakan Loyverse Advanced Inventory.

#### Produk resep

```text
recipe_cost = sum(component_quantity_in_base_unit x component_average_cost_at_outlet)
```

Modifier yang mengonsumsi bahan menambah biaya komponennya. Kemasan dapat dimodelkan sebagai komponen resep agar HPP terlihat dan stoknya terlacak.

#### Saat penjualan

- HPP produk atau resep disalin ke `sale_lines.unit_cost` saat transaksi selesai.
- Transaksi lama tidak berubah saat harga beli atau resep berikutnya berubah.
- `gross_profit = net_product_sales - cost_of_goods_sold`.
- Pajak dan service charge tidak masuk `net_product_sales`; keduanya dilaporkan terpisah.

#### Kondisi khusus

- Opening stock wajib memiliki jumlah dan biaya awal.
- Produk dengan stok terlacak dilarang minus secara default.
- Jika bisnis mengizinkan stok minus, penjualan memakai rata-rata biaya terakhir dan diberi status HPP perkiraan.
- Transfer membawa snapshot biaya dari outlet sumber dan memperbarui rata-rata outlet tujuan.
- Refund memakai snapshot HPP dari transaksi asal.
- Penerimaan atau penyesuaian yang sudah diposting tidak diedit langsung; koreksi memakai reversal.
- HPP kosong atau nol selalu tampil sebagai peringatan pada laporan laba.

### UI HPP Binyo

Pada produk tampilkan:

- HPP saat ini.
- Sumber terakhir: opening stock, penerimaan, produksi, atau perhitungan resep.
- Waktu pembaruan.
- Rincian kontribusi komponen.
- Simulasi dampak perubahan resep sebelum disimpan.

## 7. Akunting

### Temuan Vireo

- `Akun Buku Besar`: kode, nama, tipe akun, parent, dan izin jurnal manual.
- `Buat Jurnal`: nomor jurnal, tanggal, akun, referensi dokumen, kasir, keterangan, debit, dan kredit.
- `Laporan Akunting`: neraca dan laba rugi, neraca bulanan, serta laporan jurnal.
- Penjualan otomatis membentuk jurnal kas atau metode pembayaran, penjualan, HPP, dan persediaan.

### Keputusan Binyo

General ledger lengkap bukan MVP. Binyo tetap menyimpan event dan snapshot yang cukup untuk rekonsiliasi serta ekspor.

- `Laporan > Ekspor Akunting` menyediakan penjualan, pajak, service charge, pembayaran, HPP, persediaan, refund, dan pergerakan kas.
- Mapping chart of accounts ditambahkan setelah target integrasi dipilih.
- Jurnal manual, neraca, dan laba rugi ditunda agar Binyo tidak menjadi ERP setengah jadi.

## 8. Akun dan Pengaturan

| Menu Vireo | Data utama | Keputusan | Lokasi Binyo |
|---|---|---|---|
| Perangkat Kasir | Terminal, nama, outlet, staff, aktif, kedaluwarsa | Keep | Operasional > Perangkat |
| Staff | Nama, email, nomor ponsel | Keep | Operasional > Staff & Akses |
| Perusahaan | Profil, alamat, mata uang, pembulatan, pajak, bank | Merge | Pengaturan > Bisnis; field bank ditunda |
| Grup Akses | Nama dan jenis grup | Keep | Operasional > Staff & Akses > Peran |
| Pengaturan | Pola nomor dokumen operasional | Merge | Pengaturan > Penomoran; default otomatis |
| Waralaba | Gerai, merek, perusahaan, status, syarat | Defer | Setelah model franchise disetujui |

Binyo menambahkan `Operasional > Log Aktivitas` agar owner dapat melihat perubahan harga, void/refund, koreksi stok, perubahan akses, dan tindakan sensitif lain tanpa meminta akses database.

## 9. Pola UI yang Direkomendasikan

- Navigasi berdasarkan pekerjaan, bukan nama tabel.
- Maksimal tujuh menu utama; menu lanjutan muncul sebagai tab atau aksi kontekstual.
- Satu tombol utama per halaman.
- Filter outlet dan tanggal tetap terlihat pada laporan.
- Tabel menyimpan pilihan kolom dan filter pengguna.
- Status memakai bahasa tindakan: `Draft`, `Dipesan`, `Diterima sebagian`, `Selesai`, `Dibatalkan`.
- Form panjang dibagi menjadi bagian, bukan wizard bila pengguna perlu membandingkan field.
- Empty state menjelaskan tindakan pertama.
- Error menyebut penyebab, dampak, dan cara memperbaiki.
- Aksi berisiko menampilkan ringkasan dampak sebelum konfirmasi.

## 10. Referensi yang Lebih Tepat per Area

| Kebutuhan | Referensi utama | Alasan |
|---|---|---|
| POS kasir sederhana | Loyverse | Alur kasir dan backoffice mudah dipelajari |
| Checkout retail dan refund | Square for Retail | Histori stok dan transaksi dekat dengan tugas pengguna |
| Katalog, varian, multi-location | Shopify POS | Model produk dan location rapi |
| Offline dan dokumentasi workflow | Odoo POS | Cakupan teknis jelas tanpa harus menyalin ERP-nya |
| Resep dan operasional dapur | Toast | Dipakai hanya bila pilot fokus F&B |
| Barcode | GS1 | Standar GTIN, EAN/UPC, lot, dan serial |
| Keamanan mobile | OWASP MASVS | Batas secure storage, auth, network, dan privacy |

## 11. Batas Audit

Audit mencakup menu dari Dashboard, Data Master, Transaksi, Laporan, Akunting, dan Akun yang terlihat pada sesi. Formula backend, aturan pembulatan, perilaku tombol hitung ulang HPP, serta integrasi eksternal belum dapat dibuktikan hanya dari UI dan harus divalidasi dengan contoh transaksi uji sebelum migrasi.
