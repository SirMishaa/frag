<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { ref } from 'vue';

interface Props {
    id: string;
    name?: string;
    accept?: string;
    required?: boolean;
    processing?: boolean;
    icon: 'code' | 'media';
    description: string;
    modelValue?: string;
}

const props = withDefaults(defineProps<Props>(), {
    name: 'file',
    required: false,
    processing: false,
    modelValue: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const fileName = ref(props.modelValue);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        fileName.value = target.files[0].name;
        emit('update:modelValue', target.files[0].name);
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;

    const files = event.dataTransfer?.files;
    if (files && files.length > 0 && fileInputRef.value) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(files[0]);
        fileInputRef.value.files = dataTransfer.files;
        fileName.value = files[0].name;
        emit('update:modelValue', files[0].name);
    }
};

const reset = () => {
    fileName.value = '';
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
    emit('update:modelValue', '');
};

defineExpose({ reset });
</script>

<template>
    <div
        @dragover="handleDragOver"
        @dragleave="handleDragLeave"
        @drop="handleDrop"
        :class="[
            'relative flex min-h-[200px] flex-col items-center justify-center rounded-lg border-2 border-dashed px-6 py-10 transition-all',
            processing
                ? 'border-primary bg-primary/5'
                : isDragging
                  ? 'scale-[1.02] border-primary bg-primary/10'
                  : 'border-input bg-background hover:border-ring/50',
        ]"
    >
        <!-- Upload state -->
        <div v-if="!processing" class="space-y-4 text-center">
            <!-- Code icon -->
            <svg
                v-if="icon === 'code'"
                :class="[
                    'mx-auto size-12 transition-all',
                    isDragging
                        ? 'scale-110 text-primary'
                        : 'text-muted-foreground',
                ]"
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
            <!-- Media icon -->
            <svg
                v-else
                :class="[
                    'mx-auto size-12 transition-all',
                    isDragging
                        ? 'scale-110 text-primary'
                        : 'text-muted-foreground',
                ]"
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
                <p class="text-sm font-medium text-foreground">
                    <label
                        :for="id"
                        class="cursor-pointer text-primary underline-offset-4 hover:underline"
                    >
                        Click to upload
                    </label>
                    or drag and drop
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <p v-if="fileName" class="text-sm font-medium text-foreground">
                Selected: {{ fileName }}
            </p>
        </div>

        <!-- Uploading state -->
        <div v-else class="flex flex-col items-center gap-4">
            <div class="relative size-16">
                <svg
                    class="size-16 animate-spin text-primary"
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
            </div>
            <div class="space-y-1 text-center">
                <p class="text-sm font-medium text-foreground">
                    Uploading {{ fileName }}...
                </p>
                <p class="text-xs text-muted-foreground">Please wait</p>
            </div>
        </div>

        <Input
            :id="id"
            ref="fileInputRef"
            type="file"
            :name="name"
            :accept="accept"
            :required="required"
            class="absolute inset-0 cursor-pointer opacity-0"
            :class="{
                'pointer-events-none': processing,
            }"
            @change="handleFileChange"
        />
    </div>
</template>
