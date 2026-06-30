<script setup>
import { computed } from 'vue';
import { importExampleUrl, importMarkdownExamples } from '@/Constants/importMarkdownExamples';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const example = computed(() => importMarkdownExamples[props.type]);
const downloadUrl = computed(() => importExampleUrl(props.type));
</script>

<template>
    <div v-if="example" class="rounded-[8px] bg-[#f8f9fa] p-4 text-[13px] text-[#6b7280]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <p class="font-[600] text-[#374151]">Format markdown</p>
            <a
                v-if="downloadUrl"
                :href="downloadUrl"
                download
                class="shrink-0 rounded-[6px] border border-[#e5e7eb] bg-white px-2.5 py-1 text-[12px] font-[500] text-[#111111] transition-colors hover:bg-[#f8f9fa]"
            >
                ↓ Unduh {{ example.label }}
            </a>
        </div>

        <p v-if="!compact" class="mt-2">
            Satu file <code class="rounded bg-white px-1">.md</code> = satu entri.
            Gunakan heading level 2 (<code class="rounded bg-white px-1">##</code>) per bagian.
        </p>

        <div class="mt-3 overflow-x-auto rounded-[6px] border border-[#e5e7eb] bg-white">
            <table class="w-full min-w-[280px] text-left text-[12px]">
                <thead>
                    <tr class="border-b border-[#e5e7eb] bg-[#fafafa]">
                        <th class="px-3 py-2 font-[600] text-[#374151]">Heading ##</th>
                        <th class="px-3 py-2 font-[600] text-[#374151]">Field</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, index) in example.sections"
                        :key="index"
                        class="border-b border-[#f3f4f6] last:border-0"
                    >
                        <td class="px-3 py-1.5 font-mono text-[11px] text-[#6b7280]">{{ row.heading }}</td>
                        <td class="px-3 py-1.5 text-[#374151]">{{ row.field }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-2 text-[12px] text-[#898989]">{{ example.notes }}</p>
    </div>
</template>
