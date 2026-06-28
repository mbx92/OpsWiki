# Deploy OpsWiki di Coolify

**OpsWiki** — technical knowledge base SaaS untuk tim DevOps & IT.

## Setup di Coolify

1. Buat **PostgreSQL** di Coolify (resource terpisah)
2. Buka Postgres → **Internal URL** → salin **hostname container** (mis. `m2a81yq3ad7hvelheztfqywy`)
3. Buat **Docker Compose** → connect repo [github.com/mbx92/OpsWiki](https://github.com/mbx92/OpsWiki)
4. **Application → Advanced → aktifkan `Connect to Predefined Network`** (wajib)
5. Copy env dari `.env.coolify.example` → set `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` **sama persis** dengan Postgres Coolify
6. Deploy

`docker-compose.yml` sudah join network `coolify` (external). Tanpa langkah 4, `DB_HOST` benar pun tetap **connection refused**.

**Port:** internal container **80**, publik `APP_PORT=8009` → `8009:80`.

## Env database (contoh)

```env
DB_CONNECTION=pgsql
DB_HOST=m2a81yq3ad7hvelheztfqywy
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<dari Coolify Postgres>
DB_SSLMODE=disable
```

Jangan pakai `localhost`. Jangan paste full URL ke `DB_HOST` — hanya hostname.

## Alur deploy (data tetap aman)

```text
Build image (Dockerfile)
    ↓
Container app start
    ↓
entrypoint.sh
    ├─ tunggu DB siap
    ├─ php artisan migrate --force     ← hanya migration baru
    ├─ opswiki:bootstrap               ← seed sekali (jika plans belum ada)
    ├─ storage:link + cache
    ↓
Nginx + PHP-FPM + queue worker (port 80)
```

**Redeploy:** migration tambahan saja — data Postgres di Coolify tetap aman.

**Tidak pernah dijalankan:** `migrate:fresh`, `migrate:refresh`, `db:wipe`

## Env wajib

| Variable | Keterangan |
|----------|------------|
| `APP_KEY` | `php artisan key:generate --show` |
| `APP_URL` | URL publik, mis. `https://opswiki.example.com` |
| `DB_HOST` | Hostname internal Postgres Coolify |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | Kredensial dari resource DB Coolify |
| `SAAS_CENTRAL_DOMAIN` | Domain utama platform |
| `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` | Login platform admin |

Bootstrap seeder jalan **otomatis sekali** saat deploy pertama (tabel `plans` masih kosong). Redeploy hanya menjalankan migration.

## Super admin

Login: `https://domain-anda/login` → **Platform Admin**

## DNS

| Record | Value |
|--------|-------|
| `opswiki.example.com` | A → server Coolify |
| `*.opswiki.example.com` | wildcard ke server yang sama |

## Troubleshooting

**Cannot connect to database:**
1. Aktifkan **Connect to Predefined Network** di Advanced
2. `DB_HOST` = hostname internal (dari Internal URL Postgres), bukan `localhost` / IP publik
3. User, password, database name harus match resource Postgres Coolify
4. Cek dari server: `docker network inspect coolify` — container app & postgres harus ada di network yang sama

**Migration gagal:** user Postgres perlu hak CREATE/ALTER pada database.

**APP_KEY missing:** container exit — set di Coolify env.
