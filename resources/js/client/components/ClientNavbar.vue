<template>
    <header
        class="sticky top-0 z-40 transition-all duration-200 shadow-sm"
        :class="isScrolled ? 'text-white shadow-md' : 'bg-white text-slate-800 border-b border-slate-200'"
        :style="isScrolled ? { backgroundColor: brandPrimaryColor } : {}"
    >
        <!-- Top Micro-bar (Hidden when scrolled down for compact sticky layout) -->
        <div
            v-if="!isScrolled"
            class="bg-slate-900 text-slate-300 text-[11px] py-1.5 px-4 sm:px-8 border-b border-slate-800 transition-all"
        >
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 text-blue-400 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                        {{ companyTagline }}
                    </span>
                    <span class="hidden sm:inline text-slate-500">|</span>
                    <span class="hidden sm:inline text-slate-400">{{ companySubTagline }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <router-link to="/brokers" class="text-slate-300 hover:text-white transition flex items-center gap-1">
                        <span>For Brokers &amp; Builders</span>
                        <span class="text-[9px] bg-blue-600/60 text-blue-200 px-1.5 py-0.2 rounded">PRO</span>
                    </router-link>
                    <span class="text-slate-700">|</span>
                    <a href="/admin/login" class="text-amber-400 hover:text-amber-300 font-semibold transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Admin Portal &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Navigation Bar -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-18 transition-all">
                <!-- Left: Logo & City/Locality Selector -->
                <div class="flex items-center gap-3 sm:gap-5 flex-shrink-0">
                    <router-link to="/" class="flex items-center gap-2.5 group">
                        <div
                            v-if="companyLogoUrl"
                            class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center border border-slate-200 shadow-sm transition-transform group-hover:scale-105"
                        >
                            <img :src="companyLogoUrl" :alt="companyName" class="w-full h-full object-contain" />
                        </div>
                        <div
                            v-else
                            class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-lg shadow-sm transition-transform group-hover:scale-105"
                            :class="isScrolled ? 'bg-white' : 'text-white'"
                            :style="isScrolled ? { color: brandPrimaryColor } : { backgroundColor: brandPrimaryColor }"
                        >
                            {{ companyShortName }}
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="font-black text-xl tracking-tight leading-none"
                                :class="isScrolled ? 'text-white' : 'text-slate-900'"
                            >
                                {{ companyName }}<span :class="isScrolled ? 'text-amber-400' : 'text-blue-600'">.</span>
                            </span>
                            <span
                                v-if="!isScrolled"
                                class="text-[9px] font-bold tracking-wide uppercase text-slate-400"
                            >
                                {{ companyTagline }}
                            </span>
                        </div>
                    </router-link>

                    <!-- City / Area Selector Dropdown -->
                    <div class="relative hidden sm:block">
                        <div
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                            :class="isScrolled ? 'text-white/95 hover:bg-white/10' : 'bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-200'"
                        >
                            <span v-if="isScrolled" class="capitalize font-normal text-white/80">{{ stickySearchType }} in</span>
                            <select
                                :value="selectedCity"
                                @change="handleCitySelectChange"
                                class="bg-transparent border-none text-xs font-bold outline-none cursor-pointer pr-4 appearance-none"
                                :class="isScrolled ? 'text-white' : 'text-slate-800'"
                            >
                                <option class="text-blue-600 font-bold bg-blue-50" value="__DETECT__">📍 Detect My Location</option>
                                <option class="text-slate-900 bg-white" value="Delhi NCR">Delhi NCR</option>
                                <option class="text-slate-900 bg-white" value="Ahmedabad">Ahmedabad</option>
                                <option class="text-slate-900 bg-white" value="Mumbai">Mumbai</option>
                                <option class="text-slate-900 bg-white" value="Bangalore">Bangalore</option>
                                <option class="text-slate-900 bg-white" value="Pune">Pune</option>
                                <option class="text-slate-900 bg-white" value="Hyderabad">Hyderabad</option>
                                <option class="text-slate-900 bg-white" value="Chennai">Chennai</option>
                                <option class="text-slate-900 bg-white" value="Kolkata">Kolkata</option>
                            </select>
                            <svg class="w-3.5 h-3.5 -ml-3 pointer-events-none opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- CENTER: STICKY SEARCH BAR (Shown when scrolled down past hero) -->
                <div v-if="isScrolled" class="flex-1 max-w-xl mx-4 animate-fadeIn">
                    <form
                        @submit.prevent="handleStickySearch"
                        class="bg-white text-slate-800 rounded-full shadow-lg border border-slate-200/60 flex items-center px-3 py-1.5"
                    >
                        <!-- Type selector (Buy, Rent, Commercial, Plots) -->
                        <div class="relative flex-shrink-0">
                            <select
                                v-model="stickySearchType"
                                class="bg-transparent border-none text-xs font-bold text-slate-800 outline-none cursor-pointer pr-4 appearance-none capitalize pl-1"
                            >
                                <option value="buy">Buy</option>
                                <option value="rent">Rent</option>
                                <option value="commercial">Commercial</option>
                                <option value="plots">Plots</option>
                            </select>
                            <svg class="w-3 h-3 text-slate-500 absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <!-- Divider -->
                        <div class="h-4 w-px bg-slate-200 mx-2.5"></div>

                        <!-- Input -->
                        <input
                            v-model="stickyKeyword"
                            type="text"
                            placeholder="Enter Locality / Project / Landmark"
                            class="w-full text-xs font-medium text-slate-800 placeholder-slate-400 outline-none bg-transparent"
                        />

                        <!-- Actions Icons -->
                        <div class="flex items-center gap-2 pl-2">
                            <!-- GPS / Near Me Icon with Geolocation Detection -->
                            <button
                                type="button"
                                @click="handleDetectLocation"
                                :disabled="isDetectingLocation"
                                :title="isDetectingLocation ? 'Detecting location...' : 'Detect My Location'"
                                class="text-slate-400 hover:text-blue-600 p-1 cursor-pointer transition flex items-center justify-center rounded-full hover:bg-slate-100"
                            >
                                <svg v-if="isDetectingLocation" class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>

                            <!-- Voice / Mic Icon -->
                            <button
                                type="button"
                                @click="startVoiceSearch"
                                title="Voice Search"
                                class="text-slate-400 hover:text-blue-600 p-1 cursor-pointer transition flex items-center justify-center"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                            </button>

                            <!-- Search Magnifying Glass Button -->
                            <button
                                type="submit"
                                class="w-7 h-7 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-xs cursor-pointer transition flex-shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CENTER: DEFAULT NAVIGATION LINKS (When at top of page) -->
                <nav v-else class="hidden lg:flex items-center gap-1">
                    <router-link
                        to="/listings?type=buy"
                        class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 transition"
                    >
                        Buy
                    </router-link>
                    <router-link
                        to="/listings?type=rent"
                        class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 transition"
                    >
                        Rent
                    </router-link>
                    <router-link
                        to="/listings?type=commercial"
                        class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 transition"
                    >
                        Commercial
                    </router-link>
                    <router-link
                        to="/listings?type=plots"
                        class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 transition"
                    >
                        Plots / Land
                    </router-link>
                    <router-link
                        to="/brokers"
                        class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 transition"
                    >
                        Brokers &amp; Builders
                    </router-link>
                </nav>

                <!-- Right Side Actions: Post Property, User Profile, Hamburger -->
                <div class="flex items-center gap-2.5 sm:gap-3 flex-shrink-0">
                    <!-- Post Property FREE CTA -->
                    <router-link
                        to="/post-property"
                        class="inline-flex items-center gap-1.5 py-2 px-3.5 rounded-full text-xs font-bold transition shadow-xs cursor-pointer hover:opacity-95"
                        :style="isScrolled ? { backgroundColor: '#fff', color: brandPrimaryColor } : { backgroundColor: brandAccentColor || '#ff6b35', color: '#fff', boxShadow: '0 4px 12px ' + (brandAccentColor || '#ff6b35') + '40' }"
                    >
                        <span>Post property</span>
                        <span class="bg-black/20 text-white text-[9px] font-black px-1.5 py-0.2 rounded uppercase tracking-wider">FREE</span>
                    </router-link>

                    <!-- Authenticated User Avatar with Dropdown (Hover & Click) -->
                    <div
                        v-if="currentUser"
                        class="relative"
                        @mouseenter="openUserDropdown"
                        @mouseleave="scheduleCloseUserDropdown"
                    >
                        <button
                            type="button"
                            @click.stop="toggleUserDropdown"
                            class="flex items-center gap-2 py-1 px-2.5 rounded-full cursor-pointer transition select-none"
                            :class="isScrolled ? 'hover:bg-white/10 text-white' : 'hover:bg-slate-100 text-slate-800'"
                        >
                            <div class="w-8 h-8 rounded-full bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center shadow-xs">
                                {{ userInitials }}
                            </div>
                            <span class="text-xs font-bold hidden sm:inline max-w-[120px] truncate">{{ currentUser.name }}</span>
                            <svg class="w-3.5 h-3.5 opacity-70 transition-transform duration-200" :class="{ 'rotate-180': isUserDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Dropdown Menu with zero-gap hover bridge -->
                        <div
                            v-if="isUserDropdownOpen"
                            @click.stop
                            @mouseenter="openUserDropdown"
                            @mouseleave="scheduleCloseUserDropdown"
                            class="absolute right-0 top-full pt-1.5 z-50 animate-fadeIn"
                        >
                            <div class="w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-2 text-left">
                                <!-- User info banner -->
                                <div class="px-4 py-2.5 border-b border-slate-100">
                                    <span class="text-xs font-black text-slate-900 block truncate">{{ currentUser.name }}</span>
                                    <span class="text-[11px] text-slate-500 block truncate">{{ currentUser.email }}</span>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md">
                                            {{ currentUser.role || 'Owner' }}
                                        </span>
                                        <span class="text-[10px] font-semibold text-slate-500">
                                            {{ currentUser.country_code || '+91' }} {{ currentUser.mobile }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-1.5">
                                    <router-link
                                        to="/profile"
                                        @click="isUserDropdownOpen = false"
                                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition"
                                    >
                                        <span class="text-base">👤</span>
                                        <div>
                                            <span class="block">My Profile &amp; Account</span>
                                            <span class="text-[10px] font-normal text-slate-400">Edit name, email &amp; role</span>
                                        </div>
                                    </router-link>

                                    <router-link
                                        to="/profile"
                                        @click="isUserDropdownOpen = false"
                                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition"
                                    >
                                        <span class="text-base">🏢</span>
                                        <div>
                                            <span class="block">Manage My Properties</span>
                                            <span class="text-[10px] font-normal text-slate-400">View, edit &amp; toggle status</span>
                                        </div>
                                    </router-link>

                                    <router-link
                                        to="/post-property"
                                        @click="isUserDropdownOpen = false"
                                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition"
                                    >
                                        <span class="text-base">➕</span>
                                        <div>
                                            <span class="block">Post New Property</span>
                                            <span class="text-[10px] font-normal text-emerald-600/80">100% Free with 0% brokerage</span>
                                        </div>
                                    </router-link>
                                </div>

                                <div class="border-t border-slate-100 pt-1">
                                    <button
                                        type="button"
                                        @click="handleLogout(); isUserDropdownOpen = false;"
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer text-left"
                                    >
                                        <span class="text-base">🚪</span>
                                        <span>Log Out</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unauthenticated: Login Button -->
                    <button
                        v-else
                        @click="isAuthModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition cursor-pointer"
                        :class="isScrolled ? 'text-white hover:bg-white/15 border border-white/30' : 'text-slate-700 hover:text-blue-600 hover:bg-blue-50 border border-slate-200'"
                    >
                        <svg class="w-3.5 h-3.5" :class="isScrolled ? 'text-white' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="hidden sm:inline">Login / Register</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile OTP Authentication Modal -->
        <AuthModal
            :isOpen="isAuthModalOpen"
            @close="isAuthModalOpen = false"
            @authenticated="onAuthenticated"
        />
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthModal from './AuthModal.vue';
import { useVoiceSearch } from '../composables/useVoiceSearch';
import { useUserLocation } from '../composables/useUserLocation';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const router = useRouter();
const { openVoiceModal } = useVoiceSearch();
const { currentCity: selectedCity, isDetectingLocation, detectLocation, setCity } = useUserLocation();
const {
    companyName,
    companyShortName,
    companyTagline,
    companySubTagline,
    companyLogoUrl,
    brandPrimaryColor,
    brandAccentColor,
} = useCompanyBranding();

const isAuthModalOpen = ref(false);
const currentUser = ref(null);
const isUserDropdownOpen = ref(false);

// Sticky Header State
const isScrolled = ref(false);
const stickySearchType = ref('buy');
const stickyKeyword = ref('');

const handleDetectLocation = () => {
    detectLocation((data) => {
        stickyKeyword.value = 'Near me';
        router.push({
            path: '/listings',
            query: {
                type: stickySearchType.value,
                city: data.city,
                near_me: 'true',
                lat: data.lat || undefined,
                lng: data.lng || undefined,
            },
        });
    });
};

const handleCitySelectChange = (e) => {
    const val = e.target.value;
    if (val === '__DETECT__') {
        handleDetectLocation();
    } else {
        setCity(val);
    }
};

const startVoiceSearch = () => {
    openVoiceModal((transcript) => {
        stickyKeyword.value = transcript;
        handleStickySearch();
    }, {
        type: stickySearchType.value,
        city: selectedCity.value,
    });
};

const userInitials = computed(() => {
    if (!currentUser.value?.name) return 'U';
    const parts = currentUser.value.name.trim().split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return currentUser.value.name.slice(0, 2).toUpperCase();
});

const handleScroll = () => {
    // When scrolled past the top hero section (~260px), trigger sticky searchbar
    isScrolled.value = window.scrollY > 260;
};

const handleStickySearch = () => {
    router.push({
        path: '/listings',
        query: {
            type: stickySearchType.value,
            city: selectedCity.value,
            keyword: stickyKeyword.value || undefined,
        },
    });
};

const syncCurrentUser = () => {
    try {
        const stored = localStorage.getItem('realhem_user');
        currentUser.value = stored ? JSON.parse(stored) : null;
    } catch {
        currentUser.value = null;
    }
};

const onAuthenticated = (user) => {
    currentUser.value = user;
};

const handleLogout = async () => {
    const token = localStorage.getItem('realhem_user_token');
    if (token) {
        try {
            await fetch('/api/auth/logout', {
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
    localStorage.removeItem('realhem_user_token');
    localStorage.removeItem('realhem_user');
    currentUser.value = null;
};

let closeDropdownTimer = null;

const openUserDropdown = () => {
    if (closeDropdownTimer) {
        clearTimeout(closeDropdownTimer);
        closeDropdownTimer = null;
    }
    isUserDropdownOpen.value = true;
};

const scheduleCloseUserDropdown = () => {
    if (closeDropdownTimer) clearTimeout(closeDropdownTimer);
    closeDropdownTimer = setTimeout(() => {
        isUserDropdownOpen.value = false;
    }, 180);
};

const toggleUserDropdown = () => {
    if (closeDropdownTimer) {
        clearTimeout(closeDropdownTimer);
        closeDropdownTimer = null;
    }
    isUserDropdownOpen.value = !isUserDropdownOpen.value;
};

const closeDropdownOnOutside = () => {
    isUserDropdownOpen.value = false;
};

onMounted(() => {
    syncCurrentUser();
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('storage', syncCurrentUser);
    window.addEventListener('click', closeDropdownOnOutside);

    // Listen for custom open-auth-modal events dispatched anywhere in the SPA
    window.addEventListener('open-auth-modal', () => {
        isAuthModalOpen.value = true;
    });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('storage', syncCurrentUser);
    window.removeEventListener('click', closeDropdownOnOutside);
});
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.22s ease-out forwards;
}
</style>

