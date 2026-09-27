<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Calendar, Clock, CheckSquare, BookOpen, GraduationCap, 
    Plus, Trash2, FileText, AlertCircle 
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    schedules: Array,
    assignments: Array,
    classes: Array,
    subjects: Array,
});

const user = usePage().props.auth.user;

// Summaries
const totalClassesTaught = computed(() => {
    const classIds = props.schedules.map(s => s.school_class_id);
    return new Set(classIds).size;
});

const activeAssignments = computed(() => {
    return props.assignments.length; // Can be enhanced by checking due_date > now
});

const formatTime = (time) => time ? time.substring(0, 5) : '';

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
    subject_id: '',
    due_date: '',
    description: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};

const submitAssignment = () => {
    form.post(route('guru.assignments.store'), {
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
            onSuccess: () => closeDeleteModal(),
        });
    }
};
</script>

<template>
    <Head title="Dashboard Guru" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Guru
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Welcome Banner -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ user.name }}</h3>
                        <p class="text-gray-500 mt-1">Semoga hari ini penuh inspirasi dan semangat mengajar.</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-5 hover:shadow-md transition-all">
                        <div class="p-4 bg-indigo-50 text-indigo-600 rounded-2xl">
                            <GraduationCap size="32" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Kelas Diampu</p>
                            <h4 class="text-3xl font-bold text-gray-900 mt-1">{{ totalClassesTaught }} <span class="text-lg font-medium text-gray-400">kelas</span></h4>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-5 hover:shadow-md transition-all">
                        <div class="p-4 bg-orange-50 text-orange-600 rounded-2xl">
                            <CheckSquare size="32" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Tugas Aktif</p>
                            <h4 class="text-3xl font-bold text-gray-900 mt-1">{{ activeAssignments }} <span class="text-lg font-medium text-gray-400">tugas</span></h4>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Teaching Schedule -->
                    <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">
                        <div class="p-5 border-b border-gray-100 flex items-center gap-2 bg-gray-50/50 rounded-t-2xl">
                            <Calendar class="text-indigo-600" size="20" />
                            <h3 class="text-lg font-bold text-gray-900">Jadwal Mengajar Saya</h3>
                        </div>
                        <div class="p-0 flex-1 overflow-y-auto max-h-[600px]">
                            <div v-if="schedules.length === 0" class="text-gray-400 text-center py-12 px-4 flex flex-col items-center">
                                <Calendar size="40" class="mb-3 opacity-20" />
                                <p>Belum ada jadwal mengajar yang ditentukan untuk Anda.</p>
                            </div>
                            <div v-else class="divide-y divide-gray-50">
                                <div v-for="schedule in schedules" :key="schedule.id" class="p-5 hover:bg-gray-50/50 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            {{ schedule.day }}
                                        </span>
                                        <div class="text-xs font-bold text-gray-500 flex items-center gap-1">
                                            <Clock size="12" /> {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-base">{{ schedule.subject?.name }}</h4>
                                    <p class="text-sm font-medium text-gray-500 mt-1 flex items-center gap-1.5">
                                        <GraduationCap size="14" /> {{ schedule.school_class?.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignments List -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <FileText class="text-blue-600" size="20" />
                                <h3 class="text-lg font-bold text-gray-900">Daftar Tugas Diberikan</h3>
                            </div>
                            <button
                                @click="openCreateModal"
                                class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                            >
                                <Plus size="16" /> Buat Tugas Baru
                            </button>
                        </div>
                        <div class="p-0 flex-1">
                            <div v-if="assignments.length === 0" class="text-gray-400 text-center py-16 flex flex-col items-center">
                                <CheckSquare size="48" class="mb-4 opacity-20" />
                                <p>Belum ada tugas yang Anda berikan.</p>
                                <p class="text-sm mt-1">Klik tombol di atas untuk membuat tugas pertama.</p>
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500">
                                            <th class="py-4 px-6 font-semibold w-1/3">Judul Tugas</th>
                                            <th class="py-4 px-6 font-semibold w-1/4">Kelas & Mapel</th>
                                            <th class="py-4 px-6 font-semibold w-1/4">Tenggat Waktu</th>
                                            <th class="py-4 px-6 font-semibold text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="assignment in assignments" :key="assignment.id" class="hover:bg-gray-50/50 transition-colors group">
                                            <td class="py-4 px-6">
                                                <div class="font-bold text-gray-900">
                                                    <Link :href="route('guru.assignments.show', assignment.id)" class="hover:text-blue-600 transition-colors">
                                                        {{ assignment.title }}
                                                    </Link>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1 line-clamp-1" :title="assignment.description">{{ assignment.description }}</div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-sm font-bold text-indigo-700 bg-indigo-50 inline-block px-2 py-0.5 rounded border border-indigo-100 mb-1">{{ assignment.school_class?.name }}</div>
                                                <div class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                                    <BookOpen size="12" /> {{ assignment.subject?.name }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-sm text-gray-700 font-medium flex items-center gap-1.5">
                                                    <Clock size="14" class="text-orange-500" />
                                                    {{ formatDate(assignment.due_date) }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <button
                                                    @click="openDeleteModal(assignment)"
                                                    class="text-red-400 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50 inline-flex items-center justify-center focus:outline-none opacity-0 group-hover:opacity-100"
                                                    title="Hapus"
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

            </div>
        </div>

        <!-- Create Assignment Modal -->
        <Modal :show="isCreateModalOpen" @close="closeCreateModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <FileText class="text-blue-600" size="24" />
                    Buat Tugas Baru
                </h2>

                <form @submit.prevent="submitAssignment" class="space-y-5">
                    <div>
                        <InputLabel for="title" value="Judul Tugas" />
                        <input
                            id="title"
                            type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            v-model="form.title"
                            required
                            placeholder="Contoh: Makalah Sejarah Kemerdekaan"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="school_class_id" value="Tugaskan ke Kelas" />
                            <select
                                id="school_class_id"
                                v-model="form.school_class_id"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Kelas --</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.school_class_id" />
                        </div>
                        <div>
                            <InputLabel for="subject_id" value="Mata Pelajaran" />
                            <select
                                id="subject_id"
                                v-model="form.subject_id"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
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
                        <InputLabel for="due_date" value="Tenggat Waktu Pengumpulan (Deadline)" />
                        <input
                            id="due_date"
                            type="datetime-local"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            v-model="form.due_date"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.due_date" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Deskripsi & Instruksi" />
                        <textarea
                            id="description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
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
                            class="bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:ring-blue-500"
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
                    Apakah Anda yakin ingin menghapus tugas <span class="font-bold text-gray-900">{{ assignmentToDelete?.title }}</span>? Semua jawaban siswa mungkin akan terpengaruh.
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
