<template>
    <div class="space-y-12 pb-20 bg-slate-50 text-slate-800">
        <!-- Top Promotional Announcement Ribbon (Controlled by Admin Settings) -->
        <div
            v-if="bannerEnabled"
            class="text-white py-2.5 px-4 text-xs transition-colors duration-200"
            :style="{ backgroundColor: brandPrimaryColor }"
        >
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="bg-amber-400 text-slate-950 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider">
                        {{ bannerBadge }}
                    </span>
                    <span class="font-medium text-white">{{ bannerText }}</span>
                </div>
                <router-link :to="bannerLink" class="hidden sm:inline-flex items-center gap-1 font-bold text-amber-300 hover:text-white transition">
                    Explore Deals &rarr;
                </router-link>
            </div>
        </div>

        <!-- 99acres Signature Hero & Search Box -->
        <section
            class="relative text-white pt-10 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden transition-all duration-300"
            :style="{ background: heroGradient }"
        >
            <!-- Decorative backdrop glow -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -right-32 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-5xl mx-auto relative z-10 text-center space-y-6">
                <!-- Portal Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 border border-white/25 text-white text-xs font-semibold backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    India's Most Trusted Real Estate Marketplace
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
                    Find Your Dream Home <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-amber-200 via-white to-cyan-200 bg-clip-text text-transparent">
                        Buy, Rent &amp; Commercial Properties
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-200 max-w-2xl mx-auto">
                    Search from 50,000+ verified listings, new builder township launches, and direct owner properties without brokerage.
                </p>

                <!-- Floating 99acres Style Search Card -->
                <div class="mt-8 bg-white text-slate-800 rounded-3xl shadow-2xl p-4 sm:p-7 border border-slate-100 text-left max-w-4xl mx-auto">
                    <!-- Tab Pills: Buy | Rent | Commercial | Plots | PG/Co-living -->
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3.5 overflow-x-auto">
                        <button
                            v-for="tab in searchTabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                            :style="activeTab === tab.id ? { backgroundColor: brandPrimaryColor, color: '#fff', boxShadow: '0 4px 12px ' + brandPrimaryColor + '35' } : {}"
                            :class="activeTab === tab.id ? '' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                        >
                            <span>{{ tab.label }}</span>
                            <span v-if="tab.badge" class="text-[9px] bg-amber-400 text-slate-900 px-1.5 py-0.2 rounded font-black">{{ tab.badge }}</span>
                        </button>
                    </div>

                    <!-- Search Input Bar -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mt-4">
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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
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
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
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
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                </div>
                            </div>

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

                        <!-- Big Search Button -->
                        <div class="md:col-span-3 flex items-end">
                            <button
                                @click="handleSearch"
                                class="w-full py-2.5 px-4 rounded-xl text-white font-extrabold text-xs sm:text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                :style="{ backgroundColor: brandPrimaryColor, boxShadow: '0 4px 12px ' + brandPrimaryColor + '40' }"
                            >
                                <span>Search</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Granular BHK and Budget Bar -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-slate-400 text-[11px] font-bold uppercase">BHK:</span>
                            <button
                                v-for="bhk in ['1 BHK', '2 BHK', '3 BHK', '4+ BHK']"
                                :key="bhk"
                                @click="toggleBhk(bhk)"
                                class="px-3 py-1 rounded-lg text-xs font-semibold border transition cursor-pointer"
                                :style="selectedBhks.includes(bhk) ? { backgroundColor: brandPrimaryColor + '15', borderColor: brandPrimaryColor, color: brandPrimaryColor, fontWeight: '700' } : {}"
                                :class="selectedBhks.includes(bhk) ? '' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                {{ bhk }}
                            </button>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400 text-[11px] font-bold uppercase">Budget:</span>
                                <select
                                    v-model="searchForm.budget"
                                    class="bg-slate-50 border border-slate-200 text-[11px] font-semibold py-1 px-2.5 rounded-lg outline-none cursor-pointer"
                                >
                                    <option value="">Any Budget</option>
                                    <option value="under_50l">Under ₹50 Lac</option>
                                    <option value="50l_1cr">₹50 Lac - ₹1 Cr</option>
                                    <option value="1cr_2cr">₹1 Cr - ₹2 Cr</option>
                                    <option value="above_2cr">₹2 Cr+</option>
                                </select>
                            </div>

                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-600 select-none">
                                <input type="checkbox" v-model="verifiedOnly" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-[11px] font-semibold">Verified Only</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Real Estate Services Ribbon (99acres style) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-4 sm:p-5 grid grid-cols-2 md:grid-cols-5 gap-3">
                <div v-for="service in quickServices" :key="service.title" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition cursor-pointer">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0"
                        :style="{ backgroundColor: brandPrimaryColor + '18', color: brandPrimaryColor }"
                    >
                        {{ service.icon }}
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">{{ service.title }}</span>
                        <span class="text-[10px] text-slate-500">{{ service.subtitle }}</span>
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
                    :to="{ name: 'client.property-detail', params: { slug: prop.slug } }"
                    class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-lg transition flex flex-col overflow-hidden group cursor-pointer"
                >
                    <!-- Property Image / Header -->
                    <div class="h-44 bg-slate-800 relative flex items-center justify-center overflow-hidden">
                        <img
                            v-if="prop.photos && prop.photos.length"
                            :src="prop.photos[0]"
                            :alt="prop.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        />
                        <div v-else class="text-3xl">🏠</div>

                        <!-- Badge Overlay -->
                        <span
                            class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full text-white font-extrabold text-[10px] uppercase shadow-xs"
                            :style="{ backgroundColor: brandPrimaryColor }"
                        >
                            {{ prop.badge }}
                        </span>
                        <span class="absolute top-3 right-3 bg-slate-950/70 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-md">
                            📷 {{ prop.photosCount }}
                        </span>

                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent p-3 pt-6 flex items-end justify-between">
                            <span class="text-white font-extrabold text-sm">
                                <template v-if="isResidentialType(prop.category) && prop.bedrooms > 0">
                                    {{ prop.bedrooms }} BHK
                                </template>
                                <template v-else>
                                    {{ prop.category }}
                                </template>
                            </span>
                            <span class="text-slate-300 text-[11px] font-medium">{{ prop.area }} sq.ft</span>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm line-clamp-1 group-hover:text-blue-600 transition">
                                {{ prop.title }}
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span class="truncate">{{ prop.locality }}, {{ prop.city }}</span>
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
                        class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 font-bold text-2xl group-hover:scale-110 transition-transform"
                        :style="{ backgroundColor: brandPrimaryColor + '18', color: brandPrimaryColor }"
                    >
                        {{ cat.icon }}
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition">{{ cat.name }}</h3>
                    <p class="text-[11px] text-slate-500 mt-1">{{ cat.count }} listings</p>
                </div>
            </div>
        </section>

        <!-- Top Developers in Spotlight -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Top Developers in Spotlight</h2>
                    <p class="text-xs text-slate-500 mt-0.5">India's most reputed real estate brands &amp; township builders</p>
                </div>
                <router-link to="/brokers" class="text-xs font-bold hover:underline" :style="{ color: brandPrimaryColor }">
                    Builder Directory &rarr;
                </router-link>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div
                    v-for="dev in topDevelopers"
                    :key="dev.name"
                    class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition text-center group"
                >
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-black text-sm flex items-center justify-center mx-auto mb-2.5">
                        {{ dev.initials }}
                    </div>
                    <h4 class="font-bold text-slate-900 text-xs">{{ dev.name }}</h4>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ dev.projects }} Projects</p>
                    <div class="mt-2 text-[10px] font-bold text-amber-600 flex items-center justify-center gap-0.5">
                        <span>★</span> {{ dev.rating }}
                    </div>
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
                                <span class="font-black text-slate-900 text-sm">₹ {{ (loanAmount / 100000).toFixed(1) }} Lakhs</span>
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
                                <span class="font-bold text-slate-200">₹ {{ (loanAmount / 100000).toFixed(2) }} L</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Total Interest</span>
                                <span class="font-bold text-slate-200">₹ {{ (totalInterest / 100000).toFixed(2) }} L</span>
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

        <!-- Trending Localities in Demand -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Trending Localities with High Price Growth</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="loc in trendingLocalities" :key="loc.name" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">{{ loc.name }}</span>
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">{{ loc.growth }}</span>
                    </div>
                    <p class="text-xs font-black text-slate-800">{{ loc.avgRate }}</p>
                    <p class="text-[10px] text-slate-500">{{ loc.city }} &bull; {{ loc.properties }} properties available</p>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVoiceSearch } from '../composables/useVoiceSearch';
import { useUserLocation } from '../composables/useUserLocation';
import { useCompanyBranding } from '../composables/useCompanyBranding';
import { useSearchSuggestions } from '../composables/useSearchSuggestions';
import { isResidentialType } from '../constants/propertyConstants';

const router = useRouter();
const { openVoiceModal, parseVoiceQuery } = useVoiceSearch();
const { currentCity, isDetectingLocation, detectLocation, setCity } = useUserLocation();
const {
    companyName,
    brandPrimaryColor,
    brandSecondaryColor,
    brandAccentColor,
    heroGradient,
    bannerEnabled,
    bannerText,
    bannerBadge,
    bannerLink,
} = useCompanyBranding();
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

const filterByCategory = (categoryType) => {
    router.push({
        path: '/listings',
        query: { type: categoryType },
    });
};

const applyHomeLoan = () => {
    alert(`Home Loan pre-qualification initiated for ₹ ${(loanAmount.value / 100000).toFixed(1)} Lakhs at ${interestRate.value}%.`);
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
    { icon: '🏦', title: 'Home Loans', subtitle: 'Lowest Rates @ 8.4%' },
    { icon: '⚖️', title: 'Legal & RERA', subtitle: 'Title Verification' },
    { icon: '📦', title: 'Packers & Movers', subtitle: 'Hassle-free Relocation' },
    { icon: '🛋️', title: 'Home Interiors', subtitle: 'Designer Solutions' },
    { icon: '📈', title: 'Price Trends', subtitle: 'Locality Analytics' },
];

const apiProperties = ref([]);

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

const fetchHomeProperties = async () => {
    try {
        const res = await fetch('/api/properties');
        if (res.ok) {
            const data = await res.json();
            const list = data.data || data || [];
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
                    rate: p.price_per_sqft ? `₹ ${Number(p.price_per_sqft).toLocaleString('en-IN')}/sq.ft` : '',
                    locality: p.locality || '',
                    city: p.city || '',
                    area: p.carpet_area ? String(p.carpet_area) : (p.super_builtup_area ? String(p.super_builtup_area) : '1,200'),
                    possession: p.construction_status || 'Ready to Move',
                    badge: p.is_verified ? 'Verified' : 'Owner',
                    photosCount: p.photos && Array.isArray(p.photos) ? p.photos.length : 1,
                    photos: p.photos || [],
                    sellerType: p.user_type || (p.user ? p.user.role : 'Owner'),
                };
            });
        }
    } catch (err) {
        console.warn('Could not fetch home properties:', err);
    }
};

onMounted(() => {
    fetchHomeProperties();
});

const handpickedListings = computed(() => {
    return apiProperties.value;
});

const propertyCategories = [
    { name: 'Residential Apartments', type: 'buy', count: '32,400+', icon: '🏢' },
    { name: 'Independent Houses & Villas', type: 'buy', count: '11,200+', icon: '🏡' },
    { name: 'Residential Plots & Land', type: 'plots', count: '8,900+', icon: '📐' },
    { name: 'Commercial Shops & Offices', type: 'commercial', count: '6,450+', icon: '🏬' },
];

const topDevelopers = [
    { name: 'DLF Limited', initials: 'DLF', projects: 48, rating: '4.9' },
    { name: 'Godrej Properties', initials: 'GPL', projects: 62, rating: '4.8' },
    { name: 'Prestige Group', initials: 'PG', projects: 54, rating: '4.8' },
    { name: 'Sobha Developers', initials: 'SOB', projects: 39, rating: '4.9' },
    { name: 'Tata Housing', initials: 'TH', projects: 31, rating: '4.7' },
    { name: 'M3M India', initials: 'M3M', projects: 42, rating: '4.6' },
];

const trendingLocalities = [
    { name: 'Noida Expressway', city: 'Noida', avgRate: '₹ 8,450 / sq.ft', growth: '+14.2% YoY', properties: '3,200' },
    { name: 'Golf Course Extension', city: 'Gurugram', avgRate: '₹ 15,200 / sq.ft', growth: '+18.5% YoY', properties: '4,100' },
    { name: 'Whitefield', city: 'Bangalore', avgRate: '₹ 9,100 / sq.ft', growth: '+11.8% YoY', properties: '2,800' },
    { name: 'Hinjawadi IT Corridor', city: 'Pune', avgRate: '₹ 7,300 / sq.ft', growth: '+9.4% YoY', properties: '1,950' },
];
</script>
