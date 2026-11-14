<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    Megaphone, Plus, Trash2, Pencil,
    X, AlertCircle, Users, GraduationCap,
    Search, RotateCcw, Printer, Eye, SearchX
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    announcements: Object,
    filters: Object,
});

// State Filter
const search = ref(props.filters?.search || '');
const target_role = ref(props.filters?.target_role || '');
const start_date = ref(props.filters?.start_date || '');
const end_date = ref(props.filters?.end_date || '');
const per_page = ref(props.filters?.per_page || 10);

// State Modal
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const editingId = ref(null);
const selectedAnnouncement = ref(null);
const detailAnnouncement = ref(null);

// Form Create / Edit
const form = useForm({
    title: '',
    content: '',
    target_role: 'all',
});

// Normalisasi list data
const announcementList = computed(() => {
    if (props.announcements && Array.isArray(props.announcements.data)) {
        return props.announcements.data;
    }
    if (Array.isArray(props.announcements)) {
        return props.announcements;
    }
    return [];
});

// Cek filter aktif
const hasActiveFilters = computed(() => {
    return Boolean(search.value || target_role.value || start_date.value || end_date.value);
});

// URL Export Rekap PDF sesuai filter aktif
const exportRekapUrl = computed(() => {
    const params = {};
    if (search.value) params.search = search.value;
    if (target_role.value) params.target_role = target_role.value;
    if (start_date.value) params.start_date = start_date.value;
    if (end_date.value) params.end_date = end_date.value;
    return route('admin.announcements.print-rekap', params);
});

// Terapkan filter ke server
const applyFilters = () => {
    router.get(
        route('admin.announcements.index'),
        {
            search: search.value ? search.value : undefined,
            target_role: target_role.value ? target_role.value : undefined,
            start_date: start_date.value ? start_date.value : undefined,
            end_date: end_date.value ? end_date.value : undefined,
            per_page: per_page.value !== 10 ? per_page.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Debounce 300ms untuk pencarian
let searchDebounceTimeout = null;
watch(search, (newVal, oldVal) => {
    if (newVal === oldVal) return;
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const onFilterChange = () => {
    applyFilters();
};

const goToPage = (url) => {
    if (!url) return;
    router.get(
        url,
        {
            search: search.value || undefined,
            target_role: target_role.value || undefined,
            start_date: start_date.value || undefined,
            end_date: end_date.value || undefined,
            per_page: per_page.value !== 10 ? per_page.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    search.value = '';
    target_role.value = '';
    start_date.value = '';
    end_date.value = '';
    per_page.value = 10;
    router.get(
        route('admin.announcements.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Handler Modal Create / Edit
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

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => form.reset(), 300);
};

// Handler Modal Detail Preview
const openDetailModal = (announcement) => {
    detailAnnouncement.value = announcement;
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    setTimeout(() => {
        detailAnnouncement.value = null;
    }, 300);
};

// Handler Modal Delete
const openDeleteModal = (announcement) => {
    selectedAnnouncement.value = announcement;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    setTimeout(() => {
        selectedAnnouncement.value = null;
    }, 300);
};

// Submit form
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

// Helpers
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getTargetBadgeColor = (role) => {
    if (role === 'all') return 'bg-purple-50 text-purple-700 border-purple-200';
    if (role === 'guru') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    return 'bg-amber-50 text-amber-700 border-amber-200';
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
        <!-- 1. Header Bersih 1 Baris dengan Tombol Utama Title Case -->
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Megaphone size="24" class="text-indigo-600" /> Kelola Pengumuman
                </h2>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    <Plus size="16" /> Buat Pengumuman
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- 2. Bar Pencarian, Filter & Export PDF (Tanpa Container Kotak Putih) -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3 flex-1">
                        <!-- Input Pencarian -->
                        <div class="relative min-w-[220px] max-w-xs flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search size="16" class="text-gray-400" />
                            </div>
                            <input 
                                type="text" 
                                v-model="search" 
                                placeholder="Cari judul atau isi..." 
                                class="pl-10 block w-full rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>

                        <!-- Dropdown Sasaran -->
                        <div class="w-auto min-w-[160px]">
                            <select 
                                v-model="target_role" 
                                @change="onFilterChange"
                                class="block w-full rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Semua Sasaran</option>
                                <option value="all">Semua Pengguna</option>
                                <option value="guru">Khusus Guru</option>
                                <option value="siswa">Khusus Siswa</option>
                            </select>
                        </div>

                        <!-- Filter Rentang Tanggal -->
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <input 
                                type="date" 
                                v-model="start_date" 
                                @change="onFilterChange"
                                title="Dari Tanggal"
                                class="rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 px-3"
                            >
                            <span class="text-gray-400">s/d</span>
                            <input 
                                type="date" 
                                v-model="end_date" 
                                @change="onFilterChange"
                                title="Sampai Tanggal"
                                class="rounded-md border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 px-3"
                            >
                        </div>

                        <!-- Tombol Reset Filter -->
                        <div v-if="hasActiveFilters" class="flex items-center">
                            <button 
                                @click="resetFilters"
                                type="button"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 transition-colors flex items-center gap-1"
                            >
                                <RotateCcw size="14" /> Reset Filter
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Export Rekap PDF (Sejajar di Sisi Kanan Bar Filter) -->
                    <div>
                        <a 
                            :href="exportRekapUrl" 
                            target="_blank"
                            class="flex items-center gap-2 px-3.5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm whitespace-nowrap"
                            title="Export Rekap PDF sesuai filter aktif"
                        >
                            <Printer size="16" /> Export Rekap PDF
                        </a>
                    </div>
                </div>

                <!-- 3. Tabel Data Pengumuman -->
                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">
                                        Judul Pengumuman
                                    </th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">
                                        Sasaran
                                    </th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100">
                                        Tanggal Terbit
                                    </th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-center">
                                        Pembaca
                                    </th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr 
                                    v-for="announcement in announcementList" 
                                    :key="announcement.id" 
                                    class="hover:bg-gray-50/50 transition-colors"
                                >
                                    <!-- Judul (Bisa diklik untuk preview detail) & Cuplikan Isi -->
                                    <td class="py-4 px-6">
                                        <button 
                                            type="button"
                                            @click="openDetailModal(announcement)"
                                            class="text-left font-medium text-gray-900 hover:text-indigo-600 transition-colors cursor-pointer block"
                                            title="Lihat Detail"
                                        >
                                            {{ announcement.title }}
                                        </button>
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-md">
                                            {{ announcement.content }}
                                        </div>
                                    </td>

                                    <!-- Sasaran Target Badge -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border"
                                            :class="getTargetBadgeColor(announcement.target_role)"
                                        >
                                            <component :is="getTargetIcon(announcement.target_role)" size="13" />
                                            {{ getTargetLabel(announcement.target_role) }}
                                        </span>
                                    </td>

                                    <!-- Tanggal Terbit & Pembuat -->
                                    <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-500">
                                        <div class="font-medium text-gray-900">{{ formatDate(announcement.created_at) }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Oleh: {{ announcement.user?.name || 'Administrator' }}</div>
                                    </td>

                                    <!-- Indikator Jumlah Pembaca -->
                                    <td class="py-4 px-6 whitespace-nowrap text-center">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100"
                                            title="Jumlah pengguna yang telah membaca"
                                        >
                                            <Eye size="13" class="text-blue-500" />
                                            <span>{{ announcement.readers_count || 0 }} Dibaca</span>
                                        </span>
                                    </td>

                                    <!-- 4. Kolom Aksi Lega dengan Ikon Minimalis Berwarna -->
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="flex justify-end items-center gap-2">
                                            <!-- Ikon Detail (Mata) -->
                                            <button 
                                                @click="openDetailModal(announcement)"
                                                class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="Lihat Detail"
                                            >
                                                <Eye size="18" />
                                            </button>

                                            <!-- Ikon Cetak Surat PDF (Printer Hijau/Emerald) -->
                                            <a 
                                                :href="route('admin.announcements.print-letter', announcement.id)"
                                                target="_blank"
                                                class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors inline-flex items-center"
                                                title="Cetak Surat PDF"
                                            >
                                                <Printer size="18" />
                                            </a>

                                            <!-- Ikon Edit (Pensil Biru) -->
                                            <button 
                                                @click="openEditModal(announcement)"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                title="Edit Pengumuman"
                                            >
                                                <Pencil size="18" />
                                            </button>

                                            <!-- Ikon Hapus (Tempat Sampah Merah) -->
                                            <button 
                                                @click="openDeleteModal(announcement)"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus Pengumuman"
                                            >
                                                <Trash2 size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="announcementList.length === 0">
                                    <td colspan="5" class="py-12 text-center text-gray-500">
                                        <template v-if="hasActiveFilters">
                                            <SearchX class="mx-auto h-12 w-12 text-gray-300 mb-2" />
                                            <p class="font-medium text-gray-700">Tidak ada pengumuman yang sesuai dengan filter pencarian.</p>
                                            <button 
                                                @click="resetFilters" 
                                                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-medium text-xs rounded-lg transition-colors border border-indigo-200"
                                            >
                                                <RotateCcw size="14" /> Reset Filter
                                            </button>
                                        </template>
                                        <template v-else>
                                            Belum ada data pengumuman.
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 5. Footer Pagination & Dropdown Per Page Seragam -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Tampilkan:</span>
                            <select 
                                v-model="per_page" 
                                @change="onFilterChange" 
                                class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5 pl-3 pr-8"
                            >
                                <option :value="10">10 baris</option>
                                <option :value="25">25 baris</option>
                                <option :value="50">50 baris</option>
                            </select>
                        </div>

                        <div class="flex gap-1" v-if="props.announcements?.links">
                            <template v-for="(link, i) in props.announcements.links" :key="i">
                                <button
                                    v-if="link.url"
                                    @click="goToPage(link.url)"
                                    class="px-3 py-1 rounded text-sm font-medium transition-colors"
                                    :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50'"
                                    v-html="link.label"
                                ></button>
                                <span 
                                    v-else 
                                    class="px-3 py-1 rounded text-sm text-gray-400 bg-white border border-gray-200 opacity-60 cursor-not-allowed" 
                                    v-html="link.label"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Detail Preview -->
        <Modal :show="isDetailModalOpen" @close="closeDetailModal" max-width="2xl">
            <div class="p-6">
                <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span 
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold border"
                                :class="getTargetBadgeColor(detailAnnouncement?.target_role)"
                            >
                                <component :is="getTargetIcon(detailAnnouncement?.target_role)" size="12" />
                                {{ getTargetLabel(detailAnnouncement?.target_role) }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <Eye size="12" class="text-blue-500" />
                                {{ detailAnnouncement?.readers_count || 0 }} Telah Membaca
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 leading-snug">
                            {{ detailAnnouncement?.title }}
                        </h2>
                        <div class="text-xs text-gray-400 mt-1">
                            Diterbitkan oleh <strong class="text-gray-600">{{ detailAnnouncement?.user?.name || 'Administrator' }}</strong> • {{ formatDate(detailAnnouncement?.created_at) }}
                        </div>
                    </div>
                    <button @click="closeDetailModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <X size="22" />
                    </button>
                </div>

                <div class="my-5">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Isi Pengumuman:
                    </label>
                    <div class="bg-gray-50/80 border border-gray-200/80 rounded-xl p-4 text-sm text-gray-800 whitespace-pre-wrap leading-relaxed max-h-[50vh] overflow-y-auto">
                        {{ detailAnnouncement?.content }}
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100">
                    <div class="text-xs text-gray-400">
                        ID Pengumuman: #{{ detailAnnouncement?.id }}
                    </div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <SecondaryButton @click="closeDetailModal" type="button">
                            Tutup
                        </SecondaryButton>
                        <a 
                            v-if="detailAnnouncement"
                            :href="route('admin.announcements.print-letter', detailAnnouncement.id)"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-lg shadow-sm transition-colors"
                        >
                            <Printer size="15" /> Cetak Surat Edaran (PDF)
                        </a>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Tambah / Edit -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <Megaphone size="20" class="text-indigo-600" />
                        {{ editingId ? 'Edit Pengumuman' : 'Tambah Pengumuman Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <X size="22" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="title" value="Judul Pengumuman" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full rounded-md"
                            v-model="form.title"
                            placeholder="Contoh: Jadwal Ujian Akhir Semester Genap 2026/2027"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="target_role" value="Sasaran Pengumuman" />
                        <select
                            id="target_role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
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
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm resize-none text-sm"
                            placeholder="Tuliskan isi pengumuman secara lengkap dan jelas..."
                            v-model="form.content"
                            required
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.content" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan Pengumuman') }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Konfirmasi Hapus -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4 mx-auto">
                    <AlertCircle class="text-red-600" size="24" />
                </div>
                <h2 class="text-lg font-semibold text-center text-gray-900 mb-2">Hapus Pengumuman?</h2>
                <p class="text-sm text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus pengumuman <span class="font-bold text-gray-800">"{{ selectedAnnouncement?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal">Batal</SecondaryButton>
                    <button 
                        @click="deleteAnnouncement"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
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
