<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 pb-20">
        <!-- Auth Modal for quick login / sync -->
        <AuthModal
            :is-open="isAuthModalOpen"
            @close="isAuthModalOpen = false"
            @authenticated="handleUserAuthenticated"
        />

        <!-- Top Header Hero -->
        <section
            class="text-white py-10 px-4 sm:px-6 lg:px-8 shadow-sm transition-all duration-300 relative overflow-hidden"
            :style="{ background: heroGradient }"
        >
            <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs font-semibold backdrop-blur-md">
                        <span>👁️ Browsing History</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight flex items-center gap-3">
                        <span>Recently Viewed Properties</span>
                        <span
                            v-if="activities.length > 0"
                            class="text-xs sm:text-sm font-black px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 shadow-xs"
                        >
                            {{ activities.length }}
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 max-w-xl">
                        Track and revisit the apartments, houses, plots, and commercial listings you previously explored across {{ companyName }}.
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    <button
                        v-if="activities.length > 0"
                        type="button"
                        @click="handleClearHistory"
                        class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs transition cursor-pointer flex items-center gap-1.5 backdrop-blur-sm"
                        title="Clear all viewed properties"
                    >
                        <span>🗑️</span>
                        <span>Clear History</span>
                    </button>

                    <router-link
                        to="/listings"
                        class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-extrabold text-xs shadow-md hover:bg-slate-100 transition flex items-center gap-1.5"
                    >
                        <span>Browse More</span>
                        <span>&rarr;</span>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Main Body -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-6">
            <!-- Guest Mode Notice Banner -->
            <div
                v-if="!isLoggedIn"
                class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50/60 to-blue-50 border border-blue-200/90 shadow-2xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4"
            >
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-xs">
                        📱
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-slate-900">Saved in your browser (Guest Mode)</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 px-2 py-0.5 rounded-md">
                                Local Only
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            These listings are stored safely in this browser. Log in or create an account to sync your history across mobile and desktop.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 w-full md:w-auto">
                    <button
                        type="button"
                        @click="isAuthModalOpen = true"
                        class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl text-white font-bold text-xs shadow-xs transition cursor-pointer hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Login to Sync History &rarr;
                    </button>
                </div>
            </div>

            <!-- Filter & Search Bar within History (If >= 2 items) -->
            <div
                v-if="activities.length > 2"
                class="bg-white rounded-2xl border border-slate-200/90 p-3 sm:p-4 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3"
            >
                <div class="relative flex-1 max-w-md">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search your viewed properties by title, locality or city..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-9 pr-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Type Filters -->
                <div class="flex items-center gap-1.5 overflow-x-auto">
                    <button
                        v-for="f in [
                            { id: 'all', label: 'All' },
                            { id: 'buy', label: 'Buy' },
                            { id: 'rent', label: 'Rent' },
                            { id: 'commercial', label: 'Commercial' },
                            { id: 'plots', label: 'Plots' }
                        ]"
                        :key="f.id"
                        type="button"
                        @click="typeFilter = f.id"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer whitespace-nowrap"
                        :class="typeFilter === f.id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        {{ f.label }}
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="py-20 text-center space-y-3 bg-white rounded-3xl border border-slate-200">
                <div class="w-8 h-8 border-3 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-slate-500 font-semibold">Loading your viewed properties...</p>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="filteredActivities.length === 0"
                class="py-20 px-4 text-center bg-white rounded-3xl border border-slate-200 shadow-sm space-y-5"
            >
                <div class="w-20 h-20 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-3xl shadow-inner">
                    👁️
                </div>
                <div class="space-y-1 max-w-md mx-auto">
                    <h3 class="text-lg font-black text-slate-900">
                        {{ activities.length === 0 ? "You haven't viewed any properties yet" : "No matches found in your history" }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ activities.length === 0
                            ? "Explore homes, apartments, villas, and commercial real estate. Every property you open will be remembered here so you can easily compare and revisit."
                            : "Try clearing your search query or switching filters to see other properties."
                        }}
                    </p>
                </div>
                <div>
                    <router-link
                        to="/listings"
                        class="inline-flex items-center gap-2 py-3 px-6 rounded-xl text-white font-extrabold text-xs shadow-md transition cursor-pointer hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        <span>Explore Properties Now</span>
                        <span>&rarr;</span>
                    </router-link>
                </div>
            </div>

            <!-- Grid of Viewed Properties -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <div
                    v-for="act in filteredActivities"
                    :key="act.id || act.property_id"
                    class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-xl transition-all duration-200 flex flex-col justify-between overflow-hidden group relative"
                >
                    <!-- Clickable card top -->
                    <router-link
                        :to="'/property/' + (act.property?.slug || act.metadata?.slug || act.property_id)"
                        class="block flex-1 flex flex-col justify-between"
                    >
                        <!-- Cover Image -->
                        <div class="h-48 bg-slate-900 relative overflow-hidden">
                            <img
                                v-if="act.property?.photos?.[0] || act.metadata?.photo"
                                :src="act.property?.photos?.[0] || act.metadata?.photo"
                                :alt="act.property?.title || act.metadata?.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-3xl text-slate-500">
                                🏠
                            </div>

                            <!-- Badges on Photo -->
                            <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 flex-wrap z-10">
                                <span
                                    v-if="act.property?.property_for || act.metadata?.property_for"
                                    class="px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-xs text-white text-[10px] font-black uppercase tracking-wider"
                                >
                                    For {{ act.property?.property_for || act.metadata?.property_for }}
                                </span>
                                <span
                                    class="px-2 py-0.5 rounded-md bg-blue-600/90 backdrop-blur-xs text-white text-[10px] font-bold"
                                >
                                    {{ act.property?.property_type || act.metadata?.property_type || 'Property' }}
                                </span>
                            </div>

                            <!-- Remove item button -->
                            <button
                                type="button"
                                @click.prevent.stop="removeItem(act)"
                                class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-slate-900/80 hover:bg-rose-600 text-white text-xs flex items-center justify-center opacity-80 hover:opacity-100 transition z-20 cursor-pointer shadow-xs"
                                title="Remove from recent history"
                            >
                                ✕
                            </button>

                            <!-- Gradient bar on bottom of photo -->
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent p-3 pt-6 flex items-end justify-between z-10">
                                <span class="text-white font-black text-sm">
                                    {{ formatPrice(act.property?.expected_price || act.metadata?.expected_price) }}
                                </span>
                                <span v-if="act.property?.carpet_area || act.metadata?.carpet_area" class="text-slate-300 text-[11px] font-semibold">
                                    {{ act.property?.carpet_area || act.metadata?.carpet_area }} sq.ft
                                </span>
                            </div>
                        </div>

                        <!-- Card Body Details -->
                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div class="space-y-1">
                                <h3 class="text-sm font-black text-slate-900 line-clamp-1 group-hover:text-blue-600 transition">
                                    {{ act.property?.title || act.metadata?.title || 'Property Listing' }}
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-1 flex items-center gap-1">
                                    <span>📍</span>
                                    <span>
                                        {{ act.property?.locality || act.metadata?.locality || '' }}{{ (act.property?.city || act.metadata?.city) ? ', ' + (act.property?.city || act.metadata?.city) : '' }}
                                    </span>
                                </p>
                            </div>

                            <!-- Sub meta info -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400 font-medium">
                                    {{ formatViewedTime(act.created_at) }}
                                </span>
                                <span
                                    class="font-bold text-xs flex items-center gap-1"
                                    :style="{ color: brandPrimaryColor }"
                                >
                                    <span>Revisit</span>
                                    <span>&rarr;</span>
                                </span>
                            </div>
                        </div>
                    </router-link>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AuthModal from '~/components/AuthModal.vue';

const { companyName, brandPrimaryColor, heroGradient } = useCompanyBranding();
const {
    recentActivities,
    loadRecentActivities,
    getGuestActivities,
    clearGuestActivities,
    syncGuestActivitiesToServer,
} = useUserActivity();

useSeoMeta({
    title: () => `Recently Viewed Properties • ${companyName.value}`,
    description: 'Explore the properties, apartments, and houses you recently viewed on RealHem.',
    robots: 'noindex, follow',
});

const isAuthModalOpen = ref(false);
const isLoading = ref(false);
const searchQuery = ref('');
const typeFilter = ref('all');
const isLoggedIn = ref(false);

const activities = computed(() => recentActivities.value || []);

const checkUserAuth = () => {
    if (!import.meta.client) return;
    isLoggedIn.value = !!localStorage.getItem('realhem_user_token');
};

const handleUserAuthenticated = async () => {
    isAuthModalOpen.value = false;
    checkUserAuth();
    await syncGuestActivitiesToServer();
    await loadRecentActivities();
};

const formatPrice = (val: any) => {
    if (!val || Number(val) <= 0) return 'Price on Request';
    const num = Number(val);
    if (num >= 10000000) {
        const cr = (num / 10000000).toFixed(2);
        return `₹ ${cr.replace(/\.00$/, '')} Cr`;
    }
    if (num >= 100000) {
        const lk = (num / 100000).toFixed(2);
        return `₹ ${lk.replace(/\.00$/, '')} Lakh`;
    }
    return `₹ ${num.toLocaleString('en-IN')}`;
};

const formatViewedTime = (timestamp?: string) => {
    if (!timestamp) return 'Recently';
    try {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min ago`;
        if (diffHours < 24) return `${diffHours} hr ago`;
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;
        return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
    } catch {
        return 'Recently';
    }
};

const filteredActivities = computed(() => {
    let list = [...activities.value];

    if (typeFilter.value !== 'all') {
        list = list.filter((act) => {
            const pFor = (act.property?.property_for || act.metadata?.property_for || '').toLowerCase();
            const pType = (act.property?.property_type || act.metadata?.property_type || '').toLowerCase();
            if (typeFilter.value === 'buy') return pFor === 'buy' || pFor === 'sale';
            if (typeFilter.value === 'rent') return pFor === 'rent';
            if (typeFilter.value === 'commercial') return pType.includes('commercial') || pType.includes('office') || pType.includes('shop');
            if (typeFilter.value === 'plots') return pType.includes('plot') || pType.includes('land');
            return true;
        });
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.trim().toLowerCase();
        list = list.filter((act) => {
            const title = (act.property?.title || act.metadata?.title || '').toLowerCase();
            const loc = (act.property?.locality || act.metadata?.locality || '').toLowerCase();
            const city = (act.property?.city || act.metadata?.city || '').toLowerCase();
            return title.includes(q) || loc.includes(q) || city.includes(q);
        });
    }

    return list;
});

const removeItem = (act: any) => {
    const id = act.property_id || act.property?.id;
    if (!id) return;
    if (!import.meta.client) return;

    // Filter from state
    recentActivities.value = recentActivities.value.filter(
        (item) => item.property_id !== id && item.property?.id !== id
    );

    // If guest, update localStorage
    try {
        const guestActs = getGuestActivities().filter(
            (item) => item.property_id !== id && item.property?.id !== id
        );
        localStorage.setItem('realhem_guest_activity', JSON.stringify(guestActs));
    } catch (e) {
        console.warn('Could not remove item from guest activities:', e);
    }
};

const handleClearHistory = () => {
    if (!confirm('Are you sure you want to clear your recently viewed properties history?')) {
        return;
    }
    clearGuestActivities();
    recentActivities.value = [];
};

const handleActivityUpdated = () => {
    loadRecentActivities();
};

onMounted(async () => {
    checkUserAuth();
    isLoading.value = true;
    try {
        await loadRecentActivities();
    } finally {
        isLoading.value = false;
    }

    if (import.meta.client) {
        window.addEventListener('realhem-activity-logged', handleActivityUpdated);
    }
});

onUnmounted(() => {
    if (import.meta.client) {
        window.removeEventListener('realhem-activity-logged', handleActivityUpdated);
    }
});
</script>

