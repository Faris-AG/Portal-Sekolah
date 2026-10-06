<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Library, Plus, Trash2, Link as LinkIcon, FileText, File, AlertCircle
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    materials: Array,
    classes: Array,
    subjects: Array,
});

const isCreateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const materialToDelete = ref(null);

const form = useForm({
    title: '',
    school_class_id: '',
    subject_id: props.subjects.length === 1 ? props.subjects[0].id : '',
    description: '',
    file: null,
    link_url: '',
});

const openCreateModal = () => {
    form.reset();
    form.subject_id = props.subjects.length === 1 ? props.subjects[0].id : '';
    form.clearErrors();
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submitMaterial = () => {
    form.post(route('guru.materials.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const openDeleteModal = (material) => {
    materialToDelete.value = material;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    setTimeout(() => {
        materialToDelete.value = null;
    }, 300);
};

const deleteMaterial = () => {
    if (!materialToDelete.value) return;
    
    form.delete(route('guru.materials.destroy', materialToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Materi Belajar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Library size="24" class="text-indigo-600" /> Kelola Materi Belajar
                </h2>
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                >
                    <Plus size="16" /> Tambah Materi
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div v-if="materials.length === 0" class="text-center py-16 px-4">
                        <Library class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada materi belajar</h3>
                        <p class="text-gray-500">Klik tombol "Tambah Materi" untuk mengunggah bahan ajar ke kelas.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Materi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas & Mapel</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe Lampiran</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Diunggah</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="material in materials" :key="material.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ material.title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-1">{{ material.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-indigo-700 bg-indigo-50 inline-block px-2 py-0.5 rounded border border-indigo-100 mb-1">
                                            {{ material.school_class?.name }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-medium">
                                            {{ material.subject?.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1.5">
                                            <span v-if="material.file_path" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <FileText size="12" /> Berkas
                                            </span>
                                            <span v-if="material.link_url" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                <LinkIcon size="12" /> Tautan
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(material.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openDeleteModal(material)" class="text-red-600 hover:text-red-900 font-bold p-2 hover:bg-red-50 rounded-lg transition-colors">
                                            <Trash2 size="18" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="isCreateModalOpen" @close="closeCreateModal" max-width="lg">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-6">
                    <Library size="24" class="text-indigo-600" />
                    <h2 class="text-xl font-bold text-gray-900">Tambah Materi Baru</h2>
                </div>

                <form @submit.prevent="submitMaterial" class="space-y-5">
                    <div>
                        <InputLabel for="title" value="Judul Materi" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            required
                            placeholder="Contoh: Modul 1 - Persamaan Linear"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="school_class_id" value="Untuk Kelas" />
                            <select
                                id="school_class_id"
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
                        <div>
                            <InputLabel for="subject_id" value="Mata Pelajaran" />
                            <select
                                id="subject_id"
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
                        <InputLabel for="description" value="Deskripsi Singkat (Opsional)" />
                        <textarea
                            id="description"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm resize-none"
                            v-model="form.description"
                            placeholder="Tambahkan penjelasan singkat tentang materi ini..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl space-y-4">
                        <p class="text-sm font-bold text-gray-700">Pilih Lampiran (Wajib isi minimal salah satu)</p>
                        
                        <div>
                            <InputLabel for="file" value="Unggah Berkas (PDF, Word, PPT, ZIP)" />
                            <input
                                id="file"
                                type="file"
                                @change="handleFileChange"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg p-1 bg-white"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.zip"
                            />
                            <InputError class="mt-2" :message="form.errors.file" />
                            <p class="text-xs text-gray-400 mt-1">Maks. 10MB</p>
                        </div>
                        
                        <div class="flex items-center gap-3 w-full">
                            <hr class="flex-1 border-gray-300">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">ATAU</span>
                            <hr class="flex-1 border-gray-300">
                        </div>

                        <div>
                            <InputLabel for="link_url" value="Tautan Eksternal (URL)" />
                            <TextInput
                                id="link_url"
                                type="url"
                                class="mt-1 block w-full bg-white"
                                v-model="form.link_url"
                                placeholder="Contoh: https://youtube.com/watch?v=..."
                            />
                            <InputError class="mt-2" :message="form.errors.link_url" />
                        </div>
                    </div>

                    <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeCreateModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ form.processing ? 'Mengunggah...' : 'Simpan Materi' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4 mx-auto">
                    <AlertCircle class="text-red-600" size="24" />
                </div>
                <h2 class="text-lg font-bold text-center text-gray-900 mb-2">Hapus Materi?</h2>
                <p class="text-sm text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus materi <span class="font-bold">"{{ materialToDelete?.title }}"</span>? Tindakan ini tidak dapat dibatalkan dan akan menghapus berkas dari server.
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal">Batal</SecondaryButton>
                    <button 
                        @click="deleteMaterial"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
