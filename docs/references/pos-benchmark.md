---
title: "Referensi dan Analisis POS"
subtitle: "VireoPOS, Loyverse, Square, Shopify, Odoo, Toast, dan standar pendukung"
author: "Binyo POS"
date: "2026-10-27"
status: "Draft"
---

# Referensi dan Analisis

Tanggal observasi: 2026-10-27

## 1. VireoPOS

Referensi: <https://eretail.vireopos.com/#/vireodashboard.crux>

Observasi dilakukan pada sesi yang disediakan pengguna. Dokumen hanya mencatat struktur menu dan produk; identitas akun, outlet, dan nilai bisnis tidak disalin.

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
- Perangkat, staf, akses, dan audit berada dalam satu area operasional.
- Akunting penuh diganti ekspor sampai ada kebutuhan nyata.
- Loyalty, komisi, waralaba, broadcast, dan integrasi delivery ditunda.

Audit setiap menu dan HPP: [`vireo-menu-audit.md`](vireo-menu-audit.md).

## 2. Loyverse

Referensi:

- <https://loyverse.com/>
- <https://help.loyverse.com/help/advanced-inventory-management>
- <https://help.loyverse.com/help/inventory-history-and-valuation>
- <https://help.loyverse.com/help/inventory-valuation-report>
- <https://help.loyverse.com/help/how-work-additional-costs-purchase>

Pelajaran utama:

- POS kasir tetap sederhana sementara inventory lanjutan tinggal di backoffice.
- Histori stok memakai satu daftar perubahan dengan tautan ke dokumen sumber.
- Purchase order, transfer, adjustment, count, valuation, dan production dikelompokkan sebagai pekerjaan persediaan.
- Average cost diperbarui saat barang diterima, bukan saat purchase order dibuat.
- Biaya tambahan pembelian dapat dialokasikan proporsional terhadap nilai barang yang diterima.
- Laporan valuasi memisahkan nilai persediaan, nilai retail, potensi laba, dan margin.

Gunakan Loyverse sebagai acuan utama kesederhanaan navigasi dan weighted average cost. Jangan menyalin pemisahan fitur berbayar atau seluruh modul advanced inventory.

## 3. Square for Retail

Referensi resmi: <https://squareup.com/us/en/point-of-sale/retail>

Pelajaran utama:

- Inventory real-time lintas lokasi dan channel.
- Histori sale, restock, dan adjustment terlihat dalam satu alur.
- PO, penerimaan, barcode, stocktake, refund, dan exchange ditempatkan dekat tugas pengguna.
- Laporan menghubungkan penjualan, COGS, sell-through, dan slow-moving stock.

Gunakan sebagai acuan checkout, inventory history, stocktake, refund, dan laporan yang menghasilkan tindakan.

## 4. Shopify POS

Referensi resmi: <https://help.shopify.com/en/manual/sell-in-person>

Pelajaran utama:

- Katalog, order, dan inventory tersinkron antar-location dan sales channel.
- POS hanya membawa fungsi penjualan yang relevan; administrasi lebih luas tetap di backoffice.
- Model product + variant cocok untuk barang sederhana maupun produk dengan pilihan.

Gunakan sebagai acuan model katalog, lokasi, sinkronisasi, dan pemisahan POS/backoffice. Jangan menyalin kompleksitas omnichannel sebelum dibutuhkan.

## 5. Odoo POS

Referensi resmi: <https://www.odoo.com/documentation/19.0/applications/sales/point_of_sale.html>

Pelajaran utama:

- Web-based POS dapat tetap bekerja saat gangguan jaringan sementara.
- Dokumentasi memisahkan workflow, produk, hardware/network, retail, restoran, pembayaran, dan reporting.
- Fitur retail dan restoran tidak harus dipaksakan ke semua pengguna.

Gunakan sebagai acuan dokumentasi alur, offline resilience, hardware checklist, dan pemisahan capability berdasarkan tipe usaha. Jangan membawa seluruh ERP ke Binyo.

## 6. Toast

Referensi resmi: <https://pos.toasttab.com/products/point-of-sale>

Pelajaran utama:

- Menu, modifier, recipe costing, kitchen routing, dan ordering mode penting untuk F&B.
- Fungsi dapur tidak perlu muncul pada bisnis retail.

Gunakan hanya bila pilot Binyo fokus F&B dan membutuhkan alur dapur. Jangan menambahkan meja, kursus hidangan, atau KDS pada MVP tanpa kebutuhan nyata.

## 7. Standar pendukung

### GS1 Barcode

Referensi: <https://www.gs1.org/standards/barcodes>

Gunakan untuk GTIN, EAN/UPC, lot, serial, berat, tanggal, dan barcode 2D. Jangan membuat format barcode bisnis sebagai pengganti standar bila produk sudah memakai kode GS1.

### OWASP MASVS

Referensi: <https://mas.owasp.org/MASVS/>

Gunakan untuk secure storage, crypto, autentikasi, jaringan, platform, dan privasi aplikasi POS mobile.

## 8. Referensi lokal yang perlu divalidasi

- Moka POS: ekspektasi merchant Indonesia, outlet, laporan, dan pembayaran lokal.
- Olsera: retail/F&B, inventory, dan omnichannel lokal.
- Pawoon: alur kasir dan operasional UMKM.
- Dokumentasi resmi penyedia QRIS/payment gateway yang dipilih.
- Ketentuan DJP/Pemda yang relevan untuk pajak, struk, dan penyimpanan transaksi.

Referensi lokal dipakai untuk memahami kebiasaan dan regulasi, bukan untuk menyalin antarmuka.

## 9. Rekomendasi gabungan

Tidak ada satu produk yang harus disalin penuh:

- Vireo memberi cakupan operasional Indonesia.
- Loyverse memberi struktur paling sederhana untuk POS dan inventory.
- Shopify memberi model katalog dan location yang rapi.
- Square memberi alur checkout/inventory yang lebih mudah dipahami.
- Odoo memberi referensi offline, hardware, dan pemisahan retail/restoran.
- Toast memberi pola khusus F&B bila benar-benar diperlukan.
- Produk lokal memberi konteks pembayaran dan ekspektasi merchant Indonesia.

Binyo memakai bagian paling kecil yang menyelesaikan kebutuhan pilot.

Prioritas acuan:

1. Loyverse untuk struktur sederhana.
2. Square untuk checkout, refund, dan inventory history.
3. Shopify untuk produk, varian, dan outlet.
4. Vireo untuk kelengkapan proses lokal dan validasi migrasi.
5. Odoo atau Toast hanya untuk kebutuhan teknis/segmen khusus.
