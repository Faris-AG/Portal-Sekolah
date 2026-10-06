<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Users, Plus, Pencil, Trash2, Key, AlertCircle, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    teachers: Object,
    subjects: Array,
    filters: Object,
});

const currentSortBy = ref(props.filters.sort_by || 'name');
const currentDirection = ref(props.filters.direction || 'asc');
const currentPerPage = ref(props.filters.per_page || 10);

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
    router.get(route('admin.teachers.index'), {
        sort_by: currentSortBy.value,
        direction: currentDirection.value,
        per_page: currentPerPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const isModalOpen = ref(false);
const isEditMode = ref(false);
const isDeleteModalOpen = ref(false);
const selectedTeacher = ref(null);
const teacherToDelete = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    subject_id: '',
});

const openModal = (editMode = false, teacher = null) => {
    isEditMode.value = editMode;
    selectedTeacher.value = teacher;
    
    if (editMode && teacher) {
        form.name = teacher.name;
        form.email = teacher.email;
        form.subject_id = teacher.subject_id || '';
        form.password = '';
        form.password_confirmation = '';
    } else {
        form.reset();
        form.password = 'password';
        form.password_confirmation = 'password';
    }
    
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(route('admin.teachers.update', selectedTeacher.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.teachers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const openDeleteModal = (teacher) => {
    teacherToDelete.value = teacher;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    setTimeout(() => { teacherToDelete.value = null; }, 300);
};

const deleteTeacher = () => {
    if (!teacherToDelete.value) return;
    form.delete(route('admin.teachers.destroy', teacherToDelete.value.id), {
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Manajemen Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Users size="24" class="text-indigo-600" /> Manajemen Guru
                </h2>
                <button
                    @click="openModal(false)"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    <Plus size="16" /> Tambah Guru
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th @click="sortBy('name')" class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Nama Guru
                                            <span v-if="currentSortBy === 'name'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                            <span v-else class="text-gray-300 opacity-0 group-hover:opacity-100"><ChevronUp size="14" /></span>
                                        </div>
                                    </th>
                                    <th @click="sortBy('email')" class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Email
                                            <span v-if="currentSortBy === 'email'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                        </div>
                                    </th>
                                    <th @click="sortBy('subject_id')" class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Spesialisasi Mapel
                                            <span v-if="currentSortBy === 'subject_id'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Status Wali Kelas</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="teacher in teachers.data" :key="teacher.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                                {{ teacher.name.substring(0, 2).toUpperCase() }}
                                            </div>
                                            <span class="font-medium text-gray-900">{{ teacher.name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">
                                        {{ teacher.email }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span v-if="teacher.subject" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ teacher.subject.name }}
                                        </span>
                                        <span v-else class="text-gray-400 italic text-sm">Belum ditentukan</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span v-if="teacher.wali_class" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            Wali Kelas {{ teacher.wali_class.name }}
                                        </span>
                                        <span v-else class="text-gray-400 text-center">-</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button 
                                                @click="openModal(true, teacher)"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <Pencil size="18" />
                                            </button>
                                            <button 
                                                @click="openDeleteModal(teacher)"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <Trash2 size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="teachers.data.length === 0">
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        Belum ada data guru.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination & Per Page -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Tampilkan:</span>
                            <select v-model="currentPerPage" @change="changePerPage" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-8">
                                <option value="10">10 baris</option>
                                <option value="25">25 baris</option>
                                <option value="50">50 baris</option>
                            </select>
                        </div>
                        
                        <div class="flex gap-1" v-if="teachers.links">
                            <template v-for="(link, i) in teachers.links" :key="i">
                                <button
                                    v-if="link.url"
                                    @click="router.get(link.url, { sort_by: currentSortBy, direction: currentDirection, per_page: currentPerPage }, { preserveState: true, preserveScroll: true })"
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

        <!-- Add/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    {{ isEditMode ? 'Edit Guru' : 'Tambah Guru Baru' }}
                </h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Lengkap (beserta gelar)" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Alamat Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="subject_id" value="Spesialisasi Mata Pelajaran (Opsional)" />
                        <select
                            id="subject_id"
                            v-model="form.subject_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.subject_id" />
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 mt-4">
                        <h4 class="text-sm font-bold text-yellow-800 flex items-center gap-2 mb-2"><Key size="16"/> Pengaturan Password</h4>
                        <div v-if="!isEditMode" class="text-sm text-yellow-700">
                            Password default otomatis diisi: <strong>password</strong>
                        </div>
                        <div v-else class="text-sm text-yellow-700 mb-3">
                            Kosongkan jika tidak ingin mengubah password.
                        </div>

                        <div v-show="!isEditMode || isEditMode" class="space-y-4">
                            <div>
                                <InputLabel for="password" value="Password Baru" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password_confirmation"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ isEditMode ? 'Simpan Perubahan' : 'Tambahkan' }}
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
                <h2 class="text-xl font-bold text-gray-900 mb-2">Hapus Data Guru</h2>
                <p class="text-base text-gray-500 mb-8">
                    Apakah Anda yakin ingin menghapus guru <span class="font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ teacherToDelete?.name }}</span>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTeacher"
                    >
                        Ya, Hapus Guru
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
