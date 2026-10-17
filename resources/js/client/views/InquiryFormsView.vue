<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <router-link to="/" class="hover:text-blue-600 transition">Home</router-link>
            <span>/</span>
            <span class="text-slate-800 font-bold">Support &amp; Community</span>
            <span>/</span>
            <span class="text-blue-600 font-extrabold capitalize">{{ activeTabMeta.title }}</span>
        </nav>

        <!-- Header Card with Switcher Tabs -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" :class="activeTabMeta.badgeClass">
                    <span>{{ activeTabMeta.icon }}</span>
                    <span>{{ activeTabMeta.badge }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    {{ activeTabMeta.title }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ activeTabMeta.subtitle }}
                </p>
            </div>

            <!-- Tabs Nav -->
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 overflow-x-auto scrollbar-none">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="switchTab(tab.id)"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
                    :class="activeTab === tab.id
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                >
                    <span>{{ tab.icon }}</span>
                    <span>{{ tab.title }}</span>
                </button>
            </div>
        </div>

        <!-- Form Workspace -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-10 space-y-6">
            <!-- Feedback Alerts -->
            <div v-if="successMessage" class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🎉</span>
                    <span>{{ successMessage }}</span>
                </div>
                <button @click="successMessage = ''" class="text-emerald-700 hover:text-emerald-950 font-bold cursor-pointer">✕</button>
            </div>

            <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚠️</span>
                    <span>{{ errorMessage }}</span>
                </div>
                <button @click="errorMessage = ''" class="text-rose-700 hover:text-rose-950 font-bold cursor-pointer">✕</button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- User Identifiers -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Full Name <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="e.g. Ramesh Patel"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Email Address <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="ramesh@example.com"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Mobile Number</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="+91 98765 43210"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <!-- Property ID reference (Special for Report Problem or Request Info) -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">
                            {{ activeTab === 'report_problem' ? 'Property ID (if reporting a listing)' : 'Property ID / Ref # (Optional)' }}
                        </label>
                        <input
                            v-model="form.property_id"
                            type="number"
                            placeholder="e.g. 1024"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Star Rating Selector (Exclusive for Feedback) -->
                <div v-if="activeTab === 'feedback'" class="space-y-2 p-4 rounded-2xl bg-amber-50/70 border border-amber-200">
                    <label class="text-xs font-bold text-slate-800 block">How would you rate your overall experience?</label>
                    <div class="flex items-center gap-2">
                        <button
                            v-for="s in 5"
                            :key="s"
                            type="button"
                            @click="form.rating = s"
                            class="text-2xl transition cursor-pointer hover:scale-125"
                            :class="form.rating >= s ? 'text-amber-500' : 'text-slate-300'"
                        >
                            ★
                        </button>
                        <span class="text-xs font-bold text-slate-700 ml-2">
                            {{ ratingLabels[form.rating] || 'Select rating' }}
                        </span>
                    </div>
                </div>

                <!-- Subject / Category -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">
                        {{ activeTab === 'report_problem' ? 'Reason for Reporting' : 'Subject / Topic' }}
                    </label>
                    <select
                        v-if="activeTab === 'report_problem'"
                        v-model="form.subject"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        <option value="Fake or Inaccurate Price">Fake or Inaccurate Price</option>
                        <option value="Listing Already Sold / Rented">Listing Already Sold or Rented</option>
                        <option value="Misleading or Copyrighted Photos">Misleading or Copyrighted Photos</option>
                        <option value="Broker Impersonating Owner">Broker Impersonating Direct Owner</option>
                        <option value="Suspicious / Fraudulent Payment Demand">Suspicious / Fraudulent Payment Demand</option>
                        <option value="Technical Glitch or Bug">Technical Glitch or UI Bug</option>
                        <option value="Other Policy Violation">Other Policy Violation</option>
                    </select>

                    <select
                        v-else-if="activeTab === 'request_info'"
                        v-model="form.subject"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        <option value="Project Brochure & Price Sheet Request">Project Brochure &amp; Price Sheet Request</option>
                        <option value="Home Loan & Interest Rate Consultation">Home Loan &amp; Interest Rate Consultation</option>
                        <option value="Legal Title & RERA Due Diligence Advisory">Legal Title &amp; RERA Due Diligence Advisory</option>
                        <option value="Property Valuation Report">Property Valuation Report</option>
                        <option value="Bulk Developer Lead Packages">Bulk Developer Lead Packages</option>
                        <option value="Commercial Office Space Consultation">Commercial Office Space Consultation</option>
                    </select>

                    <input
                        v-else
                        v-model="form.subject"
                        type="text"
                        placeholder="e.g. Loved the new map radius search!"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>

                <!-- Message Description -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Detailed Message <span class="text-rose-500">*</span></label>
                    <textarea
                        v-model="form.message"
                        required
                        rows="5"
                        :placeholder="activeTabMeta.placeholder"
                        class="w-full p-4 rounded-xl border border-slate-200 text-xs sm:text-sm font-normal bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                    ></textarea>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-8 py-3 rounded-xl text-white font-extrabold text-xs sm:text-sm shadow-md transition hover:opacity-95 cursor-pointer flex items-center justify-center gap-2"
                        :class="activeTab === 'report_problem' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-blue-600 hover:bg-blue-700'"
                    >
                        <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>{{ isSubmitting ? 'Submitting...' : activeTabMeta.buttonText }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const route = useRoute();
const router = useRouter();
const { companyName } = useCompanyBranding();

const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const tabs = [
    {
        id: 'request_info',
        path: '/request-info',
        title: 'Request Info',
        icon: '📋',
        badge: 'Property & Advisory Services',
        badgeClass: 'bg-blue-50 text-blue-700 border border-blue-200',
        subtitle: 'Request detailed project brochures, home loan eligibility checks, or customized property consultation.',
        placeholder: 'Describe the property locations, budget, or loan advisory you need assistance with...',
        buttonText: 'Submit Info Request &rarr;',
        defaultSubject: 'Project Brochure & Price Sheet Request',
    },
    {
        id: 'feedback',
        path: '/feedback',
        title: 'Feedback',
        icon: '💬',
        badge: 'User Experience & Reviews',
        badgeClass: 'bg-amber-50 text-amber-800 border border-amber-200',
        subtitle: 'Tell us about your experience using the platform, report satisfaction, or suggest new features.',
        placeholder: 'Share your compliments, thoughts, or ideas to make RealHem better...',
        buttonText: 'Submit Feedback &rarr;',
        defaultSubject: 'General Platform Feedback',
    },
    {
        id: 'report_problem',
        path: '/report-problem',
        title: 'Report a Problem',
        icon: '🚨',
        badge: 'Trust & Moderation Desk',
        badgeClass: 'bg-rose-50 text-rose-700 border border-rose-200',
        subtitle: 'Report fraudulent listings, inaccurate pricing, unresponsive agents, or platform technical bugs.',
        placeholder: 'Please provide exact details of the issue, what happened, or why this listing should be reviewed...',
        buttonText: 'Submit Problem Report &rarr;',
        defaultSubject: 'Fake or Inaccurate Price',
    },
];

const ratingLabels = {
    1: '1 Star - Poor / Needs Improvement',
    2: '2 Stars - Fair',
    3: '3 Stars - Good',
    4: '4 Stars - Very Good',
    5: '5 Stars - Excellent / Outstanding!',
};

const activeTab = computed(() => {
    const p = route.path;
    if (p.includes('feedback')) return 'feedback';
    if (p.includes('report-problem')) return 'report_problem';
    return 'request_info';
});

const activeTabMeta = computed(() => {
    return tabs.find(t => t.id === activeTab.value) || tabs[0];
});

const form = ref({
    name: '',
    email: '',
    phone: '',
    subject: activeTabMeta.value.defaultSubject,
    message: '',
    rating: 5,
    property_id: null,
});

watch(activeTab, () => {
    form.value.subject = activeTabMeta.value.defaultSubject;
    successMessage.value = '';
    errorMessage.value = '';
});

const switchTab = (tabId) => {
    const target = tabs.find(t => t.id === tabId);
    if (target) {
        router.push(target.path);
    }
};

const submitForm = async () => {
    isSubmitting.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    try {
        const payload = {
            type: activeTab.value,
            name: form.value.name,
            email: form.value.email,
            phone: form.value.phone || null,
            subject: form.value.subject,
            message: form.value.message,
            rating: activeTab.value === 'feedback' ? form.value.rating : null,
            property_id: form.value.property_id ? parseInt(form.value.property_id) : null,
        };

        const res = await fetch('/api/inquiries', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (res.ok) {
            successMessage.value = data.message || 'Submitted successfully!';
            form.value = {
                name: '',
                email: '',
                phone: '',
                subject: activeTabMeta.value.defaultSubject,
                message: '',
                rating: 5,
                property_id: null,
            };
        } else {
            errorMessage.value = data.message || 'Validation error while submitting form.';
        }
    } catch (err) {
        errorMessage.value = 'Network error while submitting. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    if (route.query.property_id) {
        form.value.property_id = route.query.property_id;
    }
});
</script>

