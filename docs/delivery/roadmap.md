---
title: "Roadmap"
subtitle: "Milestone, acceptance gate, dan urutan pilot"
author: "Binyo POS"
date: "2026-11-10"
status: "Draft"
---

# Roadmap

Roadmap ini menjadi sumber urutan pengembangan. Milestone berbasis hasil, bukan tanggal. Detail keputusan data mengikuti [`vireo-menu-audit.md`](../references/vireo-menu-audit.md) dan [`data-model.md`](../architecture/data-model.md).

Status checkbox:

- `[x]`: sudah tersedia dan diverifikasi.
- `[-]`: tersedia sebagian atau sedang berjalan.
- `[ ]`: belum tersedia.

## Kondisi aplikasi saat ini

### Sudah tersedia

- Autentikasi internal, profil, keamanan akun, bisnis, outlet, dan seed superadmin.
- Kategori, produk, pilihan produk, kode produk, barcode, harga jual, status, serta harga/ketersediaan per outlet.
- Pencarian, filter, pagination, tema terang/gelap/mengikuti sistem, dan pola panel kanan yang konsisten.
- Daftar outlet, staf, dan perangkat; mutasi melalui UI baru tersedia untuk outlet.
- Quality gate aplikasi, test katalog, dan build produksi.

### Belum tersedia

- Pemilihan jenis produk dan kontrol pelacakan stok pada UI.
- Satuan dasar, konversi satuan beli, stok awal, ledger, dan saldo stok.
- Pemasok, pembelian, penerimaan, resep, dan harga modal rata-rata.
- Penjualan, pembayaran, shift, pengurangan stok, refund, dan laporan operasional.

Implementasi berikutnya dimulai dari M2. POS tidak dibangun lebih dahulu karena penjualan harus memiliki aturan stok dan harga modal yang sudah stabil.

## M0 - Persetujuan produk

- [x] Tetapkan segmen kedai kopi/F&B ringan dan satu outlet pilot.
- [x] Tetapkan pembayaran manual serta dukungan perluasan dua atau tiga outlet.
- [x] Tetapkan jenis produk, satuan, resep, harga modal, dan sumber kebenaran stok.
- [x] Review struktur menu, model data, dan batas MVP.
- [-] Review kebutuhan produk, sistem antarmuka, responsif, dan aksesibilitas.
- [ ] Pilih perangkat, printer, scanner, cash drawer, serta kebutuhan kitchen printer pilot.
- [ ] Tetapkan pajak dan service charge pilot.
- [ ] Putuskan kebutuhan impor data lama dari Vireo atau spreadsheet.

Keputusan hardware, pajak, dan impor tidak menghalangi M2-M3. Ketiganya wajib selesai sebelum M4 diuji pada perangkat pilot.

Selesai bila tidak ada keputusan bisnis kritis yang diserahkan kepada developer saat membangun POS dan pilot.

## M1 - Fondasi dan backoffice dasar

Status: fondasi inti selesai; pengelolaan staf dan perangkat masih sebagian.

- [x] Autentikasi, profil, keamanan akun, bisnis, dan outlet.
- [x] Kategori, produk, pilihan produk, kode, barcode, harga, dan ketersediaan outlet.
- [x] Pencarian, filter, pagination, tema, dan pola CRUD panel kanan.
- [x] Seed demo/superadmin dan quality gate aplikasi.
- [-] Daftar staf, peran, perangkat, dan laci kas.
- [ ] Pengelolaan staf, penugasan peran, registrasi perangkat, dan pencabutan akses.
- [ ] Audit log tindakan sensitif.

Gerbang M1: pemilik dapat membuat outlet dan katalog dasar tanpa mengubah database manual. Pengelolaan akses dan perangkat harus selesai sebelum M4.

## M2 - Fondasi produk dan stok

- [ ] Aktifkan jenis produk `standard`, `recipe`, `ingredient`, dan `service` pada validasi serta UI.
- [ ] Terapkan aturan tampilan POS, pembelian, dan pelacakan stok untuk setiap jenis produk.
- [ ] Tambah master satuan, satuan dasar pilihan produk, serta konversi satuan beli.
- [ ] Pindahkan harga modal manual dari produk ke alur stok awal.
- [ ] Tambah stok awal per pilihan produk dan outlet.
- [ ] Tambah `stock_movements` sebagai sumber kebenaran dan `inventory_balances` sebagai saldo cepat.
- [ ] Tampilkan posisi stok dan kartu stok minimum.
- [ ] Terapkan larangan stok minus per outlet dan transaksi stok yang aman dari duplikasi.
- [ ] Tambah test kombinasi jenis produk, konversi, stok awal, dan saldo.

Gerbang M2:

- Produk lama tetap `standard`; kategori bernama `Bahan Baku` tidak otomatis mengubah jenis produk.
- Dripp Palm Sugar dapat dibuat dengan satuan dasar `ml` dan stok awal per outlet.
- Setiap perubahan jumlah menghasilkan pergerakan; saldo sama dengan total pergerakan.
- Harga modal manual tanpa jumlah dan outlet tidak dianggap sebagai stok.

## M3 - Pembelian, penerimaan, dan resep

- [ ] Tambah pemasok.
- [ ] Tambah pembelian berstatus `Draft`, `Dipesan`, `Diterima sebagian`, `Selesai`, dan `Dibatalkan`.
- [ ] Terima seluruh atau sebagian barang dari dokumen pembelian yang sama.
- [ ] Konversi satuan beli ke satuan dasar saat penerimaan.
- [ ] Hitung harga modal rata-rata bergerak per pilihan produk dan outlet.
- [ ] Tambah komponen resep dan larang resep menunjuk dirinya sendiri atau membentuk siklus.
- [ ] Hitung harga modal menu dari komponen pada outlet.
- [ ] Tambah modifier yang mengonsumsi bahan setelah resep dasar lulus pengujian.
- [ ] Tambah test penerimaan sebagian, harga berbeda, konversi, pembatalan, dan resep.

Gerbang M3:

- Pembelian `1 botol` Dripp Palm Sugar senilai `Rp110.000` menambah `750 ml` hanya saat diterima.
- Harga modal bahan menjadi `Rp146,6667/ml` pada kondisi saldo awal nol.
- Resep Ice Coffee Lite memakai Dripp Palm Sugar `20 ml` dan komponen lain dalam satuan dasar.
- Draft pembelian tidak mengubah stok; pembatalan penerimaan membuat pembalikan terlacak.

## M4 - POS online, pembayaran, dan shift

- [ ] Selesaikan pengelolaan staf, peran, perangkat, dan data awal POS.
- [ ] Tambah katalog POS, pencarian, barcode, favorit, keranjang, modifier, dan diskon berizin.
- [ ] Tambah shift, kas masuk/keluar, pembayaran tunai, dan non-tunai manual.
- [ ] Tambah split bill, pembayaran sebagian, struk, void, dan refund berizin.
- [ ] Kurangi stok produk `standard` atau komponen `recipe` saat transaksi selesai.
- [ ] Produk `ingredient` tidak tampil di POS; produk `service` tidak mengubah stok.
- [ ] Simpan snapshot harga modal pada baris penjualan.
- [ ] Cegah retry membuat transaksi, pembayaran, atau pergerakan stok ganda.
- [ ] Refund resep tidak otomatis mengembalikan bahan; barang standar kembali hanya bila fisiknya diterima.
- [ ] Tambah test perjalanan penjualan untuk seluruh jenis produk.

Gerbang M4: satu hari operasional online dapat berjalan dari buka shift, penjualan, pembayaran, pemotongan stok, refund, sampai tutup shift tanpa perubahan database manual.

## M5 - Koreksi stok dan laporan

- [ ] Tambah opname dan penyesuaian beralasan untuk rusak, kedaluwarsa, terbuang, pemakaian internal, dan selisih fisik.
- [ ] Tambah batas stok minimum dan daftar tindakan pembelian.
- [ ] Tambah transfer antar-outlet dengan status kirim dan terima bila outlet kedua digunakan.
- [ ] Tambah dashboard tindakan harian.
- [ ] Tambah laporan penjualan, pembayaran, shift, stok, pembelian, dan laba kotor.
- [ ] Pastikan laporan memakai pergerakan dan snapshot transaksi, bukan menghitung ulang histori.
- [ ] Tambah rekonsiliasi saldo terhadap ledger, pembayaran non-tunai, dan ekspor CSV.

Gerbang M5: pemilik dapat menelusuri setiap perubahan stok dan merekonsiliasi satu siklus operasional tanpa spreadsheet tambahan.

## M6 - Offline, perangkat, dan pilot

- [ ] Tambah database lokal, antrean perintah tahan restart, cursor sync, retry, dan status sinkronisasi.
- [ ] Uji putus jaringan, restart, jam perangkat salah, dan payload duplikat.
- [ ] Tambah outbox dan replica sanitasi bila masih dibutuhkan pilot.
- [ ] Uji printer, scanner, cash drawer, dan struk pada perangkat nyata.
- [ ] Uji keamanan, performa, backup/restore, serta audit akses.
- [ ] Jalankan UAT, pelatihan singkat, SOP gangguan, dan checklist go-live.
- [ ] Jalankan pilot terbatas dan catat masalah nyata.

Gerbang M6: penjualan offline tersinkron tanpa duplikasi atau kehilangan data, perangkat pilot lulus uji, dan pemilik menyetujui go-live.

## Aturan migrasi data yang sudah ada

- Produk lama tetap `standard`; sistem tidak menebak jenis produk dari kategori.
- `track_stock=false` yang ada tetap dipertahankan sampai pengguna memilih aturan stok.
- Nilai `default_purchase_cost` tidak otomatis membuat stok atau harga modal outlet karena tidak memiliki jumlah dan outlet.
- Pemindahan nilai lama ke stok awal hanya dilakukan bila pengguna memasukkan jumlah, outlet, dan menyetujui harga modal awal.
- Produk atau dokumen yang sudah dipakai tidak dihapus; gunakan status nonaktif dan pembalikan.

## Syarat selesai setiap milestone

- Database, validasi, izin, UI, dan pesan error selesai sebagai satu alur.
- Data lintas bisnis dan outlet tetap terisolasi.
- Operasi uang, stok, dan transaksi aman saat permintaan dikirim ulang.
- Test mencakup alur normal, akses ditolak, data bisnis lain, duplikasi, serta pembalikan yang relevan.
- Seeder atau contoh data diperbarui bila struktur wajib berubah.
- Dokumentasi status di roadmap dan `PROJECT-MEMORY.md` diperbarui dalam perubahan yang sama.
- `bin/verify.sh` lulus sebelum commit dan PR.

## Setelah pilot

Urutan ditentukan data penggunaan, bukan asumsi:

- QRIS dinamis/payment gateway.
- Loyalty sederhana.
- Integrasi marketplace/delivery.
- Ekspor atau integrasi akunting.
- Produksi/kitting lebih lengkap.
- Kitchen display atau manajemen meja.
- Customer display.
- Toko online dan omnichannel.

## Gerbang perubahan scope

Fitur baru masuk bila memiliki:

- Pengguna dan masalah yang jelas.
- Frekuensi kejadian.
- Dampak bila tidak dibuat.
- Alur dan data yang dibutuhkan.
- Kriteria penerimaan.
- Keputusan apakah wajib sebelum pilot atau dapat ditunda.
