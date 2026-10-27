# Git Hooks

Hook repository menjaga pemeriksaan minimum tetap berjalan sebelum commit.

## Instalasi

```bash
bin/setup-hooks.sh
```

## pre-commit

Menjalankan `bin/verify.sh`. Commit dihentikan bila format dokumen, tautan lokal, atau diff tidak valid.

Jangan memakai `git commit --no-verify` kecuali owner menyetujui alasan konkret.
