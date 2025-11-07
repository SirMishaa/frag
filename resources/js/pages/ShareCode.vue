<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import share from '@/routes/share';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Share',
        href: share.view().url,
    },
];

type UploadType = 'code' | 'file';
const selectedType = ref<UploadType>('code');
const codeFileName = ref<string>('');
const mediaFileName = ref<string>('');

const handleCodeFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        codeFileName.value = target.files[0].name;
    }
};

const handleMediaFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        mediaFileName.value = target.files[0].name;
    }
};
</script>

<template>
    <Head title="Share" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="mx-auto w-full max-w-2xl space-y-6">
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
                </div>

                <!-- Code Upload Form -->
                <Form
                    v-if="selectedType === 'code'"
                    :action="share.code().url"
                    method="post"
                    enctype="multipart/form-data"
                    class="space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="code-file">Code File</Label>
                            <div
                                class="relative flex min-h-[200px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-input bg-background px-6 py-10 transition-colors hover:border-ring/50"
                            >
                                <div class="space-y-4 text-center">
                                    <svg
                                        class="mx-auto size-12 text-muted-foreground"
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
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm font-medium text-foreground"
                                        >
                                            <label
                                                for="code-file"
                                                class="cursor-pointer text-primary underline-offset-4 hover:underline"
                                            >
                                                Click to upload
                                            </label>
                                            or drag and drop
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            JS, TS, Python, PHP, Go, Rust, etc.
                                        </p>
                                    </div>
                                    <p
                                        v-if="codeFileName"
                                        class="text-sm font-medium text-foreground"
                                    >
                                        Selected: {{ codeFileName }}
                                    </p>
                                </div>
                                <Input
                                    id="code-file"
                                    type="file"
                                    name="file"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    @change="handleCodeFileChange"
                                    required
                                />
                            </div>
                            <InputError class="mt-2" :message="errors.file" />
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
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing || !codeFileName"
                            type="submit"
                        >
                            {{ processing ? 'Uploading...' : 'Upload Code' }}
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

                <!-- Media Upload Form -->
                <Form
                    v-if="selectedType === 'file'"
                    :action="share.file().url"
                    method="post"
                    enctype="multipart/form-data"
                    class="space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="media-file">Media File</Label>
                            <div
                                class="relative flex min-h-[200px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-input bg-background px-6 py-10 transition-colors hover:border-ring/50"
                            >
                                <div class="space-y-4 text-center">
                                    <svg
                                        class="mx-auto size-12 text-muted-foreground"
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
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm font-medium text-foreground"
                                        >
                                            <label
                                                for="media-file"
                                                class="cursor-pointer text-primary underline-offset-4 hover:underline"
                                            >
                                                Click to upload
                                            </label>
                                            or drag and drop
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Images, GIFs, Videos (PNG, JPG, GIF,
                                            MP4, etc.)
                                        </p>
                                    </div>
                                    <p
                                        v-if="mediaFileName"
                                        class="text-sm font-medium text-foreground"
                                    >
                                        Selected: {{ mediaFileName }}
                                    </p>
                                </div>
                                <Input
                                    id="media-file"
                                    type="file"
                                    name="file"
                                    accept="image/*,video/*,.gif"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    @change="handleMediaFileChange"
                                    required
                                />
                            </div>
                            <InputError class="mt-2" :message="errors.file" />
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
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing || !mediaFileName"
                            type="submit"
                        >
                            {{ processing ? 'Uploading...' : 'Upload File' }}
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

                <!-- Recent uploads section (placeholder) -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border"
                >
                    <h4 class="mb-4 text-sm font-medium">Recent Uploads</h4>
                    <p class="text-sm text-muted-foreground">
                        No files uploaded yet. Upload your first file above.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
