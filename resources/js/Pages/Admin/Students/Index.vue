<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Users, BookOpen, AlertCircle, RefreshCw } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    students: Array,
    classes: Array,
});

const isModalOpen = ref(false);
const currentStudent = ref(null);

const form = useForm({
    class_id: '',
});

const openModal = (student) => {
    currentStudent.value = student;
    form.class_id = student.class_id || '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
    currentStudent.value = null;
};

const submit = () => {
    if (currentStudent.value) {
        form.patch(route('admin.students.update-class', currentStudent.value.id), {
            onSuccess: () => closeModal(),
        });
    }
};
</script>

<template>
    <Head title="Manajemen Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Users size="24" class="text-indigo-600" /> Daftar Siswa & Penempatan Kelas
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Students Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Nama Siswa</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Email</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">Kelas Saat Ini</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="students.length === 0">
                                    <td colspan="4" class="py-8 text-center text-gray-500 flex flex-col items-center justify-center">
                                        <AlertCircle size="32" class="text-gray-400 mb-2" />
                                        Belum ada data siswa yang terdaftar.
                                    </td>
                                </tr>
                                <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-gray-900 text-base">{{ student.name }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">
                                        {{ student.email }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span v-if="student.school_class" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            {{ student.school_class.name }}
                                        </span>
                                        <span v-else class="text-gray-400 italic text-sm">
                                            Belum memiliki kelas
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <button
                                            @click="openModal(student)"
                                            class="text-blue-600 hover:text-blue-800 transition-colors py-1.5 px-3 rounded-lg hover:bg-blue-50 inline-flex items-center gap-1.5 text-sm font-medium focus:outline-none"
                                        >
                                            <RefreshCw size="16" /> Ubah Kelas
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Class Modal -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="md">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <BookOpen class="text-indigo-600" size="24" />
                    Atur Kelas Siswa
                </h2>
                
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">Siswa:</p>
                    <p class="font-bold text-gray-900">{{ currentStudent?.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="class_id" value="Pilih Kelas" />
                        <select
                            id="class_id"
                            v-model="form.class_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">-- Tidak Ada Kelas --</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }} (Tingkat {{ cls.grade_level }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.class_id" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
