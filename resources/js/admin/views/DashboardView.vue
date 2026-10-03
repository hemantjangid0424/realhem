<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <AdminNavbar />

        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-8">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                            Welcome, {{ adminUser?.name || 'Admin' }}! 👋
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Vue 3 SPA Mode
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">
                        RealHem 99acres Real Estate Portal &bull; Central Administration Engine
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="fetchDashboardData"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh Data
                    </button>
                    <a
                        href="/"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition"
                    >
                        <span>Open Client Portal</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card 1: Total Users -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Users</span>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ stats.total_users ?? '...' }}</span>
                        <span class="text-xs text-slate-500">accounts</span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 pt-3 border-t border-slate-100">
                        <span><strong class="text-slate-700">{{ stats.total_agents ?? 0 }}</strong> Brokers</span>
                        <span>&bull;</span>
                        <span><strong class="text-slate-700">{{ stats.total_owners ?? 0 }}</strong> Owners</span>
                        <span>&bull;</span>
                        <span><strong class="text-slate-700">{{ stats.total_buyers ?? 0 }}</strong> Buyers</span>
                    </div>
                </div>

                <!-- Card 2: Properties -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Properties</span>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ stats.total_properties ?? 0 }}</span>
                        <span class="text-xs text-slate-500">listings</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>Buy, Rent &amp; Commercial</span>
                    </div>
                </div>

                <!-- Card 3: Inquiries -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Leads &amp; Inquiries</span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ stats.total_inquiries ?? 0 }}</span>
                        <span class="text-xs text-slate-500">buyer leads</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Direct WhatsApp &amp; Call</span>
                    </div>
                </div>

                <!-- Card 4: Verification Queue -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Approvals</span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ stats.pending_verifications ?? 0 }}</span>
                        <span class="text-xs text-slate-500">pending</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Verified 99acres Badge</span>
                    </div>
                </div>
            </div>

            <!-- Two Columns Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left 2 Cols: Registered Users / Accounts -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Recent Users &amp; Profiles</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Brokers, property owners, and buyers</p>
                            </div>
                            <router-link
                                to="/users"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg transition"
                            >
                                View All Users &rarr;
                            </router-link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="py-3 px-5">User</th>
                                        <th class="py-3 px-5">Email</th>
                                        <th class="py-3 px-5">Portal Role</th>
                                        <th class="py-3 px-5">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    <tr v-for="user in recentUsers" :key="user.id" class="hover:bg-slate-50/50 transition">
                                        <td class="py-3.5 px-5 font-semibold text-slate-900 flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                {{ (user.name || 'U').charAt(0).toUpperCase() }}
                                            </div>
                                            <span>{{ user.name }}</span>
                                        </td>
                                        <td class="py-3.5 px-5 text-slate-600 text-xs font-mono">{{ user.email }}</td>
                                        <td class="py-3.5 px-5">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider"
                                                :class="{
                                                    'bg-purple-100 text-purple-800': user.role === 'admin',
                                                    'bg-blue-100 text-blue-800': user.role === 'agent',
                                                    'bg-emerald-100 text-emerald-800': user.role === 'owner',
                                                    'bg-slate-100 text-slate-700': user.role === 'user',
                                                }"
                                            >
                                                {{ user.role === 'agent' ? 'Broker / Agent' : user.role }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-emerald-600 font-medium flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </td>
                                    </tr>
                                    <tr v-if="recentUsers.length === 0">
                                        <td colspan="4" class="py-8 text-center text-slate-400 text-sm">
                                            {{ isLoading ? 'Loading data from API...' : 'No users found.' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right 1 Col: Quick Actions & Architecture -->
                <div class="space-y-6">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-900 mb-3">Admin Actions</h3>
                        <div class="space-y-2">
                            <router-link
                                to="/properties"
                                class="w-full text-left p-3 rounded-xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/30 transition flex items-center justify-between group cursor-pointer block"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                        +
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block">Manage Property Listings</span>
                                        <span class="text-[11px] text-slate-500">Approve, reject, or feature</span>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </router-link>

                            <router-link
                                to="/users"
                                class="w-full text-left p-3 rounded-xl border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/30 transition flex items-center justify-between group cursor-pointer block"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block">Brokers &amp; Builders</span>
                                        <span class="text-[11px] text-slate-500">Manage verified agent profiles</span>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </router-link>
                        </div>
                    </div>

                    <!-- Dual SPA Architecture status -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-md border border-slate-800">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Dual SPA Architecture</span>
                            <span class="text-[10px] bg-blue-500/20 text-blue-300 border border-blue-500/30 px-2 py-0.5 rounded-full font-semibold">Active</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed mb-3">
                            Running two isolated Vue 3 SPAs:
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-800/80 border border-slate-700">
                                <span class="font-bold text-white block">1. Admin SPA (/admin/*)</span>
                                <span class="text-slate-400 text-[11px]">Moderation, KYC, Property approvals, Users</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-800/80 border border-slate-700">
                                <span class="font-bold text-white block">2. Client SPA (/*)</span>
                                <span class="text-slate-400 text-[11px]">Buyers, Tenants, Brokers, Builders, Post Property</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminNavbar from '../components/AdminNavbar.vue';

const adminUser = ref(null);
const stats = ref({});
const recentUsers = ref([]);
const isLoading = ref(false);

const fetchDashboardData = async () => {
    isLoading.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    try {
        const response = await fetch('/api/admin/dashboard/stats', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (response.ok) {
            const data = await response.json();
            stats.value = data.stats;
            recentUsers.value = data.recent_users;
        }
    } catch (err) {
        console.error('Error fetching dashboard stats:', err);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    try {
        adminUser.value = JSON.parse(localStorage.getItem('realhem_admin_user') || '{}');
    } catch {
        adminUser.value = {};
    }
    fetchDashboardData();
});
</script>
