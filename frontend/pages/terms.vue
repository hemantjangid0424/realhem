<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <router-link to="/" class="hover:text-blue-600 transition">Home</router-link>
            <span>/</span>
            <span class="text-slate-800 font-bold">Legal &amp; Policies</span>
            <span>/</span>
            <span class="text-blue-600 font-extrabold capitalize">{{ activeTabMeta.title }}</span>
        </nav>

        <!-- Header -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-2xl relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                    <span>⚖️</span>
                    <span>Legal Compliance &amp; Trust Center</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    {{ activeTabMeta.title }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ activeTabMeta.subtitle }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 relative z-10 text-xs text-slate-500">
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 font-medium">
                    Effective Date: <strong class="text-slate-800 font-bold">{{ legalData.updated_at || 'October 2026' }}</strong>
                </span>
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 font-medium">
                    Version <strong class="text-slate-800 font-bold">{{ legalData.version || '2.4' }}</strong>
                </span>
            </div>

            <!-- Subtle background circle -->
            <div
                class="absolute -right-12 -bottom-12 w-48 h-48 rounded-full opacity-5 pointer-events-none"
                :style="{ backgroundColor: brandPrimaryColor }"
            ></div>
        </div>

        <!-- Main Layout: Sidebar Navigation + Content Body -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-4 xl:col-span-3 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-3 space-y-1 sticky top-24">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="switchTab(tab.id)"
                        class="w-full text-left px-3.5 py-3 rounded-xl text-xs font-bold transition flex items-center justify-between group cursor-pointer"
                        :class="activeTab === tab.id ? 'bg-blue-50/90 text-blue-900 font-black shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                    >
                        <div class="flex items-center gap-2.5 truncate">
                            <span class="text-base">{{ tab.icon }}</span>
                            <span class="truncate">{{ tab.title }}</span>
                        </div>
                        <svg
                            class="w-4 h-4 transition-transform group-hover:translate-x-0.5 shrink-0"
                            :class="activeTab === tab.id ? 'text-blue-600' : 'text-slate-400'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Help & Grievance Card -->
                    <div class="mt-4 pt-4 border-t border-slate-100 p-3 bg-slate-50 rounded-xl space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Grievance Officer</span>
                        <p class="text-xs font-bold text-slate-800">{{ companyName }} Legal Desk</p>
                        <p class="text-[11px] text-slate-500">For legal notices, RERA verification queries, or disputes:</p>
                        <a
                            :href="`mailto:${companyEmail || 'legal@realhem.com'}`"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline block truncate"
                        >
                            {{ companyEmail || 'legal@realhem.com' }}
                        </a>
                        <p v-if="companyPhone" class="text-[11px] text-slate-600 font-semibold pt-1">
                            Toll-Free: {{ companyPhone }}
                        </p>
                    </div>
                </div>
            </aside>

            <!-- Document Content Area -->
            <main class="lg:col-span-8 xl:col-span-9 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-10 space-y-8 text-slate-700 text-sm leading-relaxed">
                <!-- Custom Document Content Rendered (if customized by admin) -->
                <div v-if="customContentForActiveTab" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ activeTabMeta.title }}</h2>
                        <p class="text-xs text-slate-500 mt-1">{{ activeTabMeta.subtitle }}</p>
                    </div>
                    <div class="space-y-4" v-html="renderFormattedContent(customContentForActiveTab)"></div>
                </div>

                <!-- Default Structured Content when no custom override is set -->
                <template v-else>
                    <!-- 1. Terms of Service -->
                    <section v-if="activeTab === 'terms'" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Terms &amp; Conditions of Service</h2>
                        <p class="text-xs text-slate-500 mt-1">Please read these Terms carefully before using {{ companyName }}.</p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">1. Introduction &amp; Acceptance</h3>
                        <p>
                            Welcome to <strong>{{ companyName }}</strong> ("Platform", "we", "our", "us"). By accessing, browsing, searching, or posting property advertisements on this portal, you agree to be bound by these Terms and Conditions and our Privacy Policy. If you do not agree to these terms, please do not use our services.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">2. Role of {{ companyName }} (Intermediary Status)</h3>
                        <p>
                            {{ companyName }} acts solely as an online real estate advertising portal and intermediary under Section 79 of the Information Technology Act, 2000. We provide an online marketplace connecting property owners, prospective buyers, tenants, registered real estate agents, and developers.
                        </p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li>{{ companyName }} is not a party to any contract, lease, sale, or agreement between property seekers and sellers/landlords.</li>
                            <li>We do not control, inspect, endorse, or guarantee the physical condition, legal title, encumbrance status, or pricing of any listed property.</li>
                            <li>Users are strongly advised to independently verify property ownership, municipal approvals, and RERA registration before entering any transaction or transferring funds.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">3. User Obligations &amp; Property Postings</h3>
                        <p>When posting a property listing on {{ companyName }}, you warrant and confirm that:</p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li>You are the lawful owner, authorized representative, or a licensed real estate broker possessing explicit written mandate to list the property.</li>
                            <li>All details provided, including carpet area, super built-up area, bedroom count, pricing, and possession timeline, are truthful and accurate.</li>
                            <li>Uploaded photographs depict the genuine subject property or sample flat, free from copyrighted watermarks of competing portals.</li>
                            <li>You will immediately update the status of the listing (e.g., mark as Sold or Rented) once a deal is finalized.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">4. Prohibited Activities</h3>
                        <p>Users shall not:</p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li>Post duplicate, fictitious, bait-and-switch, or ghost listings with artificial prices.</li>
                            <li>Scrape, crawl, or harvest contact details, broker rosters, or image assets without prior written consent.</li>
                            <li>Misrepresent role or license (e.g., acting as an unlicensed broker under the guise of an individual owner).</li>
                            <li>Distribute defamatory, fraudulent, or unsolicited spam content.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">5. Limitation of Liability</h3>
                        <p>
                            In no event shall {{ companyName }}, its directors, employees, or tech partners be liable for any direct, indirect, incidental, punitive, or consequential damages resulting from false listings, token money disputes, title defects, or delays in real estate construction.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">6. Governing Law &amp; Jurisdiction</h3>
                        <p>
                            These Terms are governed by and construed in accordance with the laws of the Republic of India. Any legal dispute or proceeding arising out of or related to this platform shall be subject to the exclusive jurisdiction of the competent courts in India.
                        </p>
                    </div>
                </section>

                <!-- 2. Privacy Policy -->
                <section v-else-if="activeTab === 'privacy'" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Privacy Policy &amp; Data Protection</h2>
                        <p class="text-xs text-slate-500 mt-1">How {{ companyName }} collects, uses, protects, and handles your personal information.</p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">1. Information We Collect</h3>
                        <p>To deliver real-time real estate searches and facilitate seller-buyer interactions, we collect:</p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li><strong>Account Data:</strong> Name, phone number, email address, user role (Owner, Agent, Builder), and country code.</li>
                            <li><strong>Property Information:</strong> Address, geo-coordinates (latitude/longitude), floor details, sale prices, and property photographs.</li>
                            <li><strong>Location &amp; Device Data:</strong> Real-time GPS location (only when you explicitly trigger "Near Me" or browser location detection), IP address, and browser session information.</li>
                            <li><strong>Search &amp; Inquiry Records:</strong> Shortlisted properties, saved filters, and inquiries submitted to view owner/broker contact numbers.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">2. How We Use Your Data</h3>
                        <p>Your data is used strictly for legitimate property marketplace services:</p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li>Connecting property seekers with verified owners or listing brokers.</li>
                            <li>Sending transaction updates, OTP authentications, and lead notifications via SMS, WhatsApp, and email.</li>
                            <li>Computing local real-estate market price trends, nearby locality suggestions, and home loan EMI estimates.</li>
                            <li>Preventing spam, fraudulent broker postings, and unauthorized account access.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">3. Information Sharing &amp; Lead Exchange</h3>
                        <p>
                            When you click <strong>"View Contact Number"</strong>, <strong>"Contact Seller"</strong>, or request a property brochure, your basic contact information (Name, Mobile number, and inquiry interest) is shared directly with the owner, developer, or authorized agent who listed that property.
                        </p>
                        <p class="text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            🔒 <strong>We Do Not Sell Your Data:</strong> {{ companyName }} does not sell, rent, or trade your personal data to non-affiliated third-party telemarketers.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">4. Cookies &amp; Local Storage</h3>
                        <p>
                            We utilize high-speed client-side local caching (`sessionStorage` and `localStorage`) to save your preferred city, recent searches, and UI theme preferences. You may clear your browser cookies and storage at any time via your browser settings.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">5. Data Retention &amp; User Rights</h3>
                        <p>
                            You have the right to request access to your saved listings, request rectification of erroneous data, or request permanent deletion of your account and property postings by contacting our support team at <a :href="`mailto:${companyEmail || 'privacy@realhem.com'}`" class="text-blue-600 font-bold hover:underline">{{ companyEmail || 'privacy@realhem.com' }}</a>.
                        </p>
                    </div>
                </section>

                <!-- 3. RERA Disclaimer -->
                <section v-else-if="activeTab === 'rera'" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">RERA Disclaimer &amp; Regulatory Compliance</h2>
                        <p class="text-xs text-slate-500 mt-1">Real Estate (Regulation and Development) Act, 2016 Guidelines &amp; Consumer Advisory.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm space-y-2">
                        <div class="flex items-center gap-2 font-black text-sm text-amber-950">
                            <span>🏛️</span>
                            <span>Statutory RERA Advisory for Property Buyers &amp; Investors</span>
                        </div>
                        <p>
                            Under the Real Estate (Regulation and Development) Act, 2016 (RERA), all residential and commercial real estate projects where land area exceeds 500 square meters or number of apartments exceeds eight must be registered with the respective State Real Estate Regulatory Authority.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">1. Intermediary / Advertising Platform Role</h3>
                        <p>
                            {{ companyName }} is an online advertising publisher and technological platform. Neither {{ companyName }} nor its affiliates act as a real estate developer, promoter, or contracting agent for any of the listed projects.
                        </p>
                        <p>
                            Project details, floor plans, approvals, possession dates, and promotional material displayed on this website are supplied directly by developers, listing brokers, or retrieved from public records.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">2. Verification Checklist for Home Buyers</h3>
                        <p>Before issuing any booking advance, token money, or executing an Agreement for Sale, all buyers must:</p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li>Visit the official State RERA portal (e.g., GujRERA for Gujarat, MahaRERA for Maharashtra, UP RERA for UP/Noida, HRERA for Gurugram/Haryana, K-RERA for Karnataka).</li>
                            <li>Verify the unique <strong>RERA Registration Number</strong>, sanctioned building plans, layout layout approvals, and title deed encumbrances.</li>
                            <li>Confirm the designated RERA escrow bank account where installment payments are legally required to be deposited.</li>
                            <li>Examine the sanctioned timeline for project handover and quarterly progress reports uploaded by the promoter.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">3. Real Estate Agent Registration</h3>
                        <p>
                            Real estate brokers and agents publishing listings on {{ companyName }} are required under Section 9 of RERA to maintain a valid RERA Agent Registration certificate. {{ companyName }} reserves the right to request proof of registration and to delist non-compliant advertisements.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">4. Reporting Non-Compliant Listings</h3>
                        <p>
                            If you identify any project advertisement on {{ companyName }} lacking a required RERA registration number or displaying inaccurate compliance credentials, please report it immediately to <a :href="`mailto:${companyEmail || 'compliance@realhem.com'}`" class="text-blue-600 font-bold hover:underline">{{ companyEmail || 'compliance@realhem.com' }}</a> for prompt investigation and takedown.
                        </p>
                    </div>
                </section>

                <!-- 4. Refund & Cancellation Policy -->
                <section v-else class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Refund &amp; Cancellation Policy</h2>
                        <p class="text-xs text-slate-500 mt-1">Payment terms, package validity, and dispute resolution for {{ companyName }} services.</p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">1. Free Property Listings</h3>
                        <p>
                            Basic property listings posted by individual owners are offered <strong>100% Free of Cost</strong> on {{ companyName }}. There are no hidden subscription charges or mandatory brokerage commissions levied by the platform on standard free postings.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">2. Paid Promotional Packages &amp; Featured Listings</h3>
                        <p>
                            For premium marketing services, featured property badges, broker banner slots, and developer lead generation bundles:
                        </p>
                        <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-600">
                            <li>Payments are processed in Indian Rupees (INR) via secure, RBI-compliant payment gateways.</li>
                            <li>Once a listing promotion or featured badge has been activated and published on the live portal, fees are non-refundable as the digital advertising service has been delivered.</li>
                            <li>If a listing is rejected by moderation due to regulatory or policy violations prior to publishing, a full 100% refund will be credited back to the original payment source within 5–7 business days.</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">3. Cancellation of Paid Subscriptions</h3>
                        <p>
                            Brokers and developers subscribed to recurring quarterly or annual lead packages may cancel renewal at any time via the Broker Portal or by contacting account management. Cancellation prevents future billing cycles while maintaining active visibility until the end of the paid term.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-900">4. Duplicate or Accidental Transactions</h3>
                        <p>
                            In the event of an accidental double charge or network failure resulting in debited funds without package activation, please write to <a :href="`mailto:${companyEmail || 'billing@realhem.com'}`" class="text-blue-600 font-bold hover:underline">{{ companyEmail || 'billing@realhem.com' }}</a> with your transaction reference number. Verified duplicates will be refunded in full without penalty.
                        </p>
                    </div>
                </section>
                </template>

                <!-- Help CTA Banner -->
                <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50 p-5 rounded-2xl">
                    <div class="space-y-0.5 text-center sm:text-left">
                        <h4 class="text-xs font-black text-slate-900">Need specific legal or compliance clarification?</h4>
                        <p class="text-[11px] text-slate-500">Our customer legal grievance desk is available Monday through Saturday.</p>
                    </div>
                    <a
                        :href="`mailto:${companyEmail || 'support@realhem.com'}`"
                        class="px-4 py-2 rounded-xl text-white font-bold text-xs shadow-xs transition hover:opacity-95 shrink-0"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Contact Legal Support &rarr;
                    </a>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const route = useRoute();
const router = useRouter();
const { companyName, companyEmail, companyPhone, brandPrimaryColor } = useCompanyBranding();

useSeoMeta({
    title: () => `${activeTabMeta.value.title} • Legal Center`,
    description: () => activeTabMeta.value.subtitle,
    ogTitle: () => `${activeTabMeta.value.title} • Legal Center`,
    ogDescription: () => activeTabMeta.value.subtitle,
});

const legalData = ref({
    terms: '',
    privacy: '',
    rera: '',
    refund: '',
    updated_at: 'October 2026',
    version: '2.4',
});

const tabs = [
    {
        id: 'terms',
        title: 'Terms of Service',
        subtitle: `Guidelines and conditions governing the use of the ${companyName.value} real estate platform.`,
        icon: '📜',
        path: '/terms',
    },
    {
        id: 'privacy',
        title: 'Privacy Policy',
        subtitle: 'Our commitment to protecting your personal data, inquiries, and geographic privacy.',
        icon: '🛡️',
        path: '/privacy',
    },
    {
        id: 'rera',
        title: 'RERA Compliance & Disclaimer',
        subtitle: 'Statutory compliance details under the Real Estate (Regulation and Development) Act.',
        icon: '🏛️',
        path: '/rera-disclaimer',
    },
    {
        id: 'refund',
        title: 'Refund & Cancellation',
        subtitle: 'Clear terms on listing promotions, subscription validity, and payment policies.',
        icon: '💳',
        path: '/refund-policy',
    },
];

const activeTab = computed(() => {
    const p = route.path;
    if (p.includes('privacy')) return 'privacy';
    if (p.includes('rera')) return 'rera';
    if (p.includes('refund')) return 'refund';
    return 'terms';
});

const activeTabMeta = computed(() => {
    return tabs.find(t => t.id === activeTab.value) || tabs[0];
});

const switchTab = (tabId) => {
    const target = tabs.find(t => t.id === tabId);
    if (target) {
        router.push(target.path);
    }
};

const customContentForActiveTab = computed(() => {
    const raw = legalData.value[activeTab.value];
    return raw && raw.trim().length > 0 ? raw : null;
});

const renderFormattedContent = (raw) => {
    if (!raw) return '';

    // If it already looks like full HTML with tags, return it
    if (/<[a-z][\s\S]*>/i.test(raw)) {
        return raw;
    }

    // Markdown-style formatting for headers, bullet points, bold, and paragraphs
    return raw
        .split('\n\n')
        .map(paragraph => {
            paragraph = paragraph.trim();
            if (!paragraph) return '';

            // Heading 3
            if (paragraph.startsWith('### ')) {
                const headingText = paragraph.replace(/^###\s+/, '');
                return `<h3 class="text-base sm:text-lg font-bold text-slate-900 mt-6 mb-3">${headingText}</h3>`;
            }

            // Bullet list
            if (paragraph.startsWith('- ') || paragraph.startsWith('* ')) {
                const items = paragraph.split('\n').map(line => {
                    const cleanLine = line.replace(/^[-*]\s+/, '').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    return `<li class="py-1">${cleanLine}</li>`;
                }).join('');
                return `<ul class="list-disc pl-5 space-y-1 text-slate-600 my-3 text-xs sm:text-sm">${items}</ul>`;
            }

            // Standard paragraph with bold formatting
            const parsedText = paragraph
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br/>');

            return `<p class="leading-relaxed my-3 text-slate-700 text-xs sm:text-sm">${parsedText}</p>`;
        })
        .join('');
};

const fetchLegalData = async () => {
    try {
        const res = await fetch('/api/legal', {
            headers: {
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (data && data.legal) {
            legalData.value = {
                terms: data.legal.terms || '',
                privacy: data.legal.privacy || '',
                rera: data.legal.rera || '',
                refund: data.legal.refund || '',
                updated_at: data.legal.updated_at || 'October 2026',
                version: data.legal.version || '2.4',
            };
        }
    } catch (err) {
        // Fallback to default hardcoded policies gracefully
        console.error('Failed to load dynamic legal settings:', err);
    }
};

onMounted(() => {
    fetchLegalData();
});
</script>

