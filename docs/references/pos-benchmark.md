---
title: "Referensi dan Analisis POS"
subtitle: "VireoPOS, Shopify POS, Square Retail, Odoo POS, dan konteks lokal"
author: "Binyo POS"
date: "2026-10-27"
status: "Draft"
---

# Referensi dan Analisis

Tanggal observasi: 2026-10-27

## 1. VireoPOS

Referensi: <https://eretail.vireopos.com/#/vireodashboard.crux>

Observasi dilakukan pada sesi yang disediakan pengguna. Dokumen hanya mencatat struktur produk; identitas akun, outlet, dan nilai bisnis tidak disalin.

### Struktur menu yang diamati

- Beranda.
- Data Master: kategori, grup produk, merek, atribut, channel, modifier, fasilitas, produk, gerai, promo, pelanggan, pemasok, pembayaran, harga, loyalty, kupon, komisi, dan konfigurasi lain.
- Transaksi: PO, penerimaan, stok masuk/keluar/transfer, opname, adjustment, komisi, poin, produksi, dan pembatalan penjualan.
- Laporan: stok, pembelian, penjualan, laba, dan lainnya.
- Akunting: buku besar, jurnal, dan laporan.
- Akun: perangkat, staf, perusahaan, akses, pengaturan, dan waralaba.
- Integrasi: delivery platform.

### Dashboard yang diamati

- Penjualan kotor dan setelah pajak/platform.
- Jumlah transaksi dan rata-rata transaksi.
- Penjualan online.
- Tren tujuh hari.
- Produk terlaris.
- Peringatan stok minimum.
- Penjualan per kategori.

### Struktur produk yang diamati

- Produk induk, nama, kategori, grup, merek, kode, satuan, deskripsi, dan gambar.
- Ketersediaan gerai.
- Pengelolaan stok, larangan stok minus, nomor seri, dan stok minimum.
- Pengelolaan penjualan, urutan tampil, harga dinamis, pajak, favorit, dan harga jual.
- Komponen/resep: produk komponen, jumlah, harga, dan persentase.
- Modifier.

### Yang layak dipertahankan

- Pemisahan outlet dan perangkat.
- Produk resep/komponen untuk F&B.
- Kontrol stok minus dan stok minimum.
- PO sampai penerimaan.
- Transfer, opname, adjustment, dan histori stok.
- Laporan lintas penjualan, stok, pembelian, dan laba.
- Hak akses untuk tindakan sensitif.

### Yang disederhanakan

- Master data kecil tidak menjadi menu sendiri.
- Alur stok memakai satu ledger.
- PO dan penerimaan berada dalam satu modul pembelian.
- Perangkat, staf, dan akses berada dalam satu area tim/outlet.
- Akunting penuh diganti ekspor sampai ada kebutuhan nyata.
- Loyalty, komisi, waralaba, broadcast, dan integrasi delivery ditunda.

## 2. Shopify POS

Referensi resmi: <https://help.shopify.com/en/manual/sell-in-person>

Pelajaran utama:

- Katalog, order, dan inventory tersinkron antar-location dan sales channel.
- POS hanya membawa fungsi penjualan yang relevan; administrasi lebih luas tetap di backoffice.
- Model product + variant cocok untuk barang sederhana maupun produk dengan pilihan.

Gunakan sebagai acuan model katalog, lokasi, sinkronisasi, dan pemisahan POS/backoffice. Jangan menyalin kompleksitas omnichannel sebelum dibutuhkan.

## 3. Square for Retail

Referensi resmi: <https://squareup.com/us/en/point-of-sale/retail>

Pelajaran utama:

- Inventory real-time lintas lokasi dan channel.
- Histori sale, restock, dan adjustment terlihat dalam satu alur.
- PO, penerimaan, barcode, stocktake, refund, dan exchange ditempatkan dekat tugas pengguna.
- Laporan menghubungkan penjualan, COGS, sell-through, dan slow-moving stock.

Gunakan sebagai acuan checkout, inventory history, stocktake, refund, dan laporan yang menghasilkan tindakan.

## 4. Odoo POS

Referensi resmi: <https://www.odoo.com/documentation/19.0/applications/sales/point_of_sale.html>

Pelajaran utama:

- Web-based POS dapat tetap bekerja saat gangguan jaringan sementara.
- Dokumentasi memisahkan workflow, produk, hardware/network, retail, restoran, pembayaran, dan reporting.
- Fitur retail dan restoran tidak harus dipaksakan ke semua pengguna.

Gunakan sebagai acuan dokumentasi alur, offline resilience, hardware checklist, dan pemisahan capability berdasarkan tipe usaha. Jangan membawa seluruh ERP ke Binyo.

## 5. Referensi lokal yang perlu divalidasi

- Moka POS: ekspektasi merchant Indonesia, outlet, laporan, dan pembayaran lokal.
- Olsera: retail/F&B, inventory, dan omnichannel lokal.
- Pawoon: alur kasir dan operasional UMKM.
- Dokumentasi resmi penyedia QRIS/payment gateway yang dipilih.
- Ketentuan DJP/Pemda yang relevan untuk pajak, struk, dan penyimpanan transaksi.

Referensi lokal dipakai untuk memahami kebiasaan dan regulasi, bukan untuk menyalin antarmuka.

## 6. Rekomendasi gabungan

Tidak ada satu produk yang harus disalin penuh:

- Vireo memberi cakupan operasional Indonesia.
- Shopify memberi model katalog dan location yang rapi.
- Square memberi alur checkout/inventory yang lebih mudah dipahami.
- Odoo memberi referensi offline, hardware, dan pemisahan retail/restoran.
- Produk lokal memberi konteks pembayaran dan ekspektasi merchant Indonesia.

Binyo memakai bagian paling kecil yang menyelesaikan kebutuhan pilot.
