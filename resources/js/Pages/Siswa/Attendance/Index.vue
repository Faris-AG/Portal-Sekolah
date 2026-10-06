<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    Calendar, CheckCircle, XCircle, 
    AlertCircle, Clock, Info
} from 'lucide-vue-next';

const props = defineProps({
    student: Object,
    attendances: Array,
    attendanceStats: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleString('id-ID', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric'
    });
};

const getStatusColor = (status) => {
    switch(status) {
        case 'hadir': return 'bg-green-100 text-green-800 border-green-200';
        case 'sakit': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'izin': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'alpa': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusIcon = (status) => {
    switch(status) {
        case 'hadir': return CheckCircle;
        case 'sakit': return Info;
        case 'izin': return Clock;
        case 'alpa': return XCircle;
        default: return AlertCircle;
    }
};
</script>

<template>
    <Head title="Riwayat Kehadiran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <Calendar size="24" class="text-indigo-600" /> Riwayat Kehadiran
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-3">
                            <CheckCircle size="24" />
                        </div>
                        <p class="text-3xl font-black text-gray-900">{{ attendanceStats.hadir }}</p>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mt-1">Hadir</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-3">
                            <Info size="24" />
                        </div>
                        <p class="text-3xl font-black text-gray-900">{{ attendanceStats.sakit }}</p>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mt-1">Sakit</p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mb-3">
                            <Clock size="24" />
                        </div>
                        <p class="text-3xl font-black text-gray-900">{{ attendanceStats.izin }}</p>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mt-1">Izin</p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center mb-3">
                            <XCircle size="24" />
                        </div>
                        <p class="text-3xl font-black text-gray-900">{{ attendanceStats.alpa }}</p>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mt-1">Alpa</p>
                    </div>
                </div>

                <!-- History List -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Detail Riwayat Harian</h3>
                    </div>
                    
                    <div v-if="attendances.length === 0" class="text-center py-16 px-4">
                        <Calendar class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada data kehadiran</h3>
                        <p class="text-gray-500">Data kehadiran akan muncul setelah guru mengisi absensi.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="att in attendances" :key="att.id" class="p-4 sm:px-6 hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-gray-100">
                                    <component :is="getStatusIcon(att.status)" size="20" class="text-gray-700" />
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ formatDate(att.date) }}</p>
                                    <p v-if="att.note" class="text-sm text-gray-500 mt-0.5">Catatan: {{ att.note }}</p>
                                </div>
                            </div>
                            
                            <div class="shrink-0">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border"
                                    :class="getStatusColor(att.status)">
                                    {{ att.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
