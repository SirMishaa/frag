<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { link } from '@/routes/share';
import type { FragFile } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    file: FragFile;
}

const props = defineProps<Props>();

const isGenerating = ref(false);
const isCopied = ref(false);

const getExistingLink = (): string | null => {
    if (props.file.links && props.file.links.length > 0) {
        return `${window.location.origin}/l/${props.file.links[0].slug}`;
    }
    return null;
};

const handleShareClick = async () => {
    const existingLink = getExistingLink();

    if (existingLink) await copyToClipboard(existingLink);
    else await generateShareableLink();
};

const generateShareableLink = async () => {
    isGenerating.value = true;

    router.post(
        link().url,
        { frag_file_id: props.file.id },
        {
            preserveScroll: true,
            only: ['recentFragFiles'],
            onSuccess: (page) => {
                const generatedLink = page.props.flash?.generatedLink;
                if (generatedLink?.url) {
                    copyToClipboard(generatedLink.url);
                }
            },
            onFinish: () => {
                isGenerating.value = false;
            },
        },
    );
};

const copyToClipboard = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy link:', err);
    }
};
</script>

<template>
    <Button
        variant="ghost"
        size="icon"
        @click.stop="handleShareClick"
        :disabled="isGenerating"
        class="size-8"
        :title="
            isCopied
                ? 'Link copied!'
                : getExistingLink()
                  ? 'Copy shareable link'
                  : 'Generate shareable link'
        "
    >
        <!-- Check icon (when copied) -->
        <svg
            v-if="isCopied"
            class="size-4 text-green-600 dark:text-green-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
            />
        </svg>
        <!-- Loading spinner -->
        <svg
            v-else-if="isGenerating"
            class="size-4 animate-spin text-muted-foreground"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <!-- Copy icon (if link exists) -->
        <svg
            v-else-if="getExistingLink()"
            class="size-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
            />
        </svg>
        <!-- Share icon (if no link exists) -->
        <svg
            v-else
            class="size-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
            />
        </svg>
    </Button>
</template>
