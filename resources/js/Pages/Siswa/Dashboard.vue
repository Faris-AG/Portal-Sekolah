<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { 
    Book, Bell, ArrowRight, Clock, User, 
    GraduationCap, AlertCircle, CheckCircle, 
    XCircle, FileText, Star
} from 'lucide-vue-next';

const props = defineProps({
    student: Object,
    schoolClass: Object,
    todaySchedules: Array,
    tugasTerdekat: Array,
    attendanceStats: Object,
});

const user = usePage().props.auth.user;

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleString('id-ID', { 
        weekday: 'short', 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const hasSubmitted = (assignment) => {
    return assignment.submissions && assignment.submissions.length > 0;
};
</script>

<template>
    <Head title="Siswa Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Siswa
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome & Profile Banner -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center justify-between relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/4"></div>
                    <div class="flex items-center gap-6">
                        <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center border-4 border-white shadow-sm">
                            <User class="h-10 w-10 text-indigo-500" />
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-900">Halo, {{ student.name }}! 👋</h3>
                            <p class="mt-1 text-gray-500 flex items-center gap-2">
                                <span>{{ student.email }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 md:mt-0 md:text-right">
                        <div v-if="schoolClass" class="inline-flex flex-col items-center md:items-end">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Kelas Anda</span>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-lg shadow-sm">
                                <GraduationCap size="20" /> {{ schoolClass.name }}
                            </span>
                        </div>
                        <div v-else class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 shadow-sm">
                            <AlertCircle size="20" />
                            <span class="font-medium">Belum ditempatkan di kelas</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 space-y-6">
                        
                        <!-- Tasks & Assignments Section -->
                        <div v-if="schoolClass" class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <FileText class="text-orange-500" size="20" />
                                    <h3 class="text-lg font-bold text-gray-900">Tugas Perlu Dikerjakan</h3>
                                </div>
                                <Link :href="route('siswa.assignments')" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                    Lihat Semua Tugas <ArrowRight size="16" />
                                </Link>
                            </div>
                            <div class="p-6">
                                <div v-if="tugasTerdekat.length === 0" class="text-gray-400 text-center py-12 flex flex-col items-center">
                                    <CheckCircle size="48" class="mb-4 opacity-20" />
                                    <p>Yeay! Tidak ada tugas mendesak saat ini.</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="tugas in tugasTerdekat" :key="tugas.id" class="border border-gray-100 rounded-xl p-5 hover:border-indigo-100 hover:shadow-md transition-all bg-white relative overflow-hidden group">
                                        <div class="absolute top-0 left-0 w-1 h-full" :class="hasSubmitted(tugas) ? 'bg-green-500' : 'bg-red-500'"></div>
                                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 ml-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 text-gray-700">
                                                        {{ tugas.subject?.name }}
                                                    </span>
                                                    <span class="text-xs font-medium text-gray-500">
                                                        Oleh: {{ tugas.teacher?.name }}
                                                    </span>
                                                </div>
                                                <h4 class="text-lg font-bold text-gray-900">{{ tugas.title }}</h4>
                                                
                                                <div class="flex items-center gap-4 mt-4 text-sm font-medium">
                                                    <span class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-2 py-1 rounded-md">
                                                        <Clock size="14" /> Deadline: {{ formatDate(tugas.due_date) }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col items-end gap-3 min-w-[140px]">
                                                <div v-if="hasSubmitted(tugas)" class="inline-flex flex-col items-end gap-2">
                                                    <div class="inline-flex items-center gap-1.5 text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                                                        <CheckCircle size="16" /> Dikumpulkan
                                                    </div>
                                                    <div v-if="tugas.submissions[0].grade !== null" class="inline-flex items-center gap-1.5 text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                                                        <Star size="16" /> Nilai: {{ tugas.submissions[0].grade }}
                                                    </div>
                                                </div>
                                                <div v-else class="inline-flex items-center gap-1.5 text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                                                    <XCircle size="16" /> Belum
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-6">
                        <!-- Today's Schedule (Mini Card) -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-indigo-50/30 rounded-t-2xl">
                                <div class="flex items-center gap-2">
                                    <Book class="text-indigo-600" size="18" />
                                    <h3 class="text-base font-bold text-indigo-900">Jadwal Hari Ini</h3>
                                </div>
                            </div>
                            <div class="p-5 flex-1">
                                <div v-if="!schoolClass" class="text-gray-500 text-center py-6 text-sm">
                                    Anda belum tergabung di kelas mana pun.
                                </div>
                                <div v-else-if="todaySchedules.length === 0" class="text-gray-500 text-center py-6 text-sm">
                                    Hore! Tidak ada kelas hari ini.
                                </div>
                                <ul v-else class="relative border-l border-indigo-100 ml-2 space-y-5">
                                    <li v-for="jadwal in todaySchedules" :key="jadwal.id" class="pl-5 relative">
                                        <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-white border-2 border-indigo-500"></span>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ jadwal.subject?.name }}</h4>
                                            <p class="text-xs font-medium text-gray-500 flex items-center gap-1 mt-0.5">
                                                <Clock size="12" /> {{ formatTime(jadwal.start_time) }} - {{ formatTime(jadwal.end_time) }}
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Attendance Mini Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-5 border-b border-gray-100 flex items-center gap-2">
                                <CheckCircle class="text-green-500" size="18" />
                                <h3 class="text-base font-bold text-gray-900">Kehadiran Saya</h3>
                            </div>
                            <div class="p-5 flex-1">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="text-center bg-green-50 rounded-xl p-3 border border-green-100">
                                        <p class="text-xs font-bold text-green-700 uppercase">Hadir</p>
                                        <p class="text-3xl font-black text-green-900">{{ attendanceStats?.hadir || 0 }}</p>
                                    </div>
                                    <div class="text-center bg-orange-50 rounded-xl p-3 border border-orange-100">
                                        <p class="text-xs font-bold text-orange-700 uppercase">Sakit</p>
                                        <p class="text-3xl font-black text-orange-900">{{ attendanceStats?.sakit || 0 }}</p>
                                    </div>
                                    <div class="text-center bg-blue-50 rounded-xl p-3 border border-blue-100">
                                        <p class="text-xs font-bold text-blue-700 uppercase">Izin</p>
                                        <p class="text-3xl font-black text-blue-900">{{ attendanceStats?.izin || 0 }}</p>
                                    </div>
                                    <div class="text-center bg-red-50 rounded-xl p-3 border border-red-100">
                                        <p class="text-xs font-bold text-red-700 uppercase">Alpa</p>
                                        <p class="text-3xl font-black text-red-900">{{ attendanceStats?.alpa || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
