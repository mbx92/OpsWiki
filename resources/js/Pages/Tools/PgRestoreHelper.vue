<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CopyButton from '@/Components/CopyButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({ tool: Object });

const form = ref({
    host: 'localhost',
    port: '5432',
    user: 'postgres',
    database: 'mydb',
    file: 'backup.dump',
    clean: true,
    no_owner: true,
});

const command = computed(() => {
    let cmd = `pg_restore -h ${form.value.host} -p ${form.value.port} -U ${form.value.user} -d ${form.value.database}`;
    if (form.value.clean) cmd += ' --clean --if-exists';
    if (form.value.no_owner) cmd += ' --no-owner --no-acl';
    cmd += ` ${form.value.file}`;
    return cmd;
});
</script>

<template>
    <Head :title="tool.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('tools.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Tools</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ tool.title }}</h1>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><InputLabel value="Host" /><input v-model="form.host" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Port" /><input v-model="form.port" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="User" /><input v-model="form.user" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Database" /><input v-model="form.database" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div class="sm:col-span-2"><InputLabel value="Dump File" /><input v-model="form.file" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                </div>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 text-[14px]"><input type="checkbox" v-model="form.clean" /> --clean --if-exists</label>
                    <label class="flex items-center gap-2 text-[14px]"><input type="checkbox" v-model="form.no_owner" /> --no-owner --no-acl</label>
                </div>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-[15px] font-[600] text-[#111111]">Command</h2>
                    <CopyButton :text="command" />
                </div>
                <pre class="overflow-x-auto rounded-[8px] bg-[#101010] p-4 text-[13px] text-[#e5e7eb]"><code>{{ command }}</code></pre>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
