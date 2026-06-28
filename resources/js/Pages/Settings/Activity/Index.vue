<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Object });

const formatDate = (value) => new Date(value).toLocaleString();
</script>

<template>
    <Head title="Activity Log" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Activity Log</h1>
            </div>
        </template>

        <div v-if="logs.data.length" class="overflow-hidden rounded-[12px] border border-[#e5e7eb] bg-white">
            <table class="w-full text-[14px]">
                <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-left text-[13px] text-[#898989]">
                    <tr>
                        <th class="px-5 py-3 font-[500]">When</th>
                        <th class="px-5 py-3 font-[500]">User</th>
                        <th class="px-5 py-3 font-[500]">Action</th>
                        <th class="px-5 py-3 font-[500]">Subject</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="border-b border-[#e5e7eb] last:border-0">
                        <td class="px-5 py-3 text-[#6b7280]">{{ formatDate(log.created_at) }}</td>
                        <td class="px-5 py-3">{{ log.user?.name ?? 'System' }}</td>
                        <td class="px-5 py-3 capitalize">{{ log.action }}</td>
                        <td class="px-5 py-3">
                            <span class="text-[#898989]">{{ log.subject_type }}</span>
                            <span v-if="log.subject_label"> · {{ log.subject_label }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="text-[14px] text-[#898989]">No activity recorded yet.</p>
    </AuthenticatedLayout>
</template>
