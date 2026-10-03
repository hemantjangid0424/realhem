<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white relative overflow-hidden bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-900 to-black">
        <!-- Ambient background lighting -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Header & Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white font-black text-2xl shadow-xl shadow-blue-500/25 mb-4 border border-blue-400/20">
                    RH
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white flex items-center justify-center gap-2">
                    RealHem Admin SPA
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    99acres Real Estate Portal &bull; Vue 3 Admin Console
                </p>
            </div>

            <!-- Login Form Card -->
            <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 rounded-2xl shadow-2xl p-6 sm:p-8">
                <!-- Error Banner -->
                <div v-if="errorMessage" class="mb-5 p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ errorMessage }}</span>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <!-- Email field -->
                    <div>
                        <label for="admin-email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Admin Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                            </div>
                            <input
                                v-model="form.email"
                                type="email"
                                id="admin-email"
                                required
                                placeholder="admin@realhem.com"
                                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-white placeholder-slate-500 text-sm transition outline-none"
                            />
                        </div>
                    </div>

                    <!-- Password field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="admin-password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                Password
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input
                                v-model="form.password"
                                type="password"
                                id="admin-password"
                                required
                                placeholder="••••••••"
                                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-white placeholder-slate-500 text-sm transition outline-none"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ isLoading ? 'Authenticating Admin...' : 'Sign In to Admin SPA' }}</span>
                        <svg v-if="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- 1-Click Fill Demo Credentials -->
                <div class="mt-6 pt-5 border-t border-slate-700/60">
                    <div class="rounded-xl bg-slate-900/80 p-3.5 border border-slate-700/50">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-blue-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Default Admin Credentials
                            </span>
                            <button
                                @click="fillAdminCredentials"
                                type="button"
                                class="text-[11px] text-blue-400 hover:text-blue-300 font-semibold underline underline-offset-2 cursor-pointer"
                            >
                                Auto-Fill
                            </button>
                        </div>
                        <div class="text-xs text-slate-300 font-mono space-y-0.5">
                            <div>Email: <span class="text-white font-semibold">admin@realhem.com</span></div>
                            <div>Password: <span class="text-white font-semibold">password</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link back to Client SPA -->
            <div class="text-center mt-6">
                <a href="/" class="text-xs text-slate-400 hover:text-slate-200 transition inline-flex items-center gap-1.5">
                    &larr; Switch to Client App (Buyer, Broker, Builder Portal)
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
    email: 'admin@realhem.com',
    password: 'password',
});

const errorMessage = ref('');
const isLoading = ref(false);

const fillAdminCredentials = () => {
    form.value.email = 'admin@realhem.com';
    form.value.password = 'password';
};

const handleLogin = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/api/admin/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.message || 'Login failed. Please check your credentials.';
            return;
        }

        // Save token and user in localStorage
        localStorage.setItem('realhem_admin_token', data.token);
        localStorage.setItem('realhem_admin_user', JSON.stringify(data.user));

        // Navigate to dashboard SPA
        router.push({ name: 'admin.dashboard' });
    } catch (err) {
        console.error(err);
        errorMessage.value = 'Network error. Please try again.';
    } finally {
        isLoading.value = false;
    }
};
</script>
