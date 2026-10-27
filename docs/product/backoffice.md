---
title: "Struktur Backoffice"
subtitle: "Navigasi, akses, laporan, dan arah visual"
author: "Binyo POS"
date: "2026-10-27"
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
- Void dan refund.
- Shift kasir.
- Pergerakan kas.

### Produk

- Produk dan varian/SKU.
- Kategori.
- Modifier.
- Harga dan pajak.
- Resep/komponen bila dipakai.
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

`Stok masuk`, `stok keluar`, dan `stock adjustment` memakai satu model pergerakan. UI memberi jenis tindakan berbeda tanpa membuat tiga konsep data yang tumpang tindih.

### Pembelian

- Pemasok.
- Purchase order.
- Penerimaan barang.
- Pembelian belum lengkap.

### Pelanggan

- Daftar pelanggan.
- Riwayat pembelian.
- Catatan internal terbatas.

Loyalty dan kupon ditunda.

### Laporan

- Penjualan.
- Produk dan kategori.
- Pembayaran.
- Kasir dan shift.
- Stok.
- Pembelian.
- Laba kotor perkiraan.

Semua laporan memakai pola sama: rentang tanggal, outlet, filter terkait, ringkasan, tabel, lalu ekspor CSV.

### Tim & Outlet

- Outlet.
- Pengguna/staf.
- Peran dan izin.
- Perangkat POS.

### Pengaturan

- Profil bisnis.
- Pajak dan service charge.
- Metode pembayaran.
- Nomor struk dan template struk.
- Alasan void, refund, koreksi stok, dan kas keluar.
- Integrasi.
- Impor/ekspor data.

## 2. Penyederhanaan dari Vireo

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
| Proses komisi/deposito/poin | Tidak ada | Ditunda |
| Produksi/bongkar produksi | Resep/produksi | Ditunda kecuali pilot membutuhkannya |
| Akun buku besar/jurnal | Ekspor akunting | General ledger bukan MVP |
| Grup akses | Tim & Outlet > Peran | Nama lebih mudah dipahami |
| Waralaba | Tidak ada | Ditunda |
| Grab/GoBiz | Pengaturan > Integrasi | Ditunda |

## 3. Matriks akses awal

| Kemampuan | Pemilik | Manajer | Kasir | Gudang |
|---|---:|---:|---:|---:|
| Melihat semua outlet | Ya | Sesuai akses | Tidak | Sesuai akses |
| Mengubah produk dan harga | Ya | Opsional | Tidak | Tidak |
| Menjual | Ya | Ya | Ya | Tidak |
| Memberi diskon manual | Ya | Ya | Sesuai batas | Tidak |
| Void/refund | Ya | Ya | Dengan persetujuan | Tidak |
| Menutup shift orang lain | Ya | Ya | Tidak | Tidak |
| Menerima pembelian | Ya | Ya | Tidak | Ya |
| Koreksi stok | Ya | Ya | Tidak | Sesuai izin |
| Melihat laba | Ya | Opsional | Tidak | Tidak |
| Mengelola pengguna | Ya | Opsional | Tidak | Tidak |

Hak akses disimpan per kemampuan, bukan hanya nama peran. Nama peran di atas adalah preset.

## 4. Arah visual

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
| Pembelian      | Tabel/isi utama                                     |
| Pelanggan      |                                                      |
| Laporan        |                                                      |
| Tim & Outlet   |                                                      |
| Pengaturan     |                                                      |
+----------------+------------------------------------------------------+
```

Elemen khas Binyo adalah `operational rail`: bar tipis yang selalu menunjukkan outlet aktif, shift terbuka, perangkat, koneksi, dan status sinkronisasi. Elemen ini informatif, bukan dekorasi.

## 5. Keadaan wajib setiap halaman

- Memuat.
- Kosong dengan tindakan berikutnya.
- Berhasil.
- Gagal dengan cara memperbaiki.
- Tidak memiliki izin.
- Data tidak mutakhir.
- Sebagian data belum tersinkron.
