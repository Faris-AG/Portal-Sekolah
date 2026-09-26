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
});

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

const students = props.assignment.school_class?.students || [];
const submissions = props.assignment.submissions || [];

const getSubmissionForStudent = (studentId) => {
    return submissions.find(sub => sub.student_id === studentId);
};

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
                    <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                        <UserCheck class="text-blue-500" size="20" />
                        <h3 class="text-lg font-bold text-gray-900">Daftar Pengumpulan Siswa</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500">
                                    <th class="py-4 px-6 font-semibold w-1/4">Nama Siswa</th>
                                    <th class="py-4 px-6 font-semibold w-1/6">Status</th>
                                    <th class="py-4 px-6 font-semibold w-1/4">Waktu Kumpul</th>
                                    <th class="py-4 px-6 font-semibold w-1/12 text-center">Nilai</th>
                                    <th class="py-4 px-6 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ student.name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ student.email }}</div>
                                    </td>
                                    
                                    <td class="py-4 px-6">
                                        <span v-if="getSubmissionForStudent(student.id)" class="inline-flex items-center gap-1.5 text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-md text-xs font-bold">
                                            <CheckCircle size="14" /> Sudah Kumpul
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1.5 text-red-700 bg-red-50 border border-red-200 px-2.5 py-1 rounded-md text-xs font-bold">
                                            <XCircle size="14" /> Belum Kumpul
                                        </span>
                                    </td>

                                    <td class="py-4 px-6">
                                        <div v-if="getSubmissionForStudent(student.id)" class="text-sm font-medium text-gray-600 flex items-center gap-1.5">
                                            <Clock size="14" class="text-gray-400" />
                                            {{ formatDate(getSubmissionForStudent(student.id).submitted_at) }}
                                        </div>
                                        <span v-else class="text-gray-400 italic text-sm">-</span>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <div v-if="getSubmissionForStudent(student.id)?.grade !== null && getSubmissionForStudent(student.id)?.grade !== undefined" class="font-bold text-lg text-blue-700">
                                            {{ getSubmissionForStudent(student.id).grade }}
                                        </div>
                                        <span v-else class="text-gray-400 font-medium">-</span>
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <div v-if="getSubmissionForStudent(student.id)" class="flex justify-end gap-2">
                                            <a 
                                                :href="route('guru.submissions.download', getSubmissionForStudent(student.id).id)" 
                                                target="_blank"
                                                class="flex items-center justify-center p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors"
                                                title="Unduh File"
                                            >
                                                <Download size="18" />
                                            </a>
                                            <button 
                                                @click="openGradingModal(student, getSubmissionForStudent(student.id))"
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
                                <tr v-if="students.length === 0">
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        Belum ada siswa di kelas ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
