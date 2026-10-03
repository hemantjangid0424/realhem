<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Top Search Bar (99acres style with Voice Search & Category Switcher) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="w-full md:max-w-2xl flex-1">
                <form @submit.prevent="runSearch" class="bg-slate-50 border border-slate-200/90 rounded-2xl p-1.5 flex items-center gap-2">
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

                    <!-- Selected Keyword or Near Me Pill (matching 99acres) -->
                    <div v-if="searchKeyword" class="flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-xl flex-shrink-0 border border-blue-200/60">
                        <span>{{ searchKeyword }}</span>
                        <button type="button" @click="clearKeyword" class="hover:text-blue-900 cursor-pointer">✕</button>
                    </div>

                    <!-- Input for Locality / Project -->
                    <input
                        v-model="searchInput"
                        type="text"
                        :placeholder="searchKeyword ? 'Start new search' : 'Search Locality, Landmark, Project or Builder...'"
                        class="w-full text-xs font-semibold text-slate-800 placeholder-slate-400 outline-none bg-transparent px-2"
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
                            {{ displayedListings.length }} results | Property in {{ searchKeyword ? searchKeyword + ', ' : '' }}{{ activeCity }}{{ activeType !== 'all' ? ` for ${activeType === 'rent' ? 'Rent' : 'Sale'}` : '' }}
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

                    <!-- Non-NearMe Single Keyword Chip -->
                    <div v-else-if="searchKeyword && !isNearMe" class="flex flex-wrap gap-1.5 pb-2 border-b border-slate-100">
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg border border-blue-200">
                            {{ searchKeyword }}
                            <button type="button" @click="clearKeyword" class="hover:text-blue-900 cursor-pointer">✕</button>
                        </span>
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
                        <label class="block text-xs font-bold text-slate-700 mb-2">No. of Bedrooms</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="bhk in ['1 BHK', '2 BHK', '3 BHK', '4+ BHK']"
                                :key="bhk"
                                @click="toggleBhk(bhk)"
                                class="py-1.5 px-2 rounded-lg text-xs font-medium border text-center transition cursor-pointer"
                                :class="selectedBhks.includes(bhk) ? 'bg-blue-50 border-blue-500 text-blue-600 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                {{ bhk }}
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
                                        {{ item.bhk }} {{ item.type }} in <span class="font-extrabold" :style="{ color: brandPrimaryColor }">{{ item.locality }}</span>, {{ item.city }}
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

const route = useRoute();
const router = useRouter();
const { openVoiceModal } = useVoiceSearch();
const { currentCity, nearbyLocalities, isDetectingLocation, detectLocation, setCity, fetchNearbyLocalities } = useUserLocation();
const { brandPrimaryColor } = useCompanyBranding();

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
        }
        if (newQ.bhk) selectedBhks.value = newQ.bhk.split(',');
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
    const idx = selectedBhks.value.indexOf(bhk);
    if (idx === -1) selectedBhks.value.push(bhk);
    else selectedBhks.value.splice(idx, 1);
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
    openVoiceModal((transcript) => {
        searchKeyword.value = transcript;
        isNearMe.value = false;
        runSearch();
    }, {
        type: activeType.value,
        city: activeCity.value,
    });
};

const runSearch = () => {
    if (searchInput.value.trim()) {
        searchKeyword.value = searchInput.value.trim();
        isNearMe.value = false;
        activeNearbyAreas.value = [];
        searchInput.value = '';
    }

    router.replace({
        query: {
            ...route.query,
            type: activeType.value,
            city: activeCity.value,
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
        if (params.toString()) {
            url += '?' + params.toString();
        }
        const res = await fetch(url);
        if (res.ok) {
            const data = await res.json();
            const list = data.data || data || [];
            apiProperties.value = list.map(p => ({
                id: p.id,
                slug: p.slug || String(p.id),
                title: p.title,
                type: p.property_type || 'Apartment',
                bhk: `${p.bedrooms || 1} BHK`,
                bedrooms: p.bedrooms || 1,
                price: formatPriceWords(p.expected_price),
                rawPrice: p.expected_price || 0,
                rate: p.price_per_sqft ? `₹ ${Number(p.price_per_sqft).toLocaleString('en-IN')} / sq.ft` : '',
                locality: p.locality || '',
                city: p.city || '',
                area: p.carpet_area ? String(p.carpet_area) : (p.super_builtup_area ? String(p.super_builtup_area) : '1,200'),
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
            }));
        }
    } catch (err) {
        console.warn('Could not fetch API properties:', err);
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
    return apiProperties.value.filter((item) => {
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
                if (bhk === '4+ BHK') return item.bedrooms >= 4;
                const num = parseInt(bhk);
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
        // Only apply when at least one box is unchecked (both checked = show all)
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

