<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    BookOpen, CheckCircle2, MessageSquare, 
    ArrowRight, FileText, Download, User, 
    Send, ShieldCheck, Mail
} from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

// State interaksi 3D tilt & dynamic specular glare
const containerRef = ref(null);
const rotX = ref(0);
const rotY = ref(0);
const glareX = ref(50);
const glareY = ref(50);
const glareOpacity = ref(0);
const isHovered = ref(false);

const handleMouseMove = (e) => {
    if (!containerRef.value) return;
    const rect = containerRef.value.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    
    // Batas rotasi kemiringan maksimal
    const deltaX = (x - centerX) / centerX;
    const deltaY = (y - centerY) / centerY;
    
    rotX.value = -deltaY * 7;
    rotY.value = deltaX * 7;
    
    glareX.value = (x / rect.width) * 100;
    glareY.value = (y / rect.height) * 100;
    glareOpacity.value = 0.12;
    isHovered.value = true;
};

const handleMouseLeave = () => {
    rotX.value = 0;
    rotY.value = 0;
    glareOpacity.value = 0;
    isHovered.value = false;
};

// Navigasi tab simulasi antarmuka
const activeTab = ref('materi'); // 'materi' | 'presensi' | 'diskusi'
const bouncingBadge = ref(null);

const selectTab = (tabName) => {
    activeTab.value = tabName;
    bouncingBadge.value = tabName;
    setTimeout(() => {
        bouncingBadge.value = null;
    }, 600);
};

// Simulasi unduhan berkas materi
const downloadToast = ref('');
const triggerDownload = (fileName) => {
    downloadToast.value = `Mengunduh "${fileName}"...`;
    setTimeout(() => {
        downloadToast.value = `Selesai! "${fileName}" berhasil tersimpan di perangkat.`;
        setTimeout(() => {
            downloadToast.value = '';
        }, 2500);
    }, 1000);
};

// Simulasi thread forum diskusi
const discussionInput = ref('');
const discussionPosts = ref([
    {
        id: 1,
        author: 'Faris Kurniawan',
        role: 'Siswa • XI PPLG 3',
        time: '15 menit lalu',
        content: 'Pak @Hendra, untuk implementasi token refresh pada REST API sebaiknya disimpan di mana agar paling aman dari celah XSS?',
        avatarColor: 'bg-indigo-100 text-indigo-700',
    },
    {
        id: 2,
        author: 'Bpk. Hendra Gunawan, S.Kom',
        role: 'Guru Pengampu',
        time: '5 menit lalu',
        content: 'Sangat direkomendasikan memakai HttpOnly Cookie dengan flag Secure & SameSite, Mas Faris. Contoh sintaks lengkapnya sudah ada di Modul 04 halaman 12.',
        avatarColor: 'bg-blue-600 text-white font-bold',
        isTeacher: true,
    }
]);

const sendDiscussion = () => {
    if (!discussionInput.value.trim()) return;
    discussionPosts.value.push({
        id: Date.now(),
        author: 'Anda (Simulasi Siswa)',
        role: 'Siswa • XI PPLG 3',
        time: 'Baru saja',
        content: discussionInput.value.trim(),
        avatarColor: 'bg-emerald-100 text-emerald-800 font-semibold',
    });
    discussionInput.value = '';
};

// Highlight fitur utama platform
const studentFeatures = [
    {
        name: 'Materi & Modul Belajar',
        description: 'Akses bahan ajar resmi, modul PDF, dan slide presentasi yang dibagikan guru kapan saja untuk mendukung proses belajar mandiri.',
        icon: BookOpen,
        color: 'text-indigo-600',
        bg: 'bg-indigo-50 border-indigo-100',
        badge: 'Modul PDF',
    },
    {
        name: 'Catatan Presensi Terbuka',
        description: 'Cek rekapitulasi kehadiran per mata pelajaran secara berkala dan pastikan catatan kedisiplinan belajar Anda tercatat akurat.',
        icon: CheckCircle2,
        color: 'text-emerald-600',
        bg: 'bg-emerald-50 border-emerald-100',
        badge: 'Presensi Real-time',
    },
    {
        name: 'Diskusi & Tanya Jawab',
        description: 'Ruang interaksi akademik terarah bagi siswa untuk mendiskusikan materi pelajaran, bertanya tugas, dan menerima arahan dari guru.',
        icon: MessageSquare,
        color: 'text-amber-600',
        bg: 'bg-amber-50 border-amber-100',
        badge: 'Forum Kelas',
    }
];

// Alur aktivasi akun dan pembelajaran
const guideSteps = [
    {
        step: '01',
        title: 'Masuk atau Daftar Akun',
        description: 'Gunakan alamat email resmi Anda (seperti Gmail, Yahoo, atau domain sekolah) melalui halaman masuk/pendaftaran.',
        icon: User,
    },
    {
        step: '02',
        title: 'Konfirmasi Verifikasi Email',
        description: 'Buka tautan verifikasi yang dikirimkan ke kotak masuk email Anda dalam 15 menit untuk mengaktifkan akun secara aman.',
        icon: Mail,
    },
    {
        step: '03',
        title: 'Akses Kelas & Pembelajaran',
        description: 'Setelah email terverifikasi, langsung akses jadwal harian, unduh materi bahan ajar, dan ikuti kegiatan belajar kelas.',
        icon: BookOpen,
    }
];
</script>

<template>
    <Head title="Portal Akademik Siswa - SMKN 1 Ciomas" />

    <div class="bg-slate-50 min-h-screen font-sans selection:bg-indigo-500 selection:text-white flex flex-col text-slate-800">
        
        <!-- Floating Navbar -->
        <header class="fixed top-0 left-0 right-0 z-50 px-3 sm:px-6 py-3 sm:py-4">
            <div class="max-w-6xl mx-auto flex justify-between items-center bg-white/90 backdrop-blur-md px-4 sm:px-5 py-2.5 sm:py-3 rounded-full border border-slate-200/80 shadow-sm shadow-slate-200/50">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <img src="/images/logo-skanic.png" alt="Logo SMKN 1 Ciomas" class="w-8 h-8 sm:w-9 sm:h-9 object-contain drop-shadow" width="36" height="36" fetchpriority="high" />
                    <div class="flex flex-col">
                        <span class="text-sm sm:text-base font-black text-slate-900 tracking-tight leading-none">SKANIC PORTAL</span>
                        <span class="text-[9px] sm:text-[10px] font-semibold tracking-wider text-slate-500 uppercase mt-0.5">SMKN 1 Ciomas</span>
                    </div>
                </div>

                <div v-if="canLogin">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full font-semibold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-500/25 transition-all inline-flex items-center gap-1.5"
                    >
                        <span>Dashboard</span>
                        <ArrowRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full font-semibold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-500/25 transition-all inline-flex items-center gap-1.5"
                    >
                        <span>Masuk</span>
                        <ArrowRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow pt-24 pb-16 sm:pt-28 lg:pt-36 lg:pb-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                
                <!-- Badge status portal -->
                <div class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-1.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 font-semibold text-xs sm:text-sm shadow-sm mb-4 sm:mb-5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                    </span>
                    <span>Portal Akademik Siswa</span>
                </div>

                <!-- Headline -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight max-w-4xl mx-auto mb-4 sm:mb-5">
                    Pusat Informasi Akademik & <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700">
                        Pembelajaran Siswa
                    </span>
                </h1>

                <!-- Sub-headline -->
                <p class="text-sm sm:text-base lg:text-lg text-slate-600 max-w-2xl mx-auto mb-8 sm:mb-10 leading-relaxed px-2">
                    Unduh materi pembelajaran dari guru, pantau catatan kehadiran, dan berdiskusi bersama teman sekelas dalam satu tempat.
                </p>

                <!-- Call to action -->
                <div class="flex justify-center mb-10 sm:mb-14">
                    <Link
                        :href="$page.props.auth?.user ? route('dashboard') : route('login')"
                        class="px-7 sm:px-8 py-3 sm:py-3.5 rounded-2xl font-bold text-sm sm:text-base text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all inline-flex items-center gap-2.5 shadow-md"
                    >
                        <span>Akses Akun Anda</span>
                        <ArrowRight class="w-4 h-4 sm:w-5 sm:h-5" />
                    </Link>
                </div>

                <!-- Showcase antarmuka: 3D tilt card & live preview tabs -->
                <div class="relative max-w-4xl mx-auto pt-2 sm:pt-6 pb-6 sm:pb-8">
                    
                    <!-- Floating badge: Materi baru (desktop only) -->
                    <button
                        type="button"
                        @click="selectTab('materi')"
                        class="hidden lg:flex absolute -top-5 -left-6 z-20 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-3.5 shadow-xl shadow-slate-300/40 text-left transition-all duration-300 hover:scale-105 cursor-pointer select-none animate-float-slow group"
                        :class="[
                            activeTab === 'materi' ? 'ring-2 ring-indigo-500 shadow-indigo-500/20' : '',
                            bouncingBadge === 'materi' ? 'animate-bounce' : ''
                        ]"
                        :style="{
                            transform: `translate(${rotY * -2}px, ${rotX * 2}px)`
                        }"
                        title="Klik untuk membuka simulasi Materi Belajar"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <BookOpen class="w-4 h-4" />
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase">Materi Baru</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Modul 04 - REST API.pdf</p>
                            </div>
                        </div>
                    </button>

                    <!-- Floating badge: Kehadiran (desktop only) -->
                    <button
                        type="button"
                        @click="selectTab('presensi')"
                        class="hidden lg:flex absolute -top-5 -right-6 z-20 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-3.5 shadow-xl shadow-slate-300/40 text-left transition-all duration-300 hover:scale-105 cursor-pointer select-none animate-float-medium group"
                        :class="[
                            activeTab === 'presensi' ? 'ring-2 ring-emerald-500 shadow-emerald-500/20' : '',
                            bouncingBadge === 'presensi' ? 'animate-bounce' : ''
                        ]"
                        :style="{
                            transform: `translate(${rotY * -1.8}px, ${rotX * 1.8}px)`
                        }"
                        title="Klik untuk membuka simulasi Catatan Presensi"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <CheckCircle2 class="w-4 h-4" />
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">Kehadiran 98.5%</span>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Disiplin Hadir Sempurna</p>
                            </div>
                        </div>
                    </button>

                    <!-- Floating badge: Diskusi aktif (desktop only) -->
                    <button
                        type="button"
                        @click="selectTab('diskusi')"
                        class="hidden lg:flex absolute -bottom-5 -left-4 z-20 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-3.5 shadow-xl shadow-slate-300/40 text-left transition-all duration-300 hover:scale-105 cursor-pointer select-none animate-float-slow group"
                        :class="[
                            activeTab === 'diskusi' ? 'ring-2 ring-amber-500 shadow-amber-500/20' : '',
                            bouncingBadge === 'diskusi' ? 'animate-bounce' : ''
                        ]"
                        :style="{
                            transform: `translate(${rotY * -2.2}px, ${rotX * 2.2}px)`
                        }"
                        title="Klik untuk membuka simulasi Diskusi Kelas"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <MessageSquare class="w-4 h-4" />
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold uppercase">Diskusi Aktif</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Tanya Jawab Guru & Siswa</p>
                            </div>
                        </div>
                    </button>

                    <!-- Wrapper 3D tilt -->
                    <div 
                        ref="containerRef"
                        @mousemove="handleMouseMove"
                        @mouseleave="handleMouseLeave"
                        class="relative perspective-1000 transition-transform duration-200 ease-out"
                    >
                        <!-- Kartu preview utama -->
                        <div 
                            class="relative bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-3xl p-4 sm:p-7 lg:p-8 shadow-2xl shadow-indigo-500/10 text-left overflow-hidden transition-transform duration-150 ease-out"
                            :style="{
                                transform: `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg)`,
                            }"
                        >
                            <!-- Efek pencahayaan (glare overlay) -->
                            <div 
                                class="absolute inset-0 pointer-events-none rounded-3xl transition-opacity duration-300"
                                :style="{
                                    background: `radial-gradient(circle 350px at ${glareX}% ${glareY}%, rgba(255, 255, 255, 0.7), transparent 80%)`,
                                    opacity: glareOpacity
                                }"
                            ></div>

                            <!-- Header jendela preview -->
                            <div class="flex flex-wrap items-center justify-between gap-2.5 pb-4 mb-4 sm:pb-5 sm:mb-5 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="flex gap-1.5">
                                        <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-rose-400"></span>
                                        <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-amber-400"></span>
                                        <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-emerald-400"></span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 ml-1.5 tracking-tight">Simulasi Ruang Siswa</span>
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full bg-slate-100 border border-slate-200/70 text-[10px] sm:text-[11px] font-semibold text-slate-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    <span>Semester Ganjil 2026/2027 • Kelas XI PPLG 3</span>
                                </div>
                            </div>

                            <!-- Navigasi tab simulasi -->
                            <div class="grid grid-cols-3 gap-1.5 p-1.5 bg-slate-100/80 rounded-2xl mb-5 sm:mb-6 border border-slate-200/60">
                                <button
                                    type="button"
                                    @click="selectTab('materi')"
                                    class="px-2 sm:px-3.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-1.5 sm:gap-2 text-center cursor-pointer truncate"
                                    :class="activeTab === 'materi' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'"
                                >
                                    <BookOpen class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" />
                                    <span class="truncate">Materi Belajar</span>
                                </button>

                                <button
                                    type="button"
                                    @click="selectTab('presensi')"
                                    class="px-2 sm:px-3.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-1.5 sm:gap-2 text-center cursor-pointer truncate"
                                    :class="activeTab === 'presensi' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'"
                                >
                                    <CheckCircle2 class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" />
                                    <span class="truncate">Catatan Presensi</span>
                                </button>

                                <button
                                    type="button"
                                    @click="selectTab('diskusi')"
                                    class="px-2 sm:px-3.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-1.5 sm:gap-2 text-center cursor-pointer truncate"
                                    :class="activeTab === 'diskusi' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'"
                                >
                                    <MessageSquare class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" />
                                    <span class="truncate">Diskusi Kelas</span>
                                </button>
                            </div>

                            <!-- Konten tab interaktif -->
                            <Transition name="tab-fade" mode="out-in">
                                
                                <!-- Tab 1: Materi Belajar -->
                                <div v-if="activeTab === 'materi'" key="materi" class="space-y-3">
                                    <div class="flex items-center justify-between pb-1">
                                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                            <FileText class="w-4 h-4 text-indigo-600" />
                                            <span>Modul & Bahan Ajar Guru Terkini</span>
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                            3 Modul Siap Unduh
                                        </span>
                                    </div>

                                    <!-- Toast notifikasi download -->
                                    <div v-if="downloadToast" class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-xs font-semibold text-indigo-800 flex items-center gap-2 animate-pulse">
                                        <Download class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" />
                                        <span>{{ downloadToast }}</span>
                                    </div>

                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-white border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-300 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0 border border-red-100">
                                                <FileText class="w-4 h-4 sm:w-5 sm:h-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900">Modul 04: Arsitektur RESTful API & JWT</h4>
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-600">2.4 MB</span>
                                                </div>
                                                <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5">Pemrograman Web • Bpk. Hendra Gunawan, S.Kom</p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="triggerDownload('Modul 04: RESTful API.pdf')"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors w-full sm:w-auto cursor-pointer flex-shrink-0"
                                        >
                                            <Download class="w-3.5 h-3.5" />
                                            <span>Unduh Modul</span>
                                        </button>
                                    </div>

                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-white border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-300 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 border border-blue-100">
                                                <FileText class="w-4 h-4 sm:w-5 sm:h-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900">Slide: Normalisasi Database 1NF - 3NF</h4>
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-600">1.8 MB</span>
                                                </div>
                                                <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5">Basis Data • Ibu Siti Rahmawati, M.Kom</p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="triggerDownload('Slide Normalisasi Database.pdf')"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors w-full sm:w-auto cursor-pointer flex-shrink-0"
                                        >
                                            <Download class="w-3.5 h-3.5" />
                                            <span>Unduh Modul</span>
                                        </button>
                                    </div>

                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-white border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-300 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 border border-amber-100">
                                                <FileText class="w-4 h-4 sm:w-5 sm:h-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900">LKS: Matriks & Transformasi Linear</h4>
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-600">850 KB</span>
                                                </div>
                                                <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5">Matematika Terapan • Bpk. Dedi Supriatna, M.Pd</p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="triggerDownload('LKS Matriks.pdf')"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors w-full sm:w-auto cursor-pointer flex-shrink-0"
                                        >
                                            <Download class="w-3.5 h-3.5" />
                                            <span>Unduh Modul</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tab 2: Catatan Presensi -->
                                <div v-else-if="activeTab === 'presensi'" key="presensi" class="space-y-3.5">
                                    <!-- Ringkasan status kehadiran -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5">
                                        <div class="p-2.5 sm:p-3 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-center">
                                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Hadir</span>
                                            <span class="text-base sm:text-lg font-black text-emerald-700">31 Sesi</span>
                                            <span class="text-[10px] text-emerald-600 block">98.5% Kehadiran</span>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-2xl bg-blue-50/80 border border-blue-200 text-center">
                                            <span class="text-[10px] font-bold text-blue-800 uppercase tracking-wider block">Izin</span>
                                            <span class="text-base sm:text-lg font-black text-blue-700">1 Sesi</span>
                                            <span class="text-[10px] text-blue-600 block">Surat Resmi</span>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-2xl bg-amber-50/80 border border-amber-200 text-center">
                                            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Sakit</span>
                                            <span class="text-base sm:text-lg font-black text-amber-700">0 Sesi</span>
                                            <span class="text-[10px] text-amber-600 block">Nir-Sakit</span>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                                            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Alpa</span>
                                            <span class="text-base sm:text-lg font-black text-slate-700">0 Sesi</span>
                                            <span class="text-[10px] text-slate-500 block">Nir-Absen</span>
                                        </div>
                                    </div>

                                    <!-- Progress bar kehadiran -->
                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-white border border-slate-200/80">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1 mb-1.5">
                                            <span class="font-bold text-slate-800">Tingkat Disiplin Kehadiran Semester Ini</span>
                                            <span class="font-mono font-bold text-emerald-600">98.5% (Target Minimum 85%)</span>
                                        </div>
                                        <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full" style="width: 98.5%"></div>
                                        </div>
                                    </div>

                                    <!-- Riwayat presensi terbaru -->
                                    <div class="space-y-2">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Catatan Harian Terbaru</div>
                                        
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs gap-1">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                                <span class="font-semibold text-slate-800">Pemrograman Web & Bergerak</span>
                                                <span class="text-slate-400">• Hari ini 07:32 WIB</span>
                                            </div>
                                            <span class="font-bold text-emerald-700 pl-4 sm:pl-0">Hadir (Tepat Waktu)</span>
                                        </div>

                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs gap-1">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                                <span class="font-semibold text-slate-800">Basis Data Relasional</span>
                                                <span class="text-slate-400">• Kemarin 09:47 WIB</span>
                                            </div>
                                            <span class="font-bold text-emerald-700 pl-4 sm:pl-0">Hadir</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Diskusi Kelas -->
                                <div v-else-if="activeTab === 'diskusi'" key="diskusi" class="space-y-3">
                                    <div class="flex items-center justify-between pb-1">
                                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                            <MessageSquare class="w-4 h-4 text-indigo-600" />
                                            <span>Forum Diskusi: Pemrograman Web (XI PPLG 3)</span>
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-medium">Topik: Keamanan REST API</span>
                                    </div>

                                    <!-- Feed diskusi -->
                                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                        <div 
                                            v-for="post in discussionPosts" 
                                            :key="post.id"
                                            class="p-3 rounded-2xl text-xs transition-all"
                                            :class="post.isTeacher ? 'bg-blue-50/70 border border-blue-200/80 ml-2 sm:ml-5' : 'bg-slate-50 border border-slate-200/70'"
                                        >
                                            <div class="flex flex-wrap items-center justify-between gap-1 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px]" :class="post.avatarColor">
                                                        {{ post.author.charAt(0) }}
                                                    </div>
                                                    <span class="font-bold text-slate-900">{{ post.author }}</span>
                                                    <span class="text-[10px] text-slate-500">({{ post.role }})</span>
                                                </div>
                                                <span class="text-[10px] text-slate-400">{{ post.time }}</span>
                                            </div>
                                            <p class="text-slate-700 leading-relaxed pl-8">{{ post.content }}</p>
                                        </div>
                                    </div>

                                    <!-- Form input komentar -->
                                    <form @submit.prevent="sendDiscussion" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-2 border-t border-slate-100">
                                        <input 
                                            type="text" 
                                            v-model="discussionInput" 
                                            placeholder="Tulis pertanyaan atau tanggapan simulasi..." 
                                            class="flex-1 text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 bg-slate-50"
                                        />
                                        <button 
                                            type="submit" 
                                            class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer w-full sm:w-auto"
                                        >
                                            <span>Kirim</span>
                                            <Send class="w-3 h-3" />
                                        </button>
                                    </form>
                                </div>
                            </Transition>

                            <!-- Catatan footer preview -->
                            <div class="mt-4 sm:mt-5 pt-3 sm:pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-[11px] text-slate-400 gap-2">
                                <div class="flex items-center gap-1.5">
                                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" />
                                    <span>Tampilan interaktif simulasi fitur siswa di SMKN 1 Ciomas</span>
                                </div>
                                <span class="text-slate-400">Klik 3 tab di atas untuk menguji fitur</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Ringkasan Fitur Siswa -->
                <div class="mt-16 sm:mt-20 text-left">
                    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 px-2">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
                            Layanan Terpadu untuk Kebutuhan Belajar Siswa
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600">
                            Semua fitur dirancang ringkas dan mudah diakses dari komputer maupun ponsel siswa.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                        <div 
                            v-for="feature in studentFeatures" 
                            :key="feature.name" 
                            class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-md shadow-slate-200/30 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center border" :class="feature.bg">
                                        <component :is="feature.icon" :class="feature.color" class="w-6 h-6" />
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">
                                        {{ feature.badge }}
                                    </span>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2">{{ feature.name }}</h3>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Panduan Onboarding Siswa -->
                <div class="mt-16 sm:mt-20 text-left">
                    <div class="bg-gradient-to-b from-white to-slate-50/90 rounded-[2rem] sm:rounded-[2.5rem] border border-slate-200/90 p-6 sm:p-10 lg:p-12 shadow-sm relative overflow-hidden">
                        <div class="max-w-2xl mb-8">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full">
                                Panduan Siswa
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-3 mb-2">
                                Cara Mulai Menggunakan Portal
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-600">
                                Tiga langkah mudah bagi siswa untuk mulai mengakses kegiatan belajar daring di Portal Akademik.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6 relative">
                            <div 
                                v-for="guide in guideSteps" 
                                :key="guide.step" 
                                class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between relative group hover:border-indigo-300 transition-colors"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-xs font-mono font-black text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                            Langkah {{ guide.step }}
                                        </span>
                                        <component :is="guide.icon" class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 transition-colors" />
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 mb-2">{{ guide.title }}</h3>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ guide.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Kontak bantuan & akses login -->
                        <div class="mt-8 sm:mt-10 pt-6 border-t border-slate-200/70 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-xs text-slate-500 text-center sm:text-left">
                                Butuh bantuan terkait akun belajar? Hubungi bagian Tata Usaha atau Tim IT SMKN 1 Ciomas.
                            </div>
                            <Link
                                :href="$page.props.auth?.user ? route('dashboard') : route('login')"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-500/20 transition-all inline-flex items-center justify-center gap-2 whitespace-nowrap"
                            >
                                <span>Masuk ke Portal Siswa</span>
                                <ArrowRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200/80 bg-white mt-auto">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                <div class="flex items-center gap-2.5">
                    <img src="/images/logo-skanic.png" alt="Logo SMKN 1 Ciomas" class="w-6 h-6 object-contain" />
                    <span class="font-bold text-slate-800 text-sm tracking-tight">SMKN 1 CIOMAS • Portal Akademik Siswa</span>
                </div>
                <p class="text-xs text-slate-500">
                    &copy; {{ new Date().getFullYear() }} SMKN 1 Ciomas. Hak Cipta Dilindungi.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes floatSlow {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes floatMedium {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-6px);
    }
}

.animate-float-slow {
    animation: floatSlow 4s ease-in-out infinite;
}

.animate-float-medium {
    animation: floatMedium 3.2s ease-in-out infinite 0.5s;
}

/* Transisi tab */
.tab-fade-enter-active,
.tab-fade-leave-active {
    transition: all 0.2s ease-out;
}

.tab-fade-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.tab-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
