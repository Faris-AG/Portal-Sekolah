<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, GraduationCap, LayoutList, Pencil, AlertCircle } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    classes: Array,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditMode = ref(false);
const currentClass = ref(null);
const classToDelete = ref(null);

const form = useForm({
    name: '',
    grade_level: '',
    wali_kelas: '',
});

const openModal = (editMode = false, cls = null) => {
    isEditMode.value = editMode;
    if (editMode && cls) {
        currentClass.value = cls;
        form.name = cls.name;
        form.grade_level = cls.grade_level;
        form.wali_kelas = cls.wali_kelas || '';
    } else {
        form.reset();
        form.wali_kelas = '';
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
    currentClass.value = null;
    isEditMode.value = false;
};

const submit = () => {
    if (isEditMode.value && currentClass.value) {
        form.put(route('admin.classes.update', currentClass.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.classes.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (cls) => {
    classToDelete.value = cls;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    classToDelete.value = null;
};

const deleteClass = () => {
    if (classToDelete.value) {
        form.delete(route('admin.classes.destroy', classToDelete.value.id), {
            onSuccess: () => closeDeleteModal(),
        });
    }
};
</script>

<template>
    <Head title="Manajemen Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <LayoutList size="24" class="text-indigo-600" /> Manajemen Kelas
                </h2>
                <button
                    @click="openModal(false)"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    <Plus size="16" /> Tambah Kelas
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Classes Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Nama Kelas</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Tingkat</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Wali Kelas</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="classes.length === 0">
                                    <td colspan="4" class="py-8 text-center text-gray-500 flex flex-col items-center justify-center">
                                        <AlertCircle size="32" class="text-gray-400 mb-2" />
                                        Belum ada data kelas. Silakan tambahkan kelas baru.
                                    </td>
                                </tr>
                                <tr v-for="cls in classes" :key="cls.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-gray-900 text-base">{{ cls.name }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                                            <GraduationCap size="14" /> Kelas {{ cls.grade_level }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span v-if="cls.wali_kelas" class="font-medium text-gray-700">{{ cls.wali_kelas }}</span>
                                        <span v-else class="text-gray-400 italic text-sm">Belum ditentukan</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="route('admin.classes.show', cls.id)"
                                                class="text-indigo-500 hover:text-indigo-700 transition-colors p-2 rounded-lg hover:bg-indigo-50 inline-flex items-center justify-center focus:outline-none"
                                                title="Detail"
                                            >
                                                <LayoutList size="18" />
                                            </Link>
                                            <button
                                                @click="openModal(true, cls)"
                                                class="text-blue-500 hover:text-blue-700 transition-colors p-2 rounded-lg hover:bg-blue-50 inline-flex items-center justify-center focus:outline-none"
                                                title="Edit"
                                            >
                                                <Pencil size="18" />
                                            </button>
                                            <button
                                                @click="confirmDelete(cls)"
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

        <!-- Add/Edit Class Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <LayoutList class="text-indigo-600" size="24" />
                    {{ isEditMode ? 'Edit Kelas' : 'Tambah Kelas Baru' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="name" value="Nama Kelas" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            placeholder="Contoh: X MIPA 1"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="grade_level" value="Tingkat Kelas" />
                        <select
                            id="grade_level"
                            v-model="form.grade_level"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>-- Pilih Tingkat --</option>
                            <option value="10">Kelas 10</option>
                            <option value="11">Kelas 11</option>
                            <option value="12">Kelas 12</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.grade_level" />
                    </div>

                    <div>
                        <InputLabel for="wali_kelas" value="Wali Kelas" />
                        <TextInput
                            id="wali_kelas"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.wali_kelas"
                            placeholder="Contoh: Bapak Budi Santoso"
                        />
                        <InputError class="mt-2" :message="form.errors.wali_kelas" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ isEditMode ? 'Simpan Perubahan' : 'Tambahkan Kelas' }}
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
                    Hapus Data Kelas
                </h2>
                <p class="text-base text-gray-500 mb-8">
                    Apakah Anda yakin ingin menghapus kelas <span class="font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ classToDelete?.name }}</span>? Data ini tidak dapat dikembalikan lagi setelah dihapus.
                </p>

                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteClass"
                        class="gap-2"
                    >
                        Ya, Hapus Kelas
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
