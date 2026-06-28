# PRD — OpsWiki

## 1. Ringkasan Produk

**OpsWiki** adalah aplikasi wiki pribadi modern untuk menyimpan, mengelola, mencari, dan menggunakan ulang pengetahuan teknis harian.

OpsWiki bukan hanya wiki catatan. Produk ini dirancang sebagai pusat kerja teknis yang mencakup:

- Kamus pribadi
- Dokumentasi pekerjaan
- SOP
- Troubleshooting log
- Command snippet manager
- Tools generator
- Project documentation
- Asset/document storage

Target utama OpsWiki adalah membantu pengguna mengelola banyak ide, command, error, catatan teknis, dokumentasi project, dan tools kecil agar tidak tersebar di banyak tempat.

---

## 2. Masalah yang Ingin Diselesaikan

Pengguna sering mengalami kondisi berikut:

- Banyak ide teknis muncul bersamaan dan sulit dirapikan
- Command yang pernah berhasil sulit ditemukan ulang
- Dokumentasi pekerjaan tersebar di chat, terminal, file, screenshot, dan catatan acak
- Troubleshooting lama sulit dicari ulang
- SOP belum distandarkan
- Tools kecil seperti command generator dibuat terpisah dan tidak terorganisir
- Dokumentasi tidak punya status yang jelas: draft, tested, production, deprecated
- Sulit membedakan catatan mentah, dokumentasi matang, dan solusi yang sudah terbukti berhasil

OpsWiki dibuat untuk mengubah catatan teknis menjadi sistem pengetahuan yang bisa dicari, dipakai ulang, dan dikembangkan menjadi tools internal.

---

## 3. Tujuan Produk

### 3.1 Tujuan Utama

Membangun wiki pribadi yang:

- Cepat untuk mencatat
- Mudah dicari
- Modern secara tampilan
- Bisa menyimpan command dan error log
- Bisa membuat halaman tools interaktif
- Cocok untuk dokumentasi infrastruktur dan pekerjaan teknis
- Bisa menjadi pusat dokumentasi harian

### 3.2 Tujuan Sekunder

- Menjadi basis SOP internal
- Menjadi pusat dokumentasi project
- Menjadi personal knowledge base
- Bisa dikembangkan menjadi produk internal
- Bisa diakses dari semua komputer melalui domain pribadi
- Bisa menyimpan asset, screenshot, file konfigurasi, dan export dokumentasi

---

## 4. Target Pengguna

### 4.1 Primary User

**Administrator / Developer / Technical Operator**

Karakteristik:

- Sering bekerja dengan server, Docker, Proxmox, MinIO, PostgreSQL, Cloudflare, NAS, iSCSI, VLAN, Tailscale, dan Headscale
- Sering menyelesaikan masalah teknis melalui command line
- Membutuhkan dokumentasi yang cepat dibuat dan cepat ditemukan ulang
- Membutuhkan tools kecil untuk generate command
- Sering mengerjakan troubleshooting berdasarkan log, error, dan command history

### 4.2 Future User

- Tim IT internal
- Developer internal
- Operator teknis
- Dokumentator project
- Tim support teknis

---

## 5. Scope Produk

### 5.1 In Scope — MVP

Fitur yang masuk dalam versi awal:

1. Authentication
2. Dashboard
3. Inbox / Quick Notes
4. Wiki Pages
5. Categories
6. Tags
7. Markdown editor
8. Markdown preview
9. Code block dengan copy button
10. Snippet manager
11. Tools page sederhana
12. Basic search
13. Upload gambar/file ke MinIO
14. Status dokumentasi
15. Responsive UI dasar

### 5.2 Out of Scope — MVP

Tidak dibuat dulu pada versi awal:

- Real-time collaboration
- AI assistant
- Complex role-based permission
- Public marketplace tools
- Mobile app native
- Advanced workflow approval
- Full audit log kompleks
- Multi-tenant SaaS
- Public knowledge portal

---

## 6. Product Vision

OpsWiki harus terasa seperti gabungan dari:

- Notion
- GitBook
- Internal tools dashboard
- Command snippet manager
- Personal technical knowledge base

Namun fokusnya bukan untuk catatan umum, melainkan untuk pekerjaan teknis harian.

Prinsip produk:

- Capture cepat
- Struktur rapi
- Search kuat
- Command bisa dipakai ulang
- Dokumentasi bisa berubah menjadi SOP
- Troubleshooting bisa dicari ulang
- Tools menjadi bagian dari wiki
- UI harus modern, ringan, dan tidak mengganggu fokus

---

## 7. Stack Teknologi

### 7.1 Backend

- Laravel
- PostgreSQL
- Redis
- Laravel Queue
- Laravel Filesystem S3 Driver
- Laravel Scout untuk search lanjutan

### 7.2 Frontend

- Inertia.js
- Vue 3
- TypeScript
- Tailwind CSS 4
- daisyUI 5
- Vite

### 7.3 Storage

- MinIO S3-compatible storage

### 7.4 Search

- MVP: PostgreSQL full-text search atau query sederhana
- V2: Meilisearch

### 7.5 Deployment

- Docker
- Docker Compose
- Coolify
- Cloudflare Tunnel

---

## 8. Modul Utama

## 8.1 Dashboard

Dashboard adalah halaman utama setelah login.

### Fungsi

Menampilkan ringkasan:

- Recent pages
- Recent troubleshooting cases
- Favorite snippets
- Pending notes
- Tools favorit
- Dokumentasi yang butuh review
- Quick action

### Komponen UI

- Search bar besar
- Card statistik
- Recent activity list
- Quick create buttons
- Shortcut tools

### Quick Actions

- New Note
- New Wiki Page
- New SOP
- New Troubleshooting
- New Snippet
- New Tool

---

## 8.2 Inbox

Inbox adalah tempat menaruh ide mentah.

### Tujuan

Mengurangi beban pikiran saat terlalu banyak ide muncul.

### Tipe Item

- Idea
- Error log
- Command
- Link
- Draft SOP
- Draft documentation
- Temporary note

### Field

```text
id
title
content
type
source
priority
tags
status
created_by
created_at
updated_at
```

### Status

```text
new
reviewed
converted
archived
```

### Action

- Convert to Wiki Page
- Convert to SOP
- Convert to Troubleshooting
- Convert to Snippet
- Archive
- Delete

---

## 8.3 Wiki Pages

Wiki Pages adalah modul dokumentasi umum.

### Use Case

- Dokumentasi Proxmox
- Dokumentasi Docker
- Dokumentasi MinIO
- Dokumentasi PostgreSQL
- Dokumentasi Cloudflare
- Dokumentasi network
- Catatan konsep teknis
- Dokumentasi aplikasi internal

### Field

```text
id
title
slug
summary
content_markdown
content_html
category_id
status
visibility
created_by
updated_by
published_at
created_at
updated_at
```

### Status

```text
draft
review
tested
production
deprecated
archived
```

### Fitur

- Markdown editor
- Markdown preview
- Auto slug
- Table of contents
- Tagging
- Related pages
- Attachments
- Code block copy
- Internal links

---

## 8.4 SOP Module

SOP adalah dokumentasi prosedur yang sudah distandarkan.

### Template SOP

```markdown
# SOP: [Nama SOP]

## Tujuan

## Kapan Digunakan

## Prasyarat

## Variable

## Langkah Kerja

## Validasi

## Rollback

## Catatan

## Status
```

### Field

```text
id
title
slug
purpose
use_case
requirements
variables
steps
validation
rollback
notes
status
created_by
updated_by
created_at
updated_at
```

### Use Case Awal

- Membuat bucket MinIO + IAM
- Restore PostgreSQL custom dump
- Deploy container Docker
- Install Portainer
- Setup Cloudflare Tunnel
- Setup Tailscale di LXC
- Mount iSCSI ke Proxmox
- Backup PostgreSQL ke MinIO
- Restore VM/CT Proxmox

---

## 8.5 Troubleshooting Module

Modul ini digunakan untuk menyimpan kasus masalah teknis.

### Template Troubleshooting

```markdown
# Troubleshooting: [Judul Masalah]

## Gejala

## Environment

## Error Asli

## Dugaan Penyebab

## Langkah Diagnosis

## Solusi yang Berhasil

## Solusi yang Tidak Berhasil

## Validasi

## Pencegahan

## Status
```

### Field

```text
id
title
slug
symptoms
environment
error_log
suspected_causes
diagnosis_steps
working_solution
failed_attempts
validation
prevention
severity
status
created_by
updated_by
created_at
updated_at
```

### Severity

```text
low
medium
high
critical
```

### Status

```text
open
investigating
solved
workaround
failed
archived
```

---

## 8.6 Snippet Manager

Snippet menyimpan command yang sering digunakan.

### Tujuan

Command tidak lagi tercecer di chat atau terminal history.

### Field

```text
id
title
description
command
language
platform
variables
category_id
is_tested
last_used_at
created_by
created_at
updated_at
```

### Platform

```text
linux
macos
windows
docker
proxmox
postgresql
minio
mikrotik
synology
qnap
cloudflare
tailscale
```

### Fitur

- Copy command
- Edit variable
- Mark as tested
- Favorite
- Link ke halaman wiki/SOP/troubleshooting
- Search berdasarkan command, title, tag, atau platform

### Contoh Snippet

```bash
docker compose ps
```

```bash
mc admin user add myminio USERNAME PASSWORD
```

```bash
pg_restore -h HOST -p PORT -U USER -d DBNAME FILE.dump
```

---

## 8.7 Tools Module

Tools adalah halaman interaktif berbasis Vue.

### Tujuan

Mengubah dokumentasi menjadi alat kerja.

### Jenis Tools Awal

- MinIO IAM Generator
- Docker Compose Template Builder
- PostgreSQL Backup Command Builder
- pg_restore Helper
- rclone Copy Command Builder
- Proxmox VM Checklist
- iSCSI Recovery Checklist
- Cloudflare Tunnel Config Helper

### Field

```text
id
title
slug
description
tool_type
component_name
config_schema
status
created_by
created_at
updated_at
```

### Tool Type

```text
generator
checklist
calculator
template
validator
reference
```

### Contoh Flow: MinIO IAM Generator

Input:

```text
alias
bucket
username
password
policy_name
permission_type
```

Output:

```bash
mc mb myminio/dbbackup
mc admin user add myminio dbbackup-user STRONG_PASSWORD
mc admin policy create myminio dbbackup-policy dbbackup-policy.json
mc admin policy attach myminio dbbackup-policy --user dbbackup-user
```

Fitur:

- Generate command
- Copy command
- Reset form
- Save output as snippet
- Export as markdown

---

## 8.8 Project Documentation

Modul untuk dokumentasi per project.

### Field

```text
id
name
slug
description
status
repository_url
production_url
staging_url
server_location
environment_notes
created_by
created_at
updated_at
```

### Isi Project

- Overview
- Deployment notes
- Environment variables
- Database
- Backup
- Error notes
- Related SOP
- Related tools
- Related snippets

### Status Project

```text
planning
development
staging
production
maintenance
archived
```

---

## 8.9 Asset Manager

Asset manager digunakan untuk file, gambar, diagram, export, dan attachment.

### Storage

Gunakan MinIO S3-compatible storage.

### Field

```text
id
filename
original_name
mime_type
size
disk
bucket
path
url
related_type
related_id
uploaded_by
created_at
updated_at
```

### Fitur

- Upload image
- Upload config file
- Attach to page
- Preview image
- Copy asset URL
- Delete asset
- Validasi file upload

---

## 9. Search

### 9.1 MVP Search

Untuk MVP, gunakan PostgreSQL full-text search atau query sederhana.

Search target:

- title
- content
- tags
- command
- error_log
- category
- platform

### 9.2 Advanced Search

Untuk versi berikutnya gunakan Meilisearch.

Index:

- pages
- sops
- troubleshooting
- snippets
- tools
- projects

Filter:

- type
- status
- tag
- category
- platform
- severity
- created_at
- updated_at

Search harus bisa menemukan error asli seperti:

```text
The input is a PostgreSQL custom-format dump
```

atau:

```text
MinIO Console tidak menampilkan IAM
```

---

## 10. UI / UX Requirement

### 10.1 Design Direction

Style UI:

- Modern documentation dashboard
- Dark mode first
- Clean sidebar
- Search-focused
- Card-based dashboard
- Code block jelas
- Tidak terlalu ramai
- Fokus ke produktivitas teknis

### 10.2 Layout Utama

```text
┌────────────────────────────────────────────────────────────┐
│ Topbar: Logo | Global Search | Quick Create | User Menu      │
├───────────────┬──────────────────────────────┬──────────────┤
│ Sidebar       │ Main Content                  │ Right Panel   │
│ Dashboard     │ Page / Form / Tool             │ TOC           │
│ Inbox         │                                │ Related       │
│ Wiki          │                                │ Tags          │
│ SOP           │                                │ Status        │
│ Trouble       │                                │ Actions       │
│ Snippets      │                                │               │
│ Tools         │                                │               │
│ Projects      │                                │               │
└───────────────┴──────────────────────────────┴──────────────┘
```

### 10.3 Komponen daisyUI

Gunakan komponen:

- drawer
- navbar
- menu
- card
- badge
- button
- modal
- dropdown
- tabs
- table
- alert
- textarea
- input
- select
- collapse
- toast
- breadcrumbs
- loading

### 10.4 Theme

Minimal theme:

- dark
- light
- corporate/custom

Default:

```text
dark
```

### 10.5 Responsive

Target:

- Desktop first
- Tablet usable
- Mobile basic reading and quick note

Mobile tidak harus lengkap di MVP.

---

## 11. Technical Requirement

### 11.1 Backend

```text
Framework: Laravel
Auth: Laravel Breeze / Laravel Starter Kit Inertia Vue
Database: PostgreSQL
Queue: Redis
Cache: Redis
Storage: MinIO S3
Search MVP: PostgreSQL FTS
Search V2: Meilisearch
```

### 11.2 Frontend

```text
Inertia.js
Vue 3
TypeScript
Tailwind CSS 4
daisyUI 5
Vite
```

### 11.3 Editor

MVP editor:

```text
Markdown textarea + preview
```

V2 editor:

```text
TipTap / Milkdown / Monaco-lite for code-heavy docs
```

### 11.4 Syntax Highlighting

Gunakan salah satu:

- Shiki
- Prism.js

Requirement language:

- Bash
- YAML
- JSON
- SQL
- PHP
- JavaScript
- TypeScript
- Dockerfile
- Nginx config
- Markdown

### 11.5 File Storage

Gunakan Laravel filesystem S3 driver ke MinIO.

Bucket awal:

```text
opswiki-assets
opswiki-backups
```

### 11.6 Deployment

Target:

```text
Docker Compose
Coolify
Cloudflare Tunnel
```

Environment:

```text
production
staging
local
```

---

## 12. Data Model Awal

### 12.1 users

```text
id
name
email
password
avatar
created_at
updated_at
```

### 12.2 categories

```text
id
name
slug
type
parent_id
sort_order
created_at
updated_at
```

### 12.3 tags

```text
id
name
slug
color
created_at
updated_at
```

### 12.4 pages

```text
id
title
slug
summary
content_markdown
content_html
category_id
status
visibility
created_by
updated_by
published_at
created_at
updated_at
```

### 12.5 sops

```text
id
title
slug
purpose
use_case
requirements
variables
steps
validation
rollback
notes
status
created_by
updated_by
created_at
updated_at
```

### 12.6 troubleshooting_cases

```text
id
title
slug
symptoms
environment
error_log
suspected_causes
diagnosis_steps
working_solution
failed_attempts
validation
prevention
severity
status
created_by
updated_by
created_at
updated_at
```

### 12.7 snippets

```text
id
title
description
command
language
platform
variables
is_tested
last_used_at
created_by
created_at
updated_at
```

### 12.8 tools

```text
id
title
slug
description
tool_type
component_name
config_schema
status
created_by
created_at
updated_at
```

### 12.9 projects

```text
id
name
slug
description
status
repository_url
production_url
staging_url
server_location
environment_notes
created_by
created_at
updated_at
```

### 12.10 assets

```text
id
filename
original_name
mime_type
size
disk
bucket
path
url
related_type
related_id
uploaded_by
created_at
updated_at
```

### 12.11 taggables

```text
tag_id
taggable_type
taggable_id
```

### 12.12 page_relations

```text
id
source_type
source_id
target_type
target_id
relation_type
created_at
updated_at
```

---

## 13. User Stories

### 13.1 Inbox

Sebagai user, saya ingin mencatat ide cepat tanpa memilih kategori agar ide tidak hilang.

Acceptance criteria:

- User dapat membuat note dari dashboard
- Note tersimpan ke Inbox
- Note bisa diberi tag
- Note bisa dikonversi ke Wiki, SOP, Troubleshooting, atau Snippet

---

### 13.2 Wiki Page

Sebagai user, saya ingin membuat halaman dokumentasi dengan Markdown agar dokumentasi teknis mudah ditulis.

Acceptance criteria:

- User dapat membuat, edit, delete halaman
- Halaman memiliki title, category, tag, status
- Markdown dapat dipreview
- Code block memiliki tombol copy

---

### 13.3 SOP

Sebagai user, saya ingin membuat SOP dengan struktur tetap agar pekerjaan berulang bisa distandarkan.

Acceptance criteria:

- User dapat membuat SOP dari template
- SOP punya section prasyarat, langkah, validasi, rollback
- SOP bisa diberi status tested/production/deprecated

---

### 13.4 Troubleshooting

Sebagai user, saya ingin menyimpan kasus troubleshooting agar masalah yang sama bisa diselesaikan lebih cepat di masa depan.

Acceptance criteria:

- User dapat menyimpan error asli
- User dapat mencatat solusi berhasil dan solusi gagal
- Kasus dapat dicari berdasarkan error log
- Kasus memiliki status solved/open

---

### 13.5 Snippet

Sebagai user, saya ingin menyimpan command agar bisa dicopy dan digunakan ulang.

Acceptance criteria:

- User dapat membuat snippet command
- Snippet bisa diberi platform
- Snippet bisa dicopy
- Snippet bisa ditandai tested

---

### 13.6 Tools

Sebagai user, saya ingin membuat tools generator agar dokumentasi bisa langsung membantu pekerjaan teknis.

Acceptance criteria:

- User dapat membuka halaman tools
- Tool menerima input form
- Tool menghasilkan output command
- Output bisa dicopy
- Output bisa disimpan sebagai snippet

---

## 14. MVP Feature List

### Must Have

- Login/logout
- Dashboard
- Sidebar navigation
- Global quick search
- Inbox CRUD
- Wiki Page CRUD
- Category CRUD
- Tag CRUD
- Markdown editor
- Markdown preview
- Code block copy
- Snippet CRUD
- Tools index
- MinIO IAM Generator
- Asset upload
- Status badge

### Should Have

- SOP CRUD
- Troubleshooting CRUD
- Related pages
- Favorite snippets
- Export markdown
- Basic activity log

### Could Have

- Meilisearch
- Project module
- Advanced editor
- Version history
- Public share link

### Won’t Have in MVP

- AI assistant
- Multi-tenant
- Real-time collaboration
- Complex approval workflow

---

## 15. MVP Routes

```text
/auth/login
/auth/logout

/dashboard

/inbox
/inbox/create
/inbox/{id}
/inbox/{id}/edit

/wiki
/wiki/create
/wiki/{slug}
/wiki/{slug}/edit

/sop
/sop/create
/sop/{slug}

/troubleshooting
/troubleshooting/create
/troubleshooting/{slug}

/snippets
/snippets/create
/snippets/{id}/edit

/tools
/tools/minio-iam-generator
/tools/docker-compose-builder
/tools/pg-restore-helper

/assets
/settings
```

---

## 16. Vue Component Structure

```text
resources/js/
├── Layouts/
│   ├── AppLayout.vue
│   ├── AuthLayout.vue
│   └── DocumentationLayout.vue
│
├── Components/
│   ├── AppSidebar.vue
│   ├── AppTopbar.vue
│   ├── GlobalSearch.vue
│   ├── StatusBadge.vue
│   ├── TagBadge.vue
│   ├── MarkdownEditor.vue
│   ├── MarkdownPreview.vue
│   ├── CodeBlock.vue
│   ├── CopyButton.vue
│   ├── QuickCreateModal.vue
│   └── EmptyState.vue
│
├── Pages/
│   ├── Dashboard.vue
│   ├── Inbox/
│   ├── Wiki/
│   ├── SOP/
│   ├── Troubleshooting/
│   ├── Snippets/
│   ├── Tools/
│   │   ├── Index.vue
│   │   ├── MinioIamGenerator.vue
│   │   ├── DockerComposeBuilder.vue
│   │   └── PgRestoreHelper.vue
│   └── Settings/
│
└── Composables/
    ├── useCopy.ts
    ├── useMarkdown.ts
    ├── useToast.ts
    └── useToolOutput.ts
```

---

## 17. Initial Seeder Data

### Categories

- Docker
- Proxmox
- MinIO
- PostgreSQL
- Cloudflare
- Tailscale
- NAS & iSCSI
- Network
- Windows
- Mac

### Tags

- backup
- restore
- docker
- proxmox
- minio
- postgresql
- cloudflare
- tailscale
- iscsi
- nas
- production
- draft
- tested

### Default Tools

- MinIO IAM Generator
- PostgreSQL Restore Helper
- Docker Compose Builder
- rclone Copy Builder

---

## 18. Non-Functional Requirements

### 18.1 Performance

- Dashboard load < 2 seconds
- Search result < 1 second for MVP dataset
- Page view < 1.5 seconds
- Editor input tidak terasa delay pada dokumen standar

### 18.2 Security

- Auth wajib
- Password hashing default Laravel
- CSRF protection
- Private by default
- File upload validation
- No public asset listing
- Sensitive env tidak ditampilkan di UI
- Role sederhana: owner/admin/user, jika diperlukan setelah MVP

### 18.3 Backup

Minimal backup:

- PostgreSQL database
- Uploaded assets di MinIO
- `.env` production
- Docker compose file
- Custom tool component
- Export dokumentasi penting

### 18.4 Maintainability

- Modular controller
- Service layer untuk tools generator
- Vue components reusable
- Seeder untuk default category/tag/tools
- Form request validation di Laravel
- Policy/Gate disiapkan untuk permission berikutnya

---

## 19. Deployment Requirement

### 19.1 Docker Services

```yaml
services:
  app:
    build: .
    depends_on:
      - postgres
      - redis
      - minio

  postgres:
    image: postgres:16

  redis:
    image: redis:alpine

  meilisearch:
    image: getmeili/meilisearch:latest

  minio:
    image: minio/minio:latest
```

### 19.2 Environment Variables

```env
APP_NAME=OpsWiki
APP_ENV=production
APP_URL=https://wiki.yumalab.my.id

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=opswiki
DB_USERNAME=opswiki
DB_PASSWORD=change_me

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=opswiki
AWS_SECRET_ACCESS_KEY=change_me
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=opswiki-assets
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

SCOUT_DRIVER=database
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=change_me
```

---

## 20. Milestone

### Milestone 1 — Project Foundation

Target:

- Laravel + Inertia + Vue project ready
- Tailwind CSS 4 + daisyUI 5 installed
- Auth ready
- AppLayout ready
- Dashboard skeleton ready

### Milestone 2 — Core Wiki

Target:

- Categories
- Tags
- Wiki CRUD
- Markdown editor
- Markdown preview
- Code block copy

### Milestone 3 — Inbox + Snippets

Target:

- Inbox CRUD
- Convert inbox to page/snippet
- Snippet CRUD
- Copy command
- Mark snippet as tested

### Milestone 4 — Tools

Target:

- Tools index
- MinIO IAM Generator
- PostgreSQL Restore Helper
- Docker Compose Builder

### Milestone 5 — SOP + Troubleshooting

Target:

- SOP module
- Troubleshooting module
- Templates
- Status system

### Milestone 6 — Storage + Deployment

Target:

- MinIO upload
- Docker Compose production
- Coolify deployment
- Cloudflare Tunnel
- Backup script

---

## 21. Success Metrics

Produk dianggap berhasil jika:

- User dapat membuat dokumentasi teknis dalam < 1 menit
- User dapat menemukan command lama dalam < 10 detik
- Setidaknya 20 snippet tersimpan dalam minggu pertama
- Setidaknya 10 troubleshooting case terdokumentasi
- Tools generator mulai menggantikan command manual
- Wiki mulai menjadi referensi utama sebelum membuka chat lama
- Dokumentasi yang sering dipakai berubah menjadi SOP

---

## 22. Risiko

### Risiko 1 — Scope Terlalu Besar

Mitigasi:

- Bangun MVP kecil: Wiki + Inbox + Snippet + 1 Tool
- Modul besar seperti AI assistant dan version history ditunda

### Risiko 2 — Terlalu Sibuk dengan UI

Mitigasi:

- Gunakan daisyUI dulu
- Custom theme dilakukan setelah fitur inti stabil

### Risiko 3 — Search Belum Kuat

Mitigasi:

- Mulai dengan PostgreSQL search
- Tambah Meilisearch setelah data cukup banyak

### Risiko 4 — Tools Terlalu Banyak

Mitigasi:

- Mulai dari tools yang sering dipakai:
  - MinIO IAM Generator
  - pg_restore Helper
  - Docker Compose Builder

### Risiko 5 — Dokumentasi Tidak Konsisten

Mitigasi:

- Sediakan template bawaan
- Gunakan status dokumentasi
- Gunakan tag dan category sejak awal

---

## 23. Prioritas Implementasi

Urutan kerja yang paling aman:

1. Setup Laravel + Inertia + Vue
2. Setup Tailwind CSS 4 + daisyUI 5
3. Setup Auth
4. Buat layout utama
5. Buat Wiki CRUD
6. Buat Markdown editor + copy code
7. Buat Inbox
8. Buat Snippets
9. Buat MinIO IAM Generator
10. Buat SOP dan Troubleshooting
11. Tambahkan MinIO upload
12. Siapkan deployment

---

## 24. Keputusan Produk

Keputusan final untuk versi awal:

```text
Nama produk: OpsWiki
Stack frontend: Inertia.js + Vue 3
UI: Tailwind CSS 4 + daisyUI 5
Backend: Laravel
Database: PostgreSQL
Storage: MinIO
Search MVP: PostgreSQL
Search V2: Meilisearch
Deployment: Docker / Coolify
Akses: Cloudflare Tunnel
```

MVP harus fokus ke:

```text
Wiki + Inbox + Snippets + Tools Generator
```

Fokus utama versi awal adalah menyelesaikan masalah berikut:

- Ide terlalu banyak dan butuh tempat cepat untuk ditaruh
- Command tercecer
- Dokumentasi kerja sulit ditemukan ulang
- Troubleshooting lama tidak terdokumentasi
- Tools kecil perlu disatukan dengan dokumentasi

---

## 25. Roadmap Setelah MVP

### V1.1

- SOP module lebih matang
- Troubleshooting module lebih matang
- Related pages
- Favorite snippets
- Export markdown

### V1.2

- Meilisearch
- Project module
- Asset manager lanjutan
- Basic activity log

### V1.3

- Version history
- Public share link
- Role-based permission sederhana
- Better markdown editor

### V2

- AI assistant internal
- Semantic search
- Knowledge graph
- Import dari markdown folder
- Export static documentation
