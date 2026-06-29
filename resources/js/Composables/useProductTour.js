import { runTour } from '@/tours/driverConfig';
import { usePage } from '@inertiajs/vue3';

const STORAGE_PREFIX = 'opswiki-tour-completed';

function storageKey(userId) {
    return `${STORAGE_PREFIX}-${userId ?? 'guest'}`;
}

export function hasCompletedTour(userId) {
    if (typeof window === 'undefined') {
        return true;
    }

    return window.localStorage.getItem(storageKey(userId)) === '1';
}

export function markTourCompleted(userId) {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.setItem(storageKey(userId), '1');
}

export function resetTour(userId) {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.removeItem(storageKey(userId));
}

function elementExists(selector) {
    if (!selector) {
        return true;
    }

    return Boolean(document.querySelector(selector));
}

function buildSteps() {
    const steps = [
        {
            popover: {
                title: 'Selamat datang di OpsWiki',
                description: 'Panduan singkat ini membantu Anda memahami alur kerja: tangkap catatan → organisasi → dokumentasi tim. Klik <strong>Lanjut</strong> untuk mulai.',
                side: 'over',
                align: 'center',
            },
        },
        {
            element: '[data-tour="sidebar"]',
            popover: {
                title: 'Navigasi utama',
                description: 'Semua modul ada di sidebar: Inbox, Wiki, Snippet, SOP, Troubleshooting, dan lainnya. Menu menyesuaikan role dan paket workspace Anda.',
                side: 'right',
                align: 'start',
            },
        },
        {
            element: '[data-tour="nav-inbox"]',
            popover: {
                title: 'Inbox — tangkap dulu',
                description: 'Simpan ide, error log, perintah shell, atau draft SOP sebelum hilang. Nanti bisa dikonversi ke Wiki, Snippet, SOP, atau kasus Troubleshooting.',
                side: 'right',
                align: 'start',
            },
        },
        {
            element: '[data-tour="nav-wiki"]',
            popover: {
                title: 'Wiki — dokumentasi tim',
                description: 'Buat halaman dokumentasi teknis dengan Markdown. Kelompokkan dalam Books, atur kategori, dan bagikan ke tim.',
                side: 'right',
                align: 'start',
            },
        },
        {
            element: '[data-tour="nav-snippets"]',
            popover: {
                title: 'Snippets — perintah siap pakai',
                description: 'Simpan perintah CLI, skrip, atau potongan kode yang sering dipakai. Tandai favorit untuk akses cepat dari dashboard.',
                side: 'right',
                align: 'start',
            },
        },
        {
            element: '[data-tour="search"]',
            popover: {
                title: 'Pencarian global',
                description: 'Cari halaman Wiki, snippet, inbox, SOP, troubleshooting, dan proyek dari satu kotak pencarian. Ketik minimal 2 karakter.',
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '[data-tour="quick-create"]',
            popover: {
                title: 'Buat konten cepat',
                description: '<strong>+ Note</strong> untuk catatan inbox. <strong>+ Page</strong> untuk halaman Wiki baru. Gunakan kapan saja dari header.',
                side: 'bottom',
                align: 'end',
            },
        },
        {
            element: '[data-tour="dashboard-stats"]',
            popover: {
                title: 'Ringkasan dashboard',
                description: 'Lihat jumlah inbox baru, halaman Wiki, snippet, dan favorit. Bagian ini hanya tampil di halaman Dashboard.',
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '[data-tour="dashboard-quick-actions"]',
            popover: {
                title: 'Aksi cepat',
                description: 'Shortcut untuk membuat note, halaman Wiki, snippet, atau membuka Tools — tanpa lewat sidebar.',
                side: 'top',
                align: 'start',
            },
        },
        {
            element: '[data-tour="nav-settings"]',
            popover: {
                title: 'Pengaturan workspace',
                description: 'Kelola kategori, tag, integrasi (MinIO, GlitchTip, AI), billing, dan anggota tim di Settings.',
                side: 'right',
                align: 'end',
            },
        },
        {
            element: '[data-page-tour="page-help"]',
            popover: {
                title: 'Profil & panduan ulang',
                description: 'Ubah profil di menu avatar. Klik tombol <strong>?</strong> di header untuk panduan halaman/modul yang sedang dibuka.',
                side: 'bottom',
                align: 'end',
            },
        },
    ];

    return steps.filter((step) => elementExists(step.element));
}

export function useProductTour() {
    const page = usePage();
    const userId = page.props.auth?.user?.id;

    const startTour = ({ force = false } = {}) => {
        if (!force && hasCompletedTour(userId)) {
            return;
        }

        runTour(buildSteps(), {
            onComplete: () => markTourCompleted(userId),
        });
    };

    const restartTour = () => {
        resetTour(userId);
        startTour({ force: true });
    };

    return {
        startTour,
        restartTour,
        hasCompletedTour: () => hasCompletedTour(userId),
    };
}

export function dispatchProductTour({ restart = false } = {}) {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(new CustomEvent('opswiki:start-tour', { detail: { restart } }));
}
