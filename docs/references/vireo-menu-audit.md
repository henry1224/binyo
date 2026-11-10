---
title: "Audit VireoPOS dan Master Data Binyo"
subtitle: "Pemetaan master data, stok, pembelian, penjualan, harga modal, dan keputusan Binyo"
author: "Binyo POS"
date: "2026-11-10"
status: "Draft"
---

# Audit VireoPOS dan Master Data Binyo

Tanggal observasi terbaru: 10 November 2026

Referensi: <https://eretail.vireopos.com/#/vireodashboard.crux>

Audit dilakukan pada sesi yang disediakan pengguna. Dokumen ini tidak menyalin identitas akun, outlet, pelanggan, nomor dokumen, atau nilai transaksi nyata.

Inventaris 1:1 terdapat pada [`vireo-feature-matrix.md`](vireo-feature-matrix.md). Bukti stok, rumus harga modal, dan skenario uji terdapat pada [`vireo-hpp-validation.md`](vireo-hpp-validation.md).

Istilah `HPP` dipakai hanya saat mengutip label Vireo. UI Binyo memakai istilah `harga modal`.

## 1. Kesimpulan

Vireo memiliki cakupan operasional luas, tetapi banyak pekerjaan dipecah menjadi menu teknis. Binyo mengambil kemampuan intinya lalu menyusun ulang berdasarkan tugas pengguna.

Kesimpulan utama untuk katalog dan stok:

- Kategori hanya untuk pengelompokan, filter, dan tampilan. Kategori tidak menentukan apakah item dijual, dibeli, atau mengurangi stok.
- Jenis produk menentukan perilaku. Binyo memerlukan `standard`, `recipe`, `ingredient`, dan `service`.
- Kemasan tidak memerlukan jenis atau master terpisah. Kemasan memakai jenis `ingredient` dan kategori `Kemasan`.
- Produk yang dibeli lalu dijual langsung memakai `standard` dengan pelacakan stok aktif.
- Produk racikan memakai `recipe`; penjualan mengurangi komponen resep, bukan stok produk racikan.
- Nilai harga modal tidak menambah stok. Stok hanya berubah melalui stok awal, penerimaan, penjualan, transfer, atau penyesuaian.

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

### 3.1 Keputusan seluruh master data

| Master data | Keputusan | Bentuk di Binyo | Alasan |
|---|---|---|---|
| Kategori | Keep | Nama, urutan tampil, status | Dibutuhkan untuk filter dan tampilan; tidak memegang aturan stok |
| Produk dan pilihan produk | Keep | Identitas, jenis, kode, barcode, harga jual, status | Menjadi pusat hubungan penjualan, resep, pembelian, dan stok |
| Satuan | Add | `pcs`, `gr`, `kg`, `ml`, `liter`, `botol`, `karton` | Stok, resep, dan pembelian tidak aman tanpa satuan yang jelas |
| Konversi satuan produk | Add | Contoh `1 botol = 750 ml` | Pembelian memakai kemasan, sedangkan stok dan resep memakai satuan dasar |
| Pemasok | Keep | Kode, nama, kontak, alamat, status | Diperlukan untuk pembelian dan histori harga |
| Grup modifier | Keep setelah resep dasar | Pilihan, batas jumlah, tambahan harga, bahan opsional | Modifier dapat mengubah harga dan pemakaian bahan |
| Outlet | Keep | Identitas, zona waktu, aturan stok minus, status | Saldo dan harga modal dihitung per outlet |
| Metode pembayaran | Keep | Tunai, kartu manual, transfer, QRIS manual | Diperlukan saat transaksi penjualan dibuat |
| Pajak dan service charge | Keep sederhana | Profil aktif per bisnis atau outlet | Diperlukan untuk total penjualan; tidak dicampur dengan harga modal |
| Alasan koreksi stok | Add sebagai daftar tetap | Rusak, kedaluwarsa, terbuang, hasil opname, pemakaian internal, lainnya | Audit harus konsisten; menu master terpisah belum diperlukan |
| Pelanggan | Keep minimum | Nama dan kontak opsional | Berguna untuk histori; tidak menghalangi penjualan tanpa pelanggan |
| Atribut | Merge | Menjadi pilihan produk, misalnya ukuran | Menghindari master terpisah yang menggandakan konsep pilihan produk |
| Grup produk atau tag | Defer | Tambah hanya bila satu produk perlu beberapa kelompok laporan | Kategori sudah cukup untuk pilot kedai kopi |
| Merek | Defer | Field atau master ditambahkan bila pelaporan membutuhkannya | Tidak memengaruhi penjualan, resep, atau stok pilot |
| Grup harga | Defer | Harga per outlet sudah tersedia | Master tambahan membuat aturan harga tumpang tindih |
| Promo dan kupon | Defer | Setelah penjualan dasar stabil | Tidak boleh mendahului transaksi, refund, dan laporan dasar |
| Channel dan integrasi | Defer | Setelah target GrabFood, GoFood, atau web order dipilih | Harga dan mapping channel belum dibutuhkan pilot |
| Loyalty, poin, deposito | Defer | Setelah kebutuhan bisnis terukur | Menambah ledger dan risiko rekonsiliasi baru |
| Komisi | Defer | Setelah aturan komisi disetujui | Bukan kebutuhan kasir, stok, atau pembelian inti |
| Tipe item dan status dapur | Defer | Tambah bersama KDS | Tidak diperlukan sebelum alur dapur nyata dipilih |
| Grup fasilitas | Remove | Tidak dibuat | Tidak ada alur reservasi atau fasilitas pada scope Binyo |
| Template broadcast WhatsApp | Remove | Tidak dibuat | Marketing automation bukan fungsi POS inti |
| Gudang atau lokasi stok terpisah | Remove dari MVP | Satu saldo per produk dan outlet | Pilot tidak membutuhkan bin atau gudang di dalam outlet |

### 3.2 Fakta kategori Vireo

Halaman kategori Vireo yang diperiksa hanya menampilkan `Nama` dan `Urutan Tampilan`. Kategori `Bahan Baku` adalah nama kategori milik tenant, bukan tipe sistem khusus dan bukan bukti adanya field `indikator bahan baku`.

Vireo tampaknya menentukan perilaku melalui pengaturan produk seperti manajemen stok, manajemen penjualan, serta tab `Komponen`. Binyo tidak menyalin ketergantungan pada nama kategori.

Contoh kategori Binyo tetap boleh memakai nama berikut:

- `Bahan Baku` untuk susu, kopi, sirup, gula, es, dan bahan makanan.
- `Kemasan` untuk cup, tutup, sedotan, kotak, dan kantong.
- `Cold Coffee`, `Hot Coffee`, `Makanan`, atau `Snack` untuk menu jual.

Nama tersebut membantu manusia mencari data. Jenis produk tetap menentukan perilaku sistem.

### 3.3 Jenis produk Binyo

| Jenis | Tampil di POS | Dapat dibeli | Pelacakan stok | Saat penjualan selesai | Contoh |
|---|---|---|---|---|---|
| `standard` | Ya | Ya bila stok dilacak | Opsional | Kurangi stok produk sendiri bila aktif | Air mineral botol, roti kemasan |
| `recipe` | Ya | Tidak langsung | Produk jadi tidak dilacak pada MVP | Kurangi setiap komponen resep | Ice Coffee Lite |
| `ingredient` | Tidak | Ya | Wajib | Tidak dijual langsung; berkurang melalui resep atau koreksi | Susu UHT, biji kopi, cup |
| `service` | Ya | Tidak | Tidak | Tidak mengubah stok | Biaya layanan khusus |

Aturan tambahan:

- `outlet_products.is_available` menentukan apakah produk jual tersedia pada outlet; field ini bukan pengganti jenis produk.
- `track_stock` tetap diperlukan untuk `standard`. Nilainya selalu aktif untuk `ingredient` dan selalu nonaktif untuk `recipe` serta `service`.
- Semua item berstok boleh menjadi komponen resep. Air mineral botol dapat dijual langsung sekaligus dipakai oleh resep bila memang proses bisnis memerlukannya.
- Produk setengah jadi atau batch, misalnya sirup racikan yang diproduksi lebih dulu, ditunda. Tambahkan alur produksi hanya ketika stok hasil produksi benar-benar dihitung terpisah.

### 3.4 Status master data Binyo saat ini

| Area | Status saat ini | Batas yang ditemukan |
|---|---|---|
| Kategori | Tersedia dan dapat dibuat/diubah | Hanya nama, urutan tampil, dan status; tidak ada hapus permanen |
| Produk dan pilihan | Tersedia dan dapat dibuat/diubah | Jenis produk dipilih pada produk; satuan dasar dan satuan beli diatur per pilihan |
| Kode, barcode, harga jual | Tersedia | Harga jual hanya berlaku untuk `standard`, `recipe`, dan `service` |
| Harga jual dan ketersediaan outlet | Tersedia | Berlaku untuk produk jual |
| Harga modal awal | Tersedia pada alur stok awal | Wajib dicatat bersama jumlah, satuan dasar, dan outlet; tidak lagi diubah dari form produk |
| `product_type` | Aktif pada validasi, daftar, dan form | Produk lama tetap `standard`; kategori tidak mengubah jenis secara otomatis |
| `track_stock` | Aktif dengan aturan per jenis produk | Wajib untuk `ingredient`, opsional untuk `standard`, nonaktif untuk `recipe` dan `service` |
| Satuan dan konversi beli | Tersedia | Preset global; belum dipakai penerimaan karena alur pembelian belum tersedia |
| Outlet | Tersedia dan dapat dibuat/diubah | Sudah memiliki aturan stok minus, tetapi belum ada ledger stok |
| Staff dan perangkat | Tampil sebagai daftar | Belum ada route pengelolaan pada halaman operasional saat ini |
| Peran, izin, laci kas | Tersedia pada database/seeder | Belum menjadi master yang dikelola lengkap melalui UI |
| Pemasok, resep, modifier | Belum tersedia | Belum ada tabel, route, controller, atau UI operasional |
| Saldo dan pergerakan stok | Fondasi tersedia | Stok awal menghasilkan ledger, saldo per outlet, posisi stok, dan kartu stok minimum |
| Pembelian dan penerimaan | Belum tersedia | Belum dapat menambah stok atau menghitung harga modal rata-rata dari pemasok |
| Penjualan dan pemotongan bahan | Belum tersedia | Belum ada transaksi yang dapat mengurangi produk atau komponen resep |

Route mutasi yang tersedia saat ini hanya kategori, produk, dan outlet. Satuan memakai preset global tetap, bukan CRUD tenant. Data lain tidak boleh dianggap sudah berjalan hanya karena rancangan tabelnya terdapat pada dokumentasi.

### 3.5 Kolom dan struktur yang dipertahankan

| Struktur | Keputusan | Alasan |
|---|---|---|
| `products.category_id` | Pertahankan | Filter dan tampilan katalog |
| `products.product_type` | Pertahankan dan tampilkan di form | Satu pilihan terkontrol lebih aman daripada banyak checkbox perilaku |
| `product_variants.sku` dan `barcode` | Pertahankan | Identitas pembelian, penjualan, dan integrasi scanner |
| `product_variants.sell_price` | Pertahankan untuk produk jual | Harga jual bukan harga modal |
| `product_variants.track_stock` | Pertahankan dengan aturan per jenis | Produk standar dapat dijual tanpa stok; bahan selalu dilacak |
| `outlet_products.is_available` | Pertahankan untuk produk jual | Ketersediaan dapat berbeda per outlet |
| `outlet_products.price_override` | Pertahankan | Harga jual dapat berbeda per outlet tanpa grup harga |
| Status aktif/nonaktif | Pertahankan | Menghentikan penggunaan tanpa menghapus histori |

### 3.6 Kolom dan struktur target

`product_variants.base_unit_id`, `units`, dan `product_units` sudah tersedia. Struktur lain pada tabel berikut tetap menjadi pekerjaan tahap selanjutnya.

| Struktur | Isi minimum | Alasan |
|---|---|---|
| `product_variants.base_unit_id` | Satuan dasar pilihan produk | Semua stok, resep, dan harga modal membutuhkan satu sumber satuan |
| `units` | Kode, nama, jenis ukuran, presisi, status | Menyamakan `ml`, `gr`, dan `pcs` tanpa teks bebas |
| `product_units` | Satuan, faktor ke satuan dasar, penanda satuan beli | Menyimpan `1 botol = 750 ml` atau `1 karton = 12 pcs` |
| `recipe_components` | Produk resep, komponen, jumlah dalam satuan dasar | Dasar pemotongan bahan dan perhitungan harga modal menu |
| `suppliers` | Kode, nama, kontak, alamat, status | Pembelian dan histori pemasok |
| `purchase_orders` dan baris | Pemasok, outlet, item, satuan beli, jumlah, harga | Memisahkan pesanan dari stok yang benar-benar diterima |
| `goods_receipts` dan baris | Jumlah diterima, konversi, biaya per satuan dasar | Hanya penerimaan yang menambah stok dan memperbarui harga modal |
| `stock_movements` | Jenis, perubahan jumlah, biaya, sumber, alasan | Sumber kebenaran dan audit semua perubahan stok |
| `inventory_balances` | Jumlah dan harga modal rata-rata per pilihan produk/outlet | Membaca saldo cepat tanpa menjumlah seluruh histori setiap kali |
| `outlet_products.low_stock_threshold` | Batas stok minimum opsional | Peringatan stok harus spesifik per outlet |
| Snapshot harga modal pada baris penjualan | Harga modal saat transaksi selesai | Laporan lama tidak berubah ketika pembelian atau resep berubah |

### 3.7 Yang dipindah, dihilangkan, atau tidak ditambah

| Keputusan | Alasan |
|---|---|
| Pindahkan `default_purchase_cost` dari form produk ke alur `Stok awal` sebagai `Harga modal awal` | Harga tanpa jumlah dan outlet tidak membentuk nilai stok; harga modal berjalan berasal dari penerimaan |
| Hapus `default_purchase_cost` dari model target setelah migrasi data selesai | Harga modal rata-rata harus per outlet dan tersimpan pada saldo/pergerakan, bukan global pada pilihan produk |
| Jangan tambah `is_raw_material` pada kategori | Memindahkan atau mengganti nama kategori tidak boleh mengubah perilaku stok |
| Jangan tambah checkbox `is_sellable`, `is_purchasable`, dan `is_recipe` terpisah | Kombinasi boolean mudah menghasilkan kondisi tidak valid; `product_type` sudah cukup |
| Jangan buat master `Kemasan` terpisah | Cup dan kotak memiliki perilaku stok yang sama dengan bahan lain |
| Jangan buat menu stok masuk dan stok keluar terpisah | Satu alur penyesuaian dengan jumlah, arah, dan alasan lebih mudah diaudit |
| Jangan buat menu penerimaan terpisah dari pembelian | Penerimaan adalah tahap dari dokumen pembelian yang sama |
| Jangan tambah merek, grup produk, grup harga, gudang, lot, serial, atau kedaluwarsa pada MVP | Belum ada kebutuhan pilot yang membenarkan data dan alur tambahan |

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

MVP tidak memiliki rantai persetujuan terpisah. Izin `purchase_order.post` membatasi siapa yang boleh mengubah draft menjadi `Dipesan`. Persetujuan berdasarkan nominal ditambahkan hanya bila pilot membutuhkannya.

### Transfer

Empat menu transfer Vireo digabung menjadi satu dokumen dengan status:

```text
Draft -> Dikirim -> Diterima sebagian -> Selesai
                  -> Dibatalkan
```

Stok sumber berkurang saat `Dikirim`. Stok tujuan bertambah saat `Diterima`. Selisih wajib memiliki alasan dan jejak audit.

### Alur bahan baku sampai produk terjual

1. Buat bahan sebagai `ingredient`, pilih kategori, dan tentukan satuan dasar. Contoh: Dripp Palm Sugar memakai `ml`.
2. Tambahkan satuan beli pada bahan. Contoh: `1 botol = 750 ml`.
3. Buat pemasok dan draft pembelian untuk outlet tujuan. Draft belum mengubah stok.
4. Terima barang seluruh atau sebagian. Penerimaan `1 botol` menambah `750 ml`, membuat pergerakan stok, dan menghitung harga modal rata-rata outlet.
5. Buat menu sebagai `recipe`, lalu isi komponen dalam satuan dasar. Contoh Ice Coffee Lite memakai Dripp Palm Sugar `20 ml` dan Gelas plastik `1 pcs`.
6. Saat penjualan selesai, sistem membuat pengurangan untuk setiap komponen resep dan menyimpan harga modal transaksi.
7. Kartu stok membaca pergerakan; laporan laba membaca harga jual bersih dikurangi snapshot harga modal transaksi.

### Alur per jenis item

| Kasus | Master yang dipakai | Saat pembelian | Saat penjualan |
|---|---|---|---|
| Bahan minuman atau makanan | `ingredient`, satuan, pemasok | Penerimaan menambah stok bahan | Berkurang melalui resep |
| Kemasan | `ingredient`, kategori `Kemasan`, satuan `pcs` | Penerimaan menambah stok kemasan | Berkurang sebagai komponen resep |
| Barang jual langsung | `standard` dengan stok aktif | Penerimaan menambah stok produk | Stok produk sendiri berkurang |
| Menu racikan | `recipe` dan komponen | Tidak dibeli sebagai produk jadi | Seluruh komponen berkurang |
| Jasa atau biaya non-stok | `service` | Tidak ada penerimaan | Tidak mengubah stok |

### Aturan koreksi yang menutup gap operasional

- Stok awal wajib mencatat outlet, jumlah, satuan dasar, dan harga modal awal dalam satu tindakan.
- Stok rusak, kedaluwarsa, terbuang, atau dipakai internal memakai penyesuaian keluar dengan alasan dan catatan.
- Opname menyimpan jumlah sistem, jumlah fisik, selisih, penghitung, dan waktu pencatatan; hanya selisih yang membuat pergerakan.
- Void sebelum transaksi selesai tidak membuat pemakaian bahan final.
- Refund produk racikan tidak otomatis mengembalikan bahan karena minuman atau makanan biasanya sudah dibuat.
- Refund barang standar hanya menambah stok bila barang fisik benar-benar kembali dan layak dijual.
- Dokumen penerimaan, pergerakan stok, dan transaksi selesai tidak dihapus. Koreksi memakai pembalikan yang menunjuk record asal.
- Resep tidak boleh berisi dirinya sendiri atau membentuk siklus antarproduk.
- Stok minus ditolak secara default. Pengecualian harus mengikuti pengaturan outlet dan masuk daftar rekonsiliasi.

### Gap register Binyo

Gap jenis produk, satuan, stok awal, ledger, saldo, posisi stok, dan kartu stok minimum sudah ditutup pada fondasi M2. Tabel berikut memuat gap yang masih terbuka.

| Prioritas | Gap | Dampak bila belum ada | Penutup gap |
|---|---|---|---|
| P0 | Pemasok, pembelian, penerimaan belum ada | Stok masuk dan harga modal tidak dapat diaudit | Buat pemasok, pembelian, dan penerimaan sebagian |
| P0 | Resep belum ada | Penjualan menu tidak dapat mengurangi bahan | Tambah komponen resep dan validasi satuan/siklus |
| P0 | Penjualan belum ada | Belum ada titik pemotongan stok atau snapshot harga modal | Buat transaksi selesai yang mencatat stok secara idempoten |
| P1 | Opname dan penyesuaian belum ada | Selisih fisik, rusak, dan terbuang tidak tercatat benar | Buat opname dan koreksi beralasan |
| P1 | Refund dan void belum terhubung stok | Stok dapat kembali padahal bahan sudah terpakai | Terapkan kebijakan per jenis produk dan kondisi barang |
| P1 | Peringatan stok minimum belum ada | Pembelian terlambat | Tambah batas minimum per outlet dan daftar tindakan |
| P1 | Laporan stok, pembelian, dan laba belum ada | Owner tidak dapat memeriksa hasil alur | Bangun laporan dari ledger dan snapshot, bukan hitung ulang histori |
| P2 | Modifier yang memakai bahan belum ada | Topping atau tambahan tidak mengurangi stok | Hubungkan modifier ke komponen setelah resep dasar stabil |
| P2 | Transfer antar-outlet belum ada | Multi-outlet tidak dapat memindahkan stok dengan audit | Buat satu dokumen berstatus kirim/terima |
| Deferred | Produksi batch atau setengah jadi | Sirup racikan yang disimpan tidak dapat dihitung sebagai stok hasil | Tambah produksi saat proses nyata membutuhkan stok hasil terpisah |
| Deferred | Lot, kedaluwarsa, dan nomor seri | Tidak ada pelacakan per batch | Tambah bila volume, regulasi, atau risiko bahan membuktikan kebutuhan |

Gap P0 adalah rantai minimum yang harus selesai bersama. Membuat hanya form bahan, pembelian, atau resep tanpa ledger dan penjualan akan menghasilkan data yang terlihat lengkap tetapi tidak mengubah stok dengan benar.

### Urutan pengembangan yang aman

1. Klasifikasi produk, satuan dasar, konversi, dan stok awal.
2. Ledger stok, saldo per outlet, serta kartu stok minimum.
3. Pemasok, pembelian, dan penerimaan barang.
4. Resep dan harga modal menu.
5. Penjualan dengan pengurangan stok serta snapshot harga modal.
6. Opname, penyesuaian, refund/void, dan laporan dasar.
7. Modifier stok dan transfer multi-outlet.

Setiap tahap harus memakai data tahap sebelumnya. Tidak ada modul yang boleh membuat saldo stok dengan menulis langsung ke `inventory_balances`; semua perubahan wajib membuat `stock_movements`.

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
- Harga modal yang diambil dari snapshot transaksi.
- Laba kotor.
- Margin laba kotor.
- Baris dengan harga modal belum tersedia atau masih perkiraan.

### Laporan lainnya

| Laporan Vireo | Keputusan Binyo |
|---|---|
| Rekapitulasi Kas | Keep sebagai Laporan Shift & Kas |
| Diskon | Merge ke laporan penjualan |
| Harga Pokok Penjualan | Keep sebagai rincian harga modal |
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

## 6. Harga Modal dan Laba

### Fakta yang diamati pada Vireo

- Bahan baku dapat memakai satuan dasar kecil, misalnya `ml`, `gr`, atau `pcs`.
- Harga modal bahan ditampilkan per satuan dasar.
- Komponen resep memiliki jumlah, satuan, harga, dan persentase kontribusi.
- Produk resep menampilkan total harga komponen.
- Tersedia tombol `Hitung Ulang HPP`.
- Penjualan membentuk jurnal debit `Cost of Goods Sold` dan kredit persediaan.
- Kredit persediaan dapat terpecah ke lebih dari satu akun berdasarkan kelompok bahan.

Belum dapat dipastikan dari UI:

- Waktu tepat harga modal bahan diperbarui.
- Aturan pembulatan internal.
- Perlakuan transaksi lama setelah tombol hitung ulang digunakan.
- Penyebab persentase komponen tertentu tidak selalu berjumlah tepat 100 persen.

### Aturan Harga Modal Binyo

#### Satuan

Setiap pilihan produk memiliki satu satuan dasar sebagai sumber kebenaran stok dan resep. Satuan beli dikonversi saat penerimaan. Harga modal rata-rata disimpan per pilihan produk dan outlet.

Contoh:

```text
1 karton = 12 pcs
1 liter = 1.000 ml
```

Faktor konversi disalin ke baris penerimaan agar perubahan master tidak mengubah histori.

Jumlah dan biaya per satuan dasar memakai angka desimal tetap. Total dokumen dibulatkan ke rupiah saat dicatat agar biaya kecil per `ml` atau `gr` tidak hilang.

#### Biaya penerimaan

```text
base_quantity_received = purchase_quantity x conversion_factor
receipt_cost_per_base_unit = (line_cost + allocated_additional_cost) / base_quantity_received
```

Biaya tambahan bernilai nol pada MVP bila alokasi ongkos pembelian belum diaktifkan.

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

Modifier yang mengonsumsi bahan menambah biaya komponennya. Kemasan dapat dimodelkan sebagai komponen resep agar harga modal terlihat dan stoknya terlacak.

#### Saat penjualan

- Harga modal produk atau resep disalin ke `sale_lines.unit_cost` saat transaksi selesai.
- Transaksi lama tidak berubah saat harga beli atau resep berikutnya berubah.
- `gross_profit = net_product_sales - cost_of_goods_sold`.
- Pajak dan service charge tidak masuk `net_product_sales`; keduanya dilaporkan terpisah.

#### Kondisi khusus

- Stok awal wajib memiliki jumlah dan harga modal awal.
- Produk dengan stok terlacak dilarang minus secara default.
- Jika bisnis mengizinkan stok minus, penjualan memakai rata-rata biaya terakhir dan diberi status harga modal perkiraan.
- Transfer membawa snapshot biaya dari outlet sumber dan memperbarui rata-rata outlet tujuan.
- Refund memakai snapshot harga modal dari transaksi asal.
- Penerimaan atau penyesuaian yang sudah dicatat tidak diedit langsung; koreksi memakai pembalikan.
- Harga modal kosong atau nol selalu tampil sebagai peringatan pada laporan laba.

### UI Harga Modal Binyo

Pada produk tampilkan:

- Harga modal saat ini.
- Sumber terakhir: stok awal, penerimaan, produksi, atau perhitungan resep.
- Waktu pembaruan.
- Rincian kontribusi komponen.
- Simulasi dampak perubahan resep sebelum disimpan.

## 7. Akunting

### Temuan Vireo

- `Akun Buku Besar`: kode, nama, tipe akun, parent, dan izin jurnal manual.
- `Buat Jurnal`: nomor jurnal, tanggal, akun, referensi dokumen, kasir, keterangan, debit, dan kredit.
- `Laporan Akunting`: neraca dan laba rugi, neraca bulanan, serta laporan jurnal.
- Penjualan otomatis membentuk jurnal kas atau metode pembayaran, penjualan, biaya barang terjual, dan persediaan.

### Keputusan Binyo

General ledger lengkap bukan MVP. Binyo tetap menyimpan event dan snapshot yang cukup untuk rekonsiliasi serta ekspor.

- `Laporan > Ekspor Akunting` menyediakan penjualan, pajak, service charge, pembayaran, harga modal, persediaan, refund, dan pergerakan kas.
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

Audit mencakup Dashboard, 23 menu Data Master, 16 menu Transaksi, 48 variasi laporan, Akunting, Akun, Integrasi yang terlihat, serta permission aplikasi POS. Formula backend, aturan pembulatan, perilaku tombol hitung ulang HPP, dan perubahan status dokumen belum dapat dibuktikan hanya dari UI dan harus divalidasi memakai tenant sandbox.
