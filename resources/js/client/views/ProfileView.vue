<template>
    <div class="min-h-screen bg-slate-50/70 pb-20">
        <!-- Auth Modal -->
        <AuthModal
            :is-open="isAuthModalOpen"
            @close="isAuthModalOpen = false"
            @authenticated="handleUserAuthenticated"
        />

        <!-- Edit Property Modal -->
        <div v-if="editingProperty" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div @click="editingProperty = null" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div @click.stop class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100 p-6 sm:p-8 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Edit Property Listing</h3>
                            <p class="text-xs text-slate-500">ID: #PROP-{{ editingProperty.id }} &middot; {{ editingProperty.locality }}, {{ editingProperty.city }}</p>
                        </div>
                        <button
                            @click="editingProperty = null"
                            class="p-1 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="savePropertyChanges" class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Listing Title</label>
                            <input
                                v-model="editForm.title"
                                type="text"
                                required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                            />
                        </div>

                        <!-- Expected Price & Maintenance -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Expected Price (₹)</label>
                                <input
                                    v-model="editForm.expected_price"
                                    type="number"
                                    required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                />
                                <span class="text-[10px] text-blue-600 font-semibold block mt-0.5">{{ formatPriceWords(editForm.expected_price) }}</span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Maintenance (₹/month)</label>
                                <input
                                    v-model="editForm.maintenance_charge"
                                    type="number"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white"
                                />
                            </div>
                        </div>

                        <!-- Status & Price Negotiable -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Listing Status</label>
                                <select
                                    v-model="editForm.status"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white cursor-pointer"
                                >
                                    <option value="active">🟢 Active (Available)</option>
                                    <option value="sold">🟠 Sold Out</option>
                                    <option value="rented">🟣 Rented Out</option>
                                    <option value="pending_approval">⚪ Pending Approval</option>
                                </select>
                            </div>
                            <div class="pt-4">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        v-model="editForm.price_negotiable"
                                        type="checkbox"
                                        class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer"
                                    />
                                    <span class="text-xs font-bold text-slate-700">Price is Negotiable</span>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                            <textarea
                                v-model="editForm.description"
                                rows="3"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium text-slate-800 outline-none focus:border-blue-500 focus:bg-white resize-none"
                            ></textarea>
                        </div>

                        <!-- Photos -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Property Photos</label>

                            <!-- Existing + New preview thumbnails -->
                            <div v-if="editPhotoPreviews.length > 0" class="flex flex-wrap gap-2 mb-3">
                                <div
                                    v-for="(photo, idx) in editPhotoPreviews"
                                    :key="idx"
                                    class="relative w-20 h-16 rounded-xl overflow-hidden border-2 group"
                                    :class="idx === 0 ? 'border-blue-500' : 'border-slate-200'"
                                >
                                    <img :src="photo" class="w-full h-full object-cover" />
                                    <span v-if="idx === 0" class="absolute top-0.5 left-0.5 bg-blue-600 text-white text-[8px] font-bold px-1 rounded">Cover</span>
                                    <button
                                        type="button"
                                        @click="removeEditPhoto(idx)"
                                        class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-black flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer"
                                        title="Remove photo"
                                    >✕</button>
                                </div>

                                <!-- Add more slot -->
                                <button
                                    v-if="editPhotoPreviews.length < 10"
                                    type="button"
                                    @click="editFileInputRef?.click()"
                                    class="w-20 h-16 rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 hover:border-blue-400 hover:text-blue-500 transition cursor-pointer text-xs gap-0.5"
                                >
                                    <span class="text-lg leading-none">+</span>
                                    <span class="text-[9px] font-bold">Add</span>
                                </button>
                            </div>

                            <!-- Drop zone (shown when no photos yet) -->
                            <div
                                v-if="editPhotoPreviews.length === 0"
                                @click="editFileInputRef?.click()"
                                @dragover.prevent="editIsDragging = true"
                                @dragleave.prevent="editIsDragging = false"
                                @drop.prevent="handleEditFileDrop"
                                :class="['border-2 border-dashed rounded-2xl p-5 text-center transition cursor-pointer', editIsDragging ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-blue-300']"
                            >
                                <div class="text-2xl mb-1">📸</div>
                                <p class="text-xs font-bold text-slate-600">Drop photos here or <span class="text-blue-600 underline">browse</span></p>
                                <p class="text-[10px] text-slate-400 mt-0.5">JPG, JPEG, WEBP · Max 5 MB each · Up to 10 photos</p>
                            </div>

                            <!-- Hidden file input -->
                            <input
                                ref="editFileInputRef"
                                type="file"
                                accept=".jpg,.jpeg,.webp,image/jpeg,image/webp"
                                multiple
                                class="hidden"
                                @change="handleEditFileSelect"
                            />

                            <!-- Error -->
                            <p v-if="editPhotoError" class="mt-1.5 text-[11px] text-red-500 font-semibold">{{ editPhotoError }}</p>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                            <button
                                type="button"
                                @click="editingProperty = null"
                                class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isSavingProperty"
                                class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
                            >
                                <span v-if="isSavingProperty" class="inline-block animate-spin">&#8635;</span>
                                <span>{{ isSavingProperty ? 'Saving...' : 'Save Changes' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- HEADER PROFILE BANNER                      -->
        <!-- ========================================== -->
        <div class="text-white py-10 px-4 sm:px-6 lg:px-8 shadow-sm transition-all duration-300" :style="{ background: heroGradient }">
            <div class="max-w-5xl mx-auto">
                <div v-if="currentUser" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-amber-400 text-slate-950 font-black text-xl flex items-center justify-center shadow-lg">
                            {{ userInitials }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-black text-white tracking-tight">{{ currentUser.name }}</h1>
                                <span class="text-[10px] font-bold bg-emerald-500 text-white px-2 py-0.5 rounded-full capitalize">
                                    {{ currentUser.role || 'Owner' }}
                                </span>
                            </div>
                            <div class="text-xs text-blue-100/90 flex items-center gap-2 mt-1">
                                <span>📧 {{ currentUser.email }}</span>
                                <span>&middot;</span>
                                <span>📱 {{ currentUser.country_code || '+91' }} {{ currentUser.mobile }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Counters -->
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/20">
                        <div class="text-center px-3 border-r border-white/20">
                            <span class="text-xl font-black text-white block">{{ myProperties.length }}</span>
                            <span class="text-[10px] text-blue-200 uppercase font-bold tracking-wider">Properties</span>
                        </div>
                        <div class="text-center px-3 border-r border-white/20">
                            <span class="text-xl font-black text-emerald-300 block">{{ activePropertiesCount }}</span>
                            <span class="text-[10px] text-blue-200 uppercase font-bold tracking-wider">Active</span>
                        </div>
                        <div class="text-center px-3">
                            <span class="text-xl font-black text-amber-300 block">{{ closedPropertiesCount }}</span>
                            <span class="text-[10px] text-blue-200 uppercase font-bold tracking-wider">Sold / Rented</span>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-4">
                    <h1 class="text-2xl font-black text-white">User Profile &amp; Dashboard</h1>
                    <p class="text-xs text-blue-100 mt-1">Manage your properties, inquiries, and personal details on {{ companyName }}.</p>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <!-- ========================================== -->
            <!-- UNLOGGED STATE GATEKEEPER                  -->
            <!-- ========================================== -->
            <div v-if="!currentUser" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 text-center max-w-md mx-auto space-y-6">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xl font-black text-slate-900">Login to View Your Profile</h3>
                    <p class="text-xs text-slate-500">Sign in with your mobile number to manage your properties and settings on {{ companyName }}.</p>
                </div>
                <div class="space-y-3">
                    <button
                        type="button"
                        @click="isAuthModalOpen = true"
                        class="w-full py-3 px-4 rounded-xl text-white font-extrabold text-xs shadow-md cursor-pointer transition hover:opacity-95"
                        :style="{ backgroundColor: brandPrimaryColor }"
                    >
                        Login with Mobile Number &rarr;
                    </button>
                    <button
                        type="button"
                        @click="quickDemoLogin"
                        class="text-xs font-bold hover:underline cursor-pointer"
                        :style="{ color: brandPrimaryColor }"
                    >
                        1-Click Demo Login
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- AUTHENTICATED TABS                         -->
            <!-- ========================================== -->
            <div v-else class="space-y-6">
                <!-- Navigation Tabs -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="activeTab = 'properties'"
                            class="py-2 px-4 rounded-xl text-xs font-extrabold transition cursor-pointer flex items-center gap-2"
                            :class="activeTab === 'properties' ? 'text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'"
                            :style="activeTab === 'properties' ? { backgroundColor: brandPrimaryColor } : {}"
                        >
                            <span>🏢 Manage My Properties</span>
                            <span
                                class="px-1.5 py-0.5 rounded-md text-[10px] font-black"
                                :class="activeTab === 'properties' ? 'bg-black/20 text-white' : 'bg-slate-100 text-slate-600'"
                            >
                                {{ myProperties.length }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'profile'"
                            class="py-2 px-4 rounded-xl text-xs font-extrabold transition cursor-pointer flex items-center gap-2"
                            :class="activeTab === 'profile' ? 'text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'"
                            :style="activeTab === 'profile' ? { backgroundColor: brandPrimaryColor } : {}"
                        >
                            <span>👤 Edit Profile</span>
                        </button>
                    </div>

                    <router-link
                        to="/post-property"
                        class="py-2 px-4 rounded-xl text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer hover:opacity-95"
                        :style="{ backgroundColor: brandAccentColor || '#059669' }"
                    >
                        <span>+ Post New Property</span>
                    </router-link>
                </div>

                <!-- Alert Feedback -->
                <div v-if="successMessage" class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between animate-fadeIn">
                    <div class="flex items-center gap-2">
                        <span>✓</span>
                        <span class="font-bold">{{ successMessage }}</span>
                    </div>
                    <button type="button" @click="successMessage = ''" class="text-emerald-700 hover:text-emerald-900 cursor-pointer">✕</button>
                </div>

                <div v-if="errorMessage" class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between animate-fadeIn">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ errorMessage }}</span>
                    </div>
                    <button type="button" @click="errorMessage = ''" class="text-rose-700 hover:text-rose-900 cursor-pointer">✕</button>
                </div>

                <!-- ============================================== -->
                <!-- TAB 1: MANAGE MY PROPERTIES                    -->
                <!-- ============================================== -->
                <div v-if="activeTab === 'properties'" class="space-y-4">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <button
                                v-for="st in [
                                    { id: 'all', label: 'All Listings' },
                                    { id: 'active', label: 'Active' },
                                    { id: 'sold_rented', label: 'Sold / Rented' }
                                ]"
                                :key="st.id"
                                type="button"
                                @click="statusFilter = st.id"
                                class="py-1 px-3 rounded-lg text-xs font-bold transition cursor-pointer"
                                :class="statusFilter === st.id ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                {{ st.label }}
                            </button>
                        </div>

                        <span class="text-xs text-slate-500">
                            Showing {{ filteredProperties.length }} of {{ myProperties.length }} listings
                        </span>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoadingProperties" class="py-16 text-center space-y-3 bg-white rounded-3xl border border-slate-200">
                        <div class="w-8 h-8 border-3 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-xs text-slate-500 font-semibold">Loading your properties...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="filteredProperties.length === 0" class="py-16 px-4 text-center bg-white rounded-3xl border border-slate-200 space-y-4">
                        <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-2xl">
                            🏢
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-slate-800">No properties found</h3>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                {{ statusFilter === 'all' ? "You haven't posted any properties yet. Post your first listing for free with 0% brokerage." : "No properties match the selected filter." }}
                            </p>
                        </div>
                        <router-link
                            to="/post-property"
                            class="inline-block py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition"
                        >
                            + Post Property Now
                        </router-link>
                    </div>

                    <!-- Properties List -->
                    <div v-else class="space-y-3">
                        <div
                            v-for="prop in filteredProperties"
                            :key="prop.id"
                            class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4"
                        >
                            <!-- Left: Thumbnail & Details -->
                            <div class="flex items-start gap-4 flex-1">
                                <div class="w-24 sm:w-28 h-20 rounded-xl bg-slate-100 overflow-hidden relative shrink-0 border border-slate-200/80">
                                    <img
                                        :src="prop.photos?.[0] || 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=60'"
                                        class="w-full h-full object-cover"
                                        alt="Thumbnail"
                                    />
                                    <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] font-bold px-1.5 py-0.2 rounded">
                                        {{ prop.photos?.length || 1 }} 📷
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full"
                                            :class="getStatusBadgeClass(prop.status)"
                                        >
                                            {{ prop.status || 'Active' }}
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-500">
                                            {{ prop.property_for === 'Sell' ? 'For Sale' : 'For Rent' }}
                                        </span>
                                        <span class="text-[11px] text-slate-400">&middot;</span>
                                        <span class="text-[11px] text-slate-500 font-mono">#PROP-{{ prop.id }}</span>
                                    </div>

                                    <h4 class="text-sm font-black text-slate-900 leading-snug line-clamp-1">
                                        {{ prop.title }}
                                    </h4>

                                    <div class="text-xs text-slate-500 flex items-center gap-2 flex-wrap">
                                        <span>📍 {{ prop.locality }}, {{ prop.city }}</span>
                                        <span>&middot;</span>
                                        <span>{{ prop.bedrooms }} BHK ({{ prop.carpet_area }} sq.ft)</span>
                                        <span>&middot;</span>
                                        <span class="capitalize">{{ prop.furnishing_status }}</span>
                                    </div>

                                    <div class="pt-1 flex items-baseline gap-2">
                                        <span class="text-sm font-black text-blue-700">
                                            {{ formatPriceWords(prop.expected_price) }}
                                        </span>
                                        <span v-if="prop.price_per_sqft" class="text-[10px] text-slate-400">
                                            (₹ {{ prop.price_per_sqft.toLocaleString('en-IN') }}/sq.ft)
                                        </span>
                                        <span v-if="prop.price_negotiable" class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.2 rounded">
                                            Negotiable
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Quick Actions -->
                            <div class="flex items-center gap-2 w-full md:w-auto justify-end border-t md:border-t-0 pt-3 md:pt-0 border-slate-100 flex-wrap">
                                <!-- Status Toggle -->
                                <button
                                    v-if="prop.status === 'active'"
                                    type="button"
                                    @click="togglePropertyStatus(prop, 'sold')"
                                    class="py-1.5 px-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition cursor-pointer"
                                >
                                    Mark Sold
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    @click="togglePropertyStatus(prop, 'active')"
                                    class="py-1.5 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition cursor-pointer"
                                >
                                    Re-activate
                                </button>

                                <!-- Edit Button -->
                                <button
                                    type="button"
                                    @click="openEditModal(prop)"
                                    class="py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition cursor-pointer flex items-center gap-1"
                                >
                                    <span>✏️ Edit</span>
                                </button>

                                <!-- View in listings link -->
                                <router-link
                                    :to="`/listings?city=${encodeURIComponent(prop.city)}`"
                                    class="py-1.5 px-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition cursor-pointer"
                                >
                                    View
                                </router-link>

                                <!-- Delete Button -->
                                <button
                                    type="button"
                                    @click="deleteProperty(prop)"
                                    class="py-1.5 px-2.5 rounded-xl text-rose-600 hover:bg-rose-50 text-xs font-bold transition cursor-pointer"
                                    title="Delete Listing"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 2: EDIT PROFILE                            -->
                <!-- ============================================== -->
                <div v-else-if="activeTab === 'profile'" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 max-w-2xl">
                    <div class="border-b border-slate-100 pb-3 mb-6">
                        <h3 class="text-lg font-black text-slate-900">Personal Information &amp; Preferences</h3>
                        <p class="text-xs text-slate-500">Update your account name, email address, and real estate role.</p>
                    </div>

                    <form @submit.prevent="handleSaveProfile" class="space-y-5">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name</label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-semibold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                            />
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-semibold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                            />
                        </div>

                        <!-- Mobile Number (Display Only / Verified) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mobile Number</label>
                                <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">
                                    <span>✓ Verified via OTP</span>
                                </span>
                            </div>
                            <div class="relative">
                                <input
                                    :value="`${currentUser.country_code || '+91'} ${currentUser.mobile}`"
                                    type="text"
                                    disabled
                                    class="w-full bg-slate-100/80 border border-slate-200 text-slate-600 rounded-xl py-2.5 px-3.5 text-xs font-semibold cursor-not-allowed select-none"
                                />
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Phone number is verified and tied to OTP authentication.</p>
                        </div>

                        <!-- Role Selector -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">I am registered as:</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button
                                    v-for="r in [
                                        { id: 'user', label: 'Buyer / Tenant' },
                                        { id: 'owner', label: 'Property Owner' },
                                        { id: 'agent', label: 'Real Estate Agent' },
                                        { id: 'builder', label: 'Builder / Dev' }
                                    ]"
                                    :key="r.id"
                                    type="button"
                                    @click="profileForm.role = r.id"
                                    class="py-2.5 px-2 rounded-xl text-xs font-bold border transition cursor-pointer text-center"
                                    :class="profileForm.role === r.id ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                                >
                                    {{ r.label }}
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400">All changes update immediately across RealHem</span>
                            <button
                                type="submit"
                                :disabled="isSavingProfile"
                                class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
                            >
                                <span v-if="isSavingProfile" class="inline-block animate-spin">&#8635;</span>
                                <span>{{ isSavingProfile ? 'Saving...' : 'Save Profile Changes' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthModal from '../components/AuthModal.vue';
import { useCompanyBranding } from '../composables/useCompanyBranding';

const { companyName, brandPrimaryColor, brandAccentColor, heroGradient } = useCompanyBranding();

const currentUser = ref(null);
const isAuthModalOpen = ref(false);
const activeTab = ref('properties'); // 'properties' | 'profile'
const statusFilter = ref('all'); // 'all' | 'active' | 'sold_rented'

const myProperties = ref([]);
const isLoadingProperties = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const isSavingProfile = ref(false);
const profileForm = ref({
    name: '',
    email: '',
    role: 'owner',
});

// Edit Property Modal State
const editingProperty = ref(null);
const isSavingProperty = ref(false);
const editForm = ref({
    title: '',
    expected_price: 0,
    maintenance_charge: 0,
    price_negotiable: false,
    status: 'active',
    description: '',
});

/** Photo editing for the edit modal */
const EDIT_ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/webp'];
const EDIT_MAX_SIZE = 5 * 1024 * 1024; // 5 MB
const editFileInputRef = ref(null);
const editIsDragging = ref(false);
const editPhotoError = ref('');
/** base64 / existing-URL previews shown in the thumbnail grid */
const editPhotoPreviews = ref([]);
/** raw File objects for newly added photos (null for existing URL slots) */
const editPhotoFiles = ref([]);

const userInitials = computed(() => {
    if (!currentUser.value?.name) return 'U';
    const parts = currentUser.value.name.trim().split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return currentUser.value.name.slice(0, 2).toUpperCase();
});

const activePropertiesCount = computed(() => {
    return myProperties.value.filter(p => !p.status || p.status === 'active').length;
});

const closedPropertiesCount = computed(() => {
    return myProperties.value.filter(p => p.status === 'sold' || p.status === 'rented').length;
});

const filteredProperties = computed(() => {
    if (statusFilter.value === 'active') {
        return myProperties.value.filter(p => !p.status || p.status === 'active');
    }
    if (statusFilter.value === 'sold_rented') {
        return myProperties.value.filter(p => p.status === 'sold' || p.status === 'rented');
    }
    return myProperties.value;
});

const checkAuth = () => {
    try {
        const stored = localStorage.getItem('realhem_user');
        if (stored) {
            currentUser.value = JSON.parse(stored);
            profileForm.value.name = currentUser.value.name || '';
            profileForm.value.email = currentUser.value.email || '';
            profileForm.value.role = currentUser.value.role || 'owner';
            fetchMyProperties();
        } else {
            currentUser.value = null;
        }
    } catch {
        currentUser.value = null;
    }
};

const handleUserAuthenticated = (user) => {
    currentUser.value = user;
    isAuthModalOpen.value = false;
    profileForm.value.name = user.name || '';
    profileForm.value.email = user.email || '';
    profileForm.value.role = user.role || 'owner';
    fetchMyProperties();
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
    handleUserAuthenticated(demoUser);
};

const fetchMyProperties = async () => {
    const token = localStorage.getItem('realhem_user_token');
    if (!token) return;

    isLoadingProperties.value = true;
    try {
        const response = await fetch('/api/user/properties', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (response.ok) {
            const data = await response.json();
            myProperties.value = data.data || data || [];
        }
    } catch (err) {
        console.error('Failed to load user properties:', err);
    } finally {
        isLoadingProperties.value = false;
    }
};

const handleSaveProfile = async () => {
    successMessage.value = '';
    errorMessage.value = '';
    isSavingProfile.value = true;

    const token = localStorage.getItem('realhem_user_token');
    if (!token) {
        isAuthModalOpen.value = true;
        return;
    }

    try {
        const response = await fetch('/api/user/profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({
                name: profileForm.value.name,
                email: profileForm.value.email,
                role: profileForm.value.role,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.message || Object.values(data.errors || {})[0]?.[0] || 'Could not update profile.';
            return;
        }

        currentUser.value = data.user;
        localStorage.setItem('realhem_user', JSON.stringify(data.user));
        // Dispatch storage event so other components (ClientNavbar) update immediately
        window.dispatchEvent(new Event('storage'));
        successMessage.value = 'Profile updated successfully!';
    } catch (err) {
        console.error(err);
        errorMessage.value = 'Network error while updating profile.';
    } finally {
        isSavingProfile.value = false;
    }
};

const openEditModal = (prop) => {
    editingProperty.value = prop;
    editForm.value = {
        title: prop.title,
        expected_price: prop.expected_price,
        maintenance_charge: prop.maintenance_charge || 0,
        price_negotiable: Boolean(prop.price_negotiable),
        status: prop.status || 'active',
        description: prop.description || '',
    };
    // Seed photo previews from existing property photos
    editPhotoPreviews.value = Array.isArray(prop.photos) ? [...prop.photos] : [];
    // No raw File objects yet for existing URLs
    editPhotoFiles.value = editPhotoPreviews.value.map(() => null);
    editPhotoError.value = '';
};

/** Read a File as a base64 data URL for preview */
const readEditFileAsDataUrl = (file) => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => resolve(e.target.result);
    reader.onerror = () => reject(new Error('Failed to read file.'));
    reader.readAsDataURL(file);
});

/** Add one or more files to the edit photo list */
const processEditFiles = async (files) => {
    editPhotoError.value = '';
    const fileArr = Array.from(files);
    const remaining = 10 - editPhotoPreviews.value.length;

    if (remaining <= 0) {
        editPhotoError.value = 'Maximum 10 photos allowed.';
        return;
    }

    const toProcess = fileArr.slice(0, remaining);
    const errors = [];

    for (const file of toProcess) {
        // Re-check cap on every iteration — async await could let array grow
        if (editPhotoPreviews.value.length >= 10) break;

        if (!EDIT_ALLOWED_TYPES.includes(file.type)) {
            errors.push(`"${file.name}" must be JPG, JPEG, or WEBP.`);
            continue;
        }
        if (file.size > EDIT_MAX_SIZE) {
            errors.push(`"${file.name}" exceeds 5 MB.`);
            continue;
        }
        try {
            const dataUrl = await readEditFileAsDataUrl(file);
            editPhotoPreviews.value.push(dataUrl);
            editPhotoFiles.value.push(file);
        } catch {
            errors.push(`Failed to read "${file.name}".`);
        }
    }

    if (errors.length > 0) {
        editPhotoError.value = errors.join(' ');
    }
};

const removeEditPhoto = (idx) => {
    editPhotoPreviews.value.splice(idx, 1);
    editPhotoFiles.value.splice(idx, 1);
};

const handleEditFileSelect = (event) => {
    processEditFiles(event.target.files);
    // Reset so the same file can be re-selected if needed
    event.target.value = '';
};

const handleEditFileDrop = (event) => {
    editIsDragging.value = false;
    processEditFiles(event.dataTransfer.files);
};

const savePropertyChanges = async () => {
    if (!editingProperty.value) return;
    const token = localStorage.getItem('realhem_user_token');
    if (!token) {
        isAuthModalOpen.value = true;
        return;
    }
    isSavingProperty.value = true;

    try {
        // Step 1: Upload any new photo files; keep existing URLs in place
        const finalPhotoUrls = [];

        for (let i = 0; i < editPhotoPreviews.value.length; i++) {
            const file = editPhotoFiles.value[i];
            if (file instanceof File) {
                // New file — must be uploaded
                const fd = new FormData();
                fd.append('photos[]', file);
                const uploadRes = await fetch('/api/properties/upload-photos', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: fd,
                });
                const uploadData = await uploadRes.json();
                if (!uploadRes.ok) {
                    alert(uploadData.message || 'Photo upload failed.');
                    return;
                }
                finalPhotoUrls.push(...(uploadData.urls ?? []));
            } else {
                // Existing storage URL — keep as-is
                finalPhotoUrls.push(editPhotoPreviews.value[i]);
            }
        }

        // Step 2: Save property with updated fields + resolved photo URLs
        const response = await fetch(`/api/properties/${editingProperty.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({
                ...editingProperty.value,
                ...editForm.value,
                photos: finalPhotoUrls,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message || 'Could not update property.');
            return;
        }

        // Update local list
        const idx = myProperties.value.findIndex(p => p.id === editingProperty.value.id);
        if (idx !== -1) {
            myProperties.value[idx] = data.property;
        }

        editingProperty.value = null;
        successMessage.value = 'Property listing updated successfully!';
    } catch (err) {
        console.error(err);
        alert('Network error while saving property.');
    } finally {
        isSavingProperty.value = false;
    }
};

const togglePropertyStatus = async (prop, newStatus) => {
    const token = localStorage.getItem('realhem_user_token');
    try {
        const response = await fetch(`/api/properties/${prop.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ status: newStatus }),
        });

        if (response.ok) {
            prop.status = newStatus;
            successMessage.value = `Property status updated to ${newStatus}.`;
        }
    } catch (err) {
        console.error(err);
    }
};

const deleteProperty = async (prop) => {
    if (!confirm(`Are you sure you want to delete "${prop.title}"? This cannot be undone.`)) {
        return;
    }

    const token = localStorage.getItem('realhem_user_token');
    try {
        const response = await fetch(`/api/properties/${prop.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });

        if (response.ok) {
            myProperties.value = myProperties.value.filter(p => p.id !== prop.id);
            successMessage.value = 'Property deleted successfully.';
        }
    } catch (err) {
        console.error(err);
    }
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

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'sold':
            return 'bg-amber-100 text-amber-800';
        case 'rented':
            return 'bg-purple-100 text-purple-800';
        case 'pending_approval':
            return 'bg-slate-100 text-slate-700';
        default:
            return 'bg-emerald-100 text-emerald-800';
    }
};

onMounted(() => {
    checkAuth();
});
</script>

