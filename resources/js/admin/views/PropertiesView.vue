<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <AdminNavbar />

        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Property Listings &amp; Moderation</h1>
                    <p class="text-sm text-slate-500 mt-1">Review, approve, and feature residential and commercial properties</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-50 text-blue-700 font-semibold px-3 py-1.5 rounded-xl border border-blue-200">
                        Module Ready for Phase 2
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8 text-center">
                <div class="max-w-md mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Property Listing Catalog</h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        The property database schema with categories (Buy, Rent, Plots, Commercial), amenities, media uploads, and approval workflow is ready to be linked here.
                    </p>
                    <router-link to="/dashboard" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-700">
                        &larr; Return to Dashboard
                    </router-link>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import AdminNavbar from '../components/AdminNavbar.vue';
</script>
