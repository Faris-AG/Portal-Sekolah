<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { Users, BookOpen, GraduationCap, ArrowRight } from 'lucide-vue-next';

defineProps({
    totalStudents: Number,
    totalTeachers: Number,
    totalClasses: Number,
    recentClasses: Array,
});

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Admin
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome Banner -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Halo, {{ user.name }}! 👋</h3>
                        <p class="text-gray-500 mt-1">Selamat datang di panel administrasi portal sekolah.</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <Users size="28" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Siswa</p>
                            <h4 class="text-2xl font-bold text-gray-900">{{ totalStudents }}</h4>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                            <GraduationCap size="28" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Guru</p>
                            <h4 class="text-2xl font-bold text-gray-900">{{ totalTeachers }}</h4>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                            <BookOpen size="28" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total KelasAktif</p>
                            <h4 class="text-2xl font-bold text-gray-900">{{ totalClasses }}</h4>
                        </div>
                    </div>
                </div>

                <!-- List of Classes -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Daftar Kelas</h3>
                        <Link :href="route('admin.classes.index')" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                            Lihat Semua <ArrowRight size="16" />
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Nama Kelas</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Wali Kelas</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="recentClasses.length === 0">
                                    <td colspan="3" class="py-6 text-center text-gray-500">Belum ada kelas.</td>
                                </tr>
                                <tr v-for="kelas in recentClasses" :key="kelas.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6">
                                        <span class="font-medium text-gray-900">{{ kelas.name }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">
                                        <span v-if="kelas.wali_kelas">{{ kelas.wali_kelas }}</span>
                                        <span v-else class="text-gray-400 italic text-sm">Belum ditentukan</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <button class="text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">Detail</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
