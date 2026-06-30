<script setup>
import { useWikiCodeBlocks } from '@/Composables/useWikiCodeBlocks';
import { useMarkdown } from '@/Composables/useMarkdown';
import { removeEmbeddedToc } from '@/Composables/useWikiContentCleanup';
import { computed, ref, toRef, onMounted, watch } from 'vue';

const props = defineProps({
    html: { type: String, default: '' },
});

const { sanitize } = useMarkdown();
const safeHtml = computed(() => sanitize(props.html));

const container = ref(null);
useWikiCodeBlocks(container, toRef(props, 'html'));

const cleanup = () => {
    const root = container.value?.querySelector('.wiki-content') || container.value;
    removeEmbeddedToc(root);
};

onMounted(() => setTimeout(cleanup, 0));
watch(() => props.html, () => setTimeout(cleanup, 0));
</script>

<template>
    <div ref="container" class="wiki-content-host" v-html="safeHtml" />
</template>
