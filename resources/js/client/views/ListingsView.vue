<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Top Search Bar (99acres style with Voice Search & Category Switcher) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4 relative">
            <div class="w-full md:max-w-2xl flex-1 relative">
                <form @submit.prevent="selectActiveSuggestionOrSubmit" class="bg-slate-50 border border-slate-200/90 rounded-2xl p-1.5 flex items-center gap-2 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <!-- Buy/Rent Type Dropdown -->
                    <div class="relative flex-shrink-0 pl-2">
                        <select
                            v-model="activeType"
                            @change="runSearch"
                            class="bg-transparent border-none text-xs font-bold text-slate-800 outline-none cursor-pointer pr-4 appearance-none capitalize"
                        >
                            <option value="buy">Buy</option>
                            <option value="rent">Rent</option>
                            <option value="commercial">Commercial</option>
                            <option value="plots">Plots</option>
                        </select>
                        <svg class="w-3 h-3 text-slate-500 absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>

                    <div class="h-5 w-px bg-slate-200"></div>

                    <!-- Selected City Chip (matching 99acres screenshot: Buy | Ahmedabad x | Add more) -->
                    <div v-if="activeCity && activeCity !== 'All' && !isNearMe" class="flex items-center gap-1.5 bg-slate-200/90 text-slate-800 text-xs font-bold px-2.5 py-1 rounded-xl flex-shrink-0">
                        <span>{{ activeCity }}</span>
                        <button type="button" @click="clearCity" class="hover:text-red-600 text-slate-500 font-bold cursor-pointer transition text-xs" title="Clear city filter">✕</button>
                    </div>

                    <!-- Selected Keyword or Near Me Pill (matching 99acres) -->
                    <div v-if="searchKeyword" class="flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-xl flex-shrink-0 border border-blue-200/60">
                        <span>{{ searchKeyword }}</span>
                        <button type="button" @click="clearKeyword" class="hover:text-blue-900 cursor-pointer">✕</button>
                    </div>

                    <!-- Input for Locality / Project -->
                    <input
                        v-model="searchInput"
                        type="text"
                        :placeholder="(activeCity && !isNearMe) || searchKeyword ? 'Add more / Search Project, Locality...' : 'Search Locality, Landmark, Project or Title...'"
                        @input="onSearchInput"
                        @focus="onSearchFocus"
                        @blur="closeSuggestionsWithDelay"
                        @keydown.down.prevent="navigateSuggestions(1)"
                        @keydown.up.prevent="navigateSuggestions(-1)"
                        @keydown.esc="isSuggestionsOpen = false"
                        class="w-full text-xs font-semibold text-slate-800 placeholder-slate-400 outline-none bg-transparent px-2 min-w-0"
                    />

                    <!-- Voice Mic, GPS & Search Buttons -->
                    <div class="flex items-center gap-1.5 pr-1 flex-shrink-0">
                        <!-- GPS Detect Location Button -->
                        <button
                            type="button"
                            @click="detectLocationListings"
                            :disabled="isDetectingLocation"
                            :title="isDetectingLocation ? 'Detecting location...' : 'Detect My Location & Nearby Areas'"
                            class="w-8 h-8 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-slate-100 flex items-center justify-center cursor-pointer transition"
                        >
                            <svg v-if="isDetectingLocation" class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>

                        <!-- Mic Button -->
                        <button
                            type="button"
                            @click="openVoiceSearchListings"
                            title="Search by Voice"
                            class="w-8 h-8 rounded-xl text-slate-500 hover:text-[#005ca8] hover:bg-blue-50 flex items-center justify-center cursor-pointer transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        </button>

                        <!-- Search Button -->
                        <button
                            type="submit"
                            class="w-8 h-8 rounded-xl text-white flex items-center justify-center shadow-xs cursor-pointer transition hover:opacity-90"
                            :style="{ backgroundColor: brandPrimaryColor }"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Autocomplete Suggestions Dropdown -->
                <div
                    v-if="isSuggestionsOpen && (suggestions.length > 0 || isSuggestionsLoading)"
                    class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden z-50 text-left animate-fadeIn max-h-[360px] overflow-y-auto divide-y divide-slate-100"
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
                            <span class="text-base shrink-0">{{ item.icon }}</span>
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

            <!-- Quick Stats & City Title -->
            <div class="text-right hidden md:block">
                <span class="text-xs font-black text-slate-900 block">
                    {{ displayedListings.length }} verified {{ displayedListings.length === 1 ? 'property' : 'properties' }} found
                </span>
                <span class="text-[11px] text-slate-500">
                    {{ isNearMe ? 'within 3 Km Near Me' : `in ${activeCity}${activeType !== 'all' ? ` for ${activeType === 'rent' ? 'Rent' : 'Sale'}` : ''}` }}
                </span>
            </div>
        </div>

        <!-- Result Headline & Quick Filter Bar (99acres layout) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3 flex-wrap gap-3">
                <div>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                        <span v-if="isNearMe">
                            {{ displayedListings.length }} results | Properties in 3 Km Near Me
                        </span>
                        <span v-else>
                            {{ displayedListings.length }} results | {{ selectedBhks.length > 0 ? selectedBhks.join(', ') + ' ' : '' }}Property in {{ searchKeyword ? searchKeyword + ', ' : '' }}{{ activeCity }}{{ activeType !== 'all' ? ` for ${activeType === 'rent' ? 'Rent' : 'Sale'}` : '' }}
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span v-if="isNearMe">
                            📍 Automatically detected your location &middot; Showing nearby properties in {{ activeCity }}
                        </span>
                        <span v-else>
                            Showing verified listings matching your preferences
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 hidden sm:inline">Sort By:</span>
                    <select v-model="sortBy" class="bg-white border border-slate-200 rounded-xl text-xs font-semibold py-1.5 px-3 outline-none cursor-pointer">
                        <option value="relevance">Relevance</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>
            </div>

            <!-- Horizontal Quick Tags Bar (Matching 99acres: New Launch, Owner, Verified, Under Construction, Ready to Move) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs">
                <button
                    @click="toggleTag('new_launch')"
                    class="px-3.5 py-1.5 rounded-full font-bold transition cursor-pointer flex items-center gap-1.5 whitespace-nowrap border"
                    :class="selectedTags.includes('new_launch') ? 'bg-amber-50 text-amber-900 border-amber-300 font-extrabold shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                >
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>NEW LAUNCH</span>
                </button>

                <button
                    v-for="tag in quickTags"
                    :key="tag.id"
                    @click="toggleTag(tag.id)"
                    class="px-3 py-1.5 rounded-full font-semibold transition cursor-pointer whitespace-nowrap border"
                    :class="selectedTags.includes(tag.id) ? 'bg-blue-50 text-blue-700 border-blue-300 font-bold shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                >
                    {{ tag.label }}
                </button>
            </div>
        </div>

        <!-- Layout Grid: Applied Filters Left Sidebar, Listings Right -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar: Applied Filters (matching screenshot with red box) -->
            <div class="space-y-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-800">Applied Filters</span>
                        <button
                            v-if="hasActiveFilters"
                            @click="resetFilters"
                            class="text-[11px] text-blue-600 font-bold hover:underline cursor-pointer"
                        >
                            Clear All
                        </button>
                    </div>

                    <!-- Active Filter Chips (BHK, Property Type, Keywords) -->
                    <div v-if="selectedBhks.length > 0 || (searchKeyword && !isNearMe) || selectedPropTypes.length > 0" class="flex flex-wrap gap-1.5 pb-3 border-b border-slate-100">
                        <!-- BHK Chips (Matching 99acres 3 BHK x badge) -->
                        <span
                            v-for="bhk in selectedBhks"
                            :key="bhk"
                            class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-200 shadow-2xs group"
                        >
                            <span>{{ bhk }}</span>
                            <button
                                type="button"
                                @click="toggleBhk(bhk)"
                                class="text-blue-400 group-hover:text-red-500 font-bold cursor-pointer transition text-xs"
                                title="Remove BHK filter"
                            >
                                ✕
                            </button>
                        </span>

                        <!-- Property Type Chips -->
                        <span
                            v-for="pt in selectedPropTypes"
                            :key="pt"
                            class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-200 shadow-2xs group"
                        >
                            <span>{{ pt }}</span>
                            <button
                                type="button"
                                @click="togglePropertyType(pt)"
                                class="text-blue-400 group-hover:text-red-500 font-bold cursor-pointer transition text-xs"
                                title="Remove property type filter"
                            >
                                ✕
                            </button>
                        </span>

                        <!-- Keyword Chip -->
                        <span
                            v-if="searchKeyword && !isNearMe"
                            class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-200 shadow-2xs group"
                        >
                            <span>{{ searchKeyword }}</span>
                            <button
                                type="button"
                                @click="clearKeyword"
                                class="text-blue-400 group-hover:text-red-500 font-bold cursor-pointer transition text-xs"
                                title="Remove keyword filter"
                            >
                                ✕
                            </button>
                        </span>
                    </div>

                    <!-- Nearby Localities Chips (Exact 99acres style from screenshot) -->
                    <div v-if="activeNearbyAreas.length > 0" class="space-y-2 pb-3 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nearby Areas ({{ activeNearbyAreas.length }})</span>
                            <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.2 rounded">Within 3 Km</span>
                        </div>

                        <!-- Scrollable / Wrapped Chips Grid -->
                        <div class="flex flex-wrap gap-1.5 max-h-80 overflow-y-auto pr-1">
                            <span
                                v-for="area in activeNearbyAreas"
                                :key="area"
                                class="inline-flex items-center gap-1.5 text-[11px] font-semibold bg-white text-slate-700 px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs hover:border-slate-300 transition group"
                            >
                                <span>{{ area }}</span>
                                <button
                                    type="button"
                                    @click="removeNearbyArea(area)"
                                    class="text-slate-400 group-hover:text-red-500 font-bold cursor-pointer transition text-xs"
                                    title="Remove this nearby area"
                                >
                                    ✕
                                </button>
                            </span>
                        </div>
                    </div>

                    <!-- Verified Properties Toggle (from screenshot) -->
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Verified properties</span>
                            <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                by 99acres verification team
                            </span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="verifiedOnly" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- City Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">City</label>
                        <select :value="activeCity" @change="onCityFilterChange" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold outline-none cursor-pointer">
                            <option class="text-blue-600 font-bold bg-blue-50" value="__DETECT__">📍 Detect My Location</option>
                            <option value="Ahmedabad">Ahmedabad</option>
                            <option value="Delhi NCR">Delhi NCR</option>
                            <option value="Mumbai">Mumbai</option>
                            <option value="Bangalore">Bangalore</option>
                            <option value="Pune">Pune</option>
                            <option value="Hyderabad">Hyderabad</option>
                            <option value="Chennai">Chennai</option>
                            <option value="Kolkata">Kolkata</option>
                        </select>
                    </div>

                    <!-- Budget Range -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Budget</label>
                        <div class="grid grid-cols-2 gap-2">
                            <select v-model="budgetMin" class="bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-2 text-[11px] font-semibold outline-none">
                                <option value="">No min</option>
                                <option value="40">₹40 L</option>
                                <option value="60">₹60 L</option>
                                <option value="80">₹80 L</option>
                                <option value="100">₹1 Cr</option>
                            </select>
                            <select v-model="budgetMax" class="bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-2 text-[11px] font-semibold outline-none">
                                <option value="">No max</option>
                                <option value="80">₹80 L</option>
                                <option value="120">₹1.2 Cr</option>
                                <option value="150">₹1.5 Cr</option>
                                <option value="250">₹2.5 Cr</option>
                            </select>
                        </div>
                    </div>

                    <!-- Type of Property -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Type of property</label>
                        <div class="space-y-1.5">
                            <button
                                v-for="propType in propertyTypeOptions"
                                :key="propType"
                                @click="togglePropertyType(propType)"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium border transition cursor-pointer flex items-center justify-between"
                                :class="selectedPropTypes.includes(propType) ? 'bg-blue-50 border-blue-400 text-blue-700 font-bold' : 'bg-slate-50 border-slate-200/80 text-slate-600 hover:bg-slate-100'"
                            >
                                <span>+ {{ propType }}</span>
                                <span v-if="selectedPropTypes.includes(propType)">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- No. of Bedrooms (BHK) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700">No. of Bedrooms</label>
                            <button
                                v-if="selectedBhks.length > 0"
                                type="button"
                                @click="selectedBhks = []; runSearch()"
                                class="text-[11px] text-blue-600 font-bold hover:underline cursor-pointer"
                            >
                                Clear
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="bhk in ['1 RK/ 1 BHK', '2 BHK', '3 BHK', '4 BHK', '5 BHK']"
                                :key="bhk"
                                @click="toggleBhk(bhk)"
                                class="py-1.5 px-2 rounded-lg text-xs font-medium border text-center transition cursor-pointer flex items-center justify-center gap-1"
                                :class="isBhkActive(bhk) ? 'bg-blue-50 border-blue-500 text-blue-600 font-bold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                <span v-if="isBhkActive(bhk)">✓</span>
                                <span v-else>+</span>
                                <span>{{ bhk }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Possession Status -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Possession Status</label>
                        <div class="space-y-2 text-xs text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="readyToMove" class="rounded text-blue-600 focus:ring-blue-500">
                                <span>Ready to Move</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="underConstruction" class="rounded text-blue-600 focus:ring-blue-500">
                                <span>Under Construction</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listings Stream (matching cards from 99acres screenshot) -->
            <div class="lg:col-span-3 space-y-5">
                <div v-if="displayedListings.length === 0" class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-xl font-bold">
                        🔍
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No properties matching selected nearby filters</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Try adding more nearby areas or resetting filters to view all available listings.
                    </p>
                    <button
                        type="button"
                        @click="resetFilters"
                        class="px-4 py-2 text-white rounded-xl text-xs font-bold transition cursor-pointer hover:opacity-90 shadow-xs"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Reset All Filters
                    </button>
                </div>

                <!-- 99acres Property Card Template -->
                <router-link
                    v-for="item in displayedListings"
                    :key="item.id"
                    :to="{ name: 'client.property-detail', params: { slug: item.slug } }"
                    class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition p-5 flex flex-col md:flex-row gap-5 group cursor-pointer"
                >
                    <!-- Property Thumbnail / Gallery Carousel View -->
                    <div class="w-full md:w-64 h-52 bg-slate-900 rounded-xl relative overflow-hidden flex-shrink-0 flex items-center justify-center text-white">
                        <img
                            v-if="item.photos && item.photos.length > 0"
                            :src="item.photos[0]"
                            :alt="item.title"
                            class="absolute inset-0 w-full h-full object-cover"
                        />
                        <div v-if="item.photos && item.photos.length > 0" class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent z-10"></div>
                        <div class="text-center p-4 relative z-20">
                            <span class="text-[11px] font-bold tracking-wider uppercase text-amber-300 block">{{ item.builder }}</span>
                            <span class="text-base font-extrabold mt-1 block leading-snug line-clamp-2">{{ item.title }}</span>
                            <span class="text-xs text-slate-300 block mt-0.5">{{ item.locality }}, {{ item.city }}</span>
                        </div>

                        <!-- 99acres Badge Overlay -->
                        <div class="absolute top-2.5 left-2.5 flex items-center gap-1">
                            <span
                                v-if="item.isRera"
                                class="px-1.5 py-0.5 rounded text-white font-black text-[9px] uppercase tracking-wide"
                                :style="{ backgroundColor: brandPrimaryColor }"
                            >
                                RERA
                            </span>
                            <span v-if="item.zeroBrokerage" class="px-1.5 py-0.5 rounded bg-slate-900/90 text-amber-300 font-bold text-[9px] uppercase tracking-wide">
                                ZERO BROKERAGE
                            </span>
                            <span class="px-1.5 py-0.5 rounded bg-emerald-600/90 text-white font-bold text-[9px] uppercase">
                                3D
                            </span>
                        </div>

                        <!-- Top Right New Booking Ribbon -->
                        <span v-if="item.isNewBooking" class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-black text-[9px] uppercase tracking-wide border border-indigo-200 shadow-2xs">
                            NEW BOOKING
                        </span>

                        <!-- Bottom Tag & Image count -->
                        <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-[10px] text-white/90">
                            <span class="bg-black/60 px-2 py-0.5 rounded">{{ item.status }}</span>
                            <span class="bg-black/60 px-2 py-0.5 rounded">1/{{ item.photosCount || 6 }}</span>
                        </div>
                    </div>

                    <!-- Property Details Content -->
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Header Title and Pricing -->
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-lg text-slate-900 group-hover:opacity-85 cursor-pointer transition">
                                        {{ item.title }}
                                    </h3>
                                    <p class="text-xs font-bold text-slate-600 mt-0.5">
                                        <template v-if="isResidentialType(item.type) && item.bedrooms > 0">
                                            {{ item.bedrooms }} BHK {{ item.type }}
                                        </template>
                                        <template v-else-if="item.area">
                                            {{ item.area }} {{ item.type }}
                                        </template>
                                        <template v-else>
                                            {{ item.type }}
                                        </template>
                                        in <span class="font-extrabold" :style="{ color: brandPrimaryColor }">{{ item.locality }}</span>, {{ item.city }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-lg font-black block" :style="{ color: brandPrimaryColor }">{{ item.price }}</span>
                                    <span v-if="item.rate" class="text-[10px] text-slate-400 font-semibold">{{ item.rate }}</span>
                                </div>
                            </div>

                            <!-- Pricing Variants if multi-BHK -->
                            <div v-if="item.variants" class="mt-3 flex items-center gap-3 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <div v-for="v in item.variants" :key="v.bhk" class="border-r last:border-none border-slate-200 pr-3">
                                    <span class="text-[10px] text-slate-500 font-bold block">{{ v.bhk }}</span>
                                    <span class="font-extrabold text-slate-800 text-xs">{{ v.price }}</span>
                                </div>
                            </div>

                            <!-- Nearby Landmark & Metro tags (from screenshot: Nearby: The Hillock Ahmedabad, SP Ring Road) -->
                            <div v-if="item.nearbyLandmarks && item.nearbyLandmarks.length > 0" class="mt-3 text-xs flex items-center gap-1.5 flex-wrap">
                                <span class="text-[11px] font-bold text-slate-500">Nearby :</span>
                                <span
                                    v-for="(lm, idx) in item.nearbyLandmarks"
                                    :key="idx"
                                    class="text-[11px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md"
                                >
                                    {{ lm }}
                                </span>
                                <span class="text-[10px] font-bold cursor-pointer hover:underline" :style="{ color: brandPrimaryColor }">+3</span>
                            </div>

                            <!-- Description Snippet -->
                            <p class="text-xs text-slate-500 mt-2 line-clamp-1">
                                {{ item.description }}
                            </p>
                        </div>

                        <!-- Footer: Builder Info & Action Buttons -->
                        <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Builder</span>
                                <span class="text-xs font-bold text-slate-800">{{ item.builder }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="downloadBrochure(item)"
                                    class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Brochure</span>
                                </button>

                                <button
                                    type="button"
                                    @click="contactSeller(item)"
                                    class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-1.5 hover:opacity-90"
                                    :style="{ backgroundColor: brandPrimaryColor }"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>View Number</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useVoiceSearch } from '../composables/useVoiceSearch';
import { useUserLocation } from '../composables/useUserLocation';
import { useCompanyBranding } from '../composables/useCompanyBranding';
import { useSearchSuggestions } from '../composables/useSearchSuggestions';
import { isResidentialType } from '../constants/propertyConstants';

const route = useRoute();
const router = useRouter();
const { openVoiceModal, parseVoiceQuery } = useVoiceSearch();
const { currentCity, nearbyLocalities, isDetectingLocation, detectLocation, setCity, fetchNearbyLocalities } = useUserLocation();
const { brandPrimaryColor } = useCompanyBranding();
const { suggestions, isLoading: isSuggestionsLoading, fetchSuggestions, clearSuggestions } = useSearchSuggestions();

const activeType = ref(route.query.type || 'all');
const activeCity = ref(route.query.city || currentCity.value || 'Ahmedabad');
const searchKeyword = ref(route.query.keyword || (route.query.near_me === 'true' ? 'Near me' : ''));
const searchInput = ref('');
const isNearMe = ref(route.query.near_me === 'true');
const selectedBhks = ref(route.query.bhk ? route.query.bhk.split(',') : []);
const selectedTags = ref(['new_launch']);
const selectedPropTypes = ref([]);
const verifiedOnly = ref(false);
const readyToMove = ref(true);
const underConstruction = ref(false);
const budgetMin = ref('');
const budgetMax = ref('');
const sortBy = ref('relevance');

const isSuggestionsOpen = ref(false);
const activeSuggestionIndex = ref(-1);

const onSearchInput = () => {
    activeSuggestionIndex.value = -1;
    if (searchInput.value.trim().length > 0) {
        isSuggestionsOpen.value = true;
        fetchSuggestions(searchInput.value, activeCity.value !== 'All' ? activeCity.value : '');
    } else {
        isSuggestionsOpen.value = false;
        clearSuggestions();
    }
};

const onSearchFocus = () => {
    if (searchInput.value.trim().length > 0) {
        isSuggestionsOpen.value = true;
        fetchSuggestions(searchInput.value, activeCity.value !== 'All' ? activeCity.value : '');
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
        runSearch();
    }
};

const handleSelectSuggestion = (item) => {
    isSuggestionsOpen.value = false;
    clearSuggestions();
    searchInput.value = '';

    if (item.type === 'property' && item.slug) {
        router.push(`/property/${item.slug}`);
        return;
    }

    if (item.type === 'city') {
        setCity(item.city);
        activeCity.value = item.city;
        searchKeyword.value = '';
        isNearMe.value = false;
        activeNearbyAreas.value = [];
        runSearch();
        return;
    }

    if (item.city) {
        setCity(item.city);
        activeCity.value = item.city;
    }
    searchKeyword.value = item.keyword || item.title;
    isNearMe.value = false;
    activeNearbyAreas.value = [];
    runSearch();
};

// Active Nearby Areas Chips in Applied Filters (Exact from user's 99acres screenshot)
const activeNearbyAreas = ref([]);

const defaultAhmedabadNearby = [
    'Jagatpur',
    'Chandkheda',
    'Zundal',
    'Tragad',
    'Vaishnodevi Circle',
    'Gota',
    'Charodi',
    'New Ranip',
    'New CG Road',
    'Nigam Nagar',
    'Chainpur',
    'D Cabin',
    'Janta Nagar',
    'SG Highway',
    'Ranip',
    'Sabarmati',
    'Chamunda Nagar',
    'Godrej Garden City',
    'Motera',
    'Chandlodiya',
    'Anand Nagar',
    'Koteshwar',
    'Khodiyar',
];

const quickTags = [
    { id: 'owner', label: 'Owner' },
    { id: 'verified', label: 'Verified' },
    { id: 'under_construction', label: 'Under construction' },
    { id: 'ready_to_move', label: 'Ready To Move' },
    { id: 'with_photos', label: 'With Photos' },
];

const propertyTypeOptions = [
    'Residential Apartment',
    'Independent House/Villa',
    'Builder Floor',
    'Residential Land',
    '1 RK/ Studio Apartment',
];

const hasActiveFilters = computed(() => {
    return (
        activeNearbyAreas.value.length > 0 ||
        !!searchKeyword.value ||
        selectedBhks.value.length > 0 ||
        selectedPropTypes.value.length > 0 ||
        !!budgetMin.value ||
        !!budgetMax.value ||
        verifiedOnly.value ||
        !readyToMove.value ||
        underConstruction.value
    );
});

const isBhkActive = (bhk) => {
    if (bhk === '1 RK/ 1 BHK') {
        return selectedBhks.value.includes('1 BHK') || selectedBhks.value.includes('1 RK/ 1 BHK');
    }
    if (bhk === '4 BHK') {
        return selectedBhks.value.includes('4 BHK') || selectedBhks.value.includes('4+ BHK');
    }
    if (bhk === '5 BHK') {
        return selectedBhks.value.includes('5 BHK') || selectedBhks.value.includes('4+ BHK');
    }
    return selectedBhks.value.includes(bhk);
};

const initNearbyAreas = async () => {
    if (isNearMe.value) {
        if (nearbyLocalities.value && nearbyLocalities.value.length > 0) {
            activeNearbyAreas.value = [...nearbyLocalities.value];
        } else {
            const fetched = await fetchNearbyLocalities(activeCity.value);
            activeNearbyAreas.value = fetched.length > 0 ? [...fetched] : [...defaultAhmedabadNearby];
        }
    } else {
        activeNearbyAreas.value = [];
    }
};

// Sync state if route query changes
watch(
    () => route.query,
    async (newQ) => {
        activeType.value = newQ.type || 'all';
        if (newQ.city) activeCity.value = newQ.city;
        if (newQ.near_me === 'true') {
            isNearMe.value = true;
            searchKeyword.value = 'Near me';
            await initNearbyAreas();
        } else if (newQ.keyword !== undefined) {
            searchKeyword.value = newQ.keyword;
            isNearMe.value = false;
        } else {
            searchKeyword.value = '';
            isNearMe.value = false;
        }
        if (newQ.bhk) {
            selectedBhks.value = newQ.bhk.split(',');
        } else {
            selectedBhks.value = [];
        }
        fetchApiProperties();
    }
);

const removeNearbyArea = (areaName) => {
    activeNearbyAreas.value = activeNearbyAreas.value.filter((item) => item !== areaName);
};

const toggleTag = (tagId) => {
    const idx = selectedTags.value.indexOf(tagId);
    if (idx === -1) selectedTags.value.push(tagId);
    else selectedTags.value.splice(idx, 1);
};

const togglePropertyType = (type) => {
    const idx = selectedPropTypes.value.indexOf(type);
    if (idx === -1) selectedPropTypes.value.push(type);
    else selectedPropTypes.value.splice(idx, 1);
};

const toggleBhk = (bhk) => {
    let normalized = bhk;
    if (bhk === '1 RK/ 1 BHK') normalized = '1 BHK';
    else if (bhk === '4 BHK' || bhk === '5 BHK') normalized = bhk;

    const idx = selectedBhks.value.indexOf(normalized);
    if (idx === -1) {
        if ((normalized === '4 BHK' || normalized === '5 BHK') && selectedBhks.value.includes('4+ BHK')) {
            selectedBhks.value = selectedBhks.value.filter(b => b !== '4+ BHK');
        }
        selectedBhks.value.push(normalized);
    } else {
        selectedBhks.value.splice(idx, 1);
    }
    runSearch();
};

const clearCity = () => {
    activeCity.value = 'All';
    runSearch();
};

const detectLocationListings = () => {
    detectLocation((data) => {
        activeCity.value = data.city;
        isNearMe.value = true;
        searchKeyword.value = 'Near me';
        activeNearbyAreas.value = data.nearby_localities && data.nearby_localities.length > 0
            ? [...data.nearby_localities]
            : [...defaultAhmedabadNearby];

        router.replace({
            query: {
                ...route.query,
                type: activeType.value,
                city: data.city,
                near_me: 'true',
                keyword: undefined,
            },
        });
    });
};

const onCityFilterChange = (e) => {
    const val = e.target.value;
    if (val === '__DETECT__') {
        detectLocationListings();
    } else {
        setCity(val);
        activeCity.value = val;
        isNearMe.value = false;
        activeNearbyAreas.value = [];
        runSearch();
    }
};

const openVoiceSearchListings = () => {
    openVoiceModal((transcript, parsed) => {
        const queryParsed = parsed || parseVoiceQuery(transcript, {
            defaultType: activeType.value,
            defaultCity: activeCity.value,
        });

        if (queryParsed.hasCity && queryParsed.city) {
            setCity(queryParsed.city);
            activeCity.value = queryParsed.city;
        }

        if (queryParsed.hasType && queryParsed.type) {
            activeType.value = queryParsed.type;
        }

        if (queryParsed.hasBhk && queryParsed.bhks.length > 0) {
            selectedBhks.value = [...queryParsed.bhks];
        }

        if (queryParsed.propertyTypes && queryParsed.propertyTypes.length > 0) {
            selectedPropTypes.value = [...queryParsed.propertyTypes];
        }

        searchKeyword.value = queryParsed.keyword;
        isNearMe.value = false;
        activeNearbyAreas.value = [];
        runSearch();
    }, {
        type: activeType.value,
        city: activeCity.value,
    });
};

const runSearch = () => {
    if (searchInput.value.trim()) {
        const raw = searchInput.value.trim();
        const parsed = parseVoiceQuery(raw, {
            defaultType: activeType.value,
            defaultCity: activeCity.value,
        });

        if (parsed.hasCity && parsed.city) {
            setCity(parsed.city);
            activeCity.value = parsed.city;
        }

        if (parsed.hasType && parsed.type) {
            activeType.value = parsed.type;
        }

        if (parsed.hasBhk && parsed.bhks.length > 0) {
            selectedBhks.value = [...parsed.bhks];
        }

        if (parsed.propertyTypes && parsed.propertyTypes.length > 0) {
            selectedPropTypes.value = [...parsed.propertyTypes];
        }

        if (parsed.hasCity || parsed.hasBhk || parsed.hasType) {
            searchKeyword.value = parsed.keyword;
        } else {
            searchKeyword.value = raw;
        }

        isNearMe.value = false;
        activeNearbyAreas.value = [];
        searchInput.value = '';
    }

    router.replace({
        query: {
            ...route.query,
            type: activeType.value,
            city: activeCity.value !== 'All' ? activeCity.value : undefined,
            keyword: isNearMe.value ? undefined : (searchKeyword.value || undefined),
            near_me: isNearMe.value ? 'true' : undefined,
            bhk: selectedBhks.value.length ? selectedBhks.value.join(',') : undefined,
        },
    });
};

const clearKeyword = () => {
    searchKeyword.value = '';
    isNearMe.value = false;
    activeNearbyAreas.value = [];
    runSearch();
};

const resetFilters = () => {
    searchKeyword.value = '';
    isNearMe.value = false;
    activeNearbyAreas.value = [];
    selectedBhks.value = [];
    selectedPropTypes.value = [];
    selectedTags.value = [];
    readyToMove.value = true;
    underConstruction.value = false;
    budgetMin.value = '';
    budgetMax.value = '';
    runSearch();
};

const contactSeller = (item) => {
    const phone = item.sellerPhone || '';
    if (phone) {
        alert(`Connecting you to ${item.builder || item.postedBy} for "${item.title}".\nDirect Phone: ${phone}`);
    } else {
        alert(`Connecting you to ${item.builder || item.postedBy} for "${item.title}".`);
    }
};

const downloadBrochure = (item) => {
    alert(`Downloading brochure and floor plan for "${item.title}" (${item.locality}, ${item.city}).`);
};

const apiProperties = ref([]);
const isLoadingProperties = ref(false);

const defaultFallbackListings = [
    {
        id: 101,
        slug: 'the-metropark-3-bhk-flat-vastral-ahmedabad',
        title: 'The Metropark',
        type: 'Residential Apartment',
        bhk: '3 BHK',
        bedrooms: 3,
        price: '₹ 77.4 L',
        rawPrice: 7740000,
        rate: '₹ 4,000 / sq.ft',
        locality: 'Vastral',
        city: 'Ahmedabad',
        area: '1,935 sqft (180 sqm)',
        status: 'Under Construction',
        builder: 'SUNWOODS-SHREENATH BUILDCON',
        postedBy: 'Builder',
        sellerPhone: '+91 98250 12345',
        isNewBooking: true,
        isRera: true,
        zeroBrokerage: true,
        photosCount: 8,
        photos: [
            'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&auto=format&fit=crop&q=60',
        ],
        nearbyLandmarks: ['Near Vastral Ring Road', 'Metro Station'],
        description: 'Experience a new style of living with The Metropark. 3 BHK flats in Vastral, Ahmedabad with 1st out of 14 Floors, modern clubhouse and amenities.',
        property_for: 'Sell',
    },
    {
        id: 102,
        slug: 'trinay-anagh-3-bhk-flat-jodhpur-ahmedabad',
        title: 'Trinay Anagh',
        type: 'Residential Apartment',
        bhk: '3 BHK',
        bedrooms: 3,
        price: '₹ 2 Cr',
        rawPrice: 20000000,
        rate: '₹ 7,238 / sq.ft',
        locality: 'Jodhpur',
        city: 'Ahmedabad',
        area: '2,763 sqft (257 sqm)',
        status: 'Under Construction',
        builder: 'Trinay Group',
        postedBy: 'Builder',
        sellerPhone: '+91 98251 54321',
        isNewBooking: true,
        isRera: true,
        zeroBrokerage: true,
        photosCount: 12,
        photos: [
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=60',
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&auto=format&fit=crop&q=60',
        ],
        nearbyLandmarks: ['132 Ft Ring Road', 'Opposite Star Bazaar'],
        description: 'Trinay Anagh offers 3 BHK flats in Jodhpur, Ahmedabad West. 1st out of 10 floors with Italian marble flooring, 3 balconies and 2 car parkings.',
        property_for: 'Sell',
    },
    {
        id: 103,
        slug: 'godrej-garden-city-3-bhk-sg-highway-ahmedabad',
        title: '3 BHK Luxurious High-Rise Apartment in SG Highway',
        type: 'Residential Apartment',
        bhk: '3 BHK',
        bedrooms: 3,
        price: '₹ 1.15 Cr',
        rawPrice: 11500000,
        rate: '₹ 6,969 / sq.ft',
        locality: 'SG Highway',
        city: 'Ahmedabad',
        area: '2,150 sqft (200 sqm)',
        status: 'Ready to Move',
        builder: 'Godrej Properties',
        postedBy: 'Owner',
        sellerPhone: '+91 98980 11223',
        isNewBooking: false,
        isRera: true,
        zeroBrokerage: true,
        photosCount: 6,
        photos: [
            'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=60',
        ],
        nearbyLandmarks: ['Godrej Garden City', 'Near Nirma University'],
        description: 'Spacious 3 BHK apartment with marble flooring, modular kitchen, cross ventilation, 3 covered balconies, and scenic views in SG Highway Ahmedabad.',
        property_for: 'Sell',
    },
    {
        id: 104,
        slug: 'shaligram-lakeview-3-bhk-science-city-ahmedabad',
        title: '3 BHK Premium Lakeview Flat in Science City',
        type: 'Residential Apartment',
        bhk: '3 BHK',
        bedrooms: 3,
        price: '₹ 95 L',
        rawPrice: 9500000,
        rate: '₹ 6,333 / sq.ft',
        locality: 'Science City',
        city: 'Ahmedabad',
        area: '2,050 sqft (190 sqm)',
        status: 'Ready to Move',
        builder: 'Shaligram Group',
        postedBy: 'Verified Owner',
        sellerPhone: '+91 97270 33445',
        isNewBooking: false,
        isRera: true,
        zeroBrokerage: true,
        photosCount: 9,
        photos: [
            'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&auto=format&fit=crop&q=60',
        ],
        nearbyLandmarks: ['Science City Road', 'Near CIMS Hospital'],
        description: 'Stunning 3 BHK ready-to-move apartment near Science City Ahmedabad with 100% Vastu compliance and 2 designated car parkings.',
        property_for: 'Sell',
    },
    {
        id: 105,
        slug: 'aarohi-elysium-2-bhk-bopal-ahmedabad',
        title: '2 BHK Fully Furnished Flat in Bopal',
        type: 'Residential Apartment',
        bhk: '2 BHK',
        bedrooms: 2,
        price: '₹ 28,000 / mo',
        rawPrice: 28000,
        rate: '₹ 27 / sq.ft',
        locality: 'Bopal',
        city: 'Ahmedabad',
        area: '1,350 sqft (125 sqm)',
        status: 'Ready to Move',
        builder: 'Aarohi Group',
        postedBy: 'Agent',
        sellerPhone: '+91 98240 55667',
        isNewBooking: false,
        isRera: true,
        zeroBrokerage: true,
        photosCount: 5,
        photos: [
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&auto=format&fit=crop&q=60',
        ],
        nearbyLandmarks: ['South Bopal', 'Near Sobha City'],
        description: 'Ready to move 2 BHK modern flat with split ACs, sofa, beds, refrigerator, and smart TV in South Bopal Ahmedabad.',
        property_for: 'Rent',
    }
];

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

const fetchApiProperties = async () => {
    isLoadingProperties.value = true;
    try {
        let url = '/api/properties';
        const params = new URLSearchParams();
        if (activeCity.value && activeCity.value !== 'All' && !isNearMe.value) {
            params.set('city', activeCity.value);
        }
        if (activeType.value && activeType.value !== 'all') {
            params.set('type', activeType.value);
        }
        if (selectedBhks.value.length > 0) {
            const nums = selectedBhks.value.map(b => parseInt(b, 10)).filter(n => !isNaN(n));
            if (nums.length > 0) {
                params.set('bhk', nums.join(','));
            }
        }
        if (params.toString()) {
            url += '?' + params.toString();
        }
        const res = await fetch(url);
        if (res.ok) {
            const data = await res.json();
            const list = data.data || data || [];
            if (Array.isArray(list) && list.length > 0) {
                apiProperties.value = list.map(p => {
                    const isRes = isResidentialType(p.property_type);
                    const bedrooms = p.bedrooms !== null && p.bedrooms !== undefined ? Number(p.bedrooms) : 0;
                    return {
                        id: p.id,
                        slug: p.slug || String(p.id),
                        title: p.title,
                        type: p.property_type || 'Apartment',
                        bhk: isRes && bedrooms > 0 ? `${bedrooms} BHK` : '',
                        bedrooms: bedrooms,
                        price: formatPriceWords(p.expected_price),
                        rawPrice: p.expected_price || 0,
                        rate: p.price_per_sqft ? `₹ ${Number(p.price_per_sqft).toLocaleString('en-IN')} / sq.ft` : '',
                        locality: p.locality || '',
                        city: p.city || '',
                        area: p.carpet_area ? `${p.carpet_area} sqft` : (p.super_builtup_area ? `${p.super_builtup_area} sqft` : '1,200 sqft'),
                        status: p.construction_status || 'Ready to Move',
                        builder: p.project_name || (p.user ? p.user.name : 'Verified Owner'),
                        postedBy: p.user ? p.user.name : 'Owner',
                        sellerPhone: p.user ? `${p.user.country_code || '+91'} ${p.user.mobile}` : '',
                        isNewBooking: false,
                        isRera: !!p.is_verified,
                        zeroBrokerage: true,
                        photosCount: p.photos && Array.isArray(p.photos) ? p.photos.length : 1,
                        photos: p.photos || [],
                        nearbyLandmarks: [p.landmark, p.sub_locality].filter(Boolean),
                        description: p.description || '',
                        property_for: p.property_for || 'Sell',
                    };
                });
            } else {
                apiProperties.value = [...defaultFallbackListings];
            }
        } else {
            apiProperties.value = [...defaultFallbackListings];
        }
    } catch (err) {
        console.warn('Could not fetch API properties, using fallback:', err);
        apiProperties.value = [...defaultFallbackListings];
    } finally {
        isLoadingProperties.value = false;
    }
};

onMounted(() => {
    initNearbyAreas();
    fetchApiProperties();
});

watch(
    () => [activeCity.value, activeType.value],
    () => {
        fetchApiProperties();
    }
);

const displayedListings = computed(() => {
    const list = apiProperties.value.length > 0 ? apiProperties.value : defaultFallbackListings;

    return list.filter((item) => {
        // ── Near-me locality filter ──────────────────────────────────────────
        if (isNearMe.value && activeNearbyAreas.value.length > 0) {
            const matchesNearby = activeNearbyAreas.value.some((area) =>
                (item.locality && item.locality.toLowerCase().includes(area.toLowerCase())) ||
                (item.locality && area.toLowerCase().includes(item.locality.toLowerCase()))
            );
            if (!matchesNearby) return false;
        } else if (searchKeyword.value && !isNearMe.value) {
            const q = searchKeyword.value.toLowerCase();
            const match =
                (item.title && item.title.toLowerCase().includes(q)) ||
                (item.locality && item.locality.toLowerCase().includes(q)) ||
                (item.city && item.city.toLowerCase().includes(q)) ||
                (item.bhk && item.bhk.toLowerCase().includes(q)) ||
                (item.type && item.type.toLowerCase().includes(q));
            if (!match) return false;
        }

        // ── City filter ──────────────────────────────────────────────────────
        if (!isNearMe.value && activeCity.value && activeCity.value !== 'All') {
            if (!item.city.toLowerCase().includes(activeCity.value.toLowerCase())) {
                return false;
            }
        }

        // ── Property type filter ─────────────────────────────────────────────
        if (selectedPropTypes.value.length > 0) {
            const matchesType = selectedPropTypes.value.some((pt) =>
                item.type && item.type.toLowerCase() === pt.toLowerCase()
            );
            if (!matchesType) return false;
        }

        // ── BHK / Bedrooms filter ────────────────────────────────────────────
        if (selectedBhks.value.length > 0) {
            const hasBhk = selectedBhks.value.some((bhk) => {
                if (bhk === '4+ BHK' || bhk === '4 BHK' || bhk === '5 BHK') {
                    if (bhk === '4+ BHK') return item.bedrooms >= 4;
                    const num = parseInt(bhk, 10);
                    return item.bedrooms === num;
                }
                const num = parseInt(bhk, 10);
                return item.bedrooms === num;
            });
            if (!hasBhk) return false;
        }

        // ── Budget filter (dropdown values are in Lakhs, rawPrice is in ₹) ──
        const lakhs = 100000;
        if (budgetMin.value) {
            const minRupees = Number(budgetMin.value) * lakhs;
            if (item.rawPrice < minRupees) return false;
        }
        if (budgetMax.value) {
            const maxRupees = Number(budgetMax.value) * lakhs;
            if (item.rawPrice > maxRupees) return false;
        }

        // ── Possession status filter ─────────────────────────────────────────
        if (!readyToMove.value || !underConstruction.value) {
            const s = (item.status || '').toLowerCase();
            const isReady = s.includes('ready');
            const isUnder = s.includes('under') || s.includes('construction');
            if (readyToMove.value && !underConstruction.value && !isReady) return false;
            if (!readyToMove.value && underConstruction.value && !isUnder) return false;
            if (!readyToMove.value && !underConstruction.value) return false;
        }

        // ── Verified-only filter ─────────────────────────────────────────────
        if (verifiedOnly.value && !item.isRera) {
            return false;
        }

        return true;
    });
});

const filteredListings = computed(() => displayedListings.value || []);
</script>

