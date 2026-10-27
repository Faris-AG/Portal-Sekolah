<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { MessageCircle, ArrowLeft, Clock, Send, AlertTriangle, MoreVertical, Flag, Pencil, Trash2, Check, X, Ban, Lock, Pin, LockOpen } from 'lucide-vue-next';
import { ref, onMounted, nextTick, computed, watch, onUnmounted } from 'vue';

const props = defineProps({
    topic:      Object,
    authUserId: Number,
    authRole:   String,
});

const page = usePage();
const isModeratorOrAdmin = props.authRole === 'guru' || props.authRole === 'admin';

// ─── Flash Profanity Alert ───────────────────────────
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

// ─── Reply Form ──────────────────────────────────────
const replyForm = useForm({ content: '' });

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const repliesContainer = ref(null);
const scrollToBottom = () => {
    if (repliesContainer.value) {
        repliesContainer.value.scrollTop = repliesContainer.value.scrollHeight;
    }
};

onMounted(() => { scrollToBottom(); });

const submitReply = () => {
    replyForm.post(route(props.authRole + '.forum.reply', props.topic.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            nextTick(() => { scrollToBottom(); });
        }
    });
};

// ─── Dropdown menu per reply ─────────────────────────
const openDropdown = ref(null);

const toggleDropdown = (replyId) => {
    openDropdown.value = openDropdown.value === replyId ? null : replyId;
};

const closeDropdown = () => { openDropdown.value = null; };

// Tutup dropdown saat klik di luar
const handleOutsideClick = (e) => {
    if (!e.target.closest('.reply-dropdown')) closeDropdown();
};
onMounted(() => document.addEventListener('click', handleOutsideClick));
onUnmounted(() => document.removeEventListener('click', handleOutsideClick));

// ─── Cek hak aksi per reply ──────────────────────────

/**
 * Apakah masih dalam window edit 3 menit?
 */
const canEdit = (reply) => {
    if (reply.deleted_by_moderator_id !== null) return false;
    if (reply.user_id !== props.authUserId) return false;
    const minutesSince = (Date.now() - new Date(reply.created_at).getTime()) / 60000;
    return minutesSince <= 3;
};

const canDelete = (reply) => {
    if (reply.deleted_by_moderator_id !== null) return false;
    if (props.authRole === 'admin') return true;
    if (props.authRole === 'guru')  return true;   // backend validate class
    if (props.authRole === 'siswa') return reply.user_id === props.authUserId;
    return false;
};

const canReport = (reply) => {
    if (reply.deleted_by_moderator_id !== null) return false;
    // Siswa bisa laporkan, asal bukan miliknya sendiri
    return props.authRole === 'siswa' && reply.user_id !== props.authUserId;
};

const hasAnyAction = (reply) => canEdit(reply) || canDelete(reply) || canReport(reply);

// ─── Edit Modal ───────────────────────────────────────
const editingReply  = ref(null);
const editForm      = useForm({ content: '' });

const openEditModal = (reply) => {
    editingReply.value = reply;
    editForm.content   = reply.content;
    closeDropdown();
};
const closeEditModal = () => {
    editingReply.value = null;
    editForm.reset();
};
const submitEdit = () => {
    editForm.patch(route(props.authRole + '.forum.reply.update', editingReply.value.id), {
        preserveScroll: true,
        onSuccess: () => { closeEditModal(); },
    });
};

// ─── Countdown timer untuk tombol edit ───────────────
const nowTick = ref(Date.now());
let timer = null;
onMounted(() => { timer = setInterval(() => { nowTick.value = Date.now(); }, 10000); });
onUnmounted(() => { clearInterval(timer); });

const editSecondsLeft = (reply) => {
    const limit = new Date(reply.created_at).getTime() + 3 * 60 * 1000;
    return Math.max(0, Math.ceil((limit - nowTick.value) / 1000));
};

// ─── Delete Reply Confirm ─────────────────────────────
const deletingReplyId = ref(null);
const deleteForm      = useForm({});

const confirmDelete = (replyId) => {
    deletingReplyId.value = replyId;
    closeDropdown();
};
const cancelDelete  = () => { deletingReplyId.value = null; };
const submitDelete  = () => {
    deleteForm.delete(route(props.authRole + '.forum.reply.destroy', deletingReplyId.value), {
        preserveScroll: true,
        onSuccess: () => { deletingReplyId.value = null; },
    });
};

// ─── Delete Topic Modal ────────────────────────────────
const showDeleteTopicModal = ref(false);
const deleteTopicForm      = useForm({});

const canDeleteTopic = computed(() => {
    if (props.authRole === 'admin') return true;
    if (props.authRole === 'guru') {
        if (props.topic.user_id === props.authUserId) return true;
        return props.topic.user?.role === 'siswa';
    }
    if (props.authRole === 'siswa') {
        return props.topic.user_id === props.authUserId;
    }
    return false;
});

const submitDeleteTopic = () => {
    deleteTopicForm.delete(route(props.authRole + '.forum.topic.destroy', props.topic.id), {
        onSuccess: () => {
            showDeleteTopicModal.value = false;
        },
    });
};

// ─── Toggle Pin / Lock ────────────────────────────────
const pinForm  = useForm({});
const lockForm = useForm({});

const togglePin = () => {
    pinForm.patch(route(props.authRole + '.forum.topic.toggle-pin', props.topic.id), {
        preserveScroll: true,
    });
};

const toggleLock = () => {
    lockForm.patch(route(props.authRole + '.forum.topic.toggle-lock', props.topic.id), {
        preserveScroll: true,
    });
};

// ─── Report Modal ─────────────────────────────────────
const reportingReply  = ref(null);
const reportForm      = useForm({ reason: '' });

const openReportModal = (reply) => {
    reportingReply.value = reply;
    reportForm.reason    = '';
    closeDropdown();
};
const closeReportModal = () => {
    reportingReply.value = null;
    reportForm.reset();
};
const submitReport = () => {
    reportForm.post(route('siswa.forum.reply.report', reportingReply.value.id), {
        preserveScroll: true,
        onSuccess: () => { closeReportModal(); },
    });
};

const reportReasons = [
    { value: 'spam',       label: 'Spam' },
    { value: 'kata_kasar', label: 'Kata Kasar / Profanitas' },
    { value: 'bullying',   label: 'Bullying / Intimidasi' },
    { value: 'lainnya',    label: 'Lainnya' },
];
</script>

<template>
    <Head :title="topic.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4 w-full">
                <Link
                    :href="route(authRole + '.forum.index', { school_class_id: topic.school_class_id })"
                    class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors"
                >
                    <ArrowLeft size="20" />
                </Link>
                <div class="flex-1 min-w-0 flex items-center gap-3">
                    <!-- Badge Pinned -->
                    <span
                        v-if="topic.is_pinned"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-wider shrink-0"
                    >
                        <Pin size="10" /> Disematkan
                    </span>
                    <!-- Badge Locked -->
                    <span
                        v-if="topic.is_locked"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider shrink-0"
                    >
                        <Lock size="10" /> Dikunci
                    </span>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 truncate">
                        {{ topic.title }}
                    </h2>
                </div>
                <!-- Action Buttons (Guru/Admin) -->
                <div v-if="isModeratorOrAdmin || canDeleteTopic" class="flex items-center gap-2 shrink-0">
                    <button
                        v-if="isModeratorOrAdmin"
                        @click="togglePin"
                        :disabled="pinForm.processing"
                        :title="topic.is_pinned ? 'Lepas Sematan' : 'Sematkan Topik'"
                        class="p-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 flex items-center gap-1.5"
                        :class="topic.is_pinned
                            ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'
                            : 'bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-700'"
                    >
                        <Pin size="16" />
                        <span class="hidden sm:inline text-xs">{{ topic.is_pinned ? 'Lepas Sematan' : 'Sematkan' }}</span>
                    </button>

                    <button
                        v-if="isModeratorOrAdmin"
                        @click="toggleLock"
                        :disabled="lockForm.processing"
                        :title="topic.is_locked ? 'Buka Kunci Topik' : 'Kunci Topik'"
                        class="p-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 flex items-center gap-1.5"
                        :class="topic.is_locked
                            ? 'bg-rose-100 text-rose-700 hover:bg-rose-200'
                            : 'bg-gray-100 text-gray-600 hover:bg-rose-100 hover:text-rose-700'"
                    >
                        <LockOpen v-if="topic.is_locked" size="16" />
                        <Lock v-else size="16" />
                        <span class="hidden sm:inline text-xs">{{ topic.is_locked ? 'Buka Kunci' : 'Kunci' }}</span>
                    </button>

                    <button
                        v-if="canDeleteTopic"
                        @click="showDeleteTopicModal = true"
                        title="Hapus Topik"
                        class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-700 transition-colors flex items-center gap-1.5 text-sm font-medium"
                    >
                        <Trash2 size="16" />
                        <span class="hidden sm:inline text-xs">Hapus</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 flex flex-col h-[calc(100vh-140px)]">

                <!-- Replies + Topic Area -->
                <div class="flex-1 overflow-y-auto mb-6 pr-2 custom-scrollbar" ref="repliesContainer">

                    <!-- Original Post -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg">
                                    {{ topic.user.name.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                        {{ topic.user.name }}
                                        <span v-if="topic.user.role === 'guru'" class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-xs uppercase tracking-wider">Guru</span>
                                        <span v-else-if="topic.user.role === 'admin'" class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 text-xs uppercase tracking-wider">Admin</span>
                                        <span v-else class="text-xs text-gray-500 font-normal">Siswa</span>
                                    </h3>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5">
                                        <Clock size="14" />
                                        {{ formatDate(topic.created_at) }}
                                    </div>
                                </div>
                            </div>
                            <h1 class="text-xl font-black text-gray-900 mb-4">{{ topic.title }}</h1>
                            <div class="prose prose-indigo max-w-none text-gray-700 whitespace-pre-wrap">{{ topic.content }}</div>
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs font-medium text-gray-500 flex items-center gap-2">
                            <MessageCircle size="16" />
                            {{ topic.replies.length }} Balasan
                        </div>
                    </div>

                    <!-- Replies List -->
                    <div class="space-y-4">
                        <div
                            v-for="reply in topic.replies"
                            :key="reply.id"
                            class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 ml-4 sm:ml-12"
                            :class="{ 'border-l-4 border-l-indigo-500': reply.user?.role === 'guru' }"
                        >
                            <!-- ── TOMBSTONED (dihapus moderator) ── -->
                            <div v-if="reply.deleted_by_moderator_id !== null" class="flex items-center gap-2 text-gray-400 italic text-sm py-1">
                                <Ban size="16" class="text-gray-400 shrink-0" />
                                <span>Pesan ini telah dihapus oleh Moderator.</span>
                            </div>

                            <!-- ── NORMAL REPLY ── -->
                            <template v-else>
                                <div class="flex justify-between items-start mb-3">
                                    <!-- Avatar + Name -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ reply.user.name.substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
                                                {{ reply.user.name }}
                                                <span v-if="reply.user.role === 'guru'" class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider">Guru</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Right side: timestamp + edited badge + dropdown -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <div class="text-right">
                                            <span class="text-[11px] text-gray-400 block">{{ formatDate(reply.created_at) }}</span>
                                            <span v-if="reply.is_edited" class="text-[10px] text-gray-400 italic">(Diedit)</span>
                                        </div>

                                        <!-- Three-dot Dropdown -->
                                        <div v-if="hasAnyAction(reply)" class="relative reply-dropdown">
                                            <button
                                                @click.stop="toggleDropdown(reply.id)"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                                title="Opsi"
                                            >
                                                <MoreVertical size="16" />
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <transition
                                                enter-active-class="transition ease-out duration-100"
                                                enter-from-class="opacity-0 scale-95"
                                                enter-to-class="opacity-100 scale-100"
                                                leave-active-class="transition ease-in duration-75"
                                                leave-from-class="opacity-100 scale-100"
                                                leave-to-class="opacity-0 scale-95"
                                            >
                                                <div
                                                    v-if="openDropdown === reply.id"
                                                    class="absolute right-0 top-8 z-20 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden py-1"
                                                >
                                                    <!-- Edit (batas 3 menit) -->
                                                    <button
                                                        v-if="canEdit(reply)"
                                                        @click="openEditModal(reply)"
                                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors"
                                                    >
                                                        <Pencil size="15" class="text-indigo-500 shrink-0" />
                                                        <span class="font-medium">Edit Pesan</span>
                                                        <span class="ml-auto text-[10px] font-mono text-indigo-500 tabular-nums bg-indigo-50 px-1.5 py-0.5 rounded">
                                                            {{ Math.floor(editSecondsLeft(reply) / 60) }}:{{ String(editSecondsLeft(reply) % 60).padStart(2,'0') }}
                                                        </span>
                                                    </button>

                                                    <!-- Hapus -->
                                                    <button
                                                        v-if="canDelete(reply)"
                                                        @click="confirmDelete(reply.id)"
                                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors"
                                                    >
                                                        <Trash2 size="15" class="text-rose-500 shrink-0" />
                                                        <span class="font-medium">Hapus Pesan</span>
                                                    </button>

                                                    <!-- Laporkan (siswa bukan pemilik) -->
                                                    <button
                                                        v-if="canReport(reply)"
                                                        @click="openReportModal(reply)"
                                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-amber-600 hover:bg-amber-50 transition-colors"
                                                    >
                                                        <Flag size="15" class="text-amber-500 shrink-0" />
                                                        <span class="font-medium">Laporkan Pesan</span>
                                                    </button>
                                                </div>
                                            </transition>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="text-gray-700 text-sm whitespace-pre-wrap pl-11">{{ reply.content }}</div>

                                <!-- Delete Confirmation inline -->
                                <transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 -translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                >
                                    <div
                                        v-if="deletingReplyId === reply.id"
                                        class="mt-3 pl-11 flex items-center gap-3 bg-red-50 rounded-lg px-3 py-2.5 border border-red-100"
                                    >
                                        <span class="text-sm text-red-700 flex-1">Yakin ingin menghapus pesan ini?</span>
                                        <button
                                            @click="submitDelete"
                                            :disabled="deleteForm.processing"
                                            class="flex items-center gap-1 px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                                        >
                                            <Check size="12" /> Ya, Hapus
                                        </button>
                                        <button
                                            @click="cancelDelete"
                                            class="flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors"
                                        >
                                            <X size="12" /> Batal
                                        </button>
                                    </div>
                                </transition>
                            </template>
                        </div>
                    </div>
                </div><!-- end scroll area -->

                <!-- Profanity Alert Banner -->
                <transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <div v-if="showProfanityAlert" class="flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 mb-4 shadow-sm">
                        <AlertTriangle size="18" class="shrink-0 mt-0.5 text-amber-500" />
                        <div class="flex-1 text-sm">
                            <span class="font-semibold">Konten disensor otomatis.</span>
                            {{ profanityMessage }}
                        </div>
                        <button @click="showProfanityAlert = false" class="text-amber-400 hover:text-amber-600 transition-colors ml-2 shrink-0">&times;</button>
                    </div>
                </transition>

                <!-- ── TOPIK DIKUNCI — Banner untuk Siswa (merah) ── -->
                <div v-if="topic.is_locked && authRole === 'siswa'" class="bg-rose-50 border border-rose-200 rounded-2xl px-5 py-4 mb-2 shrink-0">
                    <div class="flex items-start gap-3">
                        <Lock size="20" class="text-rose-500 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-bold text-rose-700">Topik diskusi ini telah dikunci oleh Guru/Admin.</p>
                            <p class="text-sm text-rose-600 mt-0.5">Anda tidak dapat menambahkan balasan baru.</p>
                        </div>
                    </div>
                </div>

                <!-- ── TOPIK DIKUNCI — Banner untuk Guru/Admin (kuning/amber) ── -->
                <div v-if="topic.is_locked && (authRole === 'guru' || authRole === 'admin')" class="bg-amber-50 border border-amber-300 rounded-2xl px-5 py-4 mb-2 shrink-0">
                    <div class="flex items-start gap-3">
                        <Lock size="20" class="text-amber-500 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-bold text-amber-700">Topik ini sedang dikunci untuk Siswa.</p>
                            <p class="text-sm text-amber-600 mt-0.5">Sebagai {{ authRole === 'admin' ? 'Admin' : 'Guru' }}, Anda tetap dapat mengirim balasan atau pengumuman di bawah ini.</p>
                        </div>
                    </div>
                </div>

                <!-- Reply Form (disabled for siswa if locked; always enabled for guru/admin) -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 shrink-0"
                    :class="{ 'opacity-60': topic.is_locked && authRole === 'siswa' }"
                >
                    <form @submit.prevent="submitReply" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="sr-only">Balasan Anda</label>
                            <textarea
                                v-model="replyForm.content"
                                rows="2"
                                class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                                :class="{ 'cursor-not-allowed bg-gray-50': topic.is_locked && authRole === 'siswa' }"
                                :placeholder="(topic.is_locked && authRole === 'siswa') ? 'Topik ini telah dikunci...' : 'Tulis balasan Anda...'"
                                :disabled="topic.is_locked && authRole === 'siswa'"
                                :required="!(topic.is_locked && authRole === 'siswa')"
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            :disabled="replyForm.processing || (topic.is_locked && authRole === 'siswa')"
                            class="h-12 px-6 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm disabled:opacity-50 shrink-0"
                            :class="{ 'cursor-not-allowed': topic.is_locked && authRole === 'siswa' }"
                        >
                            <Send size="18" />
                            <span class="hidden sm:inline">{{ replyForm.processing ? 'Mengirim...' : 'Kirim' }}</span>
                        </button>
                    </form>
                </div>

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
                v-if="showDeleteTopicModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            >
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-red-50">
                            <h3 class="text-base font-bold text-red-700 flex items-center gap-2">
                                <Trash2 size="18" class="text-red-500" />
                                Hapus Topik Diskusi
                            </h3>
                            <button @click="showDeleteTopicModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <X size="20" />
                            </button>
                        </div>
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
                            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mb-5">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Topik yang akan dihapus:</p>
                                <p class="text-sm font-bold text-gray-800 line-clamp-2">{{ topic.title }}</p>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showDeleteTopicModal = false"
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

    <!-- ════════ MODAL EDIT ════════ -->
    <teleport to="body">
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="editingReply" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <Pencil size="16" class="text-indigo-500" />
                            Edit Pesan
                        </h3>
                        <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <X size="20" />
                        </button>
                    </div>
                    <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Pesan</label>
                            <textarea
                                v-model="editForm.content"
                                rows="4"
                                required
                                class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                                placeholder="Tulis ulang pesan Anda..."
                            ></textarea>
                            <p v-if="editForm.errors.content" class="mt-1 text-xs text-red-500">{{ editForm.errors.content }}</p>
                        </div>
                        <p class="text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-2 flex items-center gap-1.5">
                            <Clock size="13" class="shrink-0" />
                            Pesan hanya dapat diedit dalam 3 menit setelah dikirim.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="closeEditModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors text-sm">Batal</button>
                            <button type="submit" :disabled="editForm.processing" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors text-sm disabled:opacity-50">
                                {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- ════════ MODAL LAPORAN ════════ -->
    <teleport to="body">
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="reportingReply" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-red-50">
                        <h3 class="text-base font-bold text-red-700 flex items-center gap-2">
                            <Flag size="16" />
                            Laporkan Pesan
                        </h3>
                        <button @click="closeReportModal" class="text-red-400 hover:text-red-600 transition-colors">
                            <X size="20" />
                        </button>
                    </div>
                    <form @submit.prevent="submitReport" class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">Pilih alasan mengapa pesan ini melanggar aturan komunitas:</p>
                        <div class="space-y-2">
                            <label
                                v-for="r in reportReasons"
                                :key="r.value"
                                class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                                :class="reportForm.reason === r.value
                                    ? 'border-red-400 bg-red-50'
                                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                            >
                                <input type="radio" :value="r.value" v-model="reportForm.reason" class="accent-red-500" required />
                                <span class="text-sm font-medium text-gray-800">{{ r.label }}</span>
                            </label>
                        </div>
                        <p v-if="reportForm.errors.reason" class="text-xs text-red-500">{{ reportForm.errors.reason }}</p>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeReportModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors text-sm">Batal</button>
                            <button
                                type="submit"
                                :disabled="reportForm.processing || !reportForm.reason"
                                class="flex items-center gap-2 px-5 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors text-sm disabled:opacity-50"
                            >
                                <Flag size="14" />
                                {{ reportForm.processing ? 'Mengirim...' : 'Kirim Laporan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
