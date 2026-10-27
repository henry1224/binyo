---
title: "Validasi HPP VireoPOS dan Binyo"
subtitle: "Bukti UI, hipotesis, rumus target, skenario uji, dan gerbang penerimaan"
author: "Binyo POS"
date: "2026-10-27"
status: "Draft"
---

# Validasi HPP VireoPOS dan Binyo

Dokumen ini memisahkan fakta yang terlihat pada Vireo dari aturan HPP yang dipilih untuk Binyo. Nilai dan identitas bisnis pada tenant referensi tidak disalin.

## 1. Bukti read-only dari Vireo

| Area | Bukti yang terlihat | Kesimpulan aman |
|---|---|---|
| Produk bahan baku | HPP ditampilkan per satuan dasar seperti `ml`, `gr`, atau `pcs` | Vireo mendukung biaya per unit kecil |
| Produk resep | Tab Komponen memuat item, jumlah, satuan, dan persentase | Vireo menampilkan rincian kontribusi komponen; formula HPP belum terbukti |
| Additional cost | Daftar produk menunjukkan komponen biaya tambahan; pada detail dapat tampil sebagai komponen tanpa nama | Overhead dapat dimodelkan sebagai komponen, tetapi mekanismenya belum terbukti |
| Hitung ulang | Detail produk memiliki tombol `Hitung Ulang HPP` | Ada proses kalkulasi ulang; dampaknya belum diuji |
| Stok masuk | Form memuat jumlah, harga satuan, dan subtotal | Stok masuk dapat membawa nilai biaya |
| Stock adjustment | Detail item memuat HPP | Vireo menyediakan nilai biaya pada adjustment; dampak posting belum terbukti |
| Kartu stok | Kolom awal, pembelian, masuk, keluar, transfer, adjustment, penjualan, produksi, akhir, HPP, total | Laporan menggabungkan kuantitas dan valuasi |
| Stok terkini | Qty, satuan, HPP, total | Nilai stok dihitung dari kuantitas dan HPP |
| Laporan laba | Penjualan, diskon, kolom `Pembelian`, platform fee, laba | Kolom `Pembelian` tampak berfungsi sebagai biaya/HPP, tetapi labelnya ambigu |
| Jurnal otomatis | Debit `Cost of Goods Sold` dan kredit satu atau beberapa akun persediaan | Penjualan mengakui HPP dan mengurangi nilai persediaan |
| Keseimbangan jurnal | Jumlah kredit akun persediaan sama dengan debit COGS pada sampel yang diamati | Sampel jurnal HPP seimbang; aturan agregasinya belum terbukti |
| Laporan HPP | Filter item dan periode tersedia | Struktur hasil belum terlihat karena data tidak muncul pada kombinasi read-only yang diperiksa |

## 2. Hal yang belum terbukti pada Vireo

- Apakah biaya bahan memakai moving average, last purchase cost, FIFO, atau metode lain.
- Apakah HPP disimpan per outlet atau global.
- Kapan penerimaan mengubah HPP: saat draft, diterima, atau dibayar.
- Cara pembelian sebagian dan pembatalan penerimaan memperbarui biaya.
- Cara konversi karton, liter, kilogram, dan satuan dasar disimpan.
- Cara biaya tambahan pembelian dialokasikan.
- Cara additional cost resep dibuat dan diperbarui.
- Apakah `Hitung Ulang HPP` mengubah transaksi lama atau hanya nilai produk berikutnya.
- Perlakuan stok minus, transfer lintas outlet, refund, void, dan backdated receipt.
- Presisi internal dan waktu pembulatan.
- Definisi pasti kolom `Pembelian` pada laporan laba.

## 3. Keputusan HPP Binyo

Binyo memakai moving weighted average per SKU dan outlet.

Alasan:

- Mudah dijelaskan kepada owner.
- Cocok untuk penerimaan bertahap.
- Tidak membutuhkan layer FIFO.
- Mendukung bahan baku dengan satuan kecil.
- Histori laba tetap stabil melalui snapshot HPP saat penjualan.

### Satuan

- Setiap SKU memiliki satu base unit.
- Stok, resep, dan HPP disimpan pada base unit.
- Satuan beli memakai faktor konversi.
- Faktor konversi disalin ke baris penerimaan sebagai snapshot.
- Kuantitas dan biaya unit memakai fixed decimal, bukan floating point.

```text
base_quantity = purchase_quantity x conversion_factor
receipt_unit_cost =
    (line_cost + allocated_additional_cost) / base_quantity
```

### Moving weighted average

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
- Modifier menambah HPP bila mengonsumsi bahan.
- Overhead non-stok dapat menjadi additional cost resep terpisah, bukan produk persediaan palsu.

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

## 4. Contoh hitung sintetis

### Penerimaan

```text
Stok awal       : 10 pcs x Rp1.000 = Rp10.000
Penerimaan      : 10 pcs x Rp2.000 = Rp20.000
Stok setelahnya : 20 pcs
Average cost    : (Rp10.000 + Rp20.000) / 20 = Rp1.500/pcs
```

### Konversi satuan

```text
Pembelian       : 2 karton
Konversi        : 1 karton = 12 pcs
Jumlah base     : 24 pcs
Total biaya     : Rp240.000
Biaya base unit : Rp10.000/pcs
```

### Resep

```text
Komponen A : 20 ml x Rp10/ml  = Rp200
Komponen B : 5 gr x Rp40/gr   = Rp200
Kemasan    : 1 pcs x Rp500    = Rp500
HPP resep                       Rp900
```

### Biaya tambahan pembelian

Biaya pengiriman dialokasikan proporsional terhadap nilai barang yang diterima:

```text
allocated_cost_line =
    additional_cost_total x line_cost / receipt_subtotal
```

Fitur landed cost dapat ditunda; nilai alokasi menjadi nol sampai fitur diaktifkan.

## 5. Perlakuan per peristiwa

| Peristiwa | Kuantitas | Nilai/HPP | Aturan histori |
|---|---:|---|---|
| Opening stock | Bertambah | Biaya awal wajib | Posting sekali; koreksi memakai reversal |
| PO draft | Tidak berubah | Tidak berubah | Belum menjadi stok |
| Penerimaan | Bertambah | Hitung average cost | Simpan biaya dan konversi snapshot |
| Penerimaan sebagian | Bertambah sesuai diterima | Hitung dari bagian diterima | Sisa PO tetap terbuka |
| Penjualan | Berkurang | Pakai average/recipe cost saat selesai | Simpan snapshot pada sale line |
| Void sebelum selesai | Tidak berubah atau reversal draft | Tidak menjadi HPP final | Simpan audit |
| Refund dengan barang kembali | Bertambah | Pakai cost transaksi asal | Buat movement baru |
| Refund tanpa barang kembali | Tidak berubah | Koreksi laba sesuai kebijakan | Simpan alasan |
| Transfer keluar | Berkurang di sumber | Bawa cost snapshot sumber | Dokumen asal tetap ada |
| Transfer masuk | Bertambah di tujuan | Perbarui average cost tujuan | Kaitkan ke transfer yang sama |
| Adjustment keluar | Berkurang | Pakai average cost saat posting | Wajib alasan |
| Adjustment masuk | Bertambah | Pakai cost eksplisit atau average aktif | Wajib alasan |
| Produksi | Komponen berkurang, hasil bertambah | Hasil menerima total biaya komponen | Snapshot resep saat posting |
| Bongkar produksi | Balikkan produksi | Gunakan cost produksi asal | Reversal terlacak |

## 6. Stok minus

Binyo MVP melarang transaksi yang membuat stok SKU terlacak menjadi negatif. Dukungan stok minus dan revaluation ditunda karena membutuhkan kebijakan rekonsiliasi biaya terpisah.

## 7. Jurnal ekspor target

Binyo tidak membuat general ledger pada MVP, tetapi ekspor akunting mengikuti pasangan minimum:

### Penjualan

```text
Debit  Kas/Settlement
Kredit Penjualan
Kredit Pajak terutang, bila exclusive
Kredit Service charge, bila dipisahkan
```

### HPP

```text
Debit  Cost of Goods Sold
Kredit Persediaan
```

Refund dan reversal membentuk entry lawan; record asli tidak dihapus.

## 8. Skenario validasi sandbox

| ID | Skenario | Bukti yang harus cocok | Status |
|---|---|---|---|
| HPP-01 | Opening stock dengan biaya awal | Produk, stok terkini, kartu stok, valuasi | Belum diuji |
| HPP-02 | Stok masuk dengan harga satuan | HPP sebelum/sesudah dan jurnal/ledger | Belum diuji |
| HPP-03 | PO lalu penerimaan penuh | PO, receipt, stok, average cost | Belum diuji |
| HPP-04 | Penerimaan sebagian dua kali dengan harga berbeda | Average cost tiap penerimaan | Belum diuji |
| HPP-05 | Beli karton, stok pcs | Konversi dan biaya per pcs | Belum diuji |
| HPP-06 | Biaya kirim pembelian | Alokasi landed cost | Belum diuji |
| HPP-07 | Produk resep beberapa unit | Total komponen dan persentase | Belum diuji |
| HPP-08 | Modifier mengonsumsi bahan | HPP transaksi termasuk modifier | Belum diuji |
| HPP-09 | Penjualan lalu harga beli berubah | Snapshot transaksi lama tetap | Belum diuji |
| HPP-10 | Transfer antar-outlet dengan biaya berbeda | Sumber, in-transit, tujuan, average cost | Belum diuji |
| HPP-11 | Refund barang kembali | Stok dan HPP memakai transaksi asal | Belum diuji |
| HPP-12 | Void dan pembatalan | Tidak ada movement/jurnal yatim | Belum diuji |
| HPP-13 | Adjustment masuk dan keluar | Qty, nilai, alasan, audit | Belum diuji |
| HPP-14 | Penjualan melebihi stok tersedia | Transaksi ditolak tanpa movement atau jurnal | Belum diuji |
| HPP-15 | Hitung ulang HPP | Batas perubahan transaksi lama/baru | Belum diuji |
| HPP-16 | Laporan laba dan jurnal | HPP laporan = debit COGS = kredit persediaan | Belum diuji |
| HPP-17 | Pembulatan bahan per ml/gr | Total ledger tidak bocor karena pembulatan | Belum diuji |
| HPP-18 | Backdated receipt | Kebijakan periode dan histori | Belum diuji |

## 9. Gerbang penerimaan Binyo

- Satu transaksi menghasilkan HPP yang sama pada sale line, stock movement, laporan laba, dan ekspor akunting.
- Total debit COGS sama dengan total kredit persediaan.
- Perhitungan retry tidak membuat movement atau jurnal ganda.
- Perubahan master produk, resep, konversi, atau biaya tidak mengubah histori yang sudah diposting.
- Penerimaan sebagian hanya menilai jumlah yang benar-benar diterima.
- Transfer tidak menciptakan atau menghilangkan nilai persediaan, kecuali selisih yang diposting eksplisit.
- Refund, void, pembatalan penerimaan, dan bongkar produksi memakai reversal terlacak.
- Semua nilai menggunakan fixed decimal dan aturan pembulatan yang diuji.
- Laporan menandai HPP kosong atau nol.

## 10. Batas validasi

Audit Vireo saat ini read-only. Tombol `Hitung Ulang HPP`, simpan, posting, batalkan, setujui, tolak, publish, dan sinkronisasi tidak dijalankan. Validasi perilaku membutuhkan tenant sandbox berisi data sintetis.
