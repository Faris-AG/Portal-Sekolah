<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    FileText, Clock, CheckCircle, XCircle, 
    UploadCloud, Star, BookOpen 
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tugas: Array,
});

const activeTab = ref('semua'); // 'semua', 'belum', 'selesai'

const filteredTugas = computed(() => {
    if (!props.tugas) return [];
    
    if (activeTab.value === 'belum') {
        return props.tugas.filter(t => !hasSubmitted(t));
    }
    if (activeTab.value === 'selesai') {
        return props.tugas.filter(t => hasSubmitted(t));
    }
    return props.tugas;
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

// Submission Logic
const isSubmissionModalOpen = ref(false);
const selectedAssignment = ref(null);

const form = useForm({
    assignment_id: '',
    file: null,
    note: '',
});

const openSubmissionModal = (assignment) => {
    selectedAssignment.value = assignment;
    form.assignment_id = assignment.id;
    form.file = null;
    form.note = '';
    form.clearErrors();
    isSubmissionModalOpen.value = true;
};

const closeSubmissionModal = () => {
    isSubmissionModalOpen.value = false;
    selectedAssignment.value = null;
    form.reset();
};

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submitTask = () => {
    form.post(route('siswa.submissions.store'), {
        onSuccess: () => closeSubmissionModal(),
        preserveScroll: true,
    });
};

const hasSubmitted = (assignment) => {
    return assignment.submissions && assignment.submissions.length > 0;
};
</script>

<template>
    <Head title="Tugas Belajar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <BookOpen size="24" class="text-indigo-600" /> Tugas Belajar
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
                    <button 
                        @click="activeTab = 'semua'"
                        class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-colors"
                        :class="activeTab === 'semua' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    >
                        Semua Tugas
                    </button>
                    <button 
                        @click="activeTab = 'belum'"
                        class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-colors flex items-center gap-2"
                        :class="activeTab === 'belum' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    >
                        <XCircle size="16" :class="activeTab === 'belum' ? 'text-red-200' : 'text-red-500'" /> Belum Dikumpulkan
                    </button>
                    <button 
                        @click="activeTab = 'selesai'"
                        class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-colors flex items-center gap-2"
                        :class="activeTab === 'selesai' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    >
                        <CheckCircle size="16" :class="activeTab === 'selesai' ? 'text-green-200' : 'text-green-500'" /> Selesai & Dinilai
                    </button>
                </div>

                <!-- Assignment List -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div v-if="filteredTugas.length === 0" class="text-center py-16 px-4">
                        <FileText class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak ada tugas</h3>
                        <p class="text-gray-500">Belum ada daftar tugas dalam kategori ini.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="tugas in filteredTugas" :key="tugas.id" class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ tugas.subject?.name }}
                                        </span>
                                        <span class="text-xs font-medium text-gray-500">
                                            Guru: {{ tugas.teacher?.name }}
                                        </span>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">{{ tugas.title }}</h4>
                                    <p class="text-sm text-gray-600 mb-4 whitespace-pre-line">{{ tugas.description }}</p>
                                    
                                    <div class="flex flex-wrap items-center gap-4 text-sm font-medium">
                                        <span class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100">
                                            <Clock size="16" /> Deadline: {{ formatDate(tugas.due_date) }}
                                        </span>
                                    </div>

                                    <!-- Feedback Block -->
                                    <div v-if="hasSubmitted(tugas) && tugas.submissions[0].feedback" class="mt-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100 w-full md:w-3/4">
                                        <span class="text-xs font-bold uppercase tracking-wider text-blue-700 mb-2 flex items-center gap-1">
                                            <Star size="14" /> Catatan dari Guru
                                        </span>
                                        <p class="text-sm text-gray-700 italic">"{{ tugas.submissions[0].feedback }}"</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-start lg:items-end gap-3 min-w-[200px] shrink-0">
                                    <div v-if="hasSubmitted(tugas)" class="w-full">
                                        <div class="flex items-center justify-start lg:justify-end gap-2 mb-2">
                                            <div class="inline-flex items-center gap-1.5 text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg text-sm font-bold shadow-sm">
                                                <CheckCircle size="16" /> Selesai
                                            </div>
                                            <div v-if="tugas.submissions[0].grade !== null" class="inline-flex items-center gap-1.5 text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg text-sm font-black shadow-sm text-lg">
                                                {{ tugas.submissions[0].grade }} / 100
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="w-full flex justify-start lg:justify-end mb-2">
                                        <div class="inline-flex items-center gap-1.5 text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg text-sm font-bold shadow-sm">
                                            <XCircle size="16" /> Belum Dikerjakan
                                        </div>
                                    </div>

                                    <Link 
                                        :href="route('siswa.assignments.show', tugas.id)"
                                        class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold transition-all shadow-sm"
                                        :class="hasSubmitted(tugas) 
                                            ? 'bg-white border-2 border-indigo-200 text-indigo-700 hover:bg-indigo-50'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-md'"
                                    >
                                        <UploadCloud size="18" /> 
                                        {{ hasSubmitted(tugas) ? 'Lihat / Revisi Tugas' : 'Kumpulkan Sekarang' }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Modal -->
        <Modal :show="isSubmissionModalOpen" @close="closeSubmissionModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <UploadCloud class="text-indigo-600" size="24" />
                    Kumpulkan Tugas
                </h2>

                <div v-if="selectedAssignment" class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h4 class="font-bold text-gray-900">{{ selectedAssignment.title }}</h4>
                    <p class="text-sm text-gray-600 mt-1">Mata Pelajaran: {{ selectedAssignment.subject?.name }}</p>
                    <p class="text-sm font-bold text-red-600 mt-2 flex items-center gap-1">
                        <Clock size="14" /> Deadline: {{ formatDate(selectedAssignment.due_date) }}
                    </p>
                </div>

                <form @submit.prevent="submitTask" class="space-y-5">
                    <div>
                        <InputLabel for="file" value="Pilih File (.pdf, .docx, .zip, .png, .jpg - Max 5MB)" />
                        <input
                            id="file"
                            type="file"
                            @change="handleFileChange"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg shadow-sm p-1 transition-colors"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.file" />
                    </div>

                    <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                        <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: form.progress.percentage + '%' }"></div>
                    </div>

                    <div>
                        <InputLabel for="note" value="Catatan Tambahan (Opsional)" />
                        <textarea
                            id="note"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors"
                            v-model="form.note"
                            placeholder="Ketik catatan untuk guru di sini..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.note" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeSubmissionModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Mengunggah...</span>
                            <span v-else>Kirim Tugas</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
