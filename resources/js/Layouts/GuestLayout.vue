<script setup>
import { Link } from '@inertiajs/vue3';
import { School } from 'lucide-vue-next';
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 lg:p-8 relative overflow-hidden">
        
        <!-- Abstract Background Elements -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-600 rounded-full blur-3xl opacity-10 pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-cyan-500 rounded-full blur-3xl opacity-10 pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Header / Logo -->
            <div class="flex flex-col items-center mb-8">
                <Link href="/" class="group flex flex-col items-center gap-3">
                    <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:-translate-y-1 transition-transform">
                        <School size="36" stroke-width="1.5" />
                    </div>
                    <div class="text-center">
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Portal Akademik</h1>
                        <p class="text-sm font-medium text-slate-500 mt-1">Sistem Informasi Sekolah</p>
                    </div>
                </Link>
            </div>

            <!-- Main Card -->
            <div class="w-full px-8 py-10 bg-white shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-cyan-500"></div>
                
                <slot />
            </div>
            
            <div class="mt-8 text-center">
                <p class="text-xs font-medium text-slate-400">
                    &copy; {{ new Date().getFullYear() }} Portal Akademik Sekolah. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>
