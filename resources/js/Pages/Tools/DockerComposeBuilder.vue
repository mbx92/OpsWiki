<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CopyButton from '@/Components/CopyButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({ tool: Object });

const form = ref({
    service_name: 'app',
    image: 'nginx:alpine',
    port: '8080:80',
    restart: 'unless-stopped',
    volumes: './data:/data',
});

const yaml = computed(() => `services:
  ${form.value.service_name}:
    image: ${form.value.image}
    ports:
      - "${form.value.port}"
    restart: ${form.value.restart}
    volumes:
      - ${form.value.volumes}
`);
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
                <div><InputLabel value="Service Name" /><input v-model="form.service_name" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Image" /><input v-model="form.image" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Ports" /><input v-model="form.port" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Volumes" /><input v-model="form.volumes" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-[15px] font-[600] text-[#111111]">docker-compose.yml</h2>
                    <CopyButton :text="yaml" />
                </div>
                <pre class="overflow-x-auto rounded-[8px] bg-[#101010] p-4 text-[13px] text-[#e5e7eb]"><code>{{ yaml }}</code></pre>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
