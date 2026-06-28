<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import RelatedItemsPicker from '@/Components/RelatedItemsPicker.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ sop: Object, tags: Array, statuses: Array, related: Array, linkable: Array });

const tagInput = ref('');
const form = useForm({
    title: props.sop.title,
    purpose: props.sop.purpose ?? '',
    use_case: props.sop.use_case ?? '',
    requirements: props.sop.requirements ?? '',
    steps: props.sop.steps ?? '',
    validation: props.sop.validation ?? '',
    rollback: props.sop.rollback ?? '',
    notes: props.sop.notes ?? '',
    status: props.sop.status,
    tag_names: props.sop.tags?.map(t => t.name) ?? [],
    related: props.related?.map(r => ({ type: r.type, id: r.id })) ?? [],
});

const submit = () => form.put(route('sops.update', props.sop.slug));

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};
</script>

<template>
    <Head :title="`Edit: ${sop.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('sops.show', sop.slug)" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Back</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit SOP</h1>
                </div>
                <FormHeaderActions form-id="sop-edit-form" :cancel-href="route('sops.show', sop.slug)" save-label="Save Changes" :processing="form.processing" />
            </div>
        </template>

        <form id="sop-edit-form" @submit.prevent="submit" class="space-y-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Title" />
                        <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required />
                    </div>
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel value="Purpose" />
                    <textarea v-model="form.purpose" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Use case" />
                    <textarea v-model="form.use_case" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Requirements" />
                    <textarea v-model="form.requirements" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Steps" />
                    <MarkdownEditor v-model="form.steps" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel value="Validation" />
                    <textarea v-model="form.validation" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Rollback" />
                    <textarea v-model="form.rollback" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Notes" />
                    <textarea v-model="form.notes" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Tags" />
                    <div class="mt-1.5 flex gap-2">
                        <input v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Add tag..." class="flex-1 rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <button type="button" @click="addTag" class="rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[13px]">Add</button>
                    </div>
                    <div v-if="form.tag_names.length" class="mt-2 flex flex-wrap gap-2">
                        <span v-for="tag in form.tag_names" :key="tag" class="rounded-[9999px] bg-[#f3f4f6] px-2 py-0.5 text-[12px]">{{ tag }}</span>
                    </div>
                </div>
                <div>
                    <InputLabel value="Related items" />
                    <RelatedItemsPicker v-model="form.related" :linkable="linkable" class="mt-1.5" />
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
