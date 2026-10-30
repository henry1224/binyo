---
title: "Roadmap"
subtitle: "Milestone, acceptance gate, dan urutan pilot"
author: "Binyo POS"
date: "2026-10-28"
status: "Draft"
---

# Roadmap

Roadmap memakai milestone berbasis hasil, bukan tanggal. Estimasi dibuat setelah pertanyaan pada [`docs/README.md`](../README.md) dijawab dan prototipe hardware diuji.

## M0 - Persetujuan produk

- [ ] Tentukan segmen awal, outlet pilot, perangkat, printer, pembayaran, dan pajak.
- [ ] Review serta setujui product requirements.
- [ ] Review struktur menu, model data, dan batas MVP.
- [ ] Review sistem antarmuka, wireframe backoffice/POS, state wajib, responsif, dan aksesibilitas.
- [ ] Review kontrak replica: field whitelist, switch per outlet, retensi, permission, dan larangan sumber laporan resmi.
- [ ] Buat contoh data produk, transaksi, stok, dan struk.
- [ ] Pilih 5-10 alur penerimaan utama untuk demo.

Keputusan yang sudah diambil: kedai kopi, satu outlet pilot, dukungan perluasan dua atau tiga outlet, pembayaran manual, split bill per item, stok minus dikendalikan per outlet, dan HPP rata-rata bergerak per SKU/outlet. Model hardware serta pajak pilot masih terbuka.

Selesai bila tidak ada keputusan bisnis kritis yang diserahkan ke developer saat coding.

## M1 - Fondasi dan backoffice dasar

Status: sedang berjalan. Scaffold backoffice, autentikasi internal, bisnis, outlet, role per outlet, perangkat, seed demo, dan quality gate aplikasi sudah mulai diimplementasikan.

- [ ] Autentikasi, bisnis, outlet, pengguna, peran, dan perangkat.
- [ ] Produk, kategori, varian/SKU, modifier, pajak, dan harga outlet.
- [ ] Impor produk CSV minimum.
- [ ] Audit log dasar.
- [ ] Switch replica per outlet dan transactional outbox dalam kondisi default `OFF`.
- [ ] CI, environment, backup, error tracking, dan seed demo.

Selesai bila pemilik dapat menyiapkan satu outlet dan katalog siap jual tanpa mengubah database manual.

## M2 - POS online dan shift

- [ ] Login/perangkat dan bootstrap.
- [ ] Katalog, pencarian, barcode, favorit, keranjang, modifier, dan diskon.
- [ ] Pembayaran tunai serta non-tunai manual.
- [ ] Split bill per item, pembayaran parsial, dan koreksi pembayaran berizin.
- [ ] Struk thermal dan share receipt.
- [ ] Kitchen ticket berbasis tujuan produk bila dibutuhkan outlet pilot.
- [ ] Shift, kas masuk/keluar, dan penutupan.
- [ ] Daftar transaksi, void, dan refund berizin.

Selesai bila satu hari operasional dapat dijalankan online dari buka sampai tutup shift.

## M3 - Stok dan pembelian

- [ ] Ledger stok dan saldo per outlet.
- [ ] Pengurangan stok dari penjualan dan resep.
- [ ] Pemasok, PO, dan penerimaan sebagian.
- [ ] Konversi satuan beli ke base unit dan HPP rata-rata bergerak.
- [ ] Opname, adjustment, transfer, dan stok rendah.
- [ ] Rekonsiliasi ledger dan saldo.

Selesai bila setiap perubahan stok pilot dapat ditelusuri ke transaksi sumber.

## M4 - Offline dan hardening

- [ ] Database lokal dan durable command queue.
- [ ] Cursor sync, retry, idempotency, tombstone, dan status sinkronisasi.
- [ ] Skenario putus jaringan, restart, jam perangkat salah, dan payload duplikat.
- [ ] Worker replica, sanitasi field, retry idempotent, koreksi setelah switch `OFF`, dan purge 30 hari.
- [ ] Uji printer/scanner/cash drawer pada perangkat nyata.
- [ ] Performance, security, backup restore, dan audit akses.

Selesai bila penjualan offline tersinkron tanpa duplikasi atau kehilangan data.

## M5 - Laporan dan pilot

- [ ] Dashboard tindakan harian.
- [ ] Laporan penjualan rinci, total item terjual, pembayaran, produk, stok, pembelian, dan shift.
- [ ] Laba kotor perkiraan dengan penjelasan sumber biaya.
- [ ] Rekonsiliasi pembayaran non-tunai manual.
- [ ] Ekspor CSV.
- [ ] Laporan replica terpisah dengan label `Replica — Non-authoritative`.
- [ ] UAT, pelatihan singkat, SOP gangguan, dan checklist go-live.
- [ ] Pilot terbatas dan evaluasi masalah nyata.

Selesai bila pemilik dapat merekonsiliasi satu siklus operasional tanpa spreadsheet tambahan.

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
