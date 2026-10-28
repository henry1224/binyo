---
title: "Dokumentasi Binyo POS"
subtitle: "Indeks produk, arsitektur, delivery, dan aturan kerja"
author: "Binyo POS"
date: "2026-10-28"
status: "Draft"
---

# Ringkasan

Binyo adalah sistem POS untuk operasional retail dan F&B ringan. Dokumentasi memulai dari inti operasional; fitur enterprise yang belum diperlukan tidak dibawa ke MVP.

## Peta utama

- [PROJECT MEMORY](./PROJECT-MEMORY.md) — ringkasan keputusan, alur, modul, dan peta dokumen.
- [Conventions](./conventions/README.md) — aturan dokumentasi, Git workflow, dan test.
- [Product requirements](./product/product-requirements.md) — masalah, pengguna, scope, dan keberhasilan.
- [Backoffice](./product/backoffice.md) — menu, akses, laporan, serta arah visual.
- [POS mobile](./product/pos-mobile.md) — kasir, pembayaran, shift, offline, dan perangkat.
- [Sistem antarmuka](./product/interface-design.md) — visual language, komponen, layout responsif, dan wireframe.
- [Model data](./architecture/data-model.md) — entitas, relasi, ledger, snapshot, dan constraint.
- [Fondasi teknis](./architecture/technical-foundation.md) — batas modul, API, sinkronisasi, keamanan, dan operasi.
- [Roadmap](./delivery/roadmap.md) — milestone dan gerbang penerimaan.
- [Benchmark POS](./references/pos-benchmark.md) — Vireo, Loyverse, Square, Shopify, Odoo, Toast, dan standar pendukung.
- [Audit menu Vireo](./references/vireo-menu-audit.md) — pemetaan setiap menu, HPP, dan keputusan Binyo.
- [Matriks fitur Vireo](./references/vireo-feature-matrix.md) — inventaris 1:1 route, field, aksi, permission, dan laporan.
- [Validasi HPP Vireo](./references/vireo-hpp-validation.md) — bukti, rumus target, skenario sandbox, dan acceptance gate.

## Status dokumen

| Dokumen | Status | Owner | Tujuan |
|---|---|---|---|
| Product requirements | Draft | Owner produk | Masalah, pengguna, ruang lingkup, dan aturan produk |
| Struktur backoffice | Draft | Owner produk | Navigasi, halaman, laporan, dan hak akses |
| POS mobile | Draft | Owner produk | Alur kasir, shift, pembayaran, dan offline |
| Sistem antarmuka | Draft | Product Design + Engineering | Token, layout, komponen, responsif, dan aksesibilitas |
| Model data | Draft | Engineering | Entitas, relasi, sumber kebenaran, dan histori |
| Fondasi teknis | Draft | Engineering | Batas sistem, API, keamanan, sinkronisasi, dan operasi |
| Roadmap | Draft | Produk + Engineering | Urutan pembangunan dan kriteria selesai |
| Referensi | Draft | Produk | Temuan Vireo dan produk pembanding |
| Audit menu Vireo | Draft | Produk | Cakupan menu, HPP, penyederhanaan, dan batas observasi |
| Matriks fitur Vireo | Draft | Produk + Engineering | Ketertelusuran fitur Vireo ke keputusan Binyo |
| Validasi HPP Vireo | Draft | Produk + Engineering | Aturan biaya, bukti Vireo, dan skenario validasi |

## Keputusan awal

- Satu produk melayani backoffice web dan POS mobile.
- Multi-outlet didukung oleh model data, tetapi pilot dapat dimulai dari satu outlet.
- Pilot pertama adalah kedai kopi dengan satu outlet, satu perangkat utama, dan satu perangkat cadangan bila tersedia.
- Peran bersifat dinamis per outlet; izin yang sama mengontrol menu backoffice, tab POS, dan tindakan sensitif.
- Split bill per item termasuk MVP tanpa membuat transaksi pembatalan baru.
- Replica operasional sanitasi memakai switch per outlet; database utama tetap sumber laporan resmi.
- Penjualan harus tetap dapat dicatat saat internet terputus sementara.
- Penjualan, pembayaran, dan stok memakai histori; data operasional penting tidak dihapus permanen.
- Akunting penuh, waralaba, komisi, kampanye WhatsApp, dan marketplace integration bukan MVP.

## Pertanyaan yang harus dijawab sebelum pengembangan

- [x] Segmen awal: kedai kopi/F&B cafe.
- [x] Pilot: satu outlet, satu perangkat utama, dan satu perangkat cadangan bila tersedia; sistem mendukung dua atau tiga outlet.
- [x] Tampilan POS responsif untuk Android phone, tablet, dan terminal POS; model perangkat pilot belum dipilih.
- [ ] Pilih model printer pilot; aplikasi menyiapkan profil Bluetooth, USB, dan LAN serta template 58/80 mm.
- [ ] Tentukan kebutuhan scanner, cash drawer, dan kitchen printer pada outlet pilot; customer display dan kitchen display ditunda.
- [x] Pembayaran awal: tunai, kartu manual, transfer manual, dan QRIS manual; QRIS dinamis ditunda.
- [ ] Pajak yang dipakai: PB1, PPN, service charge, atau kombinasi?
- [x] Stok bahan baku, resep ringan, dan HPP rata-rata bergerak wajib pada rilis pertama.
- [ ] Apakah data lama perlu diimpor dari Vireo atau spreadsheet?
- [ ] Integrasi akunting apa yang dibutuhkan: ekspor CSV, Accurate, Jurnal, atau lainnya?

## Definisi status

- `Draft`: masih dapat berubah berdasarkan keputusan bisnis.
- `Approved`: disetujui sebagai dasar pengembangan.
- `Implemented`: sudah tersedia dan lolos kriteria penerimaan.
- `Deferred`: sengaja ditunda ke fase berikutnya.

## Quality gate

```bash
bin/verify.sh
```

Perubahan dokumen baru dianggap selesai setelah struktur, tautan lokal, trailing whitespace, final newline, dan diff Git lolos pemeriksaan.
