<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Send, ArrowLeft } from 'lucide-vue-next';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi" />

        <div class="mb-6">
            <Link :href="route('login')" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-6">
                <ArrowLeft size="16" /> Kembali ke Login
            </Link>
            
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Lupa Kata Sandi?</h2>
            <p class="text-slate-500 text-sm leading-relaxed">
                Tidak masalah. Beri tahu kami alamat email Anda, dan kami akan mengirimkan tautan pengaturan ulang kata sandi yang memungkinkan Anda memilih kata sandi baru.
            </p>
        </div>

        <div v-if="status" class="mb-6 text-sm font-medium text-green-600 bg-green-50 p-4 rounded-xl border border-green-200">
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

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <Send size="18" /> Kirim Tautan Reset
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
