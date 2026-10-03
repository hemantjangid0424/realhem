<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <AdminNavbar />

        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Users, Brokers &amp; Builders</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage all portal accounts with granular role permissions</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        v-for="role in ['all', 'admin', 'agent', 'owner', 'user']"
                        :key="role"
                        @click="currentRole = role; fetchUsers()"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold capitalize transition cursor-pointer"
                        :class="currentRole === role ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        {{ role === 'agent' ? 'Brokers' : (role === 'all' ? 'All Roles' : role + 's') }}
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-5">ID</th>
                                <th class="py-3.5 px-5">Name</th>
                                <th class="py-3.5 px-5">Email</th>
                                <th class="py-3.5 px-5">Role</th>
                                <th class="py-3.5 px-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-5 text-xs text-slate-400 font-mono">#{{ user.id }}</td>
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
                                    Verified
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                    {{ isLoading ? 'Loading...' : 'No users found for this filter.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminNavbar from '../components/AdminNavbar.vue';

const users = ref([]);
const currentRole = ref('all');
const isLoading = ref(false);

const fetchUsers = async () => {
    isLoading.value = true;
    const token = localStorage.getItem('realhem_admin_token');
    const url = currentRole.value === 'all'
        ? '/api/admin/users'
        : `/api/admin/users?role=${currentRole.value}`;

    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (response.ok) {
            const data = await response.json();
            users.value = data.data || [];
        }
    } catch (err) {
        console.error('Error fetching users:', err);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchUsers();
});
</script>
