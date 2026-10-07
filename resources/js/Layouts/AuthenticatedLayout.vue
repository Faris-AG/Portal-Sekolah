<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { 
    LayoutDashboard, 
    GraduationCap, 
    Users, 
    BookOpen, 
    Calendar, 
    CheckSquare, 
    Menu, 
    X, 
    LogOut, 
    User,
    School,
    ChevronLeft,
    ChevronRight,
    Award,
    Megaphone,
    Library,
    MessageCircle
} from 'lucide-vue-next';

const showingSidebar = ref(false);
const isSidebarCollapsed = ref(false);

const flashSuccess = ref('');
const flashError = ref('');
let timeoutId = null;

const showToast = (success, error) => {
    flashSuccess.value = success || '';
    flashError.value = error || '';
    
    if (timeoutId) clearTimeout(timeoutId);
    
    if (success || error) {
        timeoutId = setTimeout(() => {
            flashSuccess.value = '';
            flashError.value = '';
        }, 3500);
    }
};

watch(
    () => usePage().props.flash,
    (flash) => {
        showToast(flash.success, flash.error);
    },
    { deep: true, immediate: true }
);

const userRole = usePage().props.auth.user.role;
const userInitials = usePage().props.auth.user.name.substring(0, 2).toUpperCase();

// Navigation definitions based on role
const navigation = [];

if (userRole === 'admin') {
    navigation.push(
        { name: 'Dashboard', href: route('admin.dashboard'), current: route().current('admin.dashboard'), icon: LayoutDashboard },
        { name: 'Guru', href: route('admin.teachers.index'), current: route().current('admin.teachers.*'), icon: GraduationCap },
        { name: 'Kelas', href: route('admin.classes.index'), current: route().current('admin.classes.*'), icon: School },
        { name: 'Siswa', href: route('admin.students.index'), current: route().current('admin.students.*'), icon: Users },
        { name: 'Mata Pelajaran', href: route('admin.subjects.index'), current: route().current('admin.subjects.*'), icon: BookOpen },
        { name: 'Jadwal Pelajaran', href: route('admin.schedules.index'), current: route().current('admin.schedules.*'), icon: Calendar },
        { name: 'Presensi', href: route('admin.attendance.index'), current: route().current('admin.attendance.*'), icon: CheckSquare },
        { name: 'Forum Diskusi', href: route('admin.forum.index'), current: route().current('admin.forum.*'), icon: MessageCircle },
        { name: 'Pengumuman', href: route('admin.announcements.index'), current: route().current('admin.announcements.*'), icon: Megaphone }
    );
} else if (userRole === 'guru') {
    navigation.push(
        { name: 'Dashboard', href: route('guru.dashboard'), current: route().current('guru.dashboard'), icon: LayoutDashboard },
        { name: 'Materi Belajar', href: route('guru.materials.index'), current: route().current('guru.materials.*'), icon: Library },
        { name: 'Presensi Siswa', href: route('guru.attendance.create'), current: route().current('guru.attendance.create'), icon: CheckSquare },
        { name: 'Laporan Presensi', href: route('guru.attendance.index'), current: route().current('guru.attendance.index'), icon: CheckSquare },
        { name: 'Forum Diskusi', href: route('guru.forum.index'), current: route().current('guru.forum.*'), icon: MessageCircle }
    );
} else if (userRole === 'siswa') {
    navigation.push(
        { name: 'Dashboard', href: route('siswa.dashboard'), current: route().current('siswa.dashboard'), icon: LayoutDashboard },
        { name: 'Jadwal Saya', href: route('siswa.schedules'), current: route().current('siswa.schedules'), icon: Calendar },
        { name: 'Materi Belajar', href: route('siswa.materials.index'), current: route().current('siswa.materials.*'), icon: Library },
        { name: 'Tugas Belajar', href: route('siswa.assignments'), current: route().current('siswa.assignments') || route().current('siswa.assignments.show'), icon: BookOpen },
        { name: 'Rekap Nilai', href: route('siswa.grades'), current: route().current('siswa.grades'), icon: Award },
        { name: 'Riwayat Kehadiran', href: route('siswa.attendance'), current: route().current('siswa.attendance'), icon: CheckSquare },
        { name: 'Forum Diskusi', href: route('siswa.forum.index'), current: route().current('siswa.forum.*'), icon: MessageCircle }
    );
}
</script>

<template>
    <div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
        
        <!-- Toast Notifications -->
        <transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="opacity-0 translate-y-[-20px] sm:translate-y-0 sm:translate-x-10"
            enter-to-class="opacity-100 translate-y-0 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Toast v-if="flashSuccess" :message="flashSuccess" type="success" />
            <Toast v-else-if="flashError" :message="flashError" type="error" />
        </transition>

        <!-- Mobile Sidebar Overlay -->
        <div 
            v-show="showingSidebar" 
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
            @click="showingSidebar = false"
        ></div>

        <!-- Sidebar Navigation -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 bg-slate-900 text-slate-300 transition-all duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col shadow-xl"
            :class="[
                showingSidebar ? 'translate-x-0' : '-translate-x-full',
                isSidebarCollapsed ? 'w-20' : 'w-72'
            ]"
        >
            <!-- Logo & Portal Name -->
            <div class="flex items-center gap-3 px-6 py-5 h-16 bg-slate-950/50 border-b border-slate-800 shrink-0 relative overflow-hidden transition-all duration-300" :class="isSidebarCollapsed ? 'px-0 justify-center' : ''">
                <div class="flex items-center justify-center w-8 h-8 rounded bg-indigo-500 text-white shrink-0">
                    <School size="20" />
                </div>
                <div class="flex-1 overflow-hidden transition-opacity duration-300" :class="isSidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">
                    <h1 class="text-sm font-bold text-white tracking-wider truncate uppercase">Portal Akademik</h1>
                </div>
                <button @click="showingSidebar = false" class="lg:hidden text-slate-400 hover:text-white shrink-0">
                    <X size="20" />
                </button>
            </div>

            <!-- User Info (Sidebar) -->
            <div class="px-6 py-6 border-b border-slate-800 flex flex-col items-center shrink-0 transition-all duration-300" :class="isSidebarCollapsed ? 'px-2' : ''">
                <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-indigo-500 flex items-center justify-center text-white text-lg font-bold shadow-inner mb-3 shrink-0" :class="!isSidebarCollapsed ? 'w-16 h-16 text-xl' : ''">
                    {{ userInitials }}
                </div>
                <div class="flex flex-col items-center overflow-hidden transition-all duration-300" :class="isSidebarCollapsed ? 'h-0 opacity-0' : 'h-auto opacity-100'">
                    <h3 class="text-white font-bold text-base truncate w-full text-center">{{ $page.props.auth.user.name }}</h3>
                    <div class="mt-2 inline-flex items-center justify-center px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest"
                        :class="{
                            'bg-blue-500/20 text-blue-400 border border-blue-500/30': userRole === 'admin',
                            'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': userRole === 'guru',
                            'bg-orange-500/20 text-orange-400 border border-orange-500/30': userRole === 'siswa',
                        }"
                    >
                        {{ userRole }}
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200"
                    :class="[
                        item.current 
                            ? 'bg-indigo-500/10 text-indigo-400' 
                            : 'text-slate-400 hover:bg-slate-800 hover:text-white',
                        isSidebarCollapsed ? 'justify-center px-0' : ''
                    ]"
                    :title="isSidebarCollapsed ? item.name : ''"
                >
                    <component 
                        :is="item.icon" 
                        class="flex-shrink-0 h-5 w-5 transition-colors duration-200" 
                        :class="[
                            item.current ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300',
                            !isSidebarCollapsed ? 'mr-3' : ''
                        ]"
                        aria-hidden="true" 
                    />
                    <span class="truncate transition-opacity duration-300" :class="isSidebarCollapsed ? 'hidden opacity-0' : 'opacity-100'">
                        {{ item.name }}
                    </span>
                </Link>
            </nav>

            <!-- Bottom Sidebar Actions -->
            <div class="p-4 border-t border-slate-800 shrink-0">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button" 
                    class="group flex w-full items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-colors"
                    :class="isSidebarCollapsed ? 'justify-center px-0' : ''"
                    :title="isSidebarCollapsed ? 'Keluar Sistem' : ''"
                >
                    <LogOut class="h-5 w-5 text-slate-500 group-hover:text-red-400" :class="!isSidebarCollapsed ? 'mr-3' : ''" />
                    <span class="truncate transition-opacity duration-300" :class="isSidebarCollapsed ? 'hidden opacity-0' : 'opacity-100'">
                        Keluar Sistem
                    </span>
                </Link>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-10 shadow-sm">
                <!-- Left side (Mobile toggle & Page Header) -->
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <button 
                        @click="showingSidebar = true"
                        class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-1 rounded-md hover:bg-gray-100 shrink-0"
                    >
                        <Menu size="24" />
                    </button>
                    
                    <button 
                        @click="isSidebarCollapsed = !isSidebarCollapsed"
                        class="hidden lg:flex text-gray-500 hover:text-gray-700 focus:outline-none p-1 rounded-md hover:bg-gray-100 shrink-0"
                    >
                        <ChevronRight v-if="isSidebarCollapsed" size="24" />
                        <ChevronLeft v-else size="24" />
                    </button>

                    <!-- Render slot name="header" if exists -->
                    <div class="hidden sm:block flex-1 min-w-0 pr-4" v-if="$slots.header">
                        <slot name="header" />
                    </div>
                </div>

                <!-- Right side (Profile Dropdown) -->
                <div class="flex items-center">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 p-1 border border-transparent hover:border-gray-200 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                    {{ userInitials }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 hidden md:block">{{ $page.props.auth.user.name }}</span>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm">Login sebagai</p>
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $page.props.auth.user.email }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                <User size="16" /> Profile
                            </DropdownLink>
                            <div class="border-t border-gray-100"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                                <LogOut size="16" /> Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Render header slot for mobile if exists -->
            <div class="sm:hidden bg-white border-b border-gray-200 px-4 py-3" v-if="$slots.header">
                <slot name="header" />
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto focus:outline-none relative">
                <!-- Background decoration element (optional) -->
                <div class="absolute top-0 inset-x-0 h-40 bg-gradient-to-b from-slate-100 to-slate-50 -z-10"></div>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
</style>
