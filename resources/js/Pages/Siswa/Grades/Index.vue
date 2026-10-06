<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    Award, 
    BookOpen, 
    TrendingUp, 
    CheckSquare,
    ChevronDown,
    ChevronUp
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    gradesBySubject: Object,
    statistics: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric'
    });
};

const getGradeBadgeColor = (grade) => {
    if (grade >= 85) return 'bg-green-100 text-green-800 border-green-200';
    if (grade >= 75) return 'bg-blue-100 text-blue-800 border-blue-200';
    if (grade >= 60) return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    return 'bg-red-100 text-red-800 border-red-200';
};

const expandedSubjects = ref(
    Object.keys(props.gradesBySubject).reduce((acc, subject) => {
        acc[subject] = true; // Buka semua secara default
        return acc;
    }, {})
);

const toggleSubject = (subjectName) => {
    expandedSubjects.value[subjectName] = !expandedSubjects.value[subjectName];
};
</script>

<template>
    <Head title="Nilai Saya" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <Award size="24" class="text-indigo-600" /> Nilai Saya
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Statistik Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Rata-rata Keseluruhan</p>
                            <p class="text-3xl font-black text-gray-900">{{ statistics.average }}</p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                            <TrendingUp size="24" />
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Total Dinilai</p>
                            <p class="text-3xl font-black text-gray-900">{{ statistics.total }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                            <CheckSquare size="24" />
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Nilai Tertinggi</p>
                            <p class="text-3xl font-black text-gray-900">{{ statistics.highest }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center">
                            <Award size="24" />
                        </div>
                    </div>
                </div>

                <div v-if="Object.keys(gradesBySubject).length === 0" class="text-center py-16 px-4 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <BookOpen class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada nilai</h3>
                    <p class="text-gray-500">Nilai akan muncul setelah tugas Anda diperiksa oleh guru.</p>
                </div>

                <!-- Daftar Nilai per Mata Pelajaran -->
                <div v-else class="space-y-6">
                    <div v-for="(assignments, subjectName) in gradesBySubject" :key="subjectName" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        
                        <button @click="toggleSubject(subjectName)" class="w-full px-6 py-4 flex items-center justify-between bg-slate-50 hover:bg-slate-100 transition-colors border-b border-gray-100 focus:outline-none">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                                    <BookOpen size="20" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">{{ subjectName }}</h3>
                            </div>
                            <div class="flex items-center gap-4 text-gray-500">
                                <span class="text-sm font-medium">{{ assignments.length }} Tugas</span>
                                <ChevronUp v-if="expandedSubjects[subjectName]" size="20" />
                                <ChevronDown v-else size="20" />
                            </div>
                        </button>

                        <div v-show="expandedSubjects[subjectName]" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/2">Judul Tugas</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Dinilai</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Skor Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-50">
                                    <tr v-for="(item, index) in assignments" :key="index" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900 mb-1">{{ item.assignment_title }}</div>
                                            <div v-if="item.feedback" class="text-sm text-gray-600 italic bg-gray-50 p-2 rounded border border-gray-100 inline-block mt-1">
                                                Catatan Guru: "{{ item.feedback }}"
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(item.graded_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold border" :class="getGradeBadgeColor(item.grade)">
                                                {{ item.grade }}
                                            </span>
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
