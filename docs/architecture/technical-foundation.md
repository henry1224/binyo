---
title: "Fondasi Teknis"
subtitle: "Batas sistem, API, sinkronisasi, keamanan, dan operasi"
author: "Binyo POS"
date: "2026-10-28"
status: "Rekomendasi, belum dikunci"
---

# Fondasi Teknis

## 1. Bentuk sistem

```text
Backoffice web ----+
                   +---- API / modular monolith ---- PostgreSQL
POS mobile --------+             |
                                 +---- Queue worker
                                 +---- Object storage
                                 +---- Payment/integration adapters

POS mobile ---- Local database + durable sync queue
```

Modular monolith cukup untuk MVP. Microservice menambah deployment, transaksi terdistribusi, observability, dan kegagalan jaringan tanpa manfaat awal.

## 2. Stack rekomendasi

- Backend/API: Laravel.
- Backoffice: Inertia + Vue + TypeScript.
- POS mobile: Flutter, Android-first, responsif untuk phone, tablet, dan terminal POS.
- Database utama: PostgreSQL.
- Database lokal mobile: SQLite.
- Queue/cache: mulai dari database queue; Redis ditambahkan saat beban atau kebutuhan realtime membuktikannya.
- File: S3-compatible object storage untuk gambar produk dan ekspor.

Flutter 3.47 mendukung Android API 24-37. Binyo menargetkan perangkat baru Android 13+; perangkat Android 11-12 hanya diterima bila masih mendapat patch vendor. Keputusan Flutter tetap harus divalidasi memakai printer, scanner, dan cash drawer target karena dukungan hardware nyata lebih penting daripada preferensi framework.

## 3. Batas modul backend

- Identity: pengguna, peran, izin, sesi, perangkat.
- Organization: bisnis dan outlet.
- Catalog: kategori, produk, varian, modifier, harga, pajak.
- Sales: transaksi, pembayaran, refund, struk.
- Shifts: shift dan kas.
- Inventory: ledger, saldo, opname, transfer.
- Purchasing: pemasok, PO, penerimaan.
- Customers: profil pelanggan.
- Reporting: read model dan ekspor.
- Sync: bootstrap, delta, upload transaksi, retry, dan konflik.
- Audit: tindakan sensitif dan histori perubahan.

Modul tetap berada dalam satu aplikasi dan satu database. Batas modul mencegah query silang tanpa aturan, bukan membuat service terpisah.

## 4. API minimum

### Identitas dan perangkat

- `POST /auth/login`
- `POST /auth/refresh`
- `POST /auth/logout`
- `GET /me`
- `POST /devices/register`
- `POST /devices/{device}/revoke`

### Bootstrap dan sinkronisasi POS

- `GET /pos/bootstrap`
- `GET /pos/changes?cursor=...`
- `POST /pos/sales/batch`
- `POST /pos/shifts/batch`
- `POST /pos/sync/ack`

### Backoffice

- CRUD terbatas untuk outlet, pengguna, produk, kategori, modifier, pemasok, dan pelanggan.
- Endpoint workflow terpisah untuk `post`, `receive`, `send`, `close`, `void`, dan `refund`.
- Endpoint laporan bersifat read-only dan dapat menghasilkan ekspor async.

Nama endpoint final mengikuti konvensi proyek. Daftar ini menetapkan kemampuan, bukan kontrak final.

## 5. Strategi sinkronisasi

### Download

- Bootstrap memberi snapshot minimum untuk outlet dan pengguna aktif.
- Perubahan berikutnya memakai cursor monotonik dari server.
- Record yang dinonaktifkan ikut dikirim sebagai tombstone/status agar cache lokal ikut berubah.

### Upload

- Mobile menyimpan command pada durable queue sebelum menganggap aksi aman.
- Batch berisi device ID, sequence, idempotency key, waktu perangkat, dan payload version.
- Server memproses tiap command secara idempotent dan mengembalikan hasil per item.
- Item gagal validasi tidak menghalangi acknowledgement item lain yang valid.

### Konflik

- Transaksi penjualan bersifat append-only; retry mengembalikan hasil sebelumnya.
- Master data dikelola backoffice; cache mobile mengikuti versi server.
- Stok negatif atau harga lama tidak diselesaikan dengan menulis ulang transaksi lama.
- Perbedaan waktu perangkat ditandai, tetapi waktu server tetap dicatat.

## 6. Keamanan

- TLS wajib pada semua lingkungan selain local development.
- Password memakai password hasher framework; PIN tidak disimpan sebagai plaintext.
- Token mobile disimpan pada secure storage OS.
- Access token berumur pendek; refresh token dapat dicabut per perangkat.
- Semua query dibatasi `business_id` dan izin outlet.
- Otorisasi dicek server pada setiap tindakan, termasuk tindakan yang sudah disembunyikan UI.
- Rate limit untuk login, PIN approval, dan endpoint berisiko.
- Audit wajib untuk login, perubahan izin, perubahan harga, diskon, void, refund, koreksi stok, dan penutupan shift.
- Binyo tidak menyimpan PAN kartu, CVV, atau kredensial pembayaran pelanggan.
- Backup terenkripsi, diuji restore, dan memiliki retensi tertulis.

## 7. Reliabilitas dan operasi

- Health check untuk aplikasi, database, queue, storage, dan integrasi pembayaran.
- Structured logs dengan request ID, business ID, outlet ID, device ID, dan actor ID bila tersedia.
- Error tracking untuk web, API, worker, dan mobile.
- Metric minimum: error rate, latency, queue delay, sync backlog, payment failures, dan duplicate attempts.
- Backup harian dan point-in-time recovery sesuai kemampuan hosting.
- Restore drill dilakukan sebelum pilot menyimpan data produksi penting.

## 8. Target non-fungsional awal

| Area | Target pilot |
|---|---|
| Buka halaman jual dari cache | <= 2 detik pada perangkat target |
| Tambah item ke keranjang | Respons visual <= 100 ms |
| API baca normal p95 | <= 500 ms pada jaringan stabil |
| API submit penjualan p95 | <= 1 detik, tidak termasuk payment provider |
| Pemulihan setelah app restart | Tidak kehilangan command yang sudah diterima UI |
| Sinkronisasi | Retry otomatis dengan backoff dan status terlihat |
| Aksesibilitas web | Keyboard, focus visible, kontras WCAG AA |
| Ketersediaan pilot | Target ditetapkan setelah pilihan hosting |

## 9. Strategi test minimum

- Unit test: perhitungan total, pajak, diskon, biaya, dan kembalian.
- Unit/database test: alokasi split bill, pembayaran parsial, reversal pembayaran, dan larangan double allocation.
- Database test: constraint tenant, idempotency, nomor struk, dan posting ledger.
- Feature test: login, izin, penjualan, refund, shift, penerimaan, opname, dan laporan.
- Sync test: retry, urutan terbalik, duplikasi, putus jaringan, dan restart aplikasi.
- Hardware test: printer, scanner, cash drawer, dan perangkat Android target.
- End-to-end pilot: buka shift sampai tutup shift serta rekonsiliasi laporan.

## 10. Keputusan yang belum dikunci

- Hosting dan region data.
- Penyedia QRIS/payment gateway.
- Model perangkat Android serta printer 58/80 mm yang lulus proof-of-concept.
- Integrasi akunting dan bentuk ekspor.
