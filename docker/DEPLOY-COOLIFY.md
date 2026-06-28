# Deploy OpsWiki di Coolify

**OpsWiki** — technical knowledge base SaaS untuk tim DevOps & IT (wiki, SOP, troubleshooting, snippets, tools).

## Alur deploy (data tetap aman)

```text
Build image
    ↓
Container start
    ↓
entrypoint.sh
    ├─ php artisan migrate --force     ← hanya migration baru, TIDAK reset DB
    ├─ db:seed ProductionBootstrapSeeder ← idempotent (plans, legal, super admin)
    ├─ storage:link
    └─ config/route/view cache
    ↓
Nginx + PHP-FPM + queue worker
```

**Redeploy / update versi:** ulangi alur yang sama. Database volume (`opswiki_postgres`) dan `storage` tetap — data user, wiki, billing tidak hilang.

**Yang TIDAK pernah dijalankan otomatis:**
- `migrate:fresh`
- `migrate:refresh`
- `db:wipe`

## Opsi A — Docker Compose di Coolify

1. Buat project **Docker Compose** di Coolify
2. Connect repository ini
3. Set **Environment Variables** dari `.env.coolify.example`
4. Generate `APP_KEY`:
   ```bash
   php artisan key:generate --show
   ```
5. Ganti `APP_URL`, `SAAS_CENTRAL_DOMAIN`, `SUPER_ADMIN_*`, `DB_PASSWORD`
6. Deploy

Coolify akan build `Dockerfile` dan menjalankan `docker-compose.yml`.

## Opsi B — Dockerfile saja (database eksternal)

1. Buat **Application** → Build pack: Dockerfile
2. Tambahkan PostgreSQL sebagai database service Coolify
3. Set `DB_HOST` ke hostname database internal Coolify
4. Port container: **80**
5. Health check path: `/up`

## DNS

| Record | Value |
|--------|-------|
| `opswiki.example.com` | A → IP server Coolify |
| `*.opswiki.example.com` | A atau CNAME wildcard |

Set `SAAS_CENTRAL_DOMAIN=opswiki.example.com`.

## Super admin login

Setelah deploy pertama:

| Field | Env variable |
|-------|----------------|
| Email | `SUPER_ADMIN_EMAIL` |
| Password | `SUPER_ADMIN_PASSWORD` |

Login di `https://opswiki.example.com/login` → menu **Platform Admin**.

Password hanya di-set saat user pertama kali dibuat. Untuk reset via env, set `SUPER_ADMIN_SYNC_PASSWORD=true` sekali, deploy, lalu kembalikan ke `false`.

## Env penting

| Variable | Keterangan |
|----------|------------|
| `RUN_BOOTSTRAP_SEEDER` | `true` (default) — seed plans & super admin tanpa hapus data |
| `SUPER_ADMIN_EMAIL` | Email platform admin |
| `SUPER_ADMIN_PASSWORD` | Password awal super admin |
| `SAAS_CENTRAL_DOMAIN` | Domain utama untuk subdomain workspace |
| `SAAS_DEFAULT_CURRENCY` | `IDR` untuk pricing Rupiah |

## Troubleshooting

**Migration gagal:** pastikan `DB_*` benar dan Postgres sudah healthy.

**APP_KEY missing:** container exit — set `APP_KEY` di Coolify env.

**Asset 404:** pastikan build frontend sukses di stage Docker (cek build log).

**Queue tidak jalan:** sudah included di supervisord; pastikan `QUEUE_CONNECTION=database`.
