# Testing Conventions

## Prinsip

- Test membuktikan perilaku, bukan detail implementasi.
- Perubahan non-trivial meninggalkan test yang dapat dijalankan.
- Mulai dari test paling dekat dengan perubahan, lalu perluas sesuai risiko.
- Uang, stok, pajak, permission, refund, offline sync, dan concurrency selalu membutuhkan test.
- Test database hanya boleh memakai database dengan token `test` yang jelas pada namanya.
- Test yang berbagi database dijalankan serial kecuali isolasi worker sudah terbukti.

## Quality gate saat ini

Gate aktif:

```bash
bin/verify.sh
```

Gate memeriksa:

- Format dan tautan seluruh Markdown.
- Trailing whitespace dan final newline.
- Diff Git yang tidak valid.
- Formatter, static analysis, dan feature test Laravel.
- Lint, typecheck, dan build Vue/TypeScript.

CI juga menjalankan migration fresh pada PostgreSQL `binyo_test`. Suite feature cepat tetap memakai SQLite in-memory; constraint dan perilaku khusus database harus memiliki pemeriksaan PostgreSQL.

## Matriks verifikasi aplikasi

| Perubahan | Pemeriksaan minimum |
|---|---|
| Dokumentasi | `bin/check-docs.mjs` |
| PHP/Laravel | formatter, test target, lalu suite Feature/Unit sesuai risiko |
| Vue/TypeScript | lint, typecheck, unit/component test, build |
| Flutter | `dart format`, `flutter analyze`, `flutter test` |
| Database/migration | migration pada PostgreSQL test + rollback/forward check |
| Sales/payment | perhitungan, idempotency, duplicate retry, refund, audit |
| Shift/cash | saldo kas, permission, blind count, reversal, handover, offline close |
| Access/device | role per outlet, revoked user/device, PIN lockout, binding profit center |
| Inventory | ledger, saldo, reversal, race/concurrency, rekonsiliasi |
| Offline sync | retry, urutan terbalik, restart, cursor, conflict, duplicate payload |
| UI penting | browser test dan screenshot desktop/mobile |
| Hardware | perangkat Android, printer, scanner, cash drawer nyata |

## CI

Workflow [`.github/workflows/test.yml`](../../.github/workflows/test.yml) berjalan pada PR ke `main` dan push ke `main`.

CI mencerminkan `bin/verify.sh` dan menambah pemeriksaan migrasi PostgreSQL; pemeriksaan wajib tidak boleh hanya hidup di laptop developer.

## Pelaporan hasil

Final handoff menyebut:

- Command yang dijalankan.
- Status lulus/gagal.
- Test yang tidak dapat dijalankan dan penyebabnya.
- Risiko residual.

Jangan menempel seluruh log kecuali diminta; ringkas error dan tindakan perbaikannya.
