<template>
    <div>
        <!-- Mobile Drawer Backdrop -->
        <transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isMobileOpen"
                @click="$emit('close-mobile')"
                class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs lg:hidden"
            ></div>
        </transition>

        <!-- Sidebar Container (Desktop Persistent + Mobile Slide-over) -->
        <aside
            class="fixed top-0 bottom-0 left-0 z-50 flex flex-col bg-slate-900 border-r border-slate-800 text-slate-300 transition-all duration-300 ease-in-out select-none"
            :class="[
                // Mobile slide-over positioning
                isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                // Desktop width (collapsed vs expanded)
                isCollapsed ? 'lg:w-20 w-64' : 'w-64',
            ]"
        >
            <!-- 1. Header & Brand -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 shrink-0">
                <router-link to="/admin/dashboard" class="flex items-center gap-3 group overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform shrink-0">
                        RH
                    </div>
                    <div v-show="!isCollapsed" class="flex flex-col truncate transition-opacity duration-200">
                        <span class="font-extrabold text-white text-base tracking-tight leading-none flex items-center gap-1.5">
                            <span>RealHem</span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-blue-500/20 text-blue-400 border border-blue-500/30 uppercase tracking-wider">
                                Admin
                            </span>
                        </span>
                        <span class="text-[11px] text-slate-400 truncate mt-0.5">99acres Real Estate</span>
                    </div>
                </router-link>

                <!-- Mobile Close Button -->
                <button
                    @click="$emit('close-mobile')"
                    class="lg:hidden p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg cursor-pointer"
                    title="Close Sidebar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Desktop Collapse Button -->
                <button
                    @click="$emit('toggle-collapse')"
                    class="hidden lg:flex p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition cursor-pointer"
                    :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                >
                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': isCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- 2. Scrollable Navigation Menu -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-3 space-y-6 scrollbar-thin scrollbar-thumb-slate-800">

                <!-- Section: MAIN / OVERVIEW -->
                <div class="space-y-1">
                    <div v-show="!isCollapsed" class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-500">
                        Overview
                    </div>
                    <router-link
                        to="/admin/dashboard"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isActive('/dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Dashboard' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Dashboard</span>
                    </router-link>
                </div>

                <!-- Section: PROPERTY INVENTORY -->
                <div class="space-y-1">
                    <div v-show="!isCollapsed" class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-500">
                        Property Inventory
                    </div>

                    <!-- All Properties -->
                    <router-link
                        to="/admin/properties"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isPropertiesActive('all') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'All Properties' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span v-show="!isCollapsed" class="truncate">All Properties</span>
                        </div>
                    </router-link>

                    <!-- Pending Moderation -->
                    <router-link
                        to="/admin/properties?tab=pending"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isPropertiesActive('pending') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Pending Review' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span v-show="!isCollapsed" class="truncate">Pending Review</span>
                        </div>
                        <span
                            v-if="pendingCount > 0 && !isCollapsed"
                            class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30 shrink-0"
                        >
                            {{ formatCount(pendingCount) }}
                        </span>
                    </router-link>

                    <!-- Approved & Live -->
                    <router-link
                        to="/admin/properties?tab=active"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isPropertiesActive('active') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Live & Active' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span v-show="!isCollapsed" class="truncate">Live &amp; Active</span>
                        </div>
                    </router-link>

                    <!-- Featured Listings -->
                    <router-link
                        to="/admin/properties?tab=featured"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isPropertiesActive('featured') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Featured' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span v-show="!isCollapsed" class="truncate">Featured Listings</span>
                        </div>
                    </router-link>

                    <!-- Rejected Listings -->
                    <router-link
                        to="/admin/properties?tab=rejected"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isPropertiesActive('rejected') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Rejected' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span v-show="!isCollapsed" class="truncate">Rejected Listings</span>
                        </div>
                    </router-link>
                </div>

                <!-- Section: USERS & PARTNERS -->
                <div class="space-y-1">
                    <div v-show="!isCollapsed" class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-500">
                        Users &amp; CRM
                    </div>

                    <!-- All Users -->
                    <router-link
                        to="/admin/users"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isUsersActive('all') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'All Users' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span v-show="!isCollapsed" class="truncate">All Users &amp; Profiles</span>
                    </router-link>

                    <!-- Brokers & Agents -->
                    <router-link
                        to="/admin/users?role=agent"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isUsersActive('agent') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Brokers & Agents' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Brokers &amp; Agents</span>
                    </router-link>

                    <!-- Builders & Developers -->
                    <router-link
                        to="/admin/users?role=builder"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isUsersActive('builder') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Builders' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Builders &amp; Developers</span>
                    </router-link>

                    <!-- Property Owners -->
                    <router-link
                        to="/admin/users?role=owner"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isUsersActive('owner') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Owners' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Individual Owners</span>
                    </router-link>

                    <!-- Inquiries, Feedback & Reports -->
                    <router-link
                        to="/admin/inquiries"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isActive('/inquiries') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Inquiries & Feedback' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Inquiries &amp; Feedback</span>
                    </router-link>

                    <!-- Careers & Job Postings -->
                    <router-link
                        to="/admin/jobs"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isActive('/jobs') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Careers & Job Postings' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Careers &amp; Jobs</span>
                    </router-link>
                </div>

                <!-- Section: PLATFORM & POLICIES -->
                <div class="space-y-1">
                    <div v-show="!isCollapsed" class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-500">
                        Platform &amp; Policies
                    </div>

                    <!-- Branding & Appearance -->
                    <router-link
                        to="/admin/settings"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isActive('/settings') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Branding & Themes' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Branding &amp; Themes</span>
                    </router-link>

                    <!-- Legal & Policies -->
                    <router-link
                        to="/admin/legal"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group cursor-pointer"
                        :class="isActive('/legal') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                        :title="isCollapsed ? 'Legal & Policies' : ''"
                    >
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span v-show="!isCollapsed" class="truncate">Legal &amp; Compliance</span>
                    </router-link>
                </div>

                <!-- Section: MARKETPLACE SHORTCUTS -->
                <div class="space-y-1 pt-2 border-t border-slate-800/80">
                    <div v-show="!isCollapsed" class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-500">
                        Live Portal
                    </div>

                    <a
                        href="/"
                        target="_blank"
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition cursor-pointer"
                        :title="isCollapsed ? 'Open Client Portal' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span v-show="!isCollapsed" class="truncate">Public Website</span>
                        </div>
                        <span v-show="!isCollapsed" class="text-[10px] text-slate-500">&rarr;</span>
                    </a>

                    <a
                        href="/post-property"
                        target="_blank"
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition cursor-pointer"
                        :title="isCollapsed ? 'Post New Listing' : ''"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span v-show="!isCollapsed" class="truncate">Post Property</span>
                        </div>
                        <span v-show="!isCollapsed" class="text-[10px] text-slate-500">&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 3. Bottom Profile Bar & Logout -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/50 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 truncate">
                        <!-- Avatar -->
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-600/30 border border-blue-500/40 text-blue-300 flex items-center justify-center font-black text-xs">
                                {{ initials }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-slate-900" title="Online"></span>
                        </div>

                        <!-- Name & Role -->
                        <div v-show="!isCollapsed" class="flex flex-col truncate leading-tight">
                            <span class="text-xs font-bold text-white truncate">{{ user?.name || 'Super Admin' }}</span>
                            <span class="text-[10px] text-slate-400 capitalize truncate">{{ user?.role || 'Administrator' }}</span>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <button
                        @click="handleLogout"
                        class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 rounded-lg transition cursor-pointer shrink-0"
                        title="Logout from Admin SPA"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Placeholder spacer to push content right on desktop -->
        <div
            class="hidden lg:block shrink-0 transition-all duration-300 ease-in-out"
            :class="isCollapsed ? 'w-20' : 'w-64'"
        ></div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
    isMobileOpen: {
        type: Boolean,
        default: false,
    },
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close-mobile', 'toggle-collapse']);

const route = useRoute();
const router = useRouter();

const pendingCount = ref(0);

const user = computed(() => {
    try {
        return JSON.parse(localStorage.getItem('realhem_admin_user') || '{}');
    } catch {
        return {};
    }
});

const initials = computed(() => {
    const name = user.value.name || 'AD';
    return name.slice(0, 2).toUpperCase();
});

const isActive = (path) => {
    return route.path === path;
};

const isPropertiesActive = (tab) => {
    if (!route.path.startsWith('/properties')) return false;
    const currentTab = route.query.tab || 'all';
    return currentTab === tab;
};

const isUsersActive = (role) => {
    if (!route.path.startsWith('/users')) return false;
    const currentRole = route.query.role || 'all';
    return currentRole === role;
};

const formatCount = (num) => {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num);
};

const fetchStats = async () => {
    const token = localStorage.getItem('realhem_admin_token');
    if (!token) return;

    try {
        const res = await fetch('/api/admin/dashboard/stats', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (res.ok) {
            const data = await res.json();
            if (data.stats && data.stats.pending_approvals) {
                pendingCount.value = data.stats.pending_approvals;
            }
        }
    } catch {
        // graceful ignore
    }
};

const handleLogout = async () => {
    const token = localStorage.getItem('realhem_admin_token');
    if (token) {
        try {
            await fetch('/api/admin/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
            });
        } catch (err) {
            console.error('Logout error:', err);
        }
    }
    localStorage.removeItem('realhem_admin_token');
    localStorage.removeItem('realhem_admin_user');
    navigateTo('/admin/login');
};

onMounted(() => {
    fetchStats();
});
</script>

