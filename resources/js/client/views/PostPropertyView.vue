<template>
    <div class="min-h-screen bg-slate-50/70 pb-20">
        <!-- Auth Modal -->
        <AuthModal
            :is-open="isAuthModalOpen"
            @close="isAuthModalOpen = false"
            @authenticated="handleUserAuthenticated"
        />

        <!-- Top Header Banner (Dynamic white-label style) -->
        <div class="text-white py-8 px-4 sm:px-6 lg:px-8 shadow-sm transition-all duration-300" :style="{ background: heroGradient }">
            <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white uppercase tracking-wider">
                            100% FREE
                        </span>
                        <span class="text-xs text-blue-100 font-semibold">Zero Brokerage &middot; Direct Buyers &amp; Tenants</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Post Property on {{ companyName }}</h1>
                    <p class="text-xs sm:text-sm text-blue-100/90 mt-1">Get verified buyer inquiries, instant alerts, and close deals faster.</p>
                </div>

                <!-- User Status Pill -->
                <div v-if="currentUser" class="bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/20 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-400 text-slate-900 font-black text-sm flex items-center justify-center shadow-xs">
                        {{ userInitials }}
                    </div>
                    <div class="text-left">
                        <div class="text-xs font-bold text-white flex items-center gap-1.5">
                            <span>{{ currentUser.name }}</span>
                            <span class="text-[10px] bg-emerald-500/30 text-emerald-200 px-1.5 py-0.5 rounded capitalize font-medium">{{ currentUser.role || 'Owner' }}</span>
                        </div>
                        <div class="text-[11px] text-blue-100/80">{{ currentUser.country_code || '+91' }} {{ currentUser.mobile }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <!-- ========================================== -->
            <!-- GATEKEEPER: LOGIN REQUIRED (If not logged in) -->
            <!-- ========================================== -->
            <div v-if="!currentUser" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-8 animate-fadeIn">
                <div class="w-16 h-16 rounded-3xl bg-blue-50 text-[#005ca8] flex items-center justify-center mx-auto shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold tracking-wider text-blue-600 uppercase">Step 0: Verification</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Login First to Post Your Property</h2>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                        To protect our marketplace from spam and ensure genuine inquiries, please log in with your mobile number before listing your property.
                    </p>
                </div>

                <!-- 4 Value Points (99acres style) -->
                <div class="grid grid-cols-2 gap-3 text-left">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-2.5">
                        <span class="text-lg">🏷️</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Zero Brokerage</h4>
                            <p class="text-[11px] text-slate-500">Save thousands in broker commissions</p>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-2.5">
                        <span class="text-lg">⚡</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Instant Go-Live</h4>
                            <p class="text-[11px] text-slate-500">Listed across search in under 2 mins</p>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-2.5">
                        <span class="text-lg">🛡️</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Direct Inquiries</h4>
                            <p class="text-[11px] text-slate-500">Connect directly with authentic buyers</p>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-2.5">
                        <span class="text-lg">🌍</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Global Reach</h4>
                            <p class="text-[11px] text-slate-500">Accept NRI &amp; international buyers</p>
                        </div>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="space-y-3 pt-2">
                    <button
                        type="button"
                        @click="isAuthModalOpen = true"
                        class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm shadow-lg shadow-blue-500/25 transition cursor-pointer flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Login with Mobile Number to Continue &rarr;</span>
                    </button>

                    <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                        <span>Or test with demo account:</span>
                        <button
                            type="button"
                            @click="quickDemoLogin"
                            class="text-blue-600 font-bold hover:underline cursor-pointer"
                        >
                            1-Click Demo Login
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SUCCESS STATE: PROPERTY POSTED CELEBRATION -->
            <!-- ========================================== -->
            <div v-else-if="isSubmitted" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 text-center max-w-xl mx-auto space-y-6 animate-fadeIn">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl font-bold">
                    ✓
                </div>
                <div class="space-y-2">
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Congratulations!</span>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Your Property is Now Live on RealHem</h2>
                    <p class="text-xs text-slate-500">
                        Listing ID: <strong class="font-mono text-slate-800">#PROP-{{ createdProperty?.id || 'NEW' }}</strong> &middot; {{ createdProperty?.title }}
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left text-xs space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Location:</span> <strong class="text-slate-800">{{ form.locality }}, {{ form.city }}</strong></div>
                    <div class="flex justify-between"><span>Configuration:</span> <strong class="text-slate-800">{{ form.bedrooms }} BHK ({{ form.carpet_area }} sq.ft)</strong></div>
                    <div class="flex justify-between"><span>Expected Price:</span> <strong class="text-blue-700 font-black">{{ formatPriceWords(form.expected_price) }}</strong></div>
                    <div class="flex justify-between"><span>Status:</span> <span class="text-emerald-700 font-bold">Active &amp; Verified</span></div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <router-link
                        :to="`/listings?city=${encodeURIComponent(form.city)}`"
                        class="flex-1 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs text-center shadow-sm cursor-pointer transition"
                    >
                        View in Search Listings &rarr;
                    </router-link>
                    <button
                        type="button"
                        @click="resetFormForAnother"
                        class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs cursor-pointer transition"
                    >
                        + Post Another Property
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 99ACRES MULTI-STEP PROPERTY POSTING FORM   -->
            <!-- ========================================== -->
            <div v-else class="space-y-6">
                <!-- STEPPER PROGRESS HEADER (99acres layout) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-3 sm:p-4">
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="(st, idx) in steps"
                            :key="st.id"
                            type="button"
                            @click="canGoToStep(idx + 1) && (currentStep = idx + 1)"
                            :disabled="!canGoToStep(idx + 1)"
                            class="flex flex-col sm:flex-row items-center sm:items-center gap-1 sm:gap-2.5 p-2 rounded-xl transition text-left cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
                            :class="currentStep === idx + 1 ? 'bg-blue-50 text-blue-800' : 'text-slate-500 hover:bg-slate-50'"
                        >
                            <span
                                class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black shrink-0"
                                :class="currentStep === idx + 1 ? 'bg-blue-600 text-white' : (currentStep > idx + 1 ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600')"
                            >
                                <span v-if="currentStep > idx + 1">✓</span>
                                <span v-else>{{ idx + 1 }}</span>
                            </span>
                            <div class="hidden sm:block truncate">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block leading-tight">Step {{ idx + 1 }}</span>
                                <span class="text-xs font-bold truncate block" :class="currentStep === idx + 1 ? 'text-blue-900' : 'text-slate-700'">{{ st.title }}</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Form Container with Live Preview Side Card -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- MAIN FORM COLUMN (2 Cols) -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
                            <!-- Error Message Banner -->
                            <div v-if="errorMessage" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ errorMessage }}</span>
                            </div>

                            <!-- ============================================== -->
                            <!-- STEP 1: BASIC DETAILS & LOCATION               -->
                            <!-- ============================================== -->
                            <div v-if="currentStep === 1" class="space-y-6">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-black text-slate-900">Step 1: Basic Details &amp; Location</h3>
                                    <p class="text-xs text-slate-500">Provide basic classification and where the property is located.</p>
                                </div>

                                <!-- You Are (Owner, Agent, Builder) -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">You are:</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <button
                                            v-for="role in ['Owner', 'Agent', 'Builder']"
                                            :key="role"
                                            type="button"
                                            @click="form.user_type = role"
                                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                            :class="form.user_type === role ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                        >
                                            {{ role }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Property For (Sell, Rent, PG) -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Property is for:</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <button
                                            v-for="purp in [
                                                { id: 'Sell', label: 'Sale (Buy)' },
                                                { id: 'Rent', label: 'Rent / Lease' },
                                                { id: 'PG', label: 'PG / Co-Living' }
                                            ]"
                                            :key="purp.id"
                                            type="button"
                                            @click="form.property_for = purp.id"
                                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                            :class="form.property_for === purp.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                        >
                                            {{ purp.label }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Property Type -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Type of Property:</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <button
                                            v-for="pt in [
                                                'Residential Apartment',
                                                'Independent House/Villa',
                                                'Residential Plot',
                                                'Commercial Office',
                                                'Commercial Shop'
                                            ]"
                                            :key="pt"
                                            type="button"
                                            @click="form.property_type = pt"
                                            class="py-2 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-left flex items-center justify-between"
                                            :class="form.property_type === pt ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                        >
                                            <span>{{ pt }}</span>
                                            <span v-if="form.property_type === pt" class="text-blue-600">✓</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- City & Locality -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">City <span class="text-rose-500">*</span></label>
                                        <select
                                            v-model="form.city"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        >
                                            <option value="Ahmedabad">Ahmedabad</option>
                                            <option value="Delhi NCR">Delhi NCR</option>
                                            <option value="Mumbai">Mumbai</option>
                                            <option value="Bangalore">Bangalore</option>
                                            <option value="Pune">Pune</option>
                                            <option value="Hyderabad">Hyderabad</option>
                                            <option value="Chennai">Chennai</option>
                                            <option value="Kolkata">Kolkata</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Locality / Area <span class="text-rose-500">*</span></label>
                                        <input
                                            v-model="form.locality"
                                            type="text"
                                            placeholder="e.g. SG Highway, Bopal, Whitefield"
                                            required
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                </div>

                                <!-- Project / Society Name & Landmark -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Society / Project Name</label>
                                        <input
                                            v-model="form.project_name"
                                            type="text"
                                            placeholder="e.g. Godrej Garden City, Aarohi Elysium"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Landmark / Sub-locality</label>
                                        <input
                                            v-model="form.landmark"
                                            type="text"
                                            placeholder="e.g. Near Metro Station, Opp. City Mall"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                </div>

                                <!-- Address -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">House / Flat No. &amp; Street Address</label>
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        placeholder="e.g. Tower B, Flat 702, Main Road"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                    />
                                </div>

                                <div class="pt-4 flex justify-end">
                                    <button
                                        type="button"
                                        @click="goToStep(2)"
                                        :disabled="!form.locality || !form.city"
                                        class="py-3 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <span>Next: Property Profile &rarr;</span>
                                    </button>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- STEP 2: PROPERTY PROFILE & LAYOUT              -->
                            <!-- ============================================== -->
                            <div v-else-if="currentStep === 2" class="space-y-6">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-black text-slate-900">Step 2: Property Profile &amp; Dimensions</h3>
                                    <p class="text-xs text-slate-500">Specify bedrooms, layout, carpet area, and construction status.</p>
                                </div>

                                <!-- Bedrooms (BHK) -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">No. of Bedrooms (BHK):</label>
                                    <div class="grid grid-cols-5 gap-2">
                                        <button
                                            v-for="b in [1, 2, 3, 4, 5]"
                                            :key="b"
                                            type="button"
                                            @click="form.bedrooms = b"
                                            class="py-2.5 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                            :class="form.bedrooms === b ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                        >
                                            {{ b }} BHK
                                        </button>
                                    </div>
                                </div>

                                <!-- Bathrooms & Balconies -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Bathrooms</label>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button
                                                v-for="bath in [1, 2, 3, 4]"
                                                :key="bath"
                                                type="button"
                                                @click="form.bathrooms = bath"
                                                class="py-2 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                                :class="form.bathrooms === bath ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 border-slate-200 text-slate-700'"
                                            >
                                                {{ bath }}
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Balconies</label>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button
                                                v-for="balc in [0, 1, 2, 3]"
                                                :key="balc"
                                                type="button"
                                                @click="form.balconies = balc"
                                                class="py-2 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                                :class="form.balconies === balc ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 border-slate-200 text-slate-700'"
                                            >
                                                {{ balc }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Carpet Area & Super Built-up Area -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Carpet Area (Sq.Ft) <span class="text-rose-500">*</span></label>
                                        <input
                                            v-model="form.carpet_area"
                                            type="number"
                                            placeholder="e.g. 1250"
                                            required
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Super Built-up Area (Sq.Ft)</label>
                                        <input
                                            v-model="form.super_builtup_area"
                                            type="number"
                                            :placeholder="form.carpet_area ? String(Math.round(form.carpet_area * 1.3)) : 'e.g. 1650'"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                </div>

                                <!-- Furnishing Status -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Furnishing Status:</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <button
                                            v-for="furn in ['Unfurnished', 'Semi-Furnished', 'Furnished']"
                                            :key="furn"
                                            type="button"
                                            @click="form.furnishing_status = furn"
                                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                            :class="form.furnishing_status === furn ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                        >
                                            {{ furn }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Floor No. & Total Floors -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Property on Floor</label>
                                        <input
                                            v-model="form.floor_no"
                                            type="text"
                                            placeholder="e.g. 5 or Ground"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Floors in Building</label>
                                        <input
                                            v-model="form.total_floors"
                                            type="number"
                                            placeholder="e.g. 14"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>
                                </div>

                                <!-- Facing & Availability -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Facing Direction</label>
                                        <select
                                            v-model="form.facing"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        >
                                            <option value="East">East</option>
                                            <option value="North">North</option>
                                            <option value="North-East">North-East</option>
                                            <option value="West">West</option>
                                            <option value="South">South</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Construction Status</label>
                                        <select
                                            v-model="form.construction_status"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        >
                                            <option value="Ready to Move">Ready to Move</option>
                                            <option value="Under Construction">Under Construction</option>
                                            <option value="New Launch">New Launch</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-4 flex items-center justify-between">
                                    <button
                                        type="button"
                                        @click="goToStep(1)"
                                        class="py-2.5 px-5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition cursor-pointer"
                                    >
                                        &larr; Back
                                    </button>
                                    <button
                                        type="button"
                                        @click="goToStep(3)"
                                        :disabled="!form.carpet_area || form.carpet_area < 50"
                                        class="py-3 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <span>Next: Photos &amp; Amenities &rarr;</span>
                                    </button>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- STEP 3: PHOTOS & AMENITIES                     -->
                            <!-- ============================================== -->
                            <div v-else-if="currentStep === 3" class="space-y-6">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-black text-slate-900">Step 3: Photos &amp; Amenities</h3>
                                    <p class="text-xs text-slate-500">Add property photos and highlight society facilities to attract 5x more buyers.</p>
                                </div>

                            <!-- Photo Uploader -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Property Photos</label>
                                        <span class="text-[11px] font-semibold" :class="form.photos.length >= 10 ? 'text-emerald-600' : 'text-slate-400'">{{ form.photos.length }}/10 uploaded</span>
                                    </div>

                                    <!-- Always-mounted hidden file input (outside drop zone so it survives v-if) -->
                                    <input
                                        ref="fileInputRef"
                                        type="file"
                                        accept=".jpg,.jpeg,.webp,image/jpeg,image/webp"
                                        multiple
                                        class="hidden"
                                        @change="handleFileSelect"
                                    />

                                    <!-- Drop Zone — only when no photos yet -->
                                    <div
                                        v-if="form.photos.length === 0"
                                        @click="triggerFileInput"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleFileDrop"
                                        :class="[
                                            'rounded-2xl border-2 border-dashed p-8 text-center cursor-pointer transition',
                                            isDragging ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-blue-400 hover:bg-slate-50'
                                        ]"
                                    >
                                        <div class="space-y-2">
                                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto text-2xl">📸</div>
                                            <p class="text-sm font-bold text-slate-700">
                                                {{ isDragging ? 'Drop files here' : 'Click to upload or drag & drop' }}
                                            </p>
                                            <p class="text-xs text-slate-400">JPG, JPEG, WEBP only &bull; Max 10 photos &bull; Up to 5 MB each</p>
                                        </div>
                                    </div>

                                    <!-- Upload error -->
                                    <p v-if="photoUploadError" class="text-xs text-rose-600 font-semibold mt-2">{{ photoUploadError }}</p>

                                    <!-- Thumbnails grid + inline + Add slot -->
                                    <div v-if="form.photos.length > 0" class="mt-3 grid grid-cols-3 gap-3">
                                        <div
                                            v-for="(photo, pidx) in form.photos"
                                            :key="pidx"
                                            class="relative group rounded-xl overflow-hidden aspect-video border border-slate-200 bg-slate-100 shadow-2xs"
                                        >
                                            <img :src="photo" class="w-full h-full object-cover" alt="Preview" />
                                            <span v-if="pidx === 0" class="absolute bottom-1 left-1 bg-blue-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wide">Cover</span>
                                            <button
                                                type="button"
                                                @click="removePhoto(pidx)"
                                                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-slate-900/80 text-white flex items-center justify-center text-xs hover:bg-rose-600 transition cursor-pointer opacity-0 group-hover:opacity-100"
                                            >✕</button>
                                        </div>

                                        <!-- + Add photo slot — only visible when < 10 photos uploaded -->
                                        <button
                                            v-if="form.photos.length < 10"
                                            type="button"
                                            @click="triggerFileInput"
                                            class="aspect-video rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 hover:border-blue-400 hover:text-blue-500 hover:bg-blue-50 transition cursor-pointer gap-1"
                                        >
                                            <span class="text-2xl leading-none">+</span>
                                            <span class="text-[10px] font-bold">Add Photo</span>
                                        </button>
                                    </div>

                                    <p v-if="form.photos.length === 0" class="mt-3 text-xs text-center text-slate-400">No photos yet · At least 1 photo is recommended.</p>
                                </div>


                                <!-- Amenities Checkboxes -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Select Amenities:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <button
                                            v-for="amenity in availableAmenities"
                                            :key="amenity.name"
                                            type="button"
                                            @click="toggleAmenity(amenity.name)"
                                            class="p-2.5 rounded-xl border text-xs font-semibold flex items-center gap-2 transition cursor-pointer text-left"
                                            :class="form.amenities.includes(amenity.name) ? 'bg-blue-50 border-blue-500 text-blue-800 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                        >
                                            <span class="text-base">{{ amenity.icon }}</span>
                                            <span class="truncate">{{ amenity.name }}</span>
                                            <span v-if="form.amenities.includes(amenity.name)" class="ml-auto text-blue-600">✓</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="pt-4 flex items-center justify-between">
                                    <button
                                        type="button"
                                        @click="goToStep(2)"
                                        class="py-2.5 px-5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition cursor-pointer"
                                    >
                                        &larr; Back
                                    </button>
                                    <button
                                        type="button"
                                        @click="goToStep(4)"
                                        class="py-3 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-2"
                                    >
                                        <span>Next: Pricing &amp; Details &rarr;</span>
                                    </button>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- STEP 4: PRICING & FINALIZE                     -->
                            <!-- ============================================== -->
                            <div v-else-if="currentStep === 4" class="space-y-6">
                                <div class="border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-black text-slate-900">Step 4: Pricing &amp; Final Details</h3>
                                    <p class="text-xs text-slate-500">Set your expected price, maintenance, and listing description.</p>
                                </div>

                                <!-- Expected Price -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-slate-700">Expected Price (₹) <span class="text-rose-500">*</span></label>
                                        <span v-if="form.expected_price" class="text-xs font-black text-blue-700">
                                            {{ formatPriceWords(form.expected_price) }}
                                        </span>
                                    </div>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-slate-400 font-bold text-xs">₹</span>
                                        <input
                                            v-model="form.expected_price"
                                            type="number"
                                            placeholder="e.g. 8500000"
                                            required
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-8 pr-3 text-sm font-black text-slate-900 outline-none focus:border-blue-500 focus:bg-white tracking-wide"
                                        />
                                    </div>

                                    <!-- Price per sq.ft computation -->
                                    <div v-if="form.carpet_area && form.expected_price" class="mt-1 text-[11px] text-slate-500 flex items-center gap-2">
                                        <span>Approx: <strong>₹ {{ Math.round(form.expected_price / form.carpet_area).toLocaleString('en-IN') }} / sq.ft</strong></span>
                                        <span>&middot;</span>
                                        <span class="text-emerald-600 font-bold">0% Brokerage Charged</span>
                                    </div>
                                </div>

                                <!-- Maintenance & Price Negotiable -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Maintenance Charge (₹/month)</label>
                                        <input
                                            v-model="form.maintenance_charge"
                                            type="number"
                                            placeholder="e.g. 3500"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                    </div>

                                    <div class="flex items-center pt-5">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input
                                                v-model="form.price_negotiable"
                                                type="checkbox"
                                                class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer"
                                            />
                                            <span class="text-xs font-bold text-slate-700">Price is Negotiable</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Property Title -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-slate-700">Listing Title</label>
                                        <button
                                            type="button"
                                            @click="autoGenerateTitle"
                                            class="text-[11px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer"
                                        >
                                            🪄 Auto-Generate Title
                                        </button>
                                    </div>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        placeholder="e.g. 3 BHK Luxury Apartment in SG Highway with Garden View"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                    />
                                </div>

                                <!-- Description -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-slate-700">Description</label>
                                        <button
                                            type="button"
                                            @click="autoGenerateDescription"
                                            class="text-[11px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer"
                                        >
                                            🪄 Auto-Fill Description
                                        </button>
                                    </div>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Describe the property highlights, nearby landmarks, sun exposure, and neighborhood..."
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-medium text-slate-800 outline-none focus:border-blue-500 focus:bg-white resize-none"
                                    ></textarea>
                                </div>

                                <div class="pt-4 flex items-center justify-between">
                                    <button
                                        type="button"
                                        @click="goToStep(3)"
                                        class="py-2.5 px-5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition cursor-pointer"
                                    >
                                        &larr; Back
                                    </button>
                                    <button
                                        type="button"
                                        @click="submitProperty"
                                        :disabled="isSubmitting || !form.expected_price"
                                        class="py-3.5 px-8 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm shadow-lg shadow-blue-500/25 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <span v-if="isSubmitting" class="inline-block animate-spin">&#8635;</span>
                                        <span>{{ isSubmitting ? 'Posting Property...' : 'Post Property Now 🚀' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LIVE PREVIEW SIDEBAR (99acres Card Preview) -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-20 bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <span class="text-xs font-black uppercase tracking-wider text-slate-400">Live Preview</span>
                                <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">
                                    {{ form.property_for === 'Sell' ? 'For Sale' : 'For Rent' }}
                                </span>
                            </div>

                            <!-- Preview Card -->
                            <div class="rounded-2xl overflow-hidden border border-slate-100 bg-slate-50/50 shadow-2xs">
                                <div class="relative aspect-video bg-slate-200 overflow-hidden flex items-center justify-center">
                                    <img
                                        v-if="form.photos[0]"
                                        :src="form.photos[0]"
                                        class="absolute inset-0 w-full h-full object-cover"
                                        alt="Preview"
                                    />
                                    <div v-else class="text-slate-400 text-center select-none">
                                        <div class="text-4xl mb-1">🏠</div>
                                        <p class="text-[10px] font-semibold">Upload a photo to preview</p>
                                    </div>
                                    <div class="absolute bottom-2 left-2 bg-slate-900/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">
                                        {{ form.photos.length }} Photo{{ form.photos.length !== 1 ? 's' : '' }}
                                    </div>
                                    <div class="absolute top-2 right-2 bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-xs">
                                        Verified
                                    </div>
                                </div>

                                <div class="p-3.5 space-y-2">
                                    <div class="text-xs font-black text-slate-900 line-clamp-1">
                                        {{ form.title || `${form.bedrooms} BHK ${form.property_type}` }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 line-clamp-1">
                                        <span>📍</span>
                                        <span>{{ form.locality || 'Locality' }}, {{ form.city }}</span>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/60 flex items-baseline justify-between">
                                        <div>
                                            <span class="text-base font-black text-blue-700">
                                                {{ formatPriceWords(form.expected_price) }}
                                            </span>
                                            <span v-if="form.carpet_area && form.expected_price" class="text-[10px] text-slate-400 block">
                                                ₹ {{ Math.round(form.expected_price / form.carpet_area).toLocaleString('en-IN') }}/sq.ft
                                            </span>
                                        </div>
                                        <div class="text-right text-[11px] text-slate-600 font-bold">
                                            {{ form.carpet_area || 1000 }} sq.ft
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                        <span class="text-[10px] bg-white border border-slate-200 px-1.5 py-0.5 rounded font-semibold text-slate-600">
                                            {{ form.furnishing_status }}
                                        </span>
                                        <span class="text-[10px] bg-white border border-slate-200 px-1.5 py-0.5 rounded font-semibold text-slate-600">
                                            {{ form.construction_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Seller Info Box -->
                            <div class="bg-blue-50/60 rounded-2xl p-3 border border-blue-100 flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                                    {{ userInitials }}
                                </div>
                                <div class="text-left text-xs truncate">
                                    <span class="font-bold text-slate-800 block truncate">{{ currentUser?.name }}</span>
                                    <span class="text-[11px] text-slate-500 font-medium capitalize">{{ form.user_type }} &middot; {{ currentUser?.country_code }} {{ currentUser?.mobile }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthModal from '../components/AuthModal.vue';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const { companyName, brandPrimaryColor, brandAccentColor, heroGradient } = useCompanyBranding();

const router = useRouter();

const currentUser = ref(null);
const isAuthModalOpen = ref(false);
const currentStep = ref(1);
const isSubmitting = ref(false);
const isSubmitted = ref(false);
const errorMessage = ref('');
const createdProperty = ref(null);
const fileInputRef = ref(null);
const isDragging = ref(false);
const photoUploadError = ref('');
/** Raw File objects corresponding to each entry in form.photos (for upload) */
const photoFiles = ref([]);

const steps = [
    { id: 1, title: 'Basic & Location' },
    { id: 2, title: 'Property Profile' },
    { id: 3, title: 'Photos & Amenities' },
    { id: 4, title: 'Pricing & Details' },
];

const availableAmenities = [
    { name: 'Lift', icon: '🛗' },
    { name: '24x7 Security', icon: '👮' },
    { name: 'Reserved Parking', icon: '🚗' },
    { name: 'Gym', icon: '🏋️' },
    { name: 'Swimming Pool', icon: '🏊' },
    { name: 'Power Backup', icon: '⚡' },
    { name: 'Clubhouse', icon: '🏛️' },
    { name: 'Piped Gas', icon: '🔥' },
    { name: 'Children Play Area', icon: '🎠' },
    { name: 'Park / Garden', icon: '🌳' },
    { name: 'Intercom', icon: '📞' },
    { name: 'CCTV Surveillance', icon: '📹' },
];

const form = ref({
    user_type: 'Owner',
    property_for: 'Sell',
    property_type: 'Residential Apartment',
    city: 'Ahmedabad',
    locality: '',
    sub_locality: '',
    project_name: '',
    address: '',
    landmark: '',
    bedrooms: 2,
    bathrooms: 2,
    balconies: 1,
    carpet_area: 1100,
    super_builtup_area: null,
    furnishing_status: 'Semi-Furnished',
    floor_no: '4',
    total_floors: 14,
    facing: 'East',
    construction_status: 'Ready to Move',
    expected_price: 6500000,
    maintenance_charge: 2500,
    price_negotiable: true,
    title: '',
    description: '',
    amenities: ['Lift', '24x7 Security', 'Reserved Parking', 'Power Backup', 'Gym'],
    photos: [],
});

const userInitials = computed(() => {
    if (!currentUser.value?.name) return 'U';
    const parts = currentUser.value.name.trim().split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return currentUser.value.name.slice(0, 2).toUpperCase();
});

const checkAuth = () => {
    try {
        const stored = localStorage.getItem('realhem_user');
        currentUser.value = stored ? JSON.parse(stored) : null;
    } catch {
        currentUser.value = null;
    }
};

const handleUserAuthenticated = (user) => {
    currentUser.value = user;
    isAuthModalOpen.value = false;
};

const quickDemoLogin = () => {
    const demoUser = {
        id: 1,
        name: 'Hemant Sharma',
        email: 'hemant@example.com',
        country_code: '+91',
        mobile: '9876543210',
        role: 'owner',
    };
    localStorage.setItem('realhem_user', JSON.stringify(demoUser));
    localStorage.setItem('realhem_user_token', 'demo-quick-token');
    currentUser.value = demoUser;
};

const canGoToStep = (step) => {
    if (step === 1) return true;
    if (step === 2) return Boolean(form.value.city && form.value.locality);
    if (step === 3) return Boolean(form.value.carpet_area && form.value.carpet_area >= 50);
    if (step === 4) return true;
    return true;
};

const goToStep = (step) => {
    errorMessage.value = '';
    currentStep.value = step;
    window.scrollTo({ top: 120, behavior: 'smooth' });
};

const toggleAmenity = (name) => {
    const idx = form.value.amenities.indexOf(name);
    if (idx > -1) {
        form.value.amenities.splice(idx, 1);
    } else {
        form.value.amenities.push(name);
    }
};

const ALLOWED_TYPES = ['image/jpeg', 'image/webp'];
const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB
const MAX_PHOTOS = 10;

/** Open the hidden file input */
const triggerFileInput = () => {
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
        fileInputRef.value.click();
    }
};

/**
 * Read a File and resolve with a base64 data URL.
 * @param {File} file
 * @returns {Promise<string>}
 */
const readFileAsDataUrl = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = () => reject(new Error('Failed to read file.'));
        reader.readAsDataURL(file);
    });
};

/**
 * Validate and process an array of File objects.
 * Adds valid files as base64 data URLs into form.photos.
 * @param {File[]} files
 */
const processFiles = async (files) => {
    photoUploadError.value = '';
    const fileArr = Array.from(files);

    // How many slots remain?
    const remaining = MAX_PHOTOS - form.value.photos.length;
    if (remaining <= 0) {
        photoUploadError.value = 'You can only upload up to 10 photos.';
        return;
    }

    const toProcess = fileArr.slice(0, remaining);
    const errors = [];

    for (const file of toProcess) {
        // Re-check cap on every iteration — async loop could accumulate
        if (form.value.photos.length >= MAX_PHOTOS) break;

        if (!ALLOWED_TYPES.includes(file.type)) {
            errors.push(`"${file.name}" is not a JPG, JPEG, or WEBP file.`);
            continue;
        }
        if (file.size > MAX_SIZE_BYTES) {
            errors.push(`"${file.name}" exceeds the 5 MB limit.`);
            continue;
        }
        try {
            const dataUrl = await readFileAsDataUrl(file);
            form.value.photos.push(dataUrl);
            photoFiles.value.push(file); // keep raw File for actual upload
        } catch {
            errors.push(`Failed to read "${file.name}".`);
        }
    }

    if (errors.length > 0) {
        photoUploadError.value = errors.join(' ');
    }

    if (fileArr.length > remaining) {
        photoUploadError.value = (photoUploadError.value ? photoUploadError.value + ' ' : '') +
            `Only the first ${remaining} photo(s) were added (max 10 total).`;
    }
};

/** Handle native file input change */
const handleFileSelect = (event) => {
    processFiles(event.target.files);
};

/** Handle drag-and-drop */
const handleFileDrop = (event) => {
    isDragging.value = false;
    processFiles(event.dataTransfer.files);
};

const removePhoto = (idx) => {
    form.value.photos.splice(idx, 1);
    photoFiles.value.splice(idx, 1);
};

const formatPriceWords = (val) => {
    if (!val || val <= 0) return '₹ 0';
    const num = Number(val);
    if (num >= 10000000) {
        const cr = (num / 10000000).toFixed(2);
        return `₹ ${cr.replace(/\.00$/, '')} Crore`;
    }
    if (num >= 100000) {
        const lk = (num / 100000).toFixed(2);
        return `₹ ${lk.replace(/\.00$/, '')} Lakh`;
    }
    return `₹ ${num.toLocaleString('en-IN')}`;
};

const autoGenerateTitle = () => {
    const forText = form.value.property_for === 'Sell' ? 'Sale' : form.value.property_for;
    const soc = form.value.project_name ? ` in ${form.value.project_name}` : '';
    form.value.title = `${form.value.bedrooms} BHK ${form.value.property_type} for ${forText}${soc}, ${form.value.locality}, ${form.value.city}`;
};

const autoGenerateDescription = () => {
    const forText = form.value.property_for === 'Sell' ? 'sale' : 'rent';
    form.value.description = `Spacious and beautifully maintained ${form.value.bedrooms} BHK ${form.value.property_type} available for ${forText} in ${form.value.locality}, ${form.value.city}. Features ${form.value.furnishing_status.toLowerCase()} interiors, ${form.value.facing} facing entrance, good sunlight and cross-ventilation. Located in a secure community with modern amenities.`;
};

const submitProperty = async () => {
    errorMessage.value = '';

    // Check login
    const token = localStorage.getItem('realhem_user_token');
    if (!token) {
        isAuthModalOpen.value = true;
        errorMessage.value = 'Please log in to submit your property.';
        return;
    }

    isSubmitting.value = true;

    try {
        // Step 1: Upload photos to storage (if any were selected)
        let photoUrls = [];
        if (photoFiles.value.length > 0) {
            const formData = new FormData();
            photoFiles.value.forEach((file) => {
                formData.append('photos[]', file);
            });

            const uploadRes = await fetch('/api/properties/upload-photos', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: formData,
            });

            const uploadData = await uploadRes.json();
            if (!uploadRes.ok) {
                errorMessage.value = uploadData.message || 'Photo upload failed. Please try again.';
                return;
            }
            photoUrls = uploadData.urls ?? [];
        }

        // Step 2: Submit property with real storage URLs
        const payload = {
            user_type: form.value.user_type,
            property_for: form.value.property_for,
            property_type: form.value.property_type,
            city: form.value.city,
            locality: form.value.locality,
            sub_locality: form.value.sub_locality || undefined,
            project_name: form.value.project_name || undefined,
            address: form.value.address || undefined,
            landmark: form.value.landmark || undefined,
            bedrooms: Number(form.value.bedrooms),
            bathrooms: Number(form.value.bathrooms),
            balconies: Number(form.value.balconies),
            carpet_area: Number(form.value.carpet_area),
            super_builtup_area: form.value.super_builtup_area ? Number(form.value.super_builtup_area) : undefined,
            furnishing_status: form.value.furnishing_status,
            floor_no: form.value.floor_no || '1',
            total_floors: form.value.total_floors ? Number(form.value.total_floors) : 10,
            facing: form.value.facing || 'East',
            construction_status: form.value.construction_status,
            expected_price: Number(form.value.expected_price),
            maintenance_charge: form.value.maintenance_charge ? Number(form.value.maintenance_charge) : 0,
            price_negotiable: Boolean(form.value.price_negotiable),
            title: form.value.title || undefined,
            description: form.value.description || undefined,
            amenities: form.value.amenities,
            photos: photoUrls,
        };

        const response = await fetch('/api/properties', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 401) {
                isAuthModalOpen.value = true;
                errorMessage.value = 'Your session has expired. Please log in again.';
                return;
            }
            errorMessage.value = data.message || Object.values(data.errors || {})[0]?.[0] || 'Could not post property. Please check required fields.';
            return;
        }

        createdProperty.value = data.property;
        isSubmitted.value = true;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
        console.error(err);
        errorMessage.value = 'Network error while submitting property. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};

const resetFormForAnother = () => {
    isSubmitted.value = false;
    currentStep.value = 1;
    form.value.title = '';
    form.value.description = '';
    form.value.locality = '';
    form.value.expected_price = 7500000;
};

onMounted(() => {
    checkAuth();
    autoGenerateTitle();
});
</script>

