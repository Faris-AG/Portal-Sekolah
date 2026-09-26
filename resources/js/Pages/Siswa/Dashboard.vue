<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { 
    Book, Bell, CalendarDays, ArrowRight, Clock, User, 
    GraduationCap, AlertCircle, Calendar, CheckCircle, 
    XCircle, UploadCloud, FileText, Star
} from 'lucide-vue-next';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    student: Object,
    schoolClass: Object,
    todaySchedules: Array,
    allSchedules: Array,
    tugasTerdekat: Array,
    attendanceStats: Object,
    recentAttendances: Array,
});

const user = usePage().props.auth.user;
const daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleString('id-ID', { 
        weekday: 'short', 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const activeTab = ref(new Date().toLocaleDateString('id-ID', { weekday: 'long' }));
if (!daysOfWeek.includes(activeTab.value)) {
    activeTab.value = 'Senin';
}

// Submission Logic
const isSubmissionModalOpen = ref(false);
const selectedAssignment = ref(null);

const form = useForm({
    assignment_id: '',
    file: null,
    note: '',
});

const openSubmissionModal = (assignment) => {
    selectedAssignment.value = assignment;
    form.assignment_id = assignment.id;
    form.file = null;
    form.note = '';
    form.clearErrors();
    isSubmissionModalOpen.value = true;
};

const closeSubmissionModal = () => {
    isSubmissionModalOpen.value = false;
    selectedAssignment.value = null;
    form.reset();
};

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submitTask = () => {
    form.post(route('siswa.submissions.store'), {
        onSuccess: () => closeSubmissionModal(),
        preserveScroll: true,
    });
};

const hasSubmitted = (assignment) => {
    return assignment.submissions && assignment.submissions.length > 0;
};
</script>

<template>
    <Head title="Siswa Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Siswa
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome & Profile Banner -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center justify-between relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/4"></div>
                    <div class="flex items-center gap-6">
                        <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center border-4 border-white shadow-sm">
                            <User class="h-10 w-10 text-indigo-500" />
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-900">Halo, {{ student.name }}! 👋</h3>
                            <p class="mt-1 text-gray-500 flex items-center gap-2">
                                <span>{{ student.email }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 md:mt-0 md:text-right">
                        <div v-if="schoolClass" class="inline-flex flex-col items-center md:items-end">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Kelas Anda</span>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-lg shadow-sm">
                                <GraduationCap size="20" /> {{ schoolClass.name }}
                            </span>
                        </div>
                        <div v-else class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 shadow-sm">
                            <AlertCircle size="20" />
                            <span class="font-medium">Belum ditempatkan di kelas</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 space-y-6">
                        
                        <!-- Tasks & Assignments Section -->
                        <div v-if="schoolClass" class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                                <FileText class="text-orange-500" size="20" />
                                <h3 class="text-lg font-bold text-gray-900">Tugas & PR Kelas</h3>
                            </div>
                            <div class="p-6">
                                <div v-if="tugasTerdekat.length === 0" class="text-gray-400 text-center py-12 flex flex-col items-center">
                                    <CheckCircle size="48" class="mb-4 opacity-20" />
                                    <p>Yeay! Tidak ada tugas saat ini.</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="tugas in tugasTerdekat" :key="tugas.id" class="border border-gray-100 rounded-xl p-5 hover:border-indigo-100 hover:shadow-md transition-all bg-white relative overflow-hidden group">
                                        <div class="absolute top-0 left-0 w-1 h-full" :class="hasSubmitted(tugas) ? 'bg-green-500' : 'bg-red-500'"></div>
                                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 ml-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 text-gray-700">
                                                        {{ tugas.subject?.name }}
                                                    </span>
                                                    <span class="text-xs font-medium text-gray-500">
                                                        Oleh: {{ tugas.teacher?.name }}
                                                    </span>
                                                </div>
                                                <h4 class="text-lg font-bold text-gray-900">{{ tugas.title }}</h4>
                                                <p class="text-sm text-gray-600 mt-2 line-clamp-2" :title="tugas.description">{{ tugas.description }}</p>
                                                
                                                <div class="flex items-center gap-4 mt-4 text-sm font-medium">
                                                    <span class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-2 py-1 rounded-md">
                                                        <Clock size="14" /> Deadline: {{ formatDate(tugas.due_date) }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col items-end gap-3 min-w-[140px]">
                                                <div v-if="hasSubmitted(tugas)" class="inline-flex flex-col items-end gap-2">
                                                    <div class="inline-flex items-center gap-1.5 text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                                                        <CheckCircle size="16" /> Dikumpulkan
                                                    </div>
                                                    <div v-if="tugas.submissions[0].grade !== null" class="inline-flex items-center gap-1.5 text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                                                        <Star size="16" /> Nilai: {{ tugas.submissions[0].grade }}
                                                    </div>
                                                </div>
                                                <div v-else class="inline-flex items-center gap-1.5 text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                                                    <XCircle size="16" /> Belum
                                                </div>

                                                <button 
                                                    @click="openSubmissionModal(tugas)"
                                                    class="w-full justify-center flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                                                >
                                                    <UploadCloud size="16" /> 
                                                    {{ hasSubmitted(tugas) ? 'Detail / Revisi' : 'Kumpulkan' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Schedule Tabs -->
                        <div v-if="schoolClass" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                                <Calendar class="text-indigo-500" size="20" />
                                <h3 class="text-lg font-bold text-gray-900">Jadwal Pelajaran Mingguan</h3>
                            </div>
                            
                            <div class="flex border-b border-gray-100 overflow-x-auto">
                                <button 
                                    v-for="day in daysOfWeek" 
                                    :key="day"
                                    @click="activeTab = day"
                                    class="flex-1 py-4 px-6 text-sm font-medium transition-colors border-b-2 whitespace-nowrap focus:outline-none"
                                    :class="activeTab === day ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                >
                                    {{ day }}
                                </button>
                            </div>

                            <div class="p-0">
                                <div v-for="day in daysOfWeek" :key="'content-'+day" v-show="activeTab === day">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500">
                                                <th class="py-3 px-6 font-semibold w-1/4">Waktu</th>
                                                <th class="py-3 px-6 font-semibold w-2/4">Mata Pelajaran</th>
                                                <th class="py-3 px-6 font-semibold w-1/4">Guru Pengampu</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            <template v-for="schedule in allSchedules.filter(s => s.day === day)" :key="schedule.id">
                                                <tr class="hover:bg-gray-50/50 transition-colors">
                                                    <td class="py-4 px-6">
                                                        <div class="flex items-center gap-2 text-gray-900 font-medium text-sm">
                                                            <Clock size="14" class="text-indigo-400" />
                                                            {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <div class="font-bold text-gray-900">{{ schedule.subject?.name }}</div>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <span class="text-gray-600 text-sm font-medium">{{ schedule.teacher?.name || '-' }}</span>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr v-if="allSchedules.filter(s => s.day === day).length === 0">
                                                <td colspan="3" class="py-8 px-6 text-center text-gray-400">
                                                    Tidak ada jadwal di hari ini.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-6">
                        <!-- Today's Schedule (Mini Card) -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-indigo-50/30 rounded-t-2xl">
                                <div class="flex items-center gap-2">
                                    <Book class="text-indigo-600" size="18" />
                                    <h3 class="text-base font-bold text-indigo-900">Jadwal Hari Ini</h3>
                                </div>
                            </div>
                            <div class="p-5 flex-1">
                                <div v-if="!schoolClass" class="text-gray-500 text-center py-6 text-sm">
                                    Anda belum tergabung di kelas mana pun.
                                </div>
                                <div v-else-if="todaySchedules.length === 0" class="text-gray-500 text-center py-6 text-sm">
                                    Hore! Tidak ada kelas hari ini.
                                </div>
                                <ul v-else class="relative border-l border-indigo-100 ml-2 space-y-5">
                                    <li v-for="jadwal in todaySchedules" :key="jadwal.id" class="pl-5 relative">
                                        <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-white border-2 border-indigo-500"></span>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ jadwal.subject?.name }}</h4>
                                            <p class="text-xs font-medium text-gray-500 flex items-center gap-1 mt-0.5">
                                                <Clock size="12" /> {{ formatTime(jadwal.start_time) }} - {{ formatTime(jadwal.end_time) }}
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Attendance Mini Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-5 border-b border-gray-100 flex items-center gap-2">
                                <CheckCircle class="text-green-500" size="18" />
                                <h3 class="text-base font-bold text-gray-900">Kehadiran Saya</h3>
                            </div>
                            <div class="p-5 flex-1">
                                <div class="grid grid-cols-4 gap-2 mb-6">
                                    <div class="text-center bg-green-50 rounded-lg p-2 border border-green-100">
                                        <p class="text-xs font-bold text-green-700 uppercase">Hadir</p>
                                        <p class="text-xl font-black text-green-900">{{ attendanceStats?.hadir || 0 }}</p>
                                    </div>
                                    <div class="text-center bg-orange-50 rounded-lg p-2 border border-orange-100">
                                        <p class="text-xs font-bold text-orange-700 uppercase">Sakit</p>
                                        <p class="text-xl font-black text-orange-900">{{ attendanceStats?.sakit || 0 }}</p>
                                    </div>
                                    <div class="text-center bg-blue-50 rounded-lg p-2 border border-blue-100">
                                        <p class="text-xs font-bold text-blue-700 uppercase">Izin</p>
                                        <p class="text-xl font-black text-blue-900">{{ attendanceStats?.izin || 0 }}</p>
                                    </div>
                                    <div class="text-center bg-red-50 rounded-lg p-2 border border-red-100">
                                        <p class="text-xs font-bold text-red-700 uppercase">Alpa</p>
                                        <p class="text-xl font-black text-red-900">{{ attendanceStats?.alpa || 0 }}</p>
                                    </div>
                                </div>

                                <div v-if="!recentAttendances || recentAttendances.length === 0" class="text-gray-500 text-center py-4 text-xs">
                                    Belum ada catatan kehadiran.
                                </div>
                                <ul v-else class="space-y-3">
                                    <li v-for="att in recentAttendances" :key="'att-'+att.id" class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ att.subject?.name || 'Umum' }}</p>
                                            <p class="text-xs text-gray-500">{{ formatDate(att.date) }}</p>
                                        </div>
                                        <div>
                                            <span 
                                                class="text-[10px] font-bold uppercase px-2 py-1 rounded"
                                                :class="{
                                                    'bg-green-100 text-green-700': att.status === 'hadir',
                                                    'bg-orange-100 text-orange-700': att.status === 'sakit',
                                                    'bg-blue-100 text-blue-700': att.status === 'izin',
                                                    'bg-red-100 text-red-700': att.status === 'alpa',
                                                }"
                                            >
                                                {{ att.status }}
                                            </span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Upcoming Tasks Mini Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="p-5 border-b border-gray-100 flex items-center gap-2">
                                <Bell class="text-red-500" size="18" />
                                <h3 class="text-base font-bold text-gray-900">Deadline Terdekat</h3>
                            </div>
                            <div class="p-5 flex-1">
                                <div v-if="tugasTerdekat.length === 0" class="text-gray-500 text-center py-6 text-sm">
                                    Tidak ada tugas dalam waktu dekat.
                                </div>
                                <ul v-else class="space-y-3">
                                    <li v-for="tugas in tugasTerdekat.slice(0,3)" :key="'mini-'+tugas.id" class="group flex flex-col justify-between p-3 rounded-xl border border-gray-100 hover:border-red-200 hover:bg-red-50/50 transition-all">
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-900">{{ tugas.title }}</h4>
                                            <p class="text-xs font-medium text-gray-600 mt-1">{{ tugas.subject?.name }}</p>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between border-t border-gray-50 pt-2">
                                            <p class="text-[11px] font-bold text-red-600 flex items-center gap-1">
                                                <Clock size="12" /> {{ formatDate(tugas.due_date) }}
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Modal -->
        <Modal :show="isSubmissionModalOpen" @close="closeSubmissionModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <UploadCloud class="text-indigo-600" size="24" />
                    Kumpulkan Tugas
                </h2>

                <div v-if="selectedAssignment" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-bold text-gray-900">{{ selectedAssignment.title }}</h4>
                    <p class="text-sm text-gray-600 mt-1">Mata Pelajaran: {{ selectedAssignment.subject?.name }}</p>
                    <p class="text-sm font-bold text-red-600 mt-2 flex items-center gap-1">
                        <Clock size="14" /> Deadline: {{ formatDate(selectedAssignment.due_date) }}
                    </p>
                    
                    <div v-if="hasSubmitted(selectedAssignment) && selectedAssignment.submissions[0].feedback" class="mt-4 p-3 bg-blue-50/50 rounded-md border border-blue-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-700 mb-1 flex items-center gap-1"><Star size="12" /> Umpan Balik Guru (Nilai: {{ selectedAssignment.submissions[0].grade }})</span>
                        <p class="text-sm text-gray-700 italic mt-1">"{{ selectedAssignment.submissions[0].feedback }}"</p>
                    </div>
                </div>

                <form @submit.prevent="submitTask" class="space-y-5">
                    <div>
                        <InputLabel for="file" value="Pilih File (.pdf, .docx, .zip, .png, .jpg - Max 5MB)" />
                        <input
                            id="file"
                            type="file"
                            @change="handleFileChange"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md shadow-sm p-1"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.file" />
                    </div>

                    <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                        <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: form.progress.percentage + '%' }"></div>
                    </div>

                    <div>
                        <InputLabel for="note" value="Catatan Tambahan (Opsional)" />
                        <textarea
                            id="note"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.note"
                            placeholder="Ketik catatan untuk guru di sini..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.note" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeSubmissionModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Mengunggah...</span>
                            <span v-else>Kirim Tugas</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
