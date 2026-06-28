<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CopyButton from '@/Components/CopyButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ tool: Object });

const form = ref({
    alias: 'myminio',
    bucket: 'mybucket',
    username: 'myuser',
    password: 'STRONG_PASSWORD',
    policy_name: 'mybucket-policy',
    permission: 'readwrite',
});

const snippetForm = useForm({
    title: '',
    command: '',
    description: 'Generated from MinIO IAM Generator',
});

const policyJson = computed(() => JSON.stringify({
    Version: '2012-10-17',
    Statement: [{
        Effect: 'Allow',
        Action: form.value.permission === 'readonly'
            ? ['s3:GetBucketLocation', 's3:GetObject', 's3:ListBucket']
            : ['s3:GetBucketLocation', 's3:GetObject', 's3:ListBucket', 's3:PutObject', 's3:DeleteObject'],
        Resource: [
            `arn:aws:s3:::${form.value.bucket}`,
            `arn:aws:s3:::${form.value.bucket}/*`,
        ],
    }],
}, null, 2));

const commands = computed(() => [
    `mc mb ${form.value.alias}/${form.value.bucket}`,
    `mc admin user add ${form.value.alias} ${form.value.username} ${form.value.password}`,
    `cat > ${form.value.policy_name}.json << 'EOF'\n${policyJson.value}\nEOF`,
    `mc admin policy create ${form.value.alias} ${form.value.policy_name} ${form.value.policy_name}.json`,
    `mc admin policy attach ${form.value.alias} ${form.value.policy_name} --user ${form.value.username}`,
].join('\n\n'));

const defaultSnippetTitle = computed(() => `MinIO IAM: ${form.value.bucket} (${form.value.username})`);

const saveSnippet = () => {
    snippetForm.title = snippetForm.title.trim() || defaultSnippetTitle.value;
    snippetForm.command = commands.value;
    snippetForm.post(route('tools.save-snippet', props.tool.slug));
};
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
                <h2 class="text-[15px] font-[600] text-[#111111]">Input</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><InputLabel value="Alias" /><input v-model="form.alias" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Bucket" /><input v-model="form.bucket" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Username" /><input v-model="form.username" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Password" /><input v-model="form.password" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Policy Name" /><input v-model="form.policy_name" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div>
                        <InputLabel value="Permission" />
                        <select v-model="form.permission" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="readwrite">Read/Write</option>
                            <option value="readonly">Read Only</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-[15px] font-[600] text-[#111111]">Generated Commands</h2>
                        <CopyButton :text="commands" label="Copy all" />
                    </div>
                    <pre class="overflow-x-auto rounded-[8px] bg-[#101010] p-4 text-[13px] leading-relaxed text-[#e5e7eb]"><code>{{ commands }}</code></pre>
                </div>

                <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-3">
                    <h2 class="text-[15px] font-[600] text-[#111111]">Save as Snippet</h2>
                    <div>
                        <InputLabel value="Snippet title (optional)" />
                        <input
                            v-model="snippetForm.title"
                            :placeholder="defaultSnippetTitle"
                            class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]"
                        />
                    </div>
                    <button
                        type="button"
                        class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50"
                        :disabled="snippetForm.processing"
                        @click="saveSnippet"
                    >
                        Save as Snippet
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
