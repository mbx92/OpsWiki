# OpsWiki — Panduan Pengguna

**Versi dokumen:** 1.0  
**Aplikasi:** OpsWiki — Technical Knowledge Base untuk tim DevOps & IT

---

## Daftar Isi

1. [Pengenalan](#1-pengenalan)
2. [Memulai](#2-memulai)
3. [Navigasi & Dashboard](#3-navigasi--dashboard)
4. [Wiki](#4-wiki)
5. [Books (Rak Dokumentasi)](#5-books-rak-dokumentasi)
6. [Snippets](#6-snippets)
7. [SOP (Standard Operating Procedure)](#7-sop-standard-operating-procedure)
8. [Troubleshooting](#8-troubleshooting)
9. [Projects](#9-projects)
10. [Inbox](#10-inbox)
11. [Knowledge Graph](#11-knowledge-graph)
12. [Assistant (AI)](#12-assistant-ai)
13. [Tools](#13-tools)
14. [Assets](#14-assets)
15. [Pencarian](#15-pencarian)
16. [Berbagi & Portal Publik](#16-berbagi--portal-publik)
17. [Pengaturan Workspace](#17-pengaturan-workspace)
18. [Peran & Izin Pengguna](#18-peran--izin-pengguna)
19. [Paket Langganan](#19-paket-langganan)
20. [Platform Admin (Super Admin)](#20-platform-admin-super-admin)
21. [Tips & Praktik Terbaik](#21-tips--praktik-terbaik)
22. [Referensi Cepat](#22-referensi-cepat)

---

## 1. Pengenalan

OpsWiki adalah platform **basis pengetahuan teknis** untuk tim DevOps, SRE, dan IT. Semua dokumentasi operasional — runbook, SOP, catatan troubleshooting, perintah shell, dan dokumentasi proyek — dapat dikelola dalam satu workspace.

### Fitur utama

| Modul | Fungsi |
|-------|--------|
| **Wiki** | Halaman dokumentasi dengan editor Markdown |
| **Books** | Kumpulan halaman wiki yang terurut seperti buku panduan |
| **SOPs** | Prosedur operasional standar terstruktur |
| **Troubleshooting** | Kasus error dengan gejala, diagnosis, dan solusi |
| **Snippets** | Perpustakaan perintah/kode siap salin |
| **Projects** | Hub dokumentasi per proyek/aplikasi |
| **Inbox** | Tangkap ide, log error, atau catatan cepat |
| **Tools** | Generator perintah (MinIO, PostgreSQL, Docker, rclone) |
| **Knowledge Graph** | Visualisasi relasi antar konten |
| **Assistant** | Chat AI untuk menjawab pertanyaan dari basis pengetahuan |

### Siapa yang menggunakan OpsWiki?

- **Tim DevOps/SRE** — dokumentasi deploy, runbook, dan incident response
- **Tim IT** — SOP internal dan troubleshooting harian
- **Tim proyek** — dokumentasi teknis terpusat per aplikasi
- **Platform admin** — mengelola banyak workspace klien (mode SaaS)

---

## 2. Memulai

### 2.1 Login

1. Buka URL aplikasi (mis. `https://opswiki.example.com`)
2. Klik **Log in**
3. Masukkan email dan password
4. Setelah login, Anda diarahkan ke **Dashboard**

> Akun harus **aktif** dan email **terverifikasi** sebelum bisa mengakses modul utama.

### 2.2 Registrasi (jika diaktifkan)

Jika admin mengaktifkan pendaftaran publik:

1. Buka halaman **Register**
2. Isi: nama, email, password, dan **nama workspace**
3. Workspace baru dibuat otomatis dengan paket **Free**
4. Anda menjadi **Owner** workspace tersebut

### 2.3 Lupa password

1. Klik **Forgot your password?** di halaman login
2. Masukkan email → terima link reset via email
3. Buat password baru

### 2.4 Workspace & multi-tenant

Setiap organisasi memiliki **workspace** terpisah. Data antar workspace tidak saling terlihat.

- **Domain utama** — login, marketing, admin platform
- **Subdomain workspace** — `{slug}.opswiki.example.com`
- **Custom domain** (Pro) — domain sendiri yang diarahkan ke workspace

---

## 3. Navigasi & Dashboard

### Sidebar

Menu di sisi kiri aplikasi (sesuai izin dan paket Anda):

| Menu | Deskripsi |
|------|-----------|
| Dashboard | Ringkasan aktivitas workspace |
| Inbox | Catatan cepat & konversi ke konten |
| Books | Rak buku dokumentasi |
| Wiki | Halaman dokumentasi |
| Snippets | Perpustakaan perintah |
| SOPs | Prosedur operasional |
| Troubleshooting | Kasus masalah teknis |
| Projects | Dokumentasi per proyek |
| Knowledge | Graf relasi konten |
| Assistant | Chat AI |
| Tools | Generator perintah ops |
| Assets | File upload (gambar, PDF, dll.) |
| Settings | Pengaturan workspace |

Menu dengan ikon gembok memerlukan **upgrade paket** (Pro atau Team).

### Dashboard

Dashboard menampilkan:

- Statistik: inbox baru, jumlah halaman wiki, snippets, favorit
- Halaman wiki terbaru
- Item inbox terbaru
- Snippet favorit
- Aksi cepat ke modul populer

---

## 4. Wiki

Wiki adalah inti dokumentasi OpsWiki. Setiap halaman ditulis dalam **Markdown** dan dirender sebagai HTML.

### 4.1 Membuat halaman wiki

1. Sidebar → **Wiki** → **Create** (atau tombol buat baru)
2. Isi field:
   - **Title** — judul halaman
   - **Slug** — URL (otomatis dari judul, bisa diubah)
   - **Summary** — ringkasan singkat (opsional)
   - **Content** — isi Markdown
   - **Category** — kategori organisasi
   - **Tags** — label bebas
   - **Status** — draft, review, tested, production, deprecated, archived
   - **Visibility** — private, internal, public
3. Klik **Save**

### 4.2 Status halaman

| Status | Kapan digunakan |
|--------|-----------------|
| Draft | Masih ditulis, belum siap |
| Review | Menunggu review rekan |
| Tested | Sudah diverifikasi di staging |
| Production | Dokumentasi resmi/live |
| Deprecated | Tidak lagi berlaku |
| Archived | Disimpan untuk arsip |

### 4.3 Visibility (visibilitas)

| Level | Siapa bisa melihat |
|-------|-------------------|
| **Private** | Anggota workspace dengan izin wiki |
| **Internal** | Semua anggota workspace (tanpa link publik) |
| **Public** | Siapa saja via link share & portal publik |

### 4.4 Mengedit & riwayat versi

1. Buka halaman wiki → **Edit**
2. Setiap penyimpanan membuat **snapshot versi**
3. Akses riwayat: **History** → lihat versi lama → **Restore** jika perlu

### 4.5 Menghapus halaman

1. Buka halaman → **Delete**
2. Konfirmasi penghapusan

> Memerlukan izin `wiki.delete`.

### 4.6 Export halaman

- **Export markdown** — unduh file `.md` dari halaman
- **Export static site** (Pro) — unduh seluruh wiki sebagai situs HTML statis

### 4.7 Relasi antar konten

Saat membuat/mengedit halaman, Anda bisa **menautkan** ke:

- Halaman wiki lain
- SOP
- Kasus troubleshooting
- Project
- Snippet

Relasi ini muncul di **Knowledge Graph**.

---

## 5. Books (Rak Dokumentasi)

Books mengelompokkan halaman wiki menjadi satu set dokumentasi berurutan — seperti buku panduan atau manual.

> Modul Books memerlukan paket **Pro** atau lebih tinggi.

### 5.1 Membuat book baru

**Cara 1 — Book kosong:**

1. Sidebar → **Books** → **New Book**
2. Isi **Title**, **Description**, Category, Status
3. Klik **Create Book**
4. Tambahkan halaman via import atau pilih halaman wiki yang sudah ada

**Cara 2 — Import file:**

1. Sidebar → **Books** → **Import MD/HTML**
2. Isi nama book, description, category
3. Upload file `.md`, `.html`, `.zip`, atau pilih folder lokal
4. Setiap file menjadi satu halaman wiki dalam book

### 5.2 Mengelola book

Di halaman detail book:

| Aksi | Deskripsi |
|------|-----------|
| **Edit** | Ubah title, description, category, status |
| **Share** | Atur visibilitas publik |
| **Import pages** | Tambah halaman dari file MD/HTML |
| **Add existing pages** | Pilih halaman wiki standalone |
| **Reorder** | Geser halaman ↑↓ |
| **Export markdown** | Unduh semua halaman sebagai ZIP |
| **Export static site** | Unduh situs HTML statis |
| **Remove book** | Hapus book (halaman wiki tetap ada) |

### 5.3 Format import yang didukung

| Format | Judul diambil dari |
|--------|-------------------|
| `.md` | Heading `#` pertama atau frontmatter `title:` |
| `.html` | Tag `<title>` atau `<h1>` |
| `.zip` | Semua `.md`/`.html` di dalam arsip |
| Folder lokal | Semua `.md`/`.html` berurutan alfabetis |

---

## 6. Snippets

Snippets adalah perpustakaan **perintah, query, atau kode** yang sering dipakai.

### 6.1 Membuat snippet

1. Sidebar → **Snippets** → **Create**
2. Isi:
   - **Title** — nama snippet
   - **Platform** — bash, docker, kubernetes, sql, dll.
   - **Content** — perintah/kode
   - **Description** — kapan digunakan
   - **Tags** — label
3. Save

### 6.2 Menggunakan snippet

- Klik **Copy** untuk menyalin ke clipboard
- Tandai **Favorite** (bintang) untuk akses cepat di Dashboard
- Filter berdasarkan platform atau tag

### 6.3 Snippet dari Tools

Beberapa tool (mis. MinIO IAM Generator) bisa langsung **menyimpan output sebagai snippet**.

---

## 7. SOP (Standard Operating Procedure)

SOP menyimpan prosedur operasional terstruktur.

> Memerlukan paket **Pro**.

### Field SOP

| Field | Isi |
|-------|-----|
| Purpose | Tujuan prosedur |
| Use case | Kapan prosedur dipakai |
| Requirements | Prasyarat (akses, tools, dll.) |
| Steps | Langkah-langkah |
| Validation | Cara verifikasi berhasil |
| Rollback | Cara rollback jika gagal |
| Notes | Catatan tambahan |

### Import SOP

1. Sidebar → **SOPs** → **Import**
2. Upload file Markdown → field diisi otomatis dari struktur heading

---

## 8. Troubleshooting

Modul untuk mendokumentasikan **kasus masalah teknis** dan solusinya.

> Memerlukan paket **Pro**.

### Field troubleshooting

| Field | Isi |
|-------|-----|
| Symptoms | Gejala yang terlihat |
| Environment | OS, versi, stack teknologi |
| Error log | Log error relevan |
| Suspected causes | Dugaan penyebab |
| Diagnosis steps | Langkah diagnosis |
| Working solution | Solusi yang berhasil |
| Failed attempts | Percobaan yang gagal |
| Validation | Cara memastikan sudah fix |
| Prevention | Pencegahan ke depan |

### Import troubleshooting

Sama seperti SOP — upload Markdown via **Import**.

---

## 9. Projects

Project adalah **hub dokumentasi** untuk satu aplikasi atau layanan.

> Memerlukan paket **Pro**.

### 9.1 Membuat project

1. Sidebar → **Projects** → **Create**
2. Isi: name, description, status, URL repo/production/staging, catatan environment/deployment/database/backup
3. Save

### 9.2 Quick create dari project

Di halaman detail project, buat dokumentasi terkait dengan satu klik:

| Aksi | Hasil |
|------|-------|
| Wiki Overview | Halaman wiki ringkasan proyek |
| Wiki Deployment | Template dokumentasi deploy |
| Deploy SOP | SOP deploy terisi otomatis |
| Snippet | Snippet kosong ter-link ke project |
| Troubleshooting | Kasus troubleshooting ter-link |

Semua konten yang dibuat otomatis **terhubung** ke project di Knowledge Graph.

### 9.3 Status project

`planning` → `development` → `staging` → `production` → `maintenance` → `archived`

---

## 10. Inbox

Inbox adalah **kotak tangkap cepat** untuk ide, log error, perintah, atau draft sebelum dijadikan konten formal.

### 10.1 Menambah item

1. Sidebar → **Inbox** → **Create**
2. Isi: title, content, type, source, priority
3. Save

### 10.2 Priority

`low` → `medium` → `high` → `urgent`

### 10.3 Konversi ke konten

Dari detail inbox, klik **Convert to**:

- **Wiki page** — jadi halaman wiki baru
- **Snippet** — jadi snippet
- **SOP** — jadi SOP
- **Troubleshooting** — jadi kasus troubleshooting

Item inbox otomatis berstatus **converted** setelah konversi.

---

## 11. Knowledge Graph

Knowledge Graph menampilkan **peta relasi** antar semua konten yang saling ditautkan.

1. Sidebar → **Knowledge**
2. Lihat node (wiki, SOP, troubleshooting, project, snippet) dan garis relasinya
3. Identifikasi konten **orphan** (belum terhubung ke apapun)

> Berguna untuk audit kelengkapan dokumentasi dan menemukan gap.

---

## 12. Assistant (AI)

Assistant adalah chatbot AI yang menjawab pertanyaan berdasarkan konten workspace.

> Memerlukan paket **Pro** + konfigurasi AI di Settings → Integrations.

### Setup (Admin)

1. Settings → **Integrations** → **AI**
2. Aktifkan, isi Base URL, API Key, Model, System Prompt
3. Save

### Menggunakan

1. Sidebar → **Assistant**
2. Ketik pertanyaan → kirim
3. Assistant merespons berdasarkan basis pengetahuan workspace

---

## 13. Tools

Built-in generator perintah operasional.

> Memerlukan paket **Pro**.

| Tool | Output |
|------|--------|
| **MinIO IAM Generator** | Perintah `mc` untuk bucket, user, policy IAM |
| **PostgreSQL Restore Helper** | Perintah `pg_restore` |
| **Docker Compose Builder** | File `docker-compose.yml` dasar |
| **rclone Copy Builder** | Perintah rclone copy/sync |

1. Sidebar → **Tools** → pilih tool
2. Isi form → **Generate**
3. Salin output atau simpan sebagai snippet (MinIO)

---

## 14. Assets

Upload file pendukung dokumentasi.

| Tipe | Batas |
|------|-------|
| Gambar, PDF, config, arsip | Max 10 MB per file |

1. Sidebar → **Assets** → **Upload**
2. Opsional: tautkan ke halaman wiki
3. File bisa di-embed di halaman wiki

Jika MinIO dikonfigurasi, file disimpan di object storage dan arsip otomatis.

---

## 15. Pencarian

1. Klik **Search** di header atau buka `/search`
2. Ketik kata kunci
3. Hasil dari: wiki, snippets, inbox, SOP, troubleshooting, projects

Mode **Smart** (default) menggunakan pencarian semantik untuk wiki dan keyword untuk modul lain.

---

## 16. Berbagi & Portal Publik

### 16.1 Share link (halaman tunggal)

1. Buka halaman wiki → **Share**
2. Set visibility ke **Public**
3. Salin link: `/share/pages/{slug}`
4. Siapa saja dengan link bisa membaca tanpa login

### 16.2 Share book

1. Buka book → **Share**
2. Set visibility **Public**
3. Opsional: centang **Also make all pages in this book public**
4. Link: `/share/books/{slug}`

### 16.3 Portal publik workspace

Portal menampilkan semua konten **public** workspace:

- URL default: `/portal`
- URL per tenant: `/w/{slug-workspace}`

Atur visibilitas konten ke **Public** agar muncul di portal. URL portal terlihat di **Settings → Workspace**.

### Matriks visibilitas

| Visibility | Anggota workspace | Link share | Portal publik |
|------------|:-----------------:|:----------:|:-------------:|
| Private | ✓ | ✗ | ✗ |
| Internal | ✓ | ✗ | ✗ |
| Public | ✓ | ✓ | ✓ |

---

## 17. Pengaturan Workspace

Akses via Sidebar → **Settings**.

### 17.1 Profile

- Ubah nama, email, password
- Route: `/profile`

### 17.2 Workspace

- Nama workspace
- URL subdomain
- URL portal publik
- **Custom domain** (Pro): tambah domain → ikuti instruksi DNS CNAME → verifikasi

### 17.3 Billing

- Lihat paket langganan saat ini
- Riwayat invoice (read-only)
- Upgrade via admin platform

### 17.4 Categories & Tags

- Kelola kategori untuk wiki/books
- Kelola tag global

### 17.5 Users (Pro)

- Undang anggota tim
- Assign role: Owner, Admin, User
- Nonaktifkan akun

### 17.6 Roles (Team)

- Kustomisasi izin per role (Admin/User)
- Owner selalu full access

### 17.7 Integrations (Pro)

| Integrasi | Fungsi |
|-----------|--------|
| **MinIO** | Object storage untuk assets & arsip import/export |
| **GlitchTip** | Error tracking (DSN, environment) |
| **AI** | Konfigurasi Assistant |

### 17.8 Activity Log (Team)

- Audit trail: siapa membuat/mengedit/menghapus konten
- Filter berdasarkan user, aksi, tipe konten

---

## 18. Peran & Izin Pengguna

### Peran bawaan

| Role | Deskripsi |
|------|-----------|
| **Owner** | Akses penuh, kelola billing & role permissions |
| **Admin** | Kelola konten, users, settings (tidak edit role permissions) |
| **User** | Buat/edit konten sendiri, akses terbatas |

### Perbandingan singkat

| Kemampuan | Owner | Admin | User |
|-----------|:-----:|:-----:|:----:|
| Wiki CRUD | ✓ | ✓ | ✓ (tanpa delete/import) |
| Books manage | ✓ | ✓ | view only |
| SOP/Troubleshooting manage | ✓ | ✓ | view only |
| Projects manage | ✓ | ✓ | view only |
| Kelola users | ✓ | ✓ | ✗ |
| Edit role permissions | ✓ | ✗ | ✗ |
| Activity log | ✓ | ✓ | ✗ |

**Super Admin** (platform) bypass semua izin dan limit paket.

---

## 19. Paket Langganan

### Perbandingan paket

| Fitur | Free | Pro | Team |
|-------|:----:|:---:|:----:|
| Wiki, Snippets, Inbox | ✓ | ✓ | ✓ |
| Knowledge Graph | ✓ | ✓ | ✓ |
| Books, SOP, Troubleshooting | ✗ | ✓ | ✓ |
| Projects, Tools | ✗ | ✓ | ✓ |
| Wiki import/export static | ✗ | ✓ | ✓ |
| Assistant & Integrations | ✗ | ✓ | ✓ |
| Custom domain | ✗ | ✓ | ✓ |
| Kelola users | ✗ | ✓ | ✓ |
| Activity log | ✗ | ✗ | ✓ |
| Kustomisasi role | ✗ | ✗ | ✓ |
| Limit users | 3 | ∞ | ∞ |
| Limit wiki pages | 100 | ∞ | ∞ |

Menu dengan gembok → klik untuk info upgrade di `/upgrade/pro` atau `/upgrade/team`.

---

## 20. Platform Admin (Super Admin)

Hanya untuk **Super Admin** platform (`is_super_admin = true`).

Akses: Settings → **Platform Admin** atau `/platform`

| Area | Fungsi |
|------|--------|
| **Dashboard** | Statistik tenant, breakdown paket |
| **Tenants** | Lihat/kelola workspace, ubah paket/status |
| **Billing** | Revenue, subscription, invoice, payment |
| **Plans** | Edit harga, fitur marketing, feature gates, limits |
| **Policies** | Terms of Service, Privacy, Acceptable Use |
| **Super Admins** | Promote/demote platform admin |

### Perintah CLI (admin)

```bash
# Diagnosis tenant & user
php artisan tenant:diagnose --email=admin@example.com

# Perbaiki tenant_id konten legacy
php artisan tenant:repair

# Bootstrap pertama deploy
php artisan opswiki:bootstrap
```

---

## 21. Tips & Praktik Terbaik

### Organisasi konten

1. **Gunakan Projects** sebagai entry point per aplikasi
2. **Quick create** dari project untuk dokumentasi ter-link otomatis
3. **Books** untuk manual/panduan panjang yang berurutan
4. **Snippets** untuk perintah yang sering dipakai ulang
5. **Inbox** untuk tangkap cepat, konversi saat sudah matang

### Workflow dokumentasi

```
Inbox (draft cepat)
    ↓ Convert
Wiki / SOP / Troubleshooting
    ↓ Link
Project + Knowledge Graph
    ↓ Share (optional)
Portal Publik / Share Link
```

### Status & review

- Mulai dengan **Draft** → **Review** → **Tested** → **Production**
- Tandai dokumentasi lama sebagai **Deprecated** sebelum **Archived**
- Gunakan **version history** wiki sebelum edit besar

### Keamanan

- Default visibility: **Private**
- Set **Public** hanya untuk konten yang memang untuk audience eksternal
- Review portal publik secara berkala di Settings → Workspace

---

## 22. Referensi Cepat

### URL penting

| Path | Fungsi |
|------|--------|
| `/` | Halaman utama / marketing |
| `/login` | Login |
| `/register` | Registrasi workspace baru |
| `/dashboard` | Dashboard |
| `/wiki` | Daftar halaman wiki |
| `/wiki/create` | Buat halaman wiki |
| `/wiki/import` | Import MD/HTML ke book |
| `/books` | Rak buku |
| `/books/create` | Buat book baru |
| `/search` | Pencarian global |
| `/portal` | Portal publik |
| `/share/pages/{slug}` | Share link halaman |
| `/share/books/{slug}` | Share link book |
| `/settings` | Pengaturan |
| `/platform` | Admin platform |

### Kontak & legal

- Terms of Service: `/legal/terms`
- Privacy Policy: `/legal/privacy`
- Acceptable Use: `/legal/acceptable-use`

---

*Dokumentasi ini dibuat untuk OpsWiki. Untuk panduan deploy teknis, lihat [docker/DEPLOY-COOLIFY.md](../docker/DEPLOY-COOLIFY.md) dan [README.md](../README.md).*
