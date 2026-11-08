<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Mail, Clock, Eye, EyeOff, Pencil, CheckCircle2, Send } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const userEmail = computed(() => page.props.auth?.user?.email || '');

// State sensor email
const showFullEmail = ref(false);
const toggleShowEmail = () => {
    showFullEmail.value = !showFullEmail.value;
};

// Masking email: tampilkan 3 huruf awal dan 2 huruf akhir sebelum '@' (misal: faris123@gmail.com -> far***23@gmail.com)
const maskEmail = (email) => {
    if (!email || !email.includes('@')) return email;
    const [username, domain] = email.split('@');
    if (!username || !domain) return email;

    if (username.length > 5) {
        return `${username.slice(0, 3)}***${username.slice(-2)}@${domain}`;
    } else if (username.length > 2) {
        return `${username.slice(0, 1)}***${username.slice(-1)}@${domain}`;
    } else {
        return `${username.slice(0, 1)}***@${domain}`;
    }
};

const maskedEmail = computed(() => maskEmail(userEmail.value));

// Cooldown anti-spam 60 detik
const cooldownSeconds = ref(0);
let timer = null;

const startCooldown = (seconds = 60) => {
    cooldownSeconds.value = seconds;
    const expireTime = Date.now() + seconds * 1000;
    try {
        localStorage.setItem('verification_cooldown_until', expireTime.toString());
    } catch (e) {
        // Abaikan error jika storage dibatasi browser
    }

    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        const remaining = Math.max(0, Math.ceil((expireTime - Date.now()) / 1000));
        cooldownSeconds.value = remaining;
        if (remaining <= 0) {
            clearInterval(timer);
            timer = null;
            try {
                localStorage.removeItem('verification_cooldown_until');
            } catch (e) {}
        }
    }, 1000);
};

// Auto-polling status verifikasi (dukungan lintas tab / browser / perangkat)
const isAutoRedirecting = ref(false);
let pollInterval = null;

const checkVerificationStatus = async () => {
    try {
        const url = typeof route === 'function' ? route('verification.status') : '/email/verification-status';
        const response = await axios.get(url);
        if (response.data && response.data.verified) {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            isAutoRedirecting.value = true;
            setTimeout(() => {
                router.visit(route('dashboard'));
            }, 1000);
        }
    } catch (error) {
        // Abaikan error jaringan sementara saat polling di latar belakang
    }
};

onMounted(() => {
    try {
        const storedExpire = localStorage.getItem('verification_cooldown_until');
        if (storedExpire) {
            const remaining = Math.max(0, Math.ceil((parseInt(storedExpire, 10) - Date.now()) / 1000));
            if (remaining > 0) {
                startCooldown(remaining);
            } else {
                localStorage.removeItem('verification_cooldown_until');
            }
        }
    } catch (e) {}

    // Jalankan pengecekan setiap 3 detik
    pollInterval = setInterval(checkVerificationStatus, 3000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
});

// Form kirim ulang verifikasi
const resendForm = useForm({});

const submit = () => {
    if (cooldownSeconds.value > 0 || resendForm.processing) return;
    startCooldown(60);
    resendForm.post(route('verification.send'), {
        preserveScroll: true,
    });
};

// Form ubah email typo
const isEditingEmail = ref(false);
const emailForm = useForm({
    email: '',
});

const openEditEmail = () => {
    emailForm.email = userEmail.value;
    emailForm.clearErrors();
    isEditingEmail.value = true;
};

const cancelEditEmail = () => {
    emailForm.reset();
    emailForm.clearErrors();
    isEditingEmail.value = false;
};

const updateEmail = () => {
    emailForm.patch(route('verification.update-unverified'), {
        preserveScroll: true,
        onSuccess: () => {
            isEditingEmail.value = false;
            startCooldown(60);
        },
    });
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);

const emailUpdatedAndSent = computed(
    () => props.status === 'email-updated-and-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <!-- Visual Header Card -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="relative mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 ring-8 ring-indigo-50">
                    <Mail class="w-8 h-8" />
                </div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-400 border-2 border-white flex items-center justify-center text-amber-950 shadow-sm" title="Menunggu Konfirmasi">
                    <Clock class="w-3.5 h-3.5" />
                </div>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Alamat Email Anda</h2>
        </div>

        <!-- Notifikasi Status -->
        <div
            v-if="isAutoRedirecting"
            class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-300 p-4 text-sm text-emerald-800 flex items-center gap-3 shadow-md animate-pulse"
        >
            <CheckCircle2 class="w-6 h-6 text-emerald-600 flex-shrink-0" />
            <div>
                <p class="font-bold text-emerald-900">Email terverifikasi!</p>
                <p class="text-xs text-emerald-700 mt-0.5">Mengalihkan ke Dashboard...</p>
            </div>
        </div>

        <div
            v-else-if="emailUpdatedAndSent"
            class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-sm text-emerald-800 flex items-start gap-3 shadow-sm"
        >
            <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
            <div>
                <p class="font-semibold text-emerald-900">Alamat email berhasil diperbarui dan tautan verifikasi baru telah dikirim.</p>
                <p class="text-xs text-emerald-700 mt-0.5">Silakan periksa kotak masuk atau folder spam pada email baru Anda.</p>
            </div>
        </div>

        <div
            v-else-if="verificationLinkSent"
            class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-sm text-emerald-800 flex items-start gap-3 shadow-sm"
        >
            <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
            <div>
                <p class="font-semibold text-emerald-900">Tautan verifikasi baru berhasil dikirim ulang!</p>
                <p class="text-xs text-emerald-700 mt-0.5">Silakan periksa kotak masuk atau folder spam pada email Anda.</p>
            </div>
        </div>

        <!-- Badge Email Pengguna & Fitur Ubah Email -->
        <div class="mb-5 rounded-2xl bg-slate-50 border border-slate-200/80 p-4 transition-all">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                        Email Terdaftar
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm font-semibold text-slate-800 truncate" :title="showFullEmail ? userEmail : maskedEmail">
                            {{ showFullEmail ? userEmail : maskedEmail }}
                        </span>
                        <button
                            type="button"
                            @click="toggleShowEmail"
                            class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-200/60 transition-colors focus:outline-none"
                            :title="showFullEmail ? 'Sembunyikan email lengkap' : 'Lihat email lengkap (cek typo)'"
                        >
                            <EyeOff v-if="showFullEmail" class="w-4 h-4" />
                            <Eye v-else class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <button
                    v-if="!isEditingEmail"
                    type="button"
                    @click="openEditEmail"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100/80 px-2.5 py-1.5 rounded-lg transition-colors border border-indigo-100 whitespace-nowrap"
                >
                    <Pencil class="w-3 h-3" />
                    <span>Ubah Email</span>
                </button>
            </div>

            <!-- Form Inline Ubah Email Typo -->
            <div v-if="isEditingEmail" class="mt-3 pt-3 border-t border-slate-200/80">
                <form @submit.prevent="updateEmail" class="space-y-3">
                    <div>
                        <InputLabel for="new_email" value="Alamat Email Baru" class="text-xs font-semibold text-slate-700 mb-1" />
                        <TextInput
                            id="new_email"
                            type="email"
                            v-model="emailForm.email"
                            class="block w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="nama@gmail.com"
                            required
                            autofocus
                        />
                        <p class="text-[11px] text-slate-400 mt-1">
                            Gunakan domain resmi (@gmail.com, @yahoo.com, @outlook.com, @icloud.com, @belajar.id, .sch.id, .ac.id).
                        </p>
                        <InputError :message="emailForm.errors.email" class="mt-1.5" />
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <PrimaryButton
                            type="submit"
                            class="py-2 px-3 text-xs justify-center bg-indigo-600 hover:bg-indigo-700 rounded-xl font-medium"
                            :class="{ 'opacity-50 cursor-not-allowed': emailForm.processing }"
                            :disabled="emailForm.processing"
                        >
                            <span v-if="emailForm.processing">Menyimpan...</span>
                            <span v-else>Simpan & Kirim Ulang</span>
                        </PrimaryButton>

                        <SecondaryButton
                            type="button"
                            @click="cancelEditEmail"
                            class="py-2 px-3 text-xs rounded-xl"
                            :disabled="emailForm.processing"
                        >
                            Batal
                        </SecondaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deskripsi Informasi -->
        <div class="mb-4 text-sm text-slate-600 leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda
            dengan mengklik tautan yang baru saja kami kirimkan ke email Anda.
            <span class="font-medium text-slate-700">Akun Anda belum aktif sebelum dikonfirmasi.</span>
        </div>

        <!-- Info Masa Berlaku 15 Menit -->
        <div class="mb-5 rounded-xl bg-amber-50/90 border border-amber-200/80 p-3 text-xs text-amber-900 flex items-start sm:items-center gap-2.5">
            <Clock class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5 sm:mt-0" />
            <span>Tautan verifikasi hanya berlaku selama <strong>15 menit</strong>. Akun yang belum terverifikasi setelah 15 menit akan dibersihkan otomatis demi keamanan.</span>
        </div>

        <!-- Tombol Aksi Utama -->
        <form @submit.prevent="submit">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <PrimaryButton
                    type="submit"
                    class="w-full sm:w-auto flex-1 justify-center py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all text-sm font-semibold flex items-center gap-2"
                    :class="{ 'opacity-50 cursor-not-allowed': resendForm.processing || cooldownSeconds > 0 || isAutoRedirecting }"
                    :disabled="resendForm.processing || cooldownSeconds > 0 || isAutoRedirecting"
                >
                    <Send class="w-4 h-4" />
                    <span>
                        {{ cooldownSeconds > 0 ? `Kirim Ulang dalam ${cooldownSeconds}d...` : 'Kirim Ulang Email Verifikasi' }}
                    </span>
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl text-sm font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Keluar (Logout)
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
