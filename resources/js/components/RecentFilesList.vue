<script setup lang="ts">
import ShareButton from '@/components/ShareButton.vue';
import type { FragFile } from '@/types';

interface Props {
    files: FragFile[];
}

defineProps<Props>();

const formatFileSize = (size: number): string => {
    if (size < 1024) return `${size} B`;
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
};

const formatDate = (date: string | null): string => {
    if (!date) return '';
    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(date));
};

const getFileTypeLabel = (mimeType: string): string => {
    return mimeType.split('/')[0].replace('image', 'img');
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-foreground">
                Recent Uploads
            </h4>
            <span v-if="files.length > 0" class="text-xs text-muted-foreground">
                {{ files.length }} {{ files.length === 1 ? 'file' : 'files' }}
            </span>
        </div>

        <!-- Empty state -->
        <div
            v-if="files.length === 0"
            class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-sidebar-border/70 bg-card/50 p-12 text-center dark:border-sidebar-border"
        >
            <svg
                class="size-10 text-muted-foreground/50"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                />
            </svg>
            <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">
                    No files uploaded yet
                </p>
                <p class="text-xs text-muted-foreground/70">
                    Upload your first file above to get started
                </p>
            </div>
        </div>

        <!-- Files grid -->
        <div
            v-else
            class="grid max-h-[600px] gap-3 overflow-y-auto sm:grid-cols-2 lg:grid-cols-1"
        >
            <div
                v-for="file in files"
                :key="file.id"
                class="group flex items-start gap-3 rounded-xl border border-sidebar-border/70 bg-card p-3 shadow-sm transition-all hover:border-primary/50 hover:shadow-md dark:border-sidebar-border dark:hover:border-primary/50"
            >
                <!-- File icon based on mime type -->
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg transition-colors"
                    :class="[
                        file.mime_type.startsWith('image/')
                            ? 'bg-blue-500/10 text-blue-600 group-hover:bg-blue-500/20 dark:text-blue-400'
                            : file.mime_type.startsWith('video/')
                              ? 'bg-purple-500/10 text-purple-600 group-hover:bg-purple-500/20 dark:text-purple-400'
                              : 'bg-gray-500/10 text-gray-600 group-hover:bg-gray-500/20 dark:text-gray-400',
                    ]"
                >
                    <!-- Image icon -->
                    <svg
                        v-if="file.mime_type.startsWith('image/')"
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <!-- Video icon -->
                    <svg
                        v-else-if="file.mime_type.startsWith('video/')"
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                        />
                    </svg>
                    <!-- Generic file icon -->
                    <svg
                        v-else
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>
                </div>

                <!-- File info -->
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <a
                            :href="'storage/' + file.path"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm font-medium break-words text-foreground transition-colors hover:text-primary hover:underline"
                        >
                            {{ file.filename }}
                        </a>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-muted-foreground"
                    >
                        <span>{{ formatDate(file.created_at) }}</span>
                        <span class="text-muted-foreground/50">•</span>
                        <span>{{ formatFileSize(file.size) }}</span>
                        <span class="text-muted-foreground/50">•</span>
                        <span class="capitalize">{{
                            getFileTypeLabel(file.mime_type)
                        }}</span>
                        <template v-if="file.checksum">
                            <span class="text-muted-foreground/50">•</span>
                            <small
                                class="text-[.65rem] break-all text-muted-foreground/40"
                                >{{ file.checksum }}</small
                            >
                        </template>
                    </div>
                </div>

                <!-- Share button -->
                <div class="flex shrink-0 items-center">
                    <ShareButton :file="file" />
                </div>
            </div>
        </div>
    </div>
</template>
