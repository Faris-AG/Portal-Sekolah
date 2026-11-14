<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    Megaphone, Plus, Edit, Trash2, 
    X, AlertCircle, Users, GraduationCap,
    Search, Calendar, RotateCcw, Printer,
    Eye, SearchX, FileText, CheckCircle2
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

// State Filter Terpadu
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

// Normalisasi data array pengumuman (mendukung pagination object maupun raw array)
const announcementList = computed(() => {
    if (props.announcements && Array.isArray(props.announcements.data)) {
        return props.announcements.data;
    }
    if (Array.isArray(props.announcements)) {
        return props.announcements;
    }
    return [];
});

// Cek apakah ada filter yang sedang aktif
const hasActiveFilters = computed(() => {
    return Boolean(search.value || target_role.value || start_date.value || end_date.value);
});

// URL Mode 1: Export Rekap PDF sesuai filter yang sedang aktif
const exportRekapUrl = computed(() => {
    const params = {};
    if (search.value) params.search = search.value;
    if (target_role.value) params.target_role = target_role.value;
    if (start_date.value) params.start_date = start_date.value;
    if (end_date.value) params.end_date = end_date.value;
    return route('admin.announcements.print-rekap', params);
});

// Terapkan filter ke server via Inertia router
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

// Debounce 300ms untuk pencarian teks
let searchDebounceTimeout = null;
watch(search, (newVal, oldVal) => {
    if (newVal === oldVal) return;
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

// Filter dropdown dan tanggal langsung trigger request
const onFilterChange = () => {
    applyFilters();
};

// Reset seluruh filter
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

// Eksekusi Submit Form
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

// Utility format tanggal & badge
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
        <!-- Header Halaman & Aksi Utama -->
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900 flex items-center gap-2">
                        <Megaphone size="24" class="text-indigo-600" /> Kelola Pengumuman
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Publikasi informasi, pantau jumlah pembaca, dan cetak dokumen resmi sekolah
                    </p>
                </div>
                
                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- MODE 1: Tombol Export Rekap PDF -->
                    <a 
                        :href="exportRekapUrl" 
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition-colors"
                        title="Cetak seluruh daftar pengumuman sesuai filter aktif"
                    >
                        <Printer size="16" /> Export Rekap PDF
                    </a>

                    <!-- Tombol Buat Pengumuman -->
                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-900 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <Plus size="16" /> Buat Pengumuman
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50/60 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-5">
                
                <!-- 1. BARIS PENCARIAN & FILTER TERPADU -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                        
                        <!-- Input Search (Debounce 300ms) -->
                        <div class="md:col-span-4 relative">
                            <Search size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
                            <input 
                                v-model="search"
                                type="text"
                                placeholder="Cari judul atau isi pengumuman..."
                                class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50/70 border border-gray-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                            />
                        </div>

                        <!-- Dropdown Sasaran (Target Role) -->
                        <div class="md:col-span-3">
                            <select 
                                v-model="target_role"
                                @change="onFilterChange"
                                class="w-full py-2 px-3 text-sm bg-gray-50/70 border border-gray-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                            >
                                <option value="">Semua Sasaran</option>
                                <option value="all">Semua Pengguna (Guru & Siswa)</option>
                                <option value="guru">Khusus Guru</option>
                                <option value="siswa">Khusus Siswa</option>
                            </select>
                        </div>

                        <!-- Rentang Tanggal Terbit -->
                        <div class="md:col-span-4 flex items-center gap-2">
                            <div class="relative flex-1">
                                <input 
                                    v-model="start_date"
                                    @change="onFilterChange"
                                    type="date"
                                    title="Dari Tanggal"
                                    class="w-full py-2 px-2.5 text-xs bg-gray-50/70 border border-gray-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                                />
                            </div>
                            <span class="text-xs text-gray-400 font-bold">s/d</span>
                            <div class="relative flex-1">
                                <input 
                                    v-model="end_date"
                                    @change="onFilterChange"
                                    type="date"
                                    title="Sampai Tanggal"
                                    class="w-full py-2 px-2.5 text-xs bg-gray-50/70 border border-gray-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                                />
                            </div>
                        </div>

                        <!-- Tombol Reset Filter -->
                        <div class="md:col-span-1 flex justify-end">
                            <button 
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                type="button"
                                class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition-colors"
                                title="Kembalikan tabel ke kondisi awal"
                            >
                                <RotateCcw size="14" /> Reset
                            </button>
                        </div>

                    </div>
                </div>

                <!-- 2. TABEL DATA PENGUMUMAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- EMPTY STATE: Saat tidak ada data -->
                    <div v-if="announcementList.length === 0" class="text-center py-16 px-4">
                        <template v-if="hasActiveFilters">
                            <SearchX class="mx-auto h-14 w-14 text-gray-300 mb-3" />
                            <h3 class="text-base font-bold text-gray-900 mb-1">Tidak ada pengumuman yang sesuai</h3>
                            <p class="text-sm text-gray-500 mb-4 max-w-md mx-auto">
                                Tidak ditemukan pengumuman dengan kata kunci atau filter tanggal yang Anda tentukan.
                            </p>
                            <button 
                                @click="resetFilters" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold text-xs rounded-xl transition-colors border border-indigo-200"
                            >
                                <RotateCcw size="14" /> Reset Filter Pencarian
                            </button>
                        </template>
                        <template v-else>
                            <Megaphone class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada pengumuman</h3>
                            <p class="text-gray-500 mb-4">Klik tombol "Buat Pengumuman" untuk mempublikasikan informasi baru.</p>
                            <button 
                                @click="openCreateModal"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-colors"
                            >
                                <Plus size="14" /> Buat Pengumuman Baru
                            </button>
                        </template>
                    </div>

                    <!-- TABEL UTAMA -->
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200/80">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Judul Pengumuman
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Sasaran
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Tanggal Terbit
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Pembaca
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr 
                                    v-for="announcement in announcementList" 
                                    :key="announcement.id" 
                                    class="hover:bg-indigo-50/30 transition-colors"
                                >
                                    <!-- Judul & Cuplikan (Bisa Diklik untuk Preview Detail) -->
                                    <td class="px-6 py-4">
                                        <button 
                                            type="button"
                                            @click="openDetailModal(announcement)"
                                            class="text-left font-bold text-gray-900 hover:text-indigo-600 transition-colors cursor-pointer group block"
                                            title="Klik untuk melihat detail lengkap"
                                        >
                                            <span class="group-hover:underline">{{ announcement.title }}</span>
                                        </button>
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-md">
                                            {{ announcement.content }}
                                        </div>
                                    </td>

                                    <!-- Sasaran Target Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border"
                                            :class="getTargetBadgeColor(announcement.target_role)"
                                        >
                                            <component :is="getTargetIcon(announcement.target_role)" size="13" />
                                            {{ getTargetLabel(announcement.target_role) }}
                                        </span>
                                    </td>

                                    <!-- Tanggal Terbit & Pembuat -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="font-medium text-gray-700">{{ formatDate(announcement.created_at) }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Oleh: {{ announcement.user?.name || 'Administrator' }}</div>
                                    </td>

                                    <!-- Indikator Jumlah Pembaca -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/70"
                                            title="Jumlah akun yang telah membaca pengumuman ini"
                                        >
                                            <Eye size="13" class="text-blue-500" />
                                            <span>{{ announcement.readers_count || 0 }} Dibaca</span>
                                        </span>
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2.5">
                                            <!-- Tombol Detail Preview -->
                                            <button 
                                                @click="openDetailModal(announcement)"
                                                class="inline-flex items-center gap-1 text-slate-600 hover:text-indigo-600 font-bold text-xs transition-colors"
                                                title="Lihat detail lengkap pengumuman"
                                            >
                                                <Eye size="14" /> Detail
                                            </button>

                                            <!-- MODE 2: Tombol Cetak PDF / Surat Edaran Resmi -->
                                            <a 
                                                :href="route('admin.announcements.print-letter', announcement.id)"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-800 font-bold text-xs transition-colors"
                                                title="Cetak format Surat Edaran Resmi SMKN 1 Ciomas"
                                            >
                                                <Printer size="14" /> Cetak PDF
                                            </a>

                                            <!-- Tombol Edit -->
                                            <button 
                                                @click="openEditModal(announcement)" 
                                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-900 font-bold text-xs transition-colors"
                                            >
                                                <Edit size="14" /> Edit
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <button 
                                                @click="openDeleteModal(announcement)" 
                                                class="inline-flex items-center gap-1 text-red-600 hover:text-red-900 font-bold text-xs transition-colors"
                                            >
                                                <Trash2 size="14" /> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 3. PAGINASI & KONSISTENSI DATA -->
                    <div 
                        v-if="props.announcements && props.announcements.total > 0" 
                        class="px-6 py-4 bg-white border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4"
                    >
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span>
                                Menampilkan 
                                <strong class="text-gray-700">{{ props.announcements.from || 0 }}</strong>
                                –
                                <strong class="text-gray-700">{{ props.announcements.to || 0 }}</strong>
                                dari 
                                <strong class="text-gray-700">{{ props.announcements.total || 0 }}</strong>
                                pengumuman
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span>Per halaman:</span>
                                <select 
                                    v-model="per_page" 
                                    @change="onFilterChange" 
                                    class="text-xs py-1 px-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option :value="10">10</option>
                                    <option :value="25">25</option>
                                    <option :value="50">50</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tombol Pagination Links -->
                        <div 
                            v-if="props.announcements.links && props.announcements.links.length > 3" 
                            class="flex gap-1 flex-wrap justify-center"
                        >
                            <Link
                                v-for="link in props.announcements.links"
                                :key="link.label"
                                :href="link.url ?? '#'"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs transition-colors"
                                :class="[
                                    link.active ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50',
                                    !link.url ? 'opacity-40 pointer-events-none' : ''
                                ]"
                                preserve-scroll
                                preserve-state
                            />
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- 4. MODAL PREVIEW DETAIL PENGUMUMAN -->
        <Modal :show="isDetailModalOpen" @close="closeDetailModal" max-width="2xl">
            <div class="p-6">
                <!-- Header Modal Detail -->
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
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
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

                <!-- Isi Lengkap Pengumuman -->
                <div class="my-5">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Isi Pengumuman:
                    </label>
                    <div class="bg-gray-50/80 border border-gray-200/80 rounded-xl p-4 text-sm text-gray-800 whitespace-pre-wrap leading-relaxed max-h-[50vh] overflow-y-auto font-sans">
                        {{ detailAnnouncement?.content }}
                    </div>
                </div>

                <!-- Footer Aksi Modal Detail -->
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
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition-colors"
                        >
                            <Printer size="15" /> Cetak Surat Edaran (PDF)
                        </a>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- 5. MODAL CREATE / EDIT PENGUMUMAN -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <Megaphone size="20" class="text-indigo-600" />
                        {{ editingId ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <X size="22" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="title" value="Judul Pengumuman" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full rounded-xl"
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
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm"
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
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm resize-none text-sm"
                            placeholder="Tuliskan isi pengumuman secara lengkap dan jelas..."
                            v-model="form.content"
                            required
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.content" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : (editingId ? 'Perbarui Pengumuman' : 'Publikasikan Pengumuman') }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- 6. MODAL KONFIRMASI HAPUS -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4 mx-auto">
                    <AlertCircle class="text-red-600" size="24" />
                </div>
                <h2 class="text-lg font-bold text-center text-gray-900 mb-2">Hapus Pengumuman?</h2>
                <p class="text-sm text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus pengumuman <span class="font-bold text-gray-800">"{{ selectedAnnouncement?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal">Batal</SecondaryButton>
                    <button 
                        @click="deleteAnnouncement"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
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
