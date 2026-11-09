# Documentation Conventions

## Tujuan

Dokumentasi harus membantu owner menyetujui kebutuhan, developer membangun perilaku yang sama, tester memverifikasi hasil, dan operator memahami risiko tanpa mencari konteks di percakapan lama.

## Struktur

| Jenis | Lokasi | Isi |
|---|---|---|
| Product | `docs/product/` | pengguna, menu, alur, aturan, keadaan UI |
| Architecture | `docs/architecture/` | data, API, sinkronisasi, keamanan, operasi |
| Delivery | `docs/delivery/` | milestone, acceptance gate, rollout |
| References | `docs/references/` | observasi dan benchmark eksternal |
| Conventions | `docs/conventions/` | aturan kerja repository |

## Metadata dokumen domain

```yaml
---
title: "Judul"
subtitle: "Domain atau tujuan"
author: "Binyo POS"
date: "YYYY-MM-DD"
status: "Draft | Approved | Implemented | Deferred"
---
```

Tanggal memakai tanggal perubahan substantif terakhir, bukan waktu build otomatis.

## Isi minimum

- Ringkasan dan tujuan.
- Pengguna/peran serta izin.
- Alur normal dan keadaan gagal.
- Status atau lifecycle bila ada.
- Data yang dibaca dan ditulis.
- Audit, keamanan, dan risiko bila relevan.
- Kriteria penerimaan yang dapat diuji.
- Tautan ke dokumen terkait.

## Diagram dan gambar

- Gunakan Mermaid untuk alur atau relasi yang lebih jelas secara visual.
- Screenshot disimpan di `docs/images/<domain>/` setelah UI tersedia.
- Nama file: `<fitur>-<halaman|flow|state>-<urutan>.png`.
- Setiap gambar memiliki alt text yang menjelaskan isi, bukan `screenshot`.
- Jangan memasukkan data pelanggan atau transaksi produksi.

## Perubahan

- Perilaku berubah: perbarui dokumen product/domain pada commit yang sama.
- Model data/API berubah: perbarui dokumen architecture pada commit yang sama.
- Prosedur operasi berubah: buat/perbarui runbook sebelum rilis.
- Keputusan tertunda tetap ditulis sebagai keputusan terbuka, bukan diasumsikan developer.

## Verifikasi

```bash
bin/check-docs.mjs
```

Pemeriksaan mencakup file kosong, final newline, trailing whitespace, dan tautan lokal rusak.
