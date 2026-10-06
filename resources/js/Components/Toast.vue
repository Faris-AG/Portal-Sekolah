<script setup>
import { CheckCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({
    message: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: 'success', // 'success' | 'error'
    },
});
</script>

<template>
    <div
        class="fixed bottom-6 right-6 z-[9999] flex items-start gap-3 px-5 py-4 rounded-2xl shadow-2xl text-sm font-medium max-w-sm w-full border animate-slide-in"
        :class="{
            'bg-white border-green-200 text-green-800': type === 'success',
            'bg-white border-red-200 text-red-800': type === 'error',
        }"
        role="alert"
    >
        <CheckCircle
            v-if="type === 'success'"
            size="20"
            class="shrink-0 text-green-500 mt-0.5"
        />
        <XCircle
            v-else
            size="20"
            class="shrink-0 text-red-500 mt-0.5"
        />
        <span>{{ message }}</span>
    </div>
</template>

<style scoped>
@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-slide-in {
    animation: slide-in 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
</style>
