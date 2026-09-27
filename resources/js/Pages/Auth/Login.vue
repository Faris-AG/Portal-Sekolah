<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LogIn } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk Portal" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-slate-900">Selamat Datang Kembali</h2>
            <p class="text-slate-500 mt-2 text-sm">Silakan masukkan email dan kata sandi Anda untuk mengakses portal.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Alamat Email" class="text-slate-700 font-semibold" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@sekolah.test"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex justify-between items-center mt-4">
                    <InputLabel for="password" value="Kata Sandi" class="text-slate-700 font-semibold" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                    >
                        Lupa kata sandi?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="border-slate-300 text-indigo-600 focus:ring-indigo-500 rounded" />
                    <span class="text-sm text-slate-600 font-medium group-hover:text-slate-900 transition-colors">
                        Ingat saya di perangkat ini
                    </span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <LogIn size="18" /> Masuk ke Portal
                </PrimaryButton>
            </div>
            
            <div class="text-center mt-6 text-sm text-slate-500">
                Belum memiliki akun?
                <Link :href="route('register')" class="font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                    Daftar Sekarang
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
