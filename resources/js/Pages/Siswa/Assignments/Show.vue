<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    FileText, Clock, CheckCircle, XCircle, 
    UploadCloud, Star, BookOpen, ArrowLeft,
    CheckSquare
} from 'lucide-vue-next';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';

const props = defineProps({
    assignment: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleString('id-ID', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const submission = computed(() => {
    return (props.assignment.submissions && props.assignment.submissions.length > 0) 
        ? props.assignment.submissions[0] 
        : null;
});

const hasSubmitted = computed(() => submission.value !== null);
const isGraded = computed(() => submission.value !== null && submission.value.grade !== null);

const form = useForm({
    assignment_id: props.assignment.id,
    file: null,
    note: '',
});

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submitTask = () => {
    form.post(route('siswa.submissions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('file', 'note');
        }
    });
};
</script>

<template>
    <Head :title="'Tugas: ' + assignment.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('siswa.assignments')" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <ArrowLeft size="18" /> Kembali ke Tugas Belajar
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Header Tugas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-indigo-500"></div>
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full border border-indigo-100 uppercase tracking-wider">
                                    {{ assignment.subject?.name }}
                                </span>
                                <span class="text-sm font-medium text-gray-500 flex items-center gap-1">
                                    <BookOpen size="16"/> {{ assignment.teacher?.name }}
                                </span>
                            </div>
                            <h2 class="text-3xl font-black text-gray-900 mb-2">{{ assignment.title }}</h2>
                        </div>
                        <div class="shrink-0">
                            <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 flex items-center gap-3">
                                <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">
                                    <Clock size="20" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-orange-600 uppercase tracking-wider">Tenggat Waktu</p>
                                    <p class="text-sm font-bold text-gray-900">{{ formatDate(assignment.due_date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Kiri: Deskripsi Tugas -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <FileText size="20" class="text-gray-500" /> Instruksi Tugas
                                </h3>
                            </div>
                            <div class="p-6 md:p-8">
                                <div class="prose prose-indigo max-w-none text-gray-700 whitespace-pre-line leading-relaxed">
                                    {{ assignment.description }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Status Pengumpulan & Hasil Penilaian -->
                    <div class="space-y-6">
                        
                        <!-- Card Hasil Penilaian (Hanya jika sudah dinilai) -->
                        <div v-if="isGraded" class="bg-white rounded-2xl shadow-sm border border-green-200 overflow-hidden relative">
                            <div class="absolute top-0 inset-x-0 h-1 bg-green-500"></div>
                            <div class="p-6 bg-green-50/30 border-b border-green-100">
                                <h3 class="text-lg font-bold text-green-900 flex items-center gap-2">
                                    <CheckSquare size="20" class="text-green-600" /> Hasil Penilaian
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="flex items-end gap-2 mb-6">
                                    <span class="text-5xl font-black text-gray-900">{{ submission.grade }}</span>
                                    <span class="text-xl font-bold text-gray-400 mb-1">/100</span>
                                </div>
                                
                                <div v-if="submission.feedback" class="bg-gray-50 p-4 rounded-xl border border-gray-200 relative">
                                    <Star class="absolute -top-3 -right-3 text-yellow-400 fill-yellow-400 bg-white rounded-full p-0.5" size="24" />
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Catatan Guru:</p>
                                    <p class="text-sm text-gray-700 italic leading-relaxed">"{{ submission.feedback }}"</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Status Pengumpulan -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <UploadCloud size="20" class="text-indigo-600" /> 
                                    {{ hasSubmitted ? 'Status Pengumpulan' : 'Kumpulkan Tugas' }}
                                </h3>
                                
                                <span v-if="hasSubmitted" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    <CheckCircle size="12" /> Selesai
                                </span>
                                <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    <XCircle size="12" /> Belum
                                </span>
                            </div>
                            
                            <div class="p-6">
                                <div v-if="hasSubmitted" class="mb-6 space-y-3">
                                    <p class="text-sm text-gray-500">Tugas Anda telah dikumpulkan pada:</p>
                                    <p class="text-sm font-bold text-gray-900">{{ formatDate(submission.created_at) }}</p>
                                    
                                    <div v-if="submission.file_path" class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 truncate">File Jawaban</span>
                                        <a :href="'/storage/' + submission.file_path" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800 font-bold">
                                            Lihat File
                                        </a>
                                    </div>
                                    
                                    <div v-if="submission.note" class="mt-4">
                                        <p class="text-xs text-gray-500 font-medium mb-1">Catatan Anda:</p>
                                        <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200">{{ submission.note }}</p>
                                    </div>
                                </div>

                                <!-- Form Upload (Tampil jika belum dikumpul ATAU jika sudah dikumpul tapi BELUM dinilai) -->
                                <div v-if="!isGraded">
                                    <div v-if="hasSubmitted" class="mb-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm font-bold text-indigo-700 mb-2">Revisi Tugas</p>
                                        <p class="text-xs text-gray-500">Anda masih dapat mengubah file jawaban sebelum guru memberikan nilai.</p>
                                    </div>

                                    <form @submit.prevent="submitTask" class="space-y-4">
                                        <div>
                                            <InputLabel for="file" value="File Jawaban (.pdf, .docx, .zip)" />
                                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-500 transition-colors bg-gray-50">
                                                <div class="space-y-1 text-center">
                                                    <UploadCloud class="mx-auto h-12 w-12 text-gray-400" />
                                                    <div class="flex text-sm text-gray-600 justify-center">
                                                        <label for="file" class="relative cursor-pointer rounded-md bg-transparent font-medium text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-500">
                                                            <span>Pilih file</span>
                                                            <input id="file" name="file" type="file" class="sr-only" @change="handleFileChange" required />
                                                        </label>
                                                        <p class="pl-1">atau drag and drop</p>
                                                    </div>
                                                    <p class="text-xs text-gray-500">Maks. 5MB</p>
                                                </div>
                                            </div>
                                            <div v-if="form.file" class="mt-2 text-sm text-green-600 font-medium flex items-center gap-1">
                                                <CheckCircle size="14" /> {{ form.file.name }} siap diunggah.
                                            </div>
                                            <InputError class="mt-2" :message="form.errors.file" />
                                        </div>

                                        <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                                        </div>

                                        <div>
                                            <InputLabel for="note" value="Catatan (Opsional)" />
                                            <textarea
                                                id="note"
                                                rows="3"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm transition-colors"
                                                v-model="form.note"
                                                placeholder="Tambahkan pesan untuk guru..."
                                            ></textarea>
                                            <InputError class="mt-2" :message="form.errors.note" />
                                        </div>

                                        <PrimaryButton
                                            class="w-full justify-center py-3 text-sm font-bold"
                                            :class="{ 'opacity-25': form.processing }"
                                            :disabled="form.processing || !form.file"
                                        >
                                            <span v-if="form.processing">Mengunggah...</span>
                                            <span v-else>{{ hasSubmitted ? 'Kirim Revisi Tugas' : 'Kirim Tugas Sekarang' }}</span>
                                        </PrimaryButton>
                                    </form>
                                </div>
                                <div v-else class="mt-4 pt-4 border-t border-gray-100 text-center">
                                    <p class="text-sm text-gray-500">Tugas ini telah dinilai. Anda tidak dapat mengirimkan revisi lagi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
