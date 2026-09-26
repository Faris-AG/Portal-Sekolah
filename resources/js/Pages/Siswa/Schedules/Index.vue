<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Calendar, Clock } from 'lucide-vue-next';

const props = defineProps({
    student: Object,
    schoolClass: Object,
    allSchedules: Array,
});

const daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

const activeTab = ref(new Date().toLocaleDateString('id-ID', { weekday: 'long' }));
if (!daysOfWeek.includes(activeTab.value)) {
    activeTab.value = 'Senin';
}

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};
</script>

<template>
    <Head title="Jadwal Saya" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <Calendar size="24" class="text-indigo-600" /> Jadwal Pelajaran Mingguan
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-if="!schoolClass" class="bg-white p-8 rounded-2xl shadow-sm text-center border border-gray-100">
                    <p class="text-gray-500">Anda belum tergabung di kelas mana pun, sehingga jadwal tidak tersedia.</p>
                </div>
                
                <!-- Weekly Schedule Component -->
                <div v-else class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 md:p-8 border-b border-gray-100 bg-white">
                        <h3 class="text-2xl font-bold mb-2 text-slate-800">Jadwal Kelas {{ schoolClass.name }}</h3>
                        <p class="text-slate-500">Silakan lihat jadwal pelajaran Anda berdasarkan hari.</p>
                    </div>
                    
                    <div class="flex border-b border-gray-100 overflow-x-auto bg-gray-50/50">
                        <button 
                            v-for="day in daysOfWeek" 
                            :key="day"
                            @click="activeTab = day"
                            class="flex-1 py-4 px-6 text-sm font-bold transition-all border-b-2 whitespace-nowrap focus:outline-none"
                            :class="activeTab === day ? 'border-indigo-600 text-indigo-700 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                        >
                            {{ day }}
                        </button>
                    </div>

                    <div class="p-0 min-h-[400px]">
                        <div v-for="day in daysOfWeek" :key="'content-'+day" v-show="activeTab === day">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500">
                                        <th class="py-4 px-6 md:px-8 font-semibold w-1/4 border-b border-gray-100">Waktu</th>
                                        <th class="py-4 px-6 md:px-8 font-semibold w-2/4 border-b border-gray-100">Mata Pelajaran</th>
                                        <th class="py-4 px-6 md:px-8 font-semibold w-1/4 border-b border-gray-100">Guru Pengampu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template v-for="schedule in allSchedules.filter(s => s.day === day)" :key="schedule.id">
                                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                                            <td class="py-5 px-6 md:px-8">
                                                <div class="flex items-center gap-2 text-gray-900 font-bold text-sm bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg inline-flex border border-indigo-100 group-hover:bg-white transition-colors">
                                                    <Clock size="16" />
                                                    {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                                                </div>
                                            </td>
                                            <td class="py-5 px-6 md:px-8">
                                                <div class="text-lg font-black text-gray-900">{{ schedule.subject?.name }}</div>
                                            </td>
                                            <td class="py-5 px-6 md:px-8">
                                                <span class="text-gray-600 font-medium flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                                        {{ schedule.teacher?.name.substring(0,2).toUpperCase() || '?' }}
                                                    </div>
                                                    {{ schedule.teacher?.name || 'Belum diatur' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-if="allSchedules.filter(s => s.day === day).length === 0">
                                        <td colspan="3" class="py-16 px-6 text-center text-gray-400">
                                            <Calendar class="h-12 w-12 mx-auto mb-3 opacity-20" />
                                            <p class="text-lg font-medium">Tidak ada jadwal di hari ini.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
