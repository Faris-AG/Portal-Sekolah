<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    Users, BookOpen, AlertCircle, RefreshCw,
    ChevronUp, ChevronDown, ArrowRightLeft, CheckSquare,
    X, Zap, CheckCircle2
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    students: Object,
    classes: Array,
    filters: Object,
    studentsByClass: Object,
});

// ─── Filter / Sort ───────────────────────────────────────────
const currentSortBy        = ref(props.filters.sort_by  || 'name');
const currentDirection     = ref(props.filters.direction || 'asc');
const currentPerPage       = ref(props.filters.per_page  || 10);
const searchQuery          = ref(props.filters.search    || '');
const currentClassFilter   = ref(props.filters.class_id  || '');

let searchTimeout = null;
const onSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
};
const onFilterChange = () => applyFilters();

const sortBy = (column) => {
    if (currentSortBy.value === column) {
        currentDirection.value = currentDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortBy.value = column;
        currentDirection.value = 'asc';
    }
    applyFilters();
};

const changePerPage = () => applyFilters();

const applyFilters = () => {
    // Reset checkboxes whenever the page/filter changes
    selectedStudentIds.value = [];
    router.get(route('admin.students.index'), {
        sort_by:    currentSortBy.value,
        direction:  currentDirection.value,
        per_page:   currentPerPage.value,
        search:     searchQuery.value,
        class_id:   currentClassFilter.value,
    }, { preserveState: true, preserveScroll: true });
};

// ─── FITUR 1: Checkbox Bulk Select ───────────────────────────
const selectedStudentIds = ref([]);

const allOnPageSelected = computed(() => {
    const ids = props.students.data.map(s => s.id);
    return ids.length > 0 && ids.every(id => selectedStudentIds.value.includes(id));
});

const toggleSelectAll = () => {
    const ids = props.students.data.map(s => s.id);
    if (allOnPageSelected.value) {
        // deselect all on this page
        selectedStudentIds.value = selectedStudentIds.value.filter(id => !ids.includes(id));
    } else {
        // add all on this page
        const merged = new Set([...selectedStudentIds.value, ...ids]);
        selectedStudentIds.value = [...merged];
    }
};

const toggleStudent = (id) => {
    const idx = selectedStudentIds.value.indexOf(id);
    if (idx === -1) {
        selectedStudentIds.value.push(id);
    } else {
        selectedStudentIds.value.splice(idx, 1);
    }
};

const clearSelection = () => { selectedStudentIds.value = []; };

// Bulk move form
const bulkTargetClassId = ref('');
const bulkForm = useForm({
    student_ids: [],
    school_class_id: '',
});

const submitBulkMove = () => {
    if (!bulkTargetClassId.value || selectedStudentIds.value.length === 0) return;
    bulkForm.student_ids     = selectedStudentIds.value;
    bulkForm.school_class_id = bulkTargetClassId.value;
    bulkForm.post(route('admin.students.bulk-update-class'), {
        onSuccess: () => {
            selectedStudentIds.value = [];
            bulkTargetClassId.value  = '';
        },
    });
};

// ─── FITUR 2: Mass Promotion Modal ───────────────────────────
const isPromotionModalOpen = ref(false);
const promotionSourceClassId = ref('');
const promotionTargetClassId = ref('');
const promotionSelectedIds   = ref([]);

// When source class changes, auto-populate and check all students
watch(promotionSourceClassId, (newId) => {
    if (!newId) {
        promotionSelectedIds.value = [];
        return;
    }
    const list = props.studentsByClass[newId] || [];
    promotionSelectedIds.value = list.map(s => s.id);
});

const studentsInSourceClass = computed(() => {
    if (!promotionSourceClassId.value) return [];
    return props.studentsByClass[promotionSourceClassId.value] || [];
});

const allPromotionSelected = computed(() => {
    return studentsInSourceClass.value.length > 0 &&
        studentsInSourceClass.value.every(s => promotionSelectedIds.value.includes(s.id));
});

const togglePromotionAll = () => {
    if (allPromotionSelected.value) {
        promotionSelectedIds.value = [];
    } else {
        promotionSelectedIds.value = studentsInSourceClass.value.map(s => s.id);
    }
};

const togglePromotion = (id) => {
    const idx = promotionSelectedIds.value.indexOf(id);
    if (idx === -1) promotionSelectedIds.value.push(id);
    else promotionSelectedIds.value.splice(idx, 1);
};

const promotionForm = useForm({
    student_ids: [],
    school_class_id: '',
});

const openPromotionModal = () => {
    promotionSourceClassId.value = '';
    promotionTargetClassId.value = '';
    promotionSelectedIds.value   = [];
    isPromotionModalOpen.value   = true;
};

const closePromotionModal = () => {
    isPromotionModalOpen.value = false;
    promotionForm.reset();
    promotionForm.clearErrors();
};

const submitPromotion = () => {
    if (!promotionTargetClassId.value || promotionSelectedIds.value.length === 0) return;
    promotionForm.student_ids     = promotionSelectedIds.value;
    promotionForm.school_class_id = promotionTargetClassId.value;
    promotionForm.post(route('admin.students.bulk-update-class'), {
        onSuccess: () => closePromotionModal(),
    });
};

// ─── FITUR 0: Single student class modal (existing) ──────────
const isModalOpen    = ref(false);
const currentStudent = ref(null);
const form = useForm({ class_id: '' });

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
                    <Users size="24" class="text-indigo-600" /> Daftar Siswa &amp; Penempatan Kelas
                </h2>
                <!-- Tombol Kenaikan Kelas -->
                <button
                    @click="openPromotionModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95"
                >
                    <ArrowRightLeft size="16" />
                    Kenaikan Kelas / Pindah Rombel
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">

                <!-- Flash success -->
                <div
                    v-if="$page.props.flash?.success"
                    class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm text-sm font-medium"
                >
                    <CheckCircle2 size="18" class="text-emerald-500 shrink-0" />
                    {{ $page.props.flash.success }}
                </div>

                <!-- Search & Filter Bar -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="w-full sm:w-1/2 flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Users size="16" class="text-gray-400" />
                            </div>
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="onSearch"
                                placeholder="Cari nama atau email siswa..."
                                class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        <div class="w-full sm:w-1/2">
                            <select
                                v-model="currentClassFilter"
                                @change="onFilterChange"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Semua Kelas</option>
                                <option value="null">Belum Ada Kelas</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ── FITUR 1: Action Bar (muncul jika ada yang dipilih) ── -->
                <teleport to="body">
                    <transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 translate-y-4"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-4"
                    >
                        <div
                            v-if="selectedStudentIds.length > 0"
                            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex flex-wrap items-center gap-3 px-5 py-3.5 bg-indigo-600 text-white rounded-2xl shadow-2xl w-max max-w-[calc(100vw-2rem)]"
                        >
                            <CheckSquare size="18" class="shrink-0" />
                            <span class="font-semibold text-sm">
                                {{ selectedStudentIds.length }} siswa dipilih
                            </span>

                            <div class="flex items-center gap-2 ml-auto">
                                <select
                                    v-model="bulkTargetClassId"
                                    class="text-sm border-0 rounded-lg bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-white/50 py-1.5 pl-3 pr-8 font-medium min-w-[180px]"
                                >
                                    <option value="" class="text-gray-700">-- Pilih Kelas Tujuan --</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id" class="text-gray-800">
                                        {{ cls.name }}
                                    </option>
                                </select>

                                <button
                                    @click="submitBulkMove"
                                    :disabled="!bulkTargetClassId || bulkForm.processing"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-white text-indigo-700 rounded-lg text-sm font-bold hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <Zap size="14" />
                                    Pindahkan Kelas
                                </button>

                                <button
                                    @click="clearSelection"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors"
                                >
                                    <X size="14" /> Batal
                                </button>
                            </div>
                        </div>
                    </transition>
                </teleport>

                <!-- Students Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <!-- Checkbox select all -->
                                    <th class="py-4 pl-5 pr-3 w-10 border-b border-gray-100">
                                        <input
                                            type="checkbox"
                                            :checked="allOnPageSelected"
                                            :indeterminate="!allOnPageSelected && students.data.some(s => selectedStudentIds.includes(s.id))"
                                            @change="toggleSelectAll"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4"
                                        />
                                    </th>
                                    <th @click="sortBy('name')" class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Nama Siswa
                                            <span v-if="currentSortBy === 'name'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
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
                                    <th @click="sortBy('class_id')" class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Kelas Saat Ini
                                            <span v-if="currentSortBy === 'class_id'">
                                                <ChevronUp v-if="currentDirection === 'asc'" size="14" />
                                                <ChevronDown v-else size="14" />
                                            </span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="students.data.length === 0">
                                    <td :colspan="5" class="text-center py-12">
                                        <div class="flex flex-col items-center justify-center text-center mx-auto text-gray-500">
                                            <AlertCircle size="36" class="text-gray-400 mb-2" />
                                            <span>Belum ada data siswa yang terdaftar.</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="student in students.data"
                                    :key="student.id"
                                    class="hover:bg-indigo-50/30 transition-colors group"
                                    :class="{ 'bg-indigo-50/50 border-l-2 border-l-indigo-400': selectedStudentIds.includes(student.id) }"
                                >
                                    <!-- Checkbox per row -->
                                    <td class="py-4 pl-5 pr-3">
                                        <input
                                            type="checkbox"
                                            :value="student.id"
                                            :checked="selectedStudentIds.includes(student.id)"
                                            @change="toggleStudent(student.id)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4"
                                        />
                                    </td>
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

                        <div class="flex gap-1" v-if="students.links">
                            <template v-for="(link, i) in students.links" :key="i">
                                <button
                                    v-if="link.url"
                                    @click="router.get(link.url, { sort_by: currentSortBy, direction: currentDirection, per_page: currentPerPage, search: searchQuery, class_id: currentClassFilter }, { preserveState: true, preserveScroll: true })"
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

        <!-- ──────────────────────────────────────────────────── -->
        <!-- Modal 1: Ubah Kelas Siswa Tunggal (existing)        -->
        <!-- ──────────────────────────────────────────────────── -->
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
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ──────────────────────────────────────────────────────── -->
        <!-- Modal 2: Kenaikan Kelas Rombongan (Mass Promotion)      -->
        <!-- ──────────────────────────────────────────────────────── -->
        <Modal :show="isPromotionModalOpen" @close="closePromotionModal" max-width="2xl">
            <div class="p-6 flex flex-col gap-6 max-h-[85vh]">
                <!-- Header -->
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-gradient-to-br from-violet-100 to-indigo-100">
                        <ArrowRightLeft class="text-violet-600" size="22" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Kenaikan Kelas / Pindah Rombel</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Pindahkan seluruh rombongan belajar sekaligus.</p>
                    </div>
                </div>

                <!-- Class pickers -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="promotion_source" value="Pilih Kelas Asal" />
                        <select
                            id="promotion_source"
                            v-model="promotionSourceClassId"
                            class="mt-1 block w-full border-gray-300 focus:border-violet-500 focus:ring-violet-500 rounded-xl shadow-sm"
                        >
                            <option value="">-- Pilih Kelas Asal --</option>
                            <option v-for="cls in classes" :key="cls.id" :value="String(cls.id)">
                                {{ cls.name }} (Tingkat {{ cls.grade_level }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="promotion_target" value="Pilih Kelas Tujuan" />
                        <select
                            id="promotion_target"
                            v-model="promotionTargetClassId"
                            class="mt-1 block w-full border-gray-300 focus:border-violet-500 focus:ring-violet-500 rounded-xl shadow-sm"
                        >
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }} (Tingkat {{ cls.grade_level }})
                            </option>
                        </select>
                        <InputError class="mt-1" :message="promotionForm.errors.school_class_id" />
                    </div>
                </div>

                <!-- Student preview list -->
                <div class="flex-1 overflow-hidden flex flex-col min-h-0">
                    <div v-if="!promotionSourceClassId" class="flex flex-col items-center justify-center py-10 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <Users size="32" class="mb-2 opacity-40" />
                        <p class="text-sm">Pilih Kelas Asal untuk melihat daftar siswa</p>
                    </div>

                    <div v-else class="flex flex-col min-h-0 flex-1">
                        <!-- List header -->
                        <div class="flex items-center justify-between mb-2 px-1">
                            <p class="text-sm font-semibold text-gray-700">
                                Daftar Siswa
                                <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                    {{ promotionSelectedIds.length }}/{{ studentsInSourceClass.length }} dipilih
                                </span>
                            </p>
                            <button
                                @click="togglePromotionAll"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                            >
                                {{ allPromotionSelected ? 'Batalkan Semua' : 'Pilih Semua' }}
                            </button>
                        </div>

                        <!-- Empty state -->
                        <div v-if="studentsInSourceClass.length === 0" class="py-6 text-center text-gray-400 text-sm bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            Tidak ada siswa di kelas ini.
                        </div>

                        <!-- Scrollable list -->
                        <div v-else class="overflow-y-auto flex-1 rounded-xl border border-gray-200 divide-y divide-gray-100 max-h-64">
                            <label
                                v-for="student in studentsInSourceClass"
                                :key="student.id"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-indigo-50/50 cursor-pointer transition-colors"
                                :class="{ 'bg-indigo-50': promotionSelectedIds.includes(student.id) }"
                            >
                                <input
                                    type="checkbox"
                                    :value="student.id"
                                    :checked="promotionSelectedIds.includes(student.id)"
                                    @change="togglePromotion(student.id)"
                                    class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 w-4 h-4"
                                />
                                <span class="text-sm font-medium text-gray-800 select-none">{{ student.name }}</span>
                            </label>
                        </div>

                        <!-- Hint -->
                        <p class="mt-2 text-xs text-amber-600 flex items-start gap-1.5 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                            Hapus centang pada siswa jika ada yang <strong>tinggal kelas</strong> / tidak ikut pindah.
                        </p>
                    </div>
                </div>

                <!-- Error display -->
                <InputError :message="promotionForm.errors.student_ids" />

                <!-- Footer actions -->
                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <SecondaryButton @click="closePromotionModal" type="button">Batal</SecondaryButton>
                    <button
                        @click="submitPromotion"
                        :disabled="promotionForm.processing || promotionSelectedIds.length === 0 || !promotionTargetClassId"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-sm font-bold rounded-lg shadow disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        Proses Kenaikan Kelas
                        <span v-if="promotionSelectedIds.length > 0" class="ml-1 bg-white/20 px-1.5 py-0.5 rounded text-xs">
                            {{ promotionSelectedIds.length }} siswa
                        </span>
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
