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
    School
} from 'lucide-vue-next';

const showingSidebar = ref(false);

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
        { name: 'Kelas', href: route('admin.classes.index'), current: route().current('admin.classes.*'), icon: GraduationCap },
        { name: 'Siswa', href: route('admin.students.index'), current: route().current('admin.students.*'), icon: Users },
        { name: 'Mata Pelajaran', href: route('admin.subjects.index'), current: route().current('admin.subjects.*'), icon: BookOpen },
        { name: 'Jadwal Pelajaran', href: route('admin.schedules.index'), current: route().current('admin.schedules.*'), icon: Calendar }
    );
} else if (userRole === 'guru') {
    navigation.push(
        { name: 'Dashboard', href: route('guru.dashboard'), current: route().current('guru.dashboard'), icon: LayoutDashboard },
        { name: 'Presensi Siswa', href: route('guru.attendance.create'), current: route().current('guru.attendance.*'), icon: CheckSquare }
    );
} else if (userRole === 'siswa') {
    navigation.push(
        { name: 'Dashboard', href: route('siswa.dashboard'), current: route().current('siswa.dashboard'), icon: LayoutDashboard }
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
            class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col shadow-xl"
            :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo & Portal Name -->
            <div class="flex items-center gap-3 px-6 py-5 h-16 bg-slate-950/50 border-b border-slate-800 shrink-0">
                <div class="flex items-center justify-center w-8 h-8 rounded bg-indigo-500 text-white">
                    <School size="20" />
                </div>
                <div class="flex-1 overflow-hidden">
                    <h1 class="text-sm font-bold text-white tracking-wider truncate uppercase">Portal Akademik</h1>
                </div>
                <button @click="showingSidebar = false" class="lg:hidden text-slate-400 hover:text-white">
                    <X size="20" />
                </button>
            </div>

            <!-- User Info (Sidebar) -->
            <div class="px-6 py-6 border-b border-slate-800 flex flex-col items-center shrink-0">
                <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-indigo-500 flex items-center justify-center text-white text-xl font-bold shadow-inner mb-3">
                    {{ userInitials }}
                </div>
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
                            : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                    ]"
                >
                    <component 
                        :is="item.icon" 
                        class="mr-3 flex-shrink-0 h-5 w-5 transition-colors duration-200" 
                        :class="[item.current ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300']"
                        aria-hidden="true" 
                    />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- Bottom Sidebar Actions -->
            <div class="p-4 border-t border-slate-800 shrink-0">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button" 
                    class="group flex w-full items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-colors"
                >
                    <LogOut class="mr-3 h-5 w-5 text-slate-500 group-hover:text-red-400" />
                    Keluar Sistem
                </Link>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-10 shadow-sm">
                <!-- Left side (Mobile toggle & Page Header) -->
                <div class="flex items-center gap-4">
                    <button 
                        @click="showingSidebar = true"
                        class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-1 rounded-md hover:bg-gray-100"
                    >
                        <Menu size="24" />
                    </button>

                    <!-- Render slot name="header" if exists -->
                    <div class="hidden sm:block" v-if="$slots.header">
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
