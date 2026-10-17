<template>
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between shadow-2xs">
        <!-- Left: Mobile Hamburger + Breadcrumb -->
        <div class="flex items-center gap-3">
            <!-- Mobile Hamburger Toggle -->
            <button
                @click="$emit('toggle-mobile-sidebar')"
                class="lg:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                title="Toggle menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Breadcrumb Navigation -->
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span class="hidden sm:inline text-slate-400">Admin</span>
                <span class="hidden sm:inline text-slate-300">/</span>
                <span class="text-slate-900 font-bold capitalize flex items-center gap-1.5">
                    <span>{{ currentSection.icon }}</span>
                    <span>{{ currentSection.title }}</span>
                </span>
            </div>
        </div>

        <!-- Right: Actions & Profile -->
        <div class="flex items-center gap-3">
            <!-- Live Client Portal Link -->
            <a
                href="/"
                target="_blank"
                class="hidden sm:inline-flex items-center gap-1.5 text-xs text-slate-700 hover:text-blue-600 bg-slate-100 hover:bg-slate-200/80 px-3 py-1.5 rounded-xl border border-slate-200 font-bold transition cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Live Client SPA &rarr;</span>
            </a>

            <!-- Mode Badge -->
            <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200/60 uppercase tracking-wider">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                <span>Vue 3 SPA</span>
            </span>

            <!-- Admin Profile & Quick Logout -->
            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    {{ initials }}
                </div>
                <div class="hidden sm:flex flex-col text-left leading-tight">
                    <span class="text-xs font-bold text-slate-800">{{ user?.name || 'Super Admin' }}</span>
                    <span class="text-[10px] text-slate-500 capitalize">{{ user?.role || 'Administrator' }}</span>
                </div>

                <!-- Quick Logout Button -->
                <button
                    @click="handleLogout"
                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer ml-1"
                    title="Logout from Admin SPA"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

defineEmits(['toggle-mobile-sidebar']);

const route = useRoute();
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

const currentSection = computed(() => {
    const path = route.path;
    if (path.startsWith('/properties')) {
        return { title: 'Properties & Inventory', icon: '🏢' };
    }
    if (path.startsWith('/users')) {
        return { title: 'Users & Agents CRM', icon: '👥' };
    }
    if (path.startsWith('/settings')) {
        return { title: 'Branding & Themes', icon: '🎨' };
    }
    if (path.startsWith('/legal')) {
        return { title: 'Legal & Compliance', icon: '⚖️' };
    }
    return { title: 'Dashboard & Analytics', icon: '📊' };
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

