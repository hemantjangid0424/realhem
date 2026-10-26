<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" @click="closeCountryDropdown">
        <!-- Backdrop -->
        <div
            @click="closeModal"
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-300"
        ></div>

        <!-- Modal Dialog -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                @click.stop
                class="relative transform overflow-visible rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100 p-6 sm:p-8"
            >
                <!-- Close Button -->
                <button
                    @click="closeModal"
                    class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-1.5 rounded-full hover:bg-slate-100 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- STEP 1: Enter Mobile Number -->
                <div v-if="step === 'mobile'" class="space-y-6">
                    <div class="text-center space-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Login / Register</h3>
                        <p class="text-xs text-slate-500">Enter your mobile number to receive an OTP</p>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>{{ errorMessage }}</span>
                    </div>

                    <form @submit.prevent="handleSendOtp" class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mobile Number</label>
                                <span class="text-[11px] text-slate-400 font-semibold">{{ selectedCountry.name }}</span>
                            </div>

                            <!-- Modern Unified Input Box -->
                            <div class="relative flex items-center rounded-2xl border border-slate-300 bg-white shadow-2xs focus-within:border-blue-600 focus-within:ring-4 focus-within:ring-blue-500/10 focus-within:shadow-md transition-all">
                                <!-- Country Selector Trigger -->
                                <button
                                    type="button"
                                    @click.stop="isCountryDropdownOpen = !isCountryDropdownOpen"
                                    class="flex items-center gap-2 pl-3.5 pr-2.5 py-3 text-xs font-bold text-slate-800 transition cursor-pointer shrink-0 border-r border-slate-200 hover:bg-slate-50/80 rounded-l-2xl select-none"
                                >
                                    <span class="text-xl leading-none">{{ selectedCountry.flag }}</span>
                                    <span class="text-sm font-bold text-slate-800 tracking-tight">{{ selectedCountry.code }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isCountryDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <!-- Phone Input -->
                                <input
                                    v-model="mobile"
                                    type="tel"
                                    maxlength="10"
                                    placeholder="Enter 10-digit mobile number"
                                    required
                                    autofocus
                                    class="w-full bg-transparent py-3 px-3.5 text-base font-semibold text-slate-900 tracking-wider outline-none placeholder:text-slate-400 placeholder:font-normal placeholder:tracking-normal rounded-r-2xl"
                                />

                                <!-- Country Dropdown Menu (India only) -->
                                <div
                                    v-if="isCountryDropdownOpen"
                                    @click.stop
                                    class="absolute top-full left-0 mt-2 w-full z-50 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 text-left"
                                >
                                    <button
                                        v-for="c in filteredCountries"
                                        :key="c.code + c.name"
                                        type="button"
                                        @click="selectCountry(c)"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs rounded-xl bg-blue-50 text-blue-700 font-bold transition cursor-pointer text-left"
                                    >
                                        <div class="flex items-center gap-2.5 truncate">
                                            <span class="text-xl leading-none shrink-0">{{ c.flag }}</span>
                                            <span class="truncate font-semibold">{{ c.name }}</span>
                                        </div>
                                        <span class="font-mono text-xs text-blue-600 font-bold">{{ c.code }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading || mobile.length < 10"
                            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <span v-if="isLoading" class="inline-block animate-spin">&#8635;</span>
                            <span>{{ isLoading ? 'Sending OTP...' : 'Send OTP &rarr;' }}</span>
                        </button>
                    </form>

                    <p class="text-[11px] text-center text-slate-400">
                        By continuing, you agree to RealHem's Terms of Service &amp; Privacy Policy.
                    </p>
                </div>

                <!-- STEP 2: Verify OTP -->
                <div v-else-if="step === 'otp'" class="space-y-6">
                    <div class="text-center space-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Verify Mobile Number</h3>
                        <p class="text-xs text-slate-500">
                            Sent to <strong class="text-slate-700">{{ selectedCountry.flag }} {{ selectedCountry.code }} {{ mobile }}</strong>
                            <button @click="step = 'mobile'" class="ml-1 text-blue-600 font-bold hover:underline cursor-pointer">Edit</button>
                        </p>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        {{ errorMessage }}
                    </div>

                    <form @submit.prevent="handleVerifyOtp" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2 text-center">
                                Enter 4-Digit OTP
                            </label>
                            <input
                                v-model="otp"
                                type="text"
                                maxlength="4"
                                placeholder="• • • •"
                                required
                                autofocus
                                class="w-full text-center text-2xl font-black tracking-[0.5em] py-3 bg-slate-50 border border-slate-300 rounded-2xl outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading || otp.length < 4"
                            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <span v-if="isLoading" class="inline-block animate-spin">&#8635;</span>
                            <span>{{ isLoading ? 'Verifying OTP...' : 'Verify &amp; Continue' }}</span>
                        </button>
                    </form>

                    <div class="flex items-center justify-center gap-1 text-xs text-slate-500">
                        <span>Didn't receive the OTP?</span>
                        <button
                            type="button"
                            @click="handleSendOtp"
                            class="text-blue-600 font-bold hover:underline cursor-pointer"
                        >
                            Resend Code
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Complete Profile (New User) -->
                <div v-else-if="step === 'profile'" class="space-y-6">
                    <div class="text-center space-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 font-bold text-xl">
                            👋
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Complete Your Profile</h3>
                        <p class="text-xs text-slate-500">You're new to RealHem! Tell us how to address you.</p>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        {{ errorMessage }}
                    </div>

                    <form @submit.prevent="handleCompleteProfile" class="space-y-4 text-left">
                        <!-- Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Full Name</label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                placeholder="e.g. Hemant Sharma"
                                required
                                autofocus
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email Address</label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                placeholder="e.g. hemant@example.com"
                                required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition"
                            />
                        </div>

                        <!-- Role Selector -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">I am a:</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    v-for="r in [
                                        { id: 'user', label: 'Buyer / Tenant' },
                                        { id: 'owner', label: 'Owner' },
                                        { id: 'agent', label: 'Agent' },
                                    ]"
                                    :key="r.id"
                                    type="button"
                                    @click="profileForm.role = r.id"
                                    class="py-2 px-2 rounded-xl text-[11px] font-bold border transition cursor-pointer text-center"
                                    :class="profileForm.role === r.id ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                >
                                    {{ r.label }}
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading || !profileForm.name || !profileForm.email"
                            class="w-full mt-2 py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <span v-if="isLoading" class="inline-block animate-spin">&#8635;</span>
                            <span>{{ isLoading ? 'Creating Profile...' : 'Complete &amp; Continue &rarr;' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'authenticated']);

// Domestic Indian Real Estate Focus (only India in dropdown)
const countries = [
    { name: 'India', code: '+91', flag: '🇮🇳', placeholder: '98765 43210', minLength: 10, maxLength: 10 },
];

const selectedCountry = ref(countries[0]);
const isCountryDropdownOpen = ref(false);

const filteredCountries = computed(() => countries);

const selectCountry = (c) => {
    selectedCountry.value = c;
    isCountryDropdownOpen.value = false;
};

const closeCountryDropdown = () => {
    isCountryDropdownOpen.value = false;
};

const step = ref('mobile'); // 'mobile' | 'otp' | 'profile'
const mobile = ref('');
const otp = ref('');
const devOtp = ref('1234');
const sessionToken = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

const profileForm = ref({
    name: '',
    email: '',
    role: 'user',
});

const closeModal = () => {
    errorMessage.value = '';
    isCountryDropdownOpen.value = false;
    emit('close');
};

const handleSendOtp = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/api/auth/send-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                country_code: selectedCountry.value.code,
                mobile: mobile.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.errors?.mobile?.[0] || data.errors?.country_code?.[0] || data.message || 'Could not send OTP. Please check the number.';
            return;
        }

        if (data.dev_otp) {
            devOtp.value = data.dev_otp;
        }

        step.value = 'otp';
        otp.value = '';
    } catch (err) {
        console.error(err);
        errorMessage.value = 'Network error. Please try again.';
    } finally {
        isLoading.value = false;
    }
};

const handleVerifyOtp = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/api/auth/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                country_code: selectedCountry.value.code,
                mobile: mobile.value,
                otp: otp.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.errors?.otp?.[0] || data.message || 'Invalid OTP. Please try again.';
            return;
        }

        if (data.status === 'logged_in') {
            // User already registered
            finishAuth(data.user, data.token);
        } else if (data.status === 'requires_profile') {
            // New user needs to supply name & email
            sessionToken.value = data.session_token;
            step.value = 'profile';
        }
    } catch (err) {
        console.error(err);
        errorMessage.value = 'Network error. Please try again.';
    } finally {
        isLoading.value = false;
    }
};

const handleCompleteProfile = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/api/auth/complete-profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                session_token: sessionToken.value,
                name: profileForm.value.name,
                email: profileForm.value.email,
                role: profileForm.value.role,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.errors?.email?.[0] || data.errors?.name?.[0] || data.message || 'Could not complete profile.';
            return;
        }

        finishAuth(data.user, data.token);
    } catch (err) {
        console.error(err);
        errorMessage.value = 'Network error. Please try again.';
    } finally {
        isLoading.value = false;
    }
};

const { syncGuestActivitiesToServer } = useUserActivity();

const finishAuth = async (user, token) => {
    localStorage.setItem('realhem_user_token', token);
    localStorage.setItem('realhem_user', JSON.stringify(user));
    emit('authenticated', user);
    closeModal();
    step.value = 'mobile';

    try {
        await syncGuestActivitiesToServer();
    } catch {
        // non-blocking
    }
};
</script>
