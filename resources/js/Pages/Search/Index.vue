<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    query: String,
    pages: Array,
    snippets: Array,
    inbox: Array,
    sops: Array,
    troubleshooting: Array,
    projects: Array,
    mode: String,
});
</script>

<template>
    <Head title="Search" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                    Search<span v-if="query">: "{{ query }}"</span>
                </h1>
                <span v-if="mode && mode !== 'none'" class="rounded-[9999px] bg-[#f3f4f6] px-2 py-0.5 text-[11px] capitalize text-[#898989]">{{ mode === 'semantic' ? 'smart search' : mode }}</span>
            </div>
        </template>

        <p v-if="query && query.length < 2" class="text-[14px] text-[#898989]">Enter at least 2 characters to search.</p>

        <div v-if="query && query.length >= 2" class="space-y-8">
            <section v-if="pages.length">
                <h2 class="mb-3 text-[15px] font-[600] text-[#111111]">Wiki Pages ({{ pages.length }})</h2>
                <div class="space-y-2">
                    <Link v-for="page in pages" :key="page.id" :href="route('wiki.show', page.slug)" class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 hover:bg-[#f8f9fa]">
                        <div>
                            <div class="text-[14px] font-[500] text-[#111111]">{{ page.title }}</div>
                            <p v-if="page.summary" class="text-[13px] text-[#6b7280]">{{ page.summary }}</p>
                        </div>
                        <StatusBadge :status="page.status" />
                    </Link>
                </div>
            </section>

            <section v-if="sops.length">
                <h2 class="mb-3 text-[15px] font-[600] text-[#111111]">SOPs ({{ sops.length }})</h2>
                <div class="space-y-2">
                    <Link v-for="s in sops" :key="s.id" :href="route('sops.show', s.slug)" class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 hover:bg-[#f8f9fa]">
                        <div class="text-[14px] font-[500] text-[#111111]">{{ s.title }}</div>
                        <StatusBadge :status="s.status" />
                    </Link>
                </div>
            </section>

            <section v-if="troubleshooting.length">
                <h2 class="mb-3 text-[15px] font-[600] text-[#111111]">Troubleshooting ({{ troubleshooting.length }})</h2>
                <div class="space-y-2">
                    <Link v-for="c in troubleshooting" :key="c.id" :href="route('troubleshooting.show', c.slug)" class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 hover:bg-[#f8f9fa]">
                        <div class="text-[14px] font-[500] text-[#111111]">{{ c.title }}</div>
                        <span class="text-[12px] capitalize text-[#898989]">{{ c.severity }}</span>
                    </Link>
                </div>
            </section>

            <section v-if="projects.length">
                <h2 class="mb-3 text-[15px] font-[600] text-[#111111]">Projects ({{ projects.length }})</h2>
                <div class="space-y-2">
                    <Link v-for="p in projects" :key="p.id" :href="route('projects.show', p.slug)" class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 hover:bg-[#f8f9fa]">
                        <div class="text-[14px] font-[500] text-[#111111]">{{ p.name }}</div>
                        <StatusBadge :status="p.status" />
                    </Link>
                </div>
            </section>

            <section v-if="snippets.length">
                <h2 class="mb-3 text-[15px] font-[600] text-[#111111]">Snippets ({{ snippets.length }})</h2>
                <div class="space-y-2">
                    <Link v-for="s in snippets" :key="s.id" :href="route('snippets.edit', s.id)" class="block rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 hover:bg-[#f8f9fa]">
                        <div class="text-[14px] font-[500] text-[#111111]">{{ s.title }}</div>
                        <pre class="mt-1 truncate text-[12px] text-[#898989]">{{ s.command }}</pre>
                    </Link>
                </div>
            </section>

            <section v-if="inbox.length">
                <h2 class="mb-3 text-[15px] font-[600] text-[#111111]">Inbox ({{ inbox.length }})</h2>
                <div class="space-y-2">
                    <Link v-for="item in inbox" :key="item.id" :href="route('inbox.show', item.id)" class="block rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 hover:bg-[#f8f9fa]">
                        <div class="text-[14px] font-[500] text-[#111111]">{{ item.title }}</div>
                    </Link>
                </div>
            </section>

            <p v-if="!pages.length && !snippets.length && !inbox.length && !sops.length && !troubleshooting.length && !projects.length" class="text-[14px] text-[#898989]">No results found.</p>
        </div>
    </AuthenticatedLayout>
</template>
