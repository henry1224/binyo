# PROJECT KNOWLEDGE BASE

**Generated:** 2026-10-27
**Branch baseline:** `main`

## Working scope

Baca panduan terdekat dan referensi yang relevan dengan perubahan. Pertahankan perubahan pengguna yang tidak terkait. Untuk perubahan dokumentasi, periksa struktur, tautan, format, dan diff. Untuk perubahan kode, mulai dari test terdampak lalu perluas sesuai risiko.

Setiap perubahan wajib mengikuti [Git workflow](docs/conventions/git-workflow.md) dan [testing conventions](docs/conventions/testing.md).

## Overview

Binyo adalah sistem POS dengan dua permukaan:

- Backoffice web untuk katalog, persediaan, pembelian, laporan, outlet, dan pengguna.
- POS mobile untuk transaksi kasir, shift, pembayaran, struk, dan operasi offline sementara.

Status repository saat ini adalah dokumentasi produk. Stack pada [fondasi teknis](docs/architecture/technical-foundation.md) masih rekomendasi sampai disetujui.

## Structure

```text
docs/
├── architecture/    # model data dan batas teknis
├── conventions/     # aturan dokumentasi, Git, dan test
├── delivery/        # roadmap dan gerbang rilis
├── product/         # kebutuhan serta alur pengguna
├── references/      # benchmark dan hasil observasi
└── PROJECT-MEMORY.md

bin/                 # pemeriksaan lokal yang sama dengan CI
.githooks/           # hook tim
.github/workflows/   # CI
```

## Where to look

| Kebutuhan | Dokumen |
|---|---|
| Peta proyek dan keputusan aktif | [`docs/PROJECT-MEMORY.md`](docs/PROJECT-MEMORY.md) |
| Indeks aturan kerja | [`docs/conventions/README.md`](docs/conventions/README.md) |
| Branch, commit, PR, worktree | [`docs/conventions/git-workflow.md`](docs/conventions/git-workflow.md) |
| Test dan quality gate | [`docs/conventions/testing.md`](docs/conventions/testing.md) |
| Format dokumentasi | [`docs/conventions/documentation.md`](docs/conventions/documentation.md) |
| Scope produk | [`docs/product/product-requirements.md`](docs/product/product-requirements.md) |
| Backoffice | [`docs/product/backoffice.md`](docs/product/backoffice.md) |
| POS mobile | [`docs/product/pos-mobile.md`](docs/product/pos-mobile.md) |
| Model data | [`docs/architecture/data-model.md`](docs/architecture/data-model.md) |
| Fondasi teknis | [`docs/architecture/technical-foundation.md`](docs/architecture/technical-foundation.md) |
| Delivery | [`docs/delivery/roadmap.md`](docs/delivery/roadmap.md) |

## Git rules

- Branch dari `main`: `feat/`, `fix/`, `docs/`, `refactor/`, `test/`, atau `chore/`.
- Gunakan Conventional Commits: `type(scope): ringkasan`.
- Satu commit memuat satu perubahan logis.
- Jangan commit langsung ke `main` setelah bootstrap repository.
- Sebelum commit: jalankan `bin/verify.sh`, periksa `git diff`, lalu stage file yang relevan saja.
- PR menargetkan `main`; CI `Tests & Lint` wajib hijau sebelum merge.
- Worktree tambahan dibuat di `.worktree/`.

## Commands

```bash
bin/setup-hooks.sh       # aktifkan hook repository
bin/check-docs.mjs       # validasi seluruh Markdown
bin/verify.sh            # quality gate lokal
```

Saat aplikasi dibuat, `bin/verify.sh` dan CI wajib diperluas untuk formatter, static analysis, unit/feature test, build frontend, test Flutter, serta test integrasi PostgreSQL.

## Documentation rules

- Bahasa produk dan alur pengguna memakai Bahasa Indonesia.
- Identifier, nama tabel, endpoint, dan simbol kode tetap memakai nama aslinya.
- Tanggal memakai format absolut `YYYY-MM-DD`.
- Dokumen domain memakai metadata `title`, `subtitle`, `author`, `date`, dan `status`.
- Jelaskan sumber kebenaran, peran, alur, status, kegagalan, dan kriteria penerimaan.
- Jangan masukkan kredensial, data pelanggan, nilai transaksi nyata, atau identitas sesi referensi.
- Screenshot dan diagram harus memiliki nama stabil serta keterangan.

## Current constraints

- Belum ada aplikasi, dependency, database, atau environment runtime.
- Remote `origin/main` belum tersedia pada saat bootstrap dokumentasi.
- Hardware POS, payment provider, pajak, dan batas offline belum diputuskan.
