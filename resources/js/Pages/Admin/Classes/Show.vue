<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    School, ChevronLeft, Users, Calendar, Clock, BookOpen, User as UserIcon 
} from 'lucide-vue-next';

const props = defineProps({
    schoolClass: Object,
    students: Array,
    schedules: Array,
});

const daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
const activeTab = ref('Senin');

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};
</script>

<template>
    <Head :title="`Detail Kelas ${schoolClass.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4 w-full">
                <Link 
                    :href="route('admin.classes.index')"
                    class="p-2 bg-white text-gray-500 hover:text-indigo-600 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all"
                >
                    <ChevronLeft size="20" />
                </Link>
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                        Detail Kelas {{ schoolClass.name }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Class Info Banner -->
                <div class="bg-indigo-900 rounded-3xl p-8 relative overflow-hidden shadow-lg border border-indigo-800 text-white flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-500 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2 opacity-30 pointer-events-none"></div>
                    
                    <div class="flex items-center gap-6 relative z-10">
                        <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20 backdrop-blur-sm shrink-0">
                            <School size="40" class="text-indigo-200" />
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-800 text-indigo-100 border border-indigo-700 mb-3">
                                TINGKAT {{ schoolClass.grade_level }}
                            </div>
                            <h3 class="text-3xl font-black mb-1">Kelas {{ schoolClass.name }}</h3>
                            <p class="text-indigo-200 font-medium flex items-center gap-2">
                                <UserIcon size="16" /> Wali Kelas: {{ schoolClass.wali_kelas || 'Belum diatur' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 relative z-10 w-full md:w-auto">
                        <div class="bg-indigo-800/50 backdrop-blur-sm border border-indigo-700 rounded-2xl p-4 flex-1 md:w-32 flex flex-col items-center justify-center text-center">
                            <Users size="24" class="text-indigo-300 mb-2" />
                            <div class="text-3xl font-black text-white leading-none mb-1">{{ students.length }}</div>
                            <div class="text-xs font-medium text-indigo-300 uppercase tracking-wider">Siswa</div>
                        </div>
                        <div class="bg-indigo-800/50 backdrop-blur-sm border border-indigo-700 rounded-2xl p-4 flex-1 md:w-32 flex flex-col items-center justify-center text-center">
                            <Calendar size="24" class="text-indigo-300 mb-2" />
                            <div class="text-3xl font-black text-white leading-none mb-1">{{ schedules.length }}</div>
                            <div class="text-xs font-medium text-indigo-300 uppercase tracking-wider">Sesi Mapel</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Student List -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <Users class="text-indigo-600" size="20" /> Daftar Siswa
                                </h3>
                            </div>
                            <div class="p-0 overflow-y-auto max-h-[600px] custom-scrollbar">
                                <ul v-if="students.length > 0" class="divide-y divide-gray-100">
                                    <li v-for="student in students" :key="student.id" class="p-4 hover:bg-gray-50 transition-colors flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ student.name.substring(0,2).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ student.name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ student.email }}</p>
                                        </div>
                                    </li>
                                </ul>
                                <div v-else class="p-8 text-center text-gray-500">
                                    Belum ada siswa di kelas ini.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <Calendar class="text-indigo-600" size="20" /> Jadwal Pelajaran Mingguan
                                </h3>
                            </div>
                            
                            <div class="flex border-b border-gray-100 overflow-x-auto bg-white">
                                <button 
                                    v-for="day in daysOfWeek" 
                                    :key="day"
                                    @click="activeTab = day"
                                    class="flex-1 py-4 px-4 text-sm font-bold transition-all border-b-2 whitespace-nowrap focus:outline-none text-center"
                                    :class="activeTab === day ? 'border-indigo-600 text-indigo-700 bg-indigo-50/30' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                >
                                    {{ day }}
                                </button>
                            </div>

                            <div class="p-0 min-h-[400px]">
                                <div v-for="day in daysOfWeek" :key="'content-'+day" v-show="activeTab === day">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500">
                                                <th class="py-3 px-6 font-semibold w-1/4 border-b border-gray-100">Waktu</th>
                                                <th class="py-3 px-6 font-semibold w-2/4 border-b border-gray-100">Mata Pelajaran</th>
                                                <th class="py-3 px-6 font-semibold w-1/4 border-b border-gray-100">Guru</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            <template v-for="schedule in schedules.filter(s => s.day === day)" :key="schedule.id">
                                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                                    <td class="py-4 px-6">
                                                        <div class="flex items-center gap-1.5 text-gray-900 font-bold text-sm bg-indigo-50 text-indigo-700 px-2 py-1 rounded-md inline-flex border border-indigo-100">
                                                            <Clock size="14" />
                                                            {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <div class="font-bold text-gray-900">{{ schedule.subject?.name }}</div>
                                                        <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                            <BookOpen size="12" /> {{ schedule.subject?.code }}
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <div class="text-sm text-gray-700 font-medium flex items-center gap-2">
                                                            <UserIcon size="14" class="text-gray-400" />
                                                            {{ schedule.teacher?.name || '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr v-if="schedules.filter(s => s.day === day).length === 0">
                                                <td colspan="3" class="py-12 px-6 text-center text-gray-400">
                                                    <Calendar class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                                    <p class="text-sm font-medium">Tidak ada jadwal.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
