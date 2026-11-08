<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ArrowRight } from 'lucide-vue-next';

defineProps({
    email: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <GuestLayout>
        <Head title="Email Berhasil Diverifikasi" />

        <div class="flex flex-col items-center text-center py-4">
            <!-- Modern Large Green Checkmark Icon with soft green glow -->
            <div class="relative mb-6">
                <div class="w-20 h-20 rounded-3xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-xl shadow-emerald-500/10 ring-8 ring-emerald-50/50">
                    <CheckCircle2 class="w-10 h-10" />
                </div>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">
                Email Berhasil Diverifikasi!
            </h2>

            <!-- Display Email Badge if available -->
            <div
                v-if="email"
                class="mb-3 inline-flex items-center px-3 py-1 rounded-full bg-slate-100 border border-slate-200/80 text-xs font-mono font-medium text-slate-700"
            >
                {{ email }}
            </div>

            <p class="text-slate-600 text-sm leading-relaxed max-w-sm mb-3">
                Akun Anda dengan email <span class="font-semibold text-slate-800">{{ email }}</span> telah aktif dan siap digunakan.
            </p>

            <p class="text-slate-500 text-xs leading-relaxed max-w-sm mb-6 bg-slate-50 border border-slate-200/60 rounded-xl p-3">
                Jika Anda membuka halaman ini di browser atau perangkat lain, tab utama Anda akan otomatis masuk ke Dashboard.
            </p>

            <!-- Action Button: Masuk ke Dashboard -->
            <div class="w-full pt-1">
                <Link
                    :href="route('dashboard')"
                    class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all text-sm"
                >
                    <span>Masuk ke Dashboard</span>
                    <ArrowRight class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

