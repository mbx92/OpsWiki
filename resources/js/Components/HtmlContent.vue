<script setup>
import { ref, toRef, onMounted, watch } from 'vue';
import { useWikiCodeBlocks } from '@/Composables/useWikiCodeBlocks';
import { removeEmbeddedToc } from '@/Composables/useWikiContentCleanup';

const props = defineProps({
    html: { type: String, default: '' },
});

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
    <div ref="container" class="wiki-content-host" v-html="html" />
</template>
