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
            <div class="text-5xl">🏚️</div>
            <h2 class="text-2xl font-black text-slate-900">Property Not Found</h2>
            <p class="text-sm text-slate-500">This listing may have been removed or the link is incorrect.</p>
            <button @click="router.push('/listings')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer">
                Browse All Properties
            </button>
        </div>

        <!-- Main Content -->
        <div v-else-if="property" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500">
                <button @click="router.push('/')" class="hover:underline font-semibold transition cursor-pointer" :style="{ color: brandPrimaryColor }">Home</button>
                <span>›</span>
                <button @click="router.push('/listings?city=' + property.city)" class="hover:underline font-semibold transition cursor-pointer" :style="{ color: brandPrimaryColor }">{{ property.city }}</button>
                <span>›</span>
                <button @click="router.push('/listings?city=' + property.city + '&keyword=' + property.locality)" class="hover:underline font-semibold transition cursor-pointer" :style="{ color: brandPrimaryColor }">{{ property.locality }}</button>
                <span>›</span>
                <span class="text-slate-800 font-semibold line-clamp-1">{{ property.title }}</span>
            </nav>

            <!-- Hero: Photo Gallery + Quick Info -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Photo Gallery -->
                <div class="lg:col-span-2 space-y-2">
                    <!-- Main Photo -->
                    <div class="relative rounded-2xl overflow-hidden bg-slate-900 aspect-video">
                        <img
                            v-if="property.photos && property.photos.length > 0"
                            :src="property.photos[activePhotoIdx]"
                            :alt="property.title"
                            class="absolute inset-0 w-full h-full object-cover"
                        />
                        <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 gap-2">
                            <span class="text-5xl">🏠</span>
                            <p class="text-sm font-semibold">No photos available</p>
                        </div>

                        <!-- Overlay badges -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 z-10">
                            <span v-if="property.is_verified" class="px-2 py-0.5 rounded-lg text-white font-black text-[10px] uppercase tracking-wide backdrop-blur-sm shadow-xs" :style="{ backgroundColor: brandPrimaryColor }">
                                RERA Verified
                            </span>
                            <span class="px-2 py-0.5 rounded-lg bg-slate-900/80 text-amber-300 font-bold text-[10px] uppercase tracking-wide backdrop-blur-sm">
                                ZERO BROKERAGE
                            </span>
                        </div>

                        <!-- For Sale / Rent badge -->
                        <div class="absolute top-3 right-3 z-10">
                            <span :class="property.property_for === PROPERTY_FOR.SELL ? 'bg-emerald-600/90 text-white' : 'bg-orange-500/90 text-white'"
                                class="px-2.5 py-1 rounded-xl font-black text-[11px] uppercase tracking-wide backdrop-blur-sm">
                                For {{ getPropertyIntentLabel(property.property_for) }}
                            </span>
                        </div>

                        <!-- Photo navigation arrows (only when >1 photo) -->
                        <template v-if="property.photos && property.photos.length > 1">
                            <button
                                @click="prevPhoto"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-900/70 text-white flex items-center justify-center hover:bg-slate-900 transition cursor-pointer backdrop-blur-sm z-10"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button
                                @click="nextPhoto"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-900/70 text-white flex items-center justify-center hover:bg-slate-900 transition cursor-pointer backdrop-blur-sm z-10"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </template>

                        <!-- Photo count -->
                        <div class="absolute bottom-3 right-3 bg-slate-900/70 text-white text-[11px] font-bold px-2.5 py-1 rounded-lg backdrop-blur-sm z-10">
                            {{ activePhotoIdx + 1 }} / {{ property.photos?.length || 1 }}
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
                            <div class="text-2xl font-black" :style="{ color: brandPrimaryColor }">{{ formatPrice(property.expected_price) }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                <span v-if="property.price_per_sqft">₹ {{ Number(property.price_per_sqft).toLocaleString('en-IN') }}/sq.ft</span>
                                <span v-if="property.price_negotiable" class="ml-2 text-emerald-600 font-bold">• Price Negotiable</span>
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
                                <div class="text-sm font-bold text-slate-900 truncate">{{ property.user?.name || 'Verified Owner' }}</div>
                                <div class="text-[11px] text-slate-500 capitalize">{{ property.user_type || 'Owner' }}</div>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0" title="Online"></span>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="space-y-2.5">
                            <button
                                @click="showContactModal = true"
                                class="w-full py-3 rounded-2xl text-white font-black text-sm shadow-md transition cursor-pointer flex items-center justify-center gap-2 hover:opacity-95"
                                :style="{ backgroundColor: brandPrimaryColor }"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                View Contact Number
                            </button>
                            <button
                                @click="showContactModal = true"
                                class="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-sm transition cursor-pointer flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                Send Message
                            </button>
                        </div>

                        <!-- Safe dealing note -->
                        <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                            🔒 100% safe to connect. Verified listing by {{ companyName }}.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Title + Location Bar -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-1">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h1 class="text-xl font-black text-slate-900 leading-tight">{{ property.title }}</h1>
                        <p class="text-sm text-slate-500 mt-1 flex items-center gap-1.5 flex-wrap">
                            <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>
                                {{ [property.address, property.sub_locality, property.locality, property.city].filter(Boolean).join(', ') }}
                                <strong v-if="property.pincode" class="text-slate-800 font-bold"> - {{ property.pincode }}</strong>
                                <span v-if="property.landmark" class="text-slate-400"> · Near {{ property.landmark }}</span>
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="copyLink" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:border-blue-300 transition cursor-pointer" title="Copy link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                        <span v-if="copied" class="text-[11px] text-emerald-600 font-bold">Copied!</span>
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

                    <!-- Amenities -->
                    <div v-if="property.amenities && property.amenities.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-2.5">Society & Amenities</h2>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="amenity in property.amenities"
                                :key="amenity"
                                class="px-3 py-1.5 bg-blue-50 text-blue-800 border border-blue-100 rounded-xl text-xs font-semibold flex items-center gap-1.5"
                            >
                                <span>{{ amenityIcon(amenity) }}</span>
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
                                    <span>📍</span>
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
                        class="flex items-center gap-3 w-full py-3 px-4 rounded-2xl text-white font-bold text-sm transition cursor-pointer shadow-md hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ property.user.country_code || '+91' }} {{ property.user.mobile }}
                    </a>
                </div>
                <p class="text-[10px] text-slate-400 text-center">By contacting, you agree to {{ companyName }}'s Terms of Use.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCompanyBranding } from '../composables/useCompanyBranding';
import {
    PROPERTY_FOR,
    isResidentialType,
    isCommercialType,
    isPlotType,
    getPropertyIntentLabel,
} from '../constants/propertyConstants';

const { companyName, brandPrimaryColor, brandAccentColor } = useCompanyBranding();

const route = useRoute();
const router = useRouter();

const property = ref(null);
const isLoading = ref(true);
const notFound = ref(false);
const activePhotoIdx = ref(0);
const showContactModal = ref(false);
const descExpanded = ref(false);
const copied = ref(false);

const amenityIconMap = {
    'Lift': '🛗',
    '24x7 Security': '👮',
    'Reserved Parking': '🚗',
    'Gym': '🏋️',
    'Swimming Pool': '🏊',
    'Power Backup': '⚡',
    'Clubhouse': '🏛️',
    'Piped Gas': '🔥',
    'Children Play Area': '🎠',
    'Park / Garden': '🌳',
    'Intercom': '📞',
    'CCTV Surveillance': '📹',
};

const amenityIcon = (name) => amenityIconMap[name] ?? '✓';

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

const fetchProperty = async () => {
    isLoading.value = true;
    notFound.value = false;
    try {
        const res = await fetch(`/api/property/${route.params.slug}`);
        if (res.status === 404) {
            notFound.value = true;
            return;
        }
        if (!res.ok) {
            notFound.value = true;
            return;
        }
        const data = await res.json();
        property.value = data.property ?? data;
    } catch {
        notFound.value = true;
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchProperty();
});
</script>

