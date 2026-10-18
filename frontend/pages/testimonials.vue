<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <router-link to="/" class="hover:text-blue-600 transition">Home</router-link>
            <span>/</span>
            <span class="text-slate-800 font-bold">Company</span>
            <span>/</span>
            <span class="text-blue-600 font-extrabold">Testimonials</span>
        </nav>

        <!-- Header Hero -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-2xl relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <span>⭐</span>
                    <span>4.9 / 5 Overall Satisfaction Rating</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    Trusted by 1.2 Million+ Home Seekers &amp; Owners
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Discover genuine stories from buyers who found their dream apartments, owners who sold at zero brokerage, and builders who scaled their project launches with {{ companyName }}.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 relative z-10">
                <router-link
                    to="/feedback"
                    class="px-5 py-2.5 rounded-xl text-white font-extrabold text-xs shadow-md transition hover:opacity-95"
                    :style="{ backgroundColor: brandPrimaryColor }"
                >
                    Share Your Feedback &rarr;
                </router-link>
            </div>
        </div>

        <!-- Filter Pills & Category Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-none py-1">
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="activeCategory = cat"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap"
                    :class="activeCategory === cat
                        ? 'bg-slate-900 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                >
                    {{ cat }}
                </button>
            </div>

            <span class="text-xs text-slate-500 font-medium">
                Showing {{ filteredTestimonials.length }} verified experiences
            </span>
        </div>

        <!-- Testimonials Card Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="(item, idx) in filteredTestimonials"
                :key="idx"
                class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-md transition"
            >
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <!-- Star rating -->
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            <span v-for="star in item.stars" :key="star">★</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                            {{ item.category }}
                        </span>
                    </div>

                    <h4 class="text-sm font-extrabold text-slate-900 leading-snug">"{{ item.headline }}"</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ item.comment }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-slate-700 text-xs">
                            {{ item.avatarInitials }}
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 flex items-center gap-1">
                                <span>{{ item.author }}</span>
                                <span class="text-blue-500 text-[11px]" title="Verified User">✓</span>
                            </p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ item.locality }} &bull; {{ item.city }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action Banner -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-8 text-center space-y-4">
            <h3 class="text-xl sm:text-2xl font-black text-slate-900">Have you used {{ companyName }} to find or sell property?</h3>
            <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto">
                We read every review. Your feedback directly helps us improve listing verification, filter accuracy, and platform features.
            </p>
            <div class="pt-2">
                <router-link
                    to="/feedback"
                    class="inline-block px-6 py-3 rounded-xl text-white font-extrabold text-xs sm:text-sm shadow-md transition hover:opacity-95"
                    :style="{ backgroundColor: brandPrimaryColor }"
                >
                    Submit Your Feedback &amp; Review &rarr;
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

const { companyName, brandPrimaryColor } = useCompanyBranding();

useSeoMeta({
    title: () => `Customer Reviews & Testimonials • Real Property Experiences`,
    description: () => `Read verified reviews from home buyers, tenants, owners, and developers transacting across India on RealHem.`,
    ogTitle: () => `Customer Reviews & Testimonials • RealHem`,
    ogDescription: () => `Read verified reviews from home buyers, tenants, owners, and developers transacting across India on RealHem.`,
});

const activeCategory = ref('All');
const categories = ['All', 'Home Buyers', 'Property Owners', 'Tenants', 'Builders & Brokers'];

const testimonials = [
    {
        author: 'Kunal Singhania',
        avatarInitials: 'KS',
        locality: 'SG Highway',
        city: 'Ahmedabad',
        category: 'Home Buyers',
        stars: 5,
        headline: 'Closed our 3 BHK purchase in 3 weeks!',
        comment: 'The RERA verification badge gave me immense confidence. Every floor plan and carpet area specification matched the physical flat exactly. Zero ghost listings encountered.',
    },
    {
        author: 'Dr. Priya Nambiar',
        avatarInitials: 'PN',
        locality: 'Whitefield',
        city: 'Bengaluru',
        category: 'Property Owners',
        stars: 5,
        headline: 'Sold our villa with zero brokerage fees',
        comment: 'Posted our property as direct owners for free. Within 4 days, received 12 high-intent verified buyer leads through mobile OTP. We saved over ₹3.5 Lakhs in brokerage.',
    },
    {
        author: 'Siddharth Trivedi',
        avatarInitials: 'ST',
        locality: 'Andheri West',
        city: 'Mumbai',
        category: 'Tenants',
        stars: 5,
        headline: 'Zero brokerage rental that was actually genuine',
        comment: 'In Mumbai, finding a flat without having to pay 1-2 months brokerage felt impossible until I used RealHem. Connected straight to the flat owner and moved in seamlessly.',
    },
    {
        author: 'Rohit Agrawal',
        avatarInitials: 'RA',
        locality: 'Golf Course Extension',
        city: 'Gurugram',
        category: 'Builders & Brokers',
        stars: 5,
        headline: 'High conversion rate for new luxury tower launch',
        comment: 'As a Grade-A channel partner, the broker dashboard and geotagged inquiries delivered genuine NRI and local investor leads. The response time feature kept our CRM ahead.',
    },
    {
        author: 'Meenakshi Sundaram',
        avatarInitials: 'MS',
        locality: 'Gachibowli',
        city: 'Hyderabad',
        category: 'Home Buyers',
        stars: 5,
        headline: 'Clear pricing with no hidden charges',
        comment: 'Filter by possession date and super built-up ratio worked like a charm. We visited 4 shortlisted gated communities and finalized a flat in Financial District effortlessly.',
    },
    {
        author: 'Amit Joshi',
        avatarInitials: 'AJ',
        locality: 'Kothrud',
        city: 'Pune',
        category: 'Property Owners',
        stars: 5,
        headline: 'Rented out 2 BHK in less than 48 hours',
        comment: 'Uploaded pictures through my phone and verified my ownership. Got genuine family inquiries and finalized agreement without dealing with aggressive brokers calling at odd hours.',
    },
];

const filteredTestimonials = computed(() => {
    if (activeCategory.value === 'All') return testimonials;
    return testimonials.filter(t => t.category === activeCategory.value);
});
</script>

