<template>
    <div class="space-y-12 pb-20 bg-slate-50 text-slate-800">
        <!-- 99acres Signature Hero & Search Box -->
        <section
            class="relative z-20 text-white pt-10 pb-20 px-4 sm:px-6 lg:px-8 transition-all duration-300"
            :style="{ background: heroGradient }"
        >
            <!-- Festive Hero Animation (Occasion & Festival Effects) -->
            <FestiveHeroAnimation :type="festivalAnimation" />

            <!-- Decorative backdrop glow -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-32 -left-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 -right-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            </div>

            <div class="max-w-5xl mx-auto relative z-20 text-center space-y-6">
                <!-- Portal Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 border border-white/25 text-white text-xs font-semibold backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    India's Most Trusted Real Estate Marketplace
                </div>

                <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
                    Find Your Dream Home <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-amber-200 via-white to-cyan-200 bg-clip-text text-transparent">
                        Buy, Rent &amp; Commercial Properties
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-200 max-w-2xl mx-auto">
                    Search from 50,000+ verified listings, new builder township launches, and direct owner properties without brokerage.
                </p>

                <!-- Floating 99acres Style Search Card -->
                <div class="mt-8 bg-white text-slate-800 rounded-3xl shadow-2xl p-3.5 sm:p-7 border border-slate-100 text-left max-w-4xl mx-auto relative z-40">
                    <!-- Tab Pills: Buy | Rent | Commercial | Plots | PG/Co-living -->
                    <div class="flex items-center gap-1.5 sm:gap-2 border-b border-slate-100 pb-3 overflow-x-auto scrollbar-none">
                        <button
                            v-for="tab in searchTabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 shrink-0"
                            :style="activeTab === tab.id ? { backgroundColor: brandPrimaryColor, color: '#fff', boxShadow: '0 4px 12px ' + brandPrimaryColor + '35' } : {}"
                            :class="activeTab === tab.id ? '' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                        >
                            <span>{{ tab.label }}</span>
                            <span v-if="tab.badge" class="text-[9px] bg-amber-400 text-slate-900 px-1.5 py-0.2 rounded font-black">{{ tab.badge }}</span>
                        </button>
                    </div>

                    <!-- Search Input Bar -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mt-4 relative z-50">
                        <!-- Locality, City, Project or Builder Search Box -->
                        <div class="md:col-span-9 relative">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search City, Locality, Project or Builder</label>
                            <div class="relative">
                                <input
                                    v-model="searchForm.keyword"
                                    type="text"
                                    placeholder="Search City, Locality, Project or Title..."
                                    @input="onKeywordInput"
                                    @focus="onKeywordFocus"
                                    @blur="closeSuggestionsWithDelay"
                                    @keydown.down.prevent="navigateSuggestions(1)"
                                    @keydown.up.prevent="navigateSuggestions(-1)"
                                    @keydown.enter="selectActiveSuggestionOrSubmit"
                                    @keydown.esc="isSuggestionsOpen = false"
                                    class="w-full bg-slate-50 border border-slate-200 text-xs sm:text-sm font-semibold py-2.5 pl-9 pr-20 rounded-xl outline-none focus:border-blue-500 focus:bg-white transition"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <Search class="w-4 h-4" />
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                                    <!-- Mic Voice Search Icon -->
                                    <button
                                        type="button"
                                        @click="openVoiceSearchHero"
                                        title="Search by Voice"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg cursor-pointer transition flex items-center justify-center"
                                        :style="{ ':hover': { color: brandPrimaryColor } }"
                                    >
                                        <Mic class="w-4 h-4" />
                                    </button>
                                    <!-- Near Me GPS Icon with Location Detection -->
                                    <button
                                        type="button"
                                        @click="useCurrentLocation"
                                        :disabled="isDetectingLocation"
                                        :title="isDetectingLocation ? 'Detecting location...' : 'Detect My Location'"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg cursor-pointer transition flex items-center justify-center"
                                    >
                                        <svg v-if="isDetectingLocation" class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <MapPin v-else class="w-4 h-4" />
                                    </button>
                                </div>

                                <!-- Autocomplete Suggestions Dropdown directly under input -->
                                <div
                                    v-if="isSuggestionsOpen && (suggestions.length > 0 || isSuggestionsLoading)"
                                    class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden z-[100] text-left animate-fadeIn max-h-[360px] overflow-y-auto divide-y divide-slate-100"
                                >
                                    <div v-if="isSuggestionsLoading" class="p-3 text-xs text-slate-500 flex items-center gap-2 bg-slate-50">
                                        <svg class="w-3.5 h-3.5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span>Finding matching cities, apartments &amp; properties...</span>
                                    </div>

                                    <button
                                        v-for="(item, idx) in suggestions"
                                        :key="idx"
                                        type="button"
                                        @mousedown.prevent="handleSelectSuggestion(item)"
                                        @mouseenter="activeSuggestionIndex = idx"
                                        class="w-full px-4 py-2.5 flex items-center justify-between text-left transition cursor-pointer"
                                        :class="activeSuggestionIndex === idx ? 'bg-blue-50/90 text-blue-950' : 'hover:bg-slate-50 text-slate-800'"
                                    >
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                :class="item.type === 'city' ? 'bg-indigo-50 text-indigo-600' : item.type === 'project' ? 'bg-emerald-50 text-emerald-600' : item.type === 'property' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600'"
                                            >
                                                <Building2 v-if="item.type === 'project' || item.icon === 'Building2'" class="w-3.5 h-3.5" />
                                                <Home v-else-if="item.type === 'property' || item.icon === 'Home'" class="w-3.5 h-3.5" />
                                                <MapPin v-else class="w-3.5 h-3.5" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold truncate flex items-center gap-2">
                                                    <span class="truncate">{{ item.title }}</span>
                                                    <span
                                                        class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-md shrink-0"
                                                        :class="item.type === 'city' ? 'bg-indigo-100 text-indigo-700' : item.type === 'project' ? 'bg-emerald-100 text-emerald-700' : item.type === 'property' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700'"
                                                    >
                                                        {{ item.category }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 truncate mt-0.5">
                                                    {{ item.subtitle }}
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-blue-600 font-bold shrink-0 ml-2">Select &rarr;</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Big Search Button -->
                        <div class="md:col-span-3 flex items-end">
                            <button
                                @click="handleSearch"
                                class="w-full py-2.5 px-4 rounded-xl text-white font-extrabold text-xs sm:text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                :style="{ backgroundColor: brandPrimaryColor, boxShadow: '0 4px 12px ' + brandPrimaryColor + '40' }"
                            >
                                <span>Search</span>
                                <ArrowRight class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Granular BHK and Budget Bar -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 text-xs relative z-10">
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                            <span class="text-slate-400 text-[11px] font-bold uppercase shrink-0">BHK:</span>
                            <button
                                v-for="bhk in ['1 BHK', '2 BHK', '3 BHK', '4+ BHK']"
                                :key="bhk"
                                @click="toggleBhk(bhk)"
                                class="px-2.5 sm:px-3 py-1 rounded-lg text-xs font-semibold border transition cursor-pointer"
                                :style="selectedBhks.includes(bhk) ? { backgroundColor: brandPrimaryColor + '15', borderColor: brandPrimaryColor, color: brandPrimaryColor, fontWeight: '700' } : {}"
                                :class="selectedBhks.includes(bhk) ? '' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                {{ bhk }}
                            </button>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400 text-[11px] font-bold uppercase shrink-0">Budget:</span>
                                <select
                                    v-model="searchForm.budget"
                                    class="bg-slate-50 border border-slate-200 text-[11px] font-semibold py-1 px-2 rounded-lg outline-none cursor-pointer"
                                >
                                    <option value="">Any Budget</option>
                                    <option value="under_50l">Under ₹50 Lac</option>
                                    <option value="50l_1cr">₹50 Lac - ₹1 Cr</option>
                                    <option value="1cr_2cr">₹1 Cr - ₹2 Cr</option>
                                    <option value="above_2cr">₹2 Cr+</option>
                                </select>
                            </div>

                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-600 select-none shrink-0">
                                <input type="checkbox" v-model="verifiedOnly" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-[11px] font-semibold">Verified Only</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Real Estate Services Ribbon (99acres style) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-3 sm:p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3">
                <div
                    v-for="(service, sIdx) in quickServices"
                    :key="service.title"
                    class="flex items-center gap-2.5 sm:gap-3 p-2 rounded-xl hover:bg-slate-50 transition cursor-pointer"
                    :class="sIdx === 4 ? 'col-span-2 sm:col-span-1' : ''"
                >
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shrink-0"
                        :style="{ backgroundColor: brandPrimaryColor + '18', color: brandPrimaryColor }"
                    >
                        <component :is="service.icon" class="w-4 h-4 sm:w-5 sm:h-5" />
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-slate-800 block truncate">{{ service.title }}</span>
                        <span class="text-[10px] text-slate-500 block truncate">{{ service.subtitle }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Activity Modules on Homepage: Recent Searches | Viewed | Shortlisted -->
        <section
            v-if="searchCount > 0 || viewedCount > 0 || shortlistCount > 0 || contactedCount > 0"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4"
        >
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-4 sm:p-6 space-y-5">
                <!-- Tab Bar matching 99acres style from screenshot -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-0 gap-4 flex-wrap">
                    <div class="flex items-center gap-6 sm:gap-10 overflow-x-auto scrollbar-none">
                        <!-- Recent Searches -->
                        <button
                            v-if="searchCount > 0"
                            type="button"
                            @click="homeActivityTab = 'searches'"
                            class="pb-3 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                            :class="homeActivityTab === 'searches' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-500 hover:text-slate-800 border-b-[3px] border-transparent'"
                        >
                            {{ searchCount }} Recent Searches
                        </button>

                        <!-- Viewed -->
                        <button
                            v-if="viewedCount > 0"
                            type="button"
                            @click="homeActivityTab = 'viewed'"
                            class="pb-3 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                            :class="homeActivityTab === 'viewed' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-500 hover:text-slate-800 border-b-[3px] border-transparent'"
                        >
                            {{ viewedCount }} Viewed
                        </button>

                        <!-- Shortlisted -->
                        <button
                            v-if="shortlistCount > 0"
                            type="button"
                            @click="homeActivityTab = 'shortlisted'"
                            class="pb-3 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                            :class="homeActivityTab === 'shortlisted' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-500 hover:text-slate-800 border-b-[3px] border-transparent'"
                        >
                            {{ shortlistCount }} Shortlisted
                        </button>

                        <!-- Contacted -->
                        <button
                            v-if="contactedCount > 0"
                            type="button"
                            @click="homeActivityTab = 'contacted'"
                            class="pb-3 text-sm sm:text-base font-bold whitespace-nowrap transition cursor-pointer relative"
                            :class="homeActivityTab === 'contacted' ? 'text-[#091e42] border-b-[3px] border-[#0066cc]' : 'text-slate-500 hover:text-slate-800 border-b-[3px] border-transparent'"
                        >
                            {{ contactedCount }} Contacted
                        </button>
                    </div>

                    <router-link
                        :to="`/recent-activity?tab=${homeActivityTab}`"
                        class="pb-3 text-xs font-bold hover:underline flex items-center gap-1 shrink-0"
                        :style="{ color: brandPrimaryColor }"
                    >
                        <span>View All &rarr;</span>
                    </router-link>
                </div>

                <!-- 1. Recent Searches Tab Content -->
                <div v-if="homeActivityTab === 'searches'">
                    <div v-if="recentSearches.length === 0" class="py-10 text-center text-xs text-slate-500 font-semibold">
                        No recent searches saved yet. Search any property to see your search history here.
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div
                            v-for="(s, sIdx) in recentSearches.slice(0, 4)"
                            :key="s.id || sIdx"
                            class="bg-slate-50/80 hover:bg-white rounded-2xl border border-slate-200/90 p-4 transition hover:shadow-md flex flex-col justify-between"
                        >
                            <div class="space-y-2">
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-blue-100 text-blue-700">
                                    {{ s.metadata?.property_for || 'BUY' }} SEARCH
                                </span>
                                <h4 class="text-xs font-black text-slate-900 line-clamp-2">
                                    {{ s.metadata?.title || s.metadata?.keyword || 'Property Search' }}
                                </h4>
                                <div class="flex items-center gap-1.5 flex-wrap text-[10px] text-slate-600">
                                    <span v-if="s.metadata?.city" class="bg-white px-2 py-0.5 rounded border border-slate-200 flex items-center gap-1">
                                        <MapPin class="w-3 h-3 text-slate-400" />
                                        <span>{{ s.metadata?.city }}</span>
                                    </span>
                                    <span v-if="s.metadata?.bhk" class="bg-white px-2 py-0.5 rounded border border-slate-200 flex items-center gap-1">
                                        <BedDouble class="w-3 h-3 text-slate-400" />
                                        <span>{{ s.metadata?.bhk }} BHK</span>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-end">
                                <button
                                    type="button"
                                    @click="replaySearch(s)"
                                    class="text-xs font-bold hover:underline flex items-center gap-1 cursor-pointer"
                                    :style="{ color: brandPrimaryColor }"
                                >
                                    <span>Search Again</span>
                                    <span>&rarr;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Viewed Properties Tab Content -->
                <div v-else-if="homeActivityTab === 'viewed'">
                    <div v-if="recentActivities.length === 0" class="py-10 text-center text-xs text-slate-500 font-semibold">
                        No viewed properties yet. Browse properties to see them here.
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <router-link
                            v-for="act in recentActivities.slice(0, 4)"
                            :key="act.id || act.property_id"
                            :to="'/property/' + (act.property?.slug || act.metadata?.slug || act.property_id)"
                            class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition flex flex-col overflow-hidden group cursor-pointer relative"
                        >
                            <div
                                class="h-36 bg-slate-900 relative flex items-center justify-center overflow-hidden cursor-pointer"
                                @click.stop.prevent="openImageGallery(act.property || act.metadata)"
                                title="Click to view media"
                            >
                                <img
                                    v-if="getItemPhoto(act)"
                                    :src="getItemPhoto(act)"
                                    :alt="act.property?.title || act.metadata?.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
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
                                        <div class="w-8 h-8 rounded-full bg-purple-600/90 text-white flex items-center justify-center shadow-md">
                                            <Play class="w-3.5 h-3.5 fill-current translate-x-0.5" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center text-slate-500 gap-1.5">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 text-slate-400 flex items-center justify-center">
                                        <Camera class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <span class="text-[9px] font-semibold text-slate-400">No media</span>
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-30" @click.stop.prevent>
                                    <ShortlistButton
                                        :property="act.property || { id: act.property_id, ...act.metadata }"
                                        :propertyId="act.property_id || act.property?.id"
                                        size="sm"
                                    />
                                </div>

                                <span v-if="act.property?.property_for || act.metadata?.property_for" class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-xs text-white text-[9px] font-bold">
                                    For {{ act.property?.property_for || act.metadata?.property_for }}
                                </span>

                                <!-- Media Count Badge (Bottom Right) -->
                                <button
                                    v-if="getItemPhotoCount(act) > 0"
                                    type="button"
                                    @click.stop.prevent="openImageGallery(act.property || act.metadata)"
                                    class="absolute bottom-2 right-2 bg-black/60 hover:bg-black/80 backdrop-blur-xs text-white text-[9px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs"
                                    title="View photo gallery"
                                >
                                    <Camera class="w-3 h-3" />
                                    <span>{{ getItemPhotoCount(act) }}</span>
                                </button>
                                <button
                                    v-else-if="getItemVideoCount(act) > 0"
                                    type="button"
                                    @click.stop.prevent="openImageGallery(act.property || act.metadata)"
                                    class="absolute bottom-2 right-2 bg-purple-600/90 hover:bg-purple-600 backdrop-blur-xs text-white text-[9px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs"
                                    title="View video tour"
                                >
                                    <Video class="w-3 h-3" />
                                    <span>{{ getItemVideoCount(act) }}</span>
                                </button>
                            </div>

                            <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs line-clamp-1 group-hover:text-blue-600 transition">
                                        {{ act.property?.title || act.metadata?.title || 'Property Listing' }}
                                    </h4>
                                    <p class="text-[10px] text-slate-500 mt-0.5 truncate flex items-center gap-1">
                                        <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                        <span>{{ act.property?.locality || act.metadata?.locality || '' }}{{ (act.property?.city || act.metadata?.city) ? ', ' + (act.property?.city || act.metadata?.city) : '' }}</span>
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-black" :style="{ color: brandPrimaryColor }">
                                        {{ (act.property?.expected_price || act.metadata?.expected_price) ? formatPriceWords(act.property?.expected_price || act.metadata?.expected_price) : 'Price on Request' }}
                                    </span>
                                    <span class="text-[11px] font-bold text-blue-600 group-hover:underline">Revisit &rarr;</span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>

                <!-- 3. Shortlisted (Favourite) Tab Content -->
                <div v-else-if="homeActivityTab === 'shortlisted'">
                    <div v-if="shortlistedProperties.length === 0" class="py-10 text-center text-xs text-slate-500 font-semibold space-y-2">
                        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto shadow-xs">
                            <Heart class="w-6 h-6 fill-current" />
                        </div>
                        <p>No favourite properties saved yet. Click the heart icon on any property to save it here.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <router-link
                            v-for="item in shortlistedProperties.slice(0, 4)"
                            :key="item.id || item.property_id"
                            :to="'/property/' + (item.property?.slug || item.metadata?.slug || item.property_id)"
                            class="bg-white rounded-2xl border border-rose-200/90 shadow-2xs hover:shadow-md transition flex flex-col overflow-hidden group cursor-pointer relative"
                        >
                            <div
                                class="h-36 bg-slate-900 relative flex items-center justify-center overflow-hidden cursor-pointer"
                                @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                title="Click to view media"
                            >
                                <img
                                    v-if="getItemPhoto(item)"
                                    :src="getItemPhoto(item)"
                                    :alt="item.property?.title || item.metadata?.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
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
                                <div v-else class="flex flex-col items-center justify-center text-slate-500 gap-1.5">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 text-slate-400 flex items-center justify-center">
                                        <Camera class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <span class="text-[9px] font-semibold text-slate-400">No media</span>
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-30" @click.stop.prevent>
                                    <ShortlistButton
                                        :property="item.property || { id: item.property_id, ...item.metadata }"
                                        :propertyId="item.property_id || item.property?.id"
                                        size="sm"
                                    />
                                </div>

                                <!-- Media Count Badge (Bottom Right) -->
                                <button
                                    v-if="getItemPhotoCount(item) > 0"
                                    type="button"
                                    @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                    class="absolute bottom-2 right-2 bg-black/60 hover:bg-black/80 backdrop-blur-xs text-white text-[9px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs"
                                    title="View photo gallery"
                                >
                                    <Camera class="w-3 h-3" />
                                    <span>{{ getItemPhotoCount(item) }}</span>
                                </button>
                                <button
                                    v-else-if="getItemVideoCount(item) > 0"
                                    type="button"
                                    @click.stop.prevent="openImageGallery(item.property || item.metadata)"
                                    class="absolute bottom-2 right-2 bg-purple-600/90 hover:bg-purple-600 backdrop-blur-xs text-white text-[9px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer z-20 shadow-xs"
                                    title="View video tour"
                                >
                                    <Video class="w-3 h-3" />
                                    <span>{{ getItemVideoCount(item) }}</span>
                                </button>
                            </div>

                            <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs line-clamp-1 group-hover:text-rose-600 transition">
                                        {{ item.property?.title || item.metadata?.title || 'Shortlisted Property' }}
                                    </h4>
                                    <p class="text-[10px] text-slate-500 mt-0.5 truncate flex items-center gap-1">
                                        <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                        <span>{{ item.property?.locality || item.metadata?.locality || '' }}{{ (item.property?.city || item.metadata?.city) ? ', ' + (item.property?.city || item.metadata?.city) : '' }}</span>
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-black text-rose-600">
                                        {{ (item.property?.expected_price || item.metadata?.expected_price) ? formatPriceWords(item.property?.expected_price || item.metadata?.expected_price) : 'Price on Request' }}
                                    </span>
                                    <span class="text-[11px] font-bold text-rose-600 group-hover:underline">View &rarr;</span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>

                <!-- 4. Contacted Tab Content -->
                <div v-else-if="homeActivityTab === 'contacted'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <router-link
                            v-for="c in contactedProperties.slice(0, 4)"
                            :key="c.id || c.property_id"
                            :to="'/property/' + (c.property?.slug || c.metadata?.slug || c.property_id)"
                            class="bg-white rounded-2xl border border-emerald-200/90 shadow-2xs hover:shadow-md transition flex flex-col overflow-hidden group cursor-pointer"
                        >
                            <div class="p-4 space-y-2">
                                <span class="text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded">
                                    Contacted
                                </span>
                                <h4 class="font-bold text-slate-900 text-xs line-clamp-1 group-hover:text-emerald-700">
                                    {{ c.property?.title || c.metadata?.title || 'Contacted Property' }}
                                </h4>
                                <span class="text-xs font-black text-emerald-700 block">
                                    {{ (c.property?.expected_price || c.metadata?.expected_price) ? formatPriceWords(c.property?.expected_price || c.metadata?.expected_price) : 'Price on Request' }}
                                </span>
                            </div>
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Handpicked Recommendations for You (as shown in 99acres) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Handpicked Properties For You</span>
                        <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Owner &amp; Verified</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Top-rated properties with verified documents &amp; clear titles</p>
                </div>
                <router-link to="/listings" class="text-xs font-bold hover:underline" :style="{ color: brandPrimaryColor }">
                    View All &rarr;
                </router-link>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <router-link
                    v-for="prop in handpickedListings"
                    :key="prop.id"
                    :to="prop.slug ? `/property/${prop.slug}` : `/property/${prop.id}`"
                    class="bg-white rounded-2xl border shadow-xs hover:shadow-lg transition flex flex-col overflow-hidden group cursor-pointer relative"
                    :class="prop.is_featured
                        ? 'border-amber-300 ring-1 ring-amber-400/25 bg-gradient-to-b from-amber-50/15 via-white to-white'
                        : 'border-slate-200/80'"
                >
                    <!-- Property Image / Header -->
                    <div
                        class="h-44 bg-slate-800 relative flex items-center justify-center overflow-hidden cursor-pointer"
                        @click.stop.prevent="openImageGallery(prop)"
                        title="Click to view photos"
                    >
                        <img
                            v-if="prop.photos && prop.photos.length"
                            :src="prop.photos[0]"
                            :alt="prop.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        />
                        <div
                            v-else-if="prop.videos && prop.videos.length"
                            class="w-full h-full relative flex items-center justify-center bg-slate-950"
                        >
                            <video
                                :src="prop.videos[0] + '#t=0.5'"
                                preload="metadata"
                                muted
                                playsinline
                                class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-300 pointer-events-none"
                            ></video>
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                <div class="w-8 h-8 rounded-full bg-purple-600/90 text-white flex items-center justify-center shadow-md">
                                    <svg class="w-3.5 h-3.5 fill-current translate-x-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 gap-1.5">
                            <div class="w-10 h-10 rounded-xl bg-slate-800/80 text-slate-400 flex items-center justify-center">
                                <Camera class="w-5 h-5 text-slate-400" />
                            </div>
                            <span class="text-[9px] font-semibold text-slate-400">No media</span>
                        </div>

                        <!-- Badge Overlay -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap z-20">
                            <span
                                v-if="prop.is_featured"
                                class="group/badge h-6 px-1.5 hover:px-2.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-[9px] uppercase tracking-wider shadow-sm flex items-center transition-all duration-300 ease-out hover:scale-105 hover:shadow-md cursor-pointer"
                                title="Featured Property"
                                @click.stop.prevent
                            >
                                <Star class="w-3 h-3 fill-current shrink-0" />
                                <span class="max-w-0 opacity-0 group-hover/badge:max-w-24 group-hover/badge:opacity-100 group-hover/badge:pl-1 overflow-hidden whitespace-nowrap transition-all duration-300 ease-out">
                                    FEATURED
                                </span>
                            </span>
                            <span
                                v-if="prop.is_verified"
                                class="group/badge h-6 px-1.5 hover:px-2.5 rounded-full bg-blue-600 text-white font-black text-[9px] uppercase tracking-wider shadow-sm flex items-center transition-all duration-300 ease-out hover:scale-105 hover:shadow-md cursor-pointer"
                                title="Verified Property"
                                @click.stop.prevent
                            >
                                <Check class="w-3 h-3 text-white shrink-0" :stroke-width="3" />
                                <span class="max-w-0 opacity-0 group-hover/badge:max-w-24 group-hover/badge:opacity-100 group-hover/badge:pl-1 overflow-hidden whitespace-nowrap transition-all duration-300 ease-out">
                                    VERIFIED
                                </span>
                            </span>
                            <span
                                v-if="!prop.is_featured && !prop.is_verified && prop.badge"
                                class="group/badge h-6 px-1.5 hover:px-2.5 rounded-full text-white font-extrabold text-[9px] uppercase shadow-xs flex items-center transition-all duration-300 ease-out hover:scale-105 hover:shadow-md cursor-pointer"
                                :style="{ backgroundColor: brandPrimaryColor }"
                                :title="prop.badge"
                                @click.stop.prevent
                            >
                                <CheckCircle2 class="w-3 h-3 fill-current shrink-0" />
                                <span class="max-w-0 opacity-0 group-hover/badge:max-w-24 group-hover/badge:opacity-100 group-hover/badge:pl-1 overflow-hidden whitespace-nowrap transition-all duration-300 ease-out">
                                    {{ prop.badge }}
                                </span>
                            </span>
                        </div>

                        <!-- Custom Heart Shortlist Button (Top Right Corner) -->
                        <div class="absolute top-3 right-3 z-30" @click.stop.prevent>
                            <ShortlistButton
                                :property="{ id: prop.id, title: prop.title, slug: prop.slug, property_type: prop.category, expected_price: prop.expected_price || prop.price, photos: prop.photos, locality: prop.locality, city: prop.city, carpet_area: prop.area }"
                                :propertyId="prop.id"
                                size="sm"
                            />
                        </div>

                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/85 via-slate-950/30 to-transparent p-3 pt-6 flex items-end justify-between z-10">
                            <span class="text-white font-extrabold text-sm">
                                <template v-if="isResidentialType(prop.category) && prop.bedrooms > 0">
                                    {{ prop.bedrooms }} BHK
                                </template>
                                <template v-else>
                                    {{ prop.category }}
                                </template>
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-300 text-[11px] font-medium">{{ prop.area }} sq.ft</span>
                                <button
                                    v-if="((prop.photos?.length || 0) + (prop.videos?.length || 0)) > 0"
                                    type="button"
                                    @click.stop.prevent="openImageGallery(prop)"
                                    class="bg-black/70 hover:bg-black/90 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 transition-all duration-200 hover:scale-105 cursor-pointer shadow-xs border border-white/10"
                                    :title="(!prop.photos || prop.photos.length === 0) ? 'View video tour' : 'View media gallery'"
                                >
                                    <Video v-if="!prop.photos || prop.photos.length === 0" class="w-3 h-3 text-purple-300" />
                                    <Camera v-else class="w-3 h-3" />
                                    <span>{{ (prop.photos?.length || 0) + (prop.videos?.length || 0) }}</span>
                                    <Video v-if="prop.videos && prop.videos.length > 0 && prop.photos && prop.photos.length > 0" class="w-3 h-3 text-purple-300" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h3 class="font-extrabold text-slate-900 text-sm line-clamp-1 group-hover:text-blue-600 transition flex-1">
                                    {{ prop.title }}
                                </h3>
                                <span v-if="prop.is_featured" class="inline-flex items-center gap-1 text-[9px] font-black text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 shadow-2xs">
                                    <Star class="w-3 h-3 fill-current" />
                                    <span>Featured</span>
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 flex items-center justify-between gap-1">
                                <span class="flex items-center gap-1 truncate">
                                    <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                    <span class="truncate">{{ prop.locality }}, {{ prop.city }}</span>
                                </span>
                                <span v-if="prop.is_verified" class="inline-flex items-center gap-0.5 text-[9px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 shrink-0">
                                    <Check class="w-3 h-3" />
                                    <span>Verified</span>
                                </span>
                            </p>
                        </div>

                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-base font-black tracking-tight" :style="{ color: brandPrimaryColor }">{{ prop.price }}</span>
                                <span v-if="prop.rate" class="block text-[10px] text-slate-400">{{ prop.rate }}</span>
                            </div>
                            <div
                                class="px-3 py-1.5 rounded-xl font-bold text-xs shadow-xs transition group-hover:opacity-90 flex items-center gap-1"
                                :style="{ backgroundColor: brandPrimaryColor + '18', color: brandPrimaryColor }"
                            >
                                <span>View Details</span>
                                <span class="text-xs">&rarr;</span>
                            </div>
                        </div>
                    </div>
                </router-link>
            </div>
        </section>

        <!-- Explore by Property Type -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Explore by Property Type</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div
                    v-for="cat in propertyCategories"
                    :key="cat.name"
                    @click="filterByCategory(cat.type)"
                    class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition cursor-pointer text-center group"
                >
                    <div
                        class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform"
                        :style="{ backgroundColor: brandPrimaryColor + '18', color: brandPrimaryColor }"
                    >
                        <component :is="cat.icon" class="w-7 h-7" />
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition">{{ cat.name }}</h3>
                    <p class="text-[11px] text-slate-500 mt-1">{{ cat.count }} listings</p>
                </div>
            </div>
        </section>

        <!-- Interactive Home Loan EMI Calculator Widget -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md p-6 sm:p-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <!-- Left: Sliders -->
                    <div class="lg:col-span-7 space-y-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider" :style="{ color: brandPrimaryColor }">Financial Tools</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                                Home Loan EMI Calculator
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Plan your budget accurately with real-time bank interest calculations</p>
                        </div>

                        <!-- Loan Amount Slider -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600">Loan Amount:</span>
                                <span class="font-black text-slate-900 text-sm">{{ formatPriceWords(loanAmount) }}</span>
                            </div>
                            <input
                                type="range"
                                min="500000"
                                max="20000000"
                                step="100000"
                                v-model.number="loanAmount"
                                class="w-full cursor-pointer"
                                :style="{ accentColor: brandPrimaryColor }"
                            />
                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold">
                                <span>₹ 5 Lakh</span>
                                <span>₹ 1 Cr</span>
                                <span>₹ 2 Cr</span>
                            </div>
                        </div>

                        <!-- Interest Rate Slider -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600">Interest Rate:</span>
                                <span class="font-black text-slate-900 text-sm">{{ interestRate }}% p.a.</span>
                            </div>
                            <input
                                type="range"
                                min="7"
                                max="13"
                                step="0.1"
                                v-model.number="interestRate"
                                class="w-full cursor-pointer"
                                :style="{ accentColor: brandPrimaryColor }"
                            />
                        </div>

                        <!-- Loan Tenure Slider -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600">Loan Tenure:</span>
                                <span class="font-black text-slate-900 text-sm">{{ tenureYears }} Years</span>
                            </div>
                            <input
                                type="range"
                                min="5"
                                max="30"
                                step="1"
                                v-model.number="tenureYears"
                                class="w-full cursor-pointer"
                                :style="{ accentColor: brandPrimaryColor }"
                            />
                        </div>
                    </div>

                    <!-- Right: EMI Output Box -->
                    <div class="lg:col-span-5 bg-slate-900 text-white rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Estimated Monthly EMI</span>
                            <div class="text-3xl sm:text-4xl font-black mt-1" :style="{ color: brandAccentColor || '#fbbf24' }">
                                ₹ {{ calculatedEmi.toLocaleString('en-IN') }}
                                <span class="text-xs font-normal text-slate-400">/ month</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800 text-xs">
                            <div>
                                <span class="text-slate-400 block">Principal Amount</span>
                                <span class="font-bold text-slate-200">{{ formatPriceWords(loanAmount) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Total Interest</span>
                                <span class="font-bold text-slate-200">{{ formatPriceWords(totalInterest) }}</span>
                            </div>
                        </div>

                        <button
                            @click="applyHomeLoan"
                            class="w-full mt-4 py-3 rounded-xl text-white font-bold text-xs transition cursor-pointer shadow-md hover:opacity-90"
                            :style="{ backgroundColor: brandPrimaryColor }"
                        >
                            Apply for Lowest Rate Home Loan &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Big "Post Property Free" Banner (Dynamic Theme & Branding) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="text-white rounded-3xl p-8 sm:p-12 shadow-xl border border-white/10 flex flex-col md:flex-row items-center justify-between gap-6 transition-all duration-300"
                :style="{ background: heroGradient }"
            >
                <div class="space-y-3 max-w-xl">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider shadow-xs"
                        :style="{ backgroundColor: brandAccentColor || '#ff6b35', color: '#fff' }"
                    >
                        Post Free Ad
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-snug text-white">
                        Sell or Rent your property faster with {{ companyName }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-200">
                        Over 5 Lakh+ genuine buyers visit every week. Get direct phone inquiries without paying any brokerage.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                    <router-link
                        to="/post-property"
                        class="px-8 py-4 rounded-2xl font-black text-sm shadow-xl transition cursor-pointer hover:opacity-95"
                        :style="{ backgroundColor: brandAccentColor || '#ff6b35', color: '#fff', boxShadow: '0 8px 24px ' + (brandAccentColor || '#ff6b35') + '40' }"
                    >
                        Post Property FREE &rarr;
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Image Library Lightbox Modal -->
        <ImageGalleryModal
            v-model:isOpen="isGalleryOpen"
            :property="activeGalleryProperty"
            :initialIndex="activeGalleryIndex"
            @close="isGalleryOpen = false"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
    Search,
    Mic,
    MapPin,
    Building2,
    Home,
    LandPlot,
    Store,
    Camera,
    Video,
    Play,
    Heart,
    Star,
    Check,
    CheckCircle2,
    ArrowRight,
    Landmark,
    Scale,
    Truck,
    Armchair,
    TrendingUp,
    BedDouble,
} from 'lucide-vue-next';
import FestiveHeroAnimation from '~/components/FestiveHeroAnimation.vue';
import { isResidentialType } from '~/utils/propertyConstants';

const router = useRouter();

// Image Gallery Modal State
const isGalleryOpen = ref(false);
const activeGalleryProperty = ref(null);
const activeGalleryIndex = ref(0);

const openImageGallery = (prop, index = 0) => {
    if (!prop) return;
    activeGalleryProperty.value = prop;
    activeGalleryIndex.value = index;
    isGalleryOpen.value = true;
};

const getItemPhoto = (item) => {
    const photo = item?.property?.photos?.[0] || item?.metadata?.photo;
    if (photo && typeof photo === 'string') {
        return photo;
    }
    return null;
};

const getItemVideo = (item) => {
    const video = item?.property?.videos?.[0] || item?.metadata?.videos?.[0];
    if (video && typeof video === 'string') {
        return video;
    }
    return null;
};

const getItemPhotoCount = (item) => {
    if (Array.isArray(item?.property?.photos) && item.property.photos.length > 0) {
        return item.property.photos.length;
    }
    if (item?.metadata?.photo && typeof item.metadata.photo === 'string') {
        return 1;
    }
    return 0;
};

const getItemVideoCount = (item) => {
    if (Array.isArray(item?.property?.videos) && item.property.videos.length > 0) {
        return item.property.videos.length;
    }
    if (Array.isArray(item?.metadata?.videos) && item.metadata.videos.length > 0) {
        return item.metadata.videos.length;
    }
    return 0;
};
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
    logSearch,
    loadAllActivities,
} = useUserActivity();

const homeActivityTab = ref('viewed');

// Keep homeActivityTab synchronized to a tab that actually has records (> 0)
watch(
    [searchCount, viewedCount, shortlistCount, contactedCount],
    ([sc, vc, slc, cc]) => {
        const counts = {
            searches: sc || 0,
            viewed: vc || 0,
            shortlisted: slc || 0,
            contacted: cc || 0,
        };
        // If current tab is empty or has 0 records, switch to first visible tab
        if (!counts[homeActivityTab.value] || counts[homeActivityTab.value] === 0) {
            if (vc > 0) homeActivityTab.value = 'viewed';
            else if (slc > 0) homeActivityTab.value = 'shortlisted';
            else if (sc > 0) homeActivityTab.value = 'searches';
            else if (cc > 0) homeActivityTab.value = 'contacted';
        }
    },
    { immediate: true }
);
const { openVoiceModal, parseVoiceQuery } = useVoiceSearch();
const { currentCity, isDetectingLocation, detectLocation, setCity } = useUserLocation();
const {
    companyName,
    companyTagline,
    brandPrimaryColor,
    brandSecondaryColor,
    brandAccentColor,
    heroGradient,
    bannerEnabled,
    bannerText,
    bannerBadge,
    bannerLink,
    festivalAnimation,
} = useCompanyBranding();

useSeoMeta({
    title: 'Real Estate Architecture & Property Discovery Portal',
    ogTitle: 'RealHem - Buy, Rent, PG, Commercial & Builder Projects',
    description: 'Find verified properties for sale and rent across India. Connect with direct property owners and builders with zero brokerage.',
    ogDescription: 'Find verified properties for sale and rent across India. Connect with direct property owners and builders with zero brokerage.',
});
const { suggestions, isLoading: isSuggestionsLoading, fetchSuggestions, clearSuggestions } = useSearchSuggestions();

const activeTab = ref('buy');
const selectedBhks = ref([]);
const verifiedOnly = ref(false);
const isSuggestionsOpen = ref(false);
const activeSuggestionIndex = ref(-1);

const searchForm = ref({
    city: '',
    keyword: '',
    budget: '',
});

const onKeywordInput = () => {
    activeSuggestionIndex.value = -1;
    if (searchForm.value.keyword.trim().length > 0) {
        isSuggestionsOpen.value = true;
        fetchSuggestions(searchForm.value.keyword, '');
    } else {
        isSuggestionsOpen.value = false;
        clearSuggestions();
    }
};

const onKeywordFocus = () => {
    if (searchForm.value.keyword.trim().length > 0) {
        isSuggestionsOpen.value = true;
        fetchSuggestions(searchForm.value.keyword, '');
    }
};

const closeSuggestionsWithDelay = () => {
    setTimeout(() => {
        isSuggestionsOpen.value = false;
    }, 200);
};

const navigateSuggestions = (dir) => {
    if (!suggestions.value.length) return;
    activeSuggestionIndex.value = (activeSuggestionIndex.value + dir + suggestions.value.length) % suggestions.value.length;
};

const selectActiveSuggestionOrSubmit = () => {
    if (isSuggestionsOpen.value && activeSuggestionIndex.value >= 0 && suggestions.value[activeSuggestionIndex.value]) {
        handleSelectSuggestion(suggestions.value[activeSuggestionIndex.value]);
    } else {
        handleSearch();
    }
};

const handleSelectSuggestion = (item) => {
    isSuggestionsOpen.value = false;
    clearSuggestions();

    if (item.type === 'property' && item.slug) {
        router.push(`/property/${item.slug}`);
        return;
    }

    if (item.type === 'city') {
        setCity(item.city);
        searchForm.value.city = item.city;
        searchForm.value.keyword = '';
        logSearch({
            city: item.city,
            type: activeTab.value,
        });
        router.push({
            path: '/listings',
            query: {
                type: activeTab.value,
                city: item.city,
            },
        });
        return;
    }

    if (item.city) {
        setCity(item.city);
        searchForm.value.city = item.city;
    }
    searchForm.value.keyword = item.keyword || item.title;

    logSearch({
        keyword: item.keyword || item.title,
        city: item.city || searchForm.value.city,
        type: activeTab.value,
        bhk: selectedBhks.value.length > 0 ? selectedBhks.value.map(b => b.replace(/\D/g, '')).join(',') : undefined,
    });

    router.push({
        path: '/listings',
        query: {
            type: activeTab.value,
            city: item.city || searchForm.value.city || undefined,
            keyword: item.keyword || item.title,
            bhk: selectedBhks.value.length > 0 ? selectedBhks.value.map(b => b.replace(/\D/g, '')).join(',') : undefined,
        },
    });
};

const openVoiceSearchHero = () => {
    openVoiceModal((transcript, parsed) => {
        const queryParsed = parsed || parseVoiceQuery(transcript, {
            defaultType: activeTab.value,
            defaultCity: searchForm.value.city,
        });

        if (queryParsed.hasCity && queryParsed.city) {
            setCity(queryParsed.city);
            searchForm.value.city = queryParsed.city;
        }

        if (queryParsed.hasType && queryParsed.type) {
            activeTab.value = queryParsed.type;
        }

        let targetBhks = selectedBhks.value;
        if (queryParsed.hasBhk && queryParsed.bhks.length > 0) {
            selectedBhks.value = [...queryParsed.bhks];
            targetBhks = queryParsed.bhks;
        }

        searchForm.value.keyword = queryParsed.keyword;

        router.push({
            path: '/listings',
            query: {
                type: queryParsed.type || activeTab.value,
                city: queryParsed.city || searchForm.value.city,
                keyword: queryParsed.keyword || undefined,
                budget: searchForm.value.budget || undefined,
                bhk: targetBhks.length > 0 ? targetBhks.join(',') : undefined,
            },
        });
    }, {
        type: activeTab.value,
        city: searchForm.value.city,
    });
};

const searchTabs = [
    { id: 'buy', label: 'Buy' },
    { id: 'rent', label: 'Rent' },
    { id: 'commercial', label: 'Commercial' },
    { id: 'plots', label: 'Plots / Land' },
    { id: 'pg', label: 'PG / Co-living', badge: 'NEW' },
];

const majorCities = [
    'Ahmedabad',
    'Delhi NCR',
    'Mumbai',
    'Bangalore',
    'Pune',
    'Hyderabad',
    'Chennai',
    'Kolkata',
];

const toggleBhk = (bhk) => {
    const idx = selectedBhks.value.indexOf(bhk);
    if (idx === -1) selectedBhks.value.push(bhk);
    else selectedBhks.value.splice(idx, 1);
};

const useCurrentLocation = () => {
    detectLocation((data) => {
        searchForm.value.city = data.city;
        searchForm.value.keyword = 'Near me';
        router.push({
            path: '/listings',
            query: {
                type: activeTab.value,
                city: data.city,
                near_me: 'true',
                lat: data.lat || undefined,
                lng: data.lng || undefined,
            },
        });
    });
};

const handleSearch = () => {
    const raw = (searchForm.value.keyword || '').trim();
    const parsed = parseVoiceQuery(raw, {
        defaultType: activeTab.value,
        defaultCity: '',
    });

    let targetCity = parsed.hasCity ? parsed.city : (searchForm.value.city || undefined);
    let targetBhks = [...selectedBhks.value];
    let targetKeyword = raw;
    let targetType = activeTab.value;

    if (parsed.hasCity && parsed.city) {
        setCity(parsed.city);
        targetCity = parsed.city;
        searchForm.value.city = parsed.city;
    }

    if (parsed.hasBhk && parsed.bhks.length > 0) {
        targetBhks = parsed.bhks;
        selectedBhks.value = parsed.bhks;
    }

    if (parsed.hasType && parsed.type) {
        targetType = parsed.type;
        activeTab.value = parsed.type;
    }

    if (parsed.hasCity || parsed.hasBhk || parsed.hasType) {
        targetKeyword = parsed.keyword;
        searchForm.value.keyword = parsed.keyword;
    }

    logSearch({
        keyword: targetKeyword,
        city: targetCity,
        type: targetType,
        bhk: targetBhks.length > 0 ? targetBhks.map(b => b.replace(/\D/g, '')).filter(Boolean).join(',') : undefined,
        budget: searchForm.value.budget || undefined,
    });

    router.push({
        path: '/listings',
        query: {
            type: targetType,
            city: targetCity || undefined,
            keyword: targetKeyword || undefined,
            budget: searchForm.value.budget || undefined,
            bhk: targetBhks.length > 0 ? targetBhks.map(b => b.replace(/\D/g, '')).filter(Boolean).join(',') : undefined,
        },
    });
};

const replaySearch = (item) => {
    const meta = item.metadata || {};
    const queryParams = {
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

const filterByCategory = (categoryType) => {
    router.push({
        path: '/listings',
        query: {
            type: categoryType,
            city: searchForm.value.city || currentCity.value || undefined,
        },
    });
};

const applyHomeLoan = () => {
    alert(`Home Loan pre-qualification initiated for ${formatPriceWords(loanAmount.value)} at ${interestRate.value}%.`);
};

// Home Loan Calculator Reactive State
const loanAmount = ref(5000000);
const interestRate = ref(8.5);
const tenureYears = ref(20);

const calculatedEmi = computed(() => {
    const p = loanAmount.value;
    const r = (interestRate.value / 12) / 100;
    const n = tenureYears.value * 12;
    if (p <= 0 || r <= 0 || n <= 0) return 0;
    const emi = (p * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    return Math.round(emi);
});

const totalInterest = computed(() => {
    const totalPayment = calculatedEmi.value * (tenureYears.value * 12);
    return Math.max(0, Math.round(totalPayment - loanAmount.value));
});

const quickServices = [
    { icon: Landmark, title: 'Home Loans', subtitle: 'Lowest Rates @ 8.4%' },
    { icon: Scale, title: 'Legal & RERA', subtitle: 'Title Verification' },
    { icon: Truck, title: 'Packers & Movers', subtitle: 'Hassle-free Relocation' },
    { icon: Armchair, title: 'Home Interiors', subtitle: 'Designer Solutions' },
    { icon: TrendingUp, title: 'Price Trends', subtitle: 'Locality Analytics' },
];

const apiProperties = ref([]);

const formatPriceWords = (val) => {
    if (!val) return 'Price on Request';
    if (typeof val === 'string' && (val.includes('₹') || val.includes('Cr') || val.includes('L') || val.includes('Lakh'))) {
        return val;
    }
    const num = Number(val);
    if (isNaN(num) || num <= 0) {
        return typeof val === 'string' && val.trim() ? val : 'Price on Request';
    }
    if (num >= 10000000) {
        const cr = (num / 10000000).toFixed(2);
        return `₹ ${cr.replace(/\.00$/, '').replace(/\.([0-9])0$/, '.$1')} Cr`;
    }
    if (num >= 100000) {
        const lk = (num / 100000).toFixed(2);
        return `₹ ${lk.replace(/\.00$/, '').replace(/\.([0-9])0$/, '.$1')} L`;
    }
    return `₹ ${num.toLocaleString('en-IN')}`;
};

const { data: propertiesResponse } = useLazyAsyncData('home_featured_properties', async () => {
    const { get } = useApi();
    try {
        const data = await get('/api/properties?per_page=12&simple=1');
        return data;
    } catch (err) {
        if (err?.name === 'AbortError' || err?.isAbort) {
            return { data: [] };
        }
        return { data: [] };
    }
}, { default: () => ({ data: [] }) });

watch(() => propertiesResponse.value, (data) => {
    if (data) {
        const list = data.data || data || [];
        if (Array.isArray(list)) {
            apiProperties.value = list.map(p => {
                const isRes = isResidentialType(p.property_type);
                const bedrooms = p.bedrooms !== null && p.bedrooms !== undefined ? Number(p.bedrooms) : 0;
                return {
                    id: p.id,
                    slug: p.slug || String(p.id),
                    title: p.title,
                    category: p.property_type || 'Apartment',
                    bhk: isRes && bedrooms > 0 ? `${bedrooms} BHK` : '',
                    bedrooms: bedrooms,
                    price: formatPriceWords(p.expected_price),
                    expected_price: p.expected_price,
                    rate: p.price_per_sqft ? `₹ ${Number(p.price_per_sqft).toLocaleString('en-IN')}/sq.ft` : '',
                    locality: p.locality || '',
                    city: p.city || '',
                    area: p.carpet_area ? String(p.carpet_area) : (p.super_builtup_area ? String(p.super_builtup_area) : '1,200'),
                    possession: p.construction_status || 'Ready to Move',
                    is_verified: !!p.is_verified,
                    is_featured: !!p.is_featured,
                    badge: p.is_featured ? 'Featured' : (p.is_verified ? 'Verified' : 'Owner'),
                    photosCount: p.photos && Array.isArray(p.photos) ? p.photos.length : 0,
                    photos: Array.isArray(p.photos) ? p.photos : [],
                    videosCount: p.videos && Array.isArray(p.videos) ? p.videos.length : 0,
                    videos: Array.isArray(p.videos) ? p.videos : [],
                    mediaCount: (Array.isArray(p.photos) ? p.photos.length : 0) + (Array.isArray(p.videos) ? p.videos.length : 0),
                    sellerType: p.user_type || (p.user ? p.user.role : 'Owner'),
                };
            });
        }
    }
}, { immediate: true });

const handpickedListings = computed(() => {
    return [...apiProperties.value].sort((a, b) => {
        if (a.is_featured && !b.is_featured) return -1;
        if (!a.is_featured && b.is_featured) return 1;
        return 0;
    });
});

const propertyCategories = [
    { name: 'Residential Apartments', type: 'buy', count: '32,400+', icon: Building2 },
    { name: 'Independent Houses & Villas', type: 'buy', count: '11,200+', icon: Home },
    { name: 'Residential Plots & Land', type: 'plots', count: '8,900+', icon: LandPlot },
    { name: 'Commercial Shops & Offices', type: 'commercial', count: '6,450+', icon: Store },
];

const handleActivityUpdated = (e) => {
    if (e?.detail?.type === 'shortlist_added' || e?.detail?.type === 'shortlist_removed') {
        return;
    }
    loadAllActivities();
};

onMounted(async () => {
    await loadAllActivities();
    if (viewedCount.value > 0) {
        homeActivityTab.value = 'viewed';
    } else if (shortlistCount.value > 0) {
        homeActivityTab.value = 'shortlisted';
    } else if (searchCount.value > 0) {
        homeActivityTab.value = 'searches';
    } else if (contactedCount.value > 0) {
        homeActivityTab.value = 'contacted';
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
