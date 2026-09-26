<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Users, Plus, Pencil, Trash2, Key } from 'lucide-vue-next';

const props = defineProps({
    teachers: Array,
    subjects: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedTeacher = ref(null);

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

const deleteTeacher = (teacher) => {
    if (confirm(`Yakin ingin menghapus guru ${teacher.name}?`)) {
        form.delete(route('admin.teachers.destroy', teacher.id));
    }
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
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Nama Guru</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Email</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Spesialisasi Mapel</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="teacher in teachers" :key="teacher.id" class="hover:bg-gray-50/50 transition-colors">
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
                                                @click="deleteTeacher(teacher)"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <Trash2 size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="teachers.length === 0">
                                    <td colspan="4" class="py-8 text-center text-gray-500">
                                        Belum ada data guru.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

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
    </AuthenticatedLayout>
</template>
