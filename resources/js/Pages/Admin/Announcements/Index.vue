<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Megaphone, Plus, Edit, Trash2, 
    X, AlertCircle, Users, GraduationCap
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    announcements: Array,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const editingId = ref(null);
const selectedAnnouncement = ref(null);

const form = useForm({
    title: '',
    content: '',
    target_role: 'all',
});

const openCreateModal = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (announcement) => {
    editingId.value = announcement.id;
    form.title = announcement.title;
    form.content = announcement.content;
    form.target_role = announcement.target_role;
    form.clearErrors();
    isModalOpen.value = true;
};

const openDeleteModal = (announcement) => {
    selectedAnnouncement.value = announcement;
    isDeleteModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => form.reset(), 300);
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    setTimeout(() => {
        selectedAnnouncement.value = null;
    }, 300);
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.announcements.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.announcements.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAnnouncement = () => {
    if (!selectedAnnouncement.value) return;
    
    form.delete(route('admin.announcements.destroy', selectedAnnouncement.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getTargetBadgeColor = (role) => {
    if (role === 'all') return 'bg-purple-100 text-purple-800 border-purple-200';
    if (role === 'guru') return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    return 'bg-orange-100 text-orange-800 border-orange-200';
};

const getTargetLabel = (role) => {
    if (role === 'all') return 'Semua Pengguna';
    if (role === 'guru') return 'Khusus Guru';
    return 'Khusus Siswa';
};

const getTargetIcon = (role) => {
    if (role === 'all') return Megaphone;
    if (role === 'guru') return GraduationCap;
    return Users;
};
</script>

<template>
    <Head title="Kelola Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Megaphone size="24" class="text-indigo-600" /> Kelola Pengumuman
                </h2>
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                >
                    <Plus size="16" /> Buat Pengumuman
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div v-if="announcements.length === 0" class="text-center py-16 px-4">
                        <Megaphone class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada pengumuman</h3>
                        <p class="text-gray-500">Klik tombol "Buat Pengumuman" untuk menambahkan informasi baru.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Pengumuman</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sasaran (Target)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Terbit</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="announcement in announcements" :key="announcement.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ announcement.title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-1">{{ announcement.content }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border" :class="getTargetBadgeColor(announcement.target_role)">
                                            <component :is="getTargetIcon(announcement.target_role)" size="12" />
                                            {{ getTargetLabel(announcement.target_role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(announcement.created_at) }}
                                        <div class="text-xs text-gray-400 mt-0.5">Oleh: {{ announcement.user?.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openEditModal(announcement)" class="text-indigo-600 hover:text-indigo-900 mr-4 font-bold">Edit</button>
                                        <button @click="openDeleteModal(announcement)" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingId ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <X size="24" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="title" value="Judul Pengumuman" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="target_role" value="Sasaran Pengumuman" />
                        <select
                            id="target_role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                            v-model="form.target_role"
                            required
                        >
                            <option value="all">Semua Pengguna (Guru & Siswa)</option>
                            <option value="guru">Khusus Guru</option>
                            <option value="siswa">Khusus Siswa</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.target_role" />
                    </div>

                    <div>
                        <InputLabel for="content" value="Isi Pengumuman" />
                        <textarea
                            id="content"
                            rows="6"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm resize-none"
                            v-model="form.content"
                            required
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.content" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengumuman' }}
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
                <h2 class="text-lg font-bold text-center text-gray-900 mb-2">Hapus Pengumuman?</h2>
                <p class="text-sm text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus pengumuman <span class="font-bold">"{{ selectedAnnouncement?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal">Batal</SecondaryButton>
                    <button 
                        @click="deleteAnnouncement"
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
