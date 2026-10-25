<template>
    <button
        type="button"
        @click.stop.prevent="handleClick"
        @mousedown.stop.prevent
        @mouseup.stop.prevent
        @pointerdown.stop.prevent
        @touchstart.stop
        @touchend.stop.prevent
        :class="[buttonClasses, isPositionedByCaller ? '' : 'relative']"
        :aria-label="isSaved ? 'Remove from favourites' : 'Add to favourites'"
        :title="isSaved ? 'Remove from Favourite' : 'Add to Favourite'"
        class="inline-flex items-center justify-center transition-all select-none group focus:outline-hidden cursor-pointer"
    >
        <!-- Shockwave Pulse Ring (Fires on Click when Favourited) -->
        <span
            v-if="isAnimating && isSaved"
            class="heart-ripple pointer-events-none absolute rounded-full z-40"
            :class="rippleSizeClass"
        />

        <!-- Confetti / Particle Sparkle Explosion (360 degrees) -->
        <span
            v-if="isAnimating && isSaved"
            class="pointer-events-none absolute inset-0 flex items-center justify-center overflow-visible z-50"
        >
            <span
                v-for="particle in particles"
                :key="particle.id"
                class="heart-particle absolute rounded-full"
                :style="{
                    '--dx': `${particle.dx}px`,
                    '--dy': `${particle.dy}px`,
                    '--color': particle.color,
                    '--size': `${particle.size}px`,
                    '--delay': `${particle.delay}ms`,
                }"
            />
        </span>

        <!-- Floating "Saved! ❤️" Pill Badge -->
        <transition name="float-fade">
            <span
                v-if="isAnimating && isSaved && showFloatingToast"
                class="pointer-events-none absolute -top-8.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold text-white bg-gradient-to-r from-rose-500 via-pink-500 to-rose-600 shadow-lg border border-pink-200/50 whitespace-nowrap z-50 tracking-wide flex items-center gap-1"
            >
                <Heart class="w-2.5 h-2.5 fill-current" />
                <span>Favourited!</span>
            </span>
        </transition>

        <!-- Custom Heart Container with Elastic Spring Bounce Animation -->
        <span
            class="relative flex items-center justify-center transition-transform"
            :class="[
                isAnimating && isSaved ? 'heart-bounce' : '',
                isAnimating && !isSaved ? 'heart-unpop' : '',
            ]"
        >
            <svg
                :class="iconSizeClass"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="transition-all duration-200 drop-shadow-xs"
            >
                <defs>
                    <!-- Custom Multi-Stop Ruby Crimson & Rose Gradient -->
                    <linearGradient id="realhem-heart-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ff3366" />
                        <stop offset="45%" stop-color="#f43f5e" />
                        <stop offset="100%" stop-color="#be123c" />
                    </linearGradient>
                </defs>

                <!-- Custom Sculpted Vector Heart Path -->
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                    :fill="isSaved ? 'url(#realhem-heart-gradient)' : 'transparent'"
                    :stroke="isSaved ? '#e11d48' : 'currentColor'"
                    :stroke-width="isSaved ? '0.7' : strokeWidth"
                    :style="isSaved ? { filter: 'drop-shadow(0 1.5px 3.5px rgba(244, 63, 94, 0.5))' } : {}"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />

                <!-- Glossy Specular Sheen Arc (Jewel highlight on active heart) -->
                <path
                    v-if="isSaved"
                    d="M7.2 5.2 C5.8 5.6 4.6 7 4.6 8.8"
                    stroke="rgba(255, 255, 255, 0.65)"
                    stroke-width="1.3"
                    stroke-linecap="round"
                    fill="none"
                />
            </svg>
        </span>

        <!-- Optional Text Label (Button / Detail Bar variant) -->
        <span
            v-if="variant === 'button'"
            class="ml-2 font-bold text-xs tracking-tight transition-colors duration-200"
            :class="isSaved ? 'text-rose-600' : 'text-slate-700 group-hover:text-slate-900'"
        >
            {{ isSaved ? savedText : defaultText }}
        </span>
    </button>
</template>

<script setup>
import { ref, computed, useAttrs } from 'vue';
import { Heart } from 'lucide-vue-next';

const attrs = useAttrs();

const isPositionedByCaller = computed(() => {
    const cls = String(attrs.class || '');
    return /(?:^|\s)(?:!?)(absolute|fixed|sticky)(?:$|\s)/.test(cls);
});

const props = defineProps({
    property: {
        type: Object,
        default: () => ({}),
    },
    propertyId: {
        type: [Number, String],
        default: null,
    },
    variant: {
        type: String,
        default: 'icon', // 'icon' | 'button' | 'minimal'
    },
    size: {
        type: String,
        default: 'md', // 'sm' | 'md' | 'lg'
    },
    defaultText: {
        type: String,
        default: 'Favourite',
    },
    savedText: {
        type: String,
        default: 'Favourited',
    },
    showFloatingToast: {
        type: Boolean,
        default: false,
    },
    strokeWidth: {
        type: [Number, String],
        default: '2',
    },
});

const emit = defineEmits(['toggle', 'shortlisted', 'removed']);

const { toggleShortlist, isShortlisted } = useUserActivity();

const isAnimating = ref(false);
let animationTimer = null;

const targetId = computed(() => {
    return Number(props.propertyId || props.property?.id || props.property?.property_id || 0);
});

const isSaved = computed(() => {
    if (!targetId.value) return false;
    return isShortlisted(targetId.value);
});

// 8 Particle sparks radiating around the heart with vibrant party colors
const particles = [
    { id: 1, dx: 0, dy: -32, color: '#ff2a6d', size: 6, delay: 0 },
    { id: 2, dx: 24, dy: -24, color: '#ff69b4', size: 5, delay: 20 },
    { id: 3, dx: 32, dy: 0, color: '#f59e0b', size: 6, delay: 35 },
    { id: 4, dx: 24, dy: 24, color: '#a855f7', size: 5, delay: 10 },
    { id: 5, dx: 0, dy: 32, color: '#ff3366', size: 6, delay: 30 },
    { id: 6, dx: -24, dy: 24, color: '#38bdf8', size: 5, delay: 15 },
    { id: 7, dx: -32, dy: 0, color: '#10b981', size: 6, delay: 40 },
    { id: 8, dx: -24, dy: -24, color: '#f43f5e', size: 5, delay: 25 },
];

const buttonClasses = computed(() => {
    if (props.variant === 'button') {
        const base = 'px-3.5 py-1.5 rounded-xl border text-xs font-bold shadow-2xs transition-all active:scale-95';
        if (isSaved.value) {
            return `${base} bg-rose-50 border-rose-300 text-rose-600 shadow-rose-100 hover:bg-rose-100/70`;
        }
        return `${base} bg-white border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50`;
    }

    if (props.variant === 'minimal') {
        return 'text-slate-400 hover:text-rose-500 active:scale-90';
    }

    // Default 'icon' circular button
    const sizeMap = {
        sm: 'w-7.5 h-7.5',
        md: 'w-9 h-9',
        lg: 'w-11 h-11',
    };

    const sizeClass = sizeMap[props.size] || sizeMap.md;
    const base = `${sizeClass} rounded-full transition-all duration-200 active:scale-90`;

    if (isSaved.value) {
        return `${base} bg-white text-rose-600 shadow-md ring-2 ring-rose-300/80 hover:bg-rose-50/50`;
    }

    // Sleek frosted glass button that overlays seamlessly on property photos
    return `${base} bg-slate-900/55 hover:bg-slate-900/80 text-white/95 hover:text-rose-300 backdrop-blur-xs border border-white/25 shadow-sm hover:shadow-md hover:scale-105`;
});

const iconSizeClass = computed(() => {
    const map = {
        sm: 'w-4 h-4',
        md: 'w-5 h-5',
        lg: 'w-6 h-6',
    };
    return map[props.size] || map.md;
});

const rippleSizeClass = computed(() => {
    const map = {
        sm: 'w-12 h-12',
        md: 'w-16 h-16',
        lg: 'w-20 h-20',
    };
    return map[props.size] || map.md;
});

const isProcessing = ref(false);

const handleClick = async (event) => {
    if (event) {
        if (typeof event.preventDefault === 'function') event.preventDefault();
        if (typeof event.stopPropagation === 'function') event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
    }

    if (isProcessing.value) return;
    isProcessing.value = true;

    try {
        const propToToggle = props.property && (props.property.id || props.property.property_id)
            ? props.property
            : { id: targetId.value };

        if (!propToToggle.id && !propToToggle.property_id) return;

        // Trigger visual pop animation
        isAnimating.value = true;
        if (animationTimer) clearTimeout(animationTimer);
        animationTimer = setTimeout(() => {
            isAnimating.value = false;
        }, 650);

        const nowSaved = await toggleShortlist(propToToggle);

        emit('toggle', nowSaved);
        if (nowSaved) {
            emit('shortlisted', propToToggle);
        } else {
            emit('removed', propToToggle);
        }
    } finally {
        setTimeout(() => {
            isProcessing.value = false;
        }, 350);
    }
};
</script>

<style scoped>
/* Spring bounce scale & tilt for the Custom Heart */
@keyframes heartBounce {
    0% {
        transform: scale(1) rotate(0deg);
    }
    18% {
        transform: scale(0.64) rotate(-12deg);
    }
    45% {
        transform: scale(1.42) rotate(16deg);
    }
    70% {
        transform: scale(0.92) rotate(-5deg);
    }
    85% {
        transform: scale(1.06) rotate(2deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
    }
}

/* Subtle deflate pop when removing favourite */
@keyframes heartUnpop {
    0% {
        transform: scale(1) rotate(0deg);
    }
    40% {
        transform: scale(0.7) rotate(-8deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
    }
}

/* Concentric expanding rose/pink shockwave ripple */
@keyframes heartRippleExpand {
    0% {
        transform: scale(0.2);
        opacity: 0.95;
        border: 3px solid #f43f5e;
        background-color: rgba(244, 63, 94, 0.4);
    }
    45% {
        opacity: 0.7;
    }
    100% {
        transform: scale(2.6);
        opacity: 0;
        border: 1.5px solid #ff3366;
        background-color: transparent;
    }
}

/* Particle confetti sparks burst outwards */
@keyframes heartParticleBurst {
    0% {
        opacity: 1;
        transform: translate(0, 0) scale(0.2);
    }
    50% {
        opacity: 1;
        transform: translate(var(--dx), var(--dy)) scale(1.3);
    }
    100% {
        opacity: 0;
        transform: translate(calc(var(--dx) * 1.6), calc(var(--dy) * 1.6)) scale(0);
    }
}

.heart-bounce {
    animation: heartBounce 0.65s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.heart-unpop {
    animation: heartUnpop 0.35s ease-out forwards;
}

.heart-ripple {
    animation: heartRippleExpand 0.55s ease-out forwards;
}

.heart-particle {
    width: var(--size);
    height: var(--size);
    background-color: var(--color);
    box-shadow: 0 0 8px var(--color);
    animation: heartParticleBurst 0.55s ease-out var(--delay) forwards;
}

/* Floating micro-toast */
.float-fade-enter-active,
.float-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.float-fade-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.float-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* Accessibility: Respect Reduced Motion Preference */
@media (prefers-reduced-motion: reduce) {
    .heart-bounce,
    .heart-unpop,
    .heart-ripple,
    .heart-particle {
        animation: none !important;
        transform: none !important;
    }
}
</style>

