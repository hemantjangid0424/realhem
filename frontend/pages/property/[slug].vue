<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Loading State -->
        <div v-if="isLoading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex items-center justify-center min-h-[60vh]">
            <div class="text-center space-y-3">
                <svg class="w-10 h-10 text-blue-600 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-semibold text-slate-500">Loading property details…</p>
            </div>
        </div>

        <!-- Not Found State -->
        <div v-else-if="notFound" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center">
                <Building2 class="w-8 h-8" />
            </div>
            <h2 class="text-2xl font-black text-slate-900">Property Not Found</h2>
            <p class="text-sm text-slate-500">This listing may have been removed or the link is incorrect.</p>
            <button @click="router.push('/listings')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer">
                Browse All Properties
            </button>
        </div>

        <!-- Main Content -->
        <div v-else-if="property" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Header Bar with Breadcrumb & Shortlist Action -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 min-w-0">
                    <button @click="router.push('/')" class="hover:underline font-semibold transition cursor-pointer" :style="{ color: brandPrimaryColor }">Home</button>
                    <span>›</span>
                    <button @click="router.push('/listings?city=' + property.city)" class="hover:underline font-semibold transition cursor-pointer" :style="{ color: brandPrimaryColor }">{{ property.city }}</button>
                    <span>›</span>
                    <button @click="router.push('/listings?city=' + property.city + '&keyword=' + property.locality)" class="hover:underline font-semibold transition cursor-pointer" :style="{ color: brandPrimaryColor }">{{ property.locality }}</button>
                    <span>›</span>
                    <span class="text-slate-800 font-semibold truncate">{{ property.title }}</span>
                </nav>

                <div class="flex items-center gap-2 shrink-0">
                    <ShortlistButton
                        :property="property"
                        :propertyId="property.id"
                        variant="button"
                        size="md"
                        defaultText="Shortlist"
                        savedText="Shortlisted"
                        :showFloatingToast="true"
                    />

                    <button
                        type="button"
                        @click="shareProperty"
                        class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95"
                        title="Share property via system share"
                    >
                        <Share2 class="w-3.5 h-3.5 text-slate-500" />
                        <span>{{ copied ? '✓ Copied' : 'Share' }}</span>
                    </button>
                </div>
            </div>

            <!-- Hero: Photo Gallery + Quick Info -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Photo Gallery -->
                <div class="lg:col-span-2 space-y-2">
                    <!-- Main Photo Container (unclipped for heart animation burst) -->
                    <div class="relative group aspect-video">
                        <!-- Media Canvas with rounded-2xl & overflow-hidden -->
                        <div
                            class="absolute inset-0 rounded-2xl overflow-hidden bg-slate-900 cursor-pointer"
                            @click="openPhotoGallery(activePhotoIdx)"
                            title="Click to view full photo gallery"
                        >
                            <img
                                v-if="property.photos && property.photos.length > 0"
                                :src="property.photos[activePhotoIdx]"
                                :alt="property.title"
                                class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <video
                                v-else-if="property.videos && property.videos.length > 0"
                                :src="property.videos[0]"
                                controls
                                playsinline
                                preload="metadata"
                                class="absolute inset-0 w-full h-full object-contain bg-black"
                                @click.stop
                            ></video>
                            <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 gap-2">
                                <div class="w-14 h-14 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center">
                                    <Camera class="w-7 h-7" />
                                </div>
                                <p class="text-sm font-semibold">No media available</p>
                            </div>
                        </div>

                        <!-- Overlay badges -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap z-10 pointer-events-none">
                            <!-- Featured Badge -->
                            <span
                                v-if="property.is_featured"
                                class="group/badge h-7 px-2 hover:px-3 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-[10px] uppercase tracking-wider shadow-sm flex items-center transition-all duration-300 ease-out hover:scale-105 hover:shadow-md cursor-pointer backdrop-blur-sm"
                                title="Featured Listing"
                            >
                                <Star class="w-3.5 h-3.5 fill-current shrink-0" />
                                <span class="max-w-0 opacity-0 group-hover/badge:max-w-36 group-hover/badge:opacity-100 group-hover/badge:pl-1.5 overflow-hidden whitespace-nowrap transition-all duration-300 ease-out">
                                    FEATURED LISTING
                                </span>
                            </span>

                            <!-- Verified Badge -->
                            <span
                                v-if="property.is_verified"
                                class="group/badge h-7 px-2 hover:px-3 rounded-full bg-blue-600 text-white font-black text-[10px] uppercase tracking-wider shadow-sm flex items-center transition-all duration-300 ease-out hover:scale-105 hover:shadow-md cursor-pointer backdrop-blur-sm"
                                title="Verified Partner"
                            >
                                <Check class="w-3.5 h-3.5 text-white shrink-0" :stroke-width="3" />
                                <span class="max-w-0 opacity-0 group-hover/badge:max-w-40 group-hover/badge:opacity-100 group-hover/badge:pl-1.5 overflow-hidden whitespace-nowrap transition-all duration-300 ease-out">
                                    VERIFIED BY REALHEM
                                </span>
                            </span>
                        </div>

                        <!-- For Sale / Rent badge & Hero Shortlist Heart -->
                        <div class="absolute top-3 right-3 z-30 flex items-center gap-2">
                            <span :class="property.property_for === PROPERTY_FOR.SELL ? 'bg-emerald-600/90 text-white' : 'bg-orange-500/90 text-white'"
                                class="px-2.5 py-1 rounded-xl font-black text-[11px] uppercase tracking-wide backdrop-blur-sm shadow-sm">
                                For {{ getPropertyIntentLabel(property.property_for) }}
                            </span>
                            <ShortlistButton
                                :property="property"
                                :propertyId="property.id"
                                size="md"
                            />
                        </div>

                        <!-- Photo navigation arrows (only when >1 photo) -->
                        <template v-if="property.photos && property.photos.length > 1">
                            <button
                                @click="prevPhoto"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-900/70 text-white flex items-center justify-center hover:bg-slate-900 transition cursor-pointer backdrop-blur-sm z-10"
                            >
                                <ChevronLeft class="w-4 h-4" :stroke-width="2.5" />
                            </button>
                            <button
                                @click="nextPhoto"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-900/70 text-white flex items-center justify-center hover:bg-slate-900 transition cursor-pointer backdrop-blur-sm z-10"
                            >
                                <ChevronRight class="w-4 h-4" :stroke-width="2.5" />
                            </button>
                        </template>

                        <!-- Media Badges (Photos + Video Tour) -->
                        <div class="absolute bottom-3 right-3 flex items-center gap-2 z-10">
                            <!-- Video tour badge/button if property has videos and photos -->
                            <button
                                v-if="property.videos && property.videos.length > 0 && property.photos && property.photos.length > 0"
                                type="button"
                                @click.stop="openVideoGallery(0)"
                                class="bg-purple-600/90 hover:bg-purple-600 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg backdrop-blur-sm flex items-center gap-1.5 transition cursor-pointer hover:scale-105 active:scale-95 shadow-sm"
                                title="Watch property video tour"
                            >
                                <Play class="w-3.5 h-3.5 fill-current" />
                                <span>Video Tour ({{ property.videos.length }})</span>
                            </button>

                            <!-- Combined Media count badge (opens gallery) -->
                            <button
                                v-if="((property.photos?.length || 0) + (property.videos?.length || 0)) > 0"
                                type="button"
                                @click.stop="property.photos && property.photos.length > 0 ? openPhotoGallery(activePhotoIdx) : openVideoGallery(0)"
                                class="bg-slate-900/80 hover:bg-slate-900 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg backdrop-blur-sm flex items-center gap-1.5 transition cursor-pointer hover:scale-105 active:scale-95 shadow-sm border border-white/10"
                                title="View media gallery"
                            >
                                <template v-if="!property.photos || property.photos.length === 0">
                                    <Play class="w-3.5 h-3.5 fill-current text-purple-400" />
                                    <span>Video 1 of {{ property.videos.length }}</span>
                                </template>
                                <template v-else>
                                    <Camera class="w-3.5 h-3.5 text-white/90" />
                                    <span>{{ activePhotoIdx + 1 }} / {{ (property.photos?.length || 0) + (property.videos?.length || 0) }}</span>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Thumbnail Strip -->
                    <div v-if="property.photos && property.photos.length > 1" class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                        <button
                            v-for="(photo, idx) in property.photos"
                            :key="idx"
                            @click="activePhotoIdx = idx"
                            :class="[
                                'flex-shrink-0 w-20 h-14 rounded-xl overflow-hidden border-2 transition cursor-pointer',
                                activePhotoIdx === idx ? 'shadow-md' : 'border-transparent opacity-60 hover:opacity-100'
                            ]"
                            :style="activePhotoIdx === idx ? { borderColor: brandPrimaryColor } : {}"
                        >
                            <img :src="photo" :alt="`Photo ${idx + 1}`" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Quick Contact Card (sticky) -->
                <div class="lg:col-span-1">
                    <div class="sticky top-20 bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-5">
                        <!-- Price -->
                        <div class="border-b border-slate-100 pb-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-2xl font-black" :style="{ color: brandPrimaryColor }">{{ formatPrice(property.expected_price) }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        <span v-if="property.price_per_sqft">₹ {{ Number(property.price_per_sqft).toLocaleString('en-IN') }}/sq.ft</span>
                                        <span v-if="property.price_negotiable" class="ml-2 text-emerald-600 font-bold">• Price Negotiable</span>
                                    </div>
                                </div>
                                <button
                                    v-if="property.property_for === PROPERTY_FOR.SELL || !property.property_for"
                                    type="button"
                                    @click="isEmiModalOpen = true"
                                    class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100/80 px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0"
                                    title="Open EMI Calculator"
                                >
                                    <Calculator class="w-3.5 h-3.5" />
                                    <span>EMI Calculator</span>
                                </button>
                            </div>

                            <!-- Estimated Monthly EMI Pill (like 99acres & MagicBricks) -->
                            <div
                                v-if="(property.property_for === PROPERTY_FOR.SELL || !property.property_for) && property.expected_price > 0"
                                @click="isEmiModalOpen = true"
                                class="mt-3 p-2.5 rounded-xl bg-slate-50 hover:bg-blue-50/60 border border-slate-200/80 hover:border-blue-200 transition cursor-pointer flex items-center justify-between text-xs group"
                            >
                                <div class="flex items-center gap-2 text-slate-700">
                                    <Landmark class="w-4 h-4 text-blue-600" />
                                    <span>EMI starts at <strong class="text-slate-900 font-black">{{ estimatedMonthlyEmiFormatted }}</strong>/mo</span>
                                </div>
                                <span class="text-[11px] font-bold text-blue-600 group-hover:text-blue-700 group-hover:underline flex items-center gap-0.5">
                                    Calculate EMI
                                    <ChevronRight class="w-3 h-3 transition-transform group-hover:translate-x-0.5" :stroke-width="2.5" />
                                </span>
                            </div>
                        </div>

                        <!-- Key Specs -->
                        <div class="grid grid-cols-3 gap-3 text-center border-b border-slate-100 pb-4">
                            <template v-if="isResidential">
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.bedrooms }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Beds</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.bathrooms }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Baths</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.carpet_area }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">sq.ft</div>
                                </div>
                            </template>
                            <template v-else-if="isCommercial">
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.carpet_area }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">sq.ft Area</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.bathrooms > 0 ? property.bathrooms : '1' }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Washroom</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-xs font-black text-slate-900 truncate mt-1">{{ property.furnishing_status || 'Bare Shell' }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Furnishing</div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.carpet_area }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Plot Area</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-lg font-black text-slate-900">{{ property.facing || 'East' }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Facing</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-xs font-black text-slate-900 truncate mt-1">{{ property.construction_status || 'Ready' }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Status</div>
                                </div>
                            </template>
                        </div>

                        <!-- Seller Info -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow-xs" :style="{ backgroundColor: brandPrimaryColor }">
                                {{ sellerInitials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-slate-900 truncate flex items-center gap-1.5">
                                    <span>{{ property.user?.name || 'Verified Owner' }}</span>
                                    <BadgeCheck v-if="property.is_verified" class="w-4 h-4 text-blue-600 shrink-0" title="Verified Seller" />
                                </div>
                                <div class="text-[11px] text-slate-500 capitalize flex items-center gap-1">
                                    <span>{{ property.user_type || 'Owner' }}</span>
                                    <span v-if="property.is_verified" class="text-blue-600 font-semibold">• Verified Partner</span>
                                </div>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0" title="Online"></span>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="space-y-2.5">
                            <button
                                @click="handleContactClick('phone')"
                                class="w-full py-3 rounded-2xl text-white font-black text-sm shadow-md transition cursor-pointer flex items-center justify-center gap-2 hover:opacity-95"
                                :style="{ backgroundColor: brandPrimaryColor }"
                            >
                                <Phone class="w-4 h-4" />
                                View Contact Number
                            </button>
                            <button
                                @click="handleContactClick('message')"
                                class="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-sm transition cursor-pointer flex items-center justify-center gap-2"
                            >
                                <MessageSquare class="w-4 h-4" />
                                Send Message
                            </button>
                        </div>

                        <!-- Safe dealing note -->
                        <p class="text-[10px] text-slate-400 text-center leading-relaxed flex items-center justify-center gap-1">
                            <ShieldCheck class="w-3.5 h-3.5 text-emerald-600" />
                            <span>100% safe to connect. Verified listing by {{ companyName }}.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Title + Location Bar -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-1">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <!-- Badges Row -->
                        <div class="flex items-center gap-2 flex-wrap mb-1.5">
                            <span
                                v-if="property.is_featured"
                                class="inline-flex items-center gap-1 text-[11px] font-black text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-0.5 rounded-full shadow-2xs"
                            >
                                <Star class="w-3 h-3 fill-amber-500 text-amber-500" />
                                Featured Property
                            </span>
                            <span
                                v-if="property.is_verified"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full"
                            >
                                <Check class="w-3 h-3 text-blue-600" :stroke-width="3" />
                                Verified Listing
                            </span>
                        </div>
                        <h1 class="text-xl font-black text-slate-900 leading-tight">{{ property.title }}</h1>
                        <p class="text-sm text-slate-500 mt-1 flex items-center gap-1.5 flex-wrap">
                            <MapPin class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" />
                            <span>
                                {{ [property.address, property.sub_locality, property.locality, property.city].filter(Boolean).join(', ') }}
                                <strong v-if="property.pincode" class="text-slate-800 font-bold"> - {{ property.pincode }}</strong>
                                <span v-if="property.landmark" class="text-slate-400"> · Near {{ property.landmark }}</span>
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="shareProperty"
                            class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95"
                            title="Share property via system share"
                        >
                            <Share2 class="w-4 h-4 text-slate-500" />
                            <span>Share</span>
                        </button>
                        <span v-if="copied" class="text-[11px] text-emerald-600 font-bold">✓ Copied!</span>
                    </div>
                </div>
            </div>

            <!-- Details Grid: Property Overview + Amenities + Description -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">

                    <!-- Property Overview -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2.5">Property Overview</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div v-for="spec in propertySpecs" :key="spec.label" class="space-y-0.5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ spec.label }}</div>
                                <div class="text-sm font-bold text-slate-900">{{ spec.value }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="property.description" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2.5">About This Property</h2>
                        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line" :class="descExpanded ? '' : 'line-clamp-4'">
                            {{ property.description }}
                        </p>
                        <button
                            v-if="property.description.length > 220"
                            @click="descExpanded = !descExpanded"
                            class="text-xs font-bold text-blue-600 hover:underline cursor-pointer"
                        >
                            {{ descExpanded ? 'Show Less ↑' : 'Read More ↓' }}
                        </button>
                    </div>

                    <!-- Video Tour & Walkthrough Section -->
                    <div v-if="property.videos && property.videos.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                            <div class="flex items-center gap-2">
                                <Video class="w-4 h-4 text-purple-600" />
                                <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">Property Video Tour</h2>
                                <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                    {{ property.videos.length }} {{ property.videos.length === 1 ? 'Video' : 'Videos' }}
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="openVideoGallery(0)"
                                class="text-xs font-bold text-purple-600 hover:text-purple-700 hover:underline flex items-center gap-1 cursor-pointer"
                            >
                                <span>Watch Fullscreen</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(videoUrl, vIdx) in property.videos"
                                :key="vIdx"
                                class="bg-slate-900 rounded-xl overflow-hidden shadow-sm relative group border border-slate-800"
                            >
                                <video
                                    :src="videoUrl"
                                    controls
                                    playsinline
                                    preload="metadata"
                                    class="w-full aspect-video object-contain bg-black"
                                ></video>
                                <div class="px-3 py-2 bg-slate-800/95 text-white flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-200 truncate flex items-center gap-1.5">
                                        <span>Tour {{ vIdx + 1 }}</span>
                                    </span>
                                    <button
                                        type="button"
                                        @click="openVideoGallery(vIdx)"
                                        class="text-purple-300 hover:text-white font-bold text-[11px] flex items-center gap-1 cursor-pointer transition"
                                    >
                                        <span>Expand</span>
                                        <Maximize2 class="w-3 h-3" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div v-if="property.amenities && property.amenities.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2.5">Society & Amenities</h2>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="amenity in property.amenities"
                                :key="amenity"
                                class="px-3 py-1.5 bg-blue-50 text-blue-800 border border-blue-100 rounded-xl text-xs font-semibold flex items-center gap-1.5"
                            >
                                <LucideIcon :name="amenityIcon(amenity)" :size="14" class="text-blue-600 shrink-0" />
                                <span>{{ amenity }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Map Location & Neighborhood Pin -->
                    <div v-if="property.latitude && property.longitude" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">Map &amp; Neighborhood Location</h2>
                            <span class="text-xs font-mono font-bold text-slate-500">{{ Number(property.latitude).toFixed(4) }}° N, {{ Number(property.longitude).toFixed(4) }}° E</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <MapPin class="w-4 h-4 text-rose-500 shrink-0" />
                                    <span>{{ property.project_name ? `${property.project_name}, ` : '' }}{{ property.locality }}, {{ property.city }}{{ property.pincode ? ' - ' + property.pincode : '' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Precision geocoded location coordinates saved for this property.
                                </div>
                            </div>
                            <a
                                :href="`https://www.google.com/maps/search/?api=1&query=${property.latitude},${property.longitude}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5 shrink-0"
                            >
                                <span>Open in Maps &rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- Home Loan & EMI Assistance Box -->
                    <div
                        v-if="(property.property_for === PROPERTY_FOR.SELL || !property.property_for) && property.expected_price > 0"
                        class="bg-gradient-to-r from-blue-50/70 via-indigo-50/50 to-slate-50 rounded-2xl border border-blue-200/70 shadow-xs p-5 space-y-4"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-blue-100 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-xs">
                                    <Landmark class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Home Loan &amp; EMI Assistance</h2>
                                    <p class="text-xs text-slate-500">Calculate customized monthly installments with lowest interest rates</p>
                                </div>
                            </div>
                            <span class="self-start sm:self-auto text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Rates From 8.40% p.a.
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Estimated EMI</span>
                                <div class="text-base sm:text-lg font-black text-slate-900">{{ estimatedMonthlyEmiFormatted }}<span class="text-xs font-normal text-slate-500">/mo</span></div>
                                <span class="text-[10px] text-slate-500 block">for 20 Yrs @ 8.5% (20% Down)</span>
                            </div>
                            <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Max Loan Coverage</span>
                                <div class="text-base sm:text-lg font-black text-blue-700">Up to 80%</div>
                                <span class="text-[10px] text-slate-500 block">of property agreement value</span>
                            </div>
                            <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Top Lending Partners</span>
                                <div class="text-xs font-bold text-slate-800 truncate">SBI · HDFC · ICICI · Axis</div>
                                <span class="text-[10px] text-emerald-600 font-bold block">✓ Zero Processing Fee Offers</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                            <p class="text-xs text-slate-600">
                                Want to customize loan tenure, interest rate, or down payment?
                            </p>
                            <button
                                type="button"
                                @click="isEmiModalOpen = true"
                                class="px-4 py-2 rounded-xl text-white font-bold text-xs shadow-md transition cursor-pointer hover:opacity-95 flex items-center justify-center gap-1.5 shrink-0"
                                :style="{ backgroundColor: brandPrimaryColor }"
                            >
                                <Calculator class="w-3.5 h-3.5" />
                                <span>Open Full EMI Calculator</span>
                                <ChevronRight class="w-3.5 h-3.5" :stroke-width="2.5" />
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Seller card repeat on mobile + Similar properties hint -->
                <div class="space-y-5">
                    <!-- Posted By Details -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2.5">Posted By</h2>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-base flex-shrink-0 shadow-md" :style="{ backgroundColor: brandPrimaryColor }">
                                {{ sellerInitials }}
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-900">{{ property.user?.name || 'Verified Owner' }}</div>
                                <div class="text-[11px] text-slate-500 capitalize">{{ property.user_type || 'Owner' }}</div>
                                <div class="text-[11px] text-emerald-600 font-bold mt-0.5">✓ Verified by {{ companyName }}</div>
                            </div>
                        </div>
                        <button
                            @click="showContactModal = true"
                            class="w-full py-2.5 rounded-xl text-white font-bold text-xs transition cursor-pointer shadow-xs hover:opacity-95"
                            :style="{ backgroundColor: brandPrimaryColor }"
                        >
                            Contact Seller
                        </button>
                    </div>

                    <!-- Property Quick Facts -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2.5">Quick Facts</h2>
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-semibold">Listed On</span>
                                <span class="font-bold text-slate-800">{{ listedDate }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-semibold">Property For</span>
                                <span class="font-bold text-slate-800">{{ property.property_for === 'Sell' ? 'Sale' : property.property_for }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-semibold">Property Type</span>
                                <span class="font-bold text-slate-800">{{ property.property_type }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-semibold">Maintenance</span>
                                <span class="font-bold text-slate-800">
                                    {{ property.maintenance_charge ? '₹ ' + Number(property.maintenance_charge).toLocaleString('en-IN') + '/mo' : 'Included' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Contact Modal -->
        <div v-if="showContactModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showContactModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl space-y-5 z-10">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900">Contact Seller</h3>
                    <button @click="showContactModal = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer text-sm">✕</button>
                </div>
                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-base flex-shrink-0 shadow-md" :style="{ backgroundColor: brandPrimaryColor }">
                        {{ sellerInitials }}
                    </div>
                    <div>
                        <div class="text-sm font-black text-slate-900">{{ property?.user?.name || 'Verified Owner' }}</div>
                        <div class="text-[11px] text-emerald-600 font-bold">✓ Verified Seller</div>
                    </div>
                </div>
                <div v-if="property?.user?.mobile" class="space-y-2.5">
                    <a
                        :href="'tel:' + (property.user.country_code || '+91') + property.user.mobile"
                        class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-2xl text-white font-bold text-sm transition cursor-pointer shadow-md hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        <Phone class="w-4 h-4" />
                        <span>{{ property.user.country_code || '+91' }} {{ property.user.mobile }}</span>
                    </a>
                </div>
                <p class="text-[10px] text-slate-400 text-center">
                    By contacting, you agree to {{ companyName }}'s <router-link to="/terms" class="underline hover:text-slate-600">Terms of Use</router-link> and <router-link to="/privacy" class="underline hover:text-slate-600">Privacy Policy</router-link>.
                </p>
            </div>
        </div>

        <!-- EMI Calculator Modal -->
        <EmiCalculatorModal
            :is-open="isEmiModalOpen"
            :property-price="property?.expected_price"
            :property-title="property?.title"
            :property-city="property?.city"
            @close="isEmiModalOpen = false"
        />

        <!-- Fullscreen Image Library / Gallery Modal -->
        <ImageGalleryModal
            :is-open="isGalleryOpen"
            :property="property"
            :initial-index="activePhotoIdx"
            :initial-media-type="galleryMediaType"
            @close="isGalleryOpen = false"
            @update:is-open="isGalleryOpen = $event"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';

definePageMeta({
    name: 'client.property-detail',
});
import {
    Share2,
    Star,
    Check,
    ChevronLeft,
    ChevronRight,
    Play,
    Camera,
    Video,
    BadgeCheck,
    Phone,
    MessageSquare,
    MapPin,
    Maximize2,
    ShieldCheck,
    Calculator,
    Landmark,
} from 'lucide-vue-next';
import EmiCalculatorModal from '~/components/EmiCalculatorModal.vue';
import ImageGalleryModal from '~/components/ImageGalleryModal.vue';
import {
    PROPERTY_FOR,
    isResidentialType,
    isCommercialType,
    isPlotType,
    getPropertyIntentLabel,
} from '~/utils/propertyConstants';

const { companyName, brandPrimaryColor, brandAccentColor } = useCompanyBranding();
const route = useRoute();
const router = useRouter();

const slug = computed(() => (route.params.slug as string) || '');

const { data: propertyData, status, error } = useLazyAsyncData(
    `property_detail_${route.params.slug}`,
    async () => {
        const s = route.params.slug;
        if (!s) return null;
        const { get } = useApi();
        try {
            const res: any = await get(`/api/property/${s}`);
            const prop = res?.property ?? res;
            if (prop && (prop.title || prop.id)) {
                return prop;
            }
            return null;
        } catch (e: any) {
            if (e?.name === 'AbortError' || e?.isAbort) {
                return null;
            }
            return null;
        }
    },
    {
        lazy: true,
        watch: [() => route.params.slug],
    }
);

const property = computed(() => propertyData.value);
const isLoading = computed(() => status.value === 'pending');
const notFound = computed(() => (!property.value || !property.value.title) && status.value !== 'pending');

const { logPropertyView, logContacted } = useUserActivity();

const handleContactClick = (type = 'phone') => {
    showContactModal.value = true;
    if (property.value) {
        logContacted(property.value, type);
    }
};

watch(
    () => property.value,
    (val) => {
        if (val && val.id) {
            logPropertyView(val);
        }
    }
);

onMounted(() => {
    if (property.value && property.value.id) {
        logPropertyView(property.value);
    }
});

useSeoMeta({
    title: () => property.value?.title ? `${property.value.title}${property.value.locality ? ' in ' + property.value.locality : ''}${property.value.city ? ', ' + property.value.city : ''}` : 'Property Details',
    description: () => property.value?.title ? `${property.value.description || property.value.title}. Expected price: ₹ ${property.value.expected_price || 'On Request'}. Verified listing on RealHem.` : 'Explore verified real estate listing on RealHem.',
    ogTitle: () => property.value?.title ? `${property.value.title}${property.value.locality ? ' in ' + property.value.locality : ''}${property.value.city ? ', ' + property.value.city : ''}` : 'Property Details',
    ogDescription: () => property.value?.title ? `${property.value.description || property.value.title}. Expected price: ₹ ${property.value.expected_price || 'On Request'}. Verified listing on RealHem.` : 'Explore verified real estate listing on RealHem.',
    ogImage: () => property.value?.photos?.[0] || undefined,
});

const activePhotoIdx = ref(0);
const isGalleryOpen = ref(false);
const galleryMediaType = ref<'photos' | 'videos'>('photos');

const openPhotoGallery = (idx = 0) => {
    galleryMediaType.value = 'photos';
    activePhotoIdx.value = idx;
    isGalleryOpen.value = true;
};

const openVideoGallery = (vIdx = 0) => {
    galleryMediaType.value = 'videos';
    const photoCount = Array.isArray(property.value?.photos) ? property.value.photos.length : 0;
    activePhotoIdx.value = photoCount + vIdx;
    isGalleryOpen.value = true;
};

const showContactModal = ref(false);
const isEmiModalOpen = ref(false);
const descExpanded = ref(false);
const copied = ref(false);

const amenityIconMap: Record<string, string> = {
    'Lift': 'ArrowUpDown',
    '24x7 Security': 'ShieldCheck',
    'Reserved Parking': 'Car',
    'Gym': 'Dumbbell',
    'Swimming Pool': 'Waves',
    'Power Backup': 'Zap',
    'Clubhouse': 'Building2',
    'Piped Gas': 'Flame',
    'Children Play Area': 'Smile',
    'Park / Garden': 'Trees',
    'Intercom': 'PhoneCall',
    'CCTV Surveillance': 'Cctv',
};

const amenityIcon = (name: string) => amenityIconMap[name] ?? 'CheckCircle2';

const formatPrice = (val) => {
    if (!val || val <= 0) return 'Price on Request';
    const num = Number(val);
    if (num >= 10000000) {
        const cr = (num / 10000000).toFixed(2);
        return `₹ ${cr.replace(/\.00$/, '')} Crore`;
    }
    if (num >= 100000) {
        const lk = (num / 100000).toFixed(2);
        return `₹ ${lk.replace(/\.00$/, '')} Lakh`;
    }
    return `₹ ${num.toLocaleString('en-IN')}`;
};

const estimatedMonthlyEmiFormatted = computed(() => {
    const price = Number(property.value?.expected_price);
    if (!price || price <= 0) return '₹ 0';
    // Standard baseline: 20% down payment (80% loan), 8.5% interest, 20 years
    const P = price * 0.8;
    const r = 0.085 / 12;
    const n = 240;
    const factor = Math.pow(1 + r, n);
    const emi = Math.round((P * r * factor) / (factor - 1));
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(emi);
});

const sellerInitials = computed(() => {
    const name = property.value?.user?.name;
    if (!name) return 'V';
    const parts = name.trim().split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
});

const listedDate = computed(() => {
    if (!property.value?.created_at) return '—';
    return new Date(property.value.created_at).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
});

const isResidential = computed(() => {
    if (!property.value?.property_type) return true;
    return isResidentialType(property.value.property_type);
});

const isCommercial = computed(() => {
    if (!property.value?.property_type) return false;
    return isCommercialType(property.value.property_type);
});

const isPlot = computed(() => {
    if (!property.value?.property_type) return false;
    return isPlotType(property.value.property_type);
});

const propertySpecs = computed(() => {
    if (!property.value) return [];
    const p = property.value;

    const specs = [];
    if (isResidential.value) {
        if (p.bedrooms) specs.push({ label: 'Bedrooms', value: `${p.bedrooms} BHK` });
        if (p.bathrooms) specs.push({ label: 'Bathrooms', value: p.bathrooms });
        if (p.balconies) specs.push({ label: 'Balconies', value: p.balconies });
    } else if (isCommercial.value) {
        specs.push({ label: 'Washroom', value: p.bathrooms > 0 ? `${p.bathrooms} Washroom(s)` : 'Available / Shared' });
    }

    if (p.carpet_area) specs.push({ label: isPlot.value ? 'Plot Area' : 'Carpet Area', value: `${Number(p.carpet_area).toLocaleString('en-IN')} sq.ft` });
    if (p.super_builtup_area) specs.push({ label: 'Super Built-up Area', value: `${Number(p.super_builtup_area).toLocaleString('en-IN')} sq.ft` });
    if (!isPlot.value && (p.floor_no || p.total_floors)) {
        specs.push({ label: 'Floor', value: p.floor_no && p.total_floors ? `${p.floor_no} of ${p.total_floors}` : (p.floor_no || '—') });
    }
    if (!isPlot.value && p.furnishing_status) specs.push({ label: 'Furnishing', value: p.furnishing_status });
    if (p.facing) specs.push({ label: 'Facing', value: p.facing });
    if (p.construction_status) specs.push({ label: isPlot.value ? 'Possession' : 'Status', value: p.construction_status });
    if (p.project_name) specs.push({ label: 'Project / Society', value: p.project_name });
    if (p.pincode) specs.push({ label: 'Pincode', value: p.pincode });
    if (p.latitude && p.longitude) {
        specs.push({ label: 'Coordinates', value: `${Number(p.latitude).toFixed(4)}° N, ${Number(p.longitude).toFixed(4)}° E` });
    }

    return specs.filter((s) => s.value && s.value !== '—');
});

const prevPhoto = () => {
    if (!property.value?.photos?.length) return;
    activePhotoIdx.value = (activePhotoIdx.value - 1 + property.value.photos.length) % property.value.photos.length;
};

const nextPhoto = () => {
    if (!property.value?.photos?.length) return;
    activePhotoIdx.value = (activePhotoIdx.value + 1) % property.value.photos.length;
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        // ignore
    }
};

const shareProperty = async () => {
    const url = typeof window !== 'undefined' ? window.location.href : '';
    const p = property.value;
    const title = p?.title || 'RealHem Property';
    const loc = [p?.locality, p?.city].filter(Boolean).join(', ');
    const price = p?.price || '';
    const text = `Check out this property on RealHem: ${title}${loc ? ` in ${loc}` : ''}${price ? ` for ${price}` : ''}`;

    if (typeof navigator !== 'undefined' && navigator.share) {
        try {
            await navigator.share({
                title,
                text,
                url,
            });
            return;
        } catch (err: any) {
            // User closed or cancelled the native share sheet
            if (err?.name === 'AbortError') return;
        }
    }

    // Fallback if browser does not support navigator.share
    await copyLink();
};

</script>

