export const importMarkdownExamples = {
    sop: {
        filename: 'sop-deploy-contoh.md',
        label: 'SOP Deploy (contoh)',
        sections: [
            { heading: '## Tujuan', field: 'Purpose' },
            { heading: '## Use case', field: 'Use case' },
            { heading: '## Requirements / Prasyarat', field: 'Requirements' },
            { heading: '## Langkah-langkah / Steps', field: 'Steps' },
            { heading: '## Validasi', field: 'Validation' },
            { heading: '## Rollback / Pemulihan', field: 'Rollback' },
            { heading: '## Catatan / Notes', field: 'Notes' },
        ],
        notes: 'Heading boleh diberi nomor (mis. ## 1. Tujuan). Judul dari baris # pertama. Section tidak dikenali digabung ke Steps.',
    },
    troubleshooting: {
        filename: 'troubleshooting-mail-server-contoh.md',
        label: 'Mail server VLAN (contoh)',
        sections: [
            { heading: '## Ringkasan kasus', field: 'Environment' },
            { heading: '## Gejala / Gejala yang terlihat', field: 'Symptoms' },
            { heading: '## Root cause / Penyebab', field: 'Suspected causes' },
            { heading: '## Solusi yang disarankan', field: 'Working solution' },
            { heading: '## Solusi jangka panjang', field: 'Prevention' },
            { heading: '## Checklist verifikasi', field: 'Validation' },
            { heading: '## Kesimpulan akhir', field: 'Working solution' },
        ],
        notes: 'Heading boleh diberi nomor (mis. ## 2. Gejala yang terlihat). Section tidak dikenali digabung ke Working solution.',
    },
};

export function importExampleUrl(type) {
    const example = importMarkdownExamples[type];

    if (!example) {
        return null;
    }

    return `/examples/import/${example.filename}`;
}
