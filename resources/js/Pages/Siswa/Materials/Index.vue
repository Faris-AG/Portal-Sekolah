<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Library, FileText, Link as LinkIcon, Download, ExternalLink, BookOpen, Search
} from 'lucide-vue-next';

const props = defineProps({
    materials: Array,
    subjects: Array,
    filters: Object,
});

import { router } from '@inertiajs/vue3';

const activeTab = ref(props.filters?.subject_id || 'all');
const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const onSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const onTabClick = (tab) => {
    activeTab.value = tab;
    applyFilters();
};

const applyFilters = () => {
    router.get(route('siswa.materials.index'), {
        search: searchQuery.value,
        subject_id: activeTab.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
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
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Library size="24" class="text-indigo-600" /> Materi Belajar
                </h2>
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search size="16" class="text-gray-400" />
                    </div>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        @input="onSearch"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow shadow-sm" 
                        placeholder="Cari materi..." 
                    />
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filter Tabs -->
                <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto hide-scrollbar">
                    <div class="flex gap-2 min-w-max">
                        <button 
                            @click="onTabClick('all')"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-bold transition-all',
                                activeTab === 'all' 
                                    ? 'bg-indigo-600 text-white shadow-md' 
                                    : 'text-gray-600 hover:bg-gray-100'
                            ]"
                        >
                            Semua Mapel
                        </button>
                        <button 
                            v-for="subject in subjects" 
                            :key="subject.id"
                            @click="onTabClick(subject.id)"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2',
                                activeTab === subject.id 
                                    ? 'bg-indigo-600 text-white shadow-md' 
                                    : 'text-gray-600 hover:bg-gray-100'
                            ]"
                        >
                            <BookOpen size="14" :class="activeTab === subject.id ? 'text-indigo-200' : 'text-gray-400'" />
                            {{ subject.name }}
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="materials.length === 0" class="bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
                        <Library class="text-indigo-300" size="48" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Materi Ditemukan</h3>
                    <p class="text-gray-500 max-w-md">Belum ada materi belajar yang tersedia untuk mata pelajaran ini atau pencarian Anda tidak membuahkan hasil.</p>
                </div>

                <!-- Materials Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="material in materials" 
                        :key="material.id" 
                        class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all flex flex-col relative group"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-3xl pointer-events-none"></div>
                        
                        <div class="relative z-10 flex flex-col h-full">
                            <div class="flex justify-between items-start mb-4 gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                    <BookOpen size="12" /> {{ material.subject?.name }}
                                </span>
                                <span class="text-xs font-semibold text-gray-400 bg-gray-50 px-2 py-1 rounded-md">
                                    {{ formatDate(material.created_at) }}
                                </span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-indigo-700 transition-colors">{{ material.title }}</h3>
                            
                            <div class="text-sm text-gray-600 mb-4 flex-1 line-clamp-3">
                                {{ material.description || 'Tidak ada deskripsi.' }}
                            </div>
                            
                            <div class="flex items-center gap-2 mb-6 text-xs font-medium text-gray-500">
                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(material.teacher?.name || 'G')}&background=e0e7ff&color=4338ca`" alt="" class="w-6 h-6 rounded-full" />
                                Oleh: {{ material.teacher?.name }}
                            </div>
                            
                            <div class="grid grid-cols-1 gap-3 mt-auto">
                                <a 
                                    v-if="material.file_path" 
                                    :href="`/storage/${material.file_path}`" 
                                    download 
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-transparent rounded-xl font-bold text-sm transition-all"
                                >
                                    <Download size="16" /> Unduh Berkas
                                </a>
                                
                                <a 
                                    v-if="material.link_url" 
                                    :href="material.link_url" 
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-purple-50 text-purple-700 hover:bg-purple-600 hover:text-white border border-purple-200 hover:border-transparent rounded-xl font-bold text-sm transition-all"
                                >
                                    <ExternalLink size="16" /> Buka Tautan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
