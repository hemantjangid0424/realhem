<template>
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center gap-4">
                    <router-link to="/dashboard" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform">
                            RH
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-white text-lg tracking-tight leading-none flex items-center gap-2">
                                RealHem <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 uppercase tracking-wider">Vue 3 Admin SPA</span>
                            </span>
                            <span class="text-xs text-slate-400">99acres Real Estate Platform</span>
                        </div>
                    </router-link>

                    <div class="hidden md:flex items-center gap-1 ml-8">
                        <router-link
                            to="/dashboard"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                            :class="$route.path === '/dashboard' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
                        >
                            Dashboard
                        </router-link>
                        <router-link
                            to="/properties"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                            :class="$route.path.startsWith('/properties') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
                        >
                            Properties
                        </router-link>
                        <router-link
                            to="/users"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                            :class="$route.path.startsWith('/users') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
                        >
                            Users &amp; Agents
                        </router-link>
                        <router-link
                            to="/settings"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"
                            :class="$route.path.startsWith('/settings') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
                        >
                            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Branding &amp; Settings</span>
                        </router-link>
                    </div>
                </div>

                <!-- Right Tools -->
                <div class="flex items-center gap-3">
                    <a
                        href="/"
                        target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 text-xs text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-lg border border-slate-700 transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Open Client SPA &rarr;
                    </a>

                    <!-- Admin Info -->
                    <div class="flex items-center gap-2.5 pl-3 border-l border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-blue-600/30 border border-blue-500/40 text-blue-300 flex items-center justify-center font-bold text-xs">
                            {{ initials }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-semibold text-white leading-tight">{{ user?.name || 'Admin' }}</span>
                            <span class="text-[10px] text-slate-400 capitalize">{{ user?.role || 'Administrator' }}</span>
                        </div>

                        <!-- Logout -->
                        <button
                            @click="handleLogout"
                            class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition cursor-pointer ml-1"
                            title="Logout from Admin SPA"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const user = computed(() => {
    try {
        return JSON.parse(localStorage.getItem('realhem_admin_user') || '{}');
    } catch {
        return {};
    }
});

const initials = computed(() => {
    const name = user.value.name || 'AD';
    return name.slice(0, 2).toUpperCase();
});

const handleLogout = async () => {
    const token = localStorage.getItem('realhem_admin_token');
    if (token) {
        try {
            await fetch('/api/admin/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
            });
        } catch (err) {
            console.error('Logout error:', err);
        }
    }
    localStorage.removeItem('realhem_admin_token');
    localStorage.removeItem('realhem_admin_user');
    router.push({ name: 'admin.login' });
};
</script>
