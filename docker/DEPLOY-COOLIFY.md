# Deploy OpsWiki di Coolify

**OpsWiki** — technical knowledge base SaaS untuk tim DevOps & IT.

## Setup di Coolify

1. Buat **PostgreSQL** di Coolify (resource terpisah) — catat hostname internal, user, password, database
2. Buat **Docker Compose** → connect repo [github.com/mbx92/OpsWiki](https://github.com/mbx92/OpsWiki)
3. Copy env dari `.env.coolify.example` ke **Environment Variables**
4. Set `DB_HOST` ke hostname Postgres dari Coolify (bukan `localhost`)
5. Link / attach database ke compose stack jika Coolify menawarkan opsi tersebut (supaya satu network)
6. Deploy — hanya service **app** yang di-build; tidak ada Postgres di `docker-compose.yml`

`docker-compose.yml` **tidak** mendefinisikan network atau service Postgres baru.

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

**Cannot connect to database:** `DB_HOST` harus hostname **internal** Docker Coolify, bukan IP publik.

**Migration gagal:** pastikan database sudah dibuat dan user punya hak CREATE/ALTER.

**APP_KEY missing:** container exit — set di Coolify env.
