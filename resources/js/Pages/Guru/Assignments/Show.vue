<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Book, Clock, ArrowLeft, Download, 
    CheckCircle, XCircle, FileText, UserCheck, Star 
} from 'lucide-vue-next';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    assignment: Object,
    studentsList: Object,
    filters: Object,
});

import { router } from '@inertiajs/vue3';
import { ChevronUp, ChevronDown } from 'lucide-vue-next';

const currentSortBy = ref(props.filters?.sort_by || 'student_name');
const currentDirection = ref(props.filters?.direction || 'asc');
const currentPerPage = ref(props.filters?.per_page || 25);
const searchQuery = ref(props.filters?.search || '');

let searchTimeout = null;

const onSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const sortBy = (column) => {
    if (currentSortBy.value === column) {
        currentDirection.value = currentDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortBy.value = column;
        currentDirection.value = 'asc';
    }
    
    applyFilters();
};

const changePerPage = () => {
    applyFilters();
};

const applyFilters = () => {
    router.get(route('guru.assignments.show', props.assignment.id), {
        sort_by: currentSortBy.value,
        direction: currentDirection.value,
        per_page: currentPerPage.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
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

// Handled by studentsList directly from backend
// Submissions logic merged into studentsList

// Grading Modal
const isGradingModalOpen = ref(false);
const selectedStudent = ref(null);
const selectedSubmission = ref(null);

const form = useForm({
    grade: '',
    feedback: '',
});

const openGradingModal = (student, submission) => {
    selectedStudent.value = student;
    selectedSubmission.value = submission;
    form.grade = submission?.grade || '';
    form.feedback = submission?.feedback || '';
    form.clearErrors();
    isGradingModalOpen.value = true;
};

const closeGradingModal = () => {
    isGradingModalOpen.value = false;
    selectedStudent.value = null;
    selectedSubmission.value = null;
    form.reset();
};

const submitGrade = () => {
    if (selectedSubmission.value) {
        form.post(route('guru.submissions.grade', selectedSubmission.value.id), {
            onSuccess: () => closeGradingModal(),
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="'Penilaian: ' + assignment.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('guru.dashboard')" class="text-gray-500 hover:text-gray-900 transition-colors">
                    <ArrowLeft size="20" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Penilaian Tugas
                </h2>
            </div>
            <div class="flex gap-2">
                <a 
                    :href="route('guru.assignments.export', assignment.id)"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                >
                    <Download size="16" /> Export CSV
                </a>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Assignment Info Card -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row items-start justify-between relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/4"></div>
                    <div>
                        <div class="flex items-center gap-2 mb-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                            <Book size="16" /> {{ assignment.subject?.name }}
                            <span class="mx-2">&bull;</span>
                            <span class="text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ assignment.school_class?.name }}</span>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900">{{ assignment.title }}</h3>
                        <p class="mt-2 text-gray-600 max-w-2xl">{{ assignment.description }}</p>
                    </div>
                    <div class="mt-6 md:mt-0 bg-orange-50 border border-orange-100 px-5 py-3 rounded-2xl flex flex-col items-center md:items-end min-w-[200px]">
                        <span class="text-xs font-bold uppercase tracking-wider text-orange-400 mb-1 flex items-center gap-1">
                            <Clock size="14" /> Tenggat Waktu
                        </span>
                        <span class="font-bold text-orange-700 text-lg text-center md:text-right">
                            {{ formatDate(assignment.due_date) }}
                        </span>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <UserCheck class="text-blue-500" size="20" />
                            <h3 class="text-lg font-bold text-gray-900">Daftar Pengumpulan Siswa</h3>
                        </div>
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <UserCheck size="16" class="text-gray-400" />
                            </div>
                            <input 
                                type="text" 
                                v-model="searchQuery" 
                                @input="onSearch"
                                placeholder="Cari nama siswa..." 
                                class="pl-10 block w-full rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500">
                                    <th @click="sortBy('student_name')" class="py-4 px-6 font-semibold w-1/4 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Nama Siswa
                                            <span v-if="currentSortBy === 'student_name'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                            <span v-else class="text-gray-300 opacity-0 group-hover:opacity-100"><ChevronUp size="14" /></span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 font-semibold w-1/6">Status</th>
                                    <th @click="sortBy('submitted_at')" class="py-4 px-6 font-semibold w-1/4 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Waktu Kumpul
                                            <span v-if="currentSortBy === 'submitted_at'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                        </div>
                                    </th>
                                    <th @click="sortBy('grade')" class="py-4 px-6 font-semibold w-1/12 text-center cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center justify-center gap-1">
                                            Nilai
                                            <span v-if="currentSortBy === 'grade'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="student in studentsList?.data" :key="student.student_id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ student.student_name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ student.student_email }}</div>
                                    </td>
                                    
                                    <td class="py-4 px-6">
                                        <span v-if="student.submission_id" class="inline-flex items-center gap-1.5 text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-md text-xs font-bold">
                                            <CheckCircle size="14" /> Sudah Kumpul
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1.5 text-red-700 bg-red-50 border border-red-200 px-2.5 py-1 rounded-md text-xs font-bold">
                                            <XCircle size="14" /> Belum Kumpul
                                        </span>
                                    </td>

                                    <td class="py-4 px-6">
                                        <div v-if="student.submission_id" class="text-sm font-medium text-gray-600 flex items-center gap-1.5">
                                            <Clock size="14" class="text-gray-400" />
                                            {{ formatDate(student.submitted_at) }}
                                        </div>
                                        <span v-else class="text-gray-400 italic text-sm">-</span>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <div v-if="student.grade !== null && student.grade !== undefined" class="font-bold text-lg text-blue-700">
                                            {{ student.grade }}
                                        </div>
                                        <span v-else class="text-gray-400 font-medium">-</span>
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <div v-if="student.submission_id" class="flex justify-end gap-2">
                                            <a 
                                                :href="route('guru.submissions.download', student.submission_id)" 
                                                target="_blank"
                                                class="flex items-center justify-center p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors"
                                                title="Unduh File"
                                            >
                                                <Download size="18" />
                                            </a>
                                            <button 
                                                @click="openGradingModal({ name: student.student_name }, { id: student.submission_id, grade: student.grade, feedback: student.feedback, note: student.note })"
                                                class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                                            >
                                                <Star size="16" /> Nilai
                                            </button>
                                        </div>
                                        <div v-else class="text-gray-400 text-xs font-medium italic">
                                            Menunggu Siswa
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!studentsList?.data || studentsList.data.length === 0">
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        Belum ada siswa di kelas ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination & Per Page -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between" v-if="studentsList">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Tampilkan:</span>
                            <select v-model="currentPerPage" @change="changePerPage" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-8">
                                <option value="10">10 baris</option>
                                <option value="25">25 baris</option>
                                <option value="50">50 baris</option>
                            </select>
                        </div>
                        
                        <div class="flex gap-1" v-if="studentsList.links">
                            <template v-for="(link, i) in studentsList.links" :key="i">
                                <button
                                    v-if="link.url"
                                    @click="router.get(link.url, { sort_by: currentSortBy, direction: currentDirection, per_page: currentPerPage, search: searchQuery }, { preserveState: true, preserveScroll: true, replace: true })"
                                    class="px-3 py-1 rounded text-sm font-medium transition-colors"
                                    :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50'"
                                    v-html="link.label"
                                ></button>
                                <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label"></span>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Grading Modal -->
        <Modal :show="isGradingModalOpen" @close="closeGradingModal" max-width="md">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <Star class="text-blue-600" size="24" />
                    Beri Nilai: {{ selectedStudent?.name }}
                </h2>

                <div v-if="selectedSubmission?.note" class="mb-6 p-4 bg-yellow-50/50 rounded-lg border border-yellow-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-yellow-600 mb-1 block flex items-center gap-1"><FileText size="12" /> Catatan Siswa:</span>
                    <p class="text-sm text-gray-700 italic">"{{ selectedSubmission.note }}"</p>
                </div>

                <form @submit.prevent="submitGrade" class="space-y-5">
                    <div>
                        <InputLabel for="grade" value="Nilai (0 - 100)" />
                        <input
                            id="grade"
                            type="number"
                            min="0"
                            max="100"
                            class="mt-1 block w-full text-2xl font-bold border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            v-model="form.grade"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.grade" />
                    </div>

                    <div>
                        <InputLabel for="feedback" value="Feedback / Umpan Balik (Opsional)" />
                        <textarea
                            id="feedback"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            v-model="form.feedback"
                            placeholder="Tulis saran perbaikan untuk siswa..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.feedback" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeGradingModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700"
                        >
                            Simpan Nilai
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
