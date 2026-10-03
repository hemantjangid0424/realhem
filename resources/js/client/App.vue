<template>
    <div class="min-h-screen bg-slate-50 flex flex-col antialiased">
        <ClientNavbar />
        <main class="flex-1">
            <router-view />
        </main>
        <ClientFooter />
        <VoiceSearchModal />

        <!-- Floating Location Feedback Toast -->
        <transition name="toast-slide">
            <div
                v-if="locationToast"
                class="fixed bottom-6 right-6 z-50 bg-slate-900/95 text-white text-xs font-bold py-2.5 px-4 rounded-2xl shadow-2xl flex items-center gap-2 border border-slate-700/80 backdrop-blur-md animate-fadeIn"
            >
                <span>{{ locationToast }}</span>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import ClientNavbar from './components/ClientNavbar.vue';
import ClientFooter from './components/ClientFooter.vue';
import VoiceSearchModal from './components/VoiceSearchModal.vue';
import { useUserLocation } from './composables/useUserLocation';
import { useCompanyBranding } from './composables/useCompanyBranding';

const { locationToast } = useUserLocation();
const { loadBrandingSettings } = useCompanyBranding();

onMounted(() => {
    loadBrandingSettings();
});
</script>

<style scoped>
.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: all 0.25s ease-out;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
    opacity: 0;
    transform: translateY(12px) scale(0.95);
}
</style>
