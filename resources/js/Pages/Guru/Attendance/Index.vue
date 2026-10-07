<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { CheckSquare, Search, FileText } from 'lucide-vue-next';

const props = defineProps({
    classes: Array,
    subjects: Array,
    report: Array,
    filters: Object,
});

const form = ref({
    school_class_id: props.filters.school_class_id || '',
    subject_id: props.filters.subject_id || '',
    month: props.filters.month || new Date().toISOString().slice(0, 7),
});

const fetchReport = () => {
    if (!form.value.school_class_id) return;
    
    router.get(
        route('guru.attendance.index'),
        form.value,
        { preserveState: true, preserveScroll: true }
    );
};

watch(
    () => [form.value.school_class_id, form.value.subject_id, form.value.month],
    () => {
        fetchReport();
    }
);
</script>

<template>
    <Head title="Laporan Presensi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <CheckSquare size="24" class="text-indigo-600" /> Laporan Presensi Siswa
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Search size="20" class="text-gray-400" /> Filter Laporan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                            <select 
                                v-model="form.school_class_id" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih Kelas</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.name }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran (Opsional)</label>
                            <select 
                                v-model="form.subject_id" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Semua Mata Pelajaran</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                            <input 
                                type="month" 
                                v-model="form.month"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div v-if="form.school_class_id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Rekapitulasi Kehadiran</h3>
                        <div v-if="report.length > 0" class="text-sm text-gray-500">
                            Total Siswa: {{ report.length }}
                        </div>
                    </div>
                    
                    <div v-if="report.length === 0" class="p-8 text-center text-gray-500">
                        Tidak ada data siswa di kelas ini.
                    </div>
                    
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="p-4 font-semibold text-gray-600 text-sm">Nama Siswa</th>
                                    <th class="p-4 font-semibold text-green-600 text-sm text-center">Hadir</th>
                                    <th class="p-4 font-semibold text-blue-600 text-sm text-center">Sakit</th>
                                    <th class="p-4 font-semibold text-yellow-600 text-sm text-center">Izin</th>
                                    <th class="p-4 font-semibold text-red-600 text-sm text-center">Alpa</th>
                                    <th class="p-4 font-semibold text-gray-600 text-sm text-center">Total PTM</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in report" :key="item.student.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-medium text-gray-900">{{ item.student.name }}</div>
                                        <div class="text-xs text-gray-500">{{ item.student.nis || item.student.email }}</div>
                                    </td>
                                    <td class="p-4 text-center font-bold text-green-600 bg-green-50/30">{{ item.hadir }}</td>
                                    <td class="p-4 text-center font-bold text-blue-600 bg-blue-50/30">{{ item.sakit }}</td>
                                    <td class="p-4 text-center font-bold text-yellow-600 bg-yellow-50/30">{{ item.izin }}</td>
                                    <td class="p-4 text-center font-bold text-red-600 bg-red-50/30">{{ item.alpa }}</td>
                                    <td class="p-4 text-center font-bold text-gray-700 bg-gray-50">{{ item.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div v-else class="text-center py-12 px-4 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <FileText class="mx-auto h-12 w-12 text-gray-300 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900">Pilih Kelas</h3>
                    <p class="mt-1 text-gray-500">Silakan pilih kelas terlebih dahulu untuk melihat rekap kehadiran.</p>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>
