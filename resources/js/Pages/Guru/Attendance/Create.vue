<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Calendar, BookOpen, GraduationCap, Users, CheckCircle, Save
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    classes: Array,
    subjects: Array,
    students: Array,
    existingAttendances: Object,
    filters: Object,
});

const filterForm = useForm({
    date: props.filters.date || new Date().toISOString().split('T')[0],
    school_class_id: props.filters.school_class_id || '',
    subject_id: props.filters.subject_id || '',
});

const attendanceForm = useForm({
    date: props.filters.date,
    school_class_id: props.filters.school_class_id,
    subject_id: props.filters.subject_id,
    attendances: [],
});

const populateAttendances = () => {
    if (props.students.length > 0) {
        attendanceForm.attendances = props.students.map(student => {
            const existing = props.existingAttendances[student.id];
            return {
                student_id: student.id,
                status: existing ? existing.status : 'hadir',
                note: existing ? existing.note : '',
            };
        });
    }
};

populateAttendances();

const applyFilters = () => {
    router.get(route('guru.attendance.create'), {
        date: filterForm.date,
        school_class_id: filterForm.school_class_id,
        subject_id: filterForm.subject_id,
    }, { preserveState: true, preserveScroll: true });
};

watch(() => [filterForm.date, filterForm.school_class_id, filterForm.subject_id], () => {
    if (filterForm.school_class_id && filterForm.subject_id && filterForm.date) {
        applyFilters();
    }
});

watch(() => props.students, () => {
    attendanceForm.date = filterForm.date;
    attendanceForm.school_class_id = filterForm.school_class_id;
    attendanceForm.subject_id = filterForm.subject_id;
    populateAttendances();
}, { deep: true });

const submitAttendance = () => {
    attendanceForm.post(route('guru.attendance.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by toast/session
        }
    });
};

const statusColors = {
    hadir: 'text-green-700 bg-green-50 border-green-200',
    sakit: 'text-orange-700 bg-orange-50 border-orange-200',
    izin: 'text-blue-700 bg-blue-50 border-blue-200',
    alpa: 'text-red-700 bg-red-50 border-red-200',
};
</script>

<template>
    <Head title="Presensi Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                <CheckCircle class="text-indigo-600" size="24" />
                Presensi Siswa
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <InputLabel for="date" value="Tanggal Presensi" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Calendar class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="date"
                                    type="date"
                                    class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="filterForm.date"
                                />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="school_class_id" value="Kelas" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <GraduationCap class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    id="school_class_id"
                                    class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="filterForm.school_class_id"
                                >
                                    <option value="">-- Pilih Kelas --</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                        {{ cls.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="subject_id" value="Mata Pelajaran" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <BookOpen class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    id="subject_id"
                                    class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="filterForm.subject_id"
                                >
                                    <option value="">-- Pilih Mapel --</option>
                                    <option v-for="sub in subjects" :key="sub.id" :value="sub.id">
                                        {{ sub.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students List & Attendance Form -->
                <div v-if="filterForm.school_class_id && filterForm.subject_id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-indigo-50/30">
                        <div class="flex items-center gap-2">
                            <Users class="text-indigo-600" size="20" />
                            <h3 class="text-lg font-bold text-gray-900">Daftar Kehadiran</h3>
                        </div>
                        <span class="text-sm font-bold text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full">
                            {{ students.length }} Siswa
                        </span>
                    </div>

                    <form @submit.prevent="submitAttendance">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500">
                                        <th class="py-4 px-6 font-semibold w-16">No</th>
                                        <th class="py-4 px-6 font-semibold w-1/3">Nama Siswa</th>
                                        <th class="py-4 px-6 font-semibold w-auto">Status Kehadiran</th>
                                        <th class="py-4 px-6 font-semibold w-1/4">Catatan (Opsional)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-gray-50/30 transition-colors">
                                        <td class="py-4 px-6 font-medium text-gray-500">{{ index + 1 }}</td>
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-gray-900">{{ student.name }}</div>
                                            <div class="text-xs text-gray-500">{{ student.email }}</div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex flex-wrap gap-2">
                                                <label v-for="st in ['hadir', 'sakit', 'izin', 'alpa']" :key="st" 
                                                    class="cursor-pointer border rounded-lg px-3 py-1.5 text-sm font-bold transition-all flex items-center gap-1.5"
                                                    :class="attendanceForm.attendances[index].status === st ? statusColors[st] : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                                >
                                                    <input 
                                                        type="radio" 
                                                        :name="'status_'+student.id" 
                                                        :value="st" 
                                                        v-model="attendanceForm.attendances[index].status"
                                                        class="sr-only"
                                                    >
                                                    {{ st.charAt(0).toUpperCase() + st.slice(1) }}
                                                </label>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <input
                                                type="text"
                                                v-model="attendanceForm.attendances[index].note"
                                                class="block w-full text-sm border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                placeholder="Keterangan..."
                                                :disabled="attendanceForm.attendances[index].status === 'hadir'"
                                            />
                                        </td>
                                    </tr>
                                    <tr v-if="students.length === 0">
                                        <td colspan="4" class="py-12 text-center text-gray-400">
                                            Belum ada siswa di kelas ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-6 border-t border-gray-100 flex justify-end bg-gray-50">
                            <PrimaryButton 
                                type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 flex items-center gap-2 px-6 py-3"
                                :class="{ 'opacity-25': attendanceForm.processing }"
                                :disabled="attendanceForm.processing || students.length === 0"
                            >
                                <Save size="18" /> Simpan Data Presensi
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 flex flex-col items-center justify-center text-gray-400">
                    <CheckCircle size="48" class="mb-4 opacity-20" />
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Silakan Pilih Kelas dan Mapel</h3>
                    <p>Gunakan filter di atas untuk memulai pengisian presensi kelas.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
