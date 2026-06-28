<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import CopyButton from '@/Components/CopyButton.vue';
import { usePlanFeatures } from '@/Composables/usePlanFeatures';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tenant: Object,
    centralDomain: String,
    subdomainUrl: String,
    portalUrl: String,
    portalCentralUrl: String,
    dnsInstructions: {
        type: Object,
        default: () => ({ cnameTarget: '', aRecordIp: null }),
    },
});

const { hasFeature, limit, usage } = usePlanFeatures();

const form = useForm({ name: props.tenant.name });
const domainForm = useForm({ domain: '' });

const cnameTarget = computed(() => props.dnsInstructions?.cnameTarget || props.centralDomain);
const aRecordIp = computed(() => props.dnsInstructions?.aRecordIp || null);

const dnsHostLabel = (domain) => {
    const parts = domain.split('.');

    if (parts.length <= 2) {
        return '@';
    }

    return parts.slice(0, -2).join('.');
};

const submit = () => form.put(route('settings.workspace.update'));

const addDomain = () => {
    domainForm.post(route('settings.workspace.domains.store'), {
        preserveScroll: true,
        onSuccess: () => domainForm.reset(),
    });
};
</script>

<template>
    <Head title="Workspace" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Workspace</h1>
                </div>
                <FormHeaderActions form-id="workspace-form" :cancel-href="route('settings.index')" save-label="Save" :processing="form.processing" />
            </div>
        </template>

        <form id="workspace-form" @submit.prevent="submit" class="space-y-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div>
                    <InputLabel value="Workspace name" />
                    <input v-model="form.name" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Subdomain" />
                        <p class="mt-1.5 text-[14px] text-[#374151]">{{ tenant.slug }}.{{ centralDomain }}</p>
                        <a :href="subdomainUrl" target="_blank" class="mt-1 inline-block text-[13px] text-[#2563eb] hover:underline">Open workspace URL</a>
                    </div>
                    <div>
                        <InputLabel value="Current plan" />
                        <p class="mt-1.5 text-[14px] font-[600] text-[#111111]">{{ tenant.subscription?.plan?.name ?? 'Free' }}</p>
                        <p v-if="tenant.subscription?.plan?.description" class="mt-1 text-[13px] text-[#6b7280]">{{ tenant.subscription.plan.description }}</p>
                        <p v-if="limit('pages') !== null" class="mt-2 text-[13px] text-[#6b7280]">
                            Pages: {{ usage('pages') }} / {{ limit('pages') }}
                        </p>
                        <p v-if="limit('users') !== null" class="mt-1 text-[13px] text-[#6b7280]">
                            Users: {{ usage('users') }} / {{ limit('users') }}
                        </p>
                        <Link
                            v-if="tenant.subscription?.plan?.slug === 'free' || !tenant.subscription?.plan"
                            :href="route('upgrade.pro')"
                            class="mt-3 inline-block text-[13px] font-[600] text-[#2563eb] hover:underline"
                        >
                            Upgrade to Pro →
                        </Link>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="text-[16px] font-[600] text-[#111111]">Public portal</h2>
            <p class="mt-1 text-[13px] text-[#6b7280]">Halaman index dokumentasi publik workspace — menampilkan books & pages dengan visibility Public.</p>
            <div class="mt-4 space-y-2 text-[14px]">
                <div>
                    <span class="text-[#6b7280]">Portal URL: </span>
                    <a :href="portalUrl" target="_blank" class="font-[500] text-[#2563eb] hover:underline">{{ portalUrl }}</a>
                </div>
                <div>
                    <span class="text-[#6b7280]">Central link: </span>
                    <a :href="portalCentralUrl" target="_blank" class="font-[500] text-[#2563eb] hover:underline">{{ portalCentralUrl }}</a>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="text-[16px] font-[600] text-[#111111]">Custom domain</h2>
            <p class="mt-1 text-[13px] text-[#6b7280]">
                Hubungkan domain sendiri (mis. <code class="rounded bg-[#f5f5f5] px-1">docs.perusahaan.com</code>) ke workspace ini.
            </p>
            <p v-if="!hasFeature('custom_domain')" class="mt-3 rounded-[8px] bg-[#fef3c7] px-3 py-2 text-[13px] text-[#92400e]">
                Custom domain tersedia di plan Pro ke atas.
            </p>

            <template v-else>
                <div class="mt-4 rounded-[8px] border border-[#e5e7eb] bg-[#f8f9fa] p-4">
                    <p class="text-[13px] font-[600] text-[#111111]">Cara setup DNS</p>
                    <ol class="mt-2 list-inside list-decimal space-y-1.5 text-[13px] text-[#6b7280]">
                        <li>Tambahkan domain di bawah (contoh: <code class="rounded bg-white px-1">docs.perusahaan.com</code>).</li>
                        <li>Buka panel DNS provider Anda (Cloudflare, Route53, Niagahoster, dll.).</li>
                        <li>Buat record sesuai tabel di bawah ini.</li>
                        <li>Tunggu propagasi DNS (biasanya 5–60 menit, bisa sampai 24 jam).</li>
                        <li>Klik <strong>Verifikasi</strong> setelah DNS aktif.</li>
                    </ol>

                    <div class="mt-4 overflow-x-auto rounded-[8px] border border-[#e5e7eb] bg-white">
                        <table class="w-full min-w-[480px] text-left text-[13px]">
                            <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-[12px] text-[#898989]">
                                <tr>
                                    <th class="px-3 py-2 font-[500]">Tipe</th>
                                    <th class="px-3 py-2 font-[500]">Name / Host</th>
                                    <th class="px-3 py-2 font-[500]">Value / Target</th>
                                    <th class="px-3 py-2 font-[500]"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#e5e7eb]">
                                    <td class="px-3 py-2.5 font-[500] text-[#111111]">CNAME</td>
                                    <td class="px-3 py-2.5 text-[#6b7280]">
                                        <span>subdomain Anda</span>
                                        <span class="mt-0.5 block text-[11px] text-[#898989]">mis. <code>docs</code> untuk docs.perusahaan.com</span>
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-[12px] text-[#111111]">{{ cnameTarget }}</td>
                                    <td class="px-3 py-2.5">
                                        <CopyButton :text="cnameTarget" label="Salin" />
                                    </td>
                                </tr>
                                <tr v-if="aRecordIp">
                                    <td class="px-3 py-2.5 font-[500] text-[#111111]">A</td>
                                    <td class="px-3 py-2.5 text-[#6b7280]">@ <span class="text-[11px] text-[#898989]">(root domain)</span></td>
                                    <td class="px-3 py-2.5 font-mono text-[12px] text-[#111111]">{{ aRecordIp }}</td>
                                    <td class="px-3 py-2.5">
                                        <CopyButton :text="aRecordIp" label="Salin" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-3 text-[12px] text-[#898989]">
                        Gunakan <strong>CNAME</strong> untuk subdomain (disarankan).
                        <template v-if="!aRecordIp">Untuk root domain tanpa subdomain, hubungi admin platform untuk IP A-record.</template>
                        <template v-else>Untuk root domain, gunakan A-record di atas.</template>
                        Setelah verified, portal publik tersedia di <code class="rounded bg-white px-1">https://domain-anda/portal</code>.
                    </p>
                </div>

                <div class="mt-4 flex gap-2">
                    <input v-model="domainForm.domain" placeholder="docs.perusahaan.com" class="flex-1 rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989]" />
                    <button type="button" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50" :disabled="domainForm.processing" @click="addDomain">Tambah domain</button>
                </div>
            </template>
            <p v-if="domainForm.errors.domain" class="mt-2 text-[13px] text-[#ef4444]">{{ domainForm.errors.domain }}</p>

            <div v-if="tenant.domains?.length" class="mt-4 space-y-3">
                <div v-for="domain in tenant.domains" :key="domain.id" class="rounded-[8px] border border-[#e5e7eb] px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-[14px] font-[500] text-[#111111]">{{ domain.domain }}</p>
                            <p class="text-[12px] text-[#6b7280]">
                                <span v-if="domain.is_primary">Primary · </span>
                                <span :class="domain.is_verified ? 'text-[#10b981]' : 'text-[#f59e0b]'">
                                    {{ domain.is_verified ? 'Terverifikasi' : 'Menunggu verifikasi DNS' }}
                                </span>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-if="!domain.is_verified"
                                :href="route('settings.workspace.domains.verify', domain.id)"
                                method="post"
                                as="button"
                                class="rounded-[6px] border border-[#e5e7eb] px-3 py-1.5 text-[12px] font-[500] hover:bg-[#f8f9fa]"
                            >
                                Verifikasi
                            </Link>
                            <Link
                                :href="route('settings.workspace.domains.destroy', domain.id)"
                                method="delete"
                                as="button"
                                class="rounded-[6px] border border-[#fecaca] px-3 py-1.5 text-[12px] text-[#ef4444]"
                            >
                                Hapus
                            </Link>
                        </div>
                    </div>

                    <div v-if="!domain.is_verified && hasFeature('custom_domain')" class="mt-3 rounded-[6px] bg-[#fffbeb] px-3 py-2.5 text-[12px] text-[#92400e]">
                        <p class="font-[600]">Record untuk {{ domain.domain }}</p>
                        <p class="mt-1">
                            CNAME · Name: <code class="rounded bg-white px-1">{{ dnsHostLabel(domain.domain) }}</code>
                            · Target: <code class="rounded bg-white px-1">{{ cnameTarget }}</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
