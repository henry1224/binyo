---
title: "Sistem Antarmuka Binyo"
subtitle: "Arah visual, komponen, layout responsif, dan wireframe backoffice serta POS"
author: "Binyo POS"
date: "2026-10-28"
status: "Draft"
---

# Sistem Antarmuka Binyo

## 1. Tesis desain

Binyo melayani kedai kopi yang bergerak cepat: kasir membutuhkan kepastian, manajer membutuhkan pengecualian yang harus ditangani, dan pemilik membutuhkan angka yang dapat dipercaya. Satu pekerjaan antarmuka adalah membuat tindakan berikutnya terlihat jelas tanpa menyembunyikan status operasional.

Nama arah visual: `Counterline`.

Prototype interaktif: [`../prototypes/counterline.html`](../prototypes/counterline.html). Gunakan parameter `?view=dashboard`, `?view=master`, `?view=shifts`, `?view=shift`, `?view=pos`, `?view=cash`, atau `?view=phone` untuk membuka layar tertentu.

Karakter:

- Simple, elegan, bersih, dan profesional.
- Padat seperlunya, bukan kosong demi terlihat premium.
- Terang dan tenang, dengan kontras kuat pada angka serta tindakan.
- Terasa seperti alat kerja kafe yang matang, bukan template dashboard generik.
- Konsisten antara backoffice web dan POS Android tanpa membuat keduanya identik.

Elemen khas Binyo adalah `operational rail`: pita gelap tipis yang selalu menunjukkan konteks nyata seperti outlet, shift, perangkat, koneksi, antrean sinkronisasi, dan masalah yang perlu tindakan. Rail mengambil inspirasi dari rel tiket pesanan di bar atau dapur; elemen lain dibuat tenang agar rail tetap menjadi satu-satunya aksen struktural kuat.

## 2. Batas visual

Binyo tidak memakai:

- Glassmorphism, glow, neon, atau gradient pada tombol.
- Latar ungu, aksen ungu, atau dark mode sebagai identitas utama.
- Kumpulan kartu besar untuk setiap angka dashboard.
- Radius sangat besar pada semua komponen.
- Ikon tanpa label untuk tindakan berisiko.
- Grafik dekoratif yang tidak menghasilkan tindakan.
- Animasi tersebar, confetti, atau efek yang memperlambat kasir.
- Copy pemasaran pada layar operasional.

Kartu hanya digunakan bila sebuah kelompok informasi memang memiliki batas dan tindakan sendiri. Data tabular memakai garis, alignment, dan ruang, bukan bayangan berlapis.

## 3. Token visual

### Warna inti

| Token | Nilai | Penggunaan |
|---|---|---|
| `worktop` | `#EEF3F1` | Latar aplikasi dan area kerja sekunder |
| `paper` | `#FFFFFF` | Form, tabel, panel, dan lembar transaksi |
| `ink` | `#17231F` | Teks utama dan operational rail |
| `ledger` | `#126B52` | Tindakan utama, online, berhasil, dan fokus |
| `crema` | `#B86E22` | Offline, menunggu, stok rendah, dan perhatian |
| `stop` | `#C0443C` | Gagal, void, refund, hapus, dan tindakan berbahaya |

Garis memakai `ink` dengan opacity rendah. Teks sekunder memakai `ink` dengan opacity sedang. Warna status selalu ditemani ikon atau label; warna tidak menjadi satu-satunya pembeda.

Latar shell memakai transisi tipis dari worktop yang sedikit lebih gelap pada bagian atas menuju worktop utama. Panel, tombol, dan tabel tetap memakai warna datar agar data tidak kehilangan kontras.

### Tipografi

| Peran | Font | Aturan |
|---|---|---|
| Judul dan angka utama | Sora | 600; dipakai hemat untuk hierarki dan total |
| Teks UI | Source Sans 3 | 400/600; label, form, tombol, dan isi tabel |
| Data presisi | IBM Plex Mono | 500; nominal, nomor struk, SKU, waktu, dan jumlah stok |

Skala utama:

| Nama | Ukuran/tinggi baris | Penggunaan |
|---|---|---|
| Display | `32px/38px` | Total POS dan angka utama dashboard |
| H1 | `24px/32px` | Judul halaman |
| H2 | `18px/26px` | Judul bagian dan panel |
| Body | `16px/24px` | Isi utama dan form POS |
| Compact | `14px/20px` | Tabel backoffice dan metadata |
| Caption | `12px/16px` | Waktu, helper, dan status tambahan |

Nominal memakai tabular numbers dan rata kanan pada tabel. Huruf kapital penuh hanya untuk kode singkat, bukan label menu atau tombol.

### Ruang dan bentuk

- Grid ruang: `4, 8, 12, 16, 24, 32, 48, 64`.
- Radius panel: `6px`; dialog dan drawer: `10px`.
- Bayangan hanya untuk dialog, drawer, menu mengambang, dan elemen yang benar-benar berada di atas lapisan lain.
- Tinggi kontrol backoffice minimum `40px`.
- Target sentuh POS minimum `48px`; tombol bayar minimum `56px`.
- Garis fokus `2px ledger` dengan offset yang terlihat.

## 4. Operational rail

### Backoffice

```text
+--------------------------------------------------------------------------------+
| BINYO | Kedai Utama | Hari usaha 28 Okt | 2 shift aktif | Semua tersinkron | HW |
+--------------------------------------------------------------------------------+
```

Urutan informasi:

1. Produk/brand.
2. Outlet aktif.
3. Hari usaha atau rentang laporan.
4. Status operasional yang dapat ditindaklanjuti.
5. Profil pengguna.

Jika ada masalah, rail mengganti bagian status dengan tindakan spesifik seperti `3 transaksi belum sinkron — Tinjau`. Rail tidak berisi promosi, breadcrumb, atau metrik dekoratif.

### POS

```text
+--------------------------------------------------------------------------+
| Kedai Utama | Shift #018 | Kasir: Rina | Online | Tersinkron 10:42       |
+--------------------------------------------------------------------------+
```

Saat offline, label berubah menjadi `Offline · 4 menunggu` dengan warna crema. Area jual tetap stabil; rail tidak membesar menjadi banner yang menutup produk.

## 5. Backoffice

### Shell desktop

- Navigasi kiri `232px`, dapat dipadatkan menjadi ikon + tooltip pada tablet.
- Operational rail `44px` di atas seluruh area.
- Konten memakai grid 12 kolom dan lebar penuh; tabel tidak dipaksa masuk kartu sempit.
- Judul, deskripsi singkat, dan satu aksi utama berada pada baris yang sama.
- Filter menempel di atas tabel saat pengguna menggulir daftar panjang.

```text
+----------------+---------------------------------------------------------------+
| BINYO / RAIL   | Kedai Utama | Hari ini | Semua tersinkron              | HW  |
+----------------+---------------------------------------------------------------+
| Beranda        | Selasa, 28 Oktober                         [Tinjau 3 masalah] |
| Penjualan      |---------------------------------------------------------------|
| Produk         | Penjualan bersih | Transaksi | Rata-rata | Laba kotor        |
| Persediaan     | Rp8.420.000      | 186       | Rp45.269  | Rp4.910.000       |
| Laporan        |---------------------------------------------------------------|
| Operasional    | PERLU TINDAKAN                    | ARUS HARI INI             |
| Pengaturan     | 2 shift belum ditutup             | grafik ringkas + label    |
|                | 4 transaksi menunggu sinkron      | langsung                  |
|                | 6 bahan di bawah stok minimum     |                            |
|                |---------------------------------------------------------------|
|                | TRANSAKSI TERBARU                                              |
|                | Waktu  Struk   Kasir   Pembayaran   Total   Status             |
+----------------+---------------------------------------------------------------+
```

Nominal dan nama pada wireframe adalah data sintetis untuk validasi desain.

Ringkasan memakai satu `ledger band` horizontal dengan pemisah garis, bukan empat kartu mengambang. Daftar `Perlu tindakan` lebih dominan daripada grafik.

### Daftar dan detail

Desktop memakai pola master-detail agar pengguna tidak kehilangan filter saat membuka transaksi.

```text
+----------------------+----------------------------------------------------------+
| Cari / Filter        | TRX-10284 · Selesai                     [Cetak ulang]    |
|----------------------|----------------------------------------------------------|
| 10:42  Rp84.000      | 2 Latte                     2 x Rp28.000                 |
| 10:39  Rp42.000      | 1 Croissant                 1 x Rp28.000                 |
| 10:31  Rp96.000      |----------------------------------------------------------|
| 10:22  Rp38.000      | Subtotal  Rp84.000   QRIS tercatat   Kasir Rina         |
|                      | Audit     Dibuat 10:42 · Tersinkron 10:42                |
+----------------------+----------------------------------------------------------+
```

Pada layar sempit, detail menjadi halaman penuh dengan tombol kembali yang mempertahankan filter dan posisi scroll.

### Laporan outlet/profit center

```text
+-------------------------------------------------------------------------------+
| Laba kotor                         1–28 Oktober        [Semua outlet v] [CSV] |
|-------------------------------------------------------------------------------|
| Outlet       Penjualan bersih     HPP          Laba kotor      Margin         |
| Kedai Utama  Rp82.400.000          Rp31.200.000 Rp51.200.000    62,1%          |
| Kedai Timur  Rp61.800.000          Rp25.700.000 Rp36.100.000    58,4%          |
|-------------------------------------------------------------------------------|
| Detail harian / produk / kategori berada di bawah, bukan tab tersembunyi.     |
+-------------------------------------------------------------------------------+
```

Filter outlet dan tanggal selalu terlihat. Label memakai `Laba kotor`, bukan `Laba bersih`, selama biaya operasional belum dicatat.

## 6. POS mobile

### Tablet landscape

- Area katalog sekitar 64%; keranjang 36%.
- Kategori berupa rail horizontal, bukan sidebar kedua.
- Produk memakai nama maksimal dua baris, harga jelas, dan status habis yang tidak hanya mengandalkan warna.
- Keranjang tetap terlihat sampai pembayaran selesai.

```text
+--------------------------------------------------------------------------------+
| Kedai Utama | Shift #018 | Rina | Online | Tersinkron 10:42                   |
+---------------------------------------------------+----------------------------|
| [Cari produk atau scan barcode]                   | PESANAN · 4 ITEM           |
| Kopi  Non-kopi  Makanan  Favorit                  |----------------------------|
|---------------------------------------------------| Latte          2  Rp56.000 |
| Espresso       Latte          Cappuccino          | + Oat milk       Rp10.000 |
| Rp22.000       Rp28.000       Rp30.000            | Croissant      1  Rp28.000 |
|                                                   |----------------------------|
| Americano      Es Kopi Susu   Matcha              | Subtotal          Rp94.000 |
| Rp24.000       Rp25.000       Rp32.000            | Pajak              Rp0     |
|                                                   | TOTAL             Rp94.000 |
|                                                   | [ BAYAR RP94.000 ]          |
+---------------------------------------------------+----------------------------+
```

Tombol produk memakai permukaan terang dan garis tipis. Foto bersifat opsional; tanpa foto, tipografi dan kategori tetap menghasilkan grid yang rapi.

### Phone portrait

```text
+--------------------------------------+
| Kedai Utama | #018 | Online          |
|--------------------------------------|
| [Cari atau scan]                     |
| Kopi  Non-kopi  Makanan              |
|--------------------------------------|
| Latte               Es Kopi Susu     |
| Rp28.000            Rp25.000         |
|                                      |
| Cappuccino          Croissant        |
| Rp30.000            Rp28.000         |
|--------------------------------------|
| 4 item · Rp94.000       [Lihat bill] |
+--------------------------------------+
```

Ringkasan bill menempel di bawah tetapi tidak menutup produk terakhir. Keranjang penuh dibuka sebagai bottom sheet; tombol bayar tetap berada di area aman ibu jari.

### Split bill

```text
+-------------------------------------------------------------------+
| Split bill                                   2 dari 4 item dipilih |
|-------------------------------------------------------------------|
| [x] Latte          1/2       Rp28.000                            |
| [ ] Latte          1/2       Rp28.000                            |
| [x] Oat milk       1/1       Rp10.000                            |
| [ ] Croissant      1/1       Rp28.000                            |
|-------------------------------------------------------------------|
| Bagian ini                                    Rp38.000             |
| Sisa bill                                     Rp56.000             |
|                                         [Pilih pembayaran]         |
+-------------------------------------------------------------------+
```

Item yang sudah dibayar berubah menjadi read-only dengan label metode serta waktu. Salah pembayaran menampilkan tindakan `Balikkan pembayaran` yang meminta PIN dan alasan; tidak ada tombol hapus diam-diam.

### Pembayaran selesai

Satu motion utama dipakai saat pembayaran berhasil: total bergeser menjadi status `Pembayaran tercatat`, lalu tindakan `Cetak struk`, `Bagikan`, dan `Pesanan baru` muncul. Durasi maksimum `220ms`; tidak ada confetti.

### Shift dan kas tablet

Menu `Kas` berada pada operational rail agar dapat dijangkau tanpa memenuhi area jual. Ringkasan menampilkan komponen kas, bukan nominal kas sistem saat proses hitung kas berlangsung.

```text
+--------------------------------------------------------------------------+
| Kedai Utama | Shift #018 | Rina | Online                         [Kas]   |
+--------------------------------------------------------------------------+
| Kas awal             Rp500.000 | Penjualan tunai      Rp2.000.000       |
| Kas masuk/keluar      +Rp50.000 | Refund tunai           -Rp20.000       |
|--------------------------------------------------------------------------|
| 12:20 Tambahan uang kecil   +Rp100.000  Rina · disetujui Budi           |
| 11:04 Belanja es batu        -Rp50.000  Rina · disetujui Budi           |
|--------------------------------------------------------------------------|
| [Kas masuk] [Kas keluar]                         [Tutup / serah terima]   |
+--------------------------------------------------------------------------+
```

Saat tutup shift, kasir memasukkan jumlah fisik lebih dahulu. Hasil perbandingan baru muncul setelah dikirim:

```text
Kas aktual            Rp2.530.000
Kas seharusnya        Rp2.530.000
Selisih                       Rp0
Disetor               Rp2.030.000
Diteruskan Shift 2      Rp500.000

[Tutup dan mulai Shift 2]
```

Kas masuk/keluar memakai satu dialog dengan jenis, kategori, nominal, catatan, dan PIN penyetuju. Serah terima tetap satu alur terpandu; aplikasi tidak membuat pengguna menutup lalu mencari menu buka shift secara terpisah.

## 7. Komponen dan interaksi

### Tombol

- Primary: isi ledger, teks putih, satu per konteks.
- Secondary: paper dengan garis ink transparan.
- Tertiary: teks, untuk tindakan ringan.
- Danger: stop; label menyebut tindakan seperti `Void transaksi`.
- Disabled tetap dapat dibaca dan menjelaskan syarat melalui helper text atau tooltip.

### Status

Status memakai ikon kecil, label langsung, dan warna semantik:

- `Selesai`, `Tersinkron`, `Aktif`.
- `Menunggu`, `Offline`, `Stok rendah`.
- `Gagal`, `Dibatalkan`, `Perlu koreksi`.

Hindari pill pada setiap sel. Badge hanya dipakai untuk status yang perlu dipindai cepat.

### Ikon dan gambar produk

- Gunakan satu keluarga ikon outline dengan ukuran dasar `20px` dan ketebalan garis konsisten.
- Ikon navigasi selalu ditemani label saat ruang memungkinkan.
- Emoji tidak dipakai sebagai ikon aplikasi atau status.
- Foto produk memakai rasio konsisten dan crop sederhana; foto tidak wajib agar katalog tetap rapi untuk produk tanpa gambar.

### Tabel

- Header tetap terlihat pada daftar panjang.
- Angka dan nominal rata kanan.
- Baris memiliki tinggi minimum `44px`.
- Hover menunjukkan baris dapat dibuka; selection memakai garis ledger di kiri, bukan warna blok penuh.
- Aksi massal muncul hanya setelah selection.
- Kolom prioritas tetap terlihat; kolom sekunder dapat disembunyikan pada layar kecil.

### Form

- Label selalu terlihat di atas field; placeholder bukan pengganti label.
- Helper text menjelaskan format sebelum error terjadi.
- Validasi muncul dekat field dan pada ringkasan bila form panjang.
- Form panjang dibagi berdasarkan tugas, bukan accordion yang menyembunyikan field wajib.
- Tombol simpan memakai label spesifik seperti `Simpan produk` atau `Posting penerimaan`.

### Dialog berisiko

Dialog menyebut objek, dampak, dan tindakan:

```text
Void transaksi TRX-10284?

Pembayaran dan stok akan dibalik. Riwayat transaksi tetap disimpan.

Alasan                 [Pesanan salah              ]
PIN penyetuju          [••••••]

[Kembali]                              [Void transaksi]
```

### Empty, loading, dan error

- Empty state menjelaskan apa yang belum ada dan menawarkan satu tindakan.
- Loading mempertahankan bentuk halaman agar layout tidak melompat.
- Error menyebut penyebab yang diketahui dan langkah berikutnya.
- Data lama menampilkan waktu pembaruan terakhir, bukan hanya ikon peringatan.
- Tidak memiliki izin menjelaskan permission yang dibutuhkan tanpa membocorkan data.

## 8. Motion

- Transisi halaman: fade/translate ringan `160ms` hanya pada area konten.
- Drawer dan bottom sheet: `200ms` dengan easing yang tegas, tanpa bounce.
- Perubahan status sync: crossfade label; tidak membuat toast berulang.
- Skeleton hanya untuk data yang benar-benar menunggu; cache lokal POS langsung ditampilkan.
- `prefers-reduced-motion` menghapus transform dan mempertahankan perubahan instan.

## 9. Bahasa antarmuka

- Gunakan Bahasa Indonesia dan kata kerja aktif.
- Gunakan istilah pengguna: `Penerimaan barang`, bukan nama tabel atau endpoint.
- Tombol dan toast memakai kata yang sama: `Simpan produk` menghasilkan `Produk disimpan`.
- Error tidak meminta pengguna "mencoba lagi" tanpa menjelaskan apa yang gagal.
- Nominal memakai format `Rp94.000`; persentase memakai koma desimal bila diperlukan.
- `Bill` hanya dipakai pada alur split bill yang sudah dikenal pengguna; bagian lain memakai `Transaksi` atau `Pesanan`.

## 10. Responsif dan aksesibilitas

- Backoffice diuji pada lebar `768`, `1024`, `1280`, dan `1440`.
- POS diuji pada phone `360x800`, tablet portrait, tablet landscape, dan terminal `10–16` inci.
- Keyboard dapat mencapai navigasi, filter, tabel, dialog, dan semua tindakan backoffice.
- Focus visible tidak boleh dihapus.
- Kontras teks memenuhi WCAG AA.
- Touch target POS minimum `48x48px`; jarak antar-tindakan berbahaya cukup untuk mencegah salah sentuh.
- Status tidak bergantung pada warna.
- Pembaca layar mendapat label lengkap untuk ikon, nominal, perubahan jumlah, dan status sinkronisasi.
- Zoom teks tidak memotong total, tombol bayar, atau konfirmasi berisiko.

## 11. Gerbang penerimaan desain

- [ ] Satu halaman memiliki maksimal satu primary action yang dominan.
- [ ] Operational rail konsisten pada backoffice dan POS.
- [ ] Dashboard mendahulukan masalah yang perlu tindakan, bukan grafik.
- [ ] Semua nominal tabel rata kanan dan memakai tabular numbers.
- [ ] Split bill dapat dipahami tanpa pelatihan tertulis.
- [ ] Offline, pending sync, tanpa izin, kosong, loading, gagal, dan data lama memiliki desain eksplisit.
- [ ] Tidak ada tindakan berisiko yang hanya memakai ikon atau label `OK`.
- [ ] Backoffice dapat dipakai dengan keyboard dan POS memenuhi target sentuh.
- [ ] Screenshot desktop, tablet, dan phone diperiksa sebelum UI dinyatakan selesai.
- [ ] Tidak ada komponen dekoratif yang tidak menyampaikan status, hierarki, atau tindakan.

Desain visual baru dianggap final setelah diuji dengan data sintetis dua outlet dan alur kasir nyata pada perangkat target.
