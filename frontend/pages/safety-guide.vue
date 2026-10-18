<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <router-link to="/" class="hover:text-blue-600 transition">Home</router-link>
            <span>/</span>
            <span class="text-slate-800 font-bold">Company</span>
            <span>/</span>
            <span class="text-blue-600 font-extrabold">Safety Guide</span>
        </nav>

        <!-- Header Hero -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-2xl relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <span>🛡️</span>
                    <span>Consumer Protection &amp; Anti-Fraud Advisory</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    Real Estate Safety &amp; Fraud Prevention Guide
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Essential guidelines and precautions for buyers, renters, and property owners to ensure 100% safe, transparent, and legally protected property transactions.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 relative z-10">
                <router-link
                    to="/report-problem"
                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition flex items-center gap-1.5"
                >
                    <span>🚨</span>
                    <span>Report Suspicious Listing</span>
                </router-link>
            </div>
        </div>

        <!-- 4 Golden Rules of Property Safety -->
        <div class="space-y-6">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">The 4 Golden Rules of Real Estate Safety</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        1
                    </div>
                    <h3 class="text-sm font-black text-slate-900">Never Pay Token Before Physical Inspection</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Never transfer token money, gate pass fees, or booking advances over UPI/bank transfer without personally inspecting the physical property and meeting the owner.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        2
                    </div>
                    <h3 class="text-sm font-black text-slate-900">Cross-Verify RERA Credentials</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Check project registrations on the official State RERA portal (e.g. GujRERA, MahaRERA, HRERA). Ensure payments are made strictly into the designated RERA Escrow account.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        3
                    </div>
                    <h3 class="text-sm font-black text-slate-900">Inspect Original Title Deeds</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Verify index-II, property tax receipts, electricity bills in the seller's name, and clearance certificates from the society committee before executing any agreement.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        4
                    </div>
                    <h3 class="text-sm font-black text-slate-900">Only Traceable Banking Channels</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Make all transactions through verified RTGS, NEFT, Cheque, or verified escrow gateways. Never transact in untraceable cash or unofficial channels.
                    </p>
                </div>
            </div>
        </div>

        <!-- Common Red Flags & Scams Explained -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-10 space-y-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Warning Signs &amp; Common Real Estate Scams</h2>
                <p class="text-xs text-slate-500 mt-1">If you notice any of these red flags during your interaction, discontinue communication and notify our trust desk.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-rose-600 font-black text-sm">
                        <span>❌</span>
                        <h4>"Gate Pass Fee" or "Visit Booking Amount"</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Fraudsters impersonate landlords and ask prospective tenants to pay ₹2,000–₹5,000 to "generate an entry gate pass" or "security slip" before showing the flat. <strong>No genuine landlord or society ever asks for a gate pass fee online.</strong>
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-rose-600 font-black text-sm">
                        <span>❌</span>
                        <h4>Impersonation of Armed Forces / Government Officials</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Scammers send fake army identity cards and claim they are deployed at border postings or airports, offering luxury flats at 50% discount if an advance is transferred immediately. Always insist on in-person physical meetings.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-rose-600 font-black text-sm">
                        <span>❌</span>
                        <h4>QR Code / UPI PIN Reverse Fraud</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        When you list a property for rent or sale, a fake buyer calls claiming they want to transfer advance deposit immediately. They send a QR code and ask you to enter your UPI PIN. <strong>Remember: Entering your UPI PIN always DEBITS money from your account, never credits it.</strong>
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-rose-600 font-black text-sm">
                        <span>❌</span>
                        <h4>Ghost or Duplicate Low-Price Listings (Bait &amp; Switch)</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Unscrupulous brokers post photos of luxury flats at unrealistically low prices to harvest phone leads. When contacted, they claim the unit was just rented and push inferior properties at higher rates. Report such listings immediately.
                    </p>
                </div>
            </div>
        </div>

        <!-- Safe Checklist Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-10 space-y-6">
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Essential Document Checklist for Buyers &amp; Tenants</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-800 font-black uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Transaction Type</th>
                            <th class="py-3 px-4">Mandatory Documents to Inspect</th>
                            <th class="py-3 px-4">Official Verification Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-slate-900">Buying Under-Construction Flat</td>
                            <td class="py-3.5 px-4">RERA Registration certificate, Sanctioned Building Plans, Commencment Certificate (CC), Allotment Letter.</td>
                            <td class="py-3.5 px-4 text-blue-600 font-semibold">State RERA Portal (e.g. GujRERA / MahaRERA)</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-slate-900">Buying Resale Property</td>
                            <td class="py-3.5 px-4">Original Sale Deed, Chain of Title, Encumbrance Certificate (30 years), NOC from Housing Society, Property Tax receipts.</td>
                            <td class="py-3.5 px-4 text-blue-600 font-semibold">Sub-Registrar Office &amp; Municipal Corporation</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-slate-900">Renting a Residential Home</td>
                            <td class="py-3.5 px-4">Owner's Government ID (Aadhaar/PAN), Recent Electricity Bill matching owner name, Registered Rent Agreement.</td>
                            <td class="py-3.5 px-4 text-blue-600 font-semibold">State Government e-Registration portal</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-slate-900">Commercial Lease / Office</td>
                            <td class="py-3.5 px-4">Occupancy Certificate (OC), Fire Safety NOC, Land Use Certificate, Commercial Electricity Meter bills.</td>
                            <td class="py-3.5 px-4 text-blue-600 font-semibold">Urban Development Authority / Fire Dept</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Help Desk Banner -->
        <div class="bg-gradient-to-r from-blue-900 to-indigo-950 rounded-3xl p-8 sm:p-10 text-white flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <h3 class="text-xl sm:text-2xl font-black">Spotted an irregularity or suspect fraud?</h3>
                <p class="text-xs sm:text-sm text-blue-200">Our Trust, Safety &amp; Legal Desk investigates reports within 24 hours.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <router-link
                    to="/report-problem"
                    class="px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition"
                >
                    File a Safety Report &rarr;
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
const { companyName } = useCompanyBranding();

useSeoMeta({
    title: () => `Property Safety Guide & Fraud Prevention Tips • ${companyName.value}`,
    description: () => `Essential safety recommendations, verification checklists, and anti-fraud guidelines for property buyers and tenants in India.`,
    ogTitle: () => `Property Safety Guide • ${companyName.value}`,
    ogDescription: () => `Essential safety recommendations, verification checklists, and anti-fraud guidelines for property buyers and tenants in India.`,
});
</script>

