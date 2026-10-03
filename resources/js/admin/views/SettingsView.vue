<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <AdminNavbar />

        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                            Theme &amp; Brand Appearance Studio
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Full-Site White Label
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Control colors, typography, hero gradients, announcement ribbons, logos, and footer across every page in real time.
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <button
                        type="button"
                        @click="resetToDefaults"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                    >
                        Restore Defaults
                    </button>
                    <button
                        type="button"
                        @click="saveSettings"
                        :disabled="isSaving"
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg v-if="isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span v-if="isSaving">Publishing Changes...</span>
                        <span v-else>Save &amp; Apply Site-Wide</span>
                    </button>
                </div>
            </div>

            <!-- Success / Error Feedback Alerts -->
            <transition name="fade">
                <div v-if="successMessage" class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ successMessage }}</span>
                    </div>
                    <button @click="successMessage = ''" class="text-emerald-600 hover:text-emerald-900 cursor-pointer font-bold">✕</button>
                </div>
            </transition>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Settings Control Panels (7 cols) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- 1-Click Theme Presets -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">1. One-Click Theme Presets</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Quickly apply expertly designed color palettes &amp; layouts</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">8 Presets</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <button
                                v-for="preset in themePresets"
                                :key="preset.name"
                                type="button"
                                @click="applyPreset(preset)"
                                class="p-3 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between space-y-2 hover:shadow-md"
                                :class="form.brand_primary_color === preset.primary ? 'border-blue-600 ring-2 ring-blue-500/20 bg-blue-50/20' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50'"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded-full border border-white shadow-2xs" :style="{ backgroundColor: preset.primary }"></span>
                                    <span class="w-3 h-3 rounded-full border border-white shadow-2xs" :style="{ backgroundColor: preset.secondary }"></span>
                                    <span class="w-2.5 h-2.5 rounded-full border border-white shadow-2xs" :style="{ backgroundColor: preset.accent }"></span>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block leading-tight">{{ preset.name }}</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ preset.font }}</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Visual Colors Studio -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">2. Brand Colors Customizer</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Fine-tune the primary, secondary, and CTA action colors</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Primary Color -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700">Primary Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.brand_primary_color"
                                        type="color"
                                        class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-0.5 bg-transparent"
                                    />
                                    <input
                                        v-model="form.brand_primary_color"
                                        type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-2.5 text-xs font-mono font-bold uppercase outline-none focus:border-blue-500"
                                    />
                                </div>
                                <span class="text-[10px] text-slate-400 block">Headers, search buttons, prices</span>
                            </div>

                            <!-- Secondary Color -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700">Secondary Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.brand_secondary_color"
                                        type="color"
                                        class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-0.5 bg-transparent"
                                    />
                                    <input
                                        v-model="form.brand_secondary_color"
                                        type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-2.5 text-xs font-mono font-bold uppercase outline-none focus:border-blue-500"
                                    />
                                </div>
                                <span class="text-[10px] text-slate-400 block">Hero gradients &amp; sub-headers</span>
                            </div>

                            <!-- Accent CTA Color -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700">Accent / CTA Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.brand_accent_color"
                                        type="color"
                                        class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-0.5 bg-transparent"
                                    />
                                    <input
                                        v-model="form.brand_accent_color"
                                        type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-2.5 text-xs font-mono font-bold uppercase outline-none focus:border-blue-500"
                                    />
                                </div>
                                <span class="text-[10px] text-slate-400 block">Post Property button &amp; badges</span>
                            </div>
                        </div>
                    </div>

                    <!-- Typography & Geometry -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">3. Typography &amp; UI Curvature</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Select your portal's typeface and card corner radius</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Font Family -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Font Family</label>
                                <select
                                    v-model="form.theme_font_family"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold outline-none focus:border-blue-500 cursor-pointer"
                                >
                                    <option value="Instrument Sans">Instrument Sans (Modern Standard)</option>
                                    <option value="Plus Jakarta Sans">Plus Jakarta Sans (Sleek Geometric)</option>
                                    <option value="Inter">Inter (Ultra Clean Digital)</option>
                                    <option value="Poppins">Poppins (Friendly Rounded)</option>
                                    <option value="Outfit">Outfit (Luxury Real Estate)</option>
                                    <option value="Roboto">Roboto (Classic Neutral)</option>
                                </select>
                            </div>

                            <!-- Border Radius -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Card &amp; Button Curvature</label>
                                <select
                                    v-model="form.theme_border_radius"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold outline-none focus:border-blue-500 cursor-pointer"
                                >
                                    <option value="4px">Sharp Minimalist (4px)</option>
                                    <option value="8px">Subtle Rounded (8px)</option>
                                    <option value="16px">Modern Smooth (16px - Default)</option>
                                    <option value="24px">Super Pill Curved (24px)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hero Style Selector -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Hero Background Style</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                <button
                                    v-for="hero in heroStyles"
                                    :key="hero.id"
                                    type="button"
                                    @click="form.hero_style = hero.id"
                                    class="p-2.5 rounded-xl border text-xs font-bold text-left transition cursor-pointer flex items-center gap-2"
                                    :class="form.hero_style === hero.id ? 'border-blue-600 bg-blue-50/40 text-blue-900' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'"
                                >
                                    <span class="w-3.5 h-3.5 rounded-full" :style="{ background: hero.gradient }"></span>
                                    <span>{{ hero.name }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Top Announcement Ribbon Bar -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">4. Top Announcement Ribbon Bar</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Show a promotional banner at the very top of all client pages</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    v-model="form.banner_enabled"
                                    type="checkbox"
                                    true-value="true"
                                    false-value="false"
                                    class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer"
                                />
                                <span class="text-xs font-bold text-slate-700">Enable Ribbon</span>
                            </label>
                        </div>

                        <div v-if="form.banner_enabled === 'true' || form.banner_enabled === true" class="space-y-3 animate-fadeIn">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Badge Text</label>
                                    <input
                                        v-model="form.banner_badge"
                                        type="text"
                                        placeholder="e.g. MEGA LAUNCH, FEST 2026"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold outline-none focus:border-blue-500"
                                    />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Banner Announcement Text</label>
                                    <input
                                        v-model="form.banner_text"
                                        type="text"
                                        placeholder="e.g. India's Biggest Property Fest 2026: Zero Brokerage on 10,000+ Verified Homes"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium outline-none focus:border-blue-500"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Target Click Link</label>
                                <input
                                    v-model="form.banner_link"
                                    type="text"
                                    placeholder="/listings?badge=fest"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-mono outline-none focus:border-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Brand Identity & Metadata -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">5. Brand Identity &amp; Monograms</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Control company name, initials, and logos</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Company / Portal Name</label>
                                <input
                                    v-model="form.company_name"
                                    type="text"
                                    placeholder="e.g. RealHem, 99acres"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-bold outline-none focus:border-blue-500 focus:bg-white transition"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Initials / Short Monogram</label>
                                <input
                                    v-model="form.company_short_name"
                                    type="text"
                                    maxlength="6"
                                    placeholder="e.g. RH, 99"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-bold uppercase outline-none focus:border-blue-500 focus:bg-white transition"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Primary Tagline</label>
                            <input
                                v-model="form.company_tagline"
                                type="text"
                                placeholder="e.g. Real Estate Architecture & Portal"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-medium outline-none focus:border-blue-500 focus:bg-white transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Custom Logo Image URL (Optional)</label>
                            <input
                                v-model="form.company_logo_url"
                                type="url"
                                placeholder="https://example.com/logo.png (leave empty for monogram)"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono outline-none focus:border-blue-500 focus:bg-white transition"
                            />
                        </div>
                    </div>

                    <!-- Contact & Communication -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">6. Support &amp; Contact Info</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Displayed to buyers and sellers for inquiries</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Toll-Free Phone</label>
                                <input
                                    v-model="form.company_phone"
                                    type="text"
                                    placeholder="1800-41-9999"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold outline-none focus:border-blue-500 focus:bg-white"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Support Email</label>
                                <input
                                    v-model="form.company_email"
                                    type="email"
                                    placeholder="contact@realhem.com"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold outline-none focus:border-blue-500 focus:bg-white"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Footer & Legal -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">7. Footer &amp; Legal</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Set the company description paragraph and copyright line</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Footer About Paragraph</label>
                            <textarea
                                v-model="form.footer_about"
                                rows="3"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium outline-none focus:border-blue-500 focus:bg-white"
                                placeholder="Describe your real estate portal..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Copyright Line</label>
                            <input
                                v-model="form.copyright_text"
                                type="text"
                                placeholder="© 2026 RealHem India Pvt. Ltd. All rights reserved."
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-medium outline-none focus:border-blue-500 focus:bg-white"
                            />
                        </div>
                    </div>

                </div>

                <!-- Right: Real-time Multi-Component Live Preview (5 cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="sticky top-24 space-y-5">
                        <div class="bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 shadow-xl space-y-5">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                                    Full-Site Live Preview
                                </span>
                                <span class="text-[10px] text-slate-400">Updates live</span>
                            </div>

                            <!-- Preview 0: Announcement Ribbon -->
                            <div v-if="form.banner_enabled === 'true' || form.banner_enabled === true" class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Announcement Ribbon</span>
                                <div
                                    class="py-1.5 px-3 rounded-lg text-white text-[11px] font-bold flex items-center justify-between shadow-xs"
                                    :style="{ backgroundColor: form.brand_primary_color || '#005ca8' }"
                                >
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-400 text-slate-900 uppercase">
                                            {{ form.banner_badge || 'NEW' }}
                                        </span>
                                        <span class="truncate">{{ form.banner_text }}</span>
                                    </div>
                                    <span class="text-[10px] underline ml-2 flex-shrink-0">Explore &rarr;</span>
                                </div>
                            </div>

                            <!-- Preview 1: Header / Navbar -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Main Navbar</span>
                                <div class="bg-white text-slate-800 p-3 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div
                                            v-if="form.company_logo_url"
                                            class="w-7 h-7 rounded-lg overflow-hidden flex items-center justify-center border border-slate-200"
                                        >
                                            <img :src="form.company_logo_url" alt="Logo" class="w-full h-full object-contain" />
                                        </div>
                                        <div
                                            v-else
                                            class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs text-white shadow-xs"
                                            :style="{ backgroundColor: form.brand_primary_color || '#005ca8' }"
                                        >
                                            {{ form.company_short_name || 'RH' }}
                                        </div>
                                        <div>
                                            <span class="font-black text-xs tracking-tight text-slate-900 block leading-none">
                                                {{ form.company_name || 'RealHem' }}<span :style="{ color: form.brand_primary_color || '#005ca8' }">.</span>
                                            </span>
                                            <span class="text-[8px] font-bold uppercase text-slate-400 tracking-wide block mt-0.5">
                                                {{ form.company_tagline || 'Real Estate' }}
                                            </span>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg text-white font-bold text-[10px] shadow-xs cursor-default"
                                        :style="{ backgroundColor: form.brand_accent_color || '#ff6b35' }"
                                    >
                                        Post Property
                                    </button>
                                </div>
                            </div>

                            <!-- Preview 2: Hero Section & Search Bar -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hero Section &amp; Search Card</span>
                                <div
                                    class="p-4 rounded-xl text-white shadow-md space-y-3"
                                    :style="{ background: previewHeroGradient }"
                                >
                                    <div class="text-center space-y-1">
                                        <h3 class="font-black text-sm text-white">Find Your Dream Home</h3>
                                        <p class="text-[10px] text-white/80">Search from 50,000+ verified listings</p>
                                    </div>

                                    <!-- Search Box mockup -->
                                    <div class="bg-white p-2 rounded-xl text-slate-800 shadow-sm space-y-2">
                                        <div class="flex gap-1 border-b border-slate-100 pb-1.5">
                                            <span
                                                class="text-[10px] font-bold px-2 py-0.5 rounded-md text-white"
                                                :style="{ backgroundColor: form.brand_primary_color || '#005ca8' }"
                                            >Buy</span>
                                            <span class="text-[10px] font-medium text-slate-500 px-2 py-0.5">Rent</span>
                                            <span class="text-[10px] font-medium text-slate-500 px-2 py-0.5">Commercial</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex-1 bg-slate-50 px-2 py-1 rounded-lg text-[10px] text-slate-400 border border-slate-200">
                                                Enter locality or builder...
                                            </div>
                                            <button
                                                type="button"
                                                class="px-3 py-1 rounded-lg text-white font-bold text-[10px]"
                                                :style="{ backgroundColor: form.brand_primary_color || '#005ca8' }"
                                            >
                                                Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview 3: Property Card Preview -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Listing Card &amp; Price Tag</span>
                                <div class="bg-white p-3 rounded-xl border border-slate-200 text-slate-800 space-y-2">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <span class="text-xs font-black text-slate-900 block">Luxury 3 BHK Villa</span>
                                            <span class="text-[10px] text-slate-400 block">Bopal, Ahmedabad</span>
                                        </div>
                                        <span class="text-xs font-black" :style="{ color: form.brand_primary_color || '#005ca8' }">
                                            ₹ 1.25 Crore
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">3 Beds &bull; 1,850 sq.ft</span>
                                        <span
                                            class="text-[9px] font-bold px-2 py-0.5 rounded text-white"
                                            :style="{ backgroundColor: form.brand_primary_color || '#005ca8' }"
                                        >
                                            View Details
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview 4: Footer Snippet -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Footer Snippet</span>
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 text-slate-300 text-[11px] space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-white">{{ form.company_name || 'RealHem' }}</span>
                                        <span class="text-[10px] text-slate-400">📞 {{ form.company_phone || '1800-41-9999' }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 line-clamp-1">
                                        {{ form.footer_about }}
                                    </p>
                                    <div class="pt-1 border-t border-slate-800 text-[9px] text-slate-500">
                                        {{ form.copyright_text }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Save Floating Box -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Deploy Site Theme</span>
                                <span class="text-[11px] text-slate-400">Live across all devices</span>
                            </div>
                            <button
                                type="button"
                                @click="saveSettings"
                                :disabled="isSaving"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/25 transition cursor-pointer"
                            >
                                {{ isSaving ? 'Saving...' : 'Save & Publish' }}
                            </button>
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

const themePresets = [
    {
        name: '99acres Royal',
        primary: '#005ca8',
        secondary: '#1e40af',
        accent: '#ff6b35',
        hero_style: 'midnight_blue',
        font: 'Instrument Sans',
        radius: '16px',
    },
    {
        name: '99bigha Coral',
        primary: '#ff6b35',
        secondary: '#ea580c',
        accent: '#059669',
        hero_style: 'coral_sunset',
        font: 'Plus Jakarta Sans',
        radius: '16px',
    },
    {
        name: 'Emerald Luxury',
        primary: '#059669',
        secondary: '#064e3b',
        accent: '#d97706',
        hero_style: 'emerald_luxury',
        font: 'Outfit',
        radius: '18px',
    },
    {
        name: 'Royal Indigo',
        primary: '#4f46e5',
        secondary: '#312e81',
        accent: '#10b981',
        hero_style: 'royal_indigo',
        font: 'Inter',
        radius: '16px',
    },
    {
        name: 'Amber & Gold',
        primary: '#d97706',
        secondary: '#78350f',
        accent: '#2563eb',
        hero_style: 'midnight_blue',
        font: 'Poppins',
        radius: '12px',
    },
    {
        name: 'Crimson Prime',
        primary: '#dc2626',
        secondary: '#991b1b',
        accent: '#f59e0b',
        hero_style: 'midnight_blue',
        font: 'Instrument Sans',
        radius: '16px',
    },
    {
        name: 'Dark Slate Tech',
        primary: '#0f172a',
        secondary: '#1e293b',
        accent: '#3b82f6',
        hero_style: 'midnight_blue',
        font: 'Plus Jakarta Sans',
        radius: '12px',
    },
    {
        name: 'Violet Luxe',
        primary: '#7c3aed',
        secondary: '#4c1d95',
        accent: '#ec4899',
        hero_style: 'royal_indigo',
        font: 'Outfit',
        radius: '20px',
    },
];

const heroStyles = [
    { id: 'midnight_blue', name: 'Brand Gradient', gradient: 'linear-gradient(135deg, #0f172a, #005ca8)' },
    { id: 'coral_sunset', name: 'Coral Sunset', gradient: 'linear-gradient(135deg, #ea580c, #f97316)' },
    { id: 'emerald_luxury', name: 'Emerald Luxury', gradient: 'linear-gradient(135deg, #064e3b, #059669)' },
    { id: 'royal_indigo', name: 'Royal Indigo', gradient: 'linear-gradient(135deg, #312e81, #4338ca)' },
    { id: 'minimal_white', name: 'Minimal White', gradient: 'linear-gradient(180deg, #f8fafc, #ffffff)' },
];

const form = ref({
    company_name: 'RealHem',
    company_short_name: 'RH',
    company_tagline: '99acres Real Estate Architecture & Portal',
    company_sub_tagline: 'Buy, Rent, PG, Commercial & Builder Projects',
    company_logo_url: '',
    company_phone: '1800-41-9999',
    company_email: 'contact@realhem.com',
    brand_primary_color: '#005ca8',
    brand_secondary_color: '#1e40af',
    brand_accent_color: '#ff6b35',
    theme_font_family: 'Instrument Sans',
    theme_border_radius: '16px',
    header_style: 'clean_white',
    hero_style: 'midnight_blue',
    banner_enabled: 'true',
    banner_text: "India's Biggest Property Fest 2026: Zero Brokerage on 10,000+ Verified Homes",
    banner_badge: 'MEGA LAUNCH',
    banner_link: '/listings?badge=fest',
    footer_style: 'dark_slate',
    footer_about: 'RealHem is India\'s premier full-stack real estate discovery and property transaction portal, connecting verified property owners, top developers, and licensed brokers with over 5 Lakh+ buyers and tenants every week.',
    copyright_text: '© 2026 RealHem India Pvt. Ltd. All rights reserved.',
});

const previewHeroGradient = computed(() => {
    const p = form.value.brand_primary_color || '#005ca8';
    const s = form.value.brand_secondary_color || '#1e40af';
    if (form.value.hero_style === 'coral_sunset') {
        return 'linear-gradient(135deg, #ea580c 0%, #f97316 50%, #7c2d12 100%)';
    }
    if (form.value.hero_style === 'emerald_luxury') {
        return 'linear-gradient(135deg, #064e3b 0%, #059669 50%, #022c22 100%)';
    }
    if (form.value.hero_style === 'royal_indigo') {
        return 'linear-gradient(135deg, #312e81 0%, #4338ca 50%, #1e1b4b 100%)';
    }
    if (form.value.hero_style === 'minimal_white') {
        return 'linear-gradient(180deg, #f8fafc 0%, #ffffff 100%)';
    }
    return `linear-gradient(135deg, #0f172a 0%, ${p} 50%, ${s} 100%)`;
});

const applyPreset = (preset) => {
    form.value.brand_primary_color = preset.primary;
    form.value.brand_secondary_color = preset.secondary;
    form.value.brand_accent_color = preset.accent;
    form.value.hero_style = preset.hero_style;
    form.value.theme_font_family = preset.font;
    form.value.theme_border_radius = preset.radius;
};

const fetchSettings = async () => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch('/api/admin/settings', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        const data = await res.json();
        if (data.settings) {
            form.value = { ...form.value, ...data.settings };
        }
    } catch (err) {
        console.error('Failed to load branding settings:', err);
    }
};

const saveSettings = async () => {
    isSaving.value = true;
    successMessage.value = '';
    const token = localStorage.getItem('realhem_admin_token');

    try {
        const res = await fetch('/api/admin/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(form.value),
        });

        const data = await res.json();
        if (res.ok) {
            successMessage.value = data.message || 'Theme & branding settings published successfully!';
            localStorage.setItem('realhem_branding', JSON.stringify(form.value));
            window.dispatchEvent(new CustomEvent('realhem-branding-updated', { detail: form.value }));
            setTimeout(() => {
                successMessage.value = '';
            }, 4000);
        } else {
            alert(data.message || 'Validation error while saving settings.');
        }
    } catch (err) {
        alert('Network error while saving settings.');
    } finally {
        isSaving.value = false;
    }
};

const resetToDefaults = () => {
    form.value = {
        company_name: 'RealHem',
        company_short_name: 'RH',
        company_tagline: '99acres Real Estate Architecture & Portal',
        company_sub_tagline: 'Buy, Rent, PG, Commercial & Builder Projects',
        company_logo_url: '',
        company_phone: '1800-41-9999',
        company_email: 'contact@realhem.com',
        brand_primary_color: '#005ca8',
        brand_secondary_color: '#1e40af',
        brand_accent_color: '#ff6b35',
        theme_font_family: 'Instrument Sans',
        theme_border_radius: '16px',
        header_style: 'clean_white',
        hero_style: 'midnight_blue',
        banner_enabled: 'true',
        banner_text: "India's Biggest Property Fest 2026: Zero Brokerage on 10,000+ Verified Homes",
        banner_badge: 'MEGA LAUNCH',
        banner_link: '/listings?badge=fest',
        footer_style: 'dark_slate',
        footer_about: 'RealHem is India\'s premier full-stack real estate discovery and property transaction portal, connecting verified property owners, top developers, and licensed brokers with over 5 Lakh+ buyers and tenants every week.',
        copyright_text: '© 2026 RealHem India Pvt. Ltd. All rights reserved.',
    };
};

onMounted(() => {
    fetchSettings();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
