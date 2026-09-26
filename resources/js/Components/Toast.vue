<script setup>
import { CheckCircle, XCircle } from 'lucide-vue-next';

defineProps({
    message: String,
    type: {
        type: String,
        default: 'success'
    }
});
</script>

<template>
    <div 
        class="fixed top-6 right-6 z-50 flex items-center gap-3 px-6 py-4 rounded-xl shadow-lg border transform transition-all duration-300 pointer-events-auto"
        :class="[
            type === 'success' ? 'bg-white border-green-100 text-green-800' : 'bg-white border-red-100 text-red-800'
        ]"
    >
        <CheckCircle v-if="type === 'success'" class="text-green-500" size="24" />
        <XCircle v-else class="text-red-500" size="24" />
        
        <div>
            <h4 class="text-sm font-bold">{{ type === 'success' ? 'Berhasil!' : 'Oops, Terjadi Kesalahan!' }}</h4>
            <p class="text-sm font-medium opacity-90">{{ message }}</p>
        </div>
    </div>
</template>
