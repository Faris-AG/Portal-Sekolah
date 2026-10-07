<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { MessageCircle, ArrowLeft, User, Clock, Send } from 'lucide-vue-next';
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    topic: Object,
});

const userRole = usePage().props.auth.user.role;

const replyForm = useForm({
    content: '',
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const repliesContainer = ref(null);

const scrollToBottom = () => {
    if (repliesContainer.value) {
        repliesContainer.value.scrollTop = repliesContainer.value.scrollHeight;
    }
};

onMounted(() => {
    scrollToBottom();
});

const submitReply = () => {
    replyForm.post(route(userRole + '.forum.reply', props.topic.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            nextTick(() => {
                scrollToBottom();
            });
        }
    });
};
</script>

<template>
    <Head :title="topic.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4 w-full">
                <Link 
                    :href="route(userRole + '.forum.index', { school_class_id: topic.school_class_id })"
                    class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors"
                >
                    <ArrowLeft size="20" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 truncate">
                    {{ topic.title }}
                </h2>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 flex flex-col h-[calc(100vh-140px)]">
                
                <!-- Topic Content & Replies Area -->
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
                                        <span 
                                            v-if="topic.user.role === 'guru'" 
                                            class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-xs uppercase tracking-wider"
                                        >Guru</span>
                                        <span v-else class="text-xs text-gray-500 font-normal">Siswa</span>
                                    </h3>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5">
                                        <Clock size="14" />
                                        {{ formatDate(topic.created_at) }}
                                    </div>
                                </div>
                            </div>
                            
                            <h1 class="text-xl font-black text-gray-900 mb-4">{{ topic.title }}</h1>
                            <div class="prose prose-indigo max-w-none text-gray-700 whitespace-pre-wrap">
                                {{ topic.content }}
                            </div>
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs font-medium text-gray-500 flex items-center gap-2">
                            <MessageCircle size="16" />
                            {{ topic.replies.length }} Balasan
                        </div>
                    </div>

                    <!-- Replies -->
                    <div class="space-y-4">
                        <div 
                            v-for="reply in topic.replies" 
                            :key="reply.id"
                            class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 ml-4 sm:ml-12"
                            :class="{'border-l-4 border-l-indigo-500': reply.user.role === 'guru'}"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ reply.user.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
                                            {{ reply.user.name }}
                                            <span 
                                                v-if="reply.user.role === 'guru'" 
                                                class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider"
                                            >Guru</span>
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[11px] text-gray-400">{{ formatDate(reply.created_at) }}</span>
                            </div>
                            <div class="text-gray-700 text-sm whitespace-pre-wrap pl-11">
                                {{ reply.content }}
                            </div>
                        </div>
                    </div>
                    
                </div>

                <!-- Reply Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 shrink-0">
                    <form @submit.prevent="submitReply" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="sr-only">Balasan Anda</label>
                            <textarea 
                                v-model="replyForm.content"
                                rows="2"
                                class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                                placeholder="Tulis balasan Anda..."
                                required
                            ></textarea>
                        </div>
                        <button 
                            type="submit" 
                            :disabled="replyForm.processing"
                            class="h-12 px-6 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm disabled:opacity-50 shrink-0"
                        >
                            <Send size="18" />
                            <span class="hidden sm:inline">{{ replyForm.processing ? 'Mengirim...' : 'Kirim' }}</span>
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
