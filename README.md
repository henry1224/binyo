# Binyo POS

Backoffice web dan POS mobile untuk operasional retail dan F&B ringan.

## Status

Fase saat ini: **discovery dan persetujuan dokumen**. Implementasi aplikasi belum dimulai.

## Produk

| Produk | Pengguna utama | Tanggung jawab |
|---|---|---|
| Backoffice web | Pemilik, manajer, gudang | Katalog, stok, pembelian, laporan, outlet, dan akses |
| POS mobile | Kasir, manajer outlet | Penjualan, pembayaran, shift, struk, dan sinkronisasi |

## Dokumentasi

- [Indeks dokumentasi](./docs/README.md)
- [Peta pengetahuan proyek](./docs/PROJECT-MEMORY.md)
- [Product requirements](./docs/product/product-requirements.md)
- [Struktur backoffice](./docs/product/backoffice.md)
- [Alur POS mobile](./docs/product/pos-mobile.md)
- [Model data](./docs/architecture/data-model.md)
- [Fondasi teknis](./docs/architecture/technical-foundation.md)
- [Roadmap](./docs/delivery/roadmap.md)

## Verifikasi

```bash
bin/verify.sh
```

Aturan kontribusi, branch, commit, PR, dan test tersedia di [Git workflow](./docs/conventions/git-workflow.md).
