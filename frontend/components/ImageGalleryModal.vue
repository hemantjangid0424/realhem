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
                v-if="isOpen && mediaList.length > 0"
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
                            <ImageIcon class="w-5 h-5" />
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

                    <!-- Center Badges: Combined Photos + Videos summary -->
                    <div class="hidden md:flex items-center gap-2 text-xs shrink-0">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700/80 font-semibold flex items-center gap-1.5">
                            <Camera class="w-3.5 h-3.5 text-slate-400" />
                            <span>{{ photosList.length }} {{ photosList.length === 1 ? 'Photo' : 'Photos' }}</span>
                        </span>
                        <span
                            v-if="videosList.length > 0"
                            class="px-2.5 py-1 rounded-lg bg-purple-950/70 text-purple-300 border border-purple-700/60 font-semibold flex items-center gap-1.5"
                        >
                            <Video class="w-3.5 h-3.5 text-purple-400" />
                            <span>{{ videosList.length }} {{ videosList.length === 1 ? 'Video' : 'Videos' }}</span>
                        </span>
                    </div>

                    <!-- Right Controls: Active Item Badge, Counter, View Details CTA, Close -->
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <!-- Current Item Type Badge -->
                        <span
                            v-if="currentItem?.type === 'video'"
                            class="hidden xs:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-600 text-white text-[11px] font-bold shadow-xs animate-pulse"
                        >
                            <Video class="w-3 h-3 text-white" />
                            <span>Video Tour</span>
                        </span>

                        <!-- Unified Media Counter Pill -->
                        <div class="px-3 py-1 rounded-full bg-slate-800/90 border border-slate-700 text-slate-200 text-xs font-bold font-mono">
                            {{ activeIndex + 1 }} / {{ mediaList.length }}
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
                            <X class="w-5 h-5" :stroke-width="2.5" />
                        </button>
                    </div>
                </div>

                <!-- 2. Main Stage (Active Media + Next/Prev Navigation Arrows) -->
                <div class="flex-1 relative flex items-center justify-center px-4 py-3 sm:px-12 sm:py-6 overflow-hidden">
                    <!-- Left Arrow Button -->
                    <button
                        v-if="mediaList.length > 1"
                        type="button"
                        @click.stop="prev"
                        class="absolute left-3 sm:left-6 z-20 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-slate-900/80 hover:bg-slate-800 border border-slate-700/80 text-white flex items-center justify-center backdrop-blur-md transition-all shadow-xl hover:scale-105 active:scale-95 cursor-pointer"
                        title="Previous item (←)"
                    >
                        <ChevronLeft class="w-6 h-6" :stroke-width="2.5" />
                    </button>

                    <!-- Active Image / Video Viewport -->
                    <div class="relative max-w-full max-h-full flex items-center justify-center">
                        <template v-if="currentItem?.type === 'video'">
                            <div class="relative max-h-[66vh] sm:max-h-[72vh] max-w-[94vw] sm:max-w-[85vw] rounded-2xl overflow-hidden shadow-2xl bg-black border border-slate-800/80 flex items-center justify-center">
                                <video
                                    :key="currentItem.url"
                                    :src="currentItem.url"
                                    controls
                                    autoplay
                                    playsinline
                                    class="max-h-[66vh] sm:max-h-[72vh] max-w-[94vw] sm:max-w-[85vw] object-contain"
                                ></video>
                            </div>
                        </template>
                        <template v-else-if="currentItem?.type === 'photo'">
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
                                    :key="currentItem.url"
                                    :src="currentItem.url"
                                    :alt="`${propertyTitle} - ${currentItem.label}`"
                                    class="max-h-[66vh] sm:max-h-[72vh] max-w-[94vw] sm:max-w-[85vw] object-contain rounded-2xl shadow-2xl border border-slate-800/80"
                                />
                            </Transition>
                        </template>
                    </div>

                    <!-- Right Arrow Button -->
                    <button
                        v-if="mediaList.length > 1"
                        type="button"
                        @click.stop="next"
                        class="absolute right-3 sm:right-6 z-20 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-slate-900/80 hover:bg-slate-800 border border-slate-700/80 text-white flex items-center justify-center backdrop-blur-md transition-all shadow-xl hover:scale-105 active:scale-95 cursor-pointer"
                        title="Next item (→)"
                    >
                        <ChevronRight class="w-6 h-6" :stroke-width="2.5" />
                    </button>
                </div>

                <!-- 3. Bottom Filmstrip Thumbnail Carousel (Combined Photos + Videos) -->
                <div class="p-3 sm:p-4 bg-slate-900/90 backdrop-blur-md border-t border-slate-800/80 z-20">
                    <div class="max-w-6xl mx-auto flex items-center justify-center gap-2 overflow-x-auto py-1 scrollbar-thin">
                        <button
                            v-for="(item, idx) in mediaList"
                            :key="idx"
                            type="button"
                            @click="setActiveIndex(idx)"
                            class="relative rounded-xl overflow-hidden shrink-0 transition-all duration-200 cursor-pointer"
                            :class="[
                                activeIndex === idx
                                    ? (item.type === 'video' ? 'ring-2 ring-purple-500 border-purple-400 scale-105 shadow-md' : 'ring-2 ring-blue-500 border-white scale-105 shadow-md')
                                    : 'opacity-60 hover:opacity-100 border-slate-700',
                                item.type === 'video'
                                    ? 'w-18 h-12 sm:w-22 sm:h-14 bg-slate-950 border'
                                    : 'w-16 h-12 sm:w-20 sm:h-14 bg-slate-900 border'
                            ]"
                            :title="item.label"
                        >
                            <!-- Photo Thumbnail -->
                            <template v-if="item.type === 'photo'">
                                <img
                                    :src="item.url"
                                    :alt="item.label"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-if="activeIndex === idx"
                                    class="absolute inset-0 bg-blue-500/10 pointer-events-none"
                                ></div>
                            </template>

                            <!-- Video Thumbnail with Play Badge -->
                            <template v-else>
                                <video
                                    :src="item.url"
                                    preload="metadata"
                                    muted
                                    class="absolute inset-0 w-full h-full object-cover opacity-60 pointer-events-none"
                                ></video>
                                <div class="relative z-10 w-full h-full flex flex-col items-center justify-center bg-black/40 gap-0.5 text-white">
                                    <div class="w-5 h-5 rounded-full bg-purple-600/90 flex items-center justify-center shadow-xs">
                                        <Play class="w-2.5 h-2.5 fill-current translate-x-0.5" />
                                    </div>
                                    <span class="text-[9px] font-black uppercase tracking-tight text-white drop-shadow-xs">Video</span>
                                </div>
                            </template>
                        </button>
                    </div>

                    <!-- Mobile Bottom Bar with View Listing CTA -->
                    <div class="sm:hidden flex items-center justify-between pt-2 mt-1 border-t border-slate-800 text-xs">
                        <span class="text-slate-400 font-semibold">
                            {{ activeIndex + 1 }} of {{ mediaList.length }} · {{ currentItem?.type === 'video' ? 'Video' : 'Photo' }}
                        </span>
                        <NuxtLink
                            v-if="propertyLink"
                            :to="propertyLink"
                            @click="close"
                            class="text-blue-400 font-bold hover:underline"
                        >
                            View Details &rarr;
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
    ImageIcon,
    X,
    ChevronLeft,
    ChevronRight,
    Play,
    Camera,
    Video,
} from 'lucide-vue-next';

export interface GalleryProperty {
    id?: number | string;
    property_id?: number | string;
    slug?: string;
    title?: string;
    photos?: string[];
    videos?: string[];
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
        videos?: string[];
        locality?: string;
        city?: string;
        expected_price?: number | string;
    };
    property?: {
        id?: number | string;
        slug?: string;
        title?: string;
        photos?: string[];
        videos?: string[];
        locality?: string;
        city?: string;
        expected_price?: number | string;
        price?: string;
    };
}

export interface MediaItem {
    type: 'photo' | 'video';
    url: string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        isOpen?: boolean;
        property?: GalleryProperty | null;
        initialIndex?: number;
        initialMediaType?: 'photos' | 'videos';
    }>(),
    {
        isOpen: false,
        property: null,
        initialIndex: 0,
        initialMediaType: 'photos',
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

    // 3. Single photo fallback (only if real photo)
    const singlePhoto = (p as any).photo || p.metadata?.photo || p.property?.photos?.[0];
    if (singlePhoto && typeof singlePhoto === 'string' && !singlePhoto.includes('unsplash.com')) {
        return [singlePhoto];
    }

    return [];
});

// Normalize all videos into a clean string array
const videosList = computed<string[]>(() => {
    const p = props.property;
    if (!p) return [];

    if (Array.isArray(p.videos) && p.videos.length > 0) {
        return p.videos.filter(Boolean);
    }

    if (p.property?.videos && Array.isArray(p.property.videos) && p.property.videos.length > 0) {
        return p.property.videos.filter(Boolean);
    }

    if (p.metadata?.videos && Array.isArray(p.metadata.videos) && p.metadata.videos.length > 0) {
        return p.metadata.videos.filter(Boolean);
    }

    return [];
});

// Combined unified media list (photos followed by videos)
const mediaList = computed<MediaItem[]>(() => {
    const list: MediaItem[] = [];

    photosList.value.forEach((url, i) => {
        list.push({
            type: 'photo',
            url,
            label: `Photo ${i + 1}`,
        });
    });

    videosList.value.forEach((url, i) => {
        list.push({
            type: 'video',
            url,
            label: `Video Tour ${i + 1}`,
        });
    });

    return list;
});

const currentItem = computed<MediaItem | null>(() => {
    if (!mediaList.value.length) return null;
    return mediaList.value[activeIndex.value] || mediaList.value[0];
});

const propertyTitle = computed(() => {
    return props.property?.title || props.property?.property?.title || props.property?.metadata?.title || 'Property Media';
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
    if (mediaList.value.length <= 1) return;
    activeIndex.value = (activeIndex.value + 1) % mediaList.value.length;
};

const prev = () => {
    if (mediaList.value.length <= 1) return;
    activeIndex.value = (activeIndex.value - 1 + mediaList.value.length) % mediaList.value.length;
};

const setActiveIndex = (idx: number) => {
    activeIndex.value = idx;
};

// Reset index when modal opens or property changes
watch(
    () => props.isOpen,
    (val) => {
        if (val) {
            if (props.initialMediaType === 'videos') {
                const firstVideoIdx = mediaList.value.findIndex((m) => m.type === 'video');
                if (firstVideoIdx !== -1) {
                    activeIndex.value = firstVideoIdx;
                } else {
                    activeIndex.value = 0;
                }
            } else if (typeof props.initialIndex === 'number' && props.initialIndex >= 0) {
                activeIndex.value = Math.min(props.initialIndex, Math.max(0, mediaList.value.length - 1));
            } else {
                activeIndex.value = 0;
            }

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
