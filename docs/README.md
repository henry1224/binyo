---
title: "Dokumentasi Binyo POS"
subtitle: "Indeks produk, arsitektur, delivery, dan aturan kerja"
author: "Binyo POS"
date: "2026-10-27"
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
- [Model data](./architecture/data-model.md) — entitas, relasi, ledger, snapshot, dan constraint.
- [Fondasi teknis](./architecture/technical-foundation.md) — batas modul, API, sinkronisasi, keamanan, dan operasi.
- [Roadmap](./delivery/roadmap.md) — milestone dan gerbang penerimaan.
- [Benchmark POS](./references/pos-benchmark.md) — Vireo, Shopify, Square, Odoo, dan referensi lokal.

## Status dokumen

| Dokumen | Status | Owner | Tujuan |
|---|---|---|---|
| Product requirements | Draft | Owner produk | Masalah, pengguna, ruang lingkup, dan aturan produk |
| Struktur backoffice | Draft | Owner produk | Navigasi, halaman, laporan, dan hak akses |
| POS mobile | Draft | Owner produk | Alur kasir, shift, pembayaran, dan offline |
| Model data | Draft | Engineering | Entitas, relasi, sumber kebenaran, dan histori |
| Fondasi teknis | Draft | Engineering | Batas sistem, API, keamanan, sinkronisasi, dan operasi |
| Roadmap | Draft | Produk + Engineering | Urutan pembangunan dan kriteria selesai |
| Referensi | Draft | Produk | Temuan Vireo dan produk pembanding |

## Keputusan awal

- Satu produk melayani backoffice web dan POS mobile.
- Multi-outlet didukung oleh model data, tetapi pilot dapat dimulai dari satu outlet.
- Penjualan harus tetap dapat dicatat saat internet terputus sementara.
- Penjualan, pembayaran, dan stok memakai histori; data operasional penting tidak dihapus permanen.
- Akunting penuh, waralaba, komisi, kampanye WhatsApp, dan marketplace integration bukan MVP.

## Pertanyaan yang harus dijawab sebelum pengembangan

- [ ] Segmen awal: retail, F&B, atau hybrid?
- [ ] Pilot dimulai dari berapa outlet dan berapa perangkat kasir?
- [ ] Perangkat target: Android phone, Android tablet, atau keduanya?
- [ ] Printer yang dipakai: Bluetooth, USB, atau LAN? Sebutkan modelnya.
- [ ] Barcode scanner, cash drawer, customer display, dan kitchen printer diperlukan?
- [ ] Pembayaran awal: tunai, kartu, transfer, QRIS statis, atau QRIS dinamis?
- [ ] Pajak yang dipakai: PB1, PPN, service charge, atau kombinasi?
- [ ] Apakah stok bahan baku/resep wajib pada rilis pertama?
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
