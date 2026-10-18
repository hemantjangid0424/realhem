<template>
    
        <!-- Notification Toast -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="toast.show"
                class="fixed top-20 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl text-sm font-semibold border backdrop-blur-md"
                :class="toast.type === 'error'
                    ? 'bg-rose-50/95 text-rose-800 border-rose-200'
                    : 'bg-emerald-50/95 text-emerald-800 border-emerald-200'"
            >
                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0"
                    :class="toast.type === 'error' ? 'bg-rose-200 text-rose-800' : 'bg-emerald-200 text-emerald-800'">
                    <svg v-if="toast.type !== 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span>{{ toast.message }}</span>
            </div>
        </transition>

        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-[1440px] mx-auto w-full space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Property Management &amp; Moderation</h1>
                        <span class="text-xs bg-blue-50 text-blue-700 font-bold px-3 py-1 rounded-full border border-blue-200">
                            {{ totalCount }} Total Listings
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Review, approve, feature, edit, or delete residential and commercial properties</p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="fetchProperties"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                        title="Refresh listing table"
                    >
                        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Refresh</span>
                    </button>
                    <a
                        href="/post-property"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Post New Listing</span>
                    </a>
                </div>
            </div>

            <!-- Moderation Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                <button
                    v-for="tab in filterTabs"
                    :key="tab.key"
                    @click="setTab(tab.key)"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
                    :class="activeTab === tab.key
                        ? 'bg-slate-900 text-white shadow-sm'
                        : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                >
                    <span>{{ tab.label }}</span>
                    <span
                        class="px-2 py-0.5 rounded-full text-[10px] font-black"
                        :class="activeTab === tab.key ? tab.activeBadgeClass : tab.badgeClass"
                    >
                        {{ tab.count }}
                    </span>
                </button>
            </div>

            <!-- Search & Filters Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        v-model="searchQuery"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Search by title, project, locality, city, pincode, or owner's name/mobile/email..."
                        class="w-full pl-10 pr-9 py-2 bg-slate-50 hover:bg-slate-100/60 focus:bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                    />
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''; fetchProperties()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-2.5 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                    <!-- Property For Filter -->
                    <select
                        v-model="filterPropertyFor"
                        @change="fetchProperties"
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer"
                    >
                        <option value="all">All Intents (Buy/Rent)</option>
                        <option value="Sell">For Sale</option>
                        <option value="Rent">For Rent</option>
                        <option value="PG">PG / Co-living</option>
                    </select>

                    <!-- City Filter -->
                    <select
                        v-model="filterCity"
                        @change="fetchProperties"
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer"
                    >
                        <option value="all">All Cities</option>
                        <option v-for="c in availableCities" :key="c" :value="c">{{ c }}</option>
                    </select>

                    <!-- Sort -->
                    <select
                        v-model="sortBy"
                        @change="fetchProperties"
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer"
                    >
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="price_low">Price: Low to High</option>
                    </select>
                </div>
            </div>

            <!-- Properties Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-12 text-center">ID</th>
                                <th class="py-3.5 px-4 min-w-[260px]">Property Listing</th>
                                <th class="py-3.5 px-4 min-w-[140px]">Price &amp; Area</th>
                                <th class="py-3.5 px-4 min-w-[160px]">Owner / Poster</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Badges</th>
                                <th class="py-3.5 px-4 text-right min-w-[170px] sticky right-0 bg-slate-50/95 backdrop-blur-xs shadow-[-4px_0_10px_rgba(0,0,0,0.03)] z-10">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <!-- Loading Skeleton -->
                            <tr v-if="isLoading" v-for="n in 5" :key="'skel-' + n" class="animate-pulse">
                                <td class="py-4 px-4 text-center"><div class="h-4 bg-slate-200 rounded w-6 mx-auto"></div></td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-12 rounded-xl bg-slate-200 shrink-0"></div>
                                        <div class="space-y-2 flex-1">
                                            <div class="h-3.5 bg-slate-200 rounded w-3/4"></div>
                                            <div class="h-2.5 bg-slate-200 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 space-y-1.5"><div class="h-3.5 bg-slate-200 rounded w-20"></div><div class="h-2.5 bg-slate-200 rounded w-12"></div></td>
                                <td class="py-4 px-4 space-y-1.5"><div class="h-3.5 bg-slate-200 rounded w-24"></div><div class="h-2.5 bg-slate-200 rounded w-16"></div></td>
                                <td class="py-4 px-4 text-center"><div class="h-5 bg-slate-200 rounded-full w-16 mx-auto"></div></td>
                                <td class="py-4 px-4 text-center"><div class="h-5 bg-slate-200 rounded w-14 mx-auto"></div></td>
                                <td class="py-4 px-4 text-right sticky right-0 bg-white/95 backdrop-blur-xs z-10"><div class="h-7 bg-slate-200 rounded w-28 ml-auto"></div></td>
                            </tr>

                            <!-- Actual Records -->
                            <tr
                                v-else-if="properties.length > 0"
                                v-for="prop in properties"
                                :key="prop.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <!-- ID -->
                                <td class="py-4 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    #{{ prop.id }}
                                </td>

                                <!-- Property Info & Thumbnail -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3.5">
                                        <!-- Photo Thumbnail -->
                                        <div class="relative w-14 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 group">
                                            <img
                                                v-if="prop.photos && prop.photos.length > 0"
                                                :src="prop.photos[0]"
                                                :alt="prop.title"
                                                class="w-full h-full object-cover group-hover:scale-105 transition"
                                                loading="lazy"
                                            />
                                            <video
                                                v-else-if="prop.videos && prop.videos.length > 0"
                                                :src="prop.videos[0] + '#t=0.5'"
                                                preload="metadata"
                                                muted
                                                playsinline
                                                class="w-full h-full object-cover pointer-events-none opacity-80"
                                            ></video>
                                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            </div>
                                            <span
                                                v-if="((prop.photos?.length || 0) + (prop.videos?.length || 0)) > 0"
                                                class="absolute bottom-0.5 right-0.5 px-1 py-0.2 bg-black/75 text-white rounded text-[9px] font-bold flex items-center gap-0.5"
                                            >
                                                <span v-if="!prop.photos || prop.photos.length === 0">🎥</span>
                                                <span v-else>📷</span>
                                                <span>{{ (prop.photos?.length || 0) + (prop.videos?.length || 0) }}</span>
                                            </span>
                                        </div>

                                        <!-- Title & Location -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <a
                                                    :href="prop.slug ? `/property/${prop.slug}` : `/property/${prop.id}`"
                                                    target="_blank"
                                                    class="font-bold text-slate-900 hover:text-blue-600 transition truncate text-xs flex items-center gap-1 group"
                                                    title="View live listing on client portal"
                                                >
                                                    <span class="truncate">{{ prop.title }}</span>
                                                    <svg class="w-3 h-3 text-slate-400 group-hover:text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            </div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5 truncate">
                                                <span v-if="prop.bedrooms" class="font-semibold text-slate-700">{{ prop.bedrooms }} BHK</span>
                                                <span v-if="prop.bedrooms">&bull;</span>
                                                <span class="truncate">{{ prop.property_type }}</span>
                                                <span>&bull;</span>
                                                <span class="text-blue-700 font-semibold">{{ prop.property_for }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5 truncate">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span class="truncate">{{ [prop.project_name, prop.locality, prop.city].filter(Boolean).join(', ') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Price & Area -->
                                <td class="py-4 px-4">
                                    <div class="font-extrabold text-slate-900 text-xs">
                                        {{ formatPriceWords(prop.expected_price) }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ prop.carpet_area }} sq.ft
                                        <span v-if="prop.price_per_sqft" class="text-[10px] text-slate-400">
                                            (₹{{ prop.price_per_sqft }}/sq.ft)
                                        </span>
                                    </div>
                                    <span v-if="prop.price_negotiable" class="inline-block text-[9px] bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded mt-0.5 font-semibold">
                                        Negotiable
                                    </span>
                                </td>

                                <!-- Owner / Poster -->
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-900 flex items-center gap-1.5 truncate">
                                        <span>{{ prop.user?.name || 'Portal User' }}</span>
                                        <span
                                            class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase"
                                            :class="{
                                                'bg-purple-100 text-purple-800': prop.user?.role === 'admin',
                                                'bg-blue-100 text-blue-800': prop.user?.role === 'agent',
                                                'bg-emerald-100 text-emerald-800': prop.user?.role === 'owner',
                                                'bg-slate-100 text-slate-700': !prop.user?.role || prop.user?.role === 'user',
                                            }"
                                        >
                                            {{ prop.user?.role || prop.user_type || 'User' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                        {{ prop.user?.mobile ? (prop.user?.country_code || '+91') + ' ' + prop.user?.mobile : (prop.user?.email || 'No contact') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Posted {{ formatDate(prop.created_at) }}
                                    </div>
                                </td>

                                <!-- Status Badge & Quick Dropdown -->
                                <td class="py-4 px-4 text-center">
                                    <div class="inline-flex items-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-xs"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': prop.status === 'active',
                                                'bg-amber-50 text-amber-700 border-amber-200 animate-pulse': prop.status === 'pending' || prop.status === 'pending_approval',
                                                'bg-rose-50 text-rose-700 border-rose-200': prop.status === 'rejected',
                                                'bg-slate-100 text-slate-600 border-slate-200': prop.status === 'inactive' || prop.status === 'sold' || prop.status === 'rented',
                                            }"
                                        >
                                            <span
                                                class="w-1.5 h-1.5 rounded-full"
                                                :class="{
                                                    'bg-emerald-500': prop.status === 'active',
                                                    'bg-amber-500': prop.status === 'pending' || prop.status === 'pending_approval',
                                                    'bg-rose-500': prop.status === 'rejected',
                                                    'bg-slate-400': prop.status === 'inactive' || prop.status === 'sold' || prop.status === 'rented',
                                                }"
                                            ></span>
                                            {{ prop.status === 'pending_approval' ? 'Pending' : prop.status }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Badges (Verified & Featured Toggles) -->
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Verified Toggle -->
                                        <button
                                            @click="toggleVerified(prop)"
                                            class="p-1.5 rounded-lg border transition cursor-pointer"
                                            :class="prop.is_verified
                                                ? 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'
                                                : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100'"
                                            :title="prop.is_verified ? 'Verified (Click to unverify)' : 'Unverified (Click to verify)'"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        </button>

                                        <!-- Featured Toggle -->
                                        <button
                                            @click="toggleFeatured(prop)"
                                            class="p-1.5 rounded-lg border transition cursor-pointer"
                                            :class="prop.is_featured
                                                ? 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100'
                                                : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100'"
                                            :title="prop.is_featured ? 'Featured (Click to unfeature)' : 'Standard (Click to feature)'"
                                        >
                                            <svg class="w-3.5 h-3.5" :fill="prop.is_featured ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Actions Toolbar -->
                                <td class="py-4 px-4 text-right sticky right-0 bg-white/95 backdrop-blur-xs group-hover:bg-slate-50/95 transition shadow-[-4px_0_10px_rgba(0,0,0,0.03)] z-10">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Quick Approve (If Pending) -->
                                        <button
                                            v-if="prop.status === 'pending' || prop.status === 'pending_approval'"
                                            @click="quickApprove(prop)"
                                            class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition cursor-pointer"
                                            title="Approve & Publish Property"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>

                                        <!-- Quick Reject (If Pending/Active) -->
                                        <button
                                            v-if="prop.status === 'pending' || prop.status === 'pending_approval' || prop.status === 'active'"
                                            @click="quickReject(prop)"
                                            class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                            title="Reject Listing"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>

                                        <!-- Review / View -->
                                        <button
                                            @click="openReviewModal(prop)"
                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                                            title="Review full specs and photos"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            @click="openEditModal(prop)"
                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                                            title="Edit Property Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            @click="openDeleteModal(prop)"
                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                            title="Permanently Delete Property"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-else>
                                <td colspan="7" class="py-12 text-center">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800">No properties found</h3>
                                        <p class="text-xs text-slate-400">
                                            {{ searchQuery ? 'Try adjusting your search terms or filters.' : 'There are no listings matching this status category.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="pagination.total > 0" class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                    <div class="flex items-center gap-4 flex-wrap justify-center md:justify-start">
                        <div>
                            Showing <strong class="text-slate-800">{{ pagination.from || 1 }}</strong> to
                            <strong class="text-slate-800">{{ pagination.to || properties.length }}</strong> of
                            <strong class="text-slate-800">{{ pagination.total }}</strong> listings
                        </div>

                        <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                            <span class="text-slate-500 text-[11px] font-medium">Per page:</span>
                            <select
                                v-model="perPage"
                                @change="fetchProperties(1)"
                                class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer shadow-2xs"
                            >
                                <option :value="10">10</option>
                                <option :value="15">15</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <!-- First Page -->
                        <button
                            @click="changePage(1)"
                            :disabled="pagination.current_page <= 1"
                            class="p-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                            title="First Page"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                        </button>

                        <!-- Prev -->
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition flex items-center gap-1 cursor-pointer"
                            title="Previous Page"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span class="hidden sm:inline">Prev</span>
                        </button>

                        <!-- Page Numbers & Ellipses -->
                        <template v-for="(p, idx) in visiblePages" :key="idx">
                            <span v-if="p === '...'" class="px-2 py-1 text-slate-400 font-bold select-none">&hellip;</span>
                            <button
                                v-else
                                @click="changePage(p)"
                                class="min-w-8 h-8 px-2 rounded-lg font-bold transition flex items-center justify-center cursor-pointer text-xs"
                                :class="pagination.current_page === p
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            >
                                {{ p }}
                            </button>
                        </template>

                        <!-- Next -->
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition flex items-center gap-1 cursor-pointer"
                            title="Next Page"
                        >
                            <span class="hidden sm:inline">Next</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <!-- Last Page -->
                        <button
                            @click="changePage(pagination.last_page)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="p-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                            title="Last Page"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- ========================================== -->
        <!-- 1. Review & Details Modal -->
        <!-- ========================================== -->
        <div
            v-if="selectedProperty && isReviewModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
            @click.self="isReviewModalOpen = false"
        >
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
                <!-- Modal Top Header -->
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-3">
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider border"
                            :class="{
                                'bg-emerald-50 text-emerald-700 border-emerald-200': selectedProperty.status === 'active',
                                'bg-amber-50 text-amber-700 border-amber-200': selectedProperty.status === 'pending' || selectedProperty.status === 'pending_approval',
                                'bg-rose-50 text-rose-700 border-rose-200': selectedProperty.status === 'rejected',
                                'bg-slate-100 text-slate-600 border-slate-200': selectedProperty.status === 'inactive' || selectedProperty.status === 'sold',
                            }"
                        >
                            {{ selectedProperty.status }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">Listing #{{ selectedProperty.id }}</span>
                        <span v-if="selectedProperty.is_verified" class="text-xs bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded-full border border-blue-200 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Verified
                        </span>
                        <span v-if="selectedProperty.is_featured" class="text-xs bg-amber-50 text-amber-700 font-bold px-2 py-0.5 rounded-full border border-amber-200 flex items-center gap-1">
                            ★ Featured
                        </span>
                    </div>

                    <button
                        @click="isReviewModalOpen = false"
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Title & Action Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 leading-tight">
                                {{ selectedProperty.title }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>{{ [selectedProperty.address, selectedProperty.project_name, selectedProperty.locality, selectedProperty.city, selectedProperty.state, selectedProperty.pincode].filter(Boolean).join(', ') }}</span>
                            </p>
                        </div>

                        <!-- Moderation Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                v-if="selectedProperty.status !== 'active'"
                                @click="modalApprove"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Approve &amp; Publish</span>
                            </button>
                            <button
                                v-if="selectedProperty.status !== 'rejected'"
                                @click="modalReject"
                                class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Reject</span>
                            </button>
                            <button
                                @click="openEditModal(selectedProperty); isReviewModalOpen = false"
                                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                            >
                                Edit Specs
                            </button>
                        </div>
                    </div>

                    <!-- Photo Gallery Carousel -->
                    <div v-if="selectedProperty.photos && selectedProperty.photos.length > 0" class="space-y-3">
                        <div class="relative h-64 sm:h-80 w-full rounded-2xl overflow-hidden bg-slate-900 border border-slate-200 flex items-center justify-center">
                            <img
                                :src="selectedProperty.photos[activePhotoIndex] || selectedProperty.photos[0]"
                                :alt="selectedProperty.title"
                                class="w-full h-full object-contain"
                            />
                            <div class="absolute bottom-3 right-3 bg-black/70 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                                Photo {{ activePhotoIndex + 1 }} / {{ selectedProperty.photos.length }}
                            </div>
                        </div>

                        <!-- Thumbnails -->
                        <div v-if="selectedProperty.photos.length > 1" class="flex items-center gap-2 overflow-x-auto pb-1">
                            <button
                                v-for="(photo, idx) in selectedProperty.photos"
                                :key="idx"
                                @click="activePhotoIndex = idx"
                                class="w-16 h-12 rounded-xl overflow-hidden border-2 shrink-0 transition"
                                :class="activePhotoIndex === idx ? 'border-blue-600 scale-105' : 'border-transparent opacity-70 hover:opacity-100'"
                            >
                                <img :src="photo" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Key Metrics Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Expected Price</span>
                            <span class="text-base font-black text-slate-900">{{ formatPriceWords(selectedProperty.expected_price) }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Carpet Area</span>
                            <span class="text-base font-black text-slate-900">{{ selectedProperty.carpet_area }} sq.ft</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Layout</span>
                            <span class="text-base font-black text-slate-900">{{ selectedProperty.bedrooms ? selectedProperty.bedrooms + ' BHK' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Furnishing</span>
                            <span class="text-base font-black text-slate-900">{{ selectedProperty.furnishing_status || 'Unspecified' }}</span>
                        </div>
                    </div>

                    <!-- Owner / Contact Box -->
                    <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-200/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ (selectedProperty.user?.name || 'U').charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <span>{{ selectedProperty.user?.name || 'Portal User' }}</span>
                                    <span class="text-[9px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.2 rounded uppercase">
                                        {{ selectedProperty.user?.role || selectedProperty.user_type || 'User' }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    {{ selectedProperty.user?.email }} &bull; {{ selectedProperty.user?.country_code || '+91' }} {{ selectedProperty.user?.mobile }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                v-if="selectedProperty.user?.mobile"
                                :href="`tel:${selectedProperty.user?.mobile}`"
                                class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition"
                            >
                                Call Owner
                            </a>
                            <a
                                v-if="selectedProperty.user?.mobile"
                                :href="`https://wa.me/${(selectedProperty.user?.country_code || '+91').replace('+', '')}${selectedProperty.user?.mobile}`"
                                target="_blank"
                                class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition"
                            >
                                WhatsApp
                            </a>
                        </div>
                    </div>

                    <!-- Detailed Specifications Table -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Detailed Specifications</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-6 text-xs">
                            <div><span class="text-slate-400 block text-[10px]">Property For:</span> <strong class="text-slate-800">{{ selectedProperty.property_for }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Property Type:</span> <strong class="text-slate-800">{{ selectedProperty.property_type }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Project / Society:</span> <strong class="text-slate-800">{{ selectedProperty.project_name || 'Individual' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Bathrooms:</span> <strong class="text-slate-800">{{ selectedProperty.bathrooms ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Balconies:</span> <strong class="text-slate-800">{{ selectedProperty.balconies ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Floor:</span> <strong class="text-slate-800">{{ selectedProperty.floor_no ? selectedProperty.floor_no + ' of ' + (selectedProperty.total_floors || 'N/A') : 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Facing:</span> <strong class="text-slate-800">{{ selectedProperty.facing || 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Construction Status:</span> <strong class="text-slate-800">{{ selectedProperty.construction_status || 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Super Built-up Area:</span> <strong class="text-slate-800">{{ selectedProperty.super_builtup_area ? selectedProperty.super_builtup_area + ' sq.ft' : 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Maintenance Charge:</span> <strong class="text-slate-800">{{ selectedProperty.maintenance_charge ? '₹' + selectedProperty.maintenance_charge + '/mo' : 'None' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Pincode:</span> <strong class="text-slate-800">{{ selectedProperty.pincode || 'N/A' }}</strong></div>
                            <div><span class="text-slate-400 block text-[10px]">Coordinates:</span> <strong class="text-slate-800 font-mono text-[11px]">{{ selectedProperty.latitude && selectedProperty.longitude ? `${Number(selectedProperty.latitude).toFixed(4)}, ${Number(selectedProperty.longitude).toFixed(4)}` : 'N/A' }}</strong></div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div v-if="selectedProperty.amenities && selectedProperty.amenities.length > 0" class="space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Amenities</h3>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="amenity in selectedProperty.amenities"
                                :key="amenity"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold flex items-center gap-1"
                            >
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ amenity }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="selectedProperty.description" class="space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Description</h3>
                        <div class="p-3.5 bg-slate-50 rounded-xl text-xs text-slate-600 leading-relaxed whitespace-pre-line border border-slate-200/80">
                            {{ selectedProperty.description }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs">
                    <a
                        :href="selectedProperty.slug ? `/property/${selectedProperty.slug}` : `/property/${selectedProperty.id}`"
                        target="_blank"
                        class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1"
                    >
                        <span>Open Live Preview on Portal</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <button
                        @click="isReviewModalOpen = false"
                        class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold transition"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. Edit Property Modal -->
        <!-- ========================================== -->
        <div
            v-if="isEditModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
            @click.self="isEditModalOpen = false"
        >
            <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                    <div>
                        <h2 class="text-base font-black text-slate-900">Edit Property #{{ editForm.id }}</h2>
                        <p class="text-xs text-slate-500">Modify listing specifications, pricing, and moderation settings</p>
                    </div>
                    <button
                        @click="isEditModalOpen = false"
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitEditForm" class="flex-1 overflow-y-auto p-6 space-y-5">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Title *</label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            required
                            class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <!-- Row 1: Intent, Type & Project Name -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Property For *</label>
                            <select
                                v-model="editForm.property_for"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            >
                                <option value="Sell">For Sale</option>
                                <option value="Rent">For Rent</option>
                                <option value="PG">PG</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Property Type *</label>
                            <input
                                v-model="editForm.property_type"
                                type="text"
                                required
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Project / Society Name</label>
                            <input
                                v-model="editForm.project_name"
                                type="text"
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>

                    <!-- Row 2: Location (City, Locality, Sub Locality, Pincode) -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">City *</label>
                            <input
                                v-model="editForm.city"
                                type="text"
                                required
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Locality *</label>
                            <input
                                v-model="editForm.locality"
                                type="text"
                                required
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sub Locality</label>
                            <input
                                v-model="editForm.sub_locality"
                                type="text"
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pincode</label>
                            <input
                                v-model="editForm.pincode"
                                type="text"
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>

                    <!-- Row 3: Pricing & Area -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Expected Price (₹) *</label>
                            <input
                                v-model.number="editForm.expected_price"
                                type="number"
                                required
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                            <span class="text-[10px] text-blue-600 font-semibold block mt-0.5">{{ formatPriceWords(editForm.expected_price) }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Carpet Area (sq.ft) *</label>
                            <input
                                v-model.number="editForm.carpet_area"
                                type="number"
                                required
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Bedrooms</label>
                            <input
                                v-model.number="editForm.bedrooms"
                                type="number"
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Bathrooms</label>
                            <input
                                v-model.number="editForm.bathrooms"
                                type="number"
                                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>

                    <!-- Row 4: Status & Flags -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <span class="text-xs font-bold text-slate-900 uppercase tracking-wider block">Moderation &amp; Publishing Controls</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Listing Status</label>
                                <select
                                    v-model="editForm.status"
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                >
                                    <option value="active">Active (Published)</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="inactive">Inactive / Archived</option>
                                </select>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer mt-4 sm:mt-0">
                                <input
                                    v-model="editForm.is_verified"
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                                />
                                <span class="text-xs font-bold text-slate-700">Verified by RealHem</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer mt-4 sm:mt-0">
                                <input
                                    v-model="editForm.is_featured"
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-amber-500 focus:ring-amber-500 border-slate-300"
                                />
                                <span class="text-xs font-bold text-slate-700">Featured Listing ★</span>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                        <textarea
                            v-model="editForm.description"
                            rows="4"
                            class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        ></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="isEditModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5"
                        >
                            <svg v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>{{ isSubmitting ? 'Saving...' : 'Save Changes' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. Delete Confirmation Modal -->
        <!-- ========================================== -->
        <div
            v-if="isDeleteModalOpen && propertyToDelete"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            @click.self="isDeleteModalOpen = false"
        >
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-black text-slate-900">Delete Property Listing?</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Are you sure you want to permanently delete <strong class="text-slate-800">"{{ propertyToDelete.title }}"</strong> (ID #{{ propertyToDelete.id }})? This action cannot be undone.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-2 pt-2">
                    <button
                        @click="isDeleteModalOpen = false; propertyToDelete = null"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete"
                        :disabled="isDeleting"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg v-if="isDeleting" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>{{ isDeleting ? 'Deleting...' : 'Yes, Delete Permanently' }}</span>
                    </button>
                </div>
            </div>
        </div>
    
</template>

<script setup lang="ts">
definePageMeta({
    layout: 'admin',
    middleware: 'admin-auth',
});

useSeoMeta({
    robots: 'noindex, nofollow',
});

import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';


const route = useRoute();

// State
const properties = ref([]);
const counts = ref({
    total: 0,
    pending: 0,
    active: 0,
    rejected: 0,
    inactive: 0,
    verified: 0,
    featured: 0,
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 1,
    to: 15,
});

const perPage = ref(15);

const visiblePages = computed(() => {
    const current = pagination.value.current_page || 1;
    const last = pagination.value.last_page || 1;
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }
    const pages = [];
    if (current <= 4) {
        pages.push(1, 2, 3, 4, 5, '...', last);
    } else if (current >= last - 3) {
        pages.push(1, '...', last - 4, last - 3, last - 2, last - 1, last);
    } else {
        pages.push(1, '...', current - 1, current, current + 1, '...', last);
    }
    return pages;
});

const isLoading = ref(false);
const activeTab = ref(route.query.tab || 'all');
const searchQuery = ref('');
const filterPropertyFor = ref('all');
const filterCity = ref('all');
const sortBy = ref('latest');

watch(() => route.query.tab, (newTab) => {
    activeTab.value = newTab || 'all';
    fetchProperties(1);
});

let searchDebounce = null;

// Modal States
const isReviewModalOpen = ref(false);
const selectedProperty = ref(null);
const activePhotoIndex = ref(0);

const isEditModalOpen = ref(false);
const isSubmitting = ref(false);
const editForm = ref({});

const isDeleteModalOpen = ref(false);
const propertyToDelete = ref(null);
const isDeleting = ref(false);

// Toast
const toast = ref({
    show: false,
    message: '',
    type: 'success',
});
let toastTimeout = null;

const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.value.show = false;
    }, 3500);
};

// Available Cities
const availableCities = [
    'Ahmedabad',
    'Delhi NCR',
    'Mumbai',
    'Bangalore',
    'Pune',
    'Hyderabad',
    'Chennai',
    'Kolkata',
];

// Moderation Tabs
const filterTabs = computed(() => [
    {
        key: 'all',
        label: 'All Listings',
        count: counts.value.total,
        badgeClass: 'bg-slate-100 text-slate-700',
        activeBadgeClass: 'bg-white/20 text-white',
    },
    {
        key: 'pending',
        label: 'Pending Review',
        count: counts.value.pending,
        badgeClass: 'bg-amber-100 text-amber-800',
        activeBadgeClass: 'bg-white/20 text-white',
    },
    {
        key: 'active',
        label: 'Active / Published',
        count: counts.value.active,
        badgeClass: 'bg-emerald-100 text-emerald-800',
        activeBadgeClass: 'bg-white/20 text-white',
    },
    {
        key: 'rejected',
        label: 'Rejected',
        count: counts.value.rejected,
        badgeClass: 'bg-rose-100 text-rose-800',
        activeBadgeClass: 'bg-white/20 text-white',
    },
    {
        key: 'inactive',
        label: 'Inactive / Sold',
        count: counts.value.inactive,
        badgeClass: 'bg-slate-100 text-slate-600',
        activeBadgeClass: 'bg-white/20 text-white',
    },
    {
        key: 'verified',
        label: 'Verified Only',
        count: counts.value.verified,
        badgeClass: 'bg-blue-100 text-blue-800',
        activeBadgeClass: 'bg-white/20 text-white',
    },
    {
        key: 'featured',
        label: 'Featured ★',
        count: counts.value.featured,
        badgeClass: 'bg-amber-100 text-amber-800',
        activeBadgeClass: 'bg-white/20 text-white',
    },
]);

const totalCount = computed(() => counts.value.total);

// Currency Formatter
const formatPriceWords = (val) => {
    if (!val || val <= 0) return 'Price on Request';
    const num = Number(val);
    if (num >= 10000000) {
        const cr = (num / 10000000).toFixed(2);
        return `₹ ${cr.replace(/\.00$/, '')} Cr`;
    }
    if (num >= 100000) {
        const lk = (num / 100000).toFixed(2);
        return `₹ ${lk.replace(/\.00$/, '')} L`;
    }
    return `₹ ${num.toLocaleString('en-IN')}`;
};

// Date Formatter
const formatDate = (dateStr) => {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

// Fetch Properties from Admin API
const fetchProperties = async (page = 1) => {
    isLoading.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    try {
        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', String(perPage.value));

        if (activeTab.value === 'pending') {
            params.set('status', 'pending');
        } else if (activeTab.value === 'active') {
            params.set('status', 'active');
        } else if (activeTab.value === 'rejected') {
            params.set('status', 'rejected');
        } else if (activeTab.value === 'inactive') {
            params.set('status', 'inactive');
        } else if (activeTab.value === 'verified') {
            params.set('is_verified', '1');
        } else if (activeTab.value === 'featured') {
            params.set('is_featured', '1');
        }

        if (searchQuery.value.trim()) {
            params.set('search', searchQuery.value.trim());
        }
        if (filterPropertyFor.value !== 'all') {
            params.set('property_for', filterPropertyFor.value);
        }
        if (filterCity.value !== 'all') {
            params.set('city', filterCity.value);
        }
        if (sortBy.value) {
            params.set('sort', sortBy.value);
        }

        const res = await fetch(`/api/admin/properties?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (res.ok) {
            const data = await res.json();
            properties.value = data.properties?.data || [];
            if (data.counts) {
                counts.value = data.counts;
            }
            if (data.properties) {
                pagination.value = {
                    current_page: data.properties.current_page || 1,
                    last_page: data.properties.last_page || 1,
                    total: data.properties.total || 0,
                    from: data.properties.from || 1,
                    to: data.properties.to || properties.value.length,
                };
            }
        } else if (res.status === 401 || res.status === 403) {
            showToast('Administrative privileges required.', 'error');
        }
    } catch (err) {
        console.error('Failed to fetch admin properties:', err);
        showToast('Error loading properties.', 'error');
    } finally {
        isLoading.value = false;
    }
};

const setTab = (tabKey) => {
    activeTab.value = tabKey;
    fetchProperties(1);
};

const onSearchInput = () => {
    if (searchDebounce) clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        fetchProperties(1);
    }, 350);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchProperties(page);
    }
};

// 1. Quick Approve Action
const quickApprove = async (property) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/properties/${property.id}/approve`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ verify: true }),
        });

        if (res.ok) {
            const data = await res.json();
            property.status = 'active';
            property.is_verified = true;
            showToast(`Listing #${property.id} approved and published!`);
            fetchProperties(pagination.value.current_page);
        } else {
            showToast('Failed to approve property.', 'error');
        }
    } catch {
        showToast('Network error while approving.', 'error');
    }
};

// 2. Quick Reject Action
const quickReject = async (property) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/properties/${property.id}/reject`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (res.ok) {
            property.status = 'rejected';
            showToast(`Listing #${property.id} marked as rejected.`);
            fetchProperties(pagination.value.current_page);
        } else {
            showToast('Failed to reject property.', 'error');
        }
    } catch {
        showToast('Network error while rejecting.', 'error');
    }
};

// 3. Toggle Verified Badge
const toggleVerified = async (property) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/properties/${property.id}/toggle-verified`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (res.ok) {
            property.is_verified = !property.is_verified;
            showToast(property.is_verified ? 'Property marked as Verified!' : 'Property unverified.');
            counts.value.verified += property.is_verified ? 1 : -1;
        }
    } catch {
        showToast('Failed to update verified status.', 'error');
    }
};

// 4. Toggle Featured Badge
const toggleFeatured = async (property) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/properties/${property.id}/toggle-featured`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (res.ok) {
            property.is_featured = !property.is_featured;
            showToast(property.is_featured ? 'Property marked as Featured ★' : 'Property removed from Featured.');
            counts.value.featured += property.is_featured ? 1 : -1;
        }
    } catch {
        showToast('Failed to update featured status.', 'error');
    }
};

// 5. Review Modal
const openReviewModal = async (property) => {
    selectedProperty.value = property;
    activePhotoIndex.value = 0;
    isReviewModalOpen.value = true;

    // Fetch fresh single property with complete payload
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/properties/${property.id}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (res.ok) {
            const data = await res.json();
            if (data.property) {
                selectedProperty.value = data.property;
            }
        }
    } catch (err) {
        console.error('Error loading property details:', err);
    }
};

const modalApprove = async () => {
    if (!selectedProperty.value) return;
    await quickApprove(selectedProperty.value);
};

const modalReject = async () => {
    if (!selectedProperty.value) return;
    await quickReject(selectedProperty.value);
};

// 6. Edit Modal
const openEditModal = (property) => {
    editForm.value = {
        id: property.id,
        title: property.title || '',
        description: property.description || '',
        property_for: property.property_for || 'Sell',
        property_type: property.property_type || 'Residential Apartment',
        project_name: property.project_name || '',
        city: property.city || '',
        state: property.state || '',
        locality: property.locality || '',
        sub_locality: property.sub_locality || '',
        pincode: property.pincode || '',
        expected_price: property.expected_price || 0,
        carpet_area: property.carpet_area || 0,
        bedrooms: property.bedrooms || 2,
        bathrooms: property.bathrooms || 2,
        status: property.status || 'active',
        is_verified: !!property.is_verified,
        is_featured: !!property.is_featured,
    };
    isEditModalOpen.value = true;
};

const submitEditForm = async () => {
    isSubmitting.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    try {
        const res = await fetch(`/api/admin/properties/${editForm.value.id}`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(editForm.value),
        });

        if (res.ok) {
            showToast(`Property #${editForm.value.id} updated successfully!`);
            isEditModalOpen.value = false;
            fetchProperties(pagination.value.current_page);
        } else {
            const data = await res.json();
            showToast(data.message || 'Failed to update property.', 'error');
        }
    } catch {
        showToast('Network error while updating property.', 'error');
    } finally {
        isSubmitting.value = false;
    }
};

// 7. Delete Modal
const openDeleteModal = (property) => {
    propertyToDelete.value = property;
    isDeleteModalOpen.value = true;
};

const confirmDelete = async () => {
    if (!propertyToDelete.value) return;
    isDeleting.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    try {
        const res = await fetch(`/api/admin/properties/${propertyToDelete.value.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (res.ok) {
            showToast(`Property #${propertyToDelete.value.id} deleted successfully.`);
            isDeleteModalOpen.value = false;
            propertyToDelete.value = null;
            fetchProperties(pagination.value.current_page);
        } else {
            showToast('Failed to delete property.', 'error');
        }
    } catch {
        showToast('Network error while deleting.', 'error');
    } finally {
        isDeleting.value = false;
    }
};

onMounted(() => {
    fetchProperties(1);
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
