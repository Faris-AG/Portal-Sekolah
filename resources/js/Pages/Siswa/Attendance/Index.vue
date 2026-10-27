<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Calendar, CheckCircle, XCircle, 
    AlertCircle, Clock, Filter, ChevronLeft, ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    student: Object,
    attendances: Object, // Laravel Paginated Object
    attendanceStats: Object,
    monthOptions: Array,
    filters: Object,
});

const selectedMonth = ref(props.filters?.month || '');

const onFilterChange = () => {
    router.get(route('siswa.attendance'), {
        month: selectedMonth.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

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
        case 'sakit': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'izin': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'alpa': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusIconBg = (status) => {
    switch(status) {
        case 'hadir': return 'bg-green-50 text-green-600';
        case 'sakit': return 'bg-amber-50 text-amber-600';
        case 'izin': return 'bg-blue-50 text-blue-600';
        case 'alpa': return 'bg-red-50 text-red-600';
        default: return 'bg-gray-100 text-gray-600';
    }
};

const getStatusIcon = (status) => {
    switch(status) {
        case 'hadir': return CheckCircle;
        case 'sakit': return AlertCircle;
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

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <!-- Widget Ringkasan Semester Ini -->
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Ringkasan Kehadiran Semester Ini</h3>
                            <p class="text-xs text-gray-500" v-if="attendanceStats?.semester">
                                Periode: {{ attendanceStats.semester }}
                            </p>
                        </div>
                        <span class="inline-flex items-center self-start sm:self-auto px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-semibold border border-indigo-100">
                            Total: {{ attendanceStats?.total || 0 }} Rekaman
                        </span>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Hadir (Hijau) -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-100/80 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center shrink-0 border border-green-100">
                                <CheckCircle size="24" />
                            </div>
                            <div>
                                <p class="text-3xl font-black text-gray-900 leading-none">{{ attendanceStats?.hadir || 0 }}</p>
                                <p class="text-xs font-bold text-green-700 uppercase tracking-wider mt-1.5">Hadir</p>
                            </div>
                        </div>
                        
                        <!-- Sakit (Kuning/Oranye) -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-amber-100/80 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0 border border-amber-100">
                                <AlertCircle size="24" />
                            </div>
                            <div>
                                <p class="text-3xl font-black text-gray-900 leading-none">{{ attendanceStats?.sakit || 0 }}</p>
                                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mt-1.5">Sakit</p>
                            </div>
                        </div>

                        <!-- Izin (Biru) -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100/80 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0 border border-blue-100">
                                <Clock size="24" />
                            </div>
                            <div>
                                <p class="text-3xl font-black text-gray-900 leading-none">{{ attendanceStats?.izin || 0 }}</p>
                                <p class="text-xs font-bold text-blue-700 uppercase tracking-wider mt-1.5">Izin</p>
                            </div>
                        </div>

                        <!-- Alpa / Tanpa Keterangan (Merah) -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-rose-100/80 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center shrink-0 border border-red-100">
                                <XCircle size="24" />
                            </div>
                            <div>
                                <p class="text-3xl font-black text-gray-900 leading-none">{{ attendanceStats?.alpa || 0 }}</p>
                                <p class="text-xs font-bold text-red-700 uppercase tracking-wider mt-1.5">Alpa / Tanpa Ket.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History List & Filter Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Card Header with Filter -->
                    <div class="p-5 sm:px-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Detail Riwayat Harian</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar presensi dan catatan absensi pembelajaran</p>
                        </div>
                        
                        <!-- Filter Bulan/Tahun Dropdown -->
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <Filter size="16" class="text-gray-400 shrink-0" />
                            <label for="month-filter" class="text-sm font-medium text-gray-600 whitespace-nowrap">Bulan:</label>
                            <select
                                id="month-filter"
                                v-model="selectedMonth"
                                @change="onFilterChange"
                                class="text-sm border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5 pl-3 pr-8 bg-gray-50 hover:bg-white transition-colors cursor-pointer"
                            >
                                <option value="">Semua Bulan</option>
                                <option v-for="opt in monthOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-if="!attendances.data || attendances.data.length === 0" class="text-center py-16 px-4">
                        <Calendar class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <h4 class="text-base font-bold text-gray-900 mb-1">Belum ada data kehadiran</h4>
                        <p class="text-gray-500 text-sm max-w-sm mx-auto">
                            {{ selectedMonth ? 'Tidak ada data kehadiran yang tercatat pada bulan yang dipilih.' : 'Data kehadiran akan muncul setelah guru mengisi absensi.' }}
                        </p>
                        <button
                            v-if="selectedMonth"
                            @click="selectedMonth = ''; onFilterChange();"
                            class="mt-4 inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors"
                        >
                            Reset Filter Bulan
                        </button>
                    </div>

                    <!-- Attendance List -->
                    <div v-else class="divide-y divide-gray-100">
                        <div 
                            v-for="att in attendances.data" 
                            :key="att.id" 
                            class="p-4 sm:px-6 hover:bg-gray-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                        >
                            <div class="flex items-start sm:items-center gap-4">
                                <div 
                                    class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                                    :class="getStatusIconBg(att.status)"
                                >
                                    <component :is="getStatusIcon(att.status)" size="22" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-bold text-gray-900 text-sm sm:text-base">{{ formatDate(att.date) }}</p>
                                        <span v-if="att.subject" class="inline-flex items-center text-xs px-2 py-0.5 rounded-md font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ att.subject.name }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 mt-1">
                                        <span v-if="att.teacher" class="flex items-center gap-1">
                                            <span class="text-gray-400">Guru:</span> {{ att.teacher.name }}
                                        </span>
                                        <span v-if="att.note" class="italic text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                            "{{ att.note }}"
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="self-end sm:self-center shrink-0">
                                <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider border shadow-2xs"
                                    :class="getStatusColor(att.status)">
                                    {{ att.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination Controls -->
                    <div 
                        v-if="attendances.data && attendances.data.length > 0" 
                        class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row items-center justify-between gap-3"
                    >
                        <div class="text-xs sm:text-sm text-gray-500">
                            Menampilkan <span class="font-semibold text-gray-900">{{ attendances.from || 0 }}</span> - <span class="font-semibold text-gray-900">{{ attendances.to || 0 }}</span> dari <span class="font-semibold text-gray-900">{{ attendances.total || 0 }}</span> data
                        </div>
                        
                        <div class="flex items-center gap-1.5 flex-wrap justify-center" v-if="attendances.links && attendances.links.length > 3">
                            <template v-for="(link, i) in attendances.links" :key="i">
                                <button
                                    v-if="link.url"
                                    @click="goToPage(link.url)"
                                    class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors"
                                    :class="link.active ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100'"
                                    v-html="link.label"
                                ></button>
                                <span
                                    v-else
                                    class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium text-gray-300 bg-gray-50 border border-gray-100 cursor-not-allowed"
                                    v-html="link.label"
                                ></span>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
