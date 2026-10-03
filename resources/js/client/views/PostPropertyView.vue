<template>
    <div class="min-h-screen bg-[#f4f5f7] pb-24 text-slate-800">
        <!-- Auth Modal -->
        <AuthModal
            :is-open="isAuthModalOpen"
            @close="isAuthModalOpen = false"
            @authenticated="handleUserAuthenticated"
        />

        <!-- Top Header Navigation Ribbon -->
        <div class="bg-[#005ca8] text-white py-3.5 px-4 sm:px-6 lg:px-8 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <router-link to="/" class="font-black text-xl tracking-tight text-white flex items-center gap-1">
                        <span>{{ companyName }}</span>
                        <span class="text-amber-400">.</span>
                    </router-link>
                    <span class="text-white/40">|</span>
                    <span class="text-xs font-bold text-white/90">Post Property for Sale or Rent</span>
                    <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white uppercase tracking-wider">
                        100% FREE
                    </span>
                </div>

                <!-- User Status Pill -->
                <div v-if="currentUser" class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-white">{{ currentUser.name }}</div>
                        <div class="text-[10px] text-blue-100">{{ currentUser.country_code || '+91' }} {{ currentUser.mobile }}</div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-amber-400 text-slate-900 font-black text-xs flex items-center justify-center shadow-xs">
                        {{ userInitials }}
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <!-- ========================================== -->
            <!-- GATEKEEPER: LOGIN REQUIRED (If not logged in) -->
            <!-- ========================================== -->
            <div v-if="!currentUser" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 text-center max-w-2xl mx-auto space-y-8 animate-fadeIn mt-10">
                <div class="w-16 h-16 rounded-3xl bg-blue-50 text-[#005ca8] flex items-center justify-center mx-auto shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold tracking-wider text-blue-600 uppercase">Verification Required</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Login to Post Your Property</h2>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                        To protect our marketplace from spam and ensure genuine buyer inquiries, please log in with your mobile number.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-left">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-2.5">
                        <span class="text-lg">🏷️</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Zero Brokerage</h4>
                            <p class="text-[11px] text-slate-500">Save 100% in broker commissions</p>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-2.5">
                        <span class="text-lg">⚡</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Instant Go-Live</h4>
                            <p class="text-[11px] text-slate-500">Listed across search in under 2 mins</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <button
                        type="button"
                        @click="isAuthModalOpen = true"
                        class="w-full py-3.5 px-6 rounded-2xl bg-[#005ca8] hover:bg-[#004e8f] text-white font-extrabold text-sm shadow-lg shadow-blue-500/25 transition cursor-pointer flex items-center justify-center gap-2"
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
            <div v-else-if="isSubmitted" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 text-center max-w-xl mx-auto space-y-6 animate-fadeIn mt-10">
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
                    <div class="flex justify-between">
                        <span>Configuration:</span>
                        <strong class="text-slate-800">
                            {{ isResidential ? `${form.bedrooms} BHK (${form.carpet_area} ${form.carpet_area_unit})` : `${form.carpet_area} ${form.carpet_area_unit} ${form.property_type}` }}
                        </strong>
                    </div>
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

            <!-- ========================================================================= -->
            <!-- 99ACRES 5-STEP LAYOUT: LEFT SIDEBAR (STEPS & SCORE) + MAIN FORM + HELP -->
            <!-- ========================================================================= -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- ============================================== -->
                <!-- LEFT SIDEBAR: STEP WIZARD & PROPERTY SCORE     -->
                <!-- ============================================== -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Steps Progress Card (Exact 99acres style) -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                        <div
                            v-for="(st, idx) in steps"
                            :key="st.id"
                            class="relative flex items-start gap-3 transition"
                        >
                            <!-- Vertical Connecting Line -->
                            <div
                                v-if="idx < steps.length - 1"
                                class="absolute left-3 top-7 bottom--3 w-0.5 -ml-[1px]"
                                :class="currentStep > idx + 1 ? 'bg-blue-600' : 'bg-slate-200'"
                            ></div>

                            <!-- Step Bullet Circle -->
                            <div
                                class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 z-10 transition-colors"
                                :class="[
                                    currentStep === idx + 1
                                        ? 'border-2 border-blue-600 text-blue-600 bg-white ring-4 ring-blue-50'
                                        : (currentStep > idx + 1
                                            ? 'bg-blue-600 text-white'
                                            : 'border-2 border-slate-300 text-slate-400 bg-white')
                                ]"
                            >
                                <span v-if="currentStep > idx + 1">✓</span>
                                <span v-else>{{ idx + 1 }}</span>
                            </div>

                            <!-- Step Label & Summary -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold leading-tight"
                                        :class="currentStep === idx + 1 ? 'text-slate-900 font-extrabold' : 'text-slate-700'"
                                    >
                                        {{ st.title }}
                                    </span>
                                    <button
                                        v-if="currentStep > idx + 1"
                                        type="button"
                                        @click="goToStep(idx + 1)"
                                        class="text-[11px] font-bold text-blue-600 hover:underline cursor-pointer"
                                    >
                                        Edit
                                    </button>
                                </div>
                                <p v-if="st.subtitle && currentStep > idx + 1" class="text-[11px] text-slate-500 truncate mt-0.5">
                                    {{ st.subtitle }}
                                </p>
                                <p v-else-if="currentStep === idx + 1" class="text-[10px] text-blue-600 font-semibold mt-0.5">
                                    Step {{ idx + 1 }}
                                </p>
                                <p v-else class="text-[10px] text-slate-400 mt-0.5">
                                    Step {{ idx + 1 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Property Score Ring (Exact from 99acres screenshot) -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
                        <div class="relative w-14 h-14 shrink-0 flex items-center justify-center">
                            <svg class="w-14 h-14 transform -rotate-90" viewBox="0 0 36 36">
                                <path
                                    class="text-slate-100"
                                    stroke-width="3.5"
                                    stroke="currentColor"
                                    fill="none"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <path
                                    class="text-emerald-500 transition-all duration-500 ease-out"
                                    :stroke-dasharray="`${propertyScore}, 100`"
                                    stroke-width="3.5"
                                    stroke-linecap="round"
                                    stroke="currentColor"
                                    fill="none"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                            </svg>
                            <span class="absolute text-xs font-black text-slate-800">{{ propertyScore }}%</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Property Score</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                Better your property score, greater your visibility
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- CENTER COLUMN: ACTIVE FORM STEP                -->
                <!-- ============================================== -->
                <div class="lg:col-span-6">
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
                        <!-- Top Back Button Link -->
                        <div v-if="currentStep > 1" class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <button
                                type="button"
                                @click="goToStep(currentStep - 1)"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 cursor-pointer transition"
                            >
                                <span>&larr;</span>
                                <span>Back</span>
                            </button>
                            <span class="text-[11px] font-bold text-slate-400">Step {{ currentStep }} of {{ steps.length }}</span>
                        </div>

                        <!-- Error Message Banner -->
                        <div v-if="errorMessage" class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>{{ errorMessage }}</span>
                        </div>

                        <!-- ============================================== -->
                        <!-- STEP 1: BASIC DETAILS                          -->
                        <!-- ============================================== -->
                        <div v-if="currentStep === 1" class="space-y-6 animate-fadeIn">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Basic Details</h2>
                                <p class="text-xs text-slate-500 mt-1">Specify listing intent, role, and property type.</p>
                            </div>

                            <!-- You Are (Owner, Agent, Builder) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">You are:</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button
                                        v-for="role in ['Owner', 'Agent', 'Builder']"
                                        :key="role"
                                        type="button"
                                        @click="form.user_type = role"
                                        class="py-2.5 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                        :class="form.user_type === role ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                    >
                                        {{ role }}
                                    </button>
                                </div>
                            </div>

                            <!-- Property For (Sell, Rent, PG) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">Property is for:</label>
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
                                        :class="form.property_for === purp.id ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                    >
                                        {{ purp.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">Property Type:</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <button
                                        v-for="pt in [
                                            'Residential Apartment',
                                            'Independent House/Villa',
                                            'Builder Floor',
                                            'Residential Plot',
                                            'Commercial Office',
                                            'Commercial Shop'
                                        ]"
                                        :key="pt"
                                        type="button"
                                        @click="handlePropertyTypeChange(pt)"
                                        class="py-2.5 px-3 rounded-xl text-xs font-semibold border transition cursor-pointer text-left flex items-center justify-between"
                                        :class="form.property_type === pt ? 'bg-blue-50 border-blue-600 text-blue-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                    >
                                        <span>{{ pt }}</span>
                                        <span v-if="form.property_type === pt" class="text-blue-600 font-bold">✓</span>
                                    </button>
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button
                                    type="button"
                                    @click="goToStep(2)"
                                    class="py-3 px-6 rounded-xl bg-[#005ca8] hover:bg-[#004e8f] text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-2"
                                >
                                    <span>Next: Location Details &rarr;</span>
                                </button>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- STEP 2: LOCATION DETAILS                       -->
                        <!-- ============================================== -->
                        <div v-else-if="currentStep === 2" class="space-y-6 animate-fadeIn">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Location Details</h2>
                                <p class="text-xs text-slate-500 mt-1">Where is your property located?</p>
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
                                <div class="relative">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Locality / Area <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <input
                                            v-model="form.locality"
                                            @input="onLocalityInput"
                                            @focus="onLocalityInput"
                                            type="text"
                                            placeholder="e.g. SG Highway, Vastral, Bopal, Whitefield"
                                            required
                                            autocomplete="off"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                        />
                                        <div v-if="isLocalityLoading" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <div class="w-3.5 h-3.5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                        </div>
                                    </div>

                                    <!-- Locality Suggestions Dropdown -->
                                    <div
                                        v-if="showLocalitySuggestions && localitySuggestions.length > 0"
                                        class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden max-h-60 overflow-y-auto"
                                    >
                                        <div class="px-3 py-1.5 border-b border-slate-100 flex items-center justify-between text-[11px] font-semibold text-slate-400 bg-slate-50/70">
                                            <span>Suggested Localities</span>
                                            <button type="button" @click="showLocalitySuggestions = false" class="text-slate-400 hover:text-slate-600 font-bold px-1">&times;</button>
                                        </div>
                                        <button
                                            v-for="(item, idx) in localitySuggestions"
                                            :key="idx"
                                            type="button"
                                            @click="selectLocalitySuggestion(item)"
                                            class="w-full text-left px-3 py-2 hover:bg-blue-50/60 flex items-center justify-between gap-2 border-b border-slate-50 last:border-0 transition cursor-pointer"
                                        >
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <span class="text-sm flex-shrink-0">📍</span>
                                                <div class="truncate">
                                                    <p class="text-xs font-bold text-slate-800 truncate">{{ item.locality || item.name }}</p>
                                                    <p class="text-[10px] text-slate-400 truncate">{{ item.display || item.city }}</p>
                                                </div>
                                            </div>
                                            <span v-if="item.pincode" class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono font-medium flex-shrink-0">{{ item.pincode }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Project / Society Name & Landmark -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Society / Project Name</label>
                                    <input
                                        v-model="form.project_name"
                                        type="text"
                                        placeholder="e.g. The Metropark, Trinay Anagh, Godrej Garden City"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Landmark / Sub-locality</label>
                                    <input
                                        v-model="form.landmark"
                                        type="text"
                                        placeholder="e.g. Near Metro Station, 132 Ft Ring Road"
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
                                    placeholder="e.g. Tower B, Flat 402, Ring Road"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                />
                            </div>

                            <div class="pt-4 flex justify-between items-center">
                                <button
                                    type="button"
                                    @click="goToStep(1)"
                                    class="text-xs font-bold text-slate-600 hover:text-slate-900 cursor-pointer"
                                >
                                    &larr; Back
                                </button>
                                <button
                                    type="button"
                                    @click="goToStep(3)"
                                    :disabled="!form.locality || !form.city"
                                    class="py-3 px-6 rounded-xl bg-[#005ca8] hover:bg-[#004e8f] text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <span>Next: Property Profile &rarr;</span>
                                </button>
                            </div>
                        </div>

                        <!-- ========================================================================= -->
                        <!-- STEP 3: PROPERTY PROFILE ("Tell us about your property")                  -->
                        <!-- ========================================================================= -->
                        <div v-else-if="currentStep === 3" class="space-y-6 animate-fadeIn">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tell us about your property</h2>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ isResidential ? 'Specify bedrooms, area, furnishing, and pricing.' : (isPlot ? 'Specify land area, plot dimensions, open sides, and price.' : 'Specify office/shop layout, seating, area, and commercial terms.') }}
                                </p>
                            </div>

                            <!-- 1. BHK Selection: Residential Only -->
                            <div v-if="isResidential">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Your apartment / home is a</label>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button
                                        type="button"
                                        @click="setQuickBhk(form.bedrooms || 2)"
                                        class="px-4 py-2 rounded-full text-xs font-bold border transition cursor-pointer"
                                        :class="!isOtherBhkSelected ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                    >
                                        {{ form.bedrooms }} BHK
                                    </button>
                                    <button
                                        type="button"
                                        @click="isOtherBhkSelected = !isOtherBhkSelected"
                                        class="px-4 py-2 rounded-full text-xs font-bold border transition cursor-pointer"
                                        :class="isOtherBhkSelected ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                    >
                                        Other
                                    </button>
                                </div>

                                <!-- Other BHK Dropdown Selection (if Other is toggled) -->
                                <div v-if="isOtherBhkSelected" class="mt-2.5 p-3 rounded-2xl bg-slate-50 border border-slate-200 flex flex-wrap gap-1.5 animate-fadeIn">
                                    <button
                                        v-for="bhkOption in [1, 2, 3, 4, 5, 6]"
                                        :key="bhkOption"
                                        type="button"
                                        @click="setQuickBhk(bhkOption)"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold border transition cursor-pointer"
                                        :class="form.bedrooms === bhkOption ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'"
                                    >
                                        {{ bhkOption }} BHK
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Area Details (Carpet Area / Plot Area) -->
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <label class="text-xs font-bold text-slate-800">
                                        {{ isPlot ? 'Add Plot / Land Area' : 'Add Area Details' }}
                                    </label>
                                    <span class="text-slate-400 cursor-help" title="At least one area is required to calculate price per sq.ft.">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mb-2">At least one area type is mandatory</p>

                                <!-- Primary Area Input with Unit Dropdown -->
                                <div class="flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition bg-white">
                                    <input
                                        v-model="form.carpet_area"
                                        type="number"
                                        :placeholder="isPlot ? 'Plot Area' : (isCommercial ? 'Super / Carpet Area' : 'Carpet Area')"
                                        @input="recalculatePerSqFt"
                                        class="flex-1 py-2.5 px-3.5 text-xs font-bold text-slate-800 placeholder-slate-400 outline-none bg-transparent"
                                        required
                                    />
                                    <div class="h-6 w-px bg-slate-200"></div>
                                    <!-- Area Unit Selector Dropdown -->
                                    <div class="relative">
                                        <select
                                            v-model="form.carpet_area_unit"
                                            class="bg-transparent border-none text-xs font-bold text-slate-700 py-2.5 pl-3 pr-8 outline-none cursor-pointer appearance-none"
                                        >
                                            <option value="sq.ft.">sq.ft.</option>
                                            <option value="sq.yards">sq.yards</option>
                                            <option value="sq.m.">sq.m.</option>
                                            <option value="acres">acres</option>
                                            <option value="marla">marla</option>
                                            <option value="cents">cents</option>
                                            <option value="bigha">bigha</option>
                                            <option value="guntha">guntha</option>
                                        </select>
                                        <svg class="w-3 h-3 text-slate-500 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>

                                <!-- Optional Area Toggles: + Built-up Area, + Super Built-up Area (for non-plots) -->
                                <div v-if="!isPlot" class="flex items-center gap-3 mt-2 text-xs">
                                    <button
                                        v-if="!showBuiltupArea"
                                        type="button"
                                        @click="showBuiltupArea = true"
                                        class="text-blue-600 font-bold hover:underline cursor-pointer"
                                    >
                                        + Built-up Area
                                    </button>
                                    <button
                                        v-if="!showSuperBuiltupArea"
                                        type="button"
                                        @click="showSuperBuiltupArea = true"
                                        class="text-blue-600 font-bold hover:underline cursor-pointer"
                                    >
                                        + Super Built-up Area
                                    </button>
                                </div>

                                <!-- Built-up Area Input (if opened) -->
                                <div v-if="showBuiltupArea && !isPlot" class="mt-2.5 flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:border-blue-500 bg-white animate-fadeIn">
                                    <input
                                        v-model="form.builtup_area"
                                        type="number"
                                        placeholder="Built-up Area"
                                        class="flex-1 py-2.5 px-3.5 text-xs font-bold text-slate-800 placeholder-slate-400 outline-none bg-transparent"
                                    />
                                    <div class="h-6 w-px bg-slate-200"></div>
                                    <select
                                        v-model="form.builtup_area_unit"
                                        class="bg-transparent border-none text-xs font-bold text-slate-700 py-2.5 pl-3 pr-8 outline-none cursor-pointer appearance-none"
                                    >
                                        <option value="sq.ft.">sq.ft.</option>
                                        <option value="sq.yards">sq.yards</option>
                                        <option value="sq.m.">sq.m.</option>
                                    </select>
                                    <button type="button" @click="showBuiltupArea = false; form.builtup_area = null" class="px-2 text-slate-400 hover:text-red-500 text-xs font-bold">✕</button>
                                </div>

                                <!-- Super Built-up Area Input (if opened) -->
                                <div v-if="showSuperBuiltupArea && !isPlot" class="mt-2.5 flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:border-blue-500 bg-white animate-fadeIn">
                                    <input
                                        v-model="form.super_builtup_area"
                                        type="number"
                                        placeholder="Super Built-up Area"
                                        class="flex-1 py-2.5 px-3.5 text-xs font-bold text-slate-800 placeholder-slate-400 outline-none bg-transparent"
                                    />
                                    <div class="h-6 w-px bg-slate-200"></div>
                                    <select
                                        v-model="form.super_builtup_area_unit"
                                        class="bg-transparent border-none text-xs font-bold text-slate-700 py-2.5 pl-3 pr-8 outline-none cursor-pointer appearance-none"
                                    >
                                        <option value="sq.ft.">sq.ft.</option>
                                        <option value="sq.yards">sq.yards</option>
                                        <option value="sq.m.">sq.m.</option>
                                    </select>
                                    <button type="button" @click="showSuperBuiltupArea = false; form.super_builtup_area = null" class="px-2 text-slate-400 hover:text-red-500 text-xs font-bold">✕</button>
                                </div>
                            </div>

                            <!-- 3A. Residential Room Details (Bedrooms, Bathrooms, Balconies) -->
                            <div v-if="isResidential" class="space-y-4 pt-1">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Add Room Details</h3>

                                <!-- No. of Bedrooms -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5 max-w-sm">
                                        <label class="block text-xs font-semibold text-slate-700">No. of Bedrooms</label>
                                        <button
                                            v-if="form.bedrooms >= 5 && !isEditingCustomBedrooms"
                                            type="button"
                                            @click="startCustomBedrooms"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit ({{ form.bedrooms }})</span>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button
                                            v-for="num in [1, 2, 3, 4]"
                                            :key="num"
                                            type="button"
                                            @click="form.bedrooms = num"
                                            class="w-10 h-10 rounded-full border text-xs font-bold flex items-center justify-center transition cursor-pointer"
                                            :class="form.bedrooms === num ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                        >
                                            {{ num }}
                                        </button>
                                        <!-- Inline Editable 5+ Pill -->
                                        <div
                                            v-if="isEditingCustomBedrooms"
                                            class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-50 flex items-center justify-center shadow-xs ring-2 ring-blue-100"
                                        >
                                            <input
                                                ref="customBedroomsInputRef"
                                                v-model.number="customBedroomsInputVal"
                                                type="number"
                                                min="1"
                                                max="99"
                                                @blur="finishCustomBedrooms"
                                                @keydown.enter.prevent="finishCustomBedrooms"
                                                @keydown.esc="isEditingCustomBedrooms = false"
                                                class="w-full text-center text-xs font-black text-blue-800 outline-none bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none p-0"
                                            />
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            @click="startCustomBedrooms"
                                            class="relative w-10 h-10 rounded-full border text-xs font-bold flex items-center justify-center transition cursor-pointer"
                                            :class="form.bedrooms >= 5 ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                            :title="form.bedrooms >= 5 ? `Selected: ${form.bedrooms} (Click to edit)` : 'Add 5 or more bedrooms'"
                                        >
                                            <span>{{ form.bedrooms >= 5 ? form.bedrooms : '5+' }}</span>
                                            <span
                                                v-if="form.bedrooms >= 5"
                                                class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-blue-600 text-white rounded-full flex items-center justify-center text-[8px] shadow-xs"
                                                title="Edit"
                                            >
                                                ✎
                                            </span>
                                        </button>
                                        <!-- Done button while inline editing -->
                                        <button
                                            v-if="isEditingCustomBedrooms"
                                            type="button"
                                            @click="finishCustomBedrooms"
                                            class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700 cursor-pointer shadow-xs transition"
                                        >
                                            Done
                                        </button>
                                        <!-- Edit button next to round pill -->
                                        <button
                                            v-else-if="form.bedrooms >= 5"
                                            type="button"
                                            @click="startCustomBedrooms"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer ml-1 inline-flex items-center gap-0.5"
                                        >
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- No. of Bathrooms -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5 max-w-sm">
                                        <label class="block text-xs font-semibold text-slate-700">No. of Bathrooms</label>
                                        <button
                                            v-if="form.bathrooms >= 5 && !isEditingCustomBathrooms"
                                            type="button"
                                            @click="startCustomBathrooms"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit ({{ form.bathrooms }})</span>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button
                                            v-for="num in [1, 2, 3, 4]"
                                            :key="num"
                                            type="button"
                                            @click="form.bathrooms = num"
                                            class="w-10 h-10 rounded-full border text-xs font-bold flex items-center justify-center transition cursor-pointer"
                                            :class="form.bathrooms === num ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                        >
                                            {{ num }}
                                        </button>
                                        <!-- Inline Editable 5+ Pill -->
                                        <div
                                            v-if="isEditingCustomBathrooms"
                                            class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-50 flex items-center justify-center shadow-xs ring-2 ring-blue-100"
                                        >
                                            <input
                                                ref="customBathroomsInputRef"
                                                v-model.number="customBathroomsInputVal"
                                                type="number"
                                                min="1"
                                                max="99"
                                                @blur="finishCustomBathrooms"
                                                @keydown.enter.prevent="finishCustomBathrooms"
                                                @keydown.esc="isEditingCustomBathrooms = false"
                                                class="w-full text-center text-xs font-black text-blue-800 outline-none bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none p-0"
                                            />
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            @click="startCustomBathrooms"
                                            class="relative w-10 h-10 rounded-full border text-xs font-bold flex items-center justify-center transition cursor-pointer"
                                            :class="form.bathrooms >= 5 ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                            :title="form.bathrooms >= 5 ? `Selected: ${form.bathrooms} (Click to edit)` : 'Add 5 or more bathrooms'"
                                        >
                                            <span>{{ form.bathrooms >= 5 ? form.bathrooms : '5+' }}</span>
                                            <span
                                                v-if="form.bathrooms >= 5"
                                                class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-blue-600 text-white rounded-full flex items-center justify-center text-[8px] shadow-xs"
                                                title="Edit"
                                            >
                                                ✎
                                            </span>
                                        </button>
                                        <!-- Done button while inline editing -->
                                        <button
                                            v-if="isEditingCustomBathrooms"
                                            type="button"
                                            @click="finishCustomBathrooms"
                                            class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700 cursor-pointer shadow-xs transition"
                                        >
                                            Done
                                        </button>
                                        <!-- Edit button next to round pill -->
                                        <button
                                            v-else-if="form.bathrooms >= 5"
                                            type="button"
                                            @click="startCustomBathrooms"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer ml-1 inline-flex items-center gap-0.5"
                                        >
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Balconies -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5 max-w-sm">
                                        <label class="block text-xs font-semibold text-slate-700">Balconies</label>
                                        <button
                                            v-if="form.balconies >= 4 && !isEditingCustomBalconies"
                                            type="button"
                                            @click="startCustomBalconies"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit ({{ form.balconies }})</span>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button
                                            v-for="num in [0, 1, 2, 3]"
                                            :key="num"
                                            type="button"
                                            @click="form.balconies = num"
                                            class="w-10 h-10 rounded-full border text-xs font-bold flex items-center justify-center transition cursor-pointer"
                                            :class="form.balconies === num ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                        >
                                            {{ num }}
                                        </button>
                                        <!-- Inline Editable 4+ Pill -->
                                        <div
                                            v-if="isEditingCustomBalconies"
                                            class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-50 flex items-center justify-center shadow-xs ring-2 ring-blue-100"
                                        >
                                            <input
                                                ref="customBalconiesInputRef"
                                                v-model.number="customBalconiesInputVal"
                                                type="number"
                                                min="0"
                                                max="99"
                                                @blur="finishCustomBalconies"
                                                @keydown.enter.prevent="finishCustomBalconies"
                                                @keydown.esc="isEditingCustomBalconies = false"
                                                class="w-full text-center text-xs font-black text-blue-800 outline-none bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none p-0"
                                            />
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            @click="startCustomBalconies"
                                            class="relative w-10 h-10 rounded-full border text-xs font-bold flex items-center justify-center transition cursor-pointer"
                                            :class="form.balconies >= 4 ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                            :title="form.balconies >= 4 ? `Selected: ${form.balconies} (Click to edit)` : 'Add 4 or more balconies'"
                                        >
                                            <span>{{ form.balconies >= 4 ? form.balconies : '4+' }}</span>
                                            <span
                                                v-if="form.balconies >= 4"
                                                class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-blue-600 text-white rounded-full flex items-center justify-center text-[8px] shadow-xs"
                                                title="Edit"
                                            >
                                                ✎
                                            </span>
                                        </button>
                                        <!-- Done button while inline editing -->
                                        <button
                                            v-if="isEditingCustomBalconies"
                                            type="button"
                                            @click="finishCustomBalconies"
                                            class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700 cursor-pointer shadow-xs transition"
                                        >
                                            Done
                                        </button>
                                        <!-- Edit button next to round pill -->
                                        <button
                                            v-else-if="form.balconies >= 4"
                                            type="button"
                                            @click="startCustomBalconies"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer ml-1 inline-flex items-center gap-0.5"
                                        >
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3B. Commercial Office Setup Details -->
                            <div v-else-if="isCommercialOffice" class="space-y-4 pt-1">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Office Setup &amp; Capacity</h3>

                                <!-- Cabins / Meeting Rooms -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cabins / Executive Rooms</label>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button
                                            v-for="num in [0, 1, 2, 3, 4, 5]"
                                            :key="num"
                                            type="button"
                                            @click="form.cabins = num"
                                            class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition cursor-pointer"
                                            :class="form.cabins === num ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                        >
                                            {{ num === 0 ? 'None' : (num === 5 ? '5+' : num) }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Workstations / Seats -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="border border-slate-300 rounded-xl p-2 bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Workstations / Seats</label>
                                        <input
                                            v-model="form.workstations"
                                            type="number"
                                            placeholder="e.g. 25"
                                            class="w-full text-xs font-bold text-slate-900 outline-none bg-transparent pt-0.5"
                                        />
                                    </div>
                                    <div class="border border-slate-300 rounded-xl p-2 bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Conference Rooms</label>
                                        <input
                                            v-model="form.conference_rooms"
                                            type="number"
                                            placeholder="e.g. 1"
                                            class="w-full text-xs font-bold text-slate-900 outline-none bg-transparent pt-0.5"
                                        />
                                    </div>
                                </div>

                                <!-- Washrooms & Pantry -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Washroom</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <button
                                                v-for="w in ['Private', 'Shared', 'None']"
                                                :key="w"
                                                type="button"
                                                @click="form.washrooms_type = w; form.bathrooms = w === 'None' ? 0 : (w === 'Private' ? 1 : 1)"
                                                class="py-2 px-2 text-center rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="form.washrooms_type === w ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                            >
                                                {{ w }}
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pantry</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <button
                                                v-for="p in ['Wet Pantry', 'Dry Pantry', 'None']"
                                                :key="p"
                                                type="button"
                                                @click="form.pantry = p"
                                                class="py-2 px-1 text-center rounded-xl text-[11px] font-bold border transition cursor-pointer"
                                                :class="form.pantry === p ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                            >
                                                {{ p }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3C. Commercial Shop Setup Details -->
                            <div v-else-if="isCommercialShop" class="space-y-4 pt-1">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Shop &amp; Retail Specifications</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Washroom</label>
                                        <div class="flex gap-2">
                                            <button
                                                v-for="w in ['Private', 'Shared', 'None']"
                                                :key="w"
                                                type="button"
                                                @click="form.washrooms_type = w; form.bathrooms = w === 'None' ? 0 : 1"
                                                class="flex-1 py-2 text-center rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="form.washrooms_type === w ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                            >
                                                {{ w }}
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Main Road Facing</label>
                                        <div class="flex gap-2">
                                            <button
                                                v-for="opt in [{ id: true, label: 'Yes' }, { id: false, label: 'No' }]"
                                                :key="opt.label"
                                                type="button"
                                                @click="form.main_road_facing = opt.id"
                                                class="flex-1 py-2 text-center rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="form.main_road_facing === opt.id ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                            >
                                                {{ opt.label }}
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Corner Shop</label>
                                        <div class="flex gap-2">
                                            <button
                                                v-for="opt in [{ id: true, label: 'Yes' }, { id: false, label: 'No' }]"
                                                :key="opt.label"
                                                type="button"
                                                @click="form.corner_property = opt.id"
                                                class="flex-1 py-2 text-center rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="form.corner_property === opt.id ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                            >
                                                {{ opt.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3D. Plot / Land Specifications -->
                            <div v-else-if="isPlot" class="space-y-4 pt-1">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Plot &amp; Land Details</h3>

                                <!-- Open Sides -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. of Open Sides</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <button
                                            v-for="side in [1, 2, 3, 4]"
                                            :key="side"
                                            type="button"
                                            @click="form.open_sides = side"
                                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                            :class="form.open_sides === side ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                        >
                                            {{ side }} Side{{ side > 1 ? 's' : '' }} Open
                                        </button>
                                    </div>
                                </div>

                                <!-- Boundary Wall & Gated Colony -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Boundary Wall Made?</label>
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                @click="form.boundary_wall = true"
                                                class="flex-1 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="form.boundary_wall ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700'"
                                            >
                                                Yes (Constructed)
                                            </button>
                                            <button
                                                type="button"
                                                @click="form.boundary_wall = false"
                                                class="flex-1 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="!form.boundary_wall ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700'"
                                            >
                                                No
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Gated Community / Society?</label>
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                @click="form.gated_community = true"
                                                class="flex-1 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="form.gated_community ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700'"
                                            >
                                                Yes
                                            </button>
                                            <button
                                                type="button"
                                                @click="form.gated_community = false"
                                                class="flex-1 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="!form.gated_community ? 'bg-blue-50 border-blue-600 text-blue-700' : 'bg-white border-slate-200 text-slate-700'"
                                            >
                                                No
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Furnishing Status (For Residential & Commercial only) -->
                            <div v-if="!isPlot" class="space-y-2 pt-1">
                                <label class="block text-xs font-bold text-slate-800">
                                    {{ isCommercial ? 'Commercial Furnishing' : 'Furnishing Status' }}
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="furn in (isCommercial
                                            ? [{ id: 'Furnished', label: 'Fully Furnished (Plug & Play)' }, { id: 'Semi-Furnished', label: 'Semi-Furnished' }, { id: 'Unfurnished', label: 'Bare Shell (Unfurnished)' }]
                                            : [{ id: 'Furnished', label: 'Furnished' }, { id: 'Semi-Furnished', label: 'Semi-Furnished' }, { id: 'Unfurnished', label: 'Unfurnished' }])"
                                        :key="furn.id"
                                        type="button"
                                        @click="form.furnishing_status = furn.id"
                                        class="py-2.5 px-3 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                        :class="form.furnishing_status === furn.id ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                    >
                                        {{ furn.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- 5. Floor Details / Floors Allowed -->
                            <div v-if="!isPlot" class="space-y-1.5 pt-1">
                                <label class="block text-xs font-bold text-slate-800">Floor Details</label>
                                <p class="text-[11px] text-slate-400">Total no of floors and your floor details</p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <!-- Total Floors Input Box -->
                                    <div class="relative border border-slate-300 rounded-xl p-2 bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Total floors</label>
                                        <input
                                            v-model="form.total_floors"
                                            type="number"
                                            min="1"
                                            placeholder="e.g. 4"
                                            class="w-full text-xs font-bold text-slate-900 outline-none bg-transparent pt-0.5"
                                        />
                                    </div>

                                    <!-- Property On Floor Dropdown -->
                                    <div class="relative border border-slate-300 rounded-xl p-2 bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Property on floor</label>
                                        <select
                                            v-model="form.floor_no"
                                            class="w-full text-xs font-bold text-slate-900 outline-none bg-transparent cursor-pointer appearance-none pt-0.5"
                                        >
                                            <option value="">Select floor</option>
                                            <option value="Basement">Basement</option>
                                            <option value="Lower Ground">Lower Ground</option>
                                            <option value="Ground">Ground</option>
                                            <option
                                                v-for="fl in (form.total_floors ? Number(form.total_floors) : 10)"
                                                :key="fl"
                                                :value="String(fl)"
                                            >
                                                {{ fl }}{{ fl === 1 ? 'st' : fl === 2 ? 'nd' : fl === 3 ? 'rd' : 'th' }} floor
                                            </option>
                                            <option value="Entire building">Entire building</option>
                                            <option value="Top floor">Top floor</option>
                                        </select>
                                        <svg class="w-3.5 h-3.5 text-slate-500 absolute right-3 bottom-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="space-y-1.5 pt-1">
                                <label class="block text-xs font-bold text-slate-800">Floors Allowed For Construction</label>
                                <div class="grid grid-cols-4 gap-2 pt-1">
                                    <button
                                        v-for="fl in [2, 3, 4, 5]"
                                        :key="fl"
                                        type="button"
                                        @click="form.total_floors = fl"
                                        class="py-2 px-2 text-center rounded-xl text-xs font-bold border transition cursor-pointer"
                                        :class="form.total_floors === fl ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                                    >
                                        {{ fl === 5 ? '5+ Floors' : `${fl} Floors` }}
                                    </button>
                                </div>
                            </div>

                            <!-- 6. Availability Status -->
                            <div class="space-y-2 pt-1">
                                <label class="block text-xs font-bold text-slate-800">
                                    {{ isPlot ? 'Possession / Availability' : 'Availability Status' }}
                                </label>
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        @click="form.construction_status = isPlot ? 'Immediate Possession' : 'Ready to Move'"
                                        class="px-5 py-2 rounded-full text-xs font-bold border transition cursor-pointer"
                                        :class="(form.construction_status === 'Ready to Move' || form.construction_status === 'Immediate Possession') ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                    >
                                        {{ isPlot ? 'Immediate Possession' : 'Ready to move' }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.construction_status = isPlot ? 'Under Development' : 'Under Construction'"
                                        class="px-5 py-2 rounded-full text-xs font-bold border transition cursor-pointer"
                                        :class="(form.construction_status === 'Under Construction' || form.construction_status === 'Under Development') ? 'bg-blue-50 border-blue-600 text-blue-700 font-extrabold shadow-2xs' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                                    >
                                        {{ isPlot ? 'Under Development' : 'Under construction' }}
                                    </button>
                                </div>
                            </div>

                            <!-- 7. Price Details -->
                            <div class="space-y-3 pt-2 border-t border-slate-100">
                                <label class="block text-xs font-bold text-slate-800">Price Details</label>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Expected Price Input -->
                                    <div class="relative border border-slate-300 rounded-xl p-2 bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">₹ Expected Price <span class="text-rose-500">*</span></label>
                                        <input
                                            v-model="form.expected_price"
                                            type="number"
                                            placeholder="e.g. 7740000"
                                            @input="recalculatePerSqFt"
                                            required
                                            class="w-full text-xs font-bold text-slate-900 outline-none bg-transparent pt-0.5"
                                        />
                                    </div>

                                    <!-- Price per sq.ft Input -->
                                    <div class="relative border border-slate-300 rounded-xl p-2 bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">₹ Price per sq.ft.</label>
                                        <input
                                            v-model="form.price_per_sqft"
                                            type="number"
                                            placeholder="e.g. 4000"
                                            class="w-full text-xs font-bold text-slate-900 outline-none bg-transparent pt-0.5"
                                        />
                                    </div>
                                </div>

                                <!-- Price in Words display (Exact 99acres style) -->
                                <p v-if="form.expected_price" class="text-xs font-black text-[#005ca8]">
                                    ₹ Price in words: <span class="text-slate-800 font-extrabold">{{ formatPriceWords(form.expected_price) }}</span>
                                </p>

                                <!-- Pricing Checkboxes -->
                                <div class="space-y-2 pt-1 text-xs text-slate-700">
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" v-model="form.all_inclusive_price" class="rounded text-blue-600 focus:ring-blue-500">
                                        <span class="font-medium">All inclusive price</span>
                                        <span class="text-slate-400 cursor-help" title="Price includes parking, club membership, etc.">ⓘ</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" v-model="form.tax_excluded" class="rounded text-blue-600 focus:ring-blue-500">
                                        <span class="font-medium">Tax and Govt. charges excluded</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" v-model="form.price_negotiable" class="rounded text-blue-600 focus:ring-blue-500">
                                        <span class="font-medium">Price Negotiable</span>
                                    </label>
                                </div>

                                <!-- Add more pricing details link -->
                                <div>
                                    <button
                                        type="button"
                                        @click="showMorePricing = !showMorePricing"
                                        class="text-xs font-bold text-[#005ca8] hover:underline cursor-pointer"
                                    >
                                        {{ showMorePricing ? '− Hide additional pricing' : '+ Add more pricing details' }}
                                    </button>

                                    <!-- Expandable Maintenance & Booking fields -->
                                    <div v-if="showMorePricing" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 animate-fadeIn">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Maintenance Charge (₹/month)</label>
                                            <input
                                                v-model="form.maintenance_charge"
                                                type="number"
                                                placeholder="e.g. 2500"
                                                class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Booking / Token Amount (₹)</label>
                                            <input
                                                v-model="form.booking_amount"
                                                type="number"
                                                placeholder="e.g. 100000"
                                                class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Post & Continue Button (Exact 99acres action) -->
                            <div class="pt-4">
                                <button
                                    type="button"
                                    @click="goToStep(4)"
                                    :disabled="!form.carpet_area || !form.expected_price"
                                    class="w-full sm:w-auto py-3.5 px-8 rounded-xl bg-[#005ca8] hover:bg-[#004e8f] text-white font-black text-xs shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Post &amp; continue
                                </button>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- STEP 4: PHOTOS, VIDEOS & VOICE-OVER            -->
                        <!-- ============================================== -->
                        <div v-else-if="currentStep === 4" class="space-y-6 animate-fadeIn">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Photos, Videos &amp; Voice-over</h2>
                                <p class="text-xs text-slate-500 mt-1">Upload high-resolution images to get 5x more buyer leads.</p>
                            </div>

                            <!-- Photo Uploader -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Property Photos</label>
                                    <span class="text-[11px] font-semibold" :class="form.photos.length >= 10 ? 'text-emerald-600' : 'text-slate-400'">{{ form.photos.length }}/10 uploaded</span>
                                </div>

                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept=".jpg,.jpeg,.webp,image/jpeg,image/webp"
                                    multiple
                                    class="hidden"
                                    @change="handleFileSelect"
                                />

                                <!-- Drop Zone -->
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
                                        <p class="text-xs text-slate-400">JPG, JPEG, WEBP &bull; Max 10 photos &bull; Up to 5 MB each</p>
                                    </div>
                                </div>

                                <p v-if="photoUploadError" class="text-xs text-rose-600 font-semibold mt-2">{{ photoUploadError }}</p>

                                <!-- Thumbnails grid -->
                                <div v-if="form.photos.length > 0" class="grid grid-cols-3 gap-3">
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
                            </div>

                            <div class="pt-4 flex justify-between items-center">
                                <button
                                    type="button"
                                    @click="goToStep(3)"
                                    class="text-xs font-bold text-slate-600 hover:text-slate-900 cursor-pointer"
                                >
                                    &larr; Back
                                </button>
                                <button
                                    type="button"
                                    @click="goToStep(5)"
                                    class="py-3 px-6 rounded-xl bg-[#005ca8] hover:bg-[#004e8f] text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-2"
                                >
                                    <span>Next: Amenities section &rarr;</span>
                                </button>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- STEP 5: AMENITIES & FINALIZE                   -->
                        <!-- ============================================== -->
                        <div v-else-if="currentStep === 5" class="space-y-6 animate-fadeIn">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Amenities &amp; Description</h2>
                                <p class="text-xs text-slate-500 mt-1">Select community amenities and description to finalize listing.</p>
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
                                        :class="form.amenities.includes(amenity.name) ? 'bg-blue-50 border-blue-500 text-blue-800 font-bold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                    >
                                        <span class="text-base">{{ amenity.icon }}</span>
                                        <span class="truncate">{{ amenity.name }}</span>
                                        <span v-if="form.amenities.includes(amenity.name)" class="ml-auto text-blue-600">✓</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Listing Title -->
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
                                    placeholder="e.g. 3 BHK Luxury Apartment in Vastral with Garden View"
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
                                    @click="goToStep(4)"
                                    class="text-xs font-bold text-slate-600 hover:text-slate-900 cursor-pointer"
                                >
                                    &larr; Back
                                </button>
                                <button
                                    type="button"
                                    @click="submitProperty"
                                    :disabled="isSubmitting || !form.expected_price"
                                    class="py-3.5 px-8 rounded-xl bg-gradient-to-r from-blue-600 to-[#005ca8] hover:from-blue-700 hover:to-[#004e8f] text-white font-extrabold text-sm shadow-lg shadow-blue-500/25 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <span v-if="isSubmitting" class="inline-block animate-spin">&#8635;</span>
                                    <span>{{ isSubmitting ? 'Posting Property...' : 'Post Property Now 🚀' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT COLUMN: NEED HELP CARD                   -->
                <!-- ============================================== -->
                <div class="lg:col-span-3 space-y-4">
                    <!-- Need Help Box (Exact 99acres layout from screenshot) -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-2">
                        <div class="flex items-center gap-2 text-slate-800 font-bold text-xs">
                            <span class="text-blue-600">📞</span>
                            <span>Need help?</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            You can email us at <a href="mailto:services@99acres.com" class="text-blue-600 font-semibold hover:underline">services@99acres.com</a> or call us at <strong class="text-slate-700">1800 41 99099</strong> (IND Toll-Free).
                        </p>
                    </div>

                    <!-- Compact Live Summary Mini-Card -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs space-y-2 text-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Listing Summary</span>
                        <div class="font-black text-slate-800 line-clamp-1">
                            {{ isResidential ? `${form.bedrooms} BHK ${form.property_type}` : `${form.carpet_area || 0} ${form.carpet_area_unit} ${form.property_type}` }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            📍 {{ form.locality || 'Locality' }}, {{ form.city }}
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between font-bold">
                            <span class="text-[#005ca8]">{{ formatPriceWords(form.expected_price) }}</span>
                            <span class="text-slate-600">{{ form.carpet_area || 0 }} {{ form.carpet_area_unit }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import AuthModal from '../components/AuthModal.vue';
import { useCompanyBranding } from '../composables/useCompanyBranding';
import {
    PROPERTY_FOR,
    PROPERTY_TYPES,
    isResidentialType,
    isCommercialType,
    isPlotType,
    getPropertyIntentLabel,
} from '../constants/propertyConstants';

const { companyName } = useCompanyBranding();
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
const photoFiles = ref([]);

// Toggles for extra areas and custom inputs
const isOtherBhkSelected = ref(false);
const showBuiltupArea = ref(false);
const showSuperBuiltupArea = ref(false);
const showMorePricing = ref(false);

// Inline round custom inputs for Rooms
const isEditingCustomBedrooms = ref(false);
const customBedroomsInputVal = ref(5);
const customBedroomsInputRef = ref(null);

const startCustomBedrooms = async () => {
    customBedroomsInputVal.value = form.value.bedrooms >= 5 ? form.value.bedrooms : 5;
    isEditingCustomBedrooms.value = true;
    await nextTick();
    if (customBedroomsInputRef.value) {
        customBedroomsInputRef.value.focus();
        customBedroomsInputRef.value.select();
    }
};

const finishCustomBedrooms = () => {
    isEditingCustomBedrooms.value = false;
    const val = parseInt(customBedroomsInputVal.value, 10);
    if (!isNaN(val) && val > 0) {
        form.value.bedrooms = Math.min(Math.max(val, 1), 99);
    }
};

const isEditingCustomBathrooms = ref(false);
const customBathroomsInputVal = ref(5);
const customBathroomsInputRef = ref(null);

const startCustomBathrooms = async () => {
    customBathroomsInputVal.value = form.value.bathrooms >= 5 ? form.value.bathrooms : 5;
    isEditingCustomBathrooms.value = true;
    await nextTick();
    if (customBathroomsInputRef.value) {
        customBathroomsInputRef.value.focus();
        customBathroomsInputRef.value.select();
    }
};

const finishCustomBathrooms = () => {
    isEditingCustomBathrooms.value = false;
    const val = parseInt(customBathroomsInputVal.value, 10);
    if (!isNaN(val) && val > 0) {
        form.value.bathrooms = Math.min(Math.max(val, 1), 99);
    }
};

const isEditingCustomBalconies = ref(false);
const customBalconiesInputVal = ref(4);
const customBalconiesInputRef = ref(null);

const startCustomBalconies = async () => {
    customBalconiesInputVal.value = form.value.balconies >= 4 ? form.value.balconies : 4;
    isEditingCustomBalconies.value = true;
    await nextTick();
    if (customBalconiesInputRef.value) {
        customBalconiesInputRef.value.focus();
        customBalconiesInputRef.value.select();
    }
};

const finishCustomBalconies = () => {
    isEditingCustomBalconies.value = false;
    const val = parseInt(customBalconiesInputVal.value, 10);
    if (!isNaN(val) && val >= 0) {
        form.value.balconies = Math.min(Math.max(val, 0), 99);
    }
};

const isResidential = computed(() => isResidentialType(form.value.property_type));

const isCommercialOffice = computed(() => form.value.property_type === PROPERTY_TYPES.COMMERCIAL_OFFICE);

const isCommercialShop = computed(() => [PROPERTY_TYPES.COMMERCIAL_SHOP, PROPERTY_TYPES.COMMERCIAL_SHOWROOM].includes(form.value.property_type));

const isCommercial = computed(() => isCommercialType(form.value.property_type));

const isPlot = computed(() => isPlotType(form.value.property_type));

const steps = computed(() => [
    {
        id: 1,
        title: 'Basic Details',
        subtitle: `${form.value.property_type || 'Property'} for ${getPropertyIntentLabel(form.value.property_for)}`
    },
    {
        id: 2,
        title: 'Location Details',
        subtitle: form.value.project_name
            ? `${form.value.project_name}, ${form.value.city}`
            : (form.value.locality ? `${form.value.locality}, ${form.value.city}` : 'The Avaas by Nagarjuna, Ban...')
    },
    {
        id: 3,
        title: 'Property Profile',
        subtitle: isResidential.value
            ? `${form.value.bedrooms} BHK (${form.value.carpet_area} ${form.value.carpet_area_unit})`
            : `${form.value.carpet_area} ${form.value.carpet_area_unit} ${form.value.property_type}`
    },
    {
        id: 4,
        title: 'Photos, Videos & Voice-over',
        subtitle: `${form.value.photos.length} photos uploaded`
    },
    {
        id: 5,
        title: 'Amenities section',
        subtitle: `${form.value.amenities.length} amenities selected`
    },
]);

const residentialAmenities = [
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

const commercialAmenities = [
    { name: 'Central AC', icon: '❄️' },
    { name: '24x7 Power Backup', icon: '⚡' },
    { name: 'High-Speed Elevators', icon: '🛗' },
    { name: 'Reserved Parking', icon: '🚗' },
    { name: 'Visitor Parking', icon: '🅿️' },
    { name: '24x7 Security & CCTV', icon: '📹' },
    { name: 'Fire Safety System', icon: '🧯' },
    { name: 'Cafeteria / Food Court', icon: '☕' },
    { name: 'Conference Facility', icon: '💼' },
    { name: 'High-Speed Internet / Fiber', icon: '🌐' },
    { name: 'Reception & Waiting Lounge', icon: '🛋️' },
    { name: 'ATM in Campus', icon: '🏧' },
];

const plotAmenities = [
    { name: 'Boundary Wall', icon: '🧱' },
    { name: 'Gated Community', icon: '⛩️' },
    { name: '24x7 Security Guard', icon: '👮' },
    { name: 'Street Lighting', icon: '💡' },
    { name: 'Water Connection', icon: '🚰' },
    { name: 'Electricity Connection', icon: '⚡' },
    { name: 'Sewage / Drainage Line', icon: '🛣️' },
    { name: 'Wide Asphalt / Concrete Road', icon: '🛣️' },
    { name: 'Corner Plot', icon: '📐' },
    { name: 'Park Facing', icon: '🌳' },
    { name: 'Rainwater Harvesting', icon: '🌧️' },
    { name: 'Clear Title / NA Approved', icon: '📜' },
];

const availableAmenities = computed(() => {
    if (isCommercial.value) return commercialAmenities;
    if (isPlot.value) return plotAmenities;
    return residentialAmenities;
});

const form = ref({
    user_type: 'Owner',
    property_for: 'Sell',
    property_type: 'Residential Apartment',
    city: 'Ahmedabad',
    locality: 'Vastral',
    sub_locality: 'Near Vastral Ring Road',
    project_name: 'The Metropark',
    address: 'Vastral Cross Road, Ahmedabad',
    landmark: 'Near Metro Station',
    bedrooms: 2,
    bathrooms: 2,
    balconies: 2,
    carpet_area: 1200,
    carpet_area_unit: 'sq.ft.',
    builtup_area: null,
    builtup_area_unit: 'sq.ft.',
    super_builtup_area: null,
    super_builtup_area_unit: 'sq.ft.',
    furnishing_status: 'Semi-Furnished',
    floor_no: '4',
    total_floors: 4,
    facing: 'East',
    construction_status: 'Ready to Move',
    expected_price: 7740000,
    price_per_sqft: 6450,
    all_inclusive_price: false,
    tax_excluded: false,
    price_negotiable: true,
    maintenance_charge: 2500,
    booking_amount: 100000,
    title: '',
    description: '',
    amenities: ['Lift', '24x7 Security', 'Reserved Parking', 'Power Backup', 'Gym'],
    photos: [],
    // Commercial fields
    cabins: 2,
    workstations: 20,
    conference_rooms: 1,
    pantry: 'Wet Pantry',
    washrooms_type: 'Private',
    main_road_facing: true,
    corner_property: false,
    // Plot fields
    open_sides: 2,
    boundary_wall: true,
    gated_community: true,
    pincode: '',
    latitude: null,
    longitude: null,
});

// Locality autocomplete suggestions
const localitySuggestions = ref([]);
const isLocalityLoading = ref(false);
const showLocalitySuggestions = ref(false);
let localityDebounce = null;

const onLocalityInput = () => {
    if (localityDebounce) clearTimeout(localityDebounce);
    const q = (form.value.locality || '').trim();
    if (q.length < 2) {
        localitySuggestions.value = [];
        showLocalitySuggestions.value = false;
        return;
    }
    localityDebounce = setTimeout(async () => {
        isLocalityLoading.value = true;
        try {
            const res = await fetch(`/api/locations/search?q=${encodeURIComponent(q)}&city=${encodeURIComponent(form.value.city || '')}`);
            if (res.ok) {
                const data = await res.json();
                localitySuggestions.value = Array.isArray(data) ? data : [];
                showLocalitySuggestions.value = localitySuggestions.value.length > 0;
            }
        } catch (e) {
            localitySuggestions.value = [];
        } finally {
            isLocalityLoading.value = false;
        }
    }, 200);
};

const selectLocalitySuggestion = (item) => {
    form.value.locality = item.locality || item.name;
    if (item.sub_locality && !form.value.landmark) {
        form.value.landmark = item.sub_locality;
    }
    if (item.city) {
        const knownCities = ['Ahmedabad', 'Delhi NCR', 'Mumbai', 'Bangalore', 'Pune', 'Hyderabad', 'Chennai', 'Kolkata'];
        const matched = knownCities.find(c => c.toLowerCase() === item.city.toLowerCase() || item.city.toLowerCase().includes(c.toLowerCase()));
        if (matched) {
            form.value.city = matched;
        }
    }
    if (item.pincode) {
        form.value.pincode = item.pincode;
    }
    if (item.latitude && item.longitude) {
        form.value.latitude = item.latitude;
        form.value.longitude = item.longitude;
    }
    showLocalitySuggestions.value = false;
};

const handlePropertyTypeChange = (pt) => {
    form.value.property_type = pt;

    if (isCommercialType(pt)) {
        form.value.bedrooms = 0;
        form.value.balconies = 0;
        if (form.value.bathrooms === 0) form.value.bathrooms = 1;
        form.value.carpet_area_unit = 'sq.ft.';
        if (form.value.furnishing_status === 'Unfurnished') {
            form.value.furnishing_status = 'Semi-Furnished';
        }
        form.value.amenities = ['Central AC', '24x7 Power Backup', 'High-Speed Elevators', 'Reserved Parking', 'Visitor Parking', '24x7 Security & CCTV'];
    } else if (isPlotType(pt)) {
        form.value.bedrooms = 0;
        form.value.bathrooms = 0;
        form.value.balconies = 0;
        form.value.carpet_area_unit = 'sq.yards';
        form.value.furnishing_status = 'Unfurnished';
        form.value.construction_status = 'Immediate Possession';
        form.value.amenities = ['Boundary Wall', 'Gated Community', '24x7 Security Guard', 'Street Lighting', 'Water Connection', 'Electricity Connection'];
    } else {
        // Residential
        if (!form.value.bedrooms || form.value.bedrooms === 0) form.value.bedrooms = 2;
        if (!form.value.bathrooms || form.value.bathrooms === 0) form.value.bathrooms = 2;
        if (form.value.balconies === 0) form.value.balconies = 2;
        form.value.carpet_area_unit = 'sq.ft.';
        form.value.furnishing_status = 'Semi-Furnished';
        form.value.construction_status = 'Ready to Move';
        form.value.amenities = ['Lift', '24x7 Security', 'Reserved Parking', 'Power Backup', 'Gym'];
    }

    recalculatePerSqFt();
    autoGenerateTitle();
    autoGenerateDescription();
};

// Dynamic Property Score Calculation
const propertyScore = computed(() => {
    let score = 0;
    if (form.value.property_type && form.value.property_for) score += 15;
    if (form.value.city && form.value.locality) score += 15;
    if (form.value.carpet_area && form.value.expected_price) {
        if (isResidential.value) {
            if (form.value.bedrooms) score += 30;
        } else {
            score += 30;
        }
    }
    if (isPlot.value) {
        if (form.value.open_sides) score += 10;
    } else {
        if (form.value.floor_no && form.value.total_floors) score += 10;
    }
    if (form.value.photos.length > 0) score += 15;
    if (form.value.amenities.length > 0) score += 10;
    if (form.value.title || form.value.description) score += 5;
    return Math.min(score, 100);
});

const userInitials = computed(() => {
    if (!currentUser.value?.name) return 'U';
    const parts = currentUser.value.name.trim().split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return currentUser.value.name.slice(0, 2).toUpperCase();
});

const setQuickBhk = (num) => {
    form.value.bedrooms = num;
    isOtherBhkSelected.value = false;
    recalculatePerSqFt();
    autoGenerateTitle();
};

const recalculatePerSqFt = () => {
    const price = Number(form.value.expected_price);
    const area = Number(form.value.carpet_area);
    if (price && area && area > 0) {
        form.value.price_per_sqft = Math.round(price / area);
    }
};

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

const goToStep = (step) => {
    errorMessage.value = '';
    currentStep.value = step;
    window.scrollTo({ top: 40, behavior: 'smooth' });
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
const MAX_SIZE_BYTES = 5 * 1024 * 1024;
const MAX_PHOTOS = 10;

const triggerFileInput = () => {
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
        fileInputRef.value.click();
    }
};

const readFileAsDataUrl = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = () => reject(new Error('Failed to read file.'));
        reader.readAsDataURL(file);
    });
};

const processFiles = async (files) => {
    photoUploadError.value = '';
    const fileArr = Array.from(files);
    const remaining = MAX_PHOTOS - form.value.photos.length;
    if (remaining <= 0) {
        photoUploadError.value = 'You can only upload up to 10 photos.';
        return;
    }

    const toProcess = fileArr.slice(0, remaining);
    const errors = [];

    for (const file of toProcess) {
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
            photoFiles.value.push(file);
        } catch {
            errors.push(`Failed to read "${file.name}".`);
        }
    }

    if (errors.length > 0) {
        photoUploadError.value = errors.join(' ');
    }
};

const handleFileSelect = (event) => {
    processFiles(event.target.files);
};

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
    const forText = getPropertyIntentLabel(form.value.property_for);
    const soc = form.value.project_name ? ` in ${form.value.project_name}` : '';

    if (isResidential.value) {
        form.value.title = `${form.value.bedrooms} BHK ${form.value.property_type} for ${forText}${soc}, ${form.value.locality}, ${form.value.city}`;
    } else if (isPlot.value) {
        form.value.title = `${form.value.carpet_area} ${form.value.carpet_area_unit} ${form.value.property_type} for ${forText}${soc}, ${form.value.locality}, ${form.value.city}`;
    } else {
        const furnish = form.value.furnishing_status === 'Furnished' ? 'Fully Furnished ' : (form.value.furnishing_status === 'Semi-Furnished' ? 'Semi-Furnished ' : '');
        form.value.title = `${form.value.carpet_area} ${form.value.carpet_area_unit} ${furnish}${form.value.property_type} for ${forText}${soc}, ${form.value.locality}, ${form.value.city}`;
    }
};

const autoGenerateDescription = () => {
    const forText = form.value.property_for === 'Sell' ? 'sale' : 'rent';
    const proj = form.value.project_name ? ` at ${form.value.project_name}` : '';

    if (isResidential.value) {
        form.value.description = `Spacious and beautifully designed ${form.value.bedrooms} BHK ${form.value.property_type} available for ${forText}${proj} in ${form.value.locality}, ${form.value.city}. Features ${form.value.furnishing_status.toLowerCase()} interiors, ${form.value.carpet_area} ${form.value.carpet_area_unit} carpet area, ${form.value.facing} facing entrance, good sunlight and cross-ventilation. Located in a secure gated community with modern clubhouse and amenities.`;
    } else if (isCommercialOffice.value) {
        form.value.description = `Prime ${form.value.carpet_area} ${form.value.carpet_area_unit} Commercial Office space available for ${forText}${proj} in ${form.value.locality}, ${form.value.city}. Ideal for IT / corporate firms, startups, and consulting agencies. Features modern ${form.value.furnishing_status.toLowerCase()} layout, high-speed elevators, 24x7 power backup, and ample reserved parking with excellent road connectivity.`;
    } else if (isCommercialShop.value) {
        form.value.description = `High-footfall ${form.value.carpet_area} ${form.value.carpet_area_unit} Commercial Retail Shop available for ${forText}${proj} in prime commercial hub of ${form.value.locality}, ${form.value.city}. Excellent frontage, main road visibility, heavy customer footfall, suitable for retail brand, clinic, pharmacy, boutique or café.`;
    } else if (isPlot.value) {
        form.value.description = `Prime ${form.value.carpet_area} ${form.value.carpet_area_unit} ${form.value.property_type} available for ${forText}${proj} in fast-growing locality of ${form.value.locality}, ${form.value.city}. Clear title, NA/commercial approved, wide approach road with immediate registry and electricity/water connectivity. High investment appreciation potential.`;
    } else {
        form.value.description = `Well-maintained ${form.value.carpet_area} ${form.value.carpet_area_unit} ${form.value.property_type} available for ${forText}${proj} in ${form.value.locality}, ${form.value.city}. Ready for immediate possession.`;
    }
};

const submitProperty = async () => {
    errorMessage.value = '';
    const token = localStorage.getItem('realhem_user_token');
    if (!token) {
        isAuthModalOpen.value = true;
        errorMessage.value = 'Please log in to submit your property.';
        return;
    }

    isSubmitting.value = true;

    try {
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
            bedrooms: isResidential.value ? Number(form.value.bedrooms) : 0,
            bathrooms: Number(form.value.bathrooms || 0),
            balconies: isResidential.value ? Number(form.value.balconies || 0) : 0,
            carpet_area: Number(form.value.carpet_area),
            super_builtup_area: form.value.super_builtup_area ? Number(form.value.super_builtup_area) : undefined,
            furnishing_status: isPlot.value ? 'Unfurnished' : form.value.furnishing_status,
            floor_no: isPlot.value ? undefined : (form.value.floor_no || '1'),
            total_floors: form.value.total_floors ? Number(form.value.total_floors) : (isPlot.value ? 2 : 10),
            facing: form.value.facing || 'East',
            construction_status: form.value.construction_status,
            expected_price: Number(form.value.expected_price),
            maintenance_charge: form.value.maintenance_charge ? Number(form.value.maintenance_charge) : 0,
            price_negotiable: Boolean(form.value.price_negotiable),
            title: form.value.title || undefined,
            description: form.value.description || undefined,
            pincode: form.value.pincode || undefined,
            latitude: form.value.latitude ? Number(form.value.latitude) : undefined,
            longitude: form.value.longitude ? Number(form.value.longitude) : undefined,
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
    recalculatePerSqFt();
    autoGenerateTitle();
    autoGenerateDescription();
});
</script>
