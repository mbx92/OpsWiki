# OpsWiki

Technical knowledge base SaaS for DevOps & IT teams — wiki, SOP, troubleshooting, snippets, and tools.

- **Repository:** https://github.com/mbx92/OpsWiki
- **Deploy:** see [docker/DEPLOY-COOLIFY.md](docker/DEPLOY-COOLIFY.md)

## Quick start (local)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve
```

## Production (Coolify)

Copy environment from `.env.coolify.example`, then deploy with `docker-compose.yml` or `Dockerfile`.
