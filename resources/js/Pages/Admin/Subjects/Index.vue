<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, BookOpen, Pencil, AlertCircle } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    subjects: Array,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditMode = ref(false);
const currentSubject = ref(null);
const subjectToDelete = ref(null);

const form = useForm({
    name: '',
    code: '',
    description: '',
});

const openModal = (editMode = false, subject = null) => {
    isEditMode.value = editMode;
    if (editMode && subject) {
        currentSubject.value = subject;
        form.name = subject.name;
        form.code = subject.code;
        form.description = subject.description || '';
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
    currentSubject.value = null;
    isEditMode.value = false;
};

const submit = () => {
    if (isEditMode.value && currentSubject.value) {
        form.put(route('admin.subjects.update', currentSubject.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.subjects.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (subject) => {
    subjectToDelete.value = subject;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    subjectToDelete.value = null;
};

const deleteSubject = () => {
    if (subjectToDelete.value) {
        form.delete(route('admin.subjects.destroy', subjectToDelete.value.id), {
            onSuccess: () => closeDeleteModal(),
        });
    }
};
</script>

<template>
    <Head title="Mata Pelajaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <BookOpen size="24" class="text-indigo-600" /> Mata Pelajaran
                </h2>
                <button
                    @click="openModal(false)"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    <Plus size="16" /> Tambah Mapel
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Subjects Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 w-1/6">Kode</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 w-1/3">Nama Mapel</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Deskripsi</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right w-1/6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="subjects.length === 0">
                                    <td colspan="4" class="py-8 text-center text-gray-500 flex flex-col items-center justify-center">
                                        <AlertCircle size="32" class="text-gray-400 mb-2" />
                                        Belum ada data mata pelajaran.
                                    </td>
                                </tr>
                                <tr v-for="subject in subjects" :key="subject.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ subject.code }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-gray-900 text-base">{{ subject.name }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-sm text-gray-600 line-clamp-2" :title="subject.description">
                                            {{ subject.description || '-' }}
                                        </p>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                @click="openModal(true, subject)"
                                                class="text-blue-500 hover:text-blue-700 transition-colors p-2 rounded-lg hover:bg-blue-50 inline-flex items-center justify-center focus:outline-none"
                                                title="Edit"
                                            >
                                                <Pencil size="18" />
                                            </button>
                                            <button
                                                @click="confirmDelete(subject)"
                                                class="text-red-500 hover:text-red-700 transition-colors p-2 rounded-lg hover:bg-red-50 inline-flex items-center justify-center focus:outline-none"
                                                title="Hapus"
                                            >
                                                <Trash2 size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Subject Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <BookOpen class="text-indigo-600" size="24" />
                    {{ isEditMode ? 'Edit Mata Pelajaran' : 'Tambah Mapel Baru' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="code" value="Kode Mapel" />
                        <TextInput
                            id="code"
                            type="text"
                            class="mt-1 block w-full uppercase font-mono text-sm"
                            v-model="form.code"
                            required
                            placeholder="Contoh: MTK-10"
                        />
                        <p class="text-xs text-gray-500 mt-1">Kode harus unik dan tidak boleh sama dengan mapel lain.</p>
                        <InputError class="mt-2" :message="form.errors.code" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Nama Mata Pelajaran" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            placeholder="Contoh: Matematika Wajib"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Deskripsi Singkat (Opsional)" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="Jelaskan secara singkat mengenai pelajaran ini..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ isEditMode ? 'Simpan Perubahan' : 'Tambahkan Mapel' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5 shadow-sm">
                    <Trash2 class="h-8 w-8 text-red-600" stroke-width="2" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    Hapus Mata Pelajaran
                </h2>
                <p class="text-base text-gray-500 mb-8">
                    Apakah Anda yakin ingin menghapus mapel <span class="font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ subjectToDelete?.name }}</span>? Data ini tidak dapat dikembalikan lagi.
                </p>

                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteSubject"
                        class="gap-2"
                    >
                        Ya, Hapus Mapel
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
