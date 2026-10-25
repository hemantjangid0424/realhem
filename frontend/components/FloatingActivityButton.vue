<template>
    <transition name="fab-slide">
        <router-link
            v-if="totalActivityCount > 0 && route.path !== '/recent-activity'"
            to="/recent-activity"
            class="fixed bottom-5 right-5 sm:bottom-7 sm:right-7 z-40 inline-flex items-center gap-2.5 px-3.5 sm:px-4 py-2.5 rounded-full bg-slate-900/95 hover:bg-slate-900 text-white shadow-xl hover:shadow-2xl border border-slate-700/90 transition-all duration-300 hover:scale-105 active:scale-95 group select-none backdrop-blur-md"
            title="View Recent Activity (Searches, Viewed, Shortlist)"
        >
            <div class="relative flex items-center justify-center">
                <Activity class="w-4 h-4 text-blue-400 group-hover:scale-110 transition-transform" />
                <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping opacity-75"></span>
            </div>
            <span class="text-xs font-bold tracking-tight">Activity</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-blue-600 text-white shadow-2xs">
                {{ totalActivityCount }}
            </span>
        </router-link>
    </transition>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Activity } from 'lucide-vue-next';

const route = useRoute();
const {
    searchCount,
    viewedCount,
    shortlistCount,
    contactedCount,
    loadAllActivities,
} = useUserActivity();

onMounted(() => {
    loadAllActivities();
});

const totalActivityCount = computed(() => {
    return (
        (searchCount.value || 0) +
        (viewedCount.value || 0) +
        (shortlistCount.value || 0) +
        (contactedCount.value || 0)
    );
});
</script>

<style scoped>
.fab-slide-enter-active,
.fab-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.fab-slide-enter-from,
.fab-slide-leave-to {
    opacity: 0;
    transform: translateY(16px) scale(0.9);
}
</style>

