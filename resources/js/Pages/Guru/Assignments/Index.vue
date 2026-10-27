<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    FileText, Plus, Trash2, Clock, BookOpen, GraduationCap, 
    CheckSquare, AlertCircle, Users
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    assignments: Array,
    classes: Array,
    subjects: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const currentClass = ref(props.filters?.class_id || '');
const currentSubject = ref(props.filters?.subject_id || '');
let searchTimeout = null;

const onSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const onFilterChange = () => {
    applyFilters();
};

const resetFilters = () => {
    searchQuery.value = '';
    currentClass.value = '';
    currentSubject.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(route('guru.assignments.index'), {
        search: searchQuery.value,
        class_id: currentClass.value,
        subject_id: currentSubject.value,
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

// Modals
const isCreateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const assignmentToDelete = ref(null);

const form = useForm({
    title: '',
    school_class_id: '',
    subject_id: props.subjects && props.subjects.length === 1 ? props.subjects[0].id : '',
    due_date: '',
    description: '',
});

const openCreateModal = () => {
    form.reset();
    form.subject_id = props.subjects && props.subjects.length === 1 ? props.subjects[0].id : '';
    form.clearErrors();
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};

const submitAssignment = () => {
    form.post(route('guru.assignments.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const openDeleteModal = (assignment) => {
    assignmentToDelete.value = assignment;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    assignmentToDelete.value = null;
};

const deleteAssignment = () => {
    if (assignmentToDelete.value) {
        form.delete(route('guru.assignments.destroy', assignmentToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
        });
    }
};
</script>

<template>
    <Head title="Kelola Tugas Belajar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-gray-800 flex items-center gap-2">
                    <CheckSquare size="24" class="text-indigo-600" />
                    Kelola Tugas Siswa
                </h2>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all shadow-indigo-100"
                >
                    <Plus size="18" /> Buat Tugas Baru
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row gap-4 mb-2">
                    <div class="w-full relative" :class="subjects && subjects.length > 1 ? 'sm:w-1/3' : 'sm:w-1/2'">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <FileText size="16" class="text-gray-400" />
                        </div>
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            @input="onSearch"
                            placeholder="Cari judul tugas..." 
                            class="pl-10 block w-full rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div class="w-full" :class="subjects && subjects.length > 1 ? 'sm:w-1/4' : 'sm:w-1/3'">
                        <select 
                            v-model="currentClass" 
                            @change="onFilterChange"
                            class="block w-full rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>
                    <!-- Dropdown filter Mapel HANYA ditampilkan jika guru mengajar lebih dari 1 mapel -->
                    <div v-if="subjects && subjects.length > 1" class="w-full sm:w-1/4">
                        <select 
                            v-model="currentSubject" 
                            @change="onFilterChange"
                            class="block w-full rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Semua Mapel</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <button 
                            v-if="searchQuery || currentClass || (subjects && subjects.length > 1 && currentSubject)" 
                            @click="resetFilters"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 transition-colors"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div v-if="assignments.length === 0" class="text-center py-16 px-4">
                        <CheckSquare class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada tugas</h3>
                        <p class="text-gray-500">Klik tombol "Buat Tugas Baru" untuk menugaskan latihan atau tugas ke kelas.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tugas</th>
                                    <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas & Mapel</th>
                                    <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tenggat Waktu</th>
                                    <th scope="col" class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Terkumpul</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="assignment in assignments" :key="assignment.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">
                                            <Link :href="route('guru.assignments.show', assignment.id)" class="hover:text-indigo-600 transition-colors">
                                                {{ assignment.title }}
                                            </Link>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-md" :title="assignment.description">
                                            {{ assignment.description }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mb-1">
                                            <GraduationCap size="13" /> {{ assignment.school_class?.name }}
                                        </span>
                                        <div class="text-xs text-gray-500 font-medium flex items-center gap-1 mt-0.5">
                                            <BookOpen size="12" /> {{ assignment.subject?.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-2.5 py-1 rounded-md border border-orange-100 w-fit text-xs font-semibold">
                                            <Clock size="13" /> {{ formatDate(assignment.due_date) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100">
                                            <Users size="13" /> {{ assignment.submissions_count || 0 }} pengumpulan
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <Link
                                            :href="route('guru.assignments.show', assignment.id)"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors border border-indigo-200"
                                        >
                                            Periksa & Nilai
                                        </Link>
                                        <button 
                                            @click="openDeleteModal(assignment)"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center"
                                            title="Hapus Tugas"
                                        >
                                            <Trash2 size="16" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Assignment Modal -->
        <Modal :show="isCreateModalOpen" @close="closeCreateModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <FileText class="text-indigo-600" size="24" />
                    Buat Tugas Baru
                </h2>

                <form @submit.prevent="submitAssignment" class="space-y-5">
                    <div>
                        <InputLabel for="create_title" value="Judul Tugas" />
                        <input
                            id="create_title"
                            type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                            v-model="form.title"
                            required
                            placeholder="Contoh: Latihan Pemrograman Percabangan"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': subjects && subjects.length > 1 }">
                        <div>
                            <InputLabel for="create_school_class_id" value="Tugaskan ke Kelas" />
                            <select
                                id="create_school_class_id"
                                v-model="form.school_class_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Kelas --</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.school_class_id" />
                        </div>
                        <div v-if="subjects && subjects.length > 1">
                            <InputLabel for="create_subject_id" value="Mata Pelajaran" />
                            <select
                                id="create_subject_id"
                                v-model="form.subject_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Mapel --</option>
                                <option v-for="sub in subjects" :key="sub.id" :value="sub.id">
                                    {{ sub.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.subject_id" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="create_due_date" value="Tenggat Waktu Pengumpulan (Deadline)" />
                        <input
                            id="create_due_date"
                            type="datetime-local"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                            v-model="form.due_date"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.due_date" />
                    </div>

                    <div>
                        <InputLabel for="create_description" value="Deskripsi & Instruksi" />
                        <textarea
                            id="create_description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                            v-model="form.description"
                            required
                            placeholder="Berikan instruksi pengerjaan yang jelas..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeCreateModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700"
                        >
                            Sebarkan Tugas
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5 shadow-sm">
                    <AlertCircle class="h-8 w-8 text-red-600" stroke-width="2" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    Hapus Tugas
                </h2>
                <p class="text-base text-gray-500 mb-8">
                    Apakah Anda yakin ingin menghapus tugas <span class="font-bold text-gray-900">{{ assignmentToDelete?.title }}</span>? Semua jawaban siswa pada tugas ini akan ikut terhapus.
                </p>

                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteAssignment"
                    >
                        Ya, Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
