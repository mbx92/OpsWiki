<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageRelation;
use App\Models\Project;
use App\Models\Snippet;
use App\Models\Sop;
use App\Models\TroubleshootingCase;
use App\Models\User;
use Illuminate\Database\Seeder;

class KnowledgeGraphDemoSeeder extends Seeder
{
    /** @var array<string, Page> */
    private array $pages = [];

    /** @var array<string, Sop> */
    private array $sops = [];

    /** @var array<string, TroubleshootingCase> */
    private array $cases = [];

    /** @var array<string, Project> */
    private array $projects = [];

    /** @var array<string, Snippet> */
    private array $snippets = [];

    public function run(): void
    {
        $user = User::where('email', 'admin@wiki.com')->first()
            ?? User::query()->first();

        if (! $user) {
            $this->command?->warn('No user found. Run UserSeeder first.');

            return;
        }

        $this->cleanupOldBulkDemo();

        $minioCategory = Category::where('slug', 'minio')->value('id');

        $this->pages['minio-iam-bucket-baru'] = Page::updateOrCreate(
            ['slug' => 'minio-iam-bucket-baru'],
            [
                'title' => 'Dokumentasi MinIO IAM untuk Bucket Baru',
                'summary' => 'Panduan lengkap membuat bucket, user, dan IAM policy MinIO dengan mc CLI.',
                'content_markdown' => $this->minioIamDoc(),
                'category_id' => $minioCategory,
                'status' => 'production',
                'visibility' => 'internal',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'published_at' => now()->subDays(14),
            ],
        );

        $this->pages['minio-single-node'] = Page::updateOrCreate(
            ['slug' => 'minio-single-node'],
            [
                'title' => 'MinIO Single Node — Instalasi Docker',
                'summary' => 'Deploy MinIO sebagai object storage single-node dengan Docker Compose.',
                'content_markdown' => $this->minioInstallDoc(),
                'category_id' => $minioCategory,
                'status' => 'production',
                'visibility' => 'internal',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'published_at' => now()->subDays(30),
            ],
        );

        $this->pages['minio-client-alias'] = Page::updateOrCreate(
            ['slug' => 'minio-client-alias'],
            [
                'title' => 'MinIO Client (mc) — Setup Alias',
                'summary' => 'Konfigurasi mc alias agar perintah IAM dan bucket bisa dijalankan dari workstation.',
                'content_markdown' => $this->mcAliasDoc(),
                'category_id' => $minioCategory,
                'status' => 'production',
                'visibility' => 'internal',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'published_at' => now()->subDays(21),
            ],
        );

        $this->sops['sop-minio-bucket-iam'] = Sop::updateOrCreate(
            ['slug' => 'sop-minio-bucket-iam'],
            [
                'title' => 'SOP: Buat Bucket & IAM User MinIO',
                'purpose' => 'Standarisasi pembuatan bucket baru beserta user dan policy IAM di MinIO production.',
                'steps' => "## Langkah\n\n1. Verifikasi alias `mc` sudah terkonfigurasi\n2. Buat bucket dengan naming convention\n3. Buat user service account\n4. Buat dan attach IAM policy\n5. Uji akses read/write\n6. Catat credential di password manager",
                'validation' => 'User bisa `mc ls alias/bucket` dan upload file uji via aplikasi.',
                'rollback' => 'Detach policy, hapus user, lalu hapus bucket jika belum dipakai produksi.',
                'status' => 'production',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        );

        $this->cases['minio-access-denied'] = TroubleshootingCase::updateOrCreate(
            ['slug' => 'minio-access-denied'],
            [
                'title' => 'MinIO Access Denied (403)',
                'symptoms' => 'Aplikasi atau mc CLI gagal akses bucket dengan error `Access Denied` meskipun credential benar.',
                'environment' => 'MinIO single-node, Docker, mc alias production',
                'working_solution' => "1. Cek policy user: `mc admin policy info alias policy-name`\n2. Pastikan Resource ARN mencakup bucket dan `bucket/*`\n3. Re-attach policy: `mc admin policy attach alias policy-name --user username`\n4. Ulangi upload uji",
                'prevention' => 'Gunakan template policy dari dokumentasi IAM. Jangan edit manual tanpa validasi JSON.',
                'severity' => 'medium',
                'status' => 'solved',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        );

        $this->projects['backup-platform'] = Project::updateOrCreate(
            ['slug' => 'backup-platform'],
            [
                'name' => 'Backup Platform',
                'description' => 'Stack backup: Proxmox Backup Server + MinIO offsite + rclone sync.',
                'status' => 'production',
                'server_location' => 'Homelab + VPS',
                'created_by' => $user->id,
            ],
        );

        $this->snippets['mc-create-bucket'] = Snippet::updateOrCreate(
            ['title' => 'MinIO: buat bucket'],
            [
                'description' => 'Membuat bucket baru via mc CLI',
                'command' => 'mc mb myminio/my-bucket-name',
                'language' => 'bash',
                'platform' => 'minio',
                'category_id' => $minioCategory,
                'is_tested' => true,
                'created_by' => $user->id,
            ],
        );

        $this->snippets['mc-attach-policy'] = Snippet::updateOrCreate(
            ['title' => 'MinIO: attach policy ke user'],
            [
                'description' => 'Attach IAM policy ke user setelah policy dibuat',
                'command' => 'mc admin policy attach myminio mybucket-policy --user myuser',
                'language' => 'bash',
                'platform' => 'minio',
                'category_id' => $minioCategory,
                'is_tested' => true,
                'created_by' => $user->id,
            ],
        );

        foreach ($this->pages as $page) {
            $page->syncTags(['minio', 'iam', 'production']);
        }

        $this->seedRelations();

        $this->command?->info('Documentation demo seeded: '.count($this->allNodes()).' nodes, '.PageRelation::query()->count().' relations.');
    }

    private function cleanupOldBulkDemo(): void
    {
        $oldPageSlugs = [
            'infrastructure-overview', 'proxmox-cluster-setup', 'proxmox-backup-server',
            'proxmox-vm-templates', 'docker-compose-basics', 'docker-swarm-notes',
            'minio-replication', 'postgresql-backup-restore', 'postgresql-tuning',
            'cloudflare-tunnel-setup', 'tailscale-mesh-vpn', 'synology-iscsi-lun',
            'mikrotik-vlan-segmentation', 'nginx-reverse-proxy', 'coolify-deployment',
            'rclone-minio-sync', 'windows-wsl-dev', 'macos-local-dev', 'monitoring-stack',
            'ssl-certificates', 'disaster-recovery-plan', 'laravel-opswiki-stack',
            'security-hardening', 'log-aggregation',
        ];

        Page::whereIn('slug', $oldPageSlugs)->delete();

        Sop::whereIn('slug', [
            'sop-vm-backup-daily', 'sop-vm-restore', 'sop-deploy-docker-app',
            'sop-minio-bucket-create', 'sop-postgres-point-in-time', 'sop-cloudflare-dns-update',
            'sop-tailscale-acl-change', 'sop-certificate-renewal', 'sop-incident-response',
            'sop-onboard-new-server', 'sop-database-migration', 'sop-offsite-backup-verify',
        ])->delete();

        TroubleshootingCase::whereIn('slug', [
            'proxmox-vm-wont-start', 'docker-container-oom', 'postgres-connection-refused',
            'cloudflare-tunnel-502', 'tailscale-subnet-unreachable', 'iscsi-lun-disconnected',
            'nginx-502-upstream', 'disk-space-full-proxmox', 'laravel-queue-stuck',
            'ssl-cert-expired', 'backup-job-failed-pbs',
        ])->delete();

        Project::whereIn('slug', [
            'opswiki', 'homelab-core', 'internal-tools', 'monitoring',
            'client-portal', 'nas-archive', 'network-lab', 'ai-assistant-poc', 'static-docs-export',
        ])->where('slug', '!=', 'backup-platform')->delete();

        Snippet::whereIn('title', [
            'Docker prune all', 'Proxmox list VMs', 'MinIO client ls',
            'PostgreSQL connect', 'rclone sync to MinIO', 'Tailscale status',
            'Cloudflare tunnel run', 'Check disk usage', 'Laravel queue restart',
            'Nginx test config', 'pg_dump compressed', 'Proxmox backup VM',
            'Docker compose logs', 'MikroTik export config', 'Find large files',
        ])->delete();
    }

    private function seedRelations(): void
    {
        PageRelation::query()->delete();

        $links = [
            ['pages', 'minio-iam-bucket-baru', 'pages', 'minio-single-node'],
            ['pages', 'minio-iam-bucket-baru', 'pages', 'minio-client-alias'],
            ['pages', 'minio-iam-bucket-baru', 'sops', 'sop-minio-bucket-iam'],
            ['pages', 'minio-iam-bucket-baru', 'troubleshooting_cases', 'minio-access-denied'],
            ['pages', 'minio-iam-bucket-baru', 'projects', 'backup-platform'],
            ['pages', 'minio-iam-bucket-baru', 'snippets', 'mc-create-bucket'],
            ['pages', 'minio-iam-bucket-baru', 'snippets', 'mc-attach-policy'],
            ['pages', 'minio-single-node', 'pages', 'minio-client-alias'],
            ['pages', 'minio-client-alias', 'sops', 'sop-minio-bucket-iam'],
            ['sops', 'sop-minio-bucket-iam', 'troubleshooting_cases', 'minio-access-denied'],
            ['projects', 'backup-platform', 'pages', 'minio-single-node'],
            ['projects', 'backup-platform', 'pages', 'minio-iam-bucket-baru'],
        ];

        foreach ($links as [$sourceType, $sourceKey, $targetType, $targetKey]) {
            $sourceId = $this->resolveId($sourceType, $sourceKey);
            $targetId = $this->resolveId($targetType, $targetKey);

            if (! $sourceId || ! $targetId) {
                continue;
            }

            PageRelation::create([
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'relation_type' => 'related',
            ]);
        }
    }

    /** @return list<object> */
    private function allNodes(): array
    {
        return [
            ...array_values($this->pages),
            ...array_values($this->sops),
            ...array_values($this->cases),
            ...array_values($this->projects),
            ...array_values($this->snippets),
        ];
    }

    private function resolveId(string $type, string $key): ?int
    {
        return match ($type) {
            PageRelation::TYPE_PAGES => $this->pages[$key]->id ?? null,
            PageRelation::TYPE_SOPS => $this->sops[$key]->id ?? null,
            PageRelation::TYPE_TROUBLESHOOTING => $this->cases[$key]->id ?? null,
            PageRelation::TYPE_PROJECTS => $this->projects[$key]->id ?? null,
            PageRelation::TYPE_SNIPPETS => $this->snippets[$key]->id ?? null,
            default => null,
        };
    }

    private function minioIamDoc(): string
    {
        return <<<'MD'
## Ringkasan

Dokumen ini menjelaskan cara membuat **bucket baru** di MinIO beserta **user IAM** dan **policy** akses yang aman. Gunakan alur ini setiap aplikasi atau service membutuhkan bucket dedicated.

**Hasil akhir:**

- Bucket baru dengan naming convention yang konsisten
- Service user (bukan root credential)
- IAM policy scoped ke bucket tersebut saja
- Akses tervalidasi via `mc` CLI

> **Tip:** Gunakan juga tool internal [MinIO IAM Generator](/tools/minio-iam-generator) untuk generate perintah `mc` otomatis.

## Prasyarat

Sebelum mulai, pastikan:

| Item | Keterangan |
|------|------------|
| MinIO server | Sudah running (lihat dokumen instalasi single-node) |
| `mc` CLI | Terinstall di workstation admin |
| Alias `mc` | Sudah dikonfigurasi ke server production |
| Akses admin | User `mc` punya permission `admin` |

```bash
# Cek alias sudah terhubung
mc alias list
mc admin info myminio
```

## Konsep IAM MinIO

MinIO menggunakan model IAM yang kompatibel dengan S3. Tiga komponen utama:

### Bucket

Container object storage. Satu aplikasi = satu bucket (best practice).

Naming convention yang dipakai:

```text
{project}-{environment}-{purpose}
```

Contoh: `opswiki-prod-assets`, `backup-prod-offsite`

### User

Identitas service account. **Jangan** memakai root `MINIO_ROOT_USER` untuk aplikasi.

### Policy

Dokumen JSON yang mendefinisikan action (`s3:GetObject`, `s3:PutObject`, dll.) dan resource ARN bucket.

## Langkah-langkah

### 1. Buat bucket

```bash
mc mb myminio/opswiki-prod-assets
mc ls myminio
```

Verifikasi bucket muncul di daftar.

### 2. Buat user service

```bash
mc admin user add myminio opswiki-app 'GANTI_DENGAN_PASSWORD_KUAT'
```

Simpan password di password manager. Jangan commit ke git.

### 3. Buat IAM policy

Buat file `opswiki-assets-policy.json`:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:GetBucketLocation",
        "s3:ListBucket",
        "s3:GetObject",
        "s3:PutObject",
        "s3:DeleteObject"
      ],
      "Resource": [
        "arn:aws:s3:::opswiki-prod-assets",
        "arn:aws:s3:::opswiki-prod-assets/*"
      ]
    }
  ]
}
```

Register policy ke MinIO:

```bash
mc admin policy create myminio opswiki-assets-policy opswiki-assets-policy.json
```

### 4. Attach policy ke user

```bash
mc admin policy attach myminio opswiki-assets-policy --user opswiki-app
```

### 5. Variasi read-only

Untuk bucket backup atau archive, ganti action menjadi:

```json
"Action": ["s3:GetBucketLocation", "s3:ListBucket", "s3:GetObject"]
```

## Validasi

Jalankan checklist berikut:

1. **List bucket** — user bisa melihat bucket target
2. **Upload** — `mc cp test.txt myminio/opswiki-prod-assets/`
3. **Download** — `mc cat myminio/opswiki-prod-assets/test.txt`
4. **Isolasi** — user **tidak** bisa akses bucket lain

```bash
# Uji sebagai user service (set alias sementara)
mc alias set opswiki-test https://minio.example.com opswiki-app 'PASSWORD'
mc ls opswiki-test/opswiki-prod-assets
```

## Troubleshooting

| Gejala | Penyebab umum | Solusi |
|--------|---------------|--------|
| `Access Denied` | Policy belum di-attach | `mc admin policy attach ...` |
| `Access Denied` | Resource ARN salah | Pastikan ada `bucket` dan `bucket/*` |
| User tidak muncul | Typo username | `mc admin user list myminio` |
| Bucket sudah ada | Nama bentrok | Pilih nama baru atau cek ownership |

Lihat juga kasus troubleshooting **MinIO Access Denied (403)** yang terhubung dari halaman ini.

## Referensi

- [MinIO Client (mc) — Setup Alias](/wiki/minio-client-alias)
- [MinIO Single Node — Instalasi Docker](/wiki/minio-single-node)
- Tool: [MinIO IAM Generator](/tools/minio-iam-generator)
- SOP terkait: **Buat Bucket & IAM User MinIO**
MD;
    }

    private function minioInstallDoc(): string
    {
        return <<<'MD'
## Ringkasan

Deploy MinIO sebagai object storage single-node menggunakan Docker Compose. Dokumen ini menjadi dasar sebelum konfigurasi IAM bucket.

## Docker Compose

```yaml
services:
  minio:
    image: minio/minio:latest
    command: server /data --console-address ":9001"
    ports:
      - "9000:9000"
      - "9001:9001"
    environment:
      MINIO_ROOT_USER: admin
      MINIO_ROOT_PASSWORD: GANTI_PASSWORD_KUAT
    volumes:
      - minio_data:/data

volumes:
  minio_data:
```

## Setelah deploy

1. Buka Console di port `9001`
2. Login dengan root credential
3. Lanjut ke [Setup Alias mc](/wiki/minio-client-alias)
4. Lanjut ke [Dokumentasi IAM Bucket Baru](/wiki/minio-iam-bucket-baru)
MD;
    }

    private function mcAliasDoc(): string
    {
        return <<<'MD'
## Ringkasan

Alias `mc` adalah profil koneksi ke server MinIO. Semua perintah IAM dan bucket membutuhkan alias ini.

## Tambah alias

```bash
mc alias set myminio https://minio.example.com ACCESS_KEY SECRET_KEY
```

## Verifikasi

```bash
mc admin info myminio
mc ls myminio
```

## Langkah berikutnya

Setelah alias aktif, ikuti [Dokumentasi MinIO IAM untuk Bucket Baru](/wiki/minio-iam-bucket-baru).
MD;
    }
}
