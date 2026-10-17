<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <router-link to="/" class="hover:text-blue-600 transition">Home</router-link>
            <span>/</span>
            <span class="text-slate-800 font-bold">Company</span>
            <span>/</span>
            <span class="text-blue-600 font-extrabold">Contact Us</span>
        </nav>

        <!-- Header Hero -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-2xl relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                    <span>📞</span>
                    <span>Direct Support &amp; Regional Desks</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    Get in Touch with {{ companyName }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Have questions about property posting, builder packages, technical issues, or legal compliance? Our dedicated customer care and enterprise support teams are here to assist you.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 relative z-10 text-xs text-slate-600">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Toll-Free Helpline</span>
                    <a :href="`tel:${companyPhone}`" class="text-sm font-black text-blue-600 hover:underline block">
                        {{ companyPhone }}
                    </a>
                    <span class="text-[10px] text-slate-400 font-medium block">Mon–Sat, 9:30 AM to 6:30 PM IST</span>
                </div>
            </div>
        </div>

        <!-- Contact Grid: Form on Left, Office Locations & Desks on Right -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Inquiry / Contact Form -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-10 space-y-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Send Us a Direct Message</h2>
                    <p class="text-xs text-slate-500 mt-1">Fill in the form below and our team will get back to you within 24 hours.</p>
                </div>

                <!-- Success Alert -->
                <div v-if="successMessage" class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ successMessage }}</span>
                    </div>
                    <button @click="successMessage = ''" class="text-emerald-700 hover:text-emerald-950 font-bold cursor-pointer">✕</button>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ errorMessage }}</span>
                    </div>
                    <button @click="errorMessage = ''" class="text-rose-700 hover:text-rose-950 font-bold cursor-pointer">✕</button>
                </div>

                <form @submit.prevent="submitContactForm" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Your Full Name <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="e.g. Rajesh Sharma"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Email Address <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="rajesh@example.com"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Phone / Mobile Number</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="+91 98765 43210"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Department / Topic</label>
                            <select
                                v-model="form.subject"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            >
                                <option value="General Customer Support">General Customer Support</option>
                                <option value="Property Listing Verification">Property Listing Verification</option>
                                <option value="Builder & Developer Advertising">Builder &amp; Developer Advertising</option>
                                <option value="Broker Channel Partnership">Broker Channel Partnership</option>
                                <option value="Technical or Account Issue">Technical or Account Issue</option>
                                <option value="Press & Media Queries">Press &amp; Media Queries</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Message / Inquiry Details <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="form.message"
                            required
                            rows="5"
                            placeholder="Please describe your query or requirement in detail..."
                            class="w-full p-4 rounded-xl border border-slate-200 text-xs sm:text-sm font-normal bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full sm:w-auto px-8 py-3 rounded-xl text-white font-extrabold text-xs sm:text-sm shadow-md transition hover:opacity-95 cursor-pointer flex items-center justify-center gap-2"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>{{ isSubmitting ? 'Sending Message...' : 'Submit Inquiry &rarr;' }}</span>
                    </button>
                </form>
            </div>

            <!-- Regional Offices & Contact Info -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Headquarters Card -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                            🏢
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Corporate Headquarters</span>
                            <h3 class="text-sm font-black text-slate-900">{{ companyName }} Technologies Pvt. Ltd.</h3>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        902–904, Signature Tower, SG Highway, Bodakdev, Ahmedabad, Gujarat 380054, India.
                    </p>
                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 font-bold">Email:</span>
                            <a :href="`mailto:${companyEmail}`" class="text-blue-600 font-bold hover:underline">{{ companyEmail }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 font-bold">Helpline:</span>
                            <span class="font-bold text-slate-800">{{ companyPhone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Regional Network Offices -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Regional Branch Desks</h3>

                    <div class="space-y-3 divide-y divide-slate-100 text-xs">
                        <div class="pt-2 first:pt-0 space-y-0.5">
                            <p class="font-extrabold text-slate-800">Mumbai Metropolitan Desk</p>
                            <p class="text-slate-500">Bandra Kurla Complex (BKC), Bandra East, Mumbai 400051</p>
                            <p class="text-blue-600 font-medium">mumbai.desk@realhem.com</p>
                        </div>
                        <div class="pt-3 space-y-0.5">
                            <p class="font-extrabold text-slate-800">Delhi NCR Corporate Desk</p>
                            <p class="text-slate-500">Cyber City, DLF Phase 2, Gurugram, Haryana 122002</p>
                            <p class="text-blue-600 font-medium">delhi.desk@realhem.com</p>
                        </div>
                        <div class="pt-3 space-y-0.5">
                            <p class="font-extrabold text-slate-800">Bengaluru Tech &amp; Sales Desk</p>
                            <p class="text-slate-500">Indiranagar 100ft Road, Bengaluru, Karnataka 560038</p>
                            <p class="text-blue-600 font-medium">bangalore.desk@realhem.com</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Tiles -->
                <div class="grid grid-cols-2 gap-3 text-center">
                    <router-link
                        to="/request-info"
                        class="p-4 rounded-2xl bg-blue-50/70 hover:bg-blue-100/70 border border-blue-200/60 text-blue-900 transition space-y-1 block"
                    >
                        <span class="text-lg">📋</span>
                        <p class="text-xs font-bold">Request Info</p>
                        <p class="text-[10px] text-blue-700">Brochures &amp; pricing</p>
                    </router-link>
                    <router-link
                        to="/report-problem"
                        class="p-4 rounded-2xl bg-rose-50/70 hover:bg-rose-100/70 border border-rose-200/60 text-rose-900 transition space-y-1 block"
                    >
                        <span class="text-lg">🚨</span>
                        <p class="text-xs font-bold">Report Problem</p>
                        <p class="text-[10px] text-rose-700">Fraud &amp; fake listings</p>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const { companyName, companyEmail, companyPhone, brandPrimaryColor } = useCompanyBranding();

const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = ref({
    name: '',
    email: '',
    phone: '',
    subject: 'General Customer Support',
    message: '',
});

const submitContactForm = async () => {
    isSubmitting.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    try {
        const res = await fetch('/api/inquiries', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                type: 'contact',
                name: form.value.name,
                email: form.value.email,
                phone: form.value.phone,
                subject: form.value.subject,
                message: form.value.message,
            }),
        });

        const data = await res.json();
        if (res.ok) {
            successMessage.value = data.message || 'Your inquiry has been submitted successfully!';
            form.value = {
                name: '',
                email: '',
                phone: '',
                subject: 'General Customer Support',
                message: '',
            };
        } else {
            errorMessage.value = data.message || 'Failed to submit inquiry. Please check the inputs.';
        }
    } catch (err) {
        errorMessage.value = 'Network error while submitting your inquiry. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

