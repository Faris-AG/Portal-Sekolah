<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Calendar, Plus, Trash2, Clock, MapPin, User, AlertCircle, BookOpen } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    schedules: Array,
    classes: Array,
    subjects: Array,
    teachers: Array,
    selectedClass: String,
});

const currentClassFilter = ref(props.selectedClass || '');
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const scheduleToDelete = ref(null);

const form = useForm({
    school_class_id: props.selectedClass || '',
    subject_id: '',
    teacher_id: '',
    day: '',
    start_time: '',
    end_time: '',
});

const onClassFilterChange = () => {
    router.get(
        route('admin.schedules.index'),
        { class_id: currentClassFilter.value },
        { preserveState: true }
    );
};

const openModal = () => {
    form.reset();
    form.school_class_id = currentClassFilter.value;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('admin.schedules.store'), {
        onSuccess: () => {
            closeModal();
            if (form.school_class_id !== currentClassFilter.value) {
                currentClassFilter.value = form.school_class_id;
                onClassFilterChange();
            }
        },
    });
};

const confirmDelete = (schedule) => {
    scheduleToDelete.value = schedule;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    scheduleToDelete.value = null;
};

const deleteSchedule = () => {
    if (scheduleToDelete.value) {
        form.delete(route('admin.schedules.destroy', scheduleToDelete.value.id), {
            onSuccess: () => closeDeleteModal(),
        });
    }
};

const daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

const formatTime = (timeString) => {
    if (!timeString) return '';
    // timeString is like "07:30:00"
    return timeString.substring(0, 5);
};
</script>

<template>
    <Head title="Jadwal Pelajaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center gap-2">
                    <Calendar size="24" class="text-indigo-600" /> Jadwal Pelajaran
                </h2>
                <button
                    @click="openModal"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    <Plus size="16" /> Tambah Jadwal
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filter Section -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center gap-4 justify-between">
                    <div class="flex-1 max-w-sm">
                        <InputLabel for="filter_class" value="Pilih Kelas untuk melihat jadwal:" class="mb-1" />
                        <select
                            id="filter_class"
                            v-model="currentClassFilter"
                            @change="onClassFilterChange"
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">-- Silakan Pilih Kelas --</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }} (Tingkat {{ cls.grade_level }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Schedule Content -->
                <div v-if="!currentClassFilter" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-50 mb-4">
                        <Calendar class="h-8 w-8 text-indigo-500" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Pilih Kelas</h3>
                    <p class="mt-1 text-gray-500 max-w-sm mx-auto">
                        Silakan pilih kelas pada dropdown di atas untuk melihat atau mengelola jadwal pelajaran.
                    </p>
                </div>

                <div v-else-if="schedules.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-50 mb-4">
                        <AlertCircle class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum ada jadwal</h3>
                    <p class="mt-1 text-gray-500 max-w-sm mx-auto">
                        Belum ada jadwal pelajaran yang diatur untuk kelas ini. Klik tombol "Tambah Jadwal" untuk memulai.
                    </p>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="day in daysOfWeek" :key="day" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/80 border-b border-gray-100 px-6 py-4 flex items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ day }}</h3>
                        </div>
                        
                        <div class="p-0">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="hidden md:table-row bg-white border-b border-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                        <th class="py-3 px-6 font-semibold w-1/5">Waktu</th>
                                        <th class="py-3 px-6 font-semibold w-2/5">Mata Pelajaran</th>
                                        <th class="py-3 px-6 font-semibold w-1/4">Guru Pengampu</th>
                                        <th class="py-3 px-6 font-semibold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template v-for="schedule in schedules.filter(s => s.day === day)" :key="schedule.id">
                                        <tr class="hover:bg-gray-50/50 transition-colors flex flex-col md:table-row">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-2 text-gray-900 font-medium">
                                                    <Clock size="16" class="text-indigo-400" />
                                                    {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="font-bold text-gray-900">{{ schedule.subject?.name }}</div>
                                                <div class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                                                    <BookOpen size="14" /> {{ schedule.subject?.code }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-2">
                                                    <User size="16" class="text-gray-400" />
                                                    <span class="text-gray-700 font-medium">{{ schedule.teacher?.name || 'Belum ditentukan' }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <button
                                                    @click="confirmDelete(schedule)"
                                                    class="text-red-500 hover:text-red-700 transition-colors p-2 rounded-lg hover:bg-red-50 inline-flex items-center justify-center focus:outline-none"
                                                    title="Hapus"
                                                >
                                                    <Trash2 size="18" />
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-if="schedules.filter(s => s.day === day).length === 0">
                                        <td colspan="4" class="py-6 px-6 text-center text-gray-400 italic text-sm">
                                            Tidak ada jadwal di hari {{ day }}.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add Schedule Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <Calendar class="text-indigo-600" size="24" />
                    Tambah Jadwal Pelajaran
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="school_class_id" value="Kelas" />
                            <select
                                id="school_class_id"
                                v-model="form.school_class_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Kelas --</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.name }} (Tingkat {{ cls.grade_level }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.school_class_id" />
                        </div>

                        <div>
                            <InputLabel for="day" value="Hari" />
                            <select
                                id="day"
                                v-model="form.day"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Hari --</option>
                                <option v-for="day in daysOfWeek" :key="day" :value="day">{{ day }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.day" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="start_time" value="Mulai" />
                                <input
                                    id="start_time"
                                    type="time"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.start_time"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.start_time" />
                            </div>
                            <div>
                                <InputLabel for="end_time" value="Selesai" />
                                <input
                                    id="end_time"
                                    type="time"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.end_time"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.end_time" />
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="subject_id" value="Mata Pelajaran" />
                            <select
                                id="subject_id"
                                v-model="form.subject_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Mapel --</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }} ({{ subject.code }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.subject_id" />
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="teacher_id" value="Guru Pengampu (Opsional)" />
                            <select
                                id="teacher_id"
                                v-model="form.teacher_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">-- Belum Ditentukan --</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                    {{ teacher.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.teacher_id" />
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button"> Batal </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Simpan Jadwal
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5 shadow-sm">
                    <Trash2 class="h-8 w-8 text-red-600" stroke-width="2" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    Hapus Jadwal
                </h2>
                <p class="text-base text-gray-500 mb-8">
                    Apakah Anda yakin ingin menghapus jadwal <span class="font-bold text-gray-900">{{ scheduleToDelete?.subject?.name }}</span> ini? Data tidak dapat dikembalikan.
                </p>

                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteSchedule"
                        class="gap-2"
                    >
                        Ya, Hapus Jadwal
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
