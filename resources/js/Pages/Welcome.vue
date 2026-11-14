<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { BookOpen, CheckCircle2, MessageSquare, ArrowRight, FileText, Download, User, Send, ShieldCheck, Mail } from 'lucide-vue-next';

defineProps({ canLogin: { type: Boolean, default: true }, canRegister: { type: Boolean, default: true } });

// Collapsible Floating Pill State
const navRef = ref(null);
const isScrolled = ref(false), isExpanded = ref(false);
let lastExpandedScrollY = 0;
const isCompact = computed(() => isScrolled.value && !isExpanded.value);

const expandNavbar = () => { isExpanded.value = true; lastExpandedScrollY = window.scrollY; };
const onNavClick = () => { if (isCompact.value) expandNavbar(); };
const onNavMouseEnter = () => { if (isScrolled.value && !isExpanded.value) expandNavbar(); };
const handleScroll = () => {
    const y = window.scrollY;
    if (y > 80) {
        isScrolled.value = true;
        if (isExpanded.value && Math.abs(y - lastExpandedScrollY) > 30) isExpanded.value = false;
    } else {
        isScrolled.value = isExpanded.value = false;
    }
};

const handleClickOutside = (e) => {
    if (isExpanded.value && navRef.value && !navRef.value.contains(e.target)) {
        isExpanded.value = false;
    }
};

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (isExpanded.value) isExpanded.value = false;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    document.addEventListener('click', handleClickOutside);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside);
});

// 3D tilt & glare dengan rAF throttle
const containerRef = ref(null);
const rotX = ref(0), rotY = ref(0), glareX = ref(50), glareY = ref(50), glareOpacity = ref(0);
let rafId = null;

const handleMouseMove = (e) => {
    if (!containerRef.value || rafId) return;
    rafId = requestAnimationFrame(() => {
        if (!containerRef.value) return (rafId = null);
        const rect = containerRef.value.getBoundingClientRect();
        rotX.value = -((e.clientY - rect.top - rect.height / 2) / (rect.height / 2)) * 7;
        rotY.value = ((e.clientX - rect.left - rect.width / 2) / (rect.width / 2)) * 7;
        glareX.value = ((e.clientX - rect.left) / rect.width) * 100;
        glareY.value = ((e.clientY - rect.top) / rect.height) * 100;
        glareOpacity.value = 0.12;
        rafId = null;
    });
};
const handleMouseLeave = () => { if (rafId) cancelAnimationFrame(rafId); rotX.value = rotY.value = glareOpacity.value = 0; };

// Tab simulasi & download mock dengan timer guard
const activeTab = ref('materi'), bouncingBadge = ref(null), downloadToast = ref('');
let tabTimer = null, dlTimer = null, toastTimer = null;

const selectTab = (tab) => {
    activeTab.value = tab; bouncingBadge.value = tab;
    if (tabTimer) clearTimeout(tabTimer);
    tabTimer = setTimeout(() => (bouncingBadge.value = null), 600);
};

const triggerDownload = (fileName) => {
    if (dlTimer) clearTimeout(dlTimer);
    if (toastTimer) clearTimeout(toastTimer);
    downloadToast.value = `Mengunduh "${fileName}"...`;
    dlTimer = setTimeout(() => {
        downloadToast.value = `Selesai! "${fileName}" berhasil tersimpan.`;
        toastTimer = setTimeout(() => (downloadToast.value = ''), 2500);
    }, 800);
};

// Simulasi Thread Diskusi
const discussionInput = ref('');
const discussionPosts = ref([
    { id: 1, author: 'Faris Kurniawan', role: 'Siswa • XI PPLG 3', time: '15m lalu', content: 'Pak @Hendra, untuk token refresh REST API sebaiknya disimpan di mana agar aman dari XSS?', avatar: 'bg-indigo-100 text-indigo-700' },
    { id: 2, author: 'Bpk. Hendra Gunawan, S.Kom', role: 'Guru Pengampu', time: '5m lalu', content: 'Sangat direkomendasikan memakai HttpOnly Cookie dengan flag Secure & SameSite. Contoh ada di Modul 04 hal 12.', avatar: 'bg-blue-600 text-white font-bold', isTeacher: true }
]);
const sendDiscussion = () => {
    if (!discussionInput.value.trim()) return;
    discussionPosts.value.push({ id: Date.now(), author: 'Anda (Simulasi Siswa)', role: 'Siswa • XI PPLG 3', time: 'Baru saja', content: discussionInput.value.trim(), avatar: 'bg-emerald-100 text-emerald-800' });
    discussionInput.value = '';
};

// Koleksi data statis
const badges = [
    { id: 'materi', pos: '-top-5 -left-6', anim: 'animate-float-slow', rot: [-2, 2], icon: BookOpen, tag: 'Materi Baru', title: 'Modul 04 - REST API.pdf', ring: 'ring-2 ring-indigo-500 shadow-indigo-500/20', bg: 'bg-indigo-50 text-indigo-600 border-indigo-100 group-hover:bg-indigo-600', tagCls: 'bg-indigo-100 text-indigo-700' },
    { id: 'presensi', pos: '-top-5 -right-6', anim: 'animate-float-medium', rot: [-1.8, 1.8], icon: CheckCircle2, tag: 'Kehadiran 98.5%', title: 'Disiplin Hadir Sempurna', ring: 'ring-2 ring-emerald-500 shadow-emerald-500/20', bg: 'bg-emerald-50 text-emerald-600 border-emerald-100 group-hover:bg-emerald-600', tagCls: 'bg-emerald-100 text-emerald-800' },
    { id: 'diskusi', pos: '-bottom-5 -left-4', anim: 'animate-float-slow', rot: [-2.2, 2.2], icon: MessageSquare, tag: 'Diskusi Aktif', title: 'Tanya Jawab Guru & Siswa', ring: 'ring-2 ring-amber-500 shadow-amber-500/20', bg: 'bg-amber-50 text-amber-600 border-amber-100 group-hover:bg-amber-600', tagCls: 'bg-amber-100 text-amber-800' }
];
const tabs = [{ id: 'materi', label: 'Materi Belajar', icon: BookOpen }, { id: 'presensi', label: 'Catatan Presensi', icon: CheckCircle2 }, { id: 'diskusi', label: 'Diskusi Kelas', icon: MessageSquare }];
const modules = [
    { title: 'Modul 04: Arsitektur RESTful API & JWT', size: '2.4 MB', author: 'Pemrograman Web • Bpk. Hendra Gunawan, S.Kom', file: 'Modul 04: RESTful API.pdf', bg: 'bg-red-50 text-red-600 border-red-100' },
    { title: 'Slide: Normalisasi Database 1NF - 3NF', size: '1.8 MB', author: 'Basis Data • Ibu Siti Rahmawati, M.Kom', file: 'Slide Normalisasi Database.pdf', bg: 'bg-blue-50 text-blue-600 border-blue-100' },
    { title: 'LKS: Matriks & Transformasi Linear', size: '850 KB', author: 'Matematika Terapan • Bpk. Dedi Supriatna, M.Pd', file: 'LKS Matriks.pdf', bg: 'bg-amber-50 text-amber-600 border-amber-100' }
];
const attendanceStats = [
    { label: 'Hadir', count: '31 Sesi', desc: '98.5% Kehadiran', bg: 'bg-emerald-50/80 border-emerald-200', text: 'text-emerald-800', val: 'text-emerald-700', sub: 'text-emerald-600' },
    { label: 'Izin', count: '1 Sesi', desc: 'Surat Resmi', bg: 'bg-blue-50/80 border-blue-200', text: 'text-blue-800', val: 'text-blue-700', sub: 'text-blue-600' },
    { label: 'Sakit', count: '0 Sesi', desc: 'Nir-Sakit', bg: 'bg-amber-50/80 border-amber-200', text: 'text-amber-800', val: 'text-amber-700', sub: 'text-amber-600' },
    { label: 'Alpa', count: '0 Sesi', desc: 'Nir-Absen', bg: 'bg-slate-50 border-slate-200', text: 'text-slate-600', val: 'text-slate-700', sub: 'text-slate-500' }
];
const attendanceHistory = [
    { subject: 'Pemrograman Web & Bergerak', time: 'Hari ini 07:32 WIB', status: 'Hadir (Tepat Waktu)' },
    { subject: 'Basis Data Relasional', time: 'Kemarin 09:47 WIB', status: 'Hadir' }
];
const studentFeatures = [
    { name: 'Materi & Modul Belajar', desc: 'Akses bahan ajar resmi, modul PDF, dan slide presentasi yang dibagikan guru kapan saja.', icon: BookOpen, color: 'text-indigo-600', bg: 'bg-indigo-50 border-indigo-100', badge: 'Modul PDF' },
    { name: 'Catatan Presensi Terbuka', desc: 'Cek rekapitulasi kehadiran per mata pelajaran secara berkala dan pastikan kedisiplinan belajar akurat.', icon: CheckCircle2, color: 'text-emerald-600', bg: 'bg-emerald-50 border-emerald-100', badge: 'Presensi Real-time' },
    { name: 'Diskusi & Tanya Jawab', desc: 'Ruang interaksi akademik bagi siswa untuk mendiskusikan materi pelajaran dan bertanya tugas.', icon: MessageSquare, color: 'text-amber-600', bg: 'bg-amber-50 border-amber-100', badge: 'Forum Kelas' }
];
const guideSteps = [
    { step: '01', title: 'Masuk atau Daftar Akun', desc: 'Gunakan alamat email resmi Anda melalui halaman masuk atau formulir pendaftaran.', icon: User },
    { step: '02', title: 'Konfirmasi Verifikasi Email', desc: 'Buka tautan verifikasi yang dikirimkan ke kotak masuk email Anda untuk mengaktifkan akun.', icon: Mail },
    { step: '03', title: 'Akses Kelas & Pembelajaran', desc: 'Akses jadwal harian, unduh materi bahan ajar, dan ikuti kegiatan belajar daring.', icon: BookOpen }
];
</script>

<template>
    <Head title="Portal Akademik Siswa - SMKN 1 Ciomas" />
    <div class="bg-slate-50 min-h-screen font-sans selection:bg-indigo-500 selection:text-white flex flex-col text-slate-800">
        <!-- 1. Smooth Collapsible Floating Pill Navbar -->
        <header class="fixed top-3 sm:top-5 inset-x-0 z-50 px-4 flex justify-center pointer-events-none">
            <nav 
                ref="navRef"
                @click="onNavClick"
                @mouseenter="onNavMouseEnter"
                class="pointer-events-auto rounded-full bg-white/90 backdrop-blur-xl border border-slate-200/90 shadow-lg shadow-slate-900/5 ring-1 ring-slate-900/5 overflow-hidden whitespace-nowrap transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] flex items-center"
                :class="[
                    isCompact 
                        ? 'w-12 h-12 max-w-[48px] sm:max-w-[52px] p-0 cursor-pointer hover:scale-105 justify-center' 
                        : 'w-full max-w-3xl h-12 px-3 sm:px-5 justify-between'
                ]"
            >
                <!-- Brand: Logo & Title (Logo selalu tampil, teks brand memudar halus saat compact) -->
                <div class="flex items-center shrink-0 transition-all duration-300" :class="isCompact ? 'gap-0' : 'gap-2.5 sm:gap-3'">
                    <img 
                        src="/images/logo-skanic.png" 
                        alt="Logo SMKN 1 Ciomas" 
                        class="w-7 h-7 sm:w-8 sm:h-8 object-contain drop-shadow transition-transform duration-300 shrink-0" 
                        width="32" 
                        height="32" 
                        fetchpriority="high" 
                    />
                    <div 
                        class="flex flex-col transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"
                        :class="isCompact ? 'max-w-0 opacity-0 scale-90 pointer-events-none m-0 p-0' : 'max-w-xs opacity-100 scale-100'"
                    >
                        <span class="text-xs sm:text-sm font-black text-slate-900 tracking-tight leading-none">SKANIC PORTAL</span>
                        <span class="text-[9px] sm:text-[10px] font-semibold tracking-wider text-slate-500 uppercase mt-0.5">SMKN 1 Ciomas</span>
                    </div>
                </div>

                <!-- 3 Link Navigasi (Tengah) -->
                <div 
                    class="hidden md:flex items-center gap-1 transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden"
                    :class="isCompact ? 'max-w-0 opacity-0 scale-90 pointer-events-none m-0 p-0' : 'max-w-md opacity-100 scale-100'"
                >
                    <a href="#simulasi" @click.prevent="scrollToSection('simulasi')" class="px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100/80 transition-colors">Simulasi</a>
                    <a href="#fitur" @click.prevent="scrollToSection('fitur')" class="px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100/80 transition-colors">Fitur Siswa</a>
                    <a href="#panduan" @click.prevent="scrollToSection('panduan')" class="px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100/80 transition-colors">Panduan</a>
                </div>

                <!-- Tombol Aksi Masuk / Dashboard (Kanan) -->
                <div 
                    class="flex items-center transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-hidden shrink-0"
                    :class="isCompact ? 'max-w-0 opacity-0 scale-90 pointer-events-none m-0 p-0' : 'max-w-xs opacity-100 scale-100'"
                >
                    <Link 
                        v-if="canLogin" 
                        :href="$page.props.auth?.user ? route('dashboard') : route('login')" 
                        class="rounded-full px-4 py-1.5 text-xs sm:text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors inline-flex items-center gap-1.5 whitespace-nowrap"
                    >
                        <span>{{ $page.props.auth?.user ? 'Dashboard' : 'Masuk' }}</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="flex-grow pt-24 pb-16 sm:pt-28 lg:pt-36 lg:pb-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-1.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 font-semibold text-xs sm:text-sm shadow-sm mb-4 sm:mb-5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span><span>Portal Akademik Siswa</span>
                </div>
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight max-w-4xl mx-auto mb-4 sm:mb-5">
                    Pusat Informasi Akademik & <br class="hidden sm:inline" /><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700">Pembelajaran Siswa</span>
                </h1>
                <p class="text-sm sm:text-base lg:text-lg text-slate-600 max-w-2xl mx-auto mb-8 sm:mb-10 leading-relaxed px-2">Unduh materi pembelajaran dari guru, pantau catatan kehadiran, dan berdiskusi bersama teman sekelas dalam satu tempat.</p>
                <div class="flex justify-center mb-10 sm:mb-14">
                    <Link :href="$page.props.auth?.user ? route('dashboard') : route('login')" class="px-7 sm:px-8 py-3 sm:py-3.5 rounded-2xl font-bold text-sm sm:text-base text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-500/25 transition-all inline-flex items-center gap-2.5 shadow-md">
                        <span>Akses Akun Anda</span><ArrowRight class="w-4 h-4 sm:w-5 sm:h-5" />
                    </Link>
                </div>

                <!-- Showcase 3D Tilt Section -->
                <div id="simulasi" class="relative max-w-4xl mx-auto pt-2 sm:pt-6 pb-6 sm:pb-8 scroll-mt-28">
                    <button v-for="b in badges" :key="b.id" type="button" @click="selectTab(b.id)" class="hidden lg:flex absolute z-20 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-3.5 shadow-xl text-left transition-all hover:scale-105 cursor-pointer group" :class="[b.pos, b.anim, activeTab === b.id ? b.ring : '', bouncingBadge === b.id ? 'animate-bounce' : '']" :style="{ transform: `translate(${rotY * b.rot[0]}px, ${rotX * b.rot[1]}px)` }">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors border group-hover:text-white" :class="b.bg"><component :is="b.icon" class="w-4 h-4" /></div>
                            <div><span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" :class="b.tagCls">{{ b.tag }}</span><p class="text-xs font-semibold text-slate-700 mt-0.5">{{ b.title }}</p></div>
                        </div>
                    </button>

                    <div ref="containerRef" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave" class="relative perspective-1000">
                        <div class="relative bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-3xl p-4 sm:p-7 lg:p-8 shadow-2xl shadow-indigo-500/10 text-left overflow-hidden transition-transform duration-150 ease-out" :style="{ transform: `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg)` }">
                            <div class="absolute inset-0 pointer-events-none rounded-3xl transition-opacity duration-300" :style="{ background: `radial-gradient(circle 350px at ${glareX}% ${glareY}%, rgba(255, 255, 255, 0.7), transparent 80%)`, opacity: glareOpacity }"></div>
                            <div class="flex flex-wrap items-center justify-between gap-2.5 pb-4 mb-4 sm:pb-5 sm:mb-5 border-b border-slate-100">
                                <div class="flex items-center gap-2"><div class="flex gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span></div><span class="text-xs font-bold text-slate-700 ml-1.5">Simulasi Ruang Siswa</span></div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full bg-slate-100 border border-slate-200/70 text-[10px] sm:text-[11px] font-semibold text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span><span>Semester Ganjil 2026/2027 • Kelas XI PPLG 3</span></div>
                            </div>
                            <div class="grid grid-cols-3 gap-1.5 p-1.5 bg-slate-100/80 rounded-2xl mb-5 sm:mb-6 border border-slate-200/60">
                                <button v-for="t in tabs" :key="t.id" type="button" @click="selectTab(t.id)" class="px-2 sm:px-3.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-1.5 sm:gap-2 text-center cursor-pointer truncate" :class="activeTab === t.id ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                                    <component :is="t.icon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" /><span class="truncate">{{ t.label }}</span>
                                </button>
                            </div>

                            <Transition name="tab-fade" mode="out-in">
                                <div v-if="activeTab === 'materi'" key="materi" class="space-y-3">
                                    <div class="flex items-center justify-between pb-1"><div class="flex items-center gap-2 text-xs font-bold text-slate-700"><FileText class="w-4 h-4 text-indigo-600" /><span>Modul & Bahan Ajar Guru Terkini</span></div><span class="text-[10px] sm:text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">3 Modul Siap Unduh</span></div>
                                    <div v-if="downloadToast" class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-xs font-semibold text-indigo-800 flex items-center gap-2"><Download class="w-3.5 h-3.5 text-indigo-600 shrink-0" /><span>{{ downloadToast }}</span></div>
                                    <div v-for="m in modules" :key="m.title" class="p-3 sm:p-3.5 rounded-2xl bg-white border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-300 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shrink-0 border" :class="m.bg"><FileText class="w-4 h-4 sm:w-5 sm:h-5" /></div>
                                            <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-1.5"><h4 class="text-xs sm:text-sm font-bold text-slate-900">{{ m.title }}</h4><span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-600">{{ m.size }}</span></div><p class="text-[11px] sm:text-xs text-slate-600 mt-0.5">{{ m.author }}</p></div>
                                        </div>
                                        <button type="button" @click="triggerDownload(m.file)" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors w-full sm:w-auto shrink-0"><Download class="w-3.5 h-3.5" /><span>Unduh Modul</span></button>
                                    </div>
                                </div>
                                <div v-else-if="activeTab === 'presensi'" key="presensi" class="space-y-3.5">
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5">
                                        <div v-for="s in attendanceStats" :key="s.label" class="p-2.5 sm:p-3 rounded-2xl border text-center" :class="s.bg">
                                            <span class="text-[10px] font-bold uppercase tracking-wider block" :class="s.text">{{ s.label }}</span>
                                            <span class="text-base sm:text-lg font-black" :class="s.val">{{ s.count }}</span><span class="text-[10px] block" :class="s.sub">{{ s.desc }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-white border border-slate-200/80">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1 mb-1.5"><span class="font-bold text-slate-800">Tingkat Disiplin Kehadiran Semester Ini</span><span class="font-mono font-bold text-emerald-600">98.5% (Target 85%)</span></div>
                                        <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full" style="width: 98.5%"></div></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Catatan Harian Terbaru</div>
                                        <div v-for="h in attendanceHistory" :key="h.subject" class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs gap-1">
                                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span><span class="font-semibold text-slate-800">{{ h.subject }}</span><span class="text-slate-400">• {{ h.time }}</span></div>
                                            <span class="font-bold text-emerald-700 pl-4 sm:pl-0">{{ h.status }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="activeTab === 'diskusi'" key="diskusi" class="space-y-3">
                                    <div class="flex items-center justify-between pb-1"><div class="flex items-center gap-2 text-xs font-bold text-slate-700"><MessageSquare class="w-4 h-4 text-indigo-600" /><span>Forum Diskusi: Pemrograman Web (XI PPLG 3)</span></div><span class="text-[10px] sm:text-[11px] text-slate-500 font-medium">Topik: Keamanan REST API</span></div>
                                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                        <div v-for="post in discussionPosts" :key="post.id" class="p-3 rounded-2xl text-xs" :class="post.isTeacher ? 'bg-blue-50/70 border border-blue-200/80 ml-2 sm:ml-5' : 'bg-slate-50 border border-slate-200/70'">
                                            <div class="flex flex-wrap items-center justify-between gap-1 mb-1.5"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px]" :class="post.avatar">{{ post.author.charAt(0) }}</div><span class="font-bold text-slate-900">{{ post.author }}</span><span class="text-[10px] text-slate-500">({{ post.role }})</span></div><span class="text-[10px] text-slate-400">{{ post.time }}</span></div>
                                            <p class="text-slate-700 leading-relaxed pl-8">{{ post.content }}</p>
                                        </div>
                                    </div>
                                    <form @submit.prevent="sendDiscussion" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-2 border-t border-slate-100">
                                        <input type="text" v-model="discussionInput" placeholder="Tulis pertanyaan atau tanggapan simulasi..." class="flex-1 text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 bg-slate-50" />
                                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer w-full sm:w-auto"><span>Kirim</span><Send class="w-3 h-3" /></button>
                                    </form>
                                </div>
                            </Transition>

                            <div class="mt-4 sm:mt-5 pt-3 sm:pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-[11px] text-slate-400 gap-2">
                                <div class="flex items-center gap-1.5"><ShieldCheck class="w-3.5 h-3.5 text-emerald-600 shrink-0" /><span>Tampilan interaktif simulasi fitur siswa di SMKN 1 Ciomas</span></div>
                                <span>Klik 3 tab di atas untuk menguji fitur</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Fitur Siswa -->
                <div id="fitur" class="mt-16 sm:mt-20 text-left scroll-mt-28">
                    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 px-2">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Layanan Terpadu untuk Kebutuhan Belajar Siswa</h2>
                        <p class="text-xs sm:text-sm text-slate-600">Semua fitur dirancang ringkas dan mudah diakses dari komputer maupun ponsel siswa.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                        <div v-for="f in studentFeatures" :key="f.name" class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-md shadow-slate-200/30 hover:-translate-y-1 hover:shadow-xl transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4"><div class="w-12 h-12 rounded-2xl flex items-center justify-center border" :class="f.bg"><component :is="f.icon" :class="f.color" class="w-6 h-6" /></div><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ f.badge }}</span></div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2">{{ f.name }}</h3>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ f.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Panduan Siswa -->
                <div id="panduan" class="mt-16 sm:mt-20 text-left scroll-mt-28">
                    <div class="bg-gradient-to-b from-white to-slate-50/90 rounded-[2rem] sm:rounded-[2.5rem] border border-slate-200/90 p-6 sm:p-10 lg:p-12 shadow-sm">
                        <div class="max-w-2xl mb-8">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full">Panduan Siswa</span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-3 mb-2">Cara Mulai Menggunakan Portal</h2>
                            <p class="text-xs sm:text-sm text-slate-600">Tiga langkah mudah bagi siswa untuk mulai mengakses kegiatan belajar daring di Portal Akademik.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                            <div v-for="g in guideSteps" :key="g.step" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between group hover:border-indigo-300 transition-colors">
                                <div>
                                    <div class="flex items-center justify-between mb-4"><span class="text-xs font-mono font-black text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">Langkah {{ g.step }}</span><component :is="g.icon" class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 transition-colors" /></div>
                                    <h3 class="text-base font-bold text-slate-900 mb-2">{{ g.title }}</h3>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ g.desc }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 sm:mt-10 pt-6 border-t border-slate-200/70 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-xs text-slate-500 text-center sm:text-left">Butuh bantuan terkait akun belajar? Hubungi bagian Tata Usaha atau Tim IT SMKN 1 Ciomas.</div>
                            <Link :href="$page.props.auth?.user ? route('dashboard') : route('login')" class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-all inline-flex items-center justify-center gap-2 whitespace-nowrap">
                                <span>Masuk ke Portal Siswa</span><ArrowRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200/80 bg-white mt-auto">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                <div class="flex items-center gap-2.5"><img src="/images/logo-skanic.png" alt="Logo SMKN 1 Ciomas" class="w-6 h-6 object-contain" /><span class="font-bold text-slate-800 text-sm tracking-tight">SMKN 1 CIOMAS • Portal Akademik Siswa</span></div>
                <p class="text-xs text-slate-500">&copy; {{ new Date().getFullYear() }} SMKN 1 Ciomas. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes floatSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
@keyframes floatMedium { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
.animate-float-slow { animation: floatSlow 4s ease-in-out infinite; }
.animate-float-medium { animation: floatMedium 3.2s ease-in-out infinite 0.5s; }
.tab-fade-enter-active, .tab-fade-leave-active { transition: all 0.2s ease-out; }
.tab-fade-enter-from { opacity: 0; transform: translateY(6px); }
.tab-fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
