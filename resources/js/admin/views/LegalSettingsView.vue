<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <AdminNavbar />

        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                            <span>⚖️</span>
                            <span>Legal &amp; Compliance Studio</span>
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                            Public Facing Policies
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Manage statutory terms, privacy policy, RERA consumer advisories, and cancellation terms shown to all buyers, tenants, agents, and sellers.
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <a
                        :href="currentDocumentClientUrl"
                        target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>View on Live Site</span>
                    </a>

                    <button
                        type="button"
                        @click="savePolicies"
                        :disabled="isSaving"
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg v-if="isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span v-if="isSaving">Publishing Policies...</span>
                        <span v-else>Save &amp; Publish Policies</span>
                    </button>
                </div>
            </div>

            <!-- Feedback Alert -->
            <transition name="fade">
                <div v-if="successMessage" class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ successMessage }}</span>
                    </div>
                    <button @click="successMessage = ''" class="text-emerald-600 hover:text-emerald-900 cursor-pointer font-bold">✕</button>
                </div>
            </transition>

            <!-- Metadata Banner (Effective Date & Version) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700">Effective Date:</span>
                        <input
                            v-model="legalForm.updated_at"
                            type="text"
                            placeholder="e.g. October 2026"
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700">Policy Version:</span>
                        <input
                            v-model="legalForm.version"
                            type="text"
                            placeholder="e.g. 2.4"
                            class="w-24 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Status: Auto-synced with Client Frontend Legal Pages</span>
                </div>
            </div>

            <!-- Document Selection & Editor Workspace -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <!-- Document Tabs -->
                <div class="flex items-center border-b border-slate-200 bg-slate-50/70 px-4 pt-3 gap-2 overflow-x-auto scrollbar-none">
                    <button
                        v-for="doc in documents"
                        :key="doc.id"
                        type="button"
                        @click="activeDoc = doc.id"
                        class="px-4 py-3 rounded-t-xl text-xs font-bold transition flex items-center gap-2 border-b-2 cursor-pointer whitespace-nowrap"
                        :class="activeDoc === doc.id
                            ? 'bg-white text-blue-600 border-blue-600 shadow-xs'
                            : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    >
                        <span>{{ doc.icon }}</span>
                        <span>{{ doc.name }}</span>
                        <span
                            v-if="hasCustomContent(doc.id)"
                            class="text-[9px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-extrabold"
                            title="Customized content saved"
                        >
                            Customized
                        </span>
                        <span
                            v-else
                            class="text-[9px] px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-600 font-medium"
                            title="Using system default template"
                        >
                            Default
                        </span>
                    </button>
                </div>

                <!-- Editor Toolbar -->
                <div class="p-4 sm:px-6 bg-white border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-700">{{ activeDocMeta.name }}</span>
                        <span class="text-xs text-slate-400">|</span>
                        <span class="text-xs text-slate-500">
                            {{ activeWordCount }} words &bull; {{ activeCharCount }} characters
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button
                            type="button"
                            @click="loadDefaultTemplate"
                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                            title="Load standard compliant template into editor"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Load Standard Template</span>
                        </button>

                        <button
                            v-if="hasCustomContent(activeDoc)"
                            type="button"
                            @click="clearCustomContent"
                            class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1"
                            title="Reset to default system template"
                        >
                            <span>Clear Custom</span>
                        </button>

                        <div class="border-l border-slate-200 pl-2 ml-1 flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg">
                            <button
                                type="button"
                                @click="previewMode = 'split'"
                                class="px-2.5 py-1 rounded text-xs font-bold transition cursor-pointer"
                                :class="previewMode === 'split' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            >
                                Split
                            </button>
                            <button
                                type="button"
                                @click="previewMode = 'edit'"
                                class="px-2.5 py-1 rounded text-xs font-bold transition cursor-pointer"
                                :class="previewMode === 'edit' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            >
                                Editor Only
                            </button>
                            <button
                                type="button"
                                @click="previewMode = 'preview'"
                                class="px-2.5 py-1 rounded text-xs font-bold transition cursor-pointer"
                                :class="previewMode === 'preview' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            >
                                Live Preview
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Editor + Live Preview Grid -->
                <div class="grid" :class="{
                    'grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-200': previewMode === 'split',
                    'grid-cols-1': previewMode !== 'split'
                }">
                    <!-- Left: Editor Textarea -->
                    <div v-show="previewMode === 'split' || previewMode === 'edit'" class="p-6 space-y-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Markdown &amp; HTML Supported</span>
                            <span class="text-blue-600 font-medium">Auto-formatted on public view</span>
                        </div>

                        <textarea
                            v-model="legalForm[activeDoc]"
                            rows="22"
                            :placeholder="`Enter custom ${activeDocMeta.name} or click 'Load Standard Template' above to start from the pre-written legal template...`"
                            class="w-full p-4 rounded-xl border border-slate-200 bg-slate-50 font-mono text-xs text-slate-800 leading-relaxed focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                        ></textarea>

                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-blue-900 text-xs space-y-1">
                            <span class="font-bold block">💡 Editor Hint:</span>
                            <p class="text-blue-800">
                                You can use headings like <code>### 1. Section Title</code> or standard paragraphs. You can also write <code>&lt;strong&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, or <code>[Company Name]</code>. Leave empty to automatically use the built-in comprehensive default policy.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Live Public Preview -->
                    <div v-show="previewMode === 'split' || previewMode === 'preview'" class="p-6 sm:p-8 bg-slate-50/50 max-h-[720px] overflow-y-auto">
                        <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold text-slate-700">Live Client Preview (As Seen By Public)</span>
                            </div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">
                                Route: {{ activeDocMeta.clientPath }}
                            </span>
                        </div>

                        <!-- Rendered Document Preview Card -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6 text-slate-700 text-xs sm:text-sm leading-relaxed">
                            <div class="border-b border-slate-100 pb-4">
                                <h3 class="text-lg font-black text-slate-900 tracking-tight">{{ activeDocMeta.previewTitle }}</h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Last Updated: {{ legalForm.updated_at || 'October 2026' }} &bull; Version {{ legalForm.version || '2.4' }}
                                </p>
                            </div>

                            <!-- Content Output -->
                            <div v-if="legalForm[activeDoc] && legalForm[activeDoc].trim()" class="space-y-4 font-normal" v-html="renderFormattedContent(legalForm[activeDoc])">
                            </div>

                            <!-- Built-in Default Preview -->
                            <div v-else class="space-y-4">
                                <div class="px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs flex items-center gap-2 mb-4 font-medium">
                                    <span>ℹ️</span>
                                    <span>Displaying standard system default template (No custom overrides saved).</span>
                                </div>
                                <div class="space-y-4" v-html="renderFormattedContent(defaultTemplates[activeDoc])"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminNavbar from '../components/AdminNavbar.vue';

const isSaving = ref(false);
const successMessage = ref('');
const activeDoc = ref('terms');
const previewMode = ref('split');

const legalForm = ref({
    terms: '',
    privacy: '',
    rera: '',
    refund: '',
    updated_at: 'October 2026',
    version: '2.4',
});

const documents = [
    {
        id: 'terms',
        name: 'Terms of Service',
        icon: '📜',
        clientPath: '/terms',
        previewTitle: 'Terms & Conditions of Service',
    },
    {
        id: 'privacy',
        name: 'Privacy Policy',
        icon: '🛡️',
        clientPath: '/privacy',
        previewTitle: 'Privacy Policy & Data Protection',
    },
    {
        id: 'rera',
        name: 'RERA Compliance & Disclaimer',
        icon: '🏛️',
        clientPath: '/rera-disclaimer',
        previewTitle: 'RERA Disclaimer & Regulatory Advisory',
    },
    {
        id: 'refund',
        name: 'Refund & Cancellation Policy',
        icon: '💳',
        clientPath: '/refund-policy',
        previewTitle: 'Refund, Cancellation & Subscription Terms',
    },
];

const defaultTemplates = {
    terms: `### 1. Introduction & Acceptance
Welcome to our Real Estate Platform ("Platform", "we", "our", "us"). By accessing, browsing, searching, or posting property advertisements on this portal, you agree to be bound by these Terms and Conditions and our Privacy Policy. If you do not agree to these terms, please do not use our services.

### 2. Role of the Platform (Intermediary Status)
The Platform acts solely as an online real estate advertising portal and intermediary under Section 79 of the Information Technology Act, 2000. We provide an online marketplace connecting property owners, prospective buyers, tenants, registered real estate agents, and developers.
- We are not a party to any contract, lease, sale, or agreement between property seekers and sellers/landlords.
- We do not control, inspect, endorse, or guarantee the physical condition, legal title, encumbrance status, or pricing of any listed property.
- Users are strongly advised to independently verify property ownership, municipal approvals, and RERA registration before entering any transaction or transferring funds.

### 3. User Obligations & Property Postings
When posting a property listing, you warrant and confirm that:
- You are the lawful owner, authorized representative, or a licensed real estate broker possessing explicit written mandate to list the property.
- All details provided, including carpet area, super built-up area, bedroom count, pricing, and possession timeline, are truthful and accurate.
- Uploaded photographs depict the genuine subject property or sample flat, free from copyrighted watermarks of competing portals.
- You will immediately update the status of the listing (e.g., mark as Sold or Rented) once a deal is finalized.

### 4. Prohibited Activities
Users shall not:
- Post duplicate, fictitious, bait-and-switch, or ghost listings with artificial prices.
- Scrape, crawl, or harvest contact details, broker rosters, or image assets without prior written consent.
- Misrepresent role or license (e.g., acting as an unlicensed broker under the guise of an individual owner).
- Distribute defamatory, fraudulent, or unsolicited spam content.

### 5. Limitation of Liability
In no event shall the platform, its directors, employees, or tech partners be liable for any direct, indirect, incidental, punitive, or consequential damages resulting from false listings, token money disputes, title defects, or delays in real estate construction.

### 6. Governing Law & Jurisdiction
These Terms are governed by and construed in accordance with the laws of the Republic of India. Any legal dispute or proceeding arising out of or related to this platform shall be subject to the exclusive jurisdiction of the competent courts in India.`,

    privacy: `### 1. Information We Collect
To deliver real-time real estate searches and facilitate seller-buyer interactions, we collect:
- **Account Data:** Name, phone number, email address, user role (Owner, Agent, Builder), and country code.
- **Property Information:** Address, geo-coordinates (latitude/longitude), floor details, sale prices, and property photographs.
- **Location & Device Data:** Real-time GPS location (only when you explicitly trigger "Near Me" or browser location detection), IP address, and browser session information.
- **Search & Inquiry Records:** Shortlisted properties, saved filters, and inquiries submitted to view owner/broker contact numbers.

### 2. How We Use Your Data
Your data is used strictly for legitimate property marketplace services:
- Connecting property seekers with verified owners or listing brokers.
- Sending transaction updates, OTP authentications, and lead notifications via SMS, WhatsApp, and email.
- Computing local real-estate market price trends, nearby locality suggestions, and home loan EMI estimates.
- Preventing spam, fraudulent broker postings, and unauthorized account access.

### 3. Information Sharing & Lead Exchange
When you click "View Contact Number", "Contact Seller", or request a property brochure, your basic contact information (Name, Mobile number, and inquiry interest) is shared directly with the owner, developer, or authorized agent who listed that property.
We do not sell, rent, or trade your personal data to non-affiliated third-party telemarketers.

### 4. Cookies & Local Storage
We utilize high-speed client-side local caching (sessionStorage and localStorage) to save your preferred city, recent searches, and UI theme preferences. You may clear your browser cookies and storage at any time via your browser settings.

### 5. Data Retention & User Rights
You have the right to request access to your saved listings, request rectification of erroneous data, or request permanent deletion of your account and property postings by contacting our support team.`,

    rera: `### Statutory RERA Advisory for Property Buyers & Investors
Under the Real Estate (Regulation and Development) Act, 2016 (RERA), all residential and commercial real estate projects where land area exceeds 500 square meters or number of apartments exceeds eight must be registered with the respective State Real Estate Regulatory Authority.

### 1. Intermediary / Advertising Platform Role
This platform is an online advertising publisher and technological platform. Neither the platform nor its affiliates act as a real estate developer, promoter, or contracting agent for any of the listed projects.
Project details, floor plans, approvals, possession dates, and promotional material displayed on this website are supplied directly by developers, listing brokers, or retrieved from public records.

### 2. Verification Checklist for Home Buyers
Before issuing any booking advance, token money, or executing an Agreement for Sale, all buyers must:
- Visit the official State RERA portal (e.g., GujRERA for Gujarat, MahaRERA for Maharashtra, UP RERA for UP/Noida, HRERA for Gurugram/Haryana, K-RERA for Karnataka).
- Verify the unique RERA Registration Number, sanctioned building plans, layout layout approvals, and title deed encumbrances.
- Confirm the designated RERA escrow bank account where installment payments are legally required to be deposited.
- Examine the sanctioned timeline for project handover and quarterly progress reports uploaded by the promoter.

### 3. Real Estate Agent Registration
Real estate brokers and agents publishing listings on the platform are required under Section 9 of RERA to maintain a valid RERA Agent Registration certificate. We reserve the right to request proof of registration and to delist non-compliant advertisements.

### 4. Reporting Non-Compliant Listings
If you identify any project advertisement lacking a required RERA registration number or displaying inaccurate compliance credentials, please report it immediately to our compliance desk for prompt investigation and takedown.`,

    refund: `### 1. Free Property Listings
Basic property listings posted by individual owners are offered 100% Free of Cost on our platform. There are no hidden subscription charges or mandatory brokerage commissions levied by the platform on standard free postings.

### 2. Paid Promotional Packages & Featured Listings
For premium marketing services, featured property badges, broker banner slots, and developer lead generation bundles:
- Payments are processed in Indian Rupees (INR) via secure, RBI-compliant payment gateways.
- Once a listing promotion or featured badge has been activated and published on the live portal, fees are non-refundable as the digital advertising service has been delivered.
- If a listing is rejected by moderation due to regulatory or policy violations prior to publishing, a full 100% refund will be credited back to the original payment source within 5–7 business days.

### 3. Cancellation of Paid Subscriptions
Brokers and developers subscribed to recurring quarterly or annual lead packages may cancel renewal at any time via the Broker Portal or by contacting account management. Cancellation prevents future billing cycles while maintaining active visibility until the end of the paid term.

### 4. Duplicate or Accidental Transactions
In the event of an accidental double charge or network failure resulting in debited funds without package activation, please write to our billing desk with your transaction reference number. Verified duplicates will be refunded in full without penalty.`
};

const activeDocMeta = computed(() => {
    return documents.find(d => d.id === activeDoc.value) || documents[0];
});

const currentDocumentClientUrl = computed(() => {
    return activeDocMeta.value.clientPath;
});

const hasCustomContent = (docId) => {
    return Boolean(legalForm.value[docId] && legalForm.value[docId].trim().length > 0);
};

const activeWordCount = computed(() => {
    const text = legalForm.value[activeDoc.value] || defaultTemplates[activeDoc.value] || '';
    return text.trim() ? text.trim().split(/\s+/).length : 0;
});

const activeCharCount = computed(() => {
    const text = legalForm.value[activeDoc.value] || defaultTemplates[activeDoc.value] || '';
    return text.length;
});

const loadDefaultTemplate = () => {
    legalForm.value[activeDoc.value] = defaultTemplates[activeDoc.value];
};

const clearCustomContent = () => {
    legalForm.value[activeDoc.value] = '';
};

const renderFormattedContent = (raw) => {
    if (!raw) return '';

    // If it already looks like full HTML with tags, return it
    if (/<[a-z][\s\S]*>/i.test(raw)) {
        return raw;
    }

    // Simple markdown-to-html parser for headers, bold, bullets, paragraphs
    return raw
        .split('\n\n')
        .map(paragraph => {
            paragraph = paragraph.trim();
            if (!paragraph) return '';

            // Heading 3
            if (paragraph.startsWith('### ')) {
                const headingText = paragraph.replace(/^###\s+/, '');
                return `<h4 class="text-base font-bold text-slate-900 mt-5 mb-2">${headingText}</h4>`;
            }

            // Bullet list
            if (paragraph.startsWith('- ') || paragraph.startsWith('* ')) {
                const items = paragraph.split('\n').map(line => {
                    const cleanLine = line.replace(/^[-*]\s+/, '').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    return `<li class="py-0.5">${cleanLine}</li>`;
                }).join('');
                return `<ul class="list-disc pl-5 space-y-1 text-slate-600 my-2">${items}</ul>`;
            }

            // Standard paragraph with bold formatting
            const parsedText = paragraph
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br/>');

            return `<p class="leading-relaxed my-2 text-slate-700">${parsedText}</p>`;
        })
        .join('');
};

const fetchLegalSettings = async () => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch('/api/admin/legal', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        const data = await res.json();
        if (data.legal) {
            legalForm.value = {
                terms: data.legal.terms || '',
                privacy: data.legal.privacy || '',
                rera: data.legal.rera || '',
                refund: data.legal.refund || '',
                updated_at: data.legal.updated_at || 'October 2026',
                version: data.legal.version || '2.4',
            };
        }
    } catch (err) {
        console.error('Failed to load legal policies:', err);
    }
};

const savePolicies = async () => {
    isSaving.value = true;
    successMessage.value = '';
    const token = localStorage.getItem('realhem_admin_token');

    try {
        const res = await fetch('/api/admin/legal', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(legalForm.value),
        });

        const data = await res.json();
        if (res.ok) {
            successMessage.value = data.message || 'Legal policies and compliance texts published successfully!';
            setTimeout(() => {
                successMessage.value = '';
            }, 4000);
        } else {
            alert(data.message || 'Validation error while saving legal policies.');
        }
    } catch (err) {
        console.error('Failed to save legal policies:', err);
        alert('Network error while saving legal policies.');
    } finally {
        isSaving.value = false;
    }
};

onMounted(() => {
    fetchLegalSettings();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

