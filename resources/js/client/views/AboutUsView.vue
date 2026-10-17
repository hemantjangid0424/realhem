<template>
    <div class="space-y-12 pb-16">
        <!-- Hero Section -->
        <section class="relative overflow-hidden text-white py-16 sm:py-24" :style="{ background: heroGradient }">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-black tracking-wide uppercase bg-white/10 backdrop-blur-md border border-white/20">
                    <span>🏢</span>
                    <span>About {{ companyName }}</span>
                </div>
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Transforming How India <br class="hidden sm:inline" />Discovers &amp; Transacts Real Estate
                </h1>
                <p class="max-w-2xl mx-auto text-sm sm:text-base text-white/80 leading-relaxed font-normal">
                    {{ footerAbout }}
                </p>
                <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                    <router-link
                        to="/listings"
                        class="px-6 py-3 rounded-xl bg-white text-slate-900 font-extrabold text-xs sm:text-sm shadow-lg hover:bg-slate-100 transition"
                    >
                        Explore 5,00,000+ Listings &rarr;
                    </router-link>
                    <router-link
                        to="/post-property"
                        class="px-6 py-3 rounded-xl bg-white/15 hover:bg-white/25 text-white font-extrabold text-xs sm:text-sm border border-white/30 backdrop-blur-md transition"
                    >
                        Post Free Property
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Stats Counter Ribbon -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl shadow-slate-900/5">
                <div v-for="stat in stats" :key="stat.label" class="text-center space-y-1 p-2">
                    <p class="text-2xl sm:text-4xl font-black text-slate-900" :style="{ color: brandPrimaryColor }">{{ stat.value }}</p>
                    <p class="text-xs sm:text-sm font-bold text-slate-800">{{ stat.label }}</p>
                    <p class="text-[11px] text-slate-400 font-medium">{{ stat.detail }}</p>
                </div>
            </div>
        </section>

        <!-- Mission & Vision Cards -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Our Core Mission &amp; Purpose</h2>
                <p class="text-xs sm:text-sm text-slate-500">Democratizing property information, eliminating ghost listings, and ensuring safety for every rupee invested.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xs space-y-4 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                        🎯
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Zero Ghost Listings</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        We actively audit and verify listings through geotagged photos, utility bill verification, and direct phone confirmations to ensure buyers never waste time on phantom properties.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xs space-y-4 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                        🛡️
                    </div>
                    <h3 class="text-lg font-black text-slate-900">RERA Compliance First</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Every commercial and residential project links with State RERA databases (GujRERA, MahaRERA, HRERA, UP-RERA), displaying authentic carpet areas and escrow account requirements.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xs space-y-4 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                        ⚡
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Direct Owner Connect</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Empowering individual home owners to list 100% free of charge and enabling buyers &amp; tenants to negotiate directly with zero intermediate brokerage markups.
                    </p>
                </div>
            </div>
        </section>

        <!-- Our Journey / Architecture -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-900 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden space-y-8">
                <div class="max-w-2xl space-y-3 relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-blue-200 border border-white/15">
                        <span>🚀</span>
                        <span>Next-Generation PropTech Infrastructure</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">Built on Speed, Geospatial Intelligence &amp; Data Integrity</h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Our cloud platform indexes millions of location data points, nearby landmark coordinates, and verified pricing trends to deliver instant search responses under 100ms.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-2 backdrop-blur-xs">
                        <span class="text-xl">📍</span>
                        <h4 class="text-sm font-extrabold text-white">Hyperlocal Geo-Search</h4>
                        <p class="text-[11px] text-slate-400">Micro-market neighborhood searches with precise radius bounds and landmark proximity.</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-2 backdrop-blur-xs">
                        <span class="text-xl">📊</span>
                        <h4 class="text-sm font-extrabold text-white">Price Trend Engine</h4>
                        <p class="text-[11px] text-slate-400">Quarterly locality price shifts and historical transaction benchmark analytics.</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-2 backdrop-blur-xs">
                        <span class="text-xl">🔐</span>
                        <h4 class="text-sm font-extrabold text-white">Safe Mobile OTP Access</h4>
                        <p class="text-[11px] text-slate-400">Frictionless 6-digit OTP verification safeguarding buyer numbers from spam.</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-2 backdrop-blur-xs">
                        <span class="text-xl">🤝</span>
                        <h4 class="text-sm font-extrabold text-white">Enterprise Broker Portal</h4>
                        <p class="text-[11px] text-slate-400">Dedicated lead CRM, inventory management, and instant response tools for certified brokers.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Callout -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-2 text-center md:text-left">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900">Have questions or want to partner with us?</h3>
                    <p class="text-xs sm:text-sm text-slate-500">Reach out to our leadership or corporate development teams today.</p>
                </div>
                <div class="flex items-center gap-3 shrink-0 flex-wrap justify-center">
                    <router-link
                        to="/contact-us"
                        class="px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-md transition hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Contact Our Team
                    </router-link>
                    <router-link
                        to="/careers"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition"
                    >
                        Explore Open Careers &rarr;
                    </router-link>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const { companyName, footerAbout, brandPrimaryColor, heroGradient } = useCompanyBranding();

const stats = [
    { label: 'Active Properties', value: '5,00,000+', detail: 'Verified residential & commercial' },
    { label: 'Cities Covered', value: '45+ Cities', detail: 'Tier 1 & Tier 2 prime metros' },
    { label: 'Registered Brokers', value: '25,000+', detail: 'RERA certified channel partners' },
    { label: 'Happy Home Buyers', value: '1.2 Million+', detail: 'Assisted in finding their dream home' },
];
</script>

