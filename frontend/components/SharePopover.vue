<template>
    <div class="relative inline-block" ref="containerRef">
        <!-- Trigger Slot or Default Share Button -->
        <slot :toggle="toggle" :isOpen="isOpen" :open="open" :close="close">
            <button
                type="button"
                @click="toggle"
                class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all duration-200 flex items-center gap-1.5 cursor-pointer shadow-2xs"
                :class="{ 'ring-2 ring-blue-500/20 border-blue-500 bg-blue-50/50 text-blue-700': isOpen }"
                title="Share property"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 transition" :class="{ 'text-blue-600': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                <span>Share</span>
            </button>
        </slot>

        <!-- macOS Style Share Popover -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0 translate-y-1"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-2.5 w-76 sm:w-80 rounded-2xl bg-white/95 backdrop-blur-xl border border-slate-200/90 shadow-2xl overflow-hidden font-sans text-slate-800"
                :class="align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right'"
                @click.stop
            >
                <!-- Popover Caret / Pointer -->
                <div
                    class="absolute -top-1.5 w-3 h-3 bg-white/95 border-t border-l border-slate-200/90 rotate-45 z-10"
                    :class="align === 'left' ? 'left-6' : 'right-6'"
                ></div>

                <!-- 1. Top Property Preview Header (Matching macOS File Preview) -->
                <div class="p-3 bg-slate-50/80 border-b border-slate-100 flex items-center gap-3 relative z-20">
                    <img
                        v-if="propertyThumbnail"
                        :src="propertyThumbnail"
                        :alt="property?.title || 'Property'"
                        class="w-12 h-12 rounded-xl object-cover border border-slate-200/70 shadow-2xs shrink-0"
                    />
                    <div
                        v-else
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shrink-0 shadow-2xs"
                    >
                        🏠
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <h4 class="font-extrabold text-xs text-slate-900 truncate leading-snug">
                                {{ property?.title || 'Property Listing' }}
                            </h4>
                            <button
                                @click="close"
                                class="w-5 h-5 rounded-full hover:bg-slate-200/70 text-slate-400 hover:text-slate-600 flex items-center justify-center text-xs transition cursor-pointer shrink-0"
                                title="Close"
                            >
                                ✕
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-500 truncate mt-0.5">
                            {{ [property?.locality, property?.city].filter(Boolean).join(', ') || 'Real Estate' }}
                        </p>
                        <div class="text-[11px] font-black text-blue-600 mt-0.5">
                            {{ propertyPriceDisplay }}
                        </div>
                    </div>
                </div>

                <!-- 2. Quick Share Platforms (Row of rounded action buttons) -->
                <div class="p-3 border-b border-slate-100 grid grid-cols-4 gap-2 text-center relative z-20 bg-white/40">
                    <!-- WhatsApp -->
                    <button
                        type="button"
                        @click="shareViaWhatsApp"
                        class="flex flex-col items-center gap-1.5 p-1.5 rounded-xl hover:bg-slate-100/70 transition group cursor-pointer"
                        title="Share on WhatsApp"
                    >
                        <div class="w-10 h-10 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.97.529 1.766.78 2.796.78h.005c3.18 0 5.767-2.587 5.767-5.766.001-3.187-2.575-5.767-5.772-5.767zm7.391 5.763c-.002 4.073-3.314 7.387-7.391 7.387-1.258 0-2.457-.323-3.526-.931l-3.921 1.028 1.047-3.821c-.694-1.122-1.062-2.434-1.06-3.663.002-4.072 3.316-7.387 7.394-7.387 4.074 0 7.387 3.315 7.388 7.387z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-700 truncate w-full">WhatsApp</span>
                    </button>

                    <!-- Telegram -->
                    <button
                        type="button"
                        @click="shareViaTelegram"
                        class="flex flex-col items-center gap-1.5 p-1.5 rounded-xl hover:bg-slate-100/70 transition group cursor-pointer"
                        title="Share on Telegram"
                    >
                        <div class="w-10 h-10 rounded-full bg-[#229ED9] text-white flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.63 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.06-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 4-1.74 6.68-2.88 8.03-3.44 3.83-1.6 4.62-1.88 5.14-1.89.11 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.2-.04.34z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-700 truncate w-full">Telegram</span>
                    </button>

                    <!-- Copy Link -->
                    <button
                        type="button"
                        @click="copyLink"
                        class="flex flex-col items-center gap-1.5 p-1.5 rounded-xl hover:bg-slate-100/70 transition group cursor-pointer"
                        title="Copy Link"
                    >
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center shadow-xs group-hover:scale-105 transition-all duration-200"
                            :class="copied ? 'bg-emerald-600 text-white' : 'bg-slate-900 text-white'"
                        >
                            <svg v-if="copied" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold truncate w-full" :class="copied ? 'text-emerald-600 font-bold' : 'text-slate-700'">
                            {{ copied ? 'Copied!' : 'Copy Link' }}
                        </span>
                    </button>

                    <!-- Email -->
                    <button
                        type="button"
                        @click="shareViaEmail"
                        class="flex flex-col items-center gap-1.5 p-1.5 rounded-xl hover:bg-slate-100/70 transition group cursor-pointer"
                        title="Share via Email"
                    >
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-700 truncate w-full">Email</span>
                    </button>
                </div>

                <!-- 3. macOS Style Action List -->
                <div class="p-1.5 space-y-0.5 relative z-20">
                    <!-- Copy Link Row -->
                    <button
                        type="button"
                        @click="copyLink"
                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-slate-100/80 transition text-left cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-white text-slate-600 flex items-center justify-center shadow-2xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800">Copy Link</span>
                        </div>
                        <span v-if="copied" class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">
                            ✓ Copied
                        </span>
                        <span v-else class="text-[10px] text-slate-400 font-mono">⌘C</span>
                    </button>

                    <!-- Share on X (Twitter) -->
                    <button
                        type="button"
                        @click="shareViaTwitter"
                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-slate-100/80 transition text-left cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center shadow-2xs transition">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800">Share on X</span>
                        </div>
                        <span class="text-slate-400 text-xs">↗</span>
                    </button>

                    <!-- Share on Facebook -->
                    <button
                        type="button"
                        @click="shareViaFacebook"
                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-slate-100/80 transition text-left cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-[#1877F2] text-white flex items-center justify-center shadow-2xs transition">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800">Share on Facebook</span>
                        </div>
                        <span class="text-slate-400 text-xs">↗</span>
                    </button>

                    <!-- Native macOS Share Sheet Trigger (if supported) -->
                    <button
                        v-if="canNativeShare"
                        type="button"
                        @click="triggerNativeShare"
                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-slate-100/80 transition text-left cursor-pointer group border-t border-slate-100/80 mt-1"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shadow-2xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-blue-600">More Options (System Share)...</span>
                        </div>
                        <span class="text-slate-400 text-xs font-mono">⋯</span>
                    </button>
                </div>

                <!-- 4. Quick Link Box at Bottom -->
                <div class="p-2.5 bg-slate-50/90 border-t border-slate-100/90 flex items-center gap-2 relative z-20">
                    <input
                        type="text"
                        readonly
                        :value="shareUrl"
                        @click="copyLink"
                        class="w-full text-[11px] bg-white border border-slate-200/90 rounded-lg px-2.5 py-1.5 text-slate-600 truncate focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer select-all font-mono"
                    />
                    <button
                        type="button"
                        @click="copyLink"
                        class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shrink-0 cursor-pointer shadow-2xs"
                    >
                        {{ copied ? '✓' : 'Copy' }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface PropertyShareData {
    id?: number | string;
    title?: string;
    slug?: string;
    category?: string;
    property_type?: string;
    locality?: string;
    city?: string;
    price?: string;
    expected_price?: number | string;
    photos?: string[];
}

const props = withDefaults(
    defineProps<{
        property?: PropertyShareData | null;
        align?: 'left' | 'right';
    }>(),
    {
        property: null,
        align: 'right',
    }
);

const isOpen = ref(false);
const copied = ref(false);
const containerRef = ref<HTMLElement | null>(null);
const canNativeShare = ref(false);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const open = () => {
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
};

// Share URL
const shareUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.href;
    }
    return '';
});

// Property Thumbnail
const propertyThumbnail = computed(() => {
    if (props.property?.photos && props.property.photos.length > 0) {
        return props.property.photos[0];
    }
    return null;
});

// Property Price Display
const propertyPriceDisplay = computed(() => {
    if (props.property?.price) return props.property.price;
    if (props.property?.expected_price) {
        const val = Number(props.property.expected_price);
        if (val >= 10000000) return `₹ ${(val / 10000000).toFixed(2)} Cr`;
        if (val >= 100000) return `₹ ${(val / 100000).toFixed(2)} L`;
        return `₹ ${val.toLocaleString('en-IN')}`;
    }
    return 'Price on Request';
});

// Summary Text for Sharing
const getShareMessage = () => {
    const p = props.property;
    const title = p?.title || 'Property on RealHem';
    const loc = [p?.locality, p?.city].filter(Boolean).join(', ');
    const price = propertyPriceDisplay.value;

    return `Check out this property on RealHem:\n🏡 ${title}\n📍 ${loc}\n💰 ${price}\n🔗 ${shareUrl.value}`;
};

// Copy Link with Toast Feedback
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(shareUrl.value);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2200);
    } catch {
        // Fallback for older browsers
        const input = document.createElement('input');
        input.value = shareUrl.value;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2200);
    }
};

// WhatsApp
const shareViaWhatsApp = () => {
    const text = encodeURIComponent(getShareMessage());
    window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
};

// Telegram
const shareViaTelegram = () => {
    const url = encodeURIComponent(shareUrl.value);
    const text = encodeURIComponent(`Check out this property: ${props.property?.title || ''}`);
    window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
};

// Email
const shareViaEmail = () => {
    const subject = encodeURIComponent(`Property on RealHem: ${props.property?.title || ''}`);
    const body = encodeURIComponent(getShareMessage());
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
};

// Twitter / X
const shareViaTwitter = () => {
    const text = encodeURIComponent(`Check out this property: ${props.property?.title || 'RealHem'}`);
    const url = encodeURIComponent(shareUrl.value);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
};

// Facebook
const shareViaFacebook = () => {
    const url = encodeURIComponent(shareUrl.value);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
};

// Native System Share (Calls the native macOS / iOS / Android share sheet)
const triggerNativeShare = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: props.property?.title || 'RealHem Property',
                text: `Check out ${props.property?.title || 'this property'} on RealHem`,
                url: shareUrl.value,
            });
            close();
        } catch {
            // User cancelled or aborted
        }
    }
};

// Document Click & Key Listeners
const handleDocumentClick = (e: MouseEvent) => {
    if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
        close();
    }
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        close();
    }
};

onMounted(() => {
    canNativeShare.value = typeof navigator !== 'undefined' && !!navigator.share;
    window.addEventListener('click', handleDocumentClick);
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('click', handleDocumentClick);
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

