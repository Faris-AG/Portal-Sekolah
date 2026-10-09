<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Users, BookOpen, AlertCircle, RefreshCw, ChevronUp, ChevronDown } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    students: Object,
    classes: Array,
    filters: Object,
});

const currentSortBy = ref(props.filters.sort_by || 'name');
const currentDirection = ref(props.filters.direction || 'asc');
const currentPerPage = ref(props.filters.per_page || 10);
const searchQuery = ref(props.filters.search || '');
const currentClassFilter = ref(props.filters.class_id || '');

let searchTimeout = null;

const onSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300); // debounce 300ms
};

const onFilterChange = () => {
    applyFilters();
};

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
    router.get(route('admin.students.index'), {
        sort_by: currentSortBy.value,
        direction: currentDirection.value,
        per_page: currentPerPage.value,
        search: searchQuery.value,
        class_id: currentClassFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
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
                                class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        <div class="w-full sm:w-1/2">
                            <select 
                                v-model="currentClassFilter" 
                                @change="onFilterChange"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
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

                <!-- Students Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th @click="sortBy('name')" class="py-4 px-6 text-sm font-semibold text-gray-500 border-b border-gray-100 cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-1">
                                            Nama Siswa
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
                                    <td colspan="4" class="py-8 text-center text-gray-500 flex flex-col items-center justify-center">
                                        <AlertCircle size="32" class="text-gray-400 mb-2" />
                                        Belum ada data siswa yang terdaftar.
                                    </td>
                                </tr>
                                <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-50/50 transition-colors group">
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
