<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Sop;
use App\Models\TroubleshootingCase;
use App\Models\User;
use App\Services\MarkdownService;
use App\Services\SopImportService;
use App\Services\TroubleshootingImportService;
use Illuminate\Database\Seeder;

class DocumentationSampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@wiki.com')->first()
            ?? User::query()->first();

        if (! $user) {
            $this->command?->warn('No user found. Run UserSeeder first.');

            return;
        }

        $markdown = app(MarkdownService::class);
        $sopImport = app(SopImportService::class);
        $tsImport = app(TroubleshootingImportService::class);

        $infraCategory = Category::where('slug', 'infrastructure')->value('id')
            ?? Category::query()->value('id');

        $this->seedWikiPages($user, $markdown, $infraCategory);
        $this->seedSopFromMarkdown($user, $sopImport);
        $this->seedTroubleshootingFromMarkdown($user, $tsImport);
    }

    private function seedWikiPages(User $user, MarkdownService $markdown, ?int $categoryId): void
    {
        $pages = [
            'proxmox-backup-overview' => [
                'title' => 'Proxmox — Backup VM Overview',
                'summary' => 'Strategi backup VM Proxmox dengan vzdump dan offsite sync.',
                'content' => <<<'MD'
## Ringkasan

Dokumen ini menjelaskan pola backup VM di cluster Proxmox: snapshot lokal, retention, dan replikasi offsite.

## Metode backup

| Metode | Kelebihan | Kekurangan |
|--------|-----------|------------|
| vzdump (local) | Cepat, native | Single point of failure |
| PBS (Proxmox Backup Server) | Dedup, incremental | Perlu infra tambahan |
| rclone offsite | Murah, fleksibel | Manual restore |

## Retention yang disarankan

- **Harian:** 7 hari (local)
- **Mingguan:** 4 minggu (offsite)
- **Bulanan:** 3 bulan (archive)

## Langkah berikutnya

Lihat SOP **Backup VM Proxmox via vzdump** untuk prosedur operasional.
MD,
            ],
            'docker-networking-basics' => [
                'title' => 'Docker — Networking Basics',
                'summary' => 'Bridge, host, dan custom network untuk stack multi-container.',
                'content' => <<<'MD'
## Ringkasan

Memahami network mode Docker sebelum deploy stack production.

## Network modes

### bridge (default)

Container terisolasi, expose port via `-p`.

### host

Container share network namespace host — performa tinggi, isolasi rendah.

### custom bridge

Gunakan untuk komunikasi antar service tanpa expose ke host.

```bash
docker network create app_net
docker run --network app_net --name api myapi:latest
docker run --network app_net --name db postgres:16
```

## Best practice

- Satu network per aplikasi/stack
- Jangan pakai `host` kecuali benar-benar perlu
- Dokumentasikan port mapping di wiki project
MD,
            ],
            'postgresql-backup-restore' => [
                'title' => 'PostgreSQL — Backup & Restore',
                'summary' => 'pg_dump harian dan prosedur restore ke staging.',
                'content' => <<<'MD'
## Ringkasan

Backup logical dengan `pg_dump` dan restore ke environment staging untuk validasi.

## Backup harian

```bash
pg_dump -Fc -h localhost -U postgres mydb > backup_$(date +%F).dump
```

## Restore ke staging

```bash
pg_restore -h staging-db -U postgres -d mydb_staging --clean backup.dump
```

## Validasi

1. Cek jumlah row tabel kritis
2. Jalankan smoke test aplikasi
3. Update status dokumen ke `tested` setelah verifikasi
MD,
            ],
        ];

        foreach ($pages as $slug => $data) {
            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'content_markdown' => trim($data['content']),
                    'content_html' => $markdown->toHtml(trim($data['content'])),
                    'category_id' => $categoryId,
                    'status' => 'production',
                    'visibility' => 'internal',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'published_at' => now()->subDays(7),
                ],
            );
        }
    }

    private function seedSopFromMarkdown(User $user, SopImportService $import): void
    {
        $content = <<<'MD'
# SOP: Backup VM Proxmox via vzdump

## Purpose

Standarisasi backup VM production di Proxmox dengan vzdump dan verifikasi restore.

## Requirements

- Akses SSH ke node Proxmox
- Storage backup (`local` atau NFS) dengan ruang cukup
- Window maintenance yang disetujui

## Steps

1. Identifikasi VM ID: `qm list`
2. Jalankan backup:

```bash
vzdump <VMID> --storage backup-nfs --mode snapshot --compress zstd
```

3. Cek file backup di storage target
4. Catat timestamp dan ukuran file di log operasional
5. Sync offsite jika diperlukan (rclone/S3)

## Validation

- File `.vma.zst` atau `.tar` ada di storage
- Restore uji ke VM test berhasil (quarterly)

## Rollback

Hapus backup corrupt dan ulangi vzdump. Jangan hapus backup terakhir sebelum backup baru sukses.

## Notes

Jadwalkan via Proxmox cron atau external scheduler. Lihat juga dokumentasi PostgreSQL backup untuk database layer.
MD;

        $parsed = $import->parseContent($content, 'Backup VM Proxmox via vzdump');
        $sections = $parsed['sections'];

        Sop::updateOrCreate(
            ['slug' => 'sop-proxmox-vm-backup'],
            [
                'title' => $parsed['title'],
                'purpose' => $sections['purpose'] ?? null,
                'requirements' => $sections['requirements'] ?? null,
                'steps' => $sections['steps'] ?? null,
                'validation' => $sections['validation'] ?? null,
                'rollback' => $sections['rollback'] ?? null,
                'notes' => $sections['notes'] ?? null,
                'status' => 'production',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        );
    }

    private function seedTroubleshootingFromMarkdown(User $user, TroubleshootingImportService $import): void
    {
        $content = <<<'MD'
# Troubleshooting: PostgreSQL connection refused

## Symptoms

Aplikasi Laravel gagal konek ke PostgreSQL dengan error `connection refused` setelah deploy atau restart container.

## Environment

Docker Compose, PostgreSQL 16, network `app_net`, host `db`

## Error log

```
SQLSTATE[08006] connection to server at "db" (172.18.0.3), port 5432 failed: Connection refused
```

## Suspected causes

- Container `db` belum ready saat app start
- Port mapping salah
- PostgreSQL belum listen di interface Docker

## Diagnosis steps

1. `docker compose ps` — pastikan db healthy
2. `docker compose logs db` — cek startup selesai
3. Dari container app: `nc -zv db 5432`
4. Cek `DATABASE_URL` / `.env`

## Working solution

Tambahkan `depends_on` dengan healthcheck dan retry di entrypoint app:

```yaml
depends_on:
  db:
    condition: service_healthy
```

Restart stack: `docker compose up -d`

## Prevention

- Gunakan healthcheck di service db
- Jangan hardcode IP container
- Dokumentasikan urutan startup di wiki project
MD;

        $parsed = $import->parseContent($content, 'PostgreSQL connection refused');
        $sections = $parsed['sections'];

        TroubleshootingCase::updateOrCreate(
            ['slug' => 'postgresql-connection-refused'],
            [
                'title' => $parsed['title'],
                'symptoms' => $sections['symptoms'] ?? null,
                'environment' => $sections['environment'] ?? null,
                'error_log' => $sections['error_log'] ?? null,
                'suspected_causes' => $sections['suspected_causes'] ?? null,
                'diagnosis_steps' => $sections['diagnosis_steps'] ?? null,
                'working_solution' => $sections['working_solution'] ?? null,
                'prevention' => $sections['prevention'] ?? null,
                'severity' => 'high',
                'status' => 'solved',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        );
    }
}
