<script setup>
import { useMarkdown } from '@/Composables/useMarkdown';
import { useWikiCodeBlocks } from '@/Composables/useWikiCodeBlocks';
import { computed, ref, toRef } from 'vue';

const props = defineProps({
    content: { type: String, default: '' },
});

const { render } = useMarkdown();
const html = computed(() => render(props.content));
const container = ref(null);
useWikiCodeBlocks(container, toRef(props, 'content'));
</script>

<template>
    <article ref="container" class="wiki-content" v-html="html" />
</template>
