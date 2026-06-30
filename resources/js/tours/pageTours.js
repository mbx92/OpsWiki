/** @typedef {{ element?: string, popover: { title: string, description: string, side?: string, align?: string } }} TourStep */

/** @type {Array<{ id: string, label: string, match: (routeName?: string) => boolean, steps: TourStep[] }>} */
export const pageTourCatalog = [
    {
        id: 'dashboard',
        label: 'Dashboard',
        match: (name) => name === 'dashboard',
        steps: [
            {
                popover: {
                    title: 'Dashboard',
                    description: 'Halaman utama workspace. Lihat ringkasan aktivitas dan shortcut ke modul yang sering dipakai.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-header"]',
                popover: {
                    title: 'Judul halaman',
                    description: 'Setiap modul punya header sendiri. Di Dashboard, ini menandai Anda berada di ringkasan workspace.',
                    side: 'bottom',
                    align: 'start',
                },
            },
            {
                element: '[data-tour="dashboard-stats"]',
                popover: {
                    title: 'Statistik',
                    description: 'Jumlah inbox baru, halaman Wiki, snippet, dan favorit — snapshot cepat kondisi dokumentasi tim.',
                    side: 'bottom',
                    align: 'start',
                },
            },
            {
                element: '[data-tour="dashboard-quick-actions"]',
                popover: {
                    title: 'Aksi cepat',
                    description: 'Buat note, halaman Wiki, snippet, atau buka Tools tanpa navigasi sidebar.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'inbox',
        label: 'Inbox',
        match: (name) => Boolean(name?.startsWith('inbox.')),
        steps: [
            {
                popover: {
                    title: 'Inbox — tangkap dulu, rapikan nanti',
                    description: 'Gunakan Inbox untuk menyimpan ide, error log, perintah, atau draft sebelum hilang. Nanti bisa dikonversi ke Wiki, Snippet, SOP, atau Troubleshooting.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Buat catatan',
                    description: 'Klik <strong>+ New Note</strong> untuk menambah item. Pilih tipe (idea, error log, command, dll.) dan prioritas.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-filters"]',
                popover: {
                    title: 'Filter status',
                    description: 'Saring berdasarkan status: new, reviewed, converted, archived. Berguna saat inbox mulai penuh.',
                    side: 'bottom',
                    align: 'start',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Daftar catatan',
                    description: 'Klik item untuk melihat detail dan opsi konversi ke modul lain. Inbox = staging area, bukan dokumentasi final.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'wiki',
        label: 'Wiki',
        match: (name) => Boolean(name?.startsWith('wiki.')),
        steps: [
            {
                popover: {
                    title: 'Wiki — dokumentasi teknis',
                    description: 'Modul utama untuk dokumentasi tim: halaman Markdown, versi, sharing, dan export. Bisa standalone atau dalam Books.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Buat halaman',
                    description: 'Klik <strong>+ New Page</strong> atau tombol <strong>+ Page</strong> di header atas. Tulis dengan Markdown.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-filters"]',
                popover: {
                    title: 'Cari & filter',
                    description: 'Filter scope (All / Standalone / In book) dan cari judul halaman. Gunakan pencarian global (header) untuk lintas modul.',
                    side: 'bottom',
                    align: 'start',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Konten Wiki',
                    description: 'Di halaman detail: edit, riwayat versi, bagikan publik, export, dan hubungkan ke item terkait.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'books',
        label: 'Books',
        match: (name) => Boolean(name?.startsWith('books.')),
        steps: [
            {
                popover: {
                    title: 'Books — kelompokkan Wiki',
                    description: 'Books mengorganisir halaman Wiki seperti buku digital: bab, urutan, dan opsi sharing per book.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Kelola buku',
                    description: 'Buat book, attach halaman Wiki, atur urutan, export, dan visibility (internal/publik sesuai plan).',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'snippets',
        label: 'Snippets',
        match: (name) => Boolean(name?.startsWith('snippets.')),
        steps: [
            {
                popover: {
                    title: 'Snippets — perintah siap pakai',
                    description: 'Simpan perintah shell, skrip, atau potongan kode. Salin cepat, tandai favorit, filter per platform/bahasa.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Buat snippet',
                    description: 'Tambahkan judul, perintah, bahasa, dan platform. Snippet favorit muncul di Dashboard.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Daftar snippet',
                    description: 'Gunakan tombol copy, favorite, dan edit. Snippet dari Tools juga bisa disimpan ke sini.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'sops',
        label: 'SOPs',
        match: (name) => Boolean(name?.startsWith('sops.')),
        steps: [
            {
                popover: {
                    title: 'SOPs — prosedur operasional',
                    description: 'Dokumentasikan langkah standar: purpose, requirements, steps, validation, rollback. Import dari Markdown didukung.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Buat / import SOP',
                    description: 'Buat manual atau import dari file Markdown. Inbox item bertipe draft_sop bisa dikonversi ke SOP.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Kelola SOP',
                    description: 'Setiap SOP punya slug URL sendiri. Edit section per section agar mudah dirawat tim ops.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'troubleshooting',
        label: 'Troubleshooting',
        match: (name) => Boolean(name?.startsWith('troubleshooting.')),
        steps: [
            {
                popover: {
                    title: 'Troubleshooting — kasus & solusi',
                    description: 'Catat gejala, environment, diagnosis, solusi yang berhasil/gagal, dan pencegahan. Ideal untuk incident post-mortem.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Buat / import kasus',
                    description: 'Buat kasus baru atau import Markdown. Error log dari Inbox bisa dikonversi ke sini.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Daftar kasus',
                    description: 'Filter severity dan status. Kasus terdokumentasi mengurangi waktu debug insiden berulang.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'projects-show',
        label: 'Project hub',
        match: (name) => name === 'projects.show',
        steps: [
            {
                popover: {
                    title: 'Pusat dokumentasi proyek',
                    description: 'Setiap proyek adalah hub untuk semua dokumentasi sistem: Wiki, SOP, snippet, dan troubleshooting — plus catatan infrastruktur.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="project-doc-stats"]',
                popover: {
                    title: 'Ringkasan dokumentasi',
                    description: 'Jumlah total dokumen per tipe — tanpa card terpisah, cukup satu baris ringkas di header.',
                    side: 'bottom',
                    align: 'start',
                },
            },
            {
                element: '[data-page-tour="project-quick-create"]',
                popover: {
                    title: 'Quick create',
                    description: 'Buat Wiki overview, SOP deploy, snippet, atau case troubleshooting — otomatis ditautkan ke proyek ini.',
                    side: 'left',
                    align: 'start',
                },
            },
            {
                element: '[data-page-tour="project-doc-groups"]',
                popover: {
                    title: 'Dokumentasi terkait',
                    description: 'Semua konten yang ditautkan ke proyek, dikelompokkan per modul. Klik untuk membuka.',
                    side: 'right',
                    align: 'start',
                },
            },
            {
                element: '[data-page-tour="project-infra-notes"]',
                popover: {
                    title: 'Catatan infrastruktur',
                    description: 'Environment, deployment, database, dan backup — informasi operasional yang tidak perlu jadi halaman Wiki terpisah.',
                    side: 'left',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'projects',
        label: 'Projects',
        match: (name) => Boolean(name?.startsWith('projects.') && name !== 'projects.show'),
        steps: [
            {
                popover: {
                    title: 'Projects — inventaris proyek',
                    description: 'Track proyek internal: status, repo URL, staging/production URL, lokasi server, dan catatan environment.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Tambah proyek',
                    description: 'Buat entry proyek baru agar tim punya single source of truth untuk link dan environment.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Detail proyek',
                    description: 'Buka proyek untuk melihat hub dokumentasi lengkap dan update status lifecycle (planning → production).',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'knowledge',
        label: 'Knowledge Graph',
        match: (name) => Boolean(name?.startsWith('knowledge.')),
        steps: [
            {
                popover: {
                    title: 'Knowledge Graph',
                    description: 'Visualisasi relasi antar halaman Wiki. Berguna melihat hubungan dokumentasi dan menemukan gap.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Graf interaktif',
                    description: 'Klik node untuk navigasi ke halaman terkait. Semakin banyak relasi antar page, semakin berguna graf ini.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'assistant',
        label: 'Assistant',
        match: (name) => Boolean(name?.startsWith('assistant.')),
        steps: [
            {
                popover: {
                    title: 'AI Assistant',
                    description: 'Tanya jawab berbasis konteks workspace (Wiki, snippet, inbox, dll.). Butuh integrasi AI di Settings → Integrations.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Chat assistant',
                    description: 'Ajukan pertanyaan operasional — assistant merujuk ke konten yang ada di workspace Anda.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'tools',
        label: 'Tools',
        match: (name) => Boolean(name?.startsWith('tools.')),
        steps: [
            {
                popover: {
                    title: 'Tools — utilitas built-in',
                    description: 'Generator dan helper untuk DevOps: Docker Compose, rclone, pg_restore, MinIO IAM, dll. Hasil bisa disimpan sebagai snippet.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Pilih tool',
                    description: 'Buka tool yang dibutuhkan, isi form, salin output. Tool yang terkunci membutuhkan upgrade plan.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'assets',
        label: 'Assets',
        match: (name) => Boolean(name?.startsWith('assets.')),
        steps: [
            {
                popover: {
                    title: 'Assets — file & lampiran',
                    description: 'Upload dan kelola file (gambar, dokumen) untuk dilampirkan ke konten Wiki atau modul lain.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-actions"]',
                popover: {
                    title: 'Upload file',
                    description: 'Drag & drop atau pilih file. Gunakan assets di editor Wiki untuk dokumentasi visual.',
                    side: 'bottom',
                    align: 'end',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Perpustakaan file',
                    description: 'Salin URL asset untuk embed di Markdown atau halaman lain.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'search',
        label: 'Search',
        match: (name) => Boolean(name?.startsWith('search.')),
        steps: [
            {
                popover: {
                    title: 'Pencarian lanjutan',
                    description: 'Halaman hasil pencarian full — complement dari kotak search di header.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Hasil pencarian',
                    description: 'Hasil dikelompokkan per tipe: Wiki, snippet, inbox, SOP, troubleshooting, projects.',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'settings',
        label: 'Settings',
        match: (name) => Boolean(name?.startsWith('settings.') || name?.startsWith('profile.')),
        steps: [
            {
                popover: {
                    title: 'Settings — konfigurasi workspace',
                    description: 'Kelola profil, workspace, billing, kategori, tag, user/role, integrasi, dan activity log.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Menu pengaturan',
                    description: 'Pilih kartu sesuai kebutuhan. Integrasi: MinIO (archive), GlitchTip (error tracking), AI (assistant).',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
    {
        id: 'upgrade',
        label: 'Upgrade',
        match: (name) => Boolean(name?.startsWith('upgrade.')),
        steps: [
            {
                popover: {
                    title: 'Upgrade plan',
                    description: 'Beberapa modul (Books, SOP, Tools, Assistant, dll.) membutuhkan plan Pro/Team. Lihat fitur dan limit di sini.',
                    side: 'over',
                    align: 'center',
                },
            },
            {
                element: '[data-page-tour="page-content"]',
                popover: {
                    title: 'Detail paket',
                    description: 'Bandingkan fitur plan dan upgrade workspace untuk membuka modul yang terkunci (badge di sidebar).',
                    side: 'top',
                    align: 'start',
                },
            },
        ],
    },
];

const fallbackTour = {
    id: 'general',
    label: 'OpsWiki',
    steps: [
        {
            popover: {
                title: 'Panduan halaman ini',
                description: 'Gunakan sidebar untuk berpindah modul. Tombol <strong>+ Page</strong> / <strong>+ Note</strong> di header untuk buat konten cepat. Pencarian global ada di tengah header.',
                side: 'over',
                align: 'center',
            },
        },
        {
            element: '[data-tour="sidebar"]',
            popover: {
                title: 'Sidebar',
                description: 'Navigasi utama ke semua modul workspace.',
                side: 'right',
                align: 'start',
            },
        },
        {
            element: '[data-tour="search"]',
            popover: {
                title: 'Pencarian',
                description: 'Cari konten lintas modul dari satu tempat.',
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '[data-page-tour="page-content"]',
            popover: {
                title: 'Area konten',
                description: 'Konten halaman aktif ditampilkan di sini.',
                side: 'top',
                align: 'start',
            },
        },
    ],
};

export function resolvePageTour(routeName) {
    return pageTourCatalog.find((tour) => tour.match(routeName)) ?? fallbackTour;
}
