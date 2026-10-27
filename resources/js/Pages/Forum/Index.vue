<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { MessageCircle, Search, Plus, MessageSquare, Clock, User, AlertTriangle, Flag, Pin, Lock, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
    topics: Array,
    classes: Array,
    currentClass: Object,
    filters: Object,
    selectedClass: [String, Number],
});

const page = usePage();
const auth = page.props.auth.user;
const userRole = auth.role;
const isStudent = userRole === 'siswa';
const isModeratorOrAdmin = userRole === 'guru' || userRole === 'admin';

// Deteksi flash message profanity_detected dari backend
const showProfanityAlert = ref(false);
watch(
    () => page.props.flash?.profanity_detected,
    (msg) => {
        if (msg) {
            showProfanityAlert.value = true;
            setTimeout(() => { showProfanityAlert.value = false; }, 6000);
        }
    },
    { immediate: true }
);
const profanityMessage = computed(() => page.props.flash?.profanity_detected ?? '');

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

// ─── Hak akses hapus topik ──────────────────────────
const canDeleteTopic = (topic) => {
    if (userRole === 'admin') return true;
    if (userRole === 'guru') {
        // Guru bisa hapus milik sendiri dan milik siswa
        if (topic.user_id === auth.id) return true;
        return topic.user?.role === 'siswa';
    }
    if (userRole === 'siswa') {
        return topic.user_id === auth.id;
    }
    return false;
};

// ─── Modal Konfirmasi Hapus Topik ───────────────────
const deletingTopic = ref(null);
const deleteTopicForm = useForm({});

const openDeleteTopicModal = (topic, e) => {
    e.preventDefault();
    e.stopPropagation();
    deletingTopic.value = topic;
};
const closeDeleteTopicModal = () => {
    deletingTopic.value = null;
};
const submitDeleteTopic = () => {
    deleteTopicForm.delete(route(userRole + '.forum.topic.destroy', deletingTopic.value.id), {
        preserveScroll: true,
        onSuccess: () => { deletingTopic.value = null; },
    });
};

// ─── Toggle Pin & Lock ──────────────────────────────
const pinForm  = useForm({});
const lockForm = useForm({});

const togglePin = (topic, e) => {
    e.preventDefault();
    e.stopPropagation();
    pinForm.patch(route(userRole + '.forum.topic.toggle-pin', topic.id), {
        preserveScroll: true,
    });
};

const toggleLock = (topic, e) => {
    e.preventDefault();
    e.stopPropagation();
    lockForm.patch(route(userRole + '.forum.topic.toggle-lock', topic.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Forum Diskusi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <MessageCircle size="24" class="text-indigo-600" /> Forum Diskusi
                </h2>
                <div class="flex items-center gap-3">
                    <Link 
                        v-if="!isStudent"
                        :href="route(userRole + '.forum.reports')" 
                        class="bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 px-3.5 py-2 rounded-lg font-medium text-sm transition-colors flex items-center gap-2 shadow-sm"
                    >
                        <Flag size="16" class="text-amber-600" />
                        <span>Laporan Pesan</span>
                        <span 
                            v-if="$page.props.auth?.pending_reports_count > 0" 
                            class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                        >
                            {{ $page.props.auth.pending_reports_count }}
                        </span>
                    </Link>
                    <button 
                        v-if="filterForm.school_class_id"
                        @click="showNewTopicModal = true" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm"
                    >
                        <Plus size="18" /> Buat Topik
                    </button>
                </div>
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

                <!-- Profanity Alert Banner -->
                <transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div
                        v-if="showProfanityAlert"
                        class="flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 mb-4 shadow-sm"
                    >
                        <AlertTriangle size="18" class="shrink-0 mt-0.5 text-amber-500" />
                        <div class="flex-1 text-sm">
                            <span class="font-semibold">Konten disensor otomatis.</span>
                            {{ profanityMessage }}
                        </div>
                        <button
                            @click="showProfanityAlert = false"
                            class="text-amber-400 hover:text-amber-600 transition-colors ml-2 shrink-0"
                            title="Tutup"
                        >&times;</button>
                    </div>
                </transition>

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
                        <div
                            v-for="topic in topics"
                            :key="topic.id"
                            class="relative group"
                            :class="{ 'bg-indigo-50/40': topic.is_pinned }"
                        >
                            <Link
                                :href="route(userRole + '.forum.show', topic.id)"
                                class="block p-6 hover:bg-gray-50/80 transition-colors"
                                :class="{ 'hover:bg-indigo-50/60': topic.is_pinned }"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg shrink-0">
                                        {{ topic.user.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-4 mb-1">
                                            <div class="flex items-center gap-2 flex-wrap min-w-0">
                                                <!-- Badge Pinned -->
                                                <span
                                                    v-if="topic.is_pinned"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                                >
                                                    <Pin size="10" class="shrink-0" />
                                                    Disematkan
                                                </span>
                                                <!-- Badge Locked -->
                                                <span
                                                    v-if="topic.is_locked"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                                >
                                                    <Lock size="10" class="shrink-0" />
                                                    Dikunci
                                                </span>
                                                <h4 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                                    {{ topic.title }}
                                                </h4>
                                            </div>
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
                                                <span
                                                    v-else-if="topic.user.role === 'admin'"
                                                    class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 text-[10px] uppercase tracking-wider"
                                                >Admin</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <Clock size="14" />
                                                {{ formatDate(topic.updated_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>

                            <!-- Action Buttons (Pin, Lock, Delete) — shown on hover -->
                            <div
                                class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                            >
                                <!-- Toggle Pin (Guru / Admin only) -->
                                <button
                                    v-if="isModeratorOrAdmin"
                                    @click="togglePin(topic, $event)"
                                    :disabled="pinForm.processing"
                                    :title="topic.is_pinned ? 'Lepas Sematan' : 'Sematkan Topik'"
                                    class="p-2 rounded-lg transition-colors disabled:opacity-50"
                                    :class="topic.is_pinned
                                        ? 'bg-indigo-100 text-indigo-600 hover:bg-indigo-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-indigo-100 hover:text-indigo-600'"
                                >
                                    <Pin size="15" />
                                </button>

                                <!-- Toggle Lock (Guru / Admin only) -->
                                <button
                                    v-if="isModeratorOrAdmin"
                                    @click="toggleLock(topic, $event)"
                                    :disabled="lockForm.processing"
                                    :title="topic.is_locked ? 'Buka Kunci' : 'Kunci Topik'"
                                    class="p-2 rounded-lg transition-colors disabled:opacity-50"
                                    :class="topic.is_locked
                                        ? 'bg-rose-100 text-rose-600 hover:bg-rose-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-rose-100 hover:text-rose-600'"
                                >
                                    <Lock size="15" />
                                </button>

                                <!-- Delete Topic -->
                                <button
                                    v-if="canDeleteTopic(topic)"
                                    @click="openDeleteTopicModal(topic, $event)"
                                    title="Hapus Topik"
                                    class="p-2 rounded-lg bg-gray-100 text-gray-500 hover:bg-red-100 hover:text-red-600 transition-colors"
                                >
                                    <Trash2 size="15" />
                                </button>
                            </div>
                        </div>
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

    <!-- ════════ MODAL KONFIRMASI HAPUS TOPIK ════════ -->
    <teleport to="body">
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="deletingTopic"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            >
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-red-50">
                            <h3 class="text-base font-bold text-red-700 flex items-center gap-2">
                                <Trash2 size="18" class="text-red-500" />
                                Hapus Topik Diskusi
                            </h3>
                            <button @click="closeDeleteTopicModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <X size="20" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-6">
                            <div class="flex items-start gap-4 mb-5">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                    <Trash2 size="18" class="text-red-500" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 mb-1">
                                        Apakah Anda yakin ingin menghapus topik ini?
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Seluruh balasan diskusi di dalamnya juga akan terhapus secara permanen.
                                        Tindakan ini <span class="font-semibold text-red-600">tidak dapat dibatalkan</span>.
                                    </p>
                                </div>
                            </div>

                            <!-- Topic preview -->
                            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mb-5">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Topik yang akan dihapus:</p>
                                <p class="text-sm font-bold text-gray-800 line-clamp-2">{{ deletingTopic?.title }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="closeDeleteTopicModal"
                                    class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors text-sm"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="submitDeleteTopic"
                                    :disabled="deleteTopicForm.processing"
                                    class="flex items-center gap-2 px-5 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors text-sm disabled:opacity-50"
                                >
                                    <Trash2 size="15" />
                                    {{ deleteTopicForm.processing ? 'Menghapus...' : 'Ya, Hapus Topik' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>
