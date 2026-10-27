# Git Workflow

Workflow mengikuti pola `pdam-backoffice`: branch pendek dari `main`, Conventional Commits, pemeriksaan lokal, PR ke `main`, dan CI wajib hijau.

## 1. Siapkan branch

Jika remote sudah tersedia:

```bash
git switch main
git pull --ff-only
git switch -c feat/nama-perubahan
```

Prefix branch:

| Prefix | Kegunaan |
|---|---|
| `feat/` | Kemampuan baru |
| `fix/` | Bug atau regresi |
| `docs/` | Dokumentasi tanpa perubahan perilaku |
| `refactor/` | Struktur internal tanpa perubahan perilaku |
| `test/` | Penambahan/perbaikan test |
| `chore/` | Tooling, dependency, atau housekeeping |

Worktree tambahan dibuat di `.worktree/`:

```bash
git worktree add .worktree/nama-branch -b feat/nama-branch main
```

## 2. Kerjakan perubahan

- Baca `AGENTS.md`, `PROJECT-MEMORY.md`, dan convention terkait.
- Catat file, caller, test, fixture, config, export, dan dokumen yang terdampak.
- Pertahankan perubahan pengguna yang tidak terkait.
- Gunakan pola yang sudah ada sebelum membuat abstraksi baru.
- Update dokumentasi dalam perubahan yang sama saat kontrak berubah.

## 3. Verifikasi

```bash
bin/verify.sh
git status --short
git diff --check
git diff
```

Jalankan test terdampak lebih dulu. Perluas ke suite penuh bila menyentuh shared behavior, uang, stok, autentikasi, permission, sinkronisasi, migrasi, atau concurrency.

## 4. Commit

Format:

```text
type(scope): ringkasan imperatif
```

Contoh:

```text
docs(product): susun fondasi kebutuhan POS
feat(sales): simpan penjualan secara idempotent
fix(inventory): cegah saldo ganda saat retry sync
test(payments): tambah cakupan pembayaran duplikat
```

Aturan:

- Satu commit untuk satu perubahan logis.
- Stage file relevan saja; jangan memakai `git add .` tanpa memeriksa status.
- Jangan campur formatting massal dengan perubahan perilaku.
- Jangan memakai `--no-verify` kecuali owner menyetujui alasan konkret.

## 5. Push dan pull request

```bash
git push -u origin feat/nama-perubahan
```

PR menargetkan `main` dan menjelaskan:

- Masalah dan hasil.
- Perubahan perilaku/data/API.
- Risiko dan rollback.
- Test yang dijalankan beserta status.
- Screenshot untuk perubahan UI.
- Dokumen yang diperbarui.

CI `Tests & Lint` wajib hijau. Temuan review diselesaikan pada branch yang sama. Jangan merge bila ada test wajib yang belum berjalan atau keputusan bisnis material yang belum jelas.

## 6. Setelah merge

- Sinkronkan `main` dengan `git pull --ff-only`.
- Hapus branch/worktree yang sudah selesai setelah memastikan tidak ada perubahan lokal.
- Verifikasi deploy menggunakan commit yang benar sebelum UAT.
- Catat masalah produksi sebagai issue/runbook, bukan hanya percakapan.

## Kondisi repository saat ini

Remote branch `origin/main` belum tersedia saat dokumentasi ini dibuat. Push dan PR baru dapat dijalankan setelah remote utama disiapkan. Workflow lokal, commit, hook, dan test tetap berlaku.
