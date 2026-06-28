<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    minio: Object,
    recentArchives: Array,
});

const form = useForm({
    enabled: props.minio.enabled,
    endpoint: props.minio.endpoint,
    access_key: props.minio.access_key,
    secret_key: '',
    bucket: props.minio.bucket,
    region: props.minio.region,
    use_path_style_endpoint: props.minio.use_path_style_endpoint,
    archive_prefix: props.minio.archive_prefix,
    archive_imports: props.minio.archive_imports,
    archive_exports: props.minio.archive_exports,
    archive_uploads: props.minio.archive_uploads,
});

const save = () => form.put(route('settings.integrations.minio.update'));

const testConnection = () => router.post(route('settings.integrations.minio.test'));

const formatSize = (bytes) => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};
</script>

<template>
    <Head title="MinIO Integration" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.integrations')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Integrations</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">MinIO Archive</h1>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <form @submit.prevent="save" class="space-y-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div>
                    <h2 class="text-[16px] font-[600] text-[#111111]">Connection</h2>
                    <p class="mt-1 text-[13px] text-[#6b7280]">Configure MinIO or any S3-compatible object storage for file archives.</p>
                </div>

                <label class="flex items-center gap-3">
                    <input v-model="form.enabled" type="checkbox" class="rounded border-[#e5e7eb]" />
                    <span class="text-[14px] text-[#111111]">Enable MinIO archive</span>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel value="Endpoint URL" />
                        <input v-model="form.endpoint" type="url" placeholder="https://minio.example.com" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <InputError :message="form.errors.endpoint" />
                    </div>
                    <div>
                        <InputLabel value="Access key" />
                        <input v-model="form.access_key" type="text" autocomplete="off" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <InputError :message="form.errors.access_key" />
                    </div>
                    <div>
                        <InputLabel value="Secret key" />
                        <input v-model="form.secret_key" type="password" autocomplete="new-password" :placeholder="minio.has_secret_key ? 'Leave blank to keep current' : 'Secret key'" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <InputError :message="form.errors.secret_key" />
                    </div>
                    <div>
                        <InputLabel value="Bucket" />
                        <input v-model="form.bucket" type="text" placeholder="opswiki-archive" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <InputError :message="form.errors.bucket" />
                    </div>
                    <div>
                        <InputLabel value="Region" />
                        <input v-model="form.region" type="text" placeholder="us-east-1" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <InputError :message="form.errors.region" />
                    </div>
                </div>

                <label class="flex items-center gap-3">
                    <input v-model="form.use_path_style_endpoint" type="checkbox" class="rounded border-[#e5e7eb]" />
                    <span class="text-[14px] text-[#111111]">Use path-style endpoint (required for most MinIO setups)</span>
                </label>

                <div class="border-t border-[#e5e7eb] pt-6">
                    <h2 class="text-[16px] font-[600] text-[#111111]">Archive rules</h2>
                    <p class="mt-1 text-[13px] text-[#6b7280]">Choose which file operations are copied to the bucket.</p>

                    <div class="mt-4">
                        <InputLabel value="Archive prefix" />
                        <input v-model="form.archive_prefix" type="text" placeholder="archive" class="mt-1 block w-full max-w-xs rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <p class="mt-1 text-[12px] text-[#898989]">Files stored as {prefix}/imports|exports|uploads/YYYY/MM/DD/...</p>
                    </div>

                    <div class="mt-4 space-y-3">
                        <label class="flex items-center gap-3">
                            <input v-model="form.archive_imports" type="checkbox" class="rounded border-[#e5e7eb]" />
                            <span class="text-[14px] text-[#111111]">Archive wiki import files</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="form.archive_exports" type="checkbox" class="rounded border-[#e5e7eb]" />
                            <span class="text-[14px] text-[#111111]">Archive page exports</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="form.archive_uploads" type="checkbox" class="rounded border-[#e5e7eb]" />
                            <span class="text-[14px] text-[#111111]">Archive asset uploads</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-[#e5e7eb] pt-6">
                    <button type="submit" :disabled="form.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50">
                        Save settings
                    </button>
                    <button type="button" @click="testConnection" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">
                        Test connection
                    </button>
                </div>
            </form>

            <aside class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <h2 class="text-[14px] font-[600] text-[#111111]">Recent archives</h2>
                <p v-if="!recentArchives.length" class="mt-3 text-[13px] text-[#898989]">No files archived yet.</p>
                <ul v-else class="mt-3 space-y-3">
                    <li v-for="item in recentArchives" :key="item.id" class="border-b border-[#f3f4f6] pb-3 last:border-0 last:pb-0">
                        <p class="truncate text-[13px] font-[500] text-[#111111]" :title="item.original_name">{{ item.original_name }}</p>
                        <p class="mt-0.5 text-[12px] text-[#898989]">{{ item.type }} · {{ formatSize(item.size) }}</p>
                        <p class="mt-0.5 truncate text-[11px] text-[#b0b0b0]" :title="item.stored_path">{{ item.bucket }}/{{ item.stored_path }}</p>
                    </li>
                </ul>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
