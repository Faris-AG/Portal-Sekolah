<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    School, BookOpen, CalendarCheck, CheckSquare, 
    ArrowRight, User, ShieldCheck, Mail
} from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const features = [
    {
        name: 'Manajemen Jadwal',
        description: 'Penjadwalan kelas cerdas yang terintegrasi untuk siswa dan guru, memudahkan pemantauan sesi belajar harian tanpa konflik.',
        icon: CalendarCheck,
        color: 'text-blue-600',
        bg: 'bg-blue-100',
    },
    {
        name: 'LMS & Penugasan',
        description: 'Modul distribusi tugas dan pengumpulan secara digital dengan sistem penilaian yang transparan dan umpan balik langsung.',
        icon: BookOpen,
        color: 'text-indigo-600',
        bg: 'bg-indigo-100',
    },
    {
        name: 'Presensi Digital',
        description: 'Pencatatan kehadiran siswa secara real-time oleh guru untuk setiap mata pelajaran, mendukung transparansi kedisiplinan.',
        icon: CheckSquare,
        color: 'text-emerald-600',
        bg: 'bg-emerald-100',
    }
];

const demoCredentials = [
    { role: 'Administrator', email: 'admin@sekolah.test', pass: 'password', icon: ShieldCheck, color: 'border-blue-200 bg-blue-50 text-blue-800' },
    { role: 'Guru Pengajar', email: 'guru@sekolah.test', pass: 'password', icon: User, color: 'border-emerald-200 bg-emerald-50 text-emerald-800' },
    { role: 'Siswa', email: 'siswa@sekolah.test', pass: 'password', icon: BookOpen, color: 'border-orange-200 bg-orange-50 text-orange-800' }
];
</script>

<template>
    <Head title="Selamat Datang di Portal Akademik" />

    <div class="bg-slate-50 min-h-screen font-sans selection:bg-indigo-500 selection:text-white flex flex-col">
        
        <!-- Navigation -->
        <nav class="absolute top-0 w-full z-50 px-6 py-4 lg:px-8">
            <div class="max-w-7xl mx-auto flex justify-between items-center bg-white/80 backdrop-blur-md px-6 py-3 rounded-full border border-white/50 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white shadow-md">
                        <School size="24" />
                    </div>
                    <span class="text-xl font-black text-slate-900 tracking-tight uppercase">Portal Akademik</span>
                </div>

                <div v-if="canLogin" class="flex gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-5 py-2.5 rounded-full font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all"
                    >
                        Masuk Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-5 py-2.5 rounded-full font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all flex items-center gap-2"
                        >
                            Masuk <ArrowRight size="16" />
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow pt-32 pb-16 lg:pt-40 lg:pb-24">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm mb-8 border border-indigo-200 shadow-sm">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                    </span>
                    Sistem Informasi Akademik Terintegrasi 2.0
                </div>

                <h1 class="text-5xl lg:text-7xl font-black text-slate-900 tracking-tight leading-tight mb-6 max-w-4xl mx-auto">
                    Masa Depan Pendidikan <br class="hidden sm:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-cyan-500">
                        Dimulai Dari Sini.
                    </span>
                </h1>

                <p class="text-lg lg:text-xl text-slate-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Platform manajemen sekolah modern untuk menjembatani komunikasi, administrasi, dan pembelajaran antara siswa, guru, dan staf dengan pengalaman digital terbaik.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-8 py-4 rounded-full font-bold text-lg text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-500/30 transition-all flex items-center justify-center gap-2"
                    >
                        Akses Akun Anda <ArrowRight size="20" />
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="px-8 py-4 rounded-full font-bold text-lg text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-500/30 transition-all flex items-center justify-center gap-2"
                    >
                        Akses Akun Anda <ArrowRight size="20" />
                    </Link>
                </div>
            </div>

            <!-- Features -->
            <div class="max-w-7xl mx-auto px-6 lg:px-8 mt-24">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="feature in features" :key="feature.name" class="bg-white rounded-3xl p-8 border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition-transform duration-300">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6" :class="feature.bg">
                            <component :is="feature.icon" :class="feature.color" size="28" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ feature.name }}</h3>
                        <p class="text-slate-500 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Demo Credentials -->
            <div class="max-w-5xl mx-auto px-6 lg:px-8 mt-24">
                <div class="bg-slate-900 rounded-[2.5rem] p-8 md:p-12 relative overflow-hidden shadow-2xl">
                    <div class="absolute -right-20 -top-20 w-72 h-72 bg-indigo-500 rounded-full blur-3xl opacity-30 pointer-events-none"></div>
                    <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-cyan-500 rounded-full blur-3xl opacity-20 pointer-events-none"></div>
                    
                    <div class="relative z-10 text-center mb-10">
                        <h2 class="text-3xl font-black text-white mb-3">Siap Untuk Eksplorasi?</h2>
                        <p class="text-slate-400">Gunakan kredensial demo di bawah ini untuk mencoba sistem dari berbagai sudut pandang (Role).</p>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="demo in demoCredentials" :key="demo.role" 
                             class="bg-slate-800/80 backdrop-blur border border-slate-700 p-5 rounded-2xl flex flex-col hover:bg-slate-800 transition-colors">
                            <div class="flex items-center gap-2 mb-4">
                                <component :is="demo.icon" class="text-slate-400" size="18" />
                                <span class="text-sm font-bold text-white uppercase tracking-wider">{{ demo.role }}</span>
                            </div>
                            <div class="space-y-3 flex-grow">
                                <div class="bg-slate-900/50 p-3 rounded-lg border border-slate-700/50">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Email / Username</div>
                                    <div class="text-sm text-slate-300 font-mono flex items-center justify-between">
                                        {{ demo.email }}
                                    </div>
                                </div>
                                <div class="bg-slate-900/50 p-3 rounded-lg border border-slate-700/50">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Password</div>
                                    <div class="text-sm text-slate-300 font-mono">{{ demo.pass }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white mt-auto">
            <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <School class="text-indigo-600" size="20" />
                    <span class="font-bold text-slate-800">PORTAL AKADEMIK</span>
                </div>
                <p class="text-sm text-slate-500 font-medium">
                    &copy; {{ new Date().getFullYear() }} Sistem Informasi Sekolah. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>
