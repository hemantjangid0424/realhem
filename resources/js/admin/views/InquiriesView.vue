<template>
    <AdminLayout>
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                            <span>📬</span>
                            <span>Inquiries, Feedback &amp; Reports</span>
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                            CRM &amp; Moderation
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Review consumer information requests, user feedback, safety problem reports, and direct contact messages.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="fetchInquiries"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            <!-- Stats Ribbon -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs text-center space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Total</span>
                    <p class="text-xl font-black text-slate-900">{{ counts.total || 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-xs text-center space-y-1 bg-amber-50/30">
                    <span class="text-[10px] font-black uppercase text-amber-700 block tracking-wider">Pending</span>
                    <p class="text-xl font-black text-amber-600">{{ counts.pending || 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-xs text-center space-y-1 bg-emerald-50/30">
                    <span class="text-[10px] font-black uppercase text-emerald-700 block tracking-wider">Resolved</span>
                    <p class="text-xl font-black text-emerald-600">{{ counts.resolved || 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-blue-200 shadow-xs text-center space-y-1 bg-blue-50/30">
                    <span class="text-[10px] font-black uppercase text-blue-700 block tracking-wider">Info Requests</span>
                    <p class="text-xl font-black text-blue-600">{{ counts.request_info || 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-xs text-center space-y-1 bg-rose-50/30">
                    <span class="text-[10px] font-black uppercase text-rose-700 block tracking-wider">Problem Reports</span>
                    <p class="text-xl font-black text-rose-600">{{ counts.report_problem || 0 }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-purple-200 shadow-xs text-center space-y-1 bg-purple-50/30">
                    <span class="text-[10px] font-black uppercase text-purple-700 block tracking-wider">Feedback &amp; Reviews</span>
                    <p class="text-xl font-black text-purple-600">{{ counts.feedback || 0 }}</p>
                </div>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Type Filter Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1 md:pb-0">
                    <button
                        v-for="t in typeTabs"
                        :key="t.id"
                        @click="changeType(t.id)"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap"
                        :class="selectedType === t.id
                            ? 'bg-slate-900 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        <span>{{ t.label }}</span>
                    </button>
                </div>

                <!-- Status Filter & Search Input -->
                <div class="flex items-center gap-3 flex-wrap">
                    <select
                        v-model="selectedStatus"
                        @change="fetchInquiries"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                    >
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>

                    <div class="relative w-48 sm:w-64">
                        <input
                            v-model="searchQuery"
                            @keyup.enter="fetchInquiries"
                            type="text"
                            placeholder="Search name, phone, text..."
                            class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        />
                        <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Inquiries List Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div v-if="isLoading" class="p-12 text-center text-slate-500 space-y-2">
                    <svg class="w-6 h-6 animate-spin mx-auto text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <p class="text-xs font-bold">Loading submissions...</p>
                </div>

                <div v-else-if="inquiries.length === 0" class="p-12 text-center text-slate-500 space-y-3">
                    <span class="text-3xl block">📭</span>
                    <h3 class="text-base font-bold text-slate-800">No submissions found</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">There are currently no inquiries or feedback matching the selected filter criteria.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 text-slate-800 font-black uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Date &amp; Type</th>
                                <th class="py-3.5 px-4">Sender Details</th>
                                <th class="py-3.5 px-4">Subject &amp; Message Preview</th>
                                <th class="py-3.5 px-4">Reference</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr v-for="item in inquiries" :key="item.id" class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider block w-fit" :class="getTypeBadge(item.type)">
                                            {{ formatType(item.type) }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 block font-normal">{{ formatDate(item.created_at) }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <div class="space-y-0.5">
                                        <p class="font-extrabold text-slate-900">{{ item.name }}</p>
                                        <a :href="`mailto:${item.email}`" class="text-blue-600 hover:underline block truncate max-w-[180px]">{{ item.email }}</a>
                                        <p v-if="item.phone" class="text-slate-500 font-semibold">{{ item.phone }}</p>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <div class="space-y-1">
                                        <p v-if="item.subject" class="font-bold text-slate-900 truncate">{{ item.subject }}</p>
                                        <p class="text-slate-600 line-clamp-2 leading-relaxed text-[11px]">{{ item.message }}</p>
                                        <div v-if="item.rating" class="flex items-center gap-1 text-amber-500 text-xs">
                                            <span v-for="s in item.rating" :key="s">★</span>
                                            <span class="text-slate-400 text-[10px] ml-1">({{ item.rating }}/5)</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div v-if="item.property" class="space-y-0.5">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Property #{{ item.property.id }}</span>
                                        <a :href="`/property/${item.property.id}`" target="_blank" class="text-blue-600 font-bold hover:underline block truncate max-w-[140px]">
                                            {{ item.property.title }}
                                        </a>
                                        <span class="text-[10px] text-slate-500">{{ item.property.city }}</span>
                                    </div>
                                    <span v-else-if="item.property_id" class="text-slate-400 text-[11px]">ID #{{ item.property_id }}</span>
                                    <span v-else class="text-slate-300 text-xs">—</span>
                                </td>

                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide inline-block" :class="getStatusBadge(item.status)">
                                        {{ item.status }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            @click="viewDetails(item)"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                                            title="View full submission & details"
                                        >
                                            View
                                        </button>

                                        <button
                                            v-if="item.status === 'pending'"
                                            @click="updateStatus(item.id, 'resolved')"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition cursor-pointer"
                                            title="Mark as Resolved"
                                        >
                                            Resolve
                                        </button>

                                        <button
                                            v-if="item.status === 'pending'"
                                            @click="updateStatus(item.id, 'dismissed')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition cursor-pointer"
                                            title="Dismiss"
                                        >
                                            Dismiss
                                        </button>

                                        <button
                                            @click="deleteInquiry(item.id)"
                                            class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition cursor-pointer"
                                            title="Delete record"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="pagination.total > pagination.per_page" class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
                    <span>Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }}</span>

                    <div class="flex items-center gap-1.5">
                        <button
                            :disabled="pagination.current_page <= 1"
                            @click="changePage(pagination.current_page - 1)"
                            class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 font-semibold disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed"
                        >
                            &larr; Prev
                        </button>
                        <button
                            :disabled="pagination.current_page >= pagination.last_page"
                            @click="changePage(pagination.current_page + 1)"
                            class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 font-semibold disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed"
                        >
                            Next &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="activeModalItem" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 relative max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider block w-fit mb-1.5" :class="getTypeBadge(activeModalItem.type)">
                            {{ formatType(activeModalItem.type) }}
                        </span>
                        <h3 class="text-lg font-black text-slate-900">{{ activeModalItem.subject || 'Submission Details' }}</h3>
                        <p class="text-xs text-slate-400">Received {{ formatDate(activeModalItem.created_at) }}</p>
                    </div>
                    <button @click="activeModalItem = null" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer p-1">✕</button>
                </div>

                <!-- Submitter Contact Information -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Name</span>
                        <span class="font-extrabold text-slate-900">{{ activeModalItem.name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Email</span>
                        <a :href="`mailto:${activeModalItem.email}`" class="font-bold text-blue-600 hover:underline">{{ activeModalItem.email }}</a>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Phone</span>
                        <span class="font-bold text-slate-900">{{ activeModalItem.phone || 'Not provided' }}</span>
                    </div>
                </div>

                <!-- Full Message Content -->
                <div class="space-y-1.5">
                    <span class="text-xs font-bold text-slate-700 block">Message Content:</span>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                        {{ activeModalItem.message }}
                    </div>
                </div>

                <!-- Special Metadata (e.g. portfolio URL, job title) -->
                <div v-if="activeModalItem.metadata" class="space-y-2 p-4 rounded-2xl bg-blue-50/60 border border-blue-200/60 text-xs">
                    <span class="font-bold text-blue-900 block">Additional Metadata:</span>
                    <div v-for="(val, k) in activeModalItem.metadata" :key="k" class="flex items-center gap-2">
                        <span class="text-slate-500 font-semibold capitalize">{{ k.replace('_', ' ') }}:</span>
                        <a v-if="typeof val === 'string' && val.startsWith('http')" :href="val" target="_blank" class="text-blue-600 font-bold hover:underline">{{ val }}</a>
                        <span v-else class="font-bold text-slate-800">{{ val }}</span>
                    </div>
                </div>

                <!-- Status Action Bar -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-600">Current Status:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase" :class="getStatusBadge(activeModalItem.status)">
                            {{ activeModalItem.status }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            v-if="activeModalItem.status !== 'resolved'"
                            @click="updateStatus(activeModalItem.id, 'resolved')"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition cursor-pointer"
                        >
                            Mark Resolved
                        </button>
                        <button
                            v-if="activeModalItem.status !== 'dismissed'"
                            @click="updateStatus(activeModalItem.id, 'dismissed')"
                            class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            Dismiss
                        </button>
                        <button
                            @click="activeModalItem = null"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition cursor-pointer"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';

const inquiries = ref([]);
const counts = ref({});
const isLoading = ref(false);
const activeModalItem = ref(null);

const selectedType = ref('all');
const selectedStatus = ref('all');
const searchQuery = ref('');

const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
});

const typeTabs = [
    { id: 'all', label: 'All Types' },
    { id: 'request_info', label: 'Info Requests' },
    { id: 'feedback', label: 'Feedback & Reviews' },
    { id: 'report_problem', label: 'Problem Reports' },
    { id: 'contact', label: 'Contact Messages' },
];

const fetchInquiries = async (page = 1) => {
    isLoading.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    const params = new URLSearchParams({
        page: page,
        type: selectedType.value,
        status: selectedStatus.value,
        search: searchQuery.value,
    });

    try {
        const res = await fetch(`/api/admin/inquiries?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        const data = await res.json();
        if (data.success) {
            inquiries.value = data.data || [];
            counts.value = data.counts || {};
            pagination.value = data.pagination || pagination.value;
        }
    } catch (err) {
        console.error('Failed to load inquiries:', err);
    } finally {
        isLoading.value = false;
    }
};

const changeType = (typeId) => {
    selectedType.value = typeId;
    fetchInquiries(1);
};

const changePage = (page) => {
    fetchInquiries(page);
};

const viewDetails = (item) => {
    activeModalItem.value = item;
};

const updateStatus = async (id, newStatus) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/inquiries/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ status: newStatus }),
        });
        const data = await res.json();
        if (res.ok) {
            if (activeModalItem.value && activeModalItem.value.id === id) {
                activeModalItem.value.status = newStatus;
            }
            fetchInquiries(pagination.value.current_page);
        } else {
            alert(data.message || 'Failed to update status.');
        }
    } catch (err) {
        console.error('Failed to update inquiry status:', err);
    }
};

const deleteInquiry = async (id) => {
    if (!confirm('Are you sure you want to delete this submission record?')) return;

    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/inquiries/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (res.ok) {
            if (activeModalItem.value && activeModalItem.value.id === id) {
                activeModalItem.value = null;
            }
            fetchInquiries(pagination.value.current_page);
        }
    } catch (err) {
        console.error('Failed to delete inquiry:', err);
    }
};

const formatType = (type) => {
    return type ? type.replace('_', ' ') : '';
};

const formatDate = (isoStr) => {
    if (!isoStr) return '';
    const d = new Date(isoStr);
    return d.toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getTypeBadge = (type) => {
    switch (type) {
        case 'report_problem':
            return 'bg-rose-100 text-rose-800 border border-rose-200';
        case 'feedback':
            return 'bg-purple-100 text-purple-800 border border-purple-200';
        case 'request_info':
            return 'bg-blue-100 text-blue-800 border border-blue-200';
        case 'career':
            return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
        default:
            return 'bg-slate-100 text-slate-800 border border-slate-200';
    }
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'resolved':
            return 'bg-emerald-100 text-emerald-800';
        case 'dismissed':
            return 'bg-slate-200 text-slate-700';
        default:
            return 'bg-amber-100 text-amber-800';
    }
};

onMounted(() => {
    fetchInquiries(1);
});
</script>

