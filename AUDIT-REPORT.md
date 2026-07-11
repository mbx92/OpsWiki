# OpsWiki — Audit & Seeder Report
**Tanggal:** 11 Juli 2026  
**Auditor:** KING GUN 💩  
**Target:** `/home/mbx/projects/OpsWiki`

---

## 1. Tenant Isolation Audit

### Mekanisme Keamanan

| Layer | Komponen | Status |
|-------|----------|--------|
| Middleware | `IdentifyTenant` — resolve tenant dari host/session | ✅ |
| Middleware | `EnsureTenantAccess` (`tenant`) — cek user membership | ✅ |
| Global Scope | `BelongsToTenant` — auto-filter `WHERE tenant_id` | ✅ |
| Context | `TenantContext` — singleton tenant per request | ✅ |
| Super Admin | Bypass tenant scope via `is_super_admin` | ✅ (by design) |

### Model dengan Tenant Scoping (`BelongsToTenant`)

| Model | Global Scope | Status |
|-------|-------------|--------|
| Page | ✅ `BelongsToTenant` | Aman |
| Book | ✅ `BelongsToTenant` | Aman |
| Snippet | ✅ `BelongsToTenant` | Aman |
| Sop | ✅ `BelongsToTenant` | Aman |
| TroubleshootingCase | ✅ `BelongsToTenant` | Aman |
| Project | ✅ `BelongsToTenant` | Aman |
| InboxItem | ✅ `BelongsToTenant` | Aman |
| Asset | ✅ `BelongsToTenant` | Aman |
| Category | ✅ `BelongsToTenant` | Aman |
| Tag | ✅ `BelongsToTenant` | Aman |
| ActivityLog | ✅ `BelongsToTenant` | Aman |
| PageRelation | ✅ `BelongsToTenantRelation` | Aman |
| Setting | ⚠️ Manual via `rowQuery()` | Aman |

### Model TANPA Tenant Scoping (by design)

| Model | Alasan |
|-------|--------|
| User | Platform-level (cross-tenant via `tenant_user` pivot) |
| Role | Platform-level (shared across tenants) |
| Permission | Platform-level |
| Plan | Platform-level |
| Subscription | Platform-level billing |
| Invoice | Platform-level billing |
| Payment | Platform-level billing |
| Tenant | Platform-level identity |
| TenantDomain | Platform-level domain routing |
| LegalDocument | Platform-level policies |
| Tool | Platform-level tools (feature-gated by plan) |
| PageVersion | Scoped via `page_id` → Page (yang sudah tenant-scoped) |
| ArchiveRecord | ⚠️ Hanya dipakai internal `MinioArchiveService`, bukan user-facing |

### ⚠️ Temuan: ArchiveRecord
`ArchiveRecord` tidak punya `BelongsToTenant` scope. Data archive yang berisi file upload, import, dan export history bisa dilihat lintas tenant. **Dampak: rendah** karena endpoint `ArchiveRecord` tidak expose data mentah ke user. Tetap perlu dipantau.

### ⚠️ Temuan: withoutGlobalScope
`TenantUsageService` menggunakan `Page::withoutGlobalScope('tenant')` untuk menghitung usage billing. **Dampak: rendah** — hanya dipakai untuk kalkulasi billing super admin.

---

## 2. Cross-Tenant Visibility Test Results

| Test | Deskripsi | Hasil |
|------|-----------|-------|
| No tenant context | Query tanpa `TenantContext::set()` | ✅ 0 rows (`WHERE 0=1`) |
| Default tenant (id=1) | Hanya data tenant 1 | ✅ Terisolasi |
| Acme tenant (id=2) | Hanya data tenant 2 | ✅ Terisolasi |
| Cross-tenant page relation | Relasi page antar tenant | ✅ Diblokir (foreign key) |
| Raw DB query | `DB::table('pages')->get()` | ⚠️ Bypass scope (expected) |
| Super admin | Admin bisa akses semua tenant | ✅ By design |
| Search | Full-text & ILIKE | ✅ Menggunakan model scoped |
| Public share | Halaman publik | ✅ Hanya yang visibility=public |

---

## 3. Seeder Data

### OpswikiDemoSeeder

File: `database/seeders/OpswikiDemoSeeder.php`

**Data yang dibuat (di default tenant):**

| Entity | Count | Contoh |
|--------|-------|--------|
| Wiki Pages | 5 | Server Provisioning, DB Backup, Nginx Proxy, Docker, CI/CD |
| Categories | 5 | Infrastructure, Development, Security, Operations, Onboarding |
| Tags | 9 | linux, database, nginx, docker, cicd, security, etc. |
| SOPs | 3 | Incident Response, Server Patching, Employee Offboarding |
| Troubleshooting | 3 | PG Connection Pool, Nginx 502, Disk Full |
| Snippets | 5 | Find-Replace, PG Queries, Git Undo, Docker Prune, SSL Check |
| Projects | 3 | Infra Migration, Monitoring Upgrade, Zero-Trust Security |
| Inbox Items | 3 | Monitoring setup, SSL renewal, Redis SOP |

**Menjalankan:**
```bash
php artisan db:seed --class=OpswikiDemoSeeder --force
```

**Fitur seeder:**
- Idempotent — gunakan `firstOrCreate` di slug-based models
- Tenant-aware — set `TenantContext` sebelum seeding
- Realistic — data production-ready seperti dokumentasi infrastruktur

---

## 4. Infrastruktur Server

| Komponen | Detail |
|----------|--------|
| App | Laravel 13 + Inertia/Vue 3 |
| Web Server | Nginx → `http://localhost:80` |
| Database | PostgreSQL 17 @ `localhost:5432` |
| PHP-FPM | PHP 8.4 @ `/run/php/php8.4-fpm.sock` |
| Tunnel | Cloudflare Tunnel → `opswiki.yumalab.my.id` |
| HTTPS | Cloudflare edge (auto) |

---

## 5. Akses & Kredensial

| Resource | URL / Creds |
|----------|-------------|
| OpsWiki | `https://opswiki.yumalab.my.id` |
| Super Admin | `admin@opswiki.com` / `password` |
| Test User | `test@example.com` / (default seeder) |
| DB Local | `mbx` / `mbxLocal2026!` @ `localhost:5432/opswiki` |
| DB Remote | `admin` / `opsAdmin2026!` @ port `5432` |
| PGAdmin | Host: `202.58.198.98`, Port: `5432` |

---

## 6. Rekomendasi

1. **ArchiveRecord tenant scoping** — Tambahkan `tenant_id` dan `BelongsToTenant` trait untuk keamanan masa depan
2. **Tool model** — Pertimbangkan tenant-scoped tools di future releases
3. **Testing** — Tambahkan automated test untuk cross-tenant isolation (`assertDontSee` across tenants)
4. **Rate limiting** — Saat ini sudah ada `throttle:register`, bisa ditambah `throttle:api` untuk endpoint kritis
5. **Logging** — `ActivityLog` sudah ada, pastikan log audit mencakup akses cross-tenant attempt
