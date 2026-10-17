<template>
    <div class="min-h-screen bg-slate-50 flex font-sans antialiased text-slate-800">
        <!-- Sidebar Navigation -->
        <AdminSidebar
            :is-mobile-open="isMobileOpen"
            :is-collapsed="isCollapsed"
            @close-mobile="isMobileOpen = false"
            @toggle-collapse="toggleCollapse"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- Top Header -->
            <AdminTopHeader
                @toggle-mobile-sidebar="isMobileOpen = !isMobileOpen"
            />

            <!-- Page Content Body -->
            <div class="flex-1">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AdminSidebar from '../components/AdminSidebar.vue';
import AdminTopHeader from '../components/AdminTopHeader.vue';

const route = useRoute();

const isMobileOpen = ref(false);
const isCollapsed = ref(false);

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    try {
        localStorage.setItem('realhem_admin_sidebar_collapsed', isCollapsed.value ? 'true' : 'false');
    } catch {
        // graceful ignore
    }
};

// Auto close mobile drawer on route navigation
watch(() => route.fullPath, () => {
    isMobileOpen.value = false;
});

onMounted(() => {
    try {
        isCollapsed.value = localStorage.getItem('realhem_admin_sidebar_collapsed') === 'true';
    } catch {
        isCollapsed.value = false;
    }
});
</script>

