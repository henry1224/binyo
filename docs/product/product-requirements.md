---
title: "Product Requirements"
subtitle: "Visi, pengguna, ruang lingkup, dan ukuran keberhasilan"
author: "Binyo POS"
date: "2026-10-28"
status: "Draft"
---

# Product Requirements

## 1. Visi

Binyo membantu pemilik usaha mengetahui apa yang terjual, uang yang diterima, dan stok yang berubah tanpa menu berlapis atau istilah teknis yang tidak perlu.

Pilot pertama berfokus pada kedai kopi dengan satu outlet, satu pemilik, satu manajer, satu atau lebih kasir, dan satu atau lebih staf gudang. Arsitektur tetap mendukung dua atau tiga outlet dengan pengguna, perangkat, harga, stok, dan laporan yang dibatasi per outlet.

Prinsip produk:

- Cepat saat dipakai kasir.
- Jelas saat dipantau pemilik.
- Aman saat koneksi buruk.
- Mudah dipelajari tanpa pelatihan panjang.
- Cukup fleksibel untuk retail dan F&B ringan, tanpa menjadi ERP penuh.

## 2. Pengguna

### Pemilik

Membutuhkan ringkasan penjualan, laba kotor perkiraan, stok, kas, performa outlet, dan kontrol akses.

### Manajer outlet

Mengelola produk tersedia, harga yang diizinkan, staf, stok, pembelian, koreksi, dan penutupan shift.

### Kasir

Membuka shift, membuat pesanan, menerima pembayaran, mencetak atau membagikan struk, serta melihat transaksi sendiri.

### Staf gudang

Menerima barang, menghitung stok, melakukan transfer, dan mencatat penyesuaian dengan alasan.

## 3. Sasaran MVP

- Penyiapan outlet, perangkat, pengguna, dan hak akses.
- Katalog produk, kategori, varian/SKU, modifier, harga, dan ketersediaan outlet.
- Penjualan mobile dengan tunai dan metode non-tunai yang dicatat manual.
- Split bill per item atau sebagian jumlah item dengan metode pembayaran berbeda.
- Shift kasir, pergerakan kas, void, refund, dan audit tindakan sensitif.
- Stok masuk, keluar, penjualan, opname, penyesuaian, dan peringatan stok rendah.
- Pemasok, purchase order sederhana, dan penerimaan barang.
- Dashboard dan laporan operasional utama.
- Operasi offline sementara dengan sinkronisasi aman.
- Replica operasional sanitasi per outlet untuk kebutuhan demo, analytics terbatas, atau integrasi non-authoritative.

## 4. Bukan MVP

- General ledger dan laporan akunting lengkap.
- Payroll dan penjadwalan staf.
- Waralaba dan settlement antarbadan usaha.
- Komisi penjualan kompleks.
- Loyalty bertingkat, kupon kompleks, dan penukaran poin.
- Broadcast WhatsApp dan marketing automation.
- Integrasi langsung GrabFood/GoFood.
- Toko online, self-order kiosk, dan customer display.
- Tab pelanggan dan pembayaran tempo.
- Manajemen meja, reservasi, kursus hidangan, dan kitchen display.

Fitur ditambahkan setelah kebutuhan nyata muncul pada pilot.

## 5. Kebutuhan fungsional

### Organisasi dan akses

- Satu bisnis memiliki satu atau lebih outlet.
- Pengguna dapat memiliki akses berbeda per outlet.
- Peran merupakan kumpulan izin dinamis; menu, tab, dan tindakan POS hanya muncul bila pengguna memiliki izin pada outlet aktif.
- Perangkat POS didaftarkan dan dapat dicabut aksesnya.
- Tindakan sensitif memakai izin terpisah: diskon, ubah harga, void, refund, koreksi stok, dan buka laci.
- Void transaksi dan pembalikan pembayaran salah membutuhkan PIN pemilik atau manajer, alasan, serta audit penyetuju.

### Katalog

- Produk memiliki nama, kategori, gambar opsional, pajak, status, dan urutan tampil.
- Varian/SKU adalah unit yang dijual dan dapat memiliki barcode, harga, biaya, serta stok.
- Produk tanpa pilihan tetap memiliki satu varian default agar model transaksi konsisten.
- Modifier dapat wajib atau opsional dan dapat menambah harga.
- Produk dapat dibatasi untuk outlet tertentu.
- Produk resep dapat mengurangi beberapa bahan baku ketika terjual.
- SKU memiliki satu base unit dan dapat memiliki satuan beli dengan faktor konversi tersimpan.
- HPP saat transaksi selesai disimpan sebagai snapshot dan tidak berubah karena penerimaan berikutnya.

### Penjualan

- Kasir dapat mencari produk, memilih kategori, memindai barcode, dan memakai favorit.
- Keranjang mendukung jumlah, catatan, modifier, diskon berizin, pajak, dan pelanggan opsional.
- Harga, pajak, nama produk, dan biaya disalin ke baris transaksi sebagai snapshot.
- Nomor struk unik per outlet dan tetap dapat dibuat saat offline.
- Transaksi selesai tidak dapat diedit diam-diam; koreksi memakai void atau refund dengan alasan.
- Pembayaran sebagian mengubah transaksi menjadi `partially_paid`; transaksi selesai hanya setelah seluruh total teralokasi.
- Split bill memilih item atau sebagian jumlah item tanpa menggandakan baris dan tanpa membuat pembatalan transaksi.
- Kitchen ticket opsional mencetak item yang diarahkan ke bar atau dapur; kitchen display tetap fase berikutnya.

### Pembayaran

- MVP mencatat tunai dan metode non-tunai manual.
- Kembalian dihitung hanya untuk tunai.
- Pembayaran gagal atau belum terkonfirmasi tidak boleh menyelesaikan transaksi.
- Metode non-tunai manual dapat mewajibkan referensi dan direkonsiliasi dengan settlement penyedia.
- Satu bill dapat dibayar bertahap memakai metode berbeda; setiap pembayaran menyimpan alokasi item dan nominalnya.
- Kesalahan sebelum konfirmasi dapat diedit. Pembayaran yang sudah tercatat hanya dapat dibalik dan diganti melalui tindakan berizin; record asli tetap ada.
- Binyo tidak menyimpan data kartu.
- Integrasi QRIS dinamis memakai penyedia pembayaran pada fase terpisah.

### Shift dan kas

- Kasir membuka shift dengan saldo awal.
- Kas masuk/keluar non-penjualan wajib memiliki kategori, nominal, dan catatan.
- Penjualan tunai, refund tunai, dan pergerakan kas manual membentuk saldo kas sistem tanpa pencatatan ganda.
- Beberapa kasir boleh menjual pada satu shift, tetapi satu pengguna tetap menjadi penanggung jawab kas sampai serah terima.
- Penutupan shift membandingkan kas sistem dan kas fisik.
- Penutupan shift ditolak selama terdapat bill `open` atau `partially_paid` pada perangkat tersebut.
- Bill yang belum lunas harus diselesaikan atau di-void dengan persetujuan sebelum penutupan diminta.
- Selisih kas disimpan, bukan ditimpa.
- Serah terima menyimpan setoran dan kas yang diteruskan; kas diteruskan menjadi saldo awal shift berikutnya dan bukan pendapatan.
- Satu perangkat hanya memiliki satu shift aktif dan tidak dapat berbagi cash drawer aktif dengan perangkat lain.
- Penutupan offline baru final di server setelah seluruh transaksi perangkat sampai urutan penutupan diterima.
- Target operasi offline adalah satu shift atau delapan jam; melewati target menampilkan peringatan kritis dan mewajibkan sinkronisasi sebelum shift berikutnya.

### Persediaan

- Semua perubahan stok menghasilkan pergerakan dengan sumber dan pelaku.
- Stok tidak boleh diubah langsung tanpa histori.
- Koreksi stok membutuhkan alasan.
- Setiap outlet memiliki pengaturan larangan stok minus yang aktif secara default.
- Jika larangan dinonaktifkan, penjualan tetap berjalan, HPP memakai biaya terakhir, dan saldo minus masuk daftar rekonsiliasi.
- Stok opname membandingkan jumlah sistem dan fisik sebelum menghasilkan penyesuaian.

### Pembelian

- Purchase order memiliki status draft, dipesan, diterima sebagian, selesai, atau dibatalkan.
- Izin posting menentukan siapa yang boleh mengubah draft menjadi pesanan; approval berantai bukan MVP.
- Penerimaan dapat dilakukan sebagian.
- Satuan beli dikonversi ke base unit memakai faktor yang disimpan sebagai snapshot.
- Biaya pembelian per base unit dan rata-rata bergerak dihitung saat penerimaan diposting.
- Pembatalan penerimaan tidak menghapus histori; sistem membuat pembalik yang terlacak.

### Pelanggan

- Pelanggan bersifat opsional pada transaksi.
- Data minimum: nama dan salah satu kontak bila memang dibutuhkan.
- Pengumpulan data pelanggan harus memiliki tujuan jelas dan dapat dibatasi.
- Riwayat pembelian hanya dapat dilihat oleh peran berizin.

### Laporan

- Ringkasan penjualan, transaksi, rata-rata transaksi, pajak, diskon, void, dan refund.
- Penjualan per produk, kategori, outlet, kasir, jam, dan metode pembayaran.
- Penjualan rinci per transaksi serta rekap jumlah item terjual per produk dan varian.
- Perkiraan laba kotor berdasarkan snapshot biaya.
- Posisi stok, kartu stok, stok rendah, dan hasil opname.
- Pembelian per pemasok dan penerimaan belum selesai.
- Rekonsiliasi shift dan selisih kas.
- Rekonsiliasi pembayaran non-tunai dan selisih settlement.

### Replica operasional

- Setiap outlet memiliki switch replica yang default-nya `OFF` dan hanya dapat diubah pemilik.
- Saat `OFF`, transaksi hanya disimpan pada database utama.
- Saat `ON`, transaksi tetap disimpan pada database utama lalu dikirim secara asynchronous ke database replica melalui outbox.
- Database replica mempertahankan nilai penjualan, diskon, pajak, service charge, pembayaran, void, dan refund sesuai sumber.
- Database replica tidak menerima identitas pelanggan, HPP, laba, kredensial, PIN, token, atau catatan internal.
- Switch `OFF` tidak menghapus data lama dan tidak membatalkan event yang sudah antre.
- Transaksi yang pernah masuk replica tetap menerima event void, refund, dan reversal walaupun switch kemudian `OFF`.
- Transaksi baru ketika switch `OFF` tidak masuk replica; backfill historis bukan MVP.
- Data replica disimpan 30 hari lalu dihapus otomatis tanpa memengaruhi database utama.
- Laporan pajak, laporan resmi, dan rekonsiliasi keuangan hanya membaca database utama.
- Semua halaman dan ekspor replica memakai label `Replica — Non-authoritative`.

## 6. Aturan pengalaman pengguna

Arah visual, komponen, layout, dan wireframe kanonis berada pada [`interface-design.md`](interface-design.md).

- Satu tugas utama per halaman.
- Backoffice memiliki maksimal tujuh menu utama.
- Nama menu memakai istilah pengguna, bukan nama tabel atau proses internal.
- Filter outlet dan tanggal selalu terlihat pada laporan.
- Status offline, sinkronisasi, shift, dan outlet selalu terlihat pada POS.
- Profit center transaksi selalu berasal dari outlet yang terikat ke perangkat, bukan pilihan kasir.
- Pengguna hanya dapat masuk ke POS bila memiliki role aktif pada outlet perangkat.
- Pemindahan perangkat antar-outlet membutuhkan pencabutan dan registrasi ulang.
- Error menjelaskan penyebab dan tindakan berikutnya.
- Tombol berisiko menyebut tindakan dengan jelas, misalnya `Batalkan transaksi`, bukan `OK`.
- Semua alur utama dapat dipakai dengan sentuhan dan ukuran target minimum 44 x 44 px.

## 7. Ukuran keberhasilan pilot

- Kasir baru dapat menyelesaikan penjualan dasar dalam kurang dari 2 menit tanpa bantuan.
- Transaksi reguler dapat diselesaikan dalam maksimal 5 interaksi setelah produk dipilih.
- Tidak ada transaksi ganda akibat retry sinkronisasi.
- Selisih total penjualan POS dan backoffice adalah nol setelah sinkronisasi selesai.
- Setiap perubahan stok dapat dilacak ke sumbernya.
- Penutupan shift dapat direkonsiliasi tanpa spreadsheet tambahan.
- Kegagalan replica tidak menggagalkan penjualan dan retry tidak membuat record duplikat.
- Nilai transaksi pada replica sama dengan sumber untuk seluruh field yang dicerminkan.
