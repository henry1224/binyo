---
title: "Validasi Harga Modal dan Stok VireoPOS dan Binyo"
subtitle: "Bukti UI, alur stok, rumus target, skenario uji, dan gerbang penerimaan"
author: "Binyo POS"
date: "2026-11-10"
status: "Draft"
---

# Validasi Harga Modal dan Stok VireoPOS dan Binyo

Dokumen ini memisahkan fakta yang terlihat pada Vireo, status implementasi Binyo saat ini, dan aturan harga modal yang dipilih untuk Binyo. Audit dilakukan secara read-only. Identitas akun, pelanggan, dan nomor transaksi tidak disalin.

Tanggal observasi terbaru: 10 November 2026.

Dokumen memakai istilah `harga modal`. Istilah `HPP` dipertahankan hanya saat mengutip nama tombol, label laporan, atau ID skenario Vireo.

## 1. Ringkasan keputusan

- Jumlah stok berasal dari penerimaan barang, stok awal, transfer, penyesuaian, dan penjualan; bukan dari harga modal.
- Harga pembelian membentuk harga modal per satuan dasar, misalnya rupiah per `ml`, `gr`, atau `pcs`.
- Produk minuman memakai resep. Saat penjualan selesai, stok setiap bahan berkurang sesuai jumlah pada resep.
- Harga modal minuman adalah jumlah biaya seluruh bahan yang dipakai.
- Harga modal transaksi disimpan sebagai snapshot agar laporan lama tidak berubah saat harga beli atau resep berubah.
- Binyo memakai rata-rata bergerak per pilihan produk dan outlet. Formula internal Vireo tidak disalin karena belum terbukti.

## 2. Bukti read-only dari Vireo

| Area | Bukti yang terlihat | Kesimpulan aman |
|---|---|---|
| Produk bahan baku | Harga modal ditampilkan per satuan dasar seperti `ml`, `gr`, atau `pcs` | Vireo mendukung biaya per unit kecil |
| Produk resep | Tab Komponen memuat item, jumlah, satuan, dan persentase | Vireo menampilkan rincian kontribusi komponen; formula harga modal belum terbukti |
| Additional cost | Daftar produk menunjukkan komponen biaya tambahan; pada detail dapat tampil sebagai komponen tanpa nama | Overhead dapat dimodelkan sebagai komponen, tetapi mekanismenya belum terbukti |
| Hitung ulang | Detail produk memiliki tombol `Hitung Ulang HPP` | Ada proses kalkulasi ulang; dampaknya belum diuji |
| Stok masuk | Form memuat jumlah, harga satuan, dan subtotal | Stok masuk dapat membawa nilai biaya |
| Stock adjustment | Detail item memuat harga modal | Vireo menyediakan nilai biaya pada adjustment; dampak posting belum terbukti |
| Kartu stok | Kolom awal, pembelian, masuk, keluar, transfer, adjustment, penjualan, produksi, akhir, harga modal, dan total | Laporan menggabungkan jumlah dan nilai stok |
| Stok terkini | Jumlah, satuan, harga modal, dan total | Nilai stok dihitung dari jumlah dan harga modal |
| Laporan laba | Penjualan, diskon, kolom `Pembelian`, platform fee, dan laba | Kolom `Pembelian` tampak berfungsi sebagai biaya barang yang terjual, tetapi labelnya ambigu |
| Jurnal otomatis | Debit `Cost of Goods Sold` dan kredit satu atau beberapa akun persediaan | Penjualan mengakui biaya barang yang terjual dan mengurangi nilai persediaan |
| Keseimbangan jurnal | Jumlah kredit akun persediaan sama dengan debit `Cost of Goods Sold` pada sampel yang diamati | Sampel jurnal seimbang; aturan penggabungannya belum terbukti |
| Laporan HPP | Filter item dan periode tersedia | Struktur hasil belum terlihat karena data tidak muncul pada kombinasi read-only yang diperiksa |

### 2.1 Produk bahan baku Dripp Palm Sugar

Detail produk menunjukkan:

- Kategori `Bahan Baku`.
- Satuan dasar `ml`.
- Harga jual `Rp0`.
- Harga modal yang tampil `Rp70/ml`.
- Tersedia pengaturan manajemen stok, larangan stok minus, dan peringatan stok minimum.
- Tersedia tombol `Hitung Ulang HPP`, tetapi tombol tidak dijalankan.

Nilai `Rp70/ml` adalah keadaan yang tampil saat audit. Nilai tersebut bukan bukti formula Vireo dan tidak sama dengan contoh pembelian pengguna `Rp110.000 / 750 ml = Rp146,6667/ml`.

### 2.2 Resep produk minuman

Pada produk `Ice Coffee Lite`, tab `Komponen` menampilkan bahan berikut:

| Bahan | Jumlah | Satuan | Kontribusi biaya yang tampil |
|---|---:|---|---:|
| Dripp Palm Sugar | 20 | ml | 20,52% |
| Creamer Powder | 15 | gr | 3,41% |
| Air mineral | 30 | ml | 0,30% |
| Gelas plastik | 1 | pcs | 17,28% |
| Es batu | 100 | gr | 1,04% |
| Biji kopi | 9 | gr | 15,16% |
| Susu UHT | 85 | ml | 21,12% |
| Ice Cream Vanilla | 30 | gr | 12,07% |

Persentase hanya didokumentasikan sebagai nilai tampilan. Jumlah persentase tidak dipakai untuk menyimpulkan formula karena komponen lain, biaya tambahan, dan aturan pembulatan belum terbukti.

### 2.3 Bukti pembelian Dripp Palm Sugar

Laporan `Pembelian per Produk` untuk 1 Januari 2024 sampai 10 November 2026 menunjukkan:

| Data | Nilai yang tampil |
|---|---:|
| Jumlah pembelian | 23.080 ml |
| Rata-rata Nominal Transaksi | Rp88,83 |
| Jumlah yang telah diterima | 23.080 ml |
| Subtotal | Rp2.050.288,80 |

Label `Rata-rata Nominal Transaksi` dicatat sesuai UI. Nilai tersebut tidak dianggap sebagai harga modal berjalan karena produk menampilkan `Rp70/ml`.

### 2.4 Bukti pemotongan bahan saat penjualan

Kartu stok Dripp Palm Sugar untuk 1 September sampai 10 November 2026 menunjukkan:

| Data | Nilai yang tampil |
|---|---:|
| Stok awal | 2.340 ml |
| Pembelian | 0 ml |
| Penjualan | -8.900 ml |
| Stok akhir | -6.560 ml |
| Harga modal | Rp70/ml |
| Nilai stok akhir | -Rp459.200 |

Riwayat stok 3 sampai 10 November 2026 menampilkan 31 pergerakan Dripp Palm Sugar. Pola pengurangan yang terlihat:

- `20 ml` untuk satu pemakaian resep.
- `40 ml`, `60 ml`, atau `120 ml` saat satu transaksi memuat beberapa porsi atau beberapa produk yang memakai bahan sama.
- Setiap pengurangan terhubung ke dokumen transaksi penjualan.

Kesimpulan aman: Vireo mengurangi stok bahan resep ketika penjualan selesai. Data tenant juga memperlihatkan stok negatif, sehingga kontrol stok minus Vireo tidak boleh disalin tanpa perbaikan.

### 2.5 Alur Vireo yang teramati

1. Bahan baku dibuat dengan satuan dasar dan harga modal per satuan dasar.
2. Produk minuman menyimpan daftar komponen beserta jumlah pemakaian.
3. Purchase order mencatat barang, jumlah, harga, pemasok, outlet, dan status.
4. Penerimaan barang mencatat jumlah yang benar-benar diterima. Form stok masuk juga membawa harga satuan dan subtotal.
5. Penjualan produk resep membuat pengurangan stok untuk setiap bahan.
6. Kartu stok dan riwayat stok menampilkan jumlah masuk, keluar, penjualan, stok akhir, harga modal, dan nilai stok.
7. Laporan laba dan jurnal memakai nilai harga modal untuk menghitung biaya barang yang terjual.

Waktu pasti pembaruan harga modal belum terbukti: saat dokumen dibuat, barang diterima, pembayaran selesai, atau tombol `Hitung Ulang HPP` dijalankan.

## 3. Status implementasi Binyo saat ini

### 3.1 Sudah tersedia

- Outlet, peran, izin, perangkat, dan laci kas.
- Kategori, produk, pilihan produk, kode produk, barcode, harga jual, dan status.
- Field harga modal manual `default_purchase_cost` pada form pilihan produk.
- Kolom `product_type` dan `track_stock` pada database.
- Ketersediaan dan harga jual per outlet.
- Pencarian, filter, dan pagination katalog.

### 3.2 Belum tersedia

- Pemilihan jenis produk pada UI; produk baru saat ini selalu disimpan sebagai `standard`.
- Kontrol pelacakan stok pada UI; payload memiliki `track_stock`, tetapi form tidak menampilkan pengaturannya.
- Satuan dasar dan konversi satuan beli, misalnya `1 botol = 750 ml`.
- Saldo stok per outlet.
- Riwayat pergerakan stok.
- Pemasok, purchase order, dan penerimaan barang.
- Resep bahan baku.
- Transaksi penjualan dan pemotongan stok otomatis.
- Harga modal rata-rata per outlet.
- Snapshot harga modal pada transaksi.

### 3.3 Alur Binyo yang berjalan sekarang

1. Admin membuat outlet dan mengatur akses pengguna.
2. Admin membuat kategori, produk, dan pilihan produk.
3. Admin mengisi harga jual dan harga modal manual pada pilihan produk.
4. Admin mengatur ketersediaan dan harga jual per outlet. Jenis produk dan pelacakan stok belum dapat dipilih melalui UI.
5. Sistem belum membuat saldo atau pergerakan stok. Mengisi harga modal tidak menambah jumlah stok.
6. Sistem belum menerima pembelian, belum menyimpan resep, dan belum mengurangi bahan saat produk terjual.

Status ini diverifikasi dari route, controller katalog, model pilihan produk, dan seluruh migration yang tersedia pada 10 November 2026. Field stok dan harga modal saat ini masih metadata katalog, belum menjadi ledger persediaan.

## 4. Hal yang belum terbukti pada Vireo

- Apakah biaya bahan memakai moving average, last purchase cost, FIFO, atau metode lain.
- Apakah harga modal disimpan per outlet atau global.
- Kapan penerimaan mengubah harga modal: saat draft, diterima, atau dibayar.
- Cara pembelian sebagian dan pembatalan penerimaan memperbarui biaya.
- Cara konversi karton, liter, kilogram, dan satuan dasar disimpan.
- Cara biaya tambahan pembelian dialokasikan.
- Cara biaya tambahan resep dibuat dan diperbarui.
- Apakah `Hitung Ulang HPP` mengubah transaksi lama atau hanya nilai produk berikutnya.
- Perlakuan stok minus, transfer lintas outlet, refund, void, dan backdated receipt.
- Presisi internal dan waktu pembulatan.
- Definisi pasti kolom `Pembelian` pada laporan laba.

## 5. Keputusan harga modal Binyo

Binyo memakai rata-rata bergerak tertimbang per pilihan produk dan outlet.

Alasan:

- Mudah dijelaskan kepada owner.
- Cocok untuk penerimaan bertahap.
- Tidak membutuhkan layer FIFO.
- Mendukung bahan baku dengan satuan kecil.
- Histori laba tetap stabil melalui snapshot harga modal saat penjualan.

### Satuan

- Setiap pilihan produk memiliki satu satuan dasar.
- Stok, resep, dan harga modal disimpan pada satuan dasar.
- Satuan beli memakai faktor konversi.
- Faktor konversi disalin ke baris penerimaan sebagai snapshot.
- Jumlah dan biaya satuan memakai angka desimal tetap, bukan angka pecahan biner.

```text
base_quantity = purchase_quantity x conversion_factor
receipt_unit_cost =
    (line_cost + allocated_additional_cost) / base_quantity
```

### Rata-rata bergerak tertimbang

```text
new_average_cost =
    ((quantity_before x average_cost_before)
    + (quantity_received x receipt_unit_cost))
    / (quantity_before + quantity_received)
```

Jika stok sebelum penerimaan nol, biaya rata-rata baru sama dengan biaya penerimaan.
Rumus hanya dijalankan bila jumlah stok setelah penerimaan lebih dari nol.

### Resep

```text
recipe_cost =
    sum(component_quantity x component_average_cost_at_outlet)
```

- Kemasan dicatat sebagai komponen bila stok dan biayanya perlu dilacak.
- Modifier menambah harga modal bila mengonsumsi bahan.
- Biaya operasional non-stok dapat menjadi biaya tambahan resep terpisah, bukan produk persediaan palsu.

### Penjualan dan laba

```text
cost_of_goods_sold = quantity_sold x unit_cost_snapshot
gross_profit = net_product_sales - cost_of_goods_sold
gross_margin = gross_profit / net_product_sales
```

- `unit_cost_snapshot` disimpan saat transaksi selesai.
- Pajak, service charge, tip, dan platform fee dilaporkan terpisah.
- Harga beli atau resep baru tidak mengubah transaksi lama.
- Refund memakai snapshot transaksi asal.

### Alur target Binyo

1. Bahan dibuat dengan satuan dasar, misalnya Dripp Palm Sugar dalam `ml`.
2. Satuan beli ditambahkan, misalnya `1 botol = 750 ml`.
3. Purchase order mencatat jumlah dan harga yang dipesan, tetapi belum mengubah stok.
4. Penerimaan barang menambah stok dalam satuan dasar dan memperbarui harga modal rata-rata.
5. Produk minuman menyimpan resep bahan dalam satuan dasar masing-masing.
6. Saat penjualan selesai, sistem mengurangi setiap bahan, menyimpan pergerakan stok, dan menyalin harga modal ke transaksi.
7. Kartu stok, nilai persediaan, laba kotor, refund, dan audit membaca data dari pergerakan serta snapshot tersebut.

## 6. Contoh hitung sintetis

### Penerimaan

```text
Stok awal       : 10 pcs x Rp1.000 = Rp10.000
Penerimaan      : 10 pcs x Rp2.000 = Rp20.000
Stok setelahnya : 20 pcs
Harga modal rata-rata: (Rp10.000 + Rp20.000) / 20 = Rp1.500/pcs
```

### Konversi satuan

```text
Pembelian       : 2 karton
Konversi        : 1 karton = 12 pcs
Jumlah base     : 24 pcs
Total biaya     : Rp240.000
Biaya satuan dasar: Rp10.000/pcs
```

Contoh Dripp Palm Sugar:

```text
Pembelian       : 1 botol
Konversi        : 1 botol = 750 ml
Total biaya     : Rp110.000
Stok bertambah  : 750 ml
Biaya per ml    : Rp146,6667

Pemakaian resep : 20 ml
Stok berkurang  : 20 ml
Biaya bahan     : sekitar Rp2.933
```

### Resep

```text
Komponen A : 20 ml x Rp10/ml  = Rp200
Komponen B : 5 gr x Rp40/gr   = Rp200
Kemasan    : 1 pcs x Rp500    = Rp500
Harga modal resep               Rp900
```

### Biaya tambahan pembelian

Biaya pengiriman dialokasikan proporsional terhadap nilai barang yang diterima:

```text
allocated_cost_line =
    additional_cost_total x line_cost / receipt_subtotal
```

Alokasi ongkos pembelian dapat ditunda; nilainya nol sampai fitur diaktifkan.

## 7. Perlakuan per peristiwa

| Peristiwa | Jumlah stok | Harga modal | Aturan histori |
|---|---:|---|---|
| Stok awal | Bertambah | Biaya awal wajib | Catat sekali; koreksi memakai pembalikan |
| Draft pembelian | Tidak berubah | Tidak berubah | Belum menjadi stok |
| Penerimaan | Bertambah | Hitung harga modal rata-rata | Simpan biaya dan konversi saat penerimaan |
| Penerimaan sebagian | Bertambah sesuai diterima | Hitung dari bagian diterima | Sisa PO tetap terbuka |
| Penjualan | Berkurang | Pakai harga modal rata-rata atau biaya resep saat selesai | Simpan harga modal pada baris penjualan |
| Void sebelum selesai | Tidak berubah atau membalik draft | Tidak menjadi harga modal final | Simpan audit |
| Refund dengan barang kembali | Bertambah | Pakai harga modal transaksi asal | Buat pergerakan baru |
| Refund tanpa barang kembali | Tidak berubah | Koreksi laba sesuai kebijakan | Simpan alasan |
| Transfer keluar | Berkurang di sumber | Bawa harga modal sumber | Dokumen asal tetap ada |
| Transfer masuk | Bertambah di tujuan | Perbarui harga modal rata-rata tujuan | Kaitkan ke transfer yang sama |
| Penyesuaian keluar | Berkurang | Pakai harga modal rata-rata saat dicatat | Wajib alasan |
| Penyesuaian masuk | Bertambah | Pakai biaya eksplisit atau harga modal aktif | Wajib alasan |
| Produksi | Komponen berkurang, hasil bertambah | Hasil menerima total biaya komponen | Simpan resep saat dicatat |
| Bongkar produksi | Balikkan produksi | Gunakan biaya produksi asal | Pembalikan terlacak |

## 8. Stok minus

Setiap outlet memiliki pengaturan larangan stok minus yang aktif secara default. Jika dinonaktifkan, penjualan memakai harga modal rata-rata terakhir, harga modal transaksi ditandai `estimated`, dan saldo negatif masuk daftar rekonsiliasi. Penerimaan berikutnya tidak mengubah transaksi lama; penilaian ulang histori bukan MVP.

## 9. Jurnal ekspor target

Binyo tidak membuat buku besar pada MVP, tetapi ekspor akunting mengikuti pasangan minimum:

### Penjualan

```text
Debit  Kas/Settlement
Kredit Penjualan
Kredit Pajak terutang, bila exclusive
Kredit Service charge, bila dipisahkan
```

### Biaya barang yang terjual

```text
Debit  Cost of Goods Sold
Kredit Persediaan
```

Refund dan pembalikan membuat catatan lawan; catatan asli tidak dihapus.

## 10. Skenario validasi sandbox

| ID | Skenario | Bukti yang harus cocok | Status |
|---|---|---|---|
| HPP-01 | Stok awal dengan harga modal awal | Produk, stok terkini, kartu stok, valuasi | Belum diuji |
| HPP-02 | Stok masuk dengan harga satuan | Harga modal sebelum/sesudah dan jurnal persediaan | Belum diuji |
| HPP-03 | Pembelian lalu penerimaan penuh | Pembelian, penerimaan, stok, dan harga modal rata-rata | Belum diuji |
| HPP-04 | Penerimaan sebagian dua kali dengan harga berbeda | Harga modal rata-rata tiap penerimaan | Belum diuji |
| HPP-05 | Beli karton, stok pcs | Konversi dan biaya per pcs | Belum diuji |
| HPP-06 | Biaya kirim pembelian | Alokasi ongkos pembelian | Belum diuji |
| HPP-07 | Produk resep beberapa unit | Total komponen dan persentase | Belum diuji |
| HPP-08 | Modifier mengonsumsi bahan | Harga modal transaksi termasuk modifier | Belum diuji |
| HPP-09 | Penjualan lalu harga beli berubah | Snapshot transaksi lama tetap | Belum diuji |
| HPP-10 | Transfer antar-outlet dengan biaya berbeda | Sumber, perjalanan, tujuan, dan harga modal rata-rata | Belum diuji |
| HPP-11 | Refund barang kembali | Stok dan harga modal memakai transaksi asal | Belum diuji |
| HPP-12 | Void dan pembatalan | Tidak ada pergerakan atau jurnal yatim | Belum diuji |
| HPP-13 | Penyesuaian masuk dan keluar | Jumlah, nilai, alasan, dan audit | Belum diuji |
| HPP-14 | Penjualan melebihi stok tersedia | Ditolak saat larangan aktif; saat nonaktif, harga modal perkiraan dan saldo minus tercatat | Belum diuji |
| HPP-15 | Hitung ulang HPP | Batas perubahan transaksi lama/baru | Belum diuji |
| HPP-16 | Laporan laba dan jurnal | Harga modal laporan = debit biaya barang terjual = kredit persediaan | Belum diuji |
| HPP-17 | Pembulatan bahan per ml/gr | Total jurnal persediaan tidak selisih karena pembulatan | Belum diuji |
| HPP-18 | Penerimaan dengan tanggal lampau | Kebijakan periode dan histori | Belum diuji |

## 11. Gerbang penerimaan Binyo

- Satu transaksi menghasilkan harga modal yang sama pada baris penjualan, pergerakan stok, laporan laba, dan ekspor akunting.
- Total debit biaya barang terjual sama dengan total kredit persediaan.
- Pengiriman ulang transaksi tidak membuat pergerakan atau jurnal ganda.
- Perubahan master produk, resep, konversi, atau biaya tidak mengubah histori yang sudah diposting.
- Penerimaan sebagian hanya menilai jumlah yang benar-benar diterima.
- Transfer tidak menciptakan atau menghilangkan nilai persediaan, kecuali selisih yang diposting eksplisit.
- Refund, void, pembatalan penerimaan, dan bongkar produksi memakai pembalikan terlacak.
- Semua nilai menggunakan angka desimal tetap dan aturan pembulatan yang diuji.
- Laporan menandai harga modal kosong, nol, atau perkiraan.

## 12. Batas validasi

Audit Vireo saat ini read-only. Tombol `Hitung Ulang HPP`, simpan, posting, batalkan, setujui, tolak, publish, dan sinkronisasi tidak dijalankan. Validasi perilaku membutuhkan tenant sandbox berisi data sintetis.
