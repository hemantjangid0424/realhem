<template>
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800">
        <!-- Top Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8">
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            v-if="companyLogoUrl"
                            class="h-9 max-w-[160px] rounded-xl overflow-hidden flex items-center justify-center p-1 bg-white/10"
                        >
                            <img :src="companyLogoUrl" :alt="companyName" class="max-h-8 max-w-full object-contain" />
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

                <!-- Col 2: Company (99acres standard 12 items) -->
                <div class="lg:col-span-3">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Company</h4>
                    <ul class="grid grid-cols-1 gap-1.5 text-xs">
                        <li><router-link to="/about-us" class="hover:text-white transition">About us</router-link></li>
                        <li><router-link to="/contact-us" class="hover:text-white transition">Contact us</router-link></li>
                        <li><router-link to="/careers" class="hover:text-white transition">Careers with us</router-link></li>
                        <li><router-link to="/terms" class="hover:text-white transition">Terms &amp; Conditions</router-link></li>
                        <li><router-link to="/request-info" class="hover:text-white transition">Request Info</router-link></li>
                        <li><router-link to="/feedback" class="hover:text-white transition">Feedback</router-link></li>
                        <li><router-link to="/report-problem" class="hover:text-white transition">Report a problem</router-link></li>
                        <li><router-link to="/testimonials" class="hover:text-white transition">Testimonials</router-link></li>
                        <li><router-link to="/privacy" class="hover:text-white transition">Privacy Policy</router-link></li>
                        <li><router-link to="/safety-guide" class="hover:text-white transition">Safety Guide</router-link></li>
                    </ul>
                </div>

                <!-- Col 3: Real Estate in Cities -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Top Cities</h4>
                    <ul class="space-y-2">
                        <li><router-link to="/listings?city=Delhi NCR" class="hover:text-white transition">Delhi NCR</router-link></li>
                        <li><router-link to="/listings?city=Ahmedabad" class="hover:text-white transition">Ahmedabad</router-link></li>
                        <li><router-link to="/listings?city=Mumbai" class="hover:text-white transition">Mumbai</router-link></li>
                        <li><router-link to="/listings?city=Bangalore" class="hover:text-white transition">Bangalore</router-link></li>
                        <li><router-link to="/listings?city=Pune" class="hover:text-white transition">Pune</router-link></li>
                        <li><router-link to="/listings?city=Hyderabad" class="hover:text-white transition">Hyderabad</router-link></li>
                    </ul>
                </div>

                <!-- Col 4: Property Categories -->
                <div class="lg:col-span-3">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Explore Marketplace</h4>
                    <ul class="space-y-2">
                        <li><router-link to="/listings?type=buy" class="hover:text-white transition">Flats &amp; Apartments for Sale</router-link></li>
                        <li><router-link to="/listings?type=rent" class="hover:text-white transition">House &amp; Flat for Rent</router-link></li>
                        <li><router-link to="/listings?type=commercial" class="hover:text-white transition">Commercial Shops &amp; Offices</router-link></li>
                        <li><router-link to="/listings?type=plots" class="hover:text-white transition">Residential Land &amp; Plots</router-link></li>
                        <li><router-link to="/post-property" class="hover:text-white transition">Post Free Property Ad</router-link></li>
                        <li><router-link to="/brokers" class="hover:text-white transition">Broker &amp; Builder Network</router-link></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Copyright & Legal Links -->
        <div class="border-t border-slate-800 py-6 px-4 text-center text-slate-500 text-[11px]">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                <span>{{ copyrightText }}</span>

                <!-- Legal policy inline links -->
                <div class="flex items-center gap-4 flex-wrap justify-center text-[11px]">
                    <router-link to="/terms" class="hover:text-slate-300 transition">Terms of Service</router-link>
                    <span class="text-slate-700">•</span>
                    <router-link to="/privacy" class="hover:text-slate-300 transition">Privacy Policy</router-link>
                    <span class="text-slate-700">•</span>
                    <router-link to="/rera-disclaimer" class="hover:text-slate-300 transition">RERA Disclaimer</router-link>
                    <span class="text-slate-700">•</span>
                    <router-link to="/refund-policy" class="hover:text-slate-300 transition">Refund &amp; Cancellation</router-link>
                </div>

                <span class="text-slate-400">Powered by {{ companyName }} Platform</span>
            </div>
        </div>
    </footer>
</template>

<script setup>
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
