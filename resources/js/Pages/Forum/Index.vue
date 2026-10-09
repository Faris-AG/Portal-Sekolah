<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { MessageCircle, Search, Plus, MessageSquare, Clock, User } from 'lucide-vue-next';

const props = defineProps({
    topics: Array,
    classes: Array,
    currentClass: Object,
    filters: Object,
});

const userRole = usePage().props.auth.user.role;
const isStudent = userRole === 'siswa';

const filterForm = ref({
    school_class_id: props.filters.school_class_id || '',
});

const topicForm = useForm({
    school_class_id: props.filters.school_class_id || '',
    title: '',
    content: '',
});

const showNewTopicModal = ref(false);

const submitTopic = () => {
    topicForm.post(route(userRole + '.forum.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showNewTopicModal.value = false;
            topicForm.reset('title', 'content');
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

watch(
    () => filterForm.value.school_class_id,
    (newVal) => {
        if (newVal) {
            router.get(
                route(userRole + '.forum.index'),
                { school_class_id: newVal },
                { preserveState: true, preserveScroll: true }
            );
            topicForm.school_class_id = newVal;
        }
    }
);

</script>

<template>
    <Head title="Forum Diskusi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <MessageCircle size="24" class="text-indigo-600" /> Forum Diskusi
                </h2>
                <button 
                    v-if="filterForm.school_class_id"
                    @click="showNewTopicModal = true" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm"
                >
                    <Plus size="18" /> Buat Topik
                </button>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Class Selector for Guru/Admin -->
                <div v-if="!isStudent" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Search size="20" class="text-gray-400" /> Pilih Kelas
                    </h3>
                    <div class="max-w-md">
                        <select 
                            v-model="filterForm.school_class_id" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Pilih Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Forum Topics List -->
                <div v-if="filterForm.school_class_id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Diskusi Kelas: {{ currentClass?.name }}</h3>
                        <span class="text-sm font-medium text-gray-500">{{ topics.length }} Topik</span>
                    </div>

                    <div v-if="topics.length === 0" class="p-12 text-center">
                        <MessageSquare class="mx-auto h-12 w-12 text-gray-300 mb-4" />
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Belum ada diskusi</h3>
                        <p class="text-gray-500 mb-6">Jadilah yang pertama memulai diskusi di kelas ini.</p>
                        <button 
                            @click="showNewTopicModal = true"
                            class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium"
                        >
                            <Plus size="18" /> Buat Topik Baru
                        </button>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <Link 
                            v-for="topic in topics" 
                            :key="topic.id" 
                            :href="route(userRole + '.forum.show', topic.id)"
                            class="block p-6 hover:bg-gray-50 transition-colors group"
                        >
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg shrink-0">
                                    {{ topic.user.name.substring(0, 2).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-4 mb-1">
                                        <h4 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                            {{ topic.title }}
                                        </h4>
                                        <div class="flex items-center gap-1 text-sm text-gray-500 shrink-0">
                                            <MessageCircle size="16" />
                                            <span>{{ topic.replies_count }}</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                        {{ topic.content }}
                                    </p>
                                    <div class="flex items-center gap-4 text-xs font-medium text-gray-500">
                                        <div class="flex items-center gap-1.5">
                                            <User size="14" />
                                            {{ topic.user.name }}
                                            <span 
                                                v-if="topic.user.role === 'guru'" 
                                                class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider"
                                            >Guru</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <Clock size="14" />
                                            {{ formatDate(topic.updated_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
                
                <div v-else-if="!isStudent" class="text-center py-12 px-4 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <MessageSquare class="mx-auto h-12 w-12 text-gray-300 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900">Pilih Kelas</h3>
                    <p class="mt-1 text-gray-500">Silakan pilih kelas terlebih dahulu untuk melihat atau memulai diskusi.</p>
                </div>
            </div>
        </div>

        <!-- Create Topic Modal -->
        <div v-if="showNewTopicModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Buat Topik Diskusi Baru</h3>
                    <button @click="showNewTopicModal = false" class="text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitTopic" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Diskusi</label>
                        <input 
                            v-model="topicForm.title" 
                            type="text" 
                            required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Contoh: Pertanyaan tentang materi Aljabar"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Isi Diskusi</label>
                        <textarea 
                            v-model="topicForm.content" 
                            rows="5"
                            required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                            placeholder="Tuliskan pertanyaan atau topik yang ingin didiskusikan secara detail..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button 
                            type="button" 
                            @click="showNewTopicModal = false"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="topicForm.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50"
                        >
                            {{ topicForm.processing ? 'Menyimpan...' : 'Kirim Diskusi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
