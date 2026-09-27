<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-slate-900">Buat Akun Baru</h2>
            <p class="text-slate-500 mt-2 text-sm">Bergabung dengan ekosistem digital portal akademik kami.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="Nama Lengkap" class="text-slate-700 font-semibold" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Contoh: Budi Santoso"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Alamat Email" class="text-slate-700 font-semibold" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="nama@sekolah.test"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" class="text-slate-700 font-semibold" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Kata Sandi"
                    class="text-slate-700 font-semibold"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ketik ulang kata sandi"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <UserPlus size="18" /> Daftar Akun
                </PrimaryButton>
            </div>
            
            <div class="text-center mt-6 text-sm text-slate-500">
                Sudah memiliki akun?
                <Link
                    :href="route('login')"
                    class="font-bold text-indigo-600 hover:text-indigo-800 transition-colors"
                >
                    Masuk di sini
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
