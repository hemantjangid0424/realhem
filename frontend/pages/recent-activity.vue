<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 pb-24">
        <!-- Auth Modal for quick login & sync -->
        <AuthModal
            :is-open="isAuthModalOpen"
            @close="isAuthModalOpen = false"
            @authenticated="handleUserAuthenticated"
        />

        <!-- Top Header Hero -->
        <section
            class="text-white py-8 sm:py-10 px-4 sm:px-6 lg:px-8 shadow-xs transition-all duration-300 relative overflow-hidden"
            :style="{ background: heroGradient }"
        >
            <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs font-semibold backdrop-blur-md">
                        <span>📊 Activity Dashboard</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight flex items-center gap-3">
                        <span>Your Real Estate Activity</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 max-w-xl">
                        Quickly access your recent searches, viewed listings, shortlisted favourites, and contacted properties.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    <router-link
                        to="/listings"
                        class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-extrabold text-xs shadow-md hover:bg-slate-100 transition flex items-center gap-1.5"
                    >
                        <span>Explore Listings</span>
                        <span>&rarr;</span>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- EXACT TAB BAR (Matching 99acres style from user reference image) -->
        <div class="border-b border-slate-200 bg-white sticky top-16 z-30 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-6 sm:gap-12 overflow-x-auto scrollbar-none">
                <!-- 1. Recent Searches -->
                <button
                    type="button"
                    @click="activeModule = 'searches'"
                    class="py-4 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                    :class="activeModule === 'searches' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-600 hover:text-slate-900 border-b-[3px] border-transparent'"
                >
                    <span>{{ searchCount }} Recent Searches</span>
                </button>

                <!-- 2. Viewed -->
                <button
                    type="button"
                    @click="activeModule = 'viewed'"
                    class="py-4 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                    :class="activeModule === 'viewed' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-600 hover:text-slate-900 border-b-[3px] border-transparent'"
                >
                    <span>{{ viewedCount }} Viewed</span>
                </button>

                <!-- 3. Shortlisted -->
                <button
                    type="button"
                    @click="activeModule = 'shortlisted'"
                    class="py-4 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                    :class="activeModule === 'shortlisted' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-600 hover:text-slate-900 border-b-[3px] border-transparent'"
                >
                    <span>{{ shortlistCount }} Shortlisted</span>
                </button>

                <!-- 4. Contacted -->
                <button
                    type="button"
                    @click="activeModule = 'contacted'"
                    class="py-4 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                    :class="activeModule === 'contacted' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-600 hover:text-slate-900 border-b-[3px] border-transparent'"
                >
                    <span>{{ contactedCount }} Contacted</span>
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-6">
            <!-- Guest Notice Banner -->
            <div
                v-if="!isLoggedIn"
                class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50/50 to-blue-50 border border-blue-200/90 shadow-2xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4"
            >
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-xs">
                        💾
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-slate-900">Stored on this browser (Guest Mode)</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 px-2 py-0.5 rounded-md">
                                Local Only
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Your recent searches, viewed listings, and shortlisted favourites are currently preserved on this browser. Sign in to synchronize them seamlessly across all devices.
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

            <!-- Loading State -->
            <div v-if="isLoading" class="py-20 text-center space-y-3 bg-white rounded-3xl border border-slate-200">
                <div class="w-8 h-8 border-3 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-slate-500 font-semibold">Loading your {{ currentTabLabel }}...</p>
            </div>

            <!-- ========================================================= -->
            <!-- MODULE 1: RECENT SEARCHES                                 -->
            <!-- ========================================================= -->
            <div v-else-if="activeModule === 'searches'" class="space-y-4">
                <!-- Search Query bar if multiple searches -->
                <div v-if="recentSearches.length > 3" class="max-w-md">
                    <input
                        v-model="searchFilterQuery"
                        type="text"
                        placeholder="Filter your recent searches..."
                        class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3.5 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredSearches.length === 0"
                    class="py-16 px-4 text-center bg-white rounded-3xl border border-slate-200 space-y-4"
                >
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-3xl">
                        🔍
                    </div>
                    <div class="space-y-1 max-w-md mx-auto">
                        <h3 class="text-base font-black text-slate-900">No Recent Searches</h3>
                        <p class="text-xs text-slate-500">
                            Whenever you search for properties by city, locality, BHK, or budget, your searches will appear here for fast one-click replay.
                        </p>
                    </div>
                    <router-link
                        to="/"
                        class="inline-block py-2.5 px-5 rounded-xl text-white font-bold text-xs shadow-xs"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Search on Homepage
                    </router-link>
                </div>

                <!-- Searches List -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(item, idx) in filteredSearches"
                        :key="item.id || idx"
                        class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition p-4 sm:p-5 flex flex-col justify-between group relative"
                    >
                        <div class="space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-blue-50 text-blue-700">
                                    {{ item.metadata?.property_for || 'BUY' }} SEARCH
                                </span>
                                <button
                                    type="button"
                                    @click="removeItem('recent_search', item.id || item.metadata?.title)"
                                    class="text-slate-400 hover:text-rose-600 text-xs p-1 rounded transition cursor-pointer"
                                    title="Delete this search"
                                >
                                    ✕
                                </button>
                            </div>

                            <h4 class="text-sm font-black text-slate-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition">
                                {{ item.metadata?.title || item.metadata?.keyword || 'Property Search' }}
                            </h4>

                            <!-- Search Criteria Badges -->
                            <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                                <span v-if="item.metadata?.city" class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold inline-flex items-center gap-1">
                                    <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                    <span>{{ item.metadata?.city }}</span>
                                </span>
                                <span v-if="item.metadata?.bhk" class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">
                                    🛏️ {{ item.metadata?.bhk }} BHK
                                </span>
                                <span v-if="item.metadata?.budget" class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">
                                    💰 {{ item.metadata?.budget }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-400 text-[11px]">
                                {{ formatViewedTime(item.created_at) }}
                            </span>
                            <button
                                type="button"
                                @click="replaySearch(item)"
                                class="px-3 py-1.5 rounded-xl font-bold text-xs shadow-2xs transition flex items-center gap-1 cursor-pointer hover:opacity-90"
                                :style="{ backgroundColor: brandPrimaryColor + '18', color: brandPrimaryColor }"
                            >
                                <span>Search Again</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- MODULE 2: VIEWED PROPERTIES                               -->
            <!-- ========================================================= -->
            <div v-else-if="activeModule === 'viewed'" class="space-y-4">
                <!-- Search & Filter bar -->
                <div v-if="recentActivities.length > 2" class="bg-white rounded-2xl border border-slate-200/90 p-3 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-md">
                        <input
                            v-model="propertyFilterQuery"
                            type="text"
                            placeholder="Search viewed properties by title, locality or city..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-9 pr-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <!-- Type Filter -->
                    <div class="flex items-center gap-1.5 overflow-x-auto">
                        <button
                            v-for="f in [{ id: 'all', label: 'All' }, { id: 'buy', label: 'Buy' }, { id: 'rent', label: 'Rent' }, { id: 'commercial', label: 'Commercial' }, { id: 'plots', label: 'Plots' }]"
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

                <!-- Empty State -->
                <div
                    v-if="filteredViewedProperties.length === 0"
                    class="py-16 px-4 text-center bg-white rounded-3xl border border-slate-200 space-y-4"
                >
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-3xl">
                        👁️
                    </div>
                    <div class="space-y-1 max-w-md mx-auto">
                        <h3 class="text-base font-black text-slate-900">No Viewed Properties</h3>
                        <p class="text-xs text-slate-500">
                            Properties you browse will automatically appear here so you can easily compare and revisit them anytime.
                        </p>
                    </div>
                    <router-link
                        to="/listings"
                        class="inline-block py-2.5 px-5 rounded-xl text-white font-bold text-xs shadow-xs"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Browse Properties
                    </router-link>
                </div>

                <!-- Viewed Grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div
                        v-for="act in filteredViewedProperties"
                        :key="act.id || act.property_id"
                        class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-xl transition-all duration-200 flex flex-col justify-between overflow-hidden group relative"
                    >
                        <router-link
                            :to="'/property/' + (act.property?.slug || act.metadata?.slug || act.property_id)"
                            class="block flex-1 flex flex-col justify-between"
                        >
                            <!-- Cover Photo -->
                            <div
                                class="h-48 bg-slate-900 relative overflow-hidden cursor-pointer"
                                @click.stop.prevent="openImageGallery(act.property || act.metadata)"
                                title="Click to view media"
                            >
                                <img
                                    v-if="getItemPhoto(act)"
                                    :src="getItemPhoto(act)"
                                    :alt="act.property?.title || act.metadata?.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div
                                    v-else-if="getItemVideo(act)"
                                    class="w-full h-full relative flex items-center justify-center bg-slate-950"
                                >
                                    <video
                                        :src="getItemVideo(act) + '#t=0.5'"
                                        preload="metadata"
                                        muted
                                        playsinline
                                        class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-300 pointer-events-none"
                                    ></video>
                                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                        <div class="w-9 h-9 rounded-full bg-purple-600/90 text-white flex items-center justify-center shadow-md">
                                            <Play class="w-4 h-4 fill-current translate-x-0.5" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 gap-1.5">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 text-slate-400 flex items-center justify-center">
                                        <Camera class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400">No media</span>
                                </div>

                                <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 flex-wrap z-10">
                                    <span v-if="act.property?.property_for || act.metadata?.property_for" class="px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-xs text-white text-[10px] font-black uppercase tracking-wider">
                                        For {{ act.property?.property_for || act.metadata?.property_for }}
                                    </span>
                                </div>

                                <!-- Action Buttons: Shortlist Heart & Remove ✕ -->
                                <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5 z-30" @click.stop.prevent>
                                    <ShortlistButton
                                        :property="act.property || { id: act.property_id, ...act.metadata }"
                                        :propertyId="act.property_id || act.property?.id"
                                        size="sm"
                                    />

                                    <button
                                        type="button"
                                        @click.prevent.stop="removeItem('view_property', act.id || act.property_id)"
                                        class="w-7 h-7 rounded-full bg-slate-900/80 hover:bg-rose-600 text-white text-xs flex items-center justify-center shadow-md transition cursor-pointer"
                                        title="Remove from history"
                                    >
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent p-3 pt-6 flex items-end justify-between z-10">
                                    <span class="text-white font-black text-sm">
                                        {{ formatPrice(act.property?.expected_price || act.metadata?.expected_price) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span v-if="act.property?.carpet_area || act.metadata?.carpet_area" class="text-slate-300 text-[11px] font-semibold">
                                            {{ act.property?.carpet_area || act.metadata?.carpet_area }} sq.ft
                                        </span>
                                        <button
                                            v-if="getItemMediaCount(act) > 0"
                                            type="button"
                                            @click.stop.prevent="openImageGallery(act.property || act.metadata)"
                                            class="bg-black/70 hover:bg-black/90 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs border border-white/10"
                                            :title="getItemPhotoCount(act) === 0 ? 'View video tour' : 'View media gallery'"
                                        >
                                            <Video v-if="getItemPhotoCount(act) === 0" class="w-3 h-3 text-purple-300" />
                                            <Camera v-else class="w-3 h-3" />
                                            <span>{{ getItemMediaCount(act) }}</span>
                                            <Video v-if="getItemVideoCount(act) > 0 && getItemPhotoCount(act) > 0" class="text-purple-300 text-[9px] font-bold" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <h3 class="text-sm font-black text-slate-900 line-clamp-1 group-hover:text-blue-600 transition">
                                        {{ act.property?.title || act.metadata?.title || 'Property Listing' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-1 flex items-center gap-1">
                                        <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                        <span>{{ act.property?.locality || act.metadata?.locality || '' }}{{ (act.property?.city || act.metadata?.city) ? ', ' + (act.property?.city || act.metadata?.city) : '' }}</span>
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400 font-medium">
                                        {{ formatViewedTime(act.created_at) }}
                                    </span>
                                    <span class="font-bold text-xs flex items-center gap-1" :style="{ color: brandPrimaryColor }">
                                        <span>Revisit</span>
                                        <span>&rarr;</span>
                                    </span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- MODULE 3: SHORTLISTED (FAVOURITES)                        -->
            <!-- ========================================================= -->
            <div v-else-if="activeModule === 'shortlisted'" class="space-y-4">
                <!-- Empty State -->
                <div
                    v-if="shortlistedProperties.length === 0"
                    class="py-16 px-4 text-center bg-white rounded-3xl border border-slate-200 space-y-4"
                >
                    <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto shadow-sm">
                        <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <div class="space-y-1 max-w-md mx-auto">
                        <h3 class="text-base font-black text-slate-900">No Shortlisted Properties</h3>
                        <p class="text-xs text-slate-500">
                            Click the favourite heart on any property listing to save it to your shortlist for fast access and side-by-side comparison.
                        </p>
                    </div>
                    <router-link
                        to="/listings"
                        class="inline-block py-2.5 px-5 rounded-xl text-white font-bold text-xs shadow-xs"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Browse &amp; Shortlist Homes
                    </router-link>
                </div>

                <!-- Shortlisted Grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div
                        v-for="item in shortlistedProperties"
                        :key="item.id || item.property_id"
                        class="bg-white rounded-2xl border border-rose-200/80 shadow-2xs hover:shadow-xl transition-all duration-200 flex flex-col justify-between overflow-hidden group relative"
                    >
                        <router-link
                            :to="'/property/' + (item.property?.slug || item.metadata?.slug || item.property_id)"
                            class="block flex-1 flex flex-col justify-between"
                        >
                            <!-- Cover Photo -->
                            <div
                                class="h-48 bg-slate-900 relative overflow-hidden cursor-pointer"
                                @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                title="Click to view media"
                            >
                                <img
                                    v-if="getItemPhoto(item)"
                                    :src="getItemPhoto(item)"
                                    :alt="item.property?.title || item.metadata?.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div
                                    v-else-if="getItemVideo(item)"
                                    class="w-full h-full relative flex items-center justify-center bg-slate-950"
                                >
                                    <video
                                        :src="getItemVideo(item) + '#t=0.5'"
                                        preload="metadata"
                                        muted
                                        playsinline
                                        class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-300 pointer-events-none"
                                    ></video>
                                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                        <div class="w-8 h-8 rounded-full bg-purple-600/90 text-white flex items-center justify-center shadow-md">
                                            <Play class="w-3.5 h-3.5 fill-current translate-x-0.5" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 gap-1.5">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 text-slate-400 flex items-center justify-center">
                                        <Camera class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400">No media</span>
                                </div>

                                <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 flex-wrap z-10">
                                    <span class="px-2.5 py-0.5 rounded-md bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1 shadow-xs">
                                        <Heart class="w-3 h-3 fill-current" />
                                        <span>Favourited</span>
                                    </span>
                                </div>

                                <!-- Un-shortlist Button -->
                                <div class="absolute top-2.5 right-2.5 z-30" @click.stop.prevent>
                                    <ShortlistButton
                                        :property="item.property || { id: item.property_id, ...item.metadata }"
                                        :propertyId="item.property_id || item.property?.id"
                                        size="sm"
                                    />
                                </div>

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent p-3 pt-6 flex items-end justify-between z-10">
                                    <span class="text-white font-black text-sm">
                                        {{ formatPrice(item.property?.expected_price || item.metadata?.expected_price) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span v-if="item.property?.carpet_area || item.metadata?.carpet_area" class="text-slate-300 text-[11px] font-semibold">
                                            {{ item.property?.carpet_area || item.metadata?.carpet_area }} sq.ft
                                        </span>
                                        <button
                                            v-if="getItemMediaCount(item) > 0"
                                            type="button"
                                            @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                            class="bg-black/70 hover:bg-black/90 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs border border-white/10"
                                            :title="getItemPhotoCount(item) === 0 ? 'View video tour' : 'View media gallery'"
                                        >
                                            <Video v-if="getItemPhotoCount(item) === 0" class="w-3 h-3 text-purple-300" />
                                            <Camera v-else class="w-3 h-3" />
                                            <span>{{ getItemMediaCount(item) }}</span>
                                            <Video v-if="getItemVideoCount(item) > 0 && getItemPhotoCount(item) > 0" class="text-purple-300 text-[9px] font-bold" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <h3 class="text-sm font-black text-slate-900 line-clamp-1 group-hover:text-rose-600 transition">
                                        {{ item.property?.title || item.metadata?.title || 'Shortlisted Property' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-1 flex items-center gap-1">
                                        <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                        <span>{{ item.property?.locality || item.metadata?.locality || '' }}{{ (item.property?.city || item.metadata?.city) ? ', ' + (item.property?.city || item.metadata?.city) : '' }}</span>
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                                    <span>View Details &rarr;</span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- MODULE 4: CONTACTED PROPERTIES                            -->
            <!-- ========================================================= -->
            <div v-else-if="activeModule === 'contacted'" class="space-y-4">
                <!-- Empty State -->
                <div
                    v-if="contactedProperties.length === 0"
                    class="py-16 px-4 text-center bg-white rounded-3xl border border-slate-200 space-y-4"
                >
                    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-3xl">
                        📞
                    </div>
                    <div class="space-y-1 max-w-md mx-auto">
                        <h3 class="text-base font-black text-slate-900">No Contacted Properties</h3>
                        <p class="text-xs text-slate-500">
                            Whenever you click "View Contact Number" or send an inquiry for a property, it will be catalogued here for fast follow-up.
                        </p>
                    </div>
                    <router-link
                        to="/listings"
                        class="inline-block py-2.5 px-5 rounded-xl text-white font-bold text-xs shadow-xs"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Explore Properties
                    </router-link>
                </div>

                <!-- Contacted Grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div
                        v-for="item in contactedProperties"
                        :key="item.id || item.property_id"
                        class="bg-white rounded-2xl border border-emerald-200/80 shadow-2xs hover:shadow-xl transition-all duration-200 flex flex-col justify-between overflow-hidden group relative"
                    >
                        <router-link
                            :to="'/property/' + (item.property?.slug || item.metadata?.slug || item.property_id)"
                            class="block flex-1 flex flex-col justify-between"
                        >
                            <!-- Cover Photo -->
                            <div
                                class="h-48 bg-slate-900 relative overflow-hidden cursor-pointer"
                                @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                title="Click to view media"
                            >
                                <img
                                    v-if="getItemPhoto(item)"
                                    :src="getItemPhoto(item)"
                                    :alt="item.property?.title || item.metadata?.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div
                                    v-else-if="getItemVideo(item)"
                                    class="w-full h-full relative flex items-center justify-center bg-slate-950"
                                >
                                    <video
                                        :src="getItemVideo(item) + '#t=0.5'"
                                        preload="metadata"
                                        muted
                                        playsinline
                                        class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-300 pointer-events-none"
                                    ></video>
                                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                        <div class="w-8 h-8 rounded-full bg-purple-600/90 text-white flex items-center justify-center shadow-md">
                                            <Play class="w-3.5 h-3.5 fill-current translate-x-0.5" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 gap-1.5">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 text-slate-400 flex items-center justify-center">
                                        <Camera class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400">No media</span>
                                </div>

                                <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 flex-wrap z-10">
                                    <span class="px-2.5 py-0.5 rounded-md bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1 shadow-xs">
                                        <Check class="w-3 h-3 text-white" :stroke-width="3" />
                                        <span>Contacted</span>
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    @click.prevent.stop="removeItem('contacted', item.id || item.property_id)"
                                    class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-slate-900/80 hover:bg-rose-600 text-white text-xs flex items-center justify-center shadow-md transition cursor-pointer z-20"
                                    title="Remove from contacted"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent p-3 pt-6 flex items-end justify-between z-10">
                                    <span class="text-white font-black text-sm">
                                        {{ formatPrice(item.property?.expected_price || item.metadata?.expected_price) }}
                                    </span>
                                    <button
                                        v-if="getItemMediaCount(item) > 0"
                                        type="button"
                                        @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                        class="bg-black/70 hover:bg-black/90 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs border border-white/10"
                                        :title="getItemPhotoCount(item) === 0 ? 'View video tour' : 'View media gallery'"
                                    >
                                        <Video v-if="getItemPhotoCount(item) === 0" class="w-3 h-3 text-purple-300" />
                                        <Camera v-else class="w-3 h-3" />
                                        <span>{{ getItemMediaCount(item) }}</span>
                                        <Video v-if="getItemVideoCount(item) > 0 && getItemPhotoCount(item) > 0" class="text-purple-300 text-[9px] font-bold" />
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <h3 class="text-sm font-black text-slate-900 line-clamp-1 group-hover:text-emerald-600 transition">
                                        {{ item.property?.title || item.metadata?.title || 'Contacted Property' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-1 flex items-center gap-1">
                                        <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                        <span>{{ item.property?.locality || item.metadata?.locality || '' }}{{ (item.property?.city || item.metadata?.city) ? ', ' + (item.property?.city || item.metadata?.city) : '' }}</span>
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">
                                        Contacted {{ formatViewedTime(item.created_at) }}
                                    </span>
                                    <span class="font-bold text-xs text-emerald-700">
                                        Re-contact &rarr;
                                    </span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Image Library Lightbox Modal -->
        <ImageGalleryModal
            :is-open="isGalleryOpen"
            :property="activeGalleryProperty"
            :initial-index="activeGalleryIndex"
            @close="isGalleryOpen = false"
            @update:is-open="isGalleryOpen = $event"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
    Camera,
    Video,
    MapPin,
    Play,
    Check,
    X,
    Building2,
} from 'lucide-vue-next';
import AuthModal from '~/components/AuthModal.vue';
import ImageGalleryModal from '~/components/ImageGalleryModal.vue';

const router = useRouter();
const route = useRoute();
const { companyName, brandPrimaryColor, heroGradient } = useCompanyBranding();
const {
    recentActivities,
    recentSearches,
    shortlistedProperties,
    contactedProperties,
    searchCount,
    viewedCount,
    shortlistCount,
    contactedCount,
    isShortlisted,
    toggleShortlist,
    loadAllActivities,
    removeItem,
    syncGuestActivitiesToServer,
} = useUserActivity();

useSeoMeta({
    title: () => `Recent Searches, Viewed & Shortlisted Properties • ${companyName.value}`,
    description: 'Track your real estate activity, recent searches, viewed listings and shortlisted favourite properties.',
    robots: 'noindex, follow',
});

// Active tab module: 'searches' | 'viewed' | 'shortlisted' | 'contacted'
const activeModule = ref<'searches' | 'viewed' | 'shortlisted' | 'contacted'>('searches');
const isAuthModalOpen = ref(false);
const isLoading = ref(false);
const isLoggedIn = ref(false);

const isGalleryOpen = ref(false);
const activeGalleryProperty = ref<any>(null);
const activeGalleryIndex = ref(0);

const openImageGallery = (property: any, index = 0) => {
    if (!property) return;
    activeGalleryProperty.value = property;
    activeGalleryIndex.value = index;
    isGalleryOpen.value = true;
};

const getItemPhoto = (item: any) => {
    const photo = item?.property?.photos?.[0] || item?.metadata?.photo;
    if (photo && typeof photo === 'string') {
        return photo;
    }
    return null;
};

const getItemVideo = (item: any) => {
    const video = item?.property?.videos?.[0] || item?.metadata?.videos?.[0];
    if (video && typeof video === 'string') {
        return video;
    }
    return null;
};

const getItemPhotoCount = (item: any) => {
    if (Array.isArray(item?.property?.photos) && item.property.photos.length > 0) {
        return item.property.photos.length;
    }
    if (item?.metadata?.photo && typeof item.metadata.photo === 'string') {
        return 1;
    }
    return 0;
};

const getItemVideoCount = (item: any) => {
    if (Array.isArray(item?.property?.videos) && item.property.videos.length > 0) {
        return item.property.videos.length;
    }
    if (Array.isArray(item?.metadata?.videos) && item.metadata.videos.length > 0) {
        return item.metadata.videos.length;
    }
    return 0;
};

const getItemMediaCount = (item: any) => {
    return getItemPhotoCount(item) + getItemVideoCount(item);
};

const searchFilterQuery = ref('');
const propertyFilterQuery = ref('');
const typeFilter = ref('all');

// Switch active module if passed in query e.g. /recent-activity?tab=viewed or tab=shortlisted
watch(
    () => route.query.tab,
    (tab) => {
        if (tab === 'viewed') activeModule.value = 'viewed';
        else if (tab === 'shortlisted' || tab === 'shortlist') activeModule.value = 'shortlisted';
        else if (tab === 'contacted') activeModule.value = 'contacted';
        else if (tab === 'searches' || tab === 'search') activeModule.value = 'searches';
    },
    { immediate: true }
);

const currentTabCount = computed(() => {
    switch (activeModule.value) {
        case 'searches': return searchCount.value;
        case 'viewed': return viewedCount.value;
        case 'shortlisted': return shortlistCount.value;
        case 'contacted': return contactedCount.value;
        default: return 0;
    }
});

const currentTabLabel = computed(() => {
    switch (activeModule.value) {
        case 'searches': return 'Searches';
        case 'viewed': return 'Viewed';
        case 'shortlisted': return 'Shortlist';
        case 'contacted': return 'Contacted';
        default: return '';
    }
});

const checkUserAuth = () => {
    if (!import.meta.client) return;
    isLoggedIn.value = !!localStorage.getItem('realhem_user_token');
};

const handleUserAuthenticated = async () => {
    isAuthModalOpen.value = false;
    checkUserAuth();
    await syncGuestActivitiesToServer();
    await loadAllActivities();
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

const filteredSearches = computed(() => {
    if (!searchFilterQuery.value.trim()) return recentSearches.value;
    const q = searchFilterQuery.value.trim().toLowerCase();
    return recentSearches.value.filter((s) => {
        const title = (s.metadata?.title || '').toLowerCase();
        const kw = (s.metadata?.keyword || '').toLowerCase();
        const city = (s.metadata?.city || '').toLowerCase();
        return title.includes(q) || kw.includes(q) || city.includes(q);
    });
});

const filteredViewedProperties = computed(() => {
    let list = [...recentActivities.value];

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

    if (propertyFilterQuery.value.trim()) {
        const q = propertyFilterQuery.value.trim().toLowerCase();
        list = list.filter((act) => {
            const title = (act.property?.title || act.metadata?.title || '').toLowerCase();
            const loc = (act.property?.locality || act.metadata?.locality || '').toLowerCase();
            const city = (act.property?.city || act.metadata?.city || '').toLowerCase();
            return title.includes(q) || loc.includes(q) || city.includes(q);
        });
    }

    return list;
});

const replaySearch = (item: any) => {
    const meta = item.metadata || {};
    const queryParams: Record<string, any> = {
        type: meta.property_for || 'buy',
    };
    if (meta.city) queryParams.city = meta.city;
    if (meta.keyword) queryParams.keyword = meta.keyword;
    if (meta.bhk) queryParams.bhk = meta.bhk;
    if (meta.budget) queryParams.budget = meta.budget;

    router.push({
        path: '/listings',
        query: queryParams,
    });
};

const handleActivityUpdated = (e) => {
    if (e?.detail?.type === 'shortlist_added' || e?.detail?.type === 'shortlist_removed') {
        return;
    }
    loadAllActivities();
};

onMounted(async () => {
    checkUserAuth();
    isLoading.value = true;
    try {
        await loadAllActivities();
        // If searched tab is empty but viewed has items, automatically highlight viewed
        if (searchCount.value === 0 && viewedCount.value > 0 && !route.query.tab) {
            activeModule.value = 'viewed';
        }
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
