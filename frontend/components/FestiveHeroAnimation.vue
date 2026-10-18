<template>
    <div
        v-if="type && type !== 'none'"
        class="absolute inset-0 overflow-hidden pointer-events-none z-10 select-none"
        aria-hidden="true"
    >
        <!-- 🪔 1. DIWALI LIGHTS & FLOATING DIYAS -->
        <div v-if="type === 'diwali_diyas'" class="absolute inset-0">
            <!-- Golden ambient radial glows -->
            <div class="absolute -top-24 left-1/4 w-96 h-96 rounded-full bg-amber-400/15 blur-3xl pointer-events-none animate-pulse"></div>
            <div class="absolute -bottom-24 right-1/4 w-96 h-96 rounded-full bg-orange-500/15 blur-3xl pointer-events-none animate-pulse" style="animation-delay: 1.5s;"></div>

            <!-- Floating Sparkle Stars / Fireworks -->
            <div
                v-for="s in diwaliSparkles"
                :key="s.id"
                class="absolute diwali-sparkle text-amber-300 drop-shadow-[0_0_8px_rgba(251,191,36,0.8)]"
                :style="{
                    left: s.left,
                    top: s.top,
                    fontSize: s.size,
                    animationDelay: s.delay,
                    animationDuration: s.duration,
                }"
            >
                ✦
            </div>

            <!-- Left Diya Lamp -->
            <div class="absolute bottom-2 left-4 sm:left-10 z-20 flex flex-col items-center drop-shadow-[0_0_20px_rgba(245,158,11,0.6)]">
                <!-- Flame -->
                <div class="diwali-flame w-4 h-7 rounded-[50%_50%_20%_20%] bg-gradient-to-t from-orange-500 via-amber-300 to-yellow-100 shadow-[0_0_15px_#f59e0b] origin-bottom mb-[-2px]"></div>
                <!-- Brass Lamp Body -->
                <svg class="w-14 h-8 text-amber-400" viewBox="0 0 60 30" fill="currentColor">
                    <path d="M5 12 C10 24, 50 24, 55 12 C52 28, 8 28, 5 12 Z" fill="#d97706" />
                    <ellipse cx="30" cy="12" rx="25" ry="5" fill="#f59e0b" />
                    <ellipse cx="30" cy="12" rx="20" ry="3" fill="#b45309" />
                    <path d="M25 24 L35 24 L32 29 L28 29 Z" fill="#92400e" />
                </svg>
            </div>

            <!-- Right Diya Lamp -->
            <div class="absolute bottom-2 right-4 sm:right-10 z-20 flex flex-col items-center drop-shadow-[0_0_20px_rgba(245,158,11,0.6)]">
                <!-- Flame -->
                <div class="diwali-flame w-4 h-7 rounded-[50%_50%_20%_20%] bg-gradient-to-t from-orange-500 via-amber-300 to-yellow-100 shadow-[0_0_15px_#f59e0b] origin-bottom mb-[-2px]" style="animation-delay: 0.4s;"></div>
                <!-- Brass Lamp Body -->
                <svg class="w-14 h-8 text-amber-400" viewBox="0 0 60 30" fill="currentColor">
                    <path d="M5 12 C10 24, 50 24, 55 12 C52 28, 8 28, 5 12 Z" fill="#d97706" />
                    <ellipse cx="30" cy="12" rx="25" ry="5" fill="#f59e0b" />
                    <ellipse cx="30" cy="12" rx="20" ry="3" fill="#b45309" />
                    <path d="M25 24 L35 24 L32 29 L28 29 Z" fill="#92400e" />
                </svg>
            </div>
        </div>

        <!-- 🪘 2. NAVRATRI GARBA & DANDIYA -->
        <div v-if="type === 'navratri_garba'" class="absolute inset-0">
            <!-- Rotating Mandala Silhouette in Background -->
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full border border-pink-400/20 opacity-30 mandala-spin flex items-center justify-center">
                <div class="w-64 h-64 rounded-full border border-yellow-300/30 border-dashed flex items-center justify-center">
                    <div class="w-48 h-48 rounded-full border border-purple-400/30"></div>
                </div>
            </div>

            <!-- Dandiya Sticks on Bottom Right -->
            <div class="absolute bottom-3 right-6 sm:right-14 z-20 flex items-center drop-shadow-md">
                <div class="relative w-16 h-16">
                    <!-- Dandiya Stick 1 -->
                    <div class="absolute inset-0 w-2 h-16 bg-gradient-to-b from-yellow-300 via-pink-500 to-indigo-600 rounded-full rotate-45 origin-center shadow-xs"></div>
                    <!-- Dandiya Stick 2 -->
                    <div class="absolute inset-0 w-2 h-16 bg-gradient-to-b from-yellow-300 via-purple-500 to-pink-600 rounded-full -rotate-45 origin-center shadow-xs"></div>
                    <span class="absolute -top-1 left-3 text-xs animate-bounce">✨</span>
                </div>
            </div>

            <!-- Mirrored Sparkles (Garba Chaniya Choli Mirrors) -->
            <div
                v-for="m in garbaMirrors"
                :key="m.id"
                class="absolute garba-mirror rounded-full bg-white/80 shadow-[0_0_10px_rgba(255,255,255,0.9)]"
                :style="{
                    left: m.left,
                    top: m.top,
                    width: m.size,
                    height: m.size,
                    animationDelay: m.delay,
                }"
            ></div>
        </div>

        <!-- 🎄 3. CHRISTMAS & NEW YEAR SNOWFALL -->
        <div v-if="type === 'christmas_snow'" class="absolute inset-0">
            <!-- Falling Snowflakes -->
            <div
                v-for="flake in snowflakes"
                :key="flake.id"
                class="absolute snowfall text-white/85"
                :style="{
                    left: flake.left,
                    animationDuration: flake.duration,
                    animationDelay: flake.delay,
                    fontSize: flake.size,
                    opacity: flake.opacity,
                }"
            >
                ❄
            </div>

            <!-- Mini Santa Hat on Top Left -->
            <div class="absolute top-2 left-6 text-2xl drop-shadow-md animate-wiggle">
                🎅
            </div>
            <!-- Christmas Pine & Holly on Top Right -->
            <div class="absolute top-2 right-6 text-xl drop-shadow-md animate-pulse">
                🎄
            </div>
        </div>

        <!-- 🎨 4. HOLI COLOR SPLASHES -->
        <div v-if="type === 'holi_colors'" class="absolute inset-0">
            <div class="absolute -top-10 -left-10 w-60 h-60 rounded-full bg-pink-500/25 blur-3xl holi-drift"></div>
            <div class="absolute top-1/3 right-10 w-72 h-72 rounded-full bg-cyan-400/20 blur-3xl holi-drift" style="animation-delay: 2s;"></div>
            <div class="absolute -bottom-10 left-1/3 w-64 h-64 rounded-full bg-yellow-400/25 blur-3xl holi-drift" style="animation-delay: 4s;"></div>
            <div class="absolute top-10 right-1/4 w-48 h-48 rounded-full bg-purple-500/20 blur-3xl holi-drift" style="animation-delay: 1s;"></div>

            <!-- Gulal Powders Splash Icons -->
            <div class="absolute top-4 left-8 text-xl opacity-80 animate-bounce">🌸</div>
            <div class="absolute bottom-4 right-10 text-xl opacity-80 animate-bounce" style="animation-delay: 0.7s;">🎨</div>
        </div>

        <!-- 🪁 5. UTTARAYAN KITE FESTIVAL -->
        <div v-if="type === 'uttarayan_kites'" class="absolute inset-0">
            <!-- Flying Kite 1 -->
            <div class="absolute kite-fly-1 text-2xl drop-shadow-lg" style="left: 15%; top: 25%;">
                <div class="rotate-12 animate-sway">
                    🪁
                </div>
            </div>
            <!-- Flying Kite 2 -->
            <div class="absolute kite-fly-2 text-xl drop-shadow-md" style="left: 75%; top: 15%;">
                <div class="-rotate-12 animate-sway" style="animation-delay: 1.2s;">
                    🪁
                </div>
            </div>
            <!-- Flying Kite 3 -->
            <div class="absolute kite-fly-3 text-sm drop-shadow-sm opacity-75" style="left: 45%; top: 40%;">
                <div class="rotate-45 animate-sway" style="animation-delay: 2.5s;">
                    🪁
                </div>
            </div>
        </div>

        <!-- 🌙 6. EID CRESCENT & LANTERNS -->
        <div v-if="type === 'eid_lanterns'" class="absolute inset-0">
            <!-- Crescent Moon & Star Top Right -->
            <div class="absolute top-4 right-8 sm:right-16 text-3xl text-amber-300 drop-shadow-[0_0_12px_rgba(251,191,36,0.9)] animate-pulse">
                🌙
            </div>

            <!-- Hanging Lantern (Fanoos) Left -->
            <div class="absolute top-0 left-8 sm:left-16 flex flex-col items-center lantern-swing origin-top">
                <div class="w-0.5 h-12 bg-amber-400/70"></div>
                <div class="text-2xl drop-shadow-[0_0_15px_rgba(251,191,36,0.8)]">🏮</div>
            </div>

            <!-- Hanging Lantern (Fanoos) Right -->
            <div class="absolute top-0 right-28 hidden sm:flex flex-col items-center lantern-swing origin-top" style="animation-delay: 1.5s;">
                <div class="w-0.5 h-8 bg-amber-400/70"></div>
                <div class="text-xl drop-shadow-[0_0_15px_rgba(251,191,36,0.8)]">🏮</div>
            </div>

            <!-- Soft Twinkling Stars -->
            <div
                v-for="s in diwaliSparkles.slice(0, 6)"
                :key="s.id"
                class="absolute text-amber-200 diwali-sparkle"
                :style="{ left: s.left, top: s.top, fontSize: s.size, animationDelay: s.delay }"
            >
                ✦
            </div>
        </div>

        <!-- 🇮🇳 7. TRICOLOR FREEDOM CELEBRATION -->
        <div v-if="type === 'tricolor_confetti'" class="absolute inset-0">
            <div
                v-for="c in tricolorPetals"
                :key="c.id"
                class="absolute tricolor-fall"
                :style="{
                    left: c.left,
                    animationDuration: c.duration,
                    animationDelay: c.delay,
                }"
            >
                <span
                    class="block w-2.5 h-3.5 rounded-sm shadow-xs"
                    :style="{ backgroundColor: c.color, transform: `rotate(${c.rot}deg)` }"
                ></span>
            </div>
            <!-- Indian Flag Icon Corner -->
            <div class="absolute top-3 right-6 text-2xl drop-shadow-md">
                🇮🇳
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'none',
    },
});

const diwaliSparkles = [
    { id: 1, left: '10%', top: '20%', size: '14px', delay: '0s', duration: '2.5s' },
    { id: 2, left: '25%', top: '15%', size: '18px', delay: '0.8s', duration: '3s' },
    { id: 3, left: '40%', top: '30%', size: '12px', delay: '1.4s', duration: '2.8s' },
    { id: 4, left: '60%', top: '18%', size: '16px', delay: '0.4s', duration: '3.2s' },
    { id: 5, left: '75%', top: '35%', size: '20px', delay: '1.8s', duration: '2.6s' },
    { id: 6, left: '85%', top: '22%', size: '14px', delay: '1.1s', duration: '3s' },
    { id: 7, left: '50%', top: '10%', size: '22px', delay: '2.2s', duration: '3.5s' },
    { id: 8, left: '92%', top: '40%', size: '16px', delay: '0.6s', duration: '2.7s' },
];

const garbaMirrors = [
    { id: 1, left: '12%', top: '25%', size: '6px', delay: '0s' },
    { id: 2, left: '28%', top: '18%', size: '8px', delay: '0.5s' },
    { id: 3, left: '48%', top: '32%', size: '5px', delay: '1.2s' },
    { id: 4, left: '68%', top: '15%', size: '7px', delay: '0.8s' },
    { id: 5, left: '82%', top: '30%', size: '9px', delay: '1.6s' },
    { id: 6, left: '35%', top: '55%', size: '6px', delay: '2s' },
];

const snowflakes = [
    { id: 1, left: '8%', duration: '6s', delay: '0s', size: '16px', opacity: 0.9 },
    { id: 2, left: '18%', duration: '8s', delay: '1.5s', size: '12px', opacity: 0.6 },
    { id: 3, left: '28%', duration: '7s', delay: '3s', size: '20px', opacity: 0.85 },
    { id: 4, left: '40%', duration: '9s', delay: '0.5s', size: '14px', opacity: 0.7 },
    { id: 5, left: '52%', duration: '6.5s', delay: '2s', size: '18px', opacity: 0.9 },
    { id: 6, left: '65%', duration: '8.5s', delay: '4s', size: '11px', opacity: 0.5 },
    { id: 7, left: '78%', duration: '7.2s', delay: '1s', size: '22px', opacity: 0.8 },
    { id: 8, left: '88%', duration: '8s', delay: '2.5s', size: '15px', opacity: 0.75 },
    { id: 9, left: '95%', duration: '6s', delay: '0.8s', size: '13px', opacity: 0.6 },
];

const tricolorPetals = [
    { id: 1, left: '10%', duration: '5s', delay: '0s', color: '#ea580c', rot: 15 },
    { id: 2, left: '22%', duration: '6.5s', delay: '1.2s', color: '#ffffff', rot: 45 },
    { id: 3, left: '35%', duration: '5.5s', delay: '2.4s', color: '#15803d', rot: -20 },
    { id: 4, left: '48%', duration: '7s', delay: '0.6s', color: '#ea580c', rot: 30 },
    { id: 5, left: '60%', duration: '5.2s', delay: '1.8s', color: '#ffffff', rot: -40 },
    { id: 6, left: '72%', duration: '6s', delay: '3.1s', color: '#15803d', rot: 25 },
    { id: 7, left: '85%', duration: '5.8s', delay: '0.9s', color: '#ea580c', rot: -15 },
    { id: 8, left: '93%', duration: '6.8s', delay: '2s', color: '#15803d', rot: 50 },
];
</script>

<style scoped>
/* 🪔 Diwali Flame Flicker */
@keyframes flameFlicker {
    0%, 100% {
        transform: scale(1) rotate(-1deg);
        filter: brightness(1) drop-shadow(0 0 10px #f59e0b);
    }
    25% {
        transform: scale(1.08, 0.95) rotate(2deg);
        filter: brightness(1.2) drop-shadow(0 0 15px #fbbf24);
    }
    50% {
        transform: scale(0.96, 1.05) rotate(-2deg);
        filter: brightness(0.95) drop-shadow(0 0 8px #f59e0b);
    }
    75% {
        transform: scale(1.04, 0.98) rotate(1deg);
        filter: brightness(1.15) drop-shadow(0 0 12px #fbbf24);
    }
}
.diwali-flame {
    animation: flameFlicker 1.4s ease-in-out infinite alternate;
    will-change: transform, filter;
}

/* ✦ Sparkle Twinkle */
@keyframes sparkleTwinkle {
    0%, 100% {
        opacity: 0.2;
        transform: scale(0.6) rotate(0deg);
    }
    50% {
        opacity: 1;
        transform: scale(1.2) rotate(180deg);
    }
}
.diwali-sparkle {
    animation: sparkleTwinkle ease-in-out infinite;
    will-change: transform, opacity;
}

/* 🪘 Garba Mandala Spin */
@keyframes mandalaRotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.mandala-spin {
    animation: mandalaRotate 40s linear infinite;
}

/* Garba Mirror Shine */
@keyframes mirrorShine {
    0%, 100% { opacity: 0.1; transform: scale(0.7); }
    50% { opacity: 0.95; transform: scale(1.3); }
}
.garba-mirror {
    animation: mirrorShine 2s ease-in-out infinite;
}

/* ❄ Snowfall Animation */
@keyframes snowFalling {
    0% {
        transform: translateY(-20px) translateX(0);
    }
    50% {
        transform: translateY(120px) translateX(15px);
    }
    100% {
        transform: translateY(260px) translateX(-15px);
    }
}
.snowfall {
    animation: snowFalling linear infinite;
    top: -20px;
    will-change: transform;
}

/* 🎨 Holi Powder Floating */
@keyframes holiFloat {
    0%, 100% { transform: scale(1) translate(0, 0); }
    50% { transform: scale(1.15) translate(15px, -15px); }
}
.holi-drift {
    animation: holiFloat 8s ease-in-out infinite;
}

/* 🪁 Kite Drift */
@keyframes kiteDrift1 {
    0%, 100% { transform: translateY(0) translateX(0) rotate(12deg); }
    50% { transform: translateY(-20px) translateX(25px) rotate(18deg); }
}
.kite-fly-1 {
    animation: kiteDrift1 5s ease-in-out infinite;
}

@keyframes kiteDrift2 {
    0%, 100% { transform: translateY(0) translateX(0) rotate(-12deg); }
    50% { transform: translateY(-25px) translateX(-20px) rotate(-16deg); }
}
.kite-fly-2 {
    animation: kiteDrift2 6s ease-in-out infinite;
}

@keyframes kiteDrift3 {
    0%, 100% { transform: translateY(0) translateX(0) rotate(35deg); }
    50% { transform: translateY(-15px) translateX(15px) rotate(45deg); }
}
.kite-fly-3 {
    animation: kiteDrift3 4s ease-in-out infinite;
}

/* 🌙 Lantern Sway */
@keyframes lanternSway {
    0%, 100% { transform: rotate(-5deg); }
    50% { transform: rotate(5deg); }
}
.lantern-swing {
    animation: lanternSway 3.5s ease-in-out infinite;
}

/* 🇮🇳 Tricolor Petals Falling */
@keyframes tricolorFalling {
    0% {
        transform: translateY(-20px) rotate(0deg);
        opacity: 0.9;
    }
    100% {
        transform: translateY(260px) rotate(360deg);
        opacity: 0.1;
    }
}
.tricolor-fall {
    animation: tricolorFalling linear infinite;
    top: -20px;
    will-change: transform, opacity;
}

@keyframes sway {
    0%, 100% { transform: rotate(-8deg); }
    50% { transform: rotate(8deg); }
}
.animate-sway {
    animation: sway 3s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .diwali-flame,
    .diwali-sparkle,
    .mandala-spin,
    .garba-mirror,
    .snowfall,
    .holi-drift,
    .kite-fly-1,
    .kite-fly-2,
    .kite-fly-3,
    .lantern-swing,
    .tricolor-fall {
        animation: none !important;
    }
}
</style>

