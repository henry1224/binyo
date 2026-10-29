---
title: "Matriks Fitur VireoPOS"
subtitle: "Inventaris 1:1 menu, halaman, field, aksi, permission, dan pemetaan Binyo"
author: "Binyo POS"
date: "2026-10-28"
status: "Draft"
---

# Matriks Fitur VireoPOS

Tanggal observasi: 2026-10-27

Referensi: <https://eretail.vireopos.com/#/vireodashboard.crux>

Dokumen ini menjadi inventaris kanonis fitur Vireo yang terlihat pada tenant referensi. Identitas akun, outlet, pelanggan, produk, nomor dokumen, dan nilai transaksi tidak disalin.

## 1. Status audit

| Status | Arti |
|---|---|
| `Verified UI` | Route, daftar, form, field, atau laporan sudah dibuka secara read-only |
| `Permission only` | Kemampuan terlihat pada Grup Akses, tetapi menu tidak tersedia pada tenant |
| `Partial` | Halaman terlihat, tetapi membutuhkan prasyarat atau konfigurasi untuk membuka seluruh field |
| `Needs sandbox` | Perilaku hanya dapat dibuktikan dengan membuat, posting, membatalkan, atau menghitung ulang data uji |

Audit menu dapat 1:1 tanpa membuat desain Binyo 1:1. Kolom keputusan memakai:

- `Keep`: kemampuan inti dipertahankan.
- `Merge`: kemampuan dipertahankan dalam halaman atau alur lain.
- `Simplify`: kemampuan dipertahankan dengan field atau langkah lebih sedikit.
- `Defer`: ditunda sampai kebutuhan pilot terbukti.
- `Remove`: tidak dibawa ke Binyo.

## 2. Ringkasan cakupan

| Area | Cakupan read-only |
|---|---|
| Dashboard | Kartu, grafik, produk terlaris, dan peringatan stok |
| Data Master | 23 menu beserta form tambah yang dapat dibuka |
| Transaksi | 16 menu beserta form yang tidak membutuhkan dokumen sumber |
| Laporan | 48 variasi laporan pada lima kelompok |
| Akunting | Akun buku besar, jurnal, dan tiga laporan akunting |
| Akun | Perangkat, staff, perusahaan, grup akses, pengaturan, dan waralaba |
| Integrasi | GrabFood, GrabMart, dan Menu GoBiz yang terlihat |
| POS | Kemampuan aplikasi POS yang tercantum pada Grup Akses |

## 3. Dashboard

| ID | Fitur Vireo | Data atau aksi | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|
| VIR-DASH-01 | Penjualan | Nilai penjualan periode | Beranda > Penjualan bersih | Keep | Verified UI |
| VIR-DASH-02 | Penjualan setelah pajak/platform | Nilai setelah komponen tertentu | Beranda > Penjualan bersih | Simplify | Verified UI |
| VIR-DASH-03 | Jumlah nota | Jumlah transaksi | Beranda > Transaksi | Keep | Verified UI |
| VIR-DASH-04 | Penjualan rata-rata | Penjualan kotor dibagi nota | Beranda > Rata-rata transaksi | Keep | Verified UI |
| VIR-DASH-05 | Penjualan online | GrabFood dan GoFood | Beranda > Channel | Defer | Verified UI |
| VIR-DASH-06 | Grafik penjualan | Jenis dan interval | Beranda > Tren | Simplify | Verified UI |
| VIR-DASH-07 | Penjualan tujuh hari | Tren harian | Beranda > Tren | Keep | Verified UI |
| VIR-DASH-08 | Produk terlaris | Peringkat produk | Beranda > Produk terlaris | Keep | Verified UI |
| VIR-DASH-09 | Peringatan stok minimum | Stok aktual dibanding ambang | Beranda > Stok bermasalah | Keep | Verified UI |
| VIR-DASH-10 | Penjualan per kategori | Ringkasan kategori | Laporan > Penjualan | Merge | Verified UI |

Binyo menambahkan shift belum ditutup, pembayaran belum direkonsiliasi, dan perangkat gagal sinkron karena ketiganya menghasilkan tindakan operasional langsung.

## 4. Data Master

### 4.1 Katalog dan penjualan

| ID | Menu dan route | Field atau struktur utama | Aksi/permission Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-MST-01 | Kategori `/category.crux` | Nama, urutan tampil, indikator bahan baku | Tambah, impor, ekspor, ubah, hapus | Produk > Katalog > Kategori | Keep | Verified UI |
| VIR-MST-02 | Grup Produk `/vireoProductGroup.crux` | Nama, default | Tambah, impor, ekspor, ubah, hapus | Tag/filter produk | Merge | Verified UI |
| VIR-MST-03 | Merek `/vireoBrand.crux` | Nama, default | Tambah, impor, ekspor, ubah, hapus | Field produk | Merge | Verified UI |
| VIR-MST-04 | Atribut `/vireoattribute.crux` | Nama atribut dan daftar pilihan | Tambah, impor, ekspor, ubah, hapus | Produk > Varian | Merge | Verified UI |
| VIR-MST-05 | Channel `/vireoChannel.crux` | Nama; hitung PB1, PPN, dan service charge | Tambah, ubah, hapus | Pengaturan > Integrasi | Defer | Verified UI |
| VIR-MST-06 | Grup Modifier `/modifierGroup.crux` | Nama, min/max pilihan; opsi, harga, min/max/kelipatan qty, referensi barang | Tambah, impor, ubah, hapus | Produk > Modifier | Keep | Verified UI |
| VIR-MST-07 | Grup Fasilitas `/facilityGroup.crux` | Nama, jam mulai/selesai, kapasitas, mode | Tambah, ubah, hapus | Tidak ada pada MVP | Remove | Verified UI |
| VIR-MST-08 | Produk `/invproduct.crux` | Produk induk, nama, kategori, grup, merek, kode, satuan, outlet, stok, HPP, harga, pajak, resep, modifier | Tambah, varian, impor, ekspor, ubah, hapus, hitung ulang HPP | Produk > Katalog | Keep | Verified UI; hitung ulang Needs sandbox |
| VIR-MST-09 | Gerai `/vireooutlet.crux` | Profil, alamat, telepon, kredit, PB1, PPN, tipe pajak, service charge, platform fee, pembayaran, web order, dapur; tab fasilitas/channel/deposito | Tambah, ubah, hapus | Operasional > Outlet | Simplify | Verified UI |
| VIR-MST-10 | Promo `/vireopromo.crux` | Nama, jenis, minimum belanja, member/poin, periode, jadwal, outlet, channel | Tambah promo, grup produk, ubah, hapus | Produk > Promo | Simplify | Verified UI |

### 4.2 Pelanggan, pembayaran, dan harga

| ID | Menu dan route | Field atau struktur utama | Aksi/permission Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-MST-11 | Pelanggan `/vireocustomer.crux` | Identitas, kontak, NIK/NPWP, alamat, grup harga, kredit, membership, poin, deposito | Tambah, impor, ekspor, ubah, hapus | Penjualan > Pelanggan | Simplify | Verified UI |
| VIR-MST-12 | Kolom Tambahan Pelanggan `/vireoCustomerField.crux` | Nama, tipe, urutan, wajib, tampil POS, tampil daftar/ekspor | Tambah, ubah, hapus | Field tetap dahulu | Defer | Verified UI |
| VIR-MST-13 | Pemasok `/vireo_supplier.crux` | Nama, telepon, alamat, B2B | Tambah, impor, ekspor, ubah, hapus | Persediaan > Pemasok | Keep | Verified UI |
| VIR-MST-14 | Tipe Pembayaran `/vireoPaymentType.crux` | Nama, periode, tipe/nilai diskon, outlet, nominal manual, referensi wajib, deposito, akun otomatis | Tambah, impor, ekspor, ubah, hapus | Pengaturan > Metode pembayaran | Simplify | Verified UI |
| VIR-MST-15 | Grup Harga `/vireoPriceGroup.crux` | Nama, outlet, periode harga | Tambah, impor, ubah, hapus | Harga per outlet/channel | Merge | Verified UI |

### 4.3 Loyalty, komisi, dan konfigurasi khusus

| ID | Menu dan route | Field atau struktur utama | Aksi/permission Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-MST-16 | Katalog Penukaran Poin `/vireoPointRedemptionCatalog.crux` | Status, nama, deskripsi, item hadiah, periode, outlet, poin, nominal tambahan | Tambah, ubah, hapus | Loyalty | Defer | Verified UI |
| VIR-MST-17 | Grup Kupon `/vireoCouponGroup.crux` | Status, nama, deskripsi, periode, outlet, nilai, minimum belanja, tipe, kode kupon | Tambah, generate, ubah, hapus | Promo/kupon | Defer | Verified UI |
| VIR-MST-18 | Komisi `/vireoCommission.crux` | Status, nama, deskripsi, periode, jenis, outlet, staff | Tambah, ubah, hapus | Komisi | Defer | Verified UI |
| VIR-MST-19 | Program Loyalti `/vireoLoyaltyProgram.crux` | Status, periode, outlet, item, dasar poin, penukaran, bonus member, batas dan masa berlaku | Tambah, ubah, hapus | Loyalty | Defer | Verified UI |
| VIR-MST-20 | Pengaturan Channel `/vireoChannelConfiguration.crux` | Pilih channel; ketersediaan dan harga produk per channel | Ubah, impor, ekspor | Pengaturan > Integrasi | Defer | Verified UI |
| VIR-MST-21 | Tipe Item `/vireoTipeItem.crux` | Nama tipe dan timer | Tambah, ubah, hapus | Operasional dapur | Defer | Verified UI |
| VIR-MST-22 | Status `/vireoStatus.crux` | Nama, outlet, tampil pada antrean pesanan | Tambah, ubah, hapus | Operasional dapur | Defer | Verified UI |
| VIR-MST-23 | Template Broadcast WhatsApp `/vireoTemplateBroadcastNotification.crux` | Outlet, kategori, tipe, aktif, template tersedia, subject, deskripsi | Tambah, ubah, hapus | Tidak ada pada MVP | Remove | Verified UI |

## 5. Transaksi dan Persediaan

| ID | Menu dan route | Field atau struktur utama | Aksi/permission khusus | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-TRX-01 | Purchase Order `/purchaseorder.crux` | Nomor, nomor eksternal, outlet, pemasok, tanggal, catatan; tab Dokumen/Rincian/Rangkuman | Ubah, batalkan, setujui/tolak pembatalan B2B, terima barang, bayar | Persediaan > Pembelian | Keep | Header Verified UI; rincian/status Needs sandbox |
| VIR-TRX-02 | Penerimaan Barang `/purchaseorderreceipt.crux` | PO, status, outlet, pemasok, item, diterima, sisa, tanggal | Dibuka dari PO | Tahap penerimaan pada Pembelian | Merge | Partial; membutuhkan PO |
| VIR-TRX-03 | Stok Masuk `/vireo_stock_inbound.crux` | Nomor, nomor eksternal, outlet, tanggal, catatan; item, stok, qty, harga satuan, subtotal | Tambah, impor, ekspor, ubah | Penyesuaian > Tambah stok | Merge | Verified UI; posting Needs sandbox |
| VIR-TRX-04 | Stok Keluar `/vireo_stock_outbound.crux` | Nomor, nomor eksternal, outlet, tanggal, catatan; item, stok, qty | Tambah, ekspor, ubah | Penyesuaian > Kurangi stok | Merge | Verified UI; posting Needs sandbox |
| VIR-TRX-05 | Stok Transfer `/vireo_stock_transfer.crux` | Nomor, sumber, tujuan, tanggal, catatan; item, stok, qty | Tambah, ekspor, ubah | Persediaan > Transfer cepat | Merge | Verified UI; posting Needs sandbox |
| VIR-TRX-06 | Transfer Order `/vireo_stock_transfer_order.crux` | Nomor, sumber, tujuan, tanggal, status, catatan; item, stok, qty | Ubah, batalkan | Persediaan > Transfer | Merge | Verified UI; lifecycle Needs sandbox |
| VIR-TRX-07 | Surat Jalan Transfer `/vireo_stock_transfer_shipment.crux` | Transfer order, sumber, tujuan, tanggal, status | Tambah setelah memilih transfer order, ubah | Status `Dikirim` pada Transfer | Merge | Partial; membutuhkan transfer order |
| VIR-TRX-08 | Penerimaan Transfer `/vireo_stock_transfer_receipt.crux` | Surat jalan, sumber, tujuan, tanggal | Tambah setelah memilih surat jalan, ubah | Status `Diterima` pada Transfer | Merge | Partial; membutuhkan surat jalan |
| VIR-TRX-09 | Stok Opname `/vireo_stock_opname.crux` | Nomor, tanggal, outlet, status, catatan; item, stok sistem, aktual, selisih | Tambah semua item, tambah, ubah, batalkan | Persediaan > Opname | Keep | Verified UI; posting Needs sandbox |
| VIR-TRX-10 | Stock Adjustment `/vireo_stock_adjustment.crux` | Nomor, tanggal, outlet, catatan; sistem, aktual, selisih, HPP | Tambah semua item, tambah, ekspor, ubah | Opname & Penyesuaian | Merge | Verified UI; HPP posting Needs sandbox |
| VIR-TRX-11 | Proses Komisi `/vireoCommissionProcess.crux` | Komisi, tipe periode, sumber periode, status | Simpan ke draft | Komisi | Defer | Verified UI |
| VIR-TRX-12 | Adjustment Deposito `/vireoAdjustmentDeposito.crux` | Pelanggan, tanggal, outlet, alasan, tipe dokumen, status, jumlah | Ubah, setujui, tolak | Saldo pelanggan | Defer | Verified UI; approval Needs sandbox |
| VIR-TRX-13 | Adjustment Poin `/vireoAdjustmentPoint.crux` | Pelanggan, tanggal, outlet, alasan, tipe dokumen, status, jumlah | Ubah, setujui, tolak | Loyalty | Defer | Verified UI; approval Needs sandbox |
| VIR-TRX-14 | Produksi `/vireo_stock_kitting.crux` | Nomor, outlet, tanggal, catatan, item hasil, qty; komponen dan stok | Tambah, ubah | Persediaan > Produksi | Defer | Verified UI; posting Needs sandbox |
| VIR-TRX-15 | Bongkar Produksi `/vireo_stock_unkitting.crux` | Nomor, outlet, tanggal, catatan, item dibongkar, qty; komponen dan stok | Tambah, ubah | Reversal Produksi | Defer | Verified UI; posting Needs sandbox |
| VIR-TRX-16 | Pembatalan Penjualan `/vireoSalesCancel.crux` | Periode, nomor pesanan, tanggal, outlet, pelanggan, status, total | Cari, batalkan | Penjualan > Refund & Void | Keep | Daftar Verified UI; pembatalan Needs sandbox |

## 6. Laporan

Semua laporan memakai filter periode dan outlet global bila relevan. Vireo memisahkan setiap sudut pandang menjadi tombol laporan; Binyo memakai lebih sedikit halaman dengan pilihan tampilan dan pengelompokan.

### 6.1 Laporan stok

| ID | Laporan | Filter/kolom utama | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|
| VIR-RPT-STK-01 | Kartu Stok | Item, tampilkan stok kosong; awal, pembelian, masuk, keluar, transfer, adjustment, penjualan, kitting, akhir, unit, HPP, total | Histori stok | Keep | Verified UI |
| VIR-RPT-STK-02 | Stok Terkini | Item, stok kosong; outlet, item, qty, unit, HPP, total | Posisi stok | Keep | Verified UI |
| VIR-RPT-STK-03 | Riwayat Stok | Periode dan sumber perubahan | Histori stok | Merge | Partial; hasil tidak tersedia |
| VIR-RPT-STK-04 | Peringatan Stok Minimum | Item di bawah ambang | Stok bermasalah | Keep | Partial; hasil tidak tersedia |
| VIR-RPT-STK-05 | Stok Nomor Seri | Item, sertakan serial keluar | Serial tracking | Defer | Verified UI |
| VIR-RPT-STK-06 | Riwayat Stok Nomor Seri | Item dan nomor seri | Serial tracking | Defer | Verified UI |
| VIR-RPT-STK-07 | Laporan Stok Opname | Nomor dokumen | Riwayat opname | Keep | Verified UI |
| VIR-RPT-STK-08 | Stok Transfer Menggantung | Item | Pengecualian Transfer | Keep | Verified UI |

### 6.2 Laporan pembelian

| ID | Laporan | Filter utama | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|
| VIR-RPT-PUR-01 | Pembelian per Kategori | Periode/outlet | Laporan Pembelian, grup kategori | Merge | Verified UI |
| VIR-RPT-PUR-02 | Pembelian per Produk | Periode/outlet | Laporan Pembelian, grup produk | Keep | Verified UI |
| VIR-RPT-PUR-03 | Pembelian per Pemasok | Pemasok | Laporan Pembelian, grup pemasok | Keep | Verified UI |
| VIR-RPT-PUR-04 | Pembelian Rinci | Pemasok dan status | Laporan Pembelian, tampilan rinci | Keep | Verified UI |
| VIR-RPT-PUR-05 | Pembelian per Grup | Grup produk | Laporan Pembelian, grup/tag | Merge | Verified UI |

### 6.3 Laporan penjualan

| ID | Laporan | Filter/kolom utama | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|
| VIR-RPT-SAL-01 | Penjualan per Kategori | Kategori, channel; qty, bersih, rata-rata | Grup kategori | Keep | Verified UI |
| VIR-RPT-SAL-02 | Penjualan per Grup Produk | Grup, channel | Grup/tag produk | Merge | Verified UI |
| VIR-RPT-SAL-03 | Penjualan per Merek | Merek, channel | Filter merek | Merge | Verified UI |
| VIR-RPT-SAL-04 | Penjualan per Produk | Produk, channel; kategori, kode, qty, bersih, rata-rata | Grup produk | Keep | Verified UI |
| VIR-RPT-SAL-05 | Penjualan per Gerai | Channel; tanggal, outlet, pajak, transaksi, total, rata-rata | Grup outlet/hari | Keep | Verified UI |
| VIR-RPT-SAL-06 | Penjualan per Pelanggan | Pelanggan, kolom tambahan, channel | Grup pelanggan | Keep | Verified UI |
| VIR-RPT-SAL-07 | Penjualan per Metode Pembayaran | Metode, channel; frekuensi, total, rata-rata | Grup pembayaran | Keep | Verified UI |
| VIR-RPT-SAL-08 | Penjualan per Perangkat | Perangkat, channel; tanggal, transaksi, total | Grup perangkat | Keep | Verified UI |
| VIR-RPT-SAL-09 | Penjualan Ringkas | Ringkasan komponen penjualan | Tampilan ringkas | Keep | Verified UI |
| VIR-RPT-SAL-10 | Penjualan Rinci | Status, channel, grup harga; struk, staff, pelanggan, diskon, pajak, service, platform fee, pembayaran | Tampilan rinci | Keep | Verified UI |
| VIR-RPT-SAL-11 | Penjualan Harian | Channel; tanggal, transaksi, total, rata-rata | Grup hari | Keep | Verified UI |
| VIR-RPT-SAL-12 | Penjualan per Promosi | Promo dan channel | Grup promo | Keep | Verified UI |
| VIR-RPT-SAL-13 | Penjualan per Staff | Nama/email | Grup kasir | Keep | Verified UI |
| VIR-RPT-SAL-14 | Penjualan per Channel | Channel; tanggal, transaksi, total | Grup channel | Defer | Verified UI |
| VIR-RPT-SAL-15 | Penjualan per Varian Produk | Produk dan channel | Grup varian | Keep | Verified UI |
| VIR-RPT-SAL-16 | Penjualan per Grup Harga | Produk dan channel; nilai reguler dan grup harga | Harga per outlet/channel | Merge | Verified UI |

### 6.4 Laporan laba

| ID | Laporan | Kolom utama | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|
| VIR-RPT-PRO-01 | Laba per Produk | Tanggal, item, kode, qty, penjualan, diskon, `Pembelian`, platform fee, laba | Laba kotor per produk | Keep | Verified UI; arti `Pembelian` Needs sandbox |
| VIR-RPT-PRO-02 | Laba per Hari | Tanggal, outlet, penjualan, diskon, `Pembelian`, laba | Laba kotor per hari | Keep | Verified UI; arti `Pembelian` Needs sandbox |

### 6.5 Laporan lainnya

| ID | Laporan | Filter atau keluaran | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|
| VIR-RPT-OTH-01 | Rekapitulasi Kas | Cari; shift, outlet, perangkat, staff, kas masuk/keluar, penjualan, kembalian | Shift & Kas | Keep | Verified UI |
| VIR-RPT-OTH-02 | Diskon | Struk, tanggal, total, diskon | Laporan Penjualan | Merge | Verified UI |
| VIR-RPT-OTH-03 | Harga Pokok Penjualan | Pilih item dan periode | Rincian HPP | Keep | Partial; hasil tidak tersedia |
| VIR-RPT-OTH-04 | Modifier | Cari modifier | Laporan Produk | Merge | Verified UI |
| VIR-RPT-OTH-05 | Pajak | Cari struk | Pajak & Biaya | Keep | Verified UI |
| VIR-RPT-OTH-06 | Komisi | Staff | Komisi | Defer | Verified UI |
| VIR-RPT-OTH-07 | Biaya Layanan | Cari struk | Pajak & Biaya | Merge | Verified UI |
| VIR-RPT-OTH-08 | Riwayat Poin | Pelanggan, hanya member aktif | Loyalty | Defer | Verified UI |
| VIR-RPT-OTH-09 | Penggunaan Kupon | Grup kupon | Promo/Kupon | Defer | Verified UI |
| VIR-RPT-OTH-10 | Transaksi Fasilitas | Periode/outlet | Fasilitas | Remove | Partial; hasil tidak tersedia |
| VIR-RPT-OTH-11 | Transaksi Pelanggan | Pelanggan | Laporan Penjualan | Merge | Verified UI |
| VIR-RPT-OTH-12 | Topup Deposito | Pelanggan, nomor dokumen | Saldo pelanggan | Defer | Verified UI |
| VIR-RPT-OTH-13 | Mutasi Deposito | Pelanggan, tipe mutasi | Saldo pelanggan | Defer | Verified UI |
| VIR-RPT-OTH-14 | Proses Komisi | Periode/outlet | Komisi | Defer | Partial; hasil tidak tersedia |
| VIR-RPT-OTH-15 | Kombinasi Modifier | Cari | Analisis menu | Defer | Verified UI |
| VIR-RPT-OTH-16 | Kas Masuk/Kas Keluar | Catatan | Shift & Kas | Keep | Verified UI |
| VIR-RPT-OTH-17 | Sisa Kredit Kasbon | Pelanggan, hanya kasbon berjalan | Pembayaran tempo | Defer | Verified UI |

Permission Vireo memisahkan akses lihat dan ekspor untuk setiap kelompok serta sebagian besar laporan. Binyo cukup memakai permission `report.view_cost`, `report.view_sales`, `report.view_inventory`, dan `report.export` agar matriks akses tidak membengkak.

## 7. Akunting

| ID | Menu dan route | Field atau struktur utama | Aksi/permission Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-ACC-01 | Akun Buku Besar `/vireoglaccount.crux` | Kode, nama, parent, tipe akun, header/manual | Tambah, impor, ekspor, ubah, hapus | Mapping akun ekspor | Defer | Verified UI |
| VIR-ACC-02 | Buat Jurnal `/vireoglentry.crux` | Nomor, tanggal, total debit/kredit, selisih; akun, kasir, referensi, keterangan, debit/kredit | Buat, ubah, hapus | Event dan ekspor akunting | Defer | Verified UI |
| VIR-ACC-03 | Neraca & Laba Rugi | Akun dan saldo periode | Tidak terlihat aksi khusus | Ekspor ke sistem akunting | Defer | Partial; hasil tidak tersedia |
| VIR-ACC-04 | Neraca Bulanan | Mode tampilan; saldo awal, debit, kredit, saldo akhir | Tidak terlihat aksi khusus | Ekspor ke sistem akunting | Defer | Verified UI |
| VIR-ACC-05 | Laporan Jurnal | Jurnal periode | Tidak terlihat aksi khusus | Ekspor jurnal | Defer | Partial; hasil tidak tersedia |

Penjualan yang diamati membuat jurnal kas/metode pembayaran terhadap penjualan, serta debit `Cost of Goods Sold` terhadap satu atau beberapa akun persediaan. Binyo tidak membuat general ledger pada MVP, tetapi event ekspornya harus tetap seimbang.

## 8. Akun dan Pengaturan

| ID | Menu dan route | Field atau struktur utama | Aksi/permission Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-ADM-01 | Perangkat Kasir `/vireomobile.crux` | Terminal, nama, outlet, staff, aktif, kedaluwarsa | Tambah, ubah, hapus, reset perangkat | Operasional > Perangkat | Keep | Verified UI |
| VIR-ADM-02 | Staff `/vireoStaff.crux` | Email, nama, telepon, login, alamat; outlet dan grup akses; tab komisi | Tambah, ubah, hapus | Operasional > Staff & Akses | Simplify | Verified UI |
| VIR-ADM-03 | Perusahaan `/vireoCompany.crux` | Profil, kontak, alamat, tanggal berdiri, pembulatan, mata uang, desimal, kredit, wilayah, NPWP, bank | Ubah | Pengaturan > Bisnis | Simplify | Verified UI |
| VIR-ADM-04 | Grup Akses `/vireoAccessGroup.crux` | Nama dan daftar permission | Tambah dan simpan | Operasional > Staff & Akses > Peran | Simplify | Verified UI |
| VIR-ADM-05 | Pengaturan `/vireoConfiguration.crux` | Penomoran PO, stok masuk/keluar/transfer, opname, adjustment, produksi, bongkar, transfer order, surat jalan, penerimaan | Ubah | Pengaturan > Penomoran | Simplify | Verified UI |
| VIR-ADM-06 | Waralaba `/vireoFranchise.crux` | Outlet, merek, perusahaan, waralaba, status, syarat | Setujui dan kemampuan franchise lanjutan | Tidak ada pada MVP | Defer | Partial; tenant sudah terhubung |

## 9. Integrasi

| ID | Menu dan route | Field atau struktur utama | Aksi/permission Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|---|---|
| VIR-INT-01 | Grab `/vireoGrab.crux` | Pilihan GrabFood dan GrabMart | Permission ubah, hapus, publish, sinkronisasi | Pengaturan > Integrasi | Defer | Verified UI |
| VIR-INT-02 | GrabFood `/vireoGrabFood.crux` | Halaman tersedia, konfigurasi tidak tampil pada tenant | Integrasi GrabFood dan promosi | Pengaturan > Integrasi | Defer | Partial |
| VIR-INT-03 | GrabMart `/vireoGrabMart.crux` | Halaman tersedia, konfigurasi tidak tampil pada tenant | Integrasi GrabMart dan menu | Pengaturan > Integrasi | Defer | Partial |
| VIR-INT-04 | GoBiz `/vireoGoBiz.crux` | Pilihan Menu | Integrasi GoBiz | Pengaturan > Integrasi | Defer | Verified UI |
| VIR-INT-05 | Menu GoBiz `/vireoGoBizMenu.crux` | Kategori, produk, harga toko, ketersediaan, harga channel | Ubah, impor, publish | Pengaturan > Integrasi | Defer | Verified UI; publish Needs sandbox |
| VIR-INT-06 | AngkasaPura 2 | Tidak terlihat di navigasi | Permission tersedia | Tidak ada | Defer | Permission only |
| VIR-INT-07 | E-Wallet | Tidak terlihat di navigasi | Permission tersedia | Metode pembayaran/provider | Defer | Permission only |

## 10. Kemampuan Aplikasi POS

Daftar berikut berasal dari Grup Akses. Audit layar aplikasi POS Vireo belum dilakukan.

| ID | Permission POS Vireo | Binyo | Keputusan | Audit |
|---|---|---|---|---|
| VIR-POS-01 | Otorisasi void transaksi | PIN/persetujuan manajer | Keep | Permission only |
| VIR-POS-02 | Split/gabung bill | Manajemen meja/bill | Defer | Permission only |
| VIR-POS-03 | Cash in/out dan tutup shift | Shift & Kas | Keep | Permission only |
| VIR-POS-04 | Override harga manual | Permission ubah harga | Keep | Permission only |
| VIR-POS-05 | Cetak ulang struk | Transaksi > Cetak ulang | Keep | Permission only |
| VIR-POS-06 | Hapus baris pesanan | Permission edit keranjang | Keep | Permission only |
| VIR-POS-07 | Stock opname | Operasi gudang mobile | Defer | Permission only |
| VIR-POS-08 | Purchase order | Operasi gudang mobile | Defer | Permission only |
| VIR-POS-09 | Lanjut ke pembayaran | Permission checkout | Keep | Permission only |
| VIR-POS-10 | Menu Order | Jual dan Transaksi | Keep | Permission only |
| VIR-POS-11 | Menu Cash Recapitulation | Shift & Kas | Keep | Permission only |
| VIR-POS-12 | Menu Customer | Pelanggan pada transaksi | Merge | Permission only |
| VIR-POS-13 | Menu Facility | Fasilitas | Remove | Permission only |
| VIR-POS-14 | Menu Staff | Penggantian kasir terkontrol | Simplify | Permission only |
| VIR-POS-15 | Menu Promotion | Promo otomatis/kode | Merge | Permission only |
| VIR-POS-16 | Menu Production | Produksi | Defer | Permission only |
| VIR-POS-17 | Menu Stock Opname | Opname | Defer | Permission only |
| VIR-POS-18 | Menu Facility Report | Fasilitas | Remove | Permission only |
| VIR-POS-19 | Menu Commission Report | Komisi | Defer | Permission only |
| VIR-POS-20 | Menu Order Status Board | Antrean/KDS | Defer | Permission only |
| VIR-POS-21 | Menu Deposit Mutation | Saldo pelanggan | Defer | Permission only |
| VIR-POS-22 | Menu Web Order Session | Web order | Defer | Permission only |
| VIR-POS-23 | Menu Pay Later Report | Pembayaran tempo | Defer | Permission only |
| VIR-POS-24 | Menu Loyalty Point History | Loyalty | Defer | Permission only |
| VIR-POS-25 | Menu Purchase Order | Pembelian mobile | Defer | Permission only |
| VIR-POS-26 | Menu Purchase Order Receipt | Penerimaan mobile | Defer | Permission only |
| VIR-POS-27 | Menu Stock Transfer Receipt | Penerimaan transfer mobile | Defer | Permission only |
| VIR-POS-28 | Lihat Monitor Antrean | Antrean/KDS | Defer | Permission only |
| VIR-POS-29 | Perbarui Antrean Pesanan | Antrean/KDS | Defer | Permission only |

## 11. Pola permission Vireo

- Master data umumnya memiliki permission lihat, ubah, dan hapus.
- Transaksi memiliki permission khusus sesuai lifecycle, misalnya batalkan, terima barang, bayar, setujui, atau tolak.
- Laporan memisahkan permission lihat dan ekspor sampai tingkat laporan.
- Perangkat memiliki permission reset.
- Integrasi memiliki permission ubah, hapus, publish, dan sinkronisasi.
- POS memiliki permission per tindakan dan per menu.

Binyo mempertahankan permission berisiko, bukan seluruh kombinasi menu:

- `catalog.manage`
- `sale.sell`
- `price.override`
- `discount.apply`
- `sale.void`
- `sale.refund`
- `payment.reverse`
- `shift.close`
- `shift.close_other`
- `cash.manage`
- `inventory.adjust`
- `inventory.transfer`
- `purchase_order.post`
- `goods_receipt.post`
- `report.view_sales`
- `report.view_inventory`
- `report.view_cost`
- `report.export`
- `replica.view`
- `replica.manage`
- `staff.manage`
- `device.manage`
- `integration.publish`

## 12. Gap menuju verifikasi perilaku 1:1

- Status lengkap dan transisi Purchase Order, penerimaan, transfer, opname, serta adjustment.
- Dampak posting dan pembatalan terhadap stok, HPP, jurnal, dan laporan.
- Field rincian PO setelah pemasok dipilih.
- Konfigurasi tenant untuk GrabFood dan GrabMart.
- Perilaku publish/sinkronisasi integrasi.
- Layar dan interaksi aplikasi POS Vireo.
- Validasi, pesan error, dan aturan wajib setiap form.
- Formula internal serta efek `Hitung Ulang HPP`.

Semua gap di atas membutuhkan tenant sandbox atau dokumentasi resmi vendor. Tidak boleh diuji dengan mengubah data operasional tenant referensi.
