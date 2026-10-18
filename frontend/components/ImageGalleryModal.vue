<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen && photosList.length > 0"
                class="fixed inset-0 z-[100] bg-slate-950/95 backdrop-blur-md flex flex-col justify-between overflow-hidden select-none font-sans"
                tabindex="-1"
                @keydown.esc="close"
                @keydown.left="prev"
                @keydown.right="next"
            >
                <!-- 1. Top Bar / Header -->
                <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 flex items-center justify-between gap-4 z-20">
                    <!-- Property Summary Info -->
                    <div class="min-w-0 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-600/30 border border-blue-500/40 text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-white font-extrabold text-sm sm:text-base truncate leading-snug">
                                {{ propertyTitle }}
                            </h3>
                            <p class="text-xs text-slate-400 truncate flex items-center gap-2">
                                <span>{{ propertyLocation }}</span>
                                <span v-if="propertyPrice" class="text-emerald-400 font-bold">• {{ propertyPrice }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Right Controls: Counter, View Details CTA, Close -->
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <!-- Photo Counter Pill -->
                        <div class="px-3 py-1 rounded-full bg-slate-800/90 border border-slate-700 text-slate-200 text-xs font-bold font-mono">
                            {{ activeIndex + 1 }} / {{ photosList.length }}
                        </div>

                        <!-- View Property Details Link -->
                        <NuxtLink
                            v-if="propertyLink"
                            :to="propertyLink"
                            @click="close"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-xs"
                        >
                            <span>View Listing</span>
                            <span>&rarr;</span>
                        </NuxtLink>

                        <!-- Close Button -->
                        <button
                            type="button"
                            @click="close"
                            class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition cursor-pointer"
                            title="Close (Esc)"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- 2. Main Stage (Active Photo + Next/Prev Navigation Arrows) -->
                <div class="flex-1 relative flex items-center justify-center px-4 py-3 sm:px-12 sm:py-6 overflow-hidden">
                    <!-- Left Arrow Button -->
                    <button
                        v-if="photosList.length > 1"
                        type="button"
                        @click.stop="prev"
                        class="absolute left-3 sm:left-6 z-20 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-slate-900/80 hover:bg-slate-800 border border-slate-700/80 text-white flex items-center justify-center backdrop-blur-md transition-all shadow-xl hover:scale-105 active:scale-95 cursor-pointer"
                        title="Previous photo (←)"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Active Image with Fade/Scale Transition -->
                    <div class="relative max-w-full max-h-full flex items-center justify-center">
                        <Transition
                            mode="out-in"
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 scale-98"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-98"
                        >
                            <img
                                :key="currentPhoto"
                                :src="currentPhoto"
                                :alt="`${propertyTitle} - Photo ${activeIndex + 1}`"
                                class="max-h-[66vh] sm:max-h-[72vh] max-w-[94vw] sm:max-w-[85vw] object-contain rounded-2xl shadow-2xl border border-slate-800/80"
                            />
                        </Transition>
                    </div>

                    <!-- Right Arrow Button -->
                    <button
                        v-if="photosList.length > 1"
                        type="button"
                        @click.stop="next"
                        class="absolute right-3 sm:right-6 z-20 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-slate-900/80 hover:bg-slate-800 border border-slate-700/80 text-white flex items-center justify-center backdrop-blur-md transition-all shadow-xl hover:scale-105 active:scale-95 cursor-pointer"
                        title="Next photo (→)"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- 3. Bottom Filmstrip Thumbnail Carousel -->
                <div class="p-3 sm:p-4 bg-slate-900/90 backdrop-blur-md border-t border-slate-800/80 z-20">
                    <div class="max-w-6xl mx-auto flex items-center justify-center gap-2 overflow-x-auto py-1 scrollbar-thin">
                        <button
                            v-for="(photo, idx) in photosList"
                            :key="idx"
                            type="button"
                            @click="setActiveIndex(idx)"
                            class="relative rounded-xl overflow-hidden shrink-0 transition-all duration-200 cursor-pointer"
                            :class="activeIndex === idx
                                ? 'w-16 h-12 sm:w-20 sm:h-14 ring-2 ring-blue-500 scale-105 shadow-md border border-white'
                                : 'w-14 h-10 sm:w-16 sm:h-12 opacity-50 hover:opacity-90 border border-slate-700'"
                            :title="`Photo ${idx + 1}`"
                        >
                            <img
                                :src="photo"
                                :alt="`Thumbnail ${idx + 1}`"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-if="activeIndex === idx"
                                class="absolute inset-0 bg-blue-500/10 pointer-events-none"
                            ></div>
                        </button>
                    </div>

                    <!-- Mobile Bottom Bar with View Listing CTA -->
                    <div class="sm:hidden flex items-center justify-between pt-2 mt-1 border-t border-slate-800 text-xs">
                        <span class="text-slate-400 font-semibold">{{ activeIndex + 1 }} of {{ photosList.length }} photos</span>
                        <NuxtLink
                            v-if="propertyLink"
                            :to="propertyLink"
                            @click="close"
                            class="text-blue-400 font-bold hover:underline"
                        >
                            View Listing &rarr;
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

interface GalleryProperty {
    id?: number | string;
    property_id?: number | string;
    slug?: string;
    title?: string;
    photos?: string[];
    locality?: string;
    city?: string;
    price?: string;
    expected_price?: number | string;
    category?: string;
    property_type?: string;
    metadata?: {
        slug?: string;
        title?: string;
        photo?: string;
        locality?: string;
        city?: string;
        expected_price?: number | string;
    };
    property?: {
        id?: number | string;
        slug?: string;
        title?: string;
        photos?: string[];
        locality?: string;
        city?: string;
        expected_price?: number | string;
        price?: string;
    };
}

const props = withDefaults(
    defineProps<{
        isOpen?: boolean;
        property?: GalleryProperty | null;
        initialIndex?: number;
    }>(),
    {
        isOpen: false,
        property: null,
        initialIndex: 0,
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:isOpen', value: boolean): void;
}>();

const activeIndex = ref(0);

// Normalize all photos into a clean string array
const photosList = computed<string[]>(() => {
    const p = props.property;
    if (!p) return [];

    // 1. Direct photos array
    if (Array.isArray(p.photos) && p.photos.length > 0) {
        return p.photos.filter(Boolean);
    }

    // 2. Nested property.photos array
    if (p.property?.photos && Array.isArray(p.property.photos) && p.property.photos.length > 0) {
        return p.property.photos.filter(Boolean);
    }

    // 3. Single photo fallback
    const singlePhoto = (p as any).photo || p.metadata?.photo || p.property?.photos?.[0];
    if (singlePhoto) {
        return [singlePhoto];
    }

    // 4. Default unsplash property fallback
    return ['https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1200&auto=format&fit=crop&q=80'];
});

const currentPhoto = computed<string>(() => {
    if (!photosList.value.length) return '';
    return photosList.value[activeIndex.value] || photosList.value[0];
});

const propertyTitle = computed(() => {
    return props.property?.title || props.property?.property?.title || props.property?.metadata?.title || 'Property Photos';
});

const propertyLocation = computed(() => {
    const p = props.property;
    const loc = p?.locality || p?.property?.locality || p?.metadata?.locality;
    const city = p?.city || p?.property?.city || p?.metadata?.city;
    return [loc, city].filter(Boolean).join(', ') || 'Real Estate';
});

const propertyPrice = computed(() => {
    const p = props.property;
    if (p?.price) return p.price;
    if (p?.property?.price) return p.property.price;
    const exp = p?.expected_price || p?.property?.expected_price || p?.metadata?.expected_price;
    if (exp) {
        const num = Number(exp);
        if (num >= 10000000) return `₹ ${(num / 10000000).toFixed(2)} Cr`;
        if (num >= 100000) return `₹ ${(num / 100000).toFixed(2)} L`;
        return `₹ ${num.toLocaleString('en-IN')}`;
    }
    return '';
});

const propertyLink = computed(() => {
    const p = props.property;
    const slug = p?.slug || p?.property?.slug || p?.metadata?.slug || p?.id || p?.property_id;
    if (slug) {
        return `/property/${slug}`;
    }
    return '';
});

const close = () => {
    emit('close');
    emit('update:isOpen', false);
};

const next = () => {
    if (photosList.value.length <= 1) return;
    activeIndex.value = (activeIndex.value + 1) % photosList.value.length;
};

const prev = () => {
    if (photosList.value.length <= 1) return;
    activeIndex.value = (activeIndex.value - 1 + photosList.value.length) % photosList.value.length;
};

const setActiveIndex = (idx: number) => {
    activeIndex.value = idx;
};

// Reset index when modal opens or property changes
watch(
    () => props.isOpen,
    (val) => {
        if (val) {
            activeIndex.value = props.initialIndex || 0;
            // Prevent background scrolling while modal is open
            if (typeof document !== 'undefined') {
                document.body.style.overflow = 'hidden';
            }
        } else {
            if (typeof document !== 'undefined') {
                document.body.style.overflow = '';
            }
        }
    }
);

// Global Key Listeners for Arrow keys & Escape
const handleGlobalKey = (e: KeyboardEvent) => {
    if (!props.isOpen) return;
    if (e.key === 'Escape') {
        close();
    } else if (e.key === 'ArrowRight') {
        next();
    } else if (e.key === 'ArrowLeft') {
        prev();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKey);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKey);
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});
</script>

