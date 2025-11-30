<script setup lang="ts">
import FileDropZone from '@/components/FileDropZone.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import RecentFilesList from '@/components/RecentFilesList.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import share from '@/routes/share';
import { type BreadcrumbItem, FragFile } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Share',
        href: share.view().url,
    },
];

defineProps<{
    errors: Record<string, string[]>;
    /**
     * Allowed MIME types for file uploads
     * @example ['image/png', 'image/jpeg', 'video/mp4']
     */
    allowedMimeTypes: Array<string>;
    recentFragFiles: Array<FragFile>;
}>();

type UploadType = 'code' | 'file';
const selectedType = ref<UploadType>('file');
const codeFileName = ref<string>('');
const mediaFileName = ref<string>('');

const codeDropZoneRef = ref<InstanceType<typeof FileDropZone> | null>(null);
const mediaDropZoneRef = ref<InstanceType<typeof FileDropZone> | null>(null);

const handleCodeUploadSuccess = () => {
    codeDropZoneRef.value?.reset();
};

const handleMediaUploadSuccess = () => {
    mediaDropZoneRef.value?.reset();
};
</script>

<template>
    <Head title="Share" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="mx-auto w-full max-w-7xl space-y-6">
                <HeadingSmall
                    title="Share your content"
                    description="Upload code files or media to share with others"
                />

                <!-- Type selector -->
                <div
                    class="flex gap-2 rounded-xl border border-sidebar-border/70 bg-card p-1.5 shadow-sm dark:border-sidebar-border"
                >
                    <button
                        type="button"
                        @click="selectedType = 'file'"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all',
                            selectedType === 'file'
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                    >
                        <svg
                            class="size-4"
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
                        Share File
                    </button>
                    <button
                        type="button"
                        @click="selectedType = 'code'"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all',
                            selectedType === 'code'
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                    >
                        <svg
                            class="size-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"
                            />
                        </svg>
                        Share Code
                    </button>
                </div>

                <!-- Code Upload Form with Recent Uploads -->
                <div
                    v-if="selectedType === 'code'"
                    class="rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
                >
                    <div class="flex gap-6 p-6">
                        <!-- Left: Form -->
                        <Form
                            :action="share.code().url"
                            method="post"
                            enctype="multipart/form-data"
                            :only="['recentFragFiles']"
                            :preserve-scroll="true"
                            @success="handleCodeUploadSuccess"
                            class="flex-1 space-y-6"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="code-file">Code File</Label>
                                    <FileDropZone
                                        ref="codeDropZoneRef"
                                        id="code-file"
                                        icon="code"
                                        description="JS, TS, Python, PHP, Go, Rust, etc."
                                        :processing="processing"
                                        v-model="codeFileName"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="errors.file"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="code-description"
                                        >Description (optional)</Label
                                    >
                                    <textarea
                                        id="code-description"
                                        name="description"
                                        rows="4"
                                        placeholder="Add a description for your code..."
                                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                                    ></textarea>
                                    <InputError
                                        class="mt-2"
                                        :message="errors.description"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="code-expires-at"
                                        >Link expiration (optional)</Label
                                    >
                                    <input
                                        type="datetime-local"
                                        id="code-expires-at"
                                        name="expires_at"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="errors.expires_at"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button
                                    :disabled="processing || !codeFileName"
                                    type="submit"
                                >
                                    {{
                                        processing
                                            ? 'Uploading...'
                                            : 'Upload Code'
                                    }}
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-show="recentlySuccessful"
                                        class="text-sm font-medium text-green-600 dark:text-green-500"
                                    >
                                        Code uploaded successfully!
                                    </p>
                                </Transition>
                            </div>
                        </Form>

                        <!-- Separator -->
                        <Separator orientation="vertical" class="mx-2" />

                        <!-- Right: Recent Uploads -->
                        <div class="w-[380px] shrink-0">
                            <RecentFilesList :files="recentFragFiles" />
                        </div>
                    </div>
                </div>

                <!-- Media Upload Form with Recent Uploads -->
                <div
                    v-if="selectedType === 'file'"
                    class="rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
                >
                    <div class="flex gap-6 p-6">
                        <!-- Left: Form -->
                        <Form
                            :action="share.file()"
                            method="post"
                            enctype="multipart/form-data"
                            :only="['recentFragFiles']"
                            :preserve-scroll="true"
                            @success="handleMediaUploadSuccess"
                            class="flex-1 space-y-6"
                            :show-progress="true"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="media-file">Media File</Label>
                                    <FileDropZone
                                        ref="mediaDropZoneRef"
                                        id="media-file"
                                        icon="media"
                                        description="Images, GIFs, Videos (PNG, JPG, GIF, MP4, etc.)"
                                        :accept="allowedMimeTypes.join(',')"
                                        :processing="processing"
                                        v-model="mediaFileName"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="errors.file"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="media-description"
                                        >Description (optional)</Label
                                    >
                                    <textarea
                                        id="media-description"
                                        name="description"
                                        rows="4"
                                        placeholder="Add a description for your file..."
                                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                                    ></textarea>
                                    <InputError
                                        class="mt-2"
                                        :message="errors.description"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="media-expires-at"
                                        >Link expiration (optional)</Label
                                    >
                                    <input
                                        type="datetime-local"
                                        id="media-expires-at"
                                        name="expires_at"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="errors.expires_at"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button
                                    :disabled="processing || !mediaFileName"
                                    type="submit"
                                >
                                    {{
                                        processing
                                            ? 'Uploading...'
                                            : 'Upload File'
                                    }}
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-show="recentlySuccessful"
                                        class="text-sm font-medium text-green-600 dark:text-green-500"
                                    >
                                        File uploaded successfully!
                                    </p>
                                </Transition>
                            </div>
                        </Form>

                        <!-- Separator -->
                        <Separator orientation="vertical" class="mx-2" />

                        <!-- Right: Recent Uploads -->
                        <div class="w-[380px] shrink-0">
                            <RecentFilesList :files="recentFragFiles" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
