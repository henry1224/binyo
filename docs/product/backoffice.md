---
title: "Struktur Backoffice"
subtitle: "Navigasi, akses, laporan, dan arah visual"
author: "Binyo POS"
date: "2026-10-28"
status: "Draft"
---

# Struktur Backoffice

## 1. Navigasi utama

### Beranda

- Penjualan bersih hari ini.
- Jumlah transaksi dan rata-rata transaksi.
- Perbandingan dengan periode sebelumnya.
- Produk terlaris.
- Stok rendah dan stok minus.
- Shift belum ditutup dan sinkronisasi perangkat bermasalah.

Dashboard tidak menampilkan grafik yang tidak menghasilkan tindakan.

### Penjualan

- Daftar transaksi.
- Detail transaksi dan pembayaran.
- Shift dan pergerakan kas.
- Void dan refund.
- Pelanggan.
- Rekonsiliasi pembayaran non-tunai.

### Produk

- Produk dan varian/SKU.
- Kategori.
- Modifier.
- Harga per outlet.
- Resep dan HPP bila dipakai.
- Promo sederhana.
- Impor dan ekspor katalog.

Merek dan atribut tidak menjadi menu terpisah pada MVP. Keduanya ditambahkan hanya jika pencarian dan pelaporan benar-benar membutuhkannya.

### Persediaan

- Ringkasan stok per outlet.
- Kartu/pergerakan stok.
- Stok masuk atau keluar manual.
- Transfer antar-outlet.
- Stok opname.
- Penyesuaian dan alasan.
- Peringatan stok rendah.
- Pemasok.
- Pembelian dan penerimaan barang.

`Stok masuk`, `stok keluar`, dan `stock adjustment` memakai satu model pergerakan. Purchase order dan penerimaan memakai satu dokumen dengan status bertahap. Transfer order, surat jalan, dan penerimaan transfer juga tampil sebagai satu alur.

### Laporan

- Penjualan.
- Penjualan rinci per transaksi dan item.
- Total item terjual per produk dan varian.
- Produk dan kategori.
- Pembayaran.
- Kasir dan shift.
- Stok.
- Pembelian.
- HPP dan laba kotor perkiraan.
- Pajak dan service charge.

Semua laporan memakai pola sama: rentang tanggal, outlet, filter terkait, ringkasan, tabel, lalu ekspor CSV.

### Operasional

- Outlet.
- Staff dan akses.
- Perangkat POS.
- Log aktivitas.

### Pengaturan

- Profil bisnis.
- Pajak dan service charge.
- Metode pembayaran.
- Nomor struk dan template struk.
- Replica data per outlet.
- Alasan void, refund, koreksi stok, dan kas keluar.
- Integrasi.
- Impor/ekspor data.

Loyalty, kupon, komisi, waralaba, dan marketing automation ditunda.

## 2. Struktur halaman

### Pola umum

- Header: judul, outlet aktif, dan satu aksi utama.
- Filter: pencarian, status, outlet, dan periode yang relevan.
- Ringkasan: hanya metrik yang dapat ditindaklanjuti.
- Isi: tabel atau form utama.
- Panel detail: histori, audit, atau informasi lanjutan tanpa pindah konteks.

### Laporan

Binyo tidak membuat menu terpisah untuk setiap dimensi laporan. Satu laporan penjualan dapat dikelompokkan berdasarkan produk, kategori, outlet, kasir, metode pembayaran, perangkat, hari, atau channel.

`Laporan > Replica operasional` dipisahkan dari laporan utama dan selalu menampilkan banner `Replica — Non-authoritative`. Halaman ini hanya menampilkan field whitelist, status sinkronisasi, periode retensi, dan ekspor replica; laporan resmi serta pajak tidak memiliki pilihan sumber data.

### Persediaan

- `Stok`: posisi dan histori per item.
- `Pembelian`: draft, dipesan, diterima sebagian, dan selesai.
- `Transfer`: draft, dikirim, diterima sebagian, dan selesai.
- `Opname & Penyesuaian`: perhitungan fisik, selisih, alasan, dan posting.
- `Pemasok`: identitas dan histori pembelian.

### Penjualan

- `Transaksi`: daftar dan detail transaksi.
- `Shift & Kas`: buka/tutup shift, kas masuk/keluar, dan selisih.
- `Refund & Void`: koreksi dengan alasan dan persetujuan.
- `Pelanggan`: data minimum dan riwayat berizin.
- `Rekonsiliasi Pembayaran`: cocokkan nominal POS dengan settlement penyedia.

### Replica data

- Switch berada pada `Pengaturan > Replica data` dan berlaku per outlet.
- Default `OFF`; hanya pemilik dengan permission `replica.manage` yang dapat mengubahnya.
- Halaman menampilkan status, waktu pengiriman terakhir, jumlah event antre/gagal, dan retensi tetap 30 hari.
- Perubahan switch meminta konfirmasi yang menyebut outlet dan dampaknya, lalu masuk audit log.
- `OFF` menghentikan transaksi baru, bukan menghapus data replica atau membatalkan event yang sudah antre.
- Tidak tersedia editor field atau rumus nominal; whitelist ditetapkan sistem agar konfigurasi tetap sederhana.

## 3. Penyederhanaan dari Vireo

| Vireo | Binyo MVP | Keputusan |
|---|---|---|
| Kategori, grup produk, merek, atribut | Produk | Digabung sebagai pengaturan katalog |
| Channel dan pengaturan channel | Pengaturan > Integrasi | Ditunda sampai ada integrasi nyata |
| Grup modifier | Produk > Modifier | Dipertahankan karena penting untuk F&B |
| Grup fasilitas | Tidak ada | Bukan kebutuhan inti POS |
| Grup harga | Harga per outlet | Model lebih langsung |
| Tipe item dan status | Field sistem | Tidak menjadi menu pengguna |
| PO dan penerimaan | Pembelian | Satu alur dari pesan sampai terima |
| Stok masuk/keluar/adjustment | Persediaan > Pergerakan | Satu ledger, beberapa jenis transaksi |
| Transfer order/surat jalan/penerimaan | Transfer stok | Satu alur bertahap; detail dokumen muncul bila dibutuhkan |
| Laporan per kategori/produk/outlet/staff/channel | Laporan dengan pilihan pengelompokan | Satu halaman, banyak sudut pandang |
| Proses komisi/deposito/poin | Tidak ada | Ditunda |
| Produksi/bongkar produksi | Resep/produksi | Ditunda kecuali pilot membutuhkannya |
| Akun buku besar/jurnal | Ekspor akunting | General ledger bukan MVP |
| Grup akses | Operasional > Staff & Akses | Nama lebih mudah dipahami |
| Waralaba | Tidak ada | Ditunda |
| Grab/GoBiz | Pengaturan > Integrasi | Ditunda |

Audit lengkap: [`../references/vireo-menu-audit.md`](../references/vireo-menu-audit.md).

## 4. Matriks akses awal

| Kemampuan | Pemilik | Manajer | Kasir | Gudang |
|---|---:|---:|---:|---:|
| Melihat semua outlet | Ya | Sesuai akses | Tidak | Sesuai akses |
| Mengubah produk dan harga | Ya | Opsional | Tidak | Tidak |
| Menjual | Ya | Ya | Ya | Tidak |
| Memberi diskon manual | Ya | Ya | Sesuai batas | Tidak |
| Void/refund | Ya | Ya | Dengan persetujuan | Tidak |
| Balikkan pembayaran salah | Ya | Ya | Dengan persetujuan | Tidak |
| Menutup shift orang lain | Ya | Ya | Tidak | Tidak |
| Menerima pembelian | Ya | Ya | Tidak | Ya |
| Koreksi stok | Ya | Ya | Tidak | Sesuai izin |
| Melihat laba | Ya | Opsional | Tidak | Tidak |
| Mengelola pengguna | Ya | Opsional | Tidak | Tidak |
| Melihat log aktivitas | Ya | Sesuai akses | Tidak | Opsional |
| Kelola switch replica | Ya | Tidak | Tidak | Tidak |
| Melihat laporan replica | Ya | Opsional | Tidak | Tidak |

Hak akses disimpan per kemampuan, bukan hanya nama peran. Nama peran di atas adalah preset.

## 5. Arah visual

Tema: `operational calm` - data padat, kontras jelas, gangguan rendah.

### Token awal

| Peran | Nilai |
|---|---|
| Latar utama | `#EEF3F4` |
| Permukaan | `#FFFFFF` |
| Teks utama | `#172320` |
| Aksen utama | `#167052` |
| Peringatan | `#C4811C` |
| Bahaya | `#C3463D` |
| Garis | `#D5DEDC` |

Tipografi:

- Judul dan navigasi: Sora.
- Teks UI: Source Sans 3.
- Nominal, nomor struk, dan data tabular: IBM Plex Mono.

### Struktur desktop

```text
+----------------+------------------------------------------------------+
| Binyo          | Outlet | Rentang tanggal              | Profil       |
|----------------|------------------------------------------------------|
| Beranda        | Judul halaman                         | Aksi utama   |
| Penjualan      |------------------------------------------------------|
| Produk         | Filter dan ringkasan                                |
| Persediaan     |------------------------------------------------------|
| Laporan        |                                                      |
| Operasional    |                                                      |
| Pengaturan     |                                                      |
+----------------+------------------------------------------------------+
```

Elemen khas Binyo adalah `operational rail`: bar tipis yang selalu menunjukkan outlet aktif, shift terbuka, perangkat, koneksi, dan status sinkronisasi. Elemen ini informatif, bukan dekorasi.

## 6. Aturan interaksi

- Maksimal tujuh menu utama.
- Satu tindakan utama memakai tombol paling menonjol.
- Aksi lanjutan berada pada menu konteks, bukan memenuhi toolbar.
- Status memakai bahasa pengguna, bukan kode internal.
- Field lanjutan muncul setelah fitur terkait diaktifkan.
- Tabel mengingat filter dan kolom pengguna.
- Aksi berisiko menjelaskan dampak sebelum konfirmasi.
- Target sentuh minimum 44 x 44 px tetap berlaku pada tablet.

## 7. Keadaan wajib setiap halaman

- Memuat.
- Kosong dengan tindakan berikutnya.
- Berhasil.
- Gagal dengan cara memperbaiki.
- Tidak memiliki izin.
- Data tidak mutakhir.
- Sebagian data belum tersinkron.
