<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Megaphone, CheckCircle, Search, Clock, User, Bell } from 'lucide-vue-next';

const props = defineProps({
    announcements: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const activeTab = ref(props.filters?.status || 'semua');
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
    router.get(route('announcements.index'), {
        search: searchQuery.value,
        status: activeTab.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const markAsRead = (id, isRead) => {
    if (!isRead) {
        router.post(route('announcements.mark_read', id), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const markAllAsRead = () => {
    router.post(route('announcements.mark_all_read'), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <button @click="goBack" class="p-2 rounded-full hover:bg-gray-100 text-gray-600 transition-colors" title="Kembali">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    </button>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                        <Megaphone size="24" class="text-indigo-600" /> Pengumuman
                    </h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <button 
                        @click="markAllAsRead"
                        class="px-4 py-2 text-sm font-bold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors shadow-sm"
                    >
                        Tandai Semua Dibaca
                    </button>
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search size="16" class="text-gray-400" />
                        </div>
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            @input="onSearch"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm shadow-sm" 
                            placeholder="Cari pengumuman..." 
                        />
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Tabs -->
                <div class="flex space-x-2 mb-6">
                    <button 
                        @click="onTabClick('semua')"
                        class="px-5 py-2 rounded-full text-sm font-bold transition-colors"
                        :class="activeTab === 'semua' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    >
                        Semua Pengumuman
                    </button>
                    <button 
                        @click="onTabClick('belum')"
                        class="px-5 py-2 rounded-full text-sm font-bold transition-colors flex items-center gap-2"
                        :class="activeTab === 'belum' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    >
                        <Bell size="16" :class="activeTab === 'belum' ? 'text-indigo-200' : 'text-indigo-500'" /> Belum Dibaca
                    </button>
                </div>

                <!-- Announcements List -->
                <div v-if="announcements.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-50 mb-4">
                        <Megaphone class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak ada pengumuman</h3>
                    <p class="text-gray-500">Tidak ada pengumuman yang sesuai dengan kriteria filter Anda.</p>
                </div>

                <div v-else class="space-y-4">
                    <!-- Cards -->
                    <div 
                        v-for="ann in announcements" 
                        :key="ann.id" 
                        class="bg-white rounded-2xl p-6 border transition-all cursor-pointer relative overflow-hidden group"
                        :class="!ann.is_read ? 'border-l-4 border-indigo-500 shadow-md' : 'border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200'"
                        @click="markAsRead(ann.id, ann.is_read)"
                    >
                        <div v-if="!ann.is_read" class="absolute top-0 right-0 p-4 pointer-events-none">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-indigo-100 text-indigo-700">
                                Baru
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-3">
                            <div class="flex items-center gap-1.5">
                                <User size="14" />
                                {{ ann.user?.name || 'Admin' }}
                            </div>
                            <span class="text-gray-300">•</span>
                            <div class="flex items-center gap-1.5">
                                <Clock size="14" />
                                {{ formatDate(ann.created_at) }}
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-indigo-700 transition-colors" :class="{'pr-16': !ann.is_read}">
                            {{ ann.title }}
                        </h3>

                        <div class="prose prose-sm prose-slate max-w-none text-gray-600 whitespace-pre-wrap leading-relaxed">
                            {{ ann.content }}
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-50 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600">
                                Target: {{ ann.target_role === 'all' ? 'Semua Pengguna' : (ann.target_role === 'guru' ? 'Hanya Guru' : 'Hanya Siswa') }}
                            </span>
                            
                            <div v-if="ann.is_read" class="flex items-center gap-1 text-xs font-bold text-green-600">
                                <CheckCircle size="14" /> Dibaca
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
