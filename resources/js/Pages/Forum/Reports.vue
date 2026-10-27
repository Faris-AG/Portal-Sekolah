<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { Flag, CheckCircle, XCircle, ExternalLink, AlertTriangle, ShieldCheck, Clock } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    reports:       Object,   // paginated
    activeStatus:  [String, Number],
    selectedClass: [String, Number],
    pendingCount:  Number,
    reviewedCount: Number,
});

const page = usePage();
const userRole = page.props.auth.user.role;

// ─── Status Tabs ───────────────────────────────────────
const tabs = [
    { key: 'pending',  label: 'Menunggu', icon: Clock,       color: 'amber' },
    { key: 'reviewed', label: 'Ditinjau', icon: CheckCircle, color: 'green' },
];

const switchTab = (status) => {
    router.get(route(userRole + '.forum.reports'), { status }, { preserveState: true, preserveScroll: true });
};

// ─── Update status laporan ──────────────────────────────
const processingReportId = ref(null);

const markAsReviewed = (reportId) => {
    processingReportId.value = reportId;
    router.put(route(userRole + '.forum.reports.status', reportId), {
        status: 'reviewed',
    }, {
        preserveScroll: true,
        onFinish: () => {
            processingReportId.value = null;
        },
    });
};

const ignoreReport = (reportId) => {
    processingReportId.value = reportId;
    router.delete(route(userRole + '.forum.reports.destroy', reportId), {
        preserveScroll: true,
        onFinish: () => {
            processingReportId.value = null;
        },
    });
};

const setStatus = (reportId, status) => {
    if (status === 'dismissed' || status === 'ignored') {
        ignoreReport(reportId);
    } else {
        markAsReviewed(reportId);
    }
};

// ─── Helper labels ───────────────────────────────────────
const reasonLabel = {
    spam:       'Spam',
    kata_kasar: 'Kata Kasar',
    bullying:   'Bullying',
    lainnya:    'Lainnya',
};

const formatDate = (d) => {
    if (!d) return '';
    return new Date(d).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Laporan Pesan Forum" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <ShieldCheck size="22" class="text-red-500" />
                Moderasi — Laporan Pesan Forum
            </h2>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">

                <!-- Status Tabs -->
                <div class="flex gap-2 bg-white rounded-xl p-1.5 shadow-sm border border-gray-100 w-fit">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="switchTab(tab.key)"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="activeStatus === tab.key
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'text-gray-500 hover:bg-gray-100'"
                    >
                        <component :is="tab.icon" size="15" />
                        {{ tab.label }}
                        <!-- Badge Tab Menunggu -->
                        <span
                            v-if="tab.key === 'pending'"
                            class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeStatus === 'pending' ? 'bg-white/30 text-white' : 'bg-amber-100 text-amber-700'"
                        >
                            {{ props.pendingCount ?? $page.props.auth?.pending_reports_count ?? 0 }}
                        </span>

                        <!-- Badge Tab Ditinjau -->
                        <span
                            v-else-if="tab.key === 'reviewed'"
                            class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="activeStatus === 'reviewed' ? 'bg-white/30 text-white' : 'bg-green-100 text-green-700'"
                        >
                            {{ props.reviewedCount ?? (activeStatus === 'reviewed' ? reports.total : 0) }}
                        </span>
                    </button>
                </div>

                <!-- Reports Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900">
                            Daftar Laporan
                            <span class="ml-2 text-sm font-normal text-gray-400">
                                ({{ reports.total }} laporan)
                            </span>
                        </h3>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="{
                                'bg-amber-100 text-amber-700': activeStatus === 'pending',
                                'bg-green-100 text-green-700': activeStatus === 'reviewed',
                            }"
                        >
                            {{ tabs.find(t => t.key === activeStatus)?.label ?? 'Menunggu' }}
                        </span>
                    </div>

                    <!-- Empty State -->
                    <div v-if="reports.data.length === 0" class="py-16 text-center">
                        <Flag class="mx-auto h-12 w-12 text-gray-200 mb-4" />
                        <p class="text-gray-400 font-medium">Tidak ada laporan dengan status ini.</p>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/60 border-b border-gray-100">
                                    <th class="py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelapor</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Alasan</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pesan Dilaporkan</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Topik / Kelas</th>
                                    <th class="py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th v-if="activeStatus === 'pending'" class="py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr
                                    v-for="report in reports.data"
                                    :key="report.id"
                                    class="hover:bg-gray-50/50 transition-colors"
                                >
                                    <!-- Pelapor -->
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                                                {{ report.reporter?.name?.substring(0, 2).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">{{ report.reporter?.name }}</p>
                                                <p class="text-[11px] text-gray-400 capitalize">{{ report.reporter?.role }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Alasan -->
                                    <td class="py-4 px-5">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                                            {{ reasonLabel[report.reason] ?? report.reason }}
                                        </span>
                                    </td>

                                    <!-- Isi Pesan -->
                                    <td class="py-4 px-5 max-w-xs">
                                        <div v-if="report.reply?.deleted_by_moderator_id" class="text-gray-400 italic text-xs flex items-center gap-1.5">
                                            <span>Pesan sudah dihapus moderator</span>
                                        </div>
                                        <div v-else>
                                            <p class="text-sm text-gray-700 line-clamp-2">
                                                {{ report.reply?.content ?? '—' }}
                                            </p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">oleh: {{ report.reply?.user?.name }}</p>
                                        </div>
                                    </td>

                                    <!-- Topik / Kelas -->
                                    <td class="py-4 px-5">
                                        <div class="text-sm">
                                            <Link
                                                v-if="report.reply?.topic"
                                                :href="route(userRole + '.forum.show', report.reply.topic.id)"
                                                class="font-medium text-indigo-600 hover:text-indigo-700 hover:underline flex items-center gap-1"
                                            >
                                                {{ report.reply.topic.title }}
                                                <ExternalLink size="11" />
                                            </Link>
                                            <p class="text-[11px] text-gray-400 mt-0.5">
                                                {{ report.reply?.topic?.school_class?.name ?? '—' }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="py-4 px-5">
                                        <span class="text-xs text-gray-500">{{ formatDate(report.created_at) }}</span>
                                    </td>

                                    <!-- Aksi (hanya pending) -->
                                    <td v-if="activeStatus === 'pending'" class="py-4 px-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="markAsReviewed(report.id)"
                                                :disabled="processingReportId === report.id"
                                                class="flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                                                title="Tandai sudah ditinjau & moderasi pesan"
                                            >
                                                <CheckCircle size="12" /> Tinjau
                                            </button>
                                            <button
                                                @click="ignoreReport(report.id)"
                                                :disabled="processingReportId === report.id"
                                                class="flex items-center gap-1 px-3 py-1.5 bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50"
                                                title="Abaikan dan hapus laporan dari sistem"
                                            >
                                                <XCircle size="12" /> Abaikan
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="reports.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Menampilkan {{ reports.from }}–{{ reports.to }} dari {{ reports.total }} laporan
                        </p>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in reports.links"
                                :key="link.label"
                                :href="link.url ?? '#'"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                                :class="[
                                    link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50',
                                    !link.url ? 'opacity-40 pointer-events-none' : ''
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
