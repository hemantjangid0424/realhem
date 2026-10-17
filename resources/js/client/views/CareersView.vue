<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <router-link to="/" class="hover:text-blue-600 transition">Home</router-link>
            <span>/</span>
            <span class="text-slate-800 font-bold">Company</span>
            <span>/</span>
            <span class="text-blue-600 font-extrabold">Careers with Us</span>
        </nav>

        <!-- Header Hero -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-8 sm:p-14 text-white relative overflow-hidden space-y-6">
            <div class="max-w-3xl space-y-3 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-blue-200 border border-white/20">
                    <span>💼</span>
                    <span>Join Our High-Impact PropTech Team</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                    Build the Future of Indian Real Estate at {{ companyName }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl">
                    We are building high-speed discovery engines, algorithmic verification models, and transparent transaction platforms that power housing decisions for millions across India.
                </p>
                <div class="pt-2">
                    <a
                        href="#openings"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-slate-900 text-xs sm:text-sm font-extrabold shadow-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        <span>View {{ openings.length }} Open Roles</span>
                        <span>&darr;</span>
                    </a>
                </div>
            </div>

            <!-- Background design accent -->
            <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
        </div>

        <!-- Why Join Us / Perks -->
        <div class="space-y-6">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Why Engineers &amp; Operators Love {{ companyName }}</h2>
                <p class="text-xs sm:text-sm text-slate-500">Autonomous culture, cutting-edge technology, and high real-world impact.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="perk in perks" :key="perk.title" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <span class="text-3xl block">{{ perk.icon }}</span>
                    <h3 class="text-sm font-black text-slate-900">{{ perk.title }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ perk.desc }}</p>
                </div>
            </div>
        </div>

        <!-- Open Roles Section -->
        <div id="openings" class="space-y-6 pt-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Current Open Opportunities</h2>
                    <p class="text-xs text-slate-500 mt-1">Select a role to review qualifications or submit your application below.</p>
                </div>

                <!-- Department Filter Pills -->
                <div class="flex items-center gap-1.5 flex-wrap bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-600">
                    <button
                        v-for="dept in departments"
                        :key="dept"
                        @click="activeDept = dept"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer"
                        :class="activeDept === dept ? 'bg-white text-slate-900 shadow-2xs' : 'hover:text-slate-900'"
                    >
                        {{ dept }}
                    </button>
                </div>
            </div>

            <div v-if="filteredOpenings.length === 0" class="bg-white rounded-3xl p-10 border border-slate-200/80 shadow-xs text-center space-y-3">
                <span class="text-3xl block">📋</span>
                <h3 class="text-base font-bold text-slate-800">No active job openings at the moment</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    We don't have open positions listed right now, but we are always eager to connect with exceptional talent. You can still submit your profile below!
                </p>
                <div class="pt-2">
                    <a
                        href="#apply-section"
                        class="inline-block px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-xs transition hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Submit General Application &rarr;
                    </a>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="job in filteredOpenings"
                    :key="job.title"
                    class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4 hover:border-blue-400 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                {{ job.department }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">{{ job.location }} &bull; {{ job.type }}</span>
                        </div>
                        <h3 class="text-base font-black text-slate-900">{{ job.title }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ job.description || job.desc }}</p>
                        <div class="flex items-center gap-2 flex-wrap pt-2">
                            <span v-for="tag in job.tags" :key="tag" class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-semibold">
                                {{ tag }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-600">{{ job.experience }} Experience</span>
                        <button
                            @click="selectJobForApplication(job)"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer text-white shadow-xs hover:opacity-95"
                            :style="{ backgroundColor: brandPrimaryColor }"
                        >
                            Apply for Role &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Form Section -->
        <div id="apply-section" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-10 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-xl font-black text-slate-900 tracking-tight">
                    {{ selectedJob ? `Apply for ${selectedJob.title}` : 'General Application / Introduce Yourself' }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">Don't see an exact match? Submit your profile and we'll keep you in mind for future openings.</p>
            </div>

            <!-- Feedback Alerts -->
            <div v-if="successMessage" class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold">
                <div class="flex items-center gap-2">
                    <span>🎉</span>
                    <span>{{ successMessage }}</span>
                </div>
                <button @click="successMessage = ''" class="text-emerald-700 hover:text-emerald-950 font-bold cursor-pointer">✕</button>
            </div>

            <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ errorMessage }}</span>
                </div>
                <button @click="errorMessage = ''" class="text-rose-700 hover:text-rose-950 font-bold cursor-pointer">✕</button>
            </div>

            <form @submit.prevent="submitApplication" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Full Name <span class="text-rose-500">*</span></label>
                        <input
                            v-model="applicant.name"
                            type="text"
                            required
                            placeholder="e.g. Ananya Patel"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Email Address <span class="text-rose-500">*</span></label>
                        <input
                            v-model="applicant.email"
                            type="email"
                            required
                            placeholder="ananya@example.com"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Phone Number <span class="text-rose-500">*</span></label>
                        <input
                            v-model="applicant.phone"
                            type="tel"
                            required
                            placeholder="+91 98765 43210"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">LinkedIn / GitHub / Portfolio URL</label>
                        <input
                            v-model="applicant.portfolio"
                            type="url"
                            placeholder="https://linkedin.com/in/username"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Why are you interested in joining {{ companyName }}? <span class="text-rose-500">*</span></label>
                    <textarea
                        v-model="applicant.message"
                        required
                        rows="4"
                        placeholder="Tell us about your background, key achievements, and what drives you..."
                        class="w-full p-4 rounded-xl border border-slate-200 text-xs sm:text-sm font-normal bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="px-8 py-3 rounded-xl text-white font-extrabold text-xs sm:text-sm shadow-md transition hover:opacity-95 cursor-pointer flex items-center gap-2"
                    :style="{ backgroundColor: brandPrimaryColor }"
                >
                    <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>{{ isSubmitting ? 'Submitting Application...' : 'Submit Application &rarr;' }}</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const { companyName, brandPrimaryColor } = useCompanyBranding();

const activeDept = ref('All');
const departments = ref(['All', 'Engineering', 'Product & Design', 'Sales & Growth', 'Operations']);

const perks = [
    { icon: '🚀', title: 'High-Scale Systems', desc: 'Work on architectures serving millions of listing searches with sub-100ms response targets.' },
    { icon: '💰', title: 'Top-Tier Compensation', desc: 'Competitive market salary, generous performance bonuses, and ESOP equity grants.' },
    { icon: '🏥', title: 'Comprehensive Health', desc: 'Zero-deductible health insurance covering employee, spouse, children, and parents.' },
    { icon: '☕', title: 'Modern Work Environment', desc: 'Ergonomic workspaces, state-of-the-art Apple M-series hardware, and flexible hybrid arrangements.' },
];

const openings = ref([]);

const filteredOpenings = computed(() => {
    if (activeDept.value === 'All') return openings.value;
    return openings.value.filter(job => job.department === activeDept.value);
});

const selectedJob = ref(null);
const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const applicant = ref({
    name: '',
    email: '',
    phone: '',
    portfolio: '',
    message: '',
});

const selectJobForApplication = (job) => {
    selectedJob.value = job;
    const el = document.getElementById('apply-section');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};

const submitApplication = async () => {
    isSubmitting.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    const subject = selectedJob.value
        ? `Application for ${selectedJob.value.title}`
        : 'General Candidate Application';

    try {
        const res = await fetch('/api/inquiries', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                type: 'career',
                name: applicant.value.name,
                email: applicant.value.email,
                phone: applicant.value.phone,
                subject: subject,
                message: applicant.value.message,
                metadata: {
                    job_title: selectedJob.value ? selectedJob.value.title : 'General',
                    portfolio_url: applicant.value.portfolio,
                },
            }),
        });

        const data = await res.json();
        if (res.ok) {
            successMessage.value = data.message || 'Your application has been received successfully!';
            applicant.value = {
                name: '',
                email: '',
                phone: '',
                portfolio: '',
                message: '',
            };
            selectedJob.value = null;
        } else {
            errorMessage.value = data.message || 'Failed to submit application. Please check input fields.';
        }
    } catch (err) {
        errorMessage.value = 'Network error while submitting your application. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};

const fetchJobs = async () => {
    try {
        const res = await fetch('/api/jobs');
        const data = await res.json();
        if (data.success && Array.isArray(data.data)) {
            openings.value = data.data;
            if (data.departments && data.departments.length > 0) {
                departments.value = data.departments;
            }
        }
    } catch (err) {
        console.warn('Failed to load dynamic jobs:', err);
    }
};

onMounted(() => {
    fetchJobs();
});
</script>

