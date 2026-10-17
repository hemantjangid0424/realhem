<template>
    <AdminLayout>
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                            <span>💼</span>
                            <span>Careers &amp; Recruitment</span>
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                            HR Desk
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Post and manage active career openings and review candidate applications in one place.
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <button
                        v-if="activeTab === 'postings'"
                        @click="openCreateModal"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Post New Job</span>
                    </button>

                    <button
                        v-if="activeTab === 'applications'"
                        @click="fetchApplications"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isAppsLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Refresh Applications</span>
                    </button>
                </div>
            </div>

            <!-- Primary Module Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-3 overflow-x-auto scrollbar-none">
                <button
                    @click="switchTab('postings')"
                    class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl font-black text-xs transition cursor-pointer shrink-0"
                    :class="activeTab === 'postings'
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                        : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                >
                    <span>💼</span>
                    <span>Job Postings</span>
                    <span
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                        :class="activeTab === 'postings' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600'"
                    >
                        {{ counts.total || 0 }}
                    </span>
                </button>

                <button
                    @click="switchTab('applications')"
                    class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl font-black text-xs transition cursor-pointer shrink-0"
                    :class="activeTab === 'applications'
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                        : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                >
                    <span>📥</span>
                    <span>Job Applications</span>
                    <span
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                        :class="activeTab === 'applications' ? 'bg-blue-700 text-white' : (appCounts.career_pending ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600')"
                    >
                        {{ appCounts.career || appCounts.total || 0 }}
                    </span>
                    <span
                        v-if="appCounts.career_pending"
                        class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"
                        title="Pending applications waiting for review"
                    ></span>
                </button>
            </div>

            <!-- ========================================== -->
            <!-- TAB 1: JOB POSTINGS MANAGEMENT             -->
            <!-- ========================================== -->
            <div v-if="activeTab === 'postings'" class="space-y-6">
                <!-- Stats Ribbon -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs text-center space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Total Positions</span>
                        <p class="text-2xl font-black text-slate-900">{{ counts.total || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-xs text-center space-y-1 bg-emerald-50/30">
                        <span class="text-[10px] font-black uppercase text-emerald-700 block tracking-wider">Active Openings</span>
                        <p class="text-2xl font-black text-emerald-600">{{ counts.active || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-1 bg-slate-50/50">
                        <span class="text-[10px] font-black uppercase text-slate-500 block tracking-wider">Inactive / Closed</span>
                        <p class="text-2xl font-black text-slate-600">{{ counts.inactive || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-blue-200 shadow-xs text-center space-y-1 bg-blue-50/30">
                        <span class="text-[10px] font-black uppercase text-blue-700 block tracking-wider">Departments</span>
                        <p class="text-2xl font-black text-blue-600">{{ departments.length || 0 }}</p>
                    </div>
                </div>

                <!-- Filters & Search Toolbar -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1 md:pb-0">
                        <button
                            @click="selectedDept = 'all'; fetchJobs()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap"
                            :class="selectedDept === 'all'
                                ? 'bg-slate-900 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            All Departments
                        </button>
                        <button
                            v-for="dept in departments"
                            :key="dept"
                            @click="selectedDept = dept; fetchJobs()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap"
                            :class="selectedDept === dept
                                ? 'bg-slate-900 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            {{ dept }}
                        </button>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        <select
                            v-model="selectedStatus"
                            @change="fetchJobs"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>

                        <div class="relative w-48 sm:w-64">
                            <input
                                v-model="searchQuery"
                                @keyup.enter="fetchJobs"
                                type="text"
                                placeholder="Search title, tech, city..."
                                class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                            <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Jobs Table -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div v-if="isLoading" class="p-12 text-center text-slate-500 space-y-2">
                        <svg class="w-6 h-6 animate-spin mx-auto text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-xs font-bold">Loading job postings...</p>
                    </div>

                    <div v-else-if="jobs.length === 0" class="p-12 text-center text-slate-500 space-y-3">
                        <span class="text-3xl block">📋</span>
                        <h3 class="text-base font-bold text-slate-800">No job postings found</h3>
                        <p class="text-xs text-slate-400">Click "Post New Job" above to create your first opening.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/80 text-slate-800 font-black uppercase text-[10px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3.5 px-4">Role Title &amp; Details</th>
                                    <th class="py-3.5 px-4">Department</th>
                                    <th class="py-3.5 px-4">Location &amp; Type</th>
                                    <th class="py-3.5 px-4">Experience</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="job in jobs" :key="job.id" class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 align-top max-w-sm">
                                        <div class="space-y-1">
                                            <p class="font-extrabold text-slate-900 text-sm">{{ job.title }}</p>
                                            <p class="text-slate-500 text-[11px] line-clamp-2 leading-relaxed">{{ job.description }}</p>
                                            <div v-if="job.tags && job.tags.length" class="flex items-center gap-1.5 flex-wrap pt-1">
                                                <span v-for="tag in job.tags" :key="tag" class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-semibold">
                                                    {{ tag }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ job.department }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                        <div class="space-y-0.5">
                                            <p class="font-bold text-slate-800">{{ job.location }}</p>
                                            <p class="text-slate-400 text-[11px]">{{ job.type }}</p>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 align-top whitespace-nowrap font-semibold text-slate-700">
                                        {{ job.experience || 'Not specified' }}
                                    </td>

                                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                        <button
                                            @click="toggleActive(job)"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide cursor-pointer transition flex items-center gap-1.5"
                                            :class="job.is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'"
                                            :title="job.is_active ? 'Click to deactivate' : 'Click to activate'"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="job.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            <span>{{ job.is_active ? 'Active' : 'Inactive' }}</span>
                                        </button>
                                    </td>

                                    <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                @click="openEditModal(job)"
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                                                title="Edit Job Details"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                @click="deleteJob(job)"
                                                class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition cursor-pointer"
                                                title="Delete Job"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: CANDIDATE JOB APPLICATIONS         -->
            <!-- ========================================== -->
            <div v-else-if="activeTab === 'applications'" class="space-y-6">
                <!-- Applications Stats Ribbon -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs text-center space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Total Applications</span>
                        <p class="text-2xl font-black text-slate-900">{{ appCounts.career || appCounts.total || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-xs text-center space-y-1 bg-amber-50/30">
                        <span class="text-[10px] font-black uppercase text-amber-700 block tracking-wider">Pending Review</span>
                        <p class="text-2xl font-black text-amber-600">{{ appCounts.career_pending || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-xs text-center space-y-1 bg-emerald-50/30">
                        <span class="text-[10px] font-black uppercase text-emerald-700 block tracking-wider">Shortlisted / Resolved</span>
                        <p class="text-2xl font-black text-emerald-600">{{ appCounts.career_resolved || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-1 bg-slate-50/50">
                        <span class="text-[10px] font-black uppercase text-slate-500 block tracking-wider">Dismissed</span>
                        <p class="text-2xl font-black text-slate-600">{{ appCounts.career_dismissed || 0 }}</p>
                    </div>
                </div>

                <!-- Applications Filter Toolbar -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <button
                            v-for="st in [
                                { id: 'all', label: 'All Candidates' },
                                { id: 'pending', label: 'Pending' },
                                { id: 'resolved', label: 'Resolved' },
                                { id: 'dismissed', label: 'Dismissed' }
                            ]"
                            :key="st.id"
                            @click="appSelectedStatus = st.id; fetchApplications(1)"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap"
                            :class="appSelectedStatus === st.id
                                ? 'bg-slate-900 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            {{ st.label }}
                        </button>
                    </div>

                    <div class="relative w-full sm:w-72">
                        <input
                            v-model="appSearchQuery"
                            @keyup.enter="fetchApplications(1)"
                            type="text"
                            placeholder="Search applicant name, email, role..."
                            class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        />
                        <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div v-if="isAppsLoading" class="p-12 text-center text-slate-500 space-y-2">
                        <svg class="w-6 h-6 animate-spin mx-auto text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-xs font-bold">Loading candidate applications...</p>
                    </div>

                    <div v-else-if="applications.length === 0" class="p-12 text-center text-slate-500 space-y-3">
                        <span class="text-3xl block">📭</span>
                        <h3 class="text-base font-bold text-slate-800">No applications found</h3>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">Candidates who submit their application through your public Careers page will appear directly here.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/80 text-slate-800 font-black uppercase text-[10px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3.5 px-4">Applied Date &amp; Role</th>
                                    <th class="py-3.5 px-4">Candidate Details</th>
                                    <th class="py-3.5 px-4">Portfolio / Links</th>
                                    <th class="py-3.5 px-4">Cover Note Preview</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="app in applications" :key="app.id" class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                        <div class="space-y-1">
                                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider block w-fit bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ getAppliedRole(app) }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 block font-normal">{{ formatDate(app.created_at) }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 align-top">
                                        <div class="space-y-0.5">
                                            <p class="font-extrabold text-slate-900">{{ app.name }}</p>
                                            <a :href="`mailto:${app.email}`" class="text-blue-600 hover:underline block truncate max-w-[190px]">{{ app.email }}</a>
                                            <p v-if="app.phone" class="text-slate-500 font-semibold">{{ app.phone }}</p>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                        <a
                                            v-if="app.metadata && app.metadata.portfolio_url"
                                            :href="app.metadata.portfolio_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold hover:underline"
                                        >
                                            <span>Portfolio / Link</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                        <span v-else class="text-slate-300 text-xs">—</span>
                                    </td>

                                    <td class="py-3.5 px-4 align-top max-w-xs">
                                        <p class="text-slate-600 line-clamp-2 leading-relaxed text-[11px]">
                                            {{ app.message }}
                                        </p>
                                    </td>

                                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide inline-block" :class="getStatusBadge(app.status)">
                                            {{ app.status }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                @click="viewAppDetails(app)"
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                                                title="View full application"
                                            >
                                                View
                                            </button>

                                            <button
                                                v-if="app.status === 'pending'"
                                                @click="updateAppStatus(app.id, 'resolved')"
                                                class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition cursor-pointer"
                                                title="Mark as Shortlisted / Resolved"
                                            >
                                                Resolve
                                            </button>

                                            <button
                                                v-if="app.status === 'pending'"
                                                @click="updateAppStatus(app.id, 'dismissed')"
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition cursor-pointer"
                                                title="Dismiss Application"
                                            >
                                                Dismiss
                                            </button>

                                            <button
                                                @click="deleteApplication(app.id)"
                                                class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition cursor-pointer"
                                                title="Delete Application"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Applications Pagination Footer -->
                    <div v-if="appPagination.total > appPagination.per_page" class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
                        <span>Showing {{ ((appPagination.current_page - 1) * appPagination.per_page) + 1 }} to {{ Math.min(appPagination.current_page * appPagination.per_page, appPagination.total) }} of {{ appPagination.total }}</span>

                        <div class="flex items-center gap-1.5">
                            <button
                                :disabled="appPagination.current_page <= 1"
                                @click="fetchApplications(appPagination.current_page - 1)"
                                class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 font-semibold disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed"
                            >
                                &larr; Prev
                            </button>
                            <button
                                :disabled="appPagination.current_page >= appPagination.last_page"
                                @click="fetchApplications(appPagination.current_page + 1)"
                                class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 font-semibold disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed"
                            >
                                Next &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- CANDIDATE APPLICATION DETAIL MODAL         -->
        <!-- ========================================== -->
        <div v-if="activeAppModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 relative max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider block w-fit mb-1.5 bg-blue-100 text-blue-800 border border-blue-200">
                            Candidate Application
                        </span>
                        <h3 class="text-xl font-black text-slate-900">{{ getAppliedRole(activeAppModal) }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Submitted on {{ formatDate(activeAppModal.created_at) }}</p>
                    </div>
                    <button @click="activeAppModal = null" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer p-1">✕</button>
                </div>

                <!-- Submitter Contact Information -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Candidate</span>
                        <span class="font-extrabold text-slate-900">{{ activeAppModal.name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Email</span>
                        <a :href="`mailto:${activeAppModal.email}`" class="font-bold text-blue-600 hover:underline">{{ activeAppModal.email }}</a>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block uppercase text-[10px]">Phone</span>
                        <a v-if="activeAppModal.phone" :href="`tel:${activeAppModal.phone}`" class="font-bold text-slate-900 hover:text-blue-600">{{ activeAppModal.phone }}</a>
                        <span v-else class="font-bold text-slate-400">Not provided</span>
                    </div>
                </div>

                <!-- Portfolio / Links -->
                <div v-if="activeAppModal.metadata && activeAppModal.metadata.portfolio_url" class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/60 text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-blue-900 block text-[11px] uppercase tracking-wider">Candidate Portfolio / Profile</span>
                        <a :href="activeAppModal.metadata.portfolio_url" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-bold hover:underline truncate block max-w-md mt-0.5">
                            {{ activeAppModal.metadata.portfolio_url }}
                        </a>
                    </div>
                    <a
                        :href="activeAppModal.metadata.portfolio_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-1.5 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition"
                    >
                        Open Link &rarr;
                    </a>
                </div>

                <!-- Full Cover Note Content -->
                <div class="space-y-1.5">
                    <span class="text-xs font-bold text-slate-700 block">Candidate Statement / Cover Letter:</span>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                        {{ activeAppModal.message }}
                    </div>
                </div>

                <!-- Status Action Bar -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-600">Status:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase" :class="getStatusBadge(activeAppModal.status)">
                            {{ activeAppModal.status }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            v-if="activeAppModal.status !== 'resolved'"
                            @click="updateAppStatus(activeAppModal.id, 'resolved')"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition cursor-pointer"
                        >
                            Mark Shortlisted / Resolved
                        </button>
                        <button
                            v-if="activeAppModal.status !== 'dismissed'"
                            @click="updateAppStatus(activeAppModal.id, 'dismissed')"
                            class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            Dismiss
                        </button>
                        <button
                            @click="activeAppModal = null"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition cursor-pointer"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- CREATE / EDIT JOB OPENING MODAL           -->
        <!-- ========================================== -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 relative max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[10px] font-black uppercase text-blue-600 tracking-wider block mb-1">Recruitment Management</span>
                        <h3 class="text-xl font-black text-slate-900">{{ editingJob ? 'Edit Job Opening' : 'Post New Job Opening' }}</h3>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer p-1">✕</button>
                </div>

                <form @submit.prevent="saveJob" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Job Title <span class="text-rose-500">*</span></label>
                        <input
                            v-model="jobForm.title"
                            type="text"
                            required
                            placeholder="e.g. Senior Full-Stack Engineer"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Department <span class="text-rose-500">*</span></label>
                            <input
                                v-model="jobForm.department"
                                list="departmentsList"
                                required
                                placeholder="e.g. Engineering, Sales, Product"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                            <datalist id="departmentsList">
                                <option value="Engineering" />
                                <option value="Product & Design" />
                                <option value="Sales & Growth" />
                                <option value="Operations" />
                                <option value="Marketing & Content" />
                                <option value="Legal & Trust" />
                            </datalist>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Location <span class="text-rose-500">*</span></label>
                            <input
                                v-model="jobForm.location"
                                type="text"
                                required
                                placeholder="e.g. Ahmedabad / Remote"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Employment Type <span class="text-rose-500">*</span></label>
                            <select
                                v-model="jobForm.type"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            >
                                <option value="Full Time">Full Time</option>
                                <option value="Part Time">Part Time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700">Experience Required</label>
                            <input
                                v-model="jobForm.experience"
                                type="text"
                                placeholder="e.g. 3–5 Years"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Key Tags / Skills (Comma-separated)</label>
                        <input
                            v-model="rawTags"
                            type="text"
                            placeholder="Laravel 12, Vue 3, Tailwind, MySQL"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Role Description <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="jobForm.description"
                            required
                            rows="4"
                            placeholder="Overview of duties, technical goals, and scope of impact..."
                            class="w-full p-4 rounded-xl border border-slate-200 text-xs sm:text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                        ></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Requirements &amp; Qualifications</label>
                        <textarea
                            v-model="jobForm.requirements"
                            rows="3"
                            placeholder="• Bullet points of requirements&#10;• Experience with frameworks&#10;• Education or certifications"
                            class="w-full p-4 rounded-xl border border-slate-200 text-xs sm:text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="jobForm.is_active" class="sr-only peer" />
                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-xs font-bold text-slate-700">Publish immediately (Active on public Careers page)</span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSaving"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg v-if="isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ isSaving ? 'Saving...' : (editingJob ? 'Update Job' : 'Publish Job') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';

const route = useRoute();
const router = useRouter();

// Tab state: 'postings' | 'applications'
const activeTab = ref(route.query.tab === 'applications' ? 'applications' : 'postings');

const switchTab = (tab) => {
    activeTab.value = tab;
    router.replace({ query: { ...route.query, tab } });
    if (tab === 'applications' && applications.value.length === 0) {
        fetchApplications(1);
    }
};

/* ----------------------------------------------------
 * JOB POSTINGS STATE & METHODS
 * -------------------------------------------------- */
const jobs = ref([]);
const counts = ref({});
const departments = ref([]);
const isLoading = ref(false);
const isSaving = ref(false);

const selectedDept = ref('all');
const selectedStatus = ref('');
const searchQuery = ref('');

const isModalOpen = ref(false);
const editingJob = ref(null);
const rawTags = ref('');

const jobForm = ref({
    title: '',
    department: 'Engineering',
    location: 'Ahmedabad',
    type: 'Full Time',
    experience: '',
    description: '',
    requirements: '',
    is_active: true,
    tags: [],
});

const fetchJobs = async () => {
    isLoading.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    const params = new URLSearchParams({
        department: selectedDept.value,
        status: selectedStatus.value,
        search: searchQuery.value,
    });

    try {
        const res = await fetch(`/api/admin/jobs?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        const data = await res.json();
        if (data.success) {
            jobs.value = data.data || [];
            counts.value = data.counts || {};
            departments.value = data.departments || [];
        }
    } catch (err) {
        console.error('Failed to load jobs:', err);
    } finally {
        isLoading.value = false;
    }
};

const openCreateModal = () => {
    editingJob.value = null;
    rawTags.value = '';
    jobForm.value = {
        title: '',
        department: 'Engineering',
        location: 'Ahmedabad / Remote',
        type: 'Full Time',
        experience: '2–5 Years',
        description: '',
        requirements: '',
        is_active: true,
        tags: [],
    };
    isModalOpen.value = true;
};

const openEditModal = (job) => {
    editingJob.value = job;
    rawTags.value = Array.isArray(job.tags) ? job.tags.join(', ') : '';
    jobForm.value = {
        title: job.title,
        department: job.department,
        location: job.location,
        type: job.type,
        experience: job.experience || '',
        description: job.description,
        requirements: job.requirements || '',
        is_active: Boolean(job.is_active),
        tags: job.tags || [],
    };
    isModalOpen.value = true;
};

const saveJob = async () => {
    isSaving.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    const parsedTags = rawTags.value
        .split(',')
        .map(t => t.trim())
        .filter(t => t.length > 0);

    const payload = {
        ...jobForm.value,
        tags: parsedTags,
    };

    try {
        const url = editingJob.value
            ? `/api/admin/jobs/${editingJob.value.id}`
            : '/api/admin/jobs';
        const method = editingJob.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (res.ok) {
            isModalOpen.value = false;
            fetchJobs();
        } else {
            alert(data.message || 'Validation error while saving job.');
        }
    } catch (err) {
        console.error('Failed to save job:', err);
        alert('Network error while saving job posting.');
    } finally {
        isSaving.value = false;
    }
};

const toggleActive = async (job) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/jobs/${job.id}/toggle-active`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (res.ok) {
            job.is_active = !job.is_active;
            if (job.is_active) {
                counts.value.active++;
                counts.value.inactive--;
            } else {
                counts.value.active--;
                counts.value.inactive++;
            }
        }
    } catch (err) {
        console.error('Failed to toggle job active status:', err);
    }
};

const deleteJob = async (job) => {
    if (!confirm(`Are you sure you want to delete "${job.title}"?`)) return;

    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/jobs/${job.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (res.ok) {
            fetchJobs();
        }
    } catch (err) {
        console.error('Failed to delete job:', err);
    }
};

/* ----------------------------------------------------
 * JOB APPLICATIONS STATE & METHODS
 * -------------------------------------------------- */
const applications = ref([]);
const appCounts = ref({});
const isAppsLoading = ref(false);
const activeAppModal = ref(null);

const appSelectedStatus = ref('all');
const appSearchQuery = ref('');

const appPagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
});

const fetchApplications = async (page = 1) => {
    isAppsLoading.value = true;
    const token = localStorage.getItem('realhem_admin_token');

    const params = new URLSearchParams({
        page: page,
        type: 'career',
        status: appSelectedStatus.value,
        search: appSearchQuery.value,
    });

    try {
        const res = await fetch(`/api/admin/inquiries?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        const data = await res.json();
        if (data.success) {
            applications.value = data.data || [];
            appCounts.value = data.counts || {};
            appPagination.value = data.pagination || appPagination.value;
        }
    } catch (err) {
        console.error('Failed to load candidate applications:', err);
    } finally {
        isAppsLoading.value = false;
    }
};

const viewAppDetails = (app) => {
    activeAppModal.value = app;
};

const updateAppStatus = async (id, newStatus) => {
    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/inquiries/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ status: newStatus }),
        });
        const data = await res.json();
        if (res.ok) {
            if (activeAppModal.value && activeAppModal.value.id === id) {
                activeAppModal.value.status = newStatus;
            }
            fetchApplications(appPagination.value.current_page);
        } else {
            alert(data.message || 'Failed to update application status.');
        }
    } catch (err) {
        console.error('Failed to update application status:', err);
    }
};

const deleteApplication = async (id) => {
    if (!confirm('Are you sure you want to delete this job application record?')) return;

    const token = localStorage.getItem('realhem_admin_token');
    try {
        const res = await fetch(`/api/admin/inquiries/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (res.ok) {
            if (activeAppModal.value && activeAppModal.value.id === id) {
                activeAppModal.value = null;
            }
            fetchApplications(appPagination.value.current_page);
        }
    } catch (err) {
        console.error('Failed to delete application:', err);
    }
};

const getAppliedRole = (item) => {
    if (item.metadata && item.metadata.job_title) {
        return item.metadata.job_title;
    }
    if (item.subject) {
        return item.subject.replace(/^Application for /i, '');
    }
    return 'General Candidate Application';
};

const formatDate = (isoStr) => {
    if (!isoStr) return '';
    const d = new Date(isoStr);
    return d.toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'resolved':
            return 'bg-emerald-100 text-emerald-800';
        case 'dismissed':
            return 'bg-slate-200 text-slate-700';
        default:
            return 'bg-amber-100 text-amber-800';
    }
};

onMounted(() => {
    fetchJobs();
    fetchApplications(1);
});
</script>
