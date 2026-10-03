<template>
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800">
        <!-- Top Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            v-if="companyLogoUrl"
                            class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center border border-slate-700 bg-white"
                        >
                            <img :src="companyLogoUrl" :alt="companyName" class="w-full h-full object-contain" />
                        </div>
                        <div
                            v-else
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-md"
                            :style="{ backgroundColor: brandPrimaryColor || '#005ca8' }"
                        >
                            {{ companyShortName }}
                        </div>
                        <span class="font-black text-white text-xl tracking-tight">
                            {{ companyName }}<span :style="{ color: brandPrimaryColor || '#005ca8' }">.</span>
                        </span>
                    </div>

                    <p class="text-slate-400 text-xs leading-relaxed max-w-sm">
                        {{ footerAbout }}
                    </p>

                    <!-- Contact Details -->
                    <div class="space-y-1 text-[11px] text-slate-300 pt-1">
                        <p v-if="companyPhone" class="flex items-center gap-2">
                            <span>📞 Toll-Free:</span>
                            <a :href="`tel:${companyPhone}`" class="text-blue-400 font-bold hover:underline">{{ companyPhone }}</a>
                        </p>
                        <p v-if="companyEmail" class="flex items-center gap-2">
                            <span>✉️ Email:</span>
                            <a :href="`mailto:${companyEmail}`" class="text-blue-400 font-semibold hover:underline">{{ companyEmail }}</a>
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <a href="/admin/login" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold transition border border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Admin Console SPA &rarr;
                        </a>
                    </div>
                </div>

                <!-- Col 2: Real Estate in Cities -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Top Cities</h4>
                    <ul class="space-y-2">
                        <li><router-link to="/listings?city=Delhi NCR" class="hover:text-white transition">Property in Delhi NCR</router-link></li>
                        <li><router-link to="/listings?city=Ahmedabad" class="hover:text-white transition">Property in Ahmedabad</router-link></li>
                        <li><router-link to="/listings?city=Mumbai" class="hover:text-white transition">Property in Mumbai</router-link></li>
                        <li><router-link to="/listings?city=Bangalore" class="hover:text-white transition">Property in Bangalore</router-link></li>
                        <li><router-link to="/listings?city=Pune" class="hover:text-white transition">Property in Pune</router-link></li>
                    </ul>
                </div>

                <!-- Col 3: Property Categories -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Categories</h4>
                    <ul class="space-y-2">
                        <li><router-link to="/listings?type=buy" class="hover:text-white transition">Flats &amp; Apartments for Sale</router-link></li>
                        <li><router-link to="/listings?type=rent" class="hover:text-white transition">House &amp; Flat for Rent</router-link></li>
                        <li><router-link to="/listings?type=commercial" class="hover:text-white transition">Commercial Shops &amp; Offices</router-link></li>
                        <li><router-link to="/listings?type=plots" class="hover:text-white transition">Residential Land &amp; Plots</router-link></li>
                        <li><router-link to="/post-property" class="hover:text-white transition">Post Free Property Ad</router-link></li>
                    </ul>
                </div>

                <!-- Col 4: For Partners & Builders -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Partners</h4>
                    <ul class="space-y-2">
                        <li><router-link to="/brokers" class="hover:text-white transition">Real Estate Agents / Brokers</router-link></li>
                        <li><router-link to="/brokers" class="hover:text-white transition">Builders &amp; Developers</router-link></li>
                        <li><router-link to="/brokers" class="hover:text-white transition">Lead Generation Packages</router-link></li>
                        <li><a href="/admin/login" class="hover:text-white transition">Admin Portal Access</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="border-t border-slate-800 py-6 px-4 text-center text-slate-500 text-[11px]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>{{ copyrightText }}</span>
                <span class="text-slate-400">Powered by {{ companyName }} Platform</span>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { useCompanyBranding } from '../composables/useCompanyBranding';

const {
    companyName,
    companyShortName,
    companyLogoUrl,
    brandPrimaryColor,
    companyPhone,
    companyEmail,
    footerAbout,
    copyrightText,
} = useCompanyBranding();
</script>
