<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div
            @click="closeModal"
            class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity duration-300"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
            <div
                @click.stop
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-slate-100 my-4 flex flex-col"
            >
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-lg border border-blue-100">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2v14a2 2 0 002 2h14M18 22V8a2 2 0 00-2-2H2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Crop &amp; Edit Profile Picture</h3>
                            <p class="text-xs text-slate-500">Drag to reposition, zoom or rotate to fit perfectly.</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        :disabled="isProcessing"
                        class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition cursor-pointer disabled:opacity-50"
                        title="Close"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Cropper Workspace -->
                <div class="p-6 flex flex-col items-center bg-slate-900 select-none overflow-hidden relative">
                    <!-- Viewport Box -->
                    <div
                        ref="viewportRef"
                        class="relative w-64 h-64 sm:w-72 sm:h-72 overflow-hidden bg-slate-950 shadow-2xl cursor-grab active:cursor-grabbing border border-slate-700/50 touch-none"
                        @mousedown="handleMouseDown"
                        @touchstart="handleTouchStart"
                        @wheel.prevent="handleWheel"
                    >
                        <!-- Interactive Transformed Image -->
                        <img
                            v-if="imageLoaded"
                            ref="imageRef"
                            :src="imageSrc"
                            alt="Crop target"
                            class="absolute pointer-events-none origin-center max-w-none transition-none will-change-transform"
                            :style="imageTransformStyle"
                            draggable="false"
                        />

                        <!-- Crop Mask Overlay (Circular or Rounded) -->
                        <div
                            class="absolute inset-0 pointer-events-none transition-all duration-200"
                            :class="cropShape === 'circle' ? 'rounded-full' : 'rounded-3xl'"
                            style="box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.72); border: 2px solid rgba(255, 255, 255, 0.85);"
                        >
                            <!-- Rule of Thirds Guide Grid (Faint) -->
                            <div class="w-full h-full grid grid-cols-3 grid-rows-3 pointer-events-none opacity-25">
                                <div class="border-r border-b border-white"></div>
                                <div class="border-r border-b border-white"></div>
                                <div class="border-b border-white"></div>
                                <div class="border-r border-b border-white"></div>
                                <div class="border-r border-b border-white"></div>
                                <div class="border-b border-white"></div>
                                <div class="border-r border-b border-white"></div>
                                <div class="border-r border-b border-white"></div>
                                <div></div>
                            </div>
                        </div>

                        <!-- Loading Spinner while image initializes -->
                        <div v-if="!imageLoaded" class="absolute inset-0 flex items-center justify-center bg-slate-950/90 text-white">
                            <span class="inline-block animate-spin text-blue-500 text-2xl">&#8635;</span>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 mt-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8l4-4m0 0l4 4m-4-4v12m8-4l4 4m0 0l4-4m-4 4V4"/></svg>
                        <span>Click and drag to pan &middot; Scroll or use slider to zoom</span>
                    </p>
                </div>

                <!-- Controls Toolbar -->
                <div class="p-5 bg-white border-t border-slate-100 space-y-4">
                    <!-- Zoom Slider -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                <span>Zoom</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                {{ Math.round(zoom * 100) }}%
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="zoom = Math.max(0.5, +(zoom - 0.1).toFixed(2))"
                                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition cursor-pointer"
                                title="Zoom Out"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </button>
                            <input
                                type="range"
                                min="0.5"
                                max="3.0"
                                step="0.05"
                                v-model.number="zoom"
                                class="flex-1 accent-blue-600 cursor-pointer h-1.5 bg-slate-200 rounded-lg"
                            />
                            <button
                                type="button"
                                @click="zoom = Math.min(3.0, +(zoom + 0.1).toFixed(2))"
                                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition cursor-pointer"
                                title="Zoom In"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Editing Tools: Rotate, Flip, Mask Shape, Reset -->
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <!-- Rotate Counter-Clockwise -->
                            <button
                                type="button"
                                @click="rotateBy(-90)"
                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-90 active:bg-blue-100 active:text-blue-700 text-slate-700 text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1 group"
                                title="Rotate 90° Left"
                            >
                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:-rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/></svg>
                                <span class="hidden sm:inline">-90°</span>
                            </button>

                            <!-- Rotate Clockwise -->
                            <button
                                type="button"
                                @click="rotateBy(90)"
                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-90 active:bg-blue-100 active:text-blue-700 text-slate-700 text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1 group"
                                title="Rotate 90° Right"
                            >
                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"/></svg>
                                <span class="hidden sm:inline">+90°</span>
                            </button>

                            <!-- Flip Horizontal -->
                            <button
                                type="button"
                                @click="flipH = !flipH"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1 active:scale-90 group"
                                :class="flipH ? 'bg-blue-100 text-blue-700 border border-blue-200 shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                title="Flip Horizontally"
                            >
                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:scale-x-[-1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span class="hidden sm:inline">Flip</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <!-- Shape Toggle (Circle / Rounded Square) -->
                            <button
                                type="button"
                                @click="cropShape = cropShape === 'circle' ? 'square' : 'circle'"
                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-90 text-slate-700 text-xs font-bold transition-all duration-200 cursor-pointer"
                                title="Toggle Preview Mask"
                            >
                                {{ cropShape === 'circle' ? 'Circle' : 'Square' }}
                            </button>

                            <!-- Reset -->
                            <button
                                type="button"
                                @click="resetTransforms"
                                class="px-2.5 py-1.5 rounded-xl text-slate-500 hover:text-slate-800 active:scale-90 text-xs font-bold hover:bg-slate-100 transition-all duration-200 cursor-pointer group flex items-center gap-1"
                                title="Reset All"
                            >
                                <span class="transition-transform duration-500 group-hover:rotate-180">&#8635;</span>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
                    <button
                        type="button"
                        @click="closeModal"
                        :disabled="isProcessing"
                        class="py-2.5 px-4 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs transition cursor-pointer disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="applyCropAndSave"
                        :disabled="isProcessing || !imageLoaded"
                        class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer disabled:opacity-50 flex items-center gap-2"
                    >
                        <span v-if="isProcessing" class="inline-block animate-spin">&#8635;</span>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ isProcessing ? 'Saving Picture...' : 'Apply & Save Picture' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    imageSrc: { type: String, default: '' },
    isSaving: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'crop']);

const viewportRef = ref(null);
const imageRef = ref(null);

const imageLoaded = ref(false);
const isProcessing = ref(false);
const naturalWidth = ref(0);
const naturalHeight = ref(0);

// Editing state
const zoom = ref(1.0);
const rotation = ref(0);
const flipH = ref(false);
const panX = ref(0);
const panY = ref(0);
const cropShape = ref('circle');

// Dragging state
const isDragging = ref(false);
const dragStart = { x: 0, y: 0 };
const panStart = { x: 0, y: 0 };
let touchPinchDistance = 0;

const resetTransforms = () => {
    zoom.value = 1.0;
    rotation.value = 0;
    flipH.value = false;
    panX.value = 0;
    panY.value = 0;
};

// Base fit scale to cover the viewport
const baseFitScale = computed(() => {
    if (!naturalWidth.value || !naturalHeight.value) return 1;
    const vpSize = 288;
    return Math.max(vpSize / naturalWidth.value, vpSize / naturalHeight.value);
});

// Real-time GPU accelerated CSS transform style with smooth physics animation
const imageTransformStyle = computed(() => {
    const scale = baseFitScale.value * zoom.value;
    const flip = flipH.value ? -1 : 1;
    return {
        top: '50%',
        left: '50%',
        transform: `translate(-50%, -50%) translate(${panX.value}px, ${panY.value}px) rotate(${rotation.value}deg) scale(${scale}) scaleX(${flip})`,
        transition: isDragging.value ? 'none' : 'transform 0.4s cubic-bezier(0.34, 1.35, 0.64, 1)',
        willChange: 'transform',
    };
});

const rotateBy = (deg) => {
    rotation.value += deg;
};

const handleWheel = (e) => {
    const delta = e.deltaY < 0 ? 0.08 : -0.08;
    zoom.value = Math.min(3.0, Math.max(0.5, +(zoom.value + delta).toFixed(2)));
};

// Mouse Drag
const handleMouseDown = (e) => {
    isDragging.value = true;
    dragStart.x = e.clientX;
    dragStart.y = e.clientY;
    panStart.x = panX.value;
    panStart.y = panY.value;

    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', handleMouseUp);
};

const handleMouseMove = (e) => {
    if (!isDragging.value) return;
    const dx = e.clientX - dragStart.x;
    const dy = e.clientY - dragStart.y;
    panX.value = panStart.x + dx;
    panY.value = panStart.y + dy;
};

const handleMouseUp = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('mouseup', handleMouseUp);
};

// Touch Drag & Pinch-to-zoom
const handleTouchStart = (e) => {
    if (e.touches.length === 1) {
        isDragging.value = true;
        dragStart.x = e.touches[0].clientX;
        dragStart.y = e.touches[0].clientY;
        panStart.x = panX.value;
        panStart.y = panY.value;
    } else if (e.touches.length === 2) {
        touchPinchDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
    }
    window.addEventListener('touchmove', handleTouchMove, { passive: false });
    window.addEventListener('touchend', handleTouchEnd);
};

const handleTouchMove = (e) => {
    if (e.touches.length === 1 && isDragging.value) {
        e.preventDefault();
        const dx = e.touches[0].clientX - dragStart.x;
        const dy = e.touches[0].clientY - dragStart.y;
        panX.value = panStart.x + dx;
        panY.value = panStart.y + dy;
    } else if (e.touches.length === 2) {
        e.preventDefault();
        const dist = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
        if (touchPinchDistance > 0) {
            const factor = dist / touchPinchDistance;
            zoom.value = Math.min(3.0, Math.max(0.5, +(zoom.value * factor).toFixed(2)));
        }
        touchPinchDistance = dist;
    }
};

const handleTouchEnd = () => {
    isDragging.value = false;
    touchPinchDistance = 0;
    window.removeEventListener('touchmove', handleTouchMove);
    window.removeEventListener('touchend', handleTouchEnd);
};

// Load image info
const loadImage = (src) => {
    if (!src) return;
    imageLoaded.value = false;
    resetTransforms();

    const img = new Image();
    if (src.startsWith('http') || src.startsWith('/')) {
        img.crossOrigin = 'anonymous';
    }
    img.onload = () => {
        naturalWidth.value = img.naturalWidth || img.width;
        naturalHeight.value = img.naturalHeight || img.height;
        imageLoaded.value = true;
    };
    img.onerror = () => {
        imageLoaded.value = true;
    };
    img.src = src;
};

watch(
    () => props.imageSrc,
    (newSrc) => {
        if (props.isOpen && newSrc) {
            loadImage(newSrc);
        }
    },
    { immediate: true }
);

watch(
    () => props.isOpen,
    (open) => {
        if (open && props.imageSrc) {
            loadImage(props.imageSrc);
        }
    }
);

watch(
    () => props.isSaving,
    (saving) => {
        isProcessing.value = !!saving;
    }
);

const closeModal = () => {
    if (isProcessing.value) return;
    emit('close');
};

/**
 * Render cropped & transformed image into an offscreen canvas and export.
 */
const applyCropAndSave = async () => {
    if (!props.imageSrc || !imageLoaded.value) return;

    isProcessing.value = true;
    try {
        const outputSize = 512;
        const canvas = document.createElement('canvas');
        canvas.width = outputSize;
        canvas.height = outputSize;
        const ctx = canvas.getContext('2d');
        if (!ctx) throw new Error('Canvas not supported');

        const vpElement = viewportRef.value;
        const vpWidth = vpElement ? vpElement.clientWidth : 288;
        const scaleRatio = outputSize / vpWidth;

        const img = new Image();
        if (props.imageSrc.startsWith('http') || props.imageSrc.startsWith('/')) {
            img.crossOrigin = 'anonymous';
        }

        await new Promise((resolve, reject) => {
            img.onload = () => resolve();
            img.onerror = (e) => reject(e);
            img.src = props.imageSrc;
        });

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        ctx.save();
        ctx.translate(outputSize / 2, outputSize / 2);
        ctx.translate(panX.value * scaleRatio, panY.value * scaleRatio);
        ctx.rotate((rotation.value * Math.PI) / 180);
        ctx.scale(flipH.value ? -1 : 1, 1);

        const drawScale = baseFitScale.value * zoom.value * scaleRatio;
        const drawW = naturalWidth.value * drawScale;
        const drawH = naturalHeight.value * drawScale;
        ctx.drawImage(img, -drawW / 2, -drawH / 2, drawW, drawH);
        ctx.restore();

        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    isProcessing.value = false;
                    return;
                }
                const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                emit('crop', { file, dataUrl, blob });
            },
            'image/jpeg',
            0.92
        );
    } catch (err) {
        console.error('Failed to crop avatar:', err);
        isProcessing.value = false;
    }
};

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('mouseup', handleMouseUp);
    window.removeEventListener('touchmove', handleTouchMove);
    window.removeEventListener('touchend', handleTouchEnd);
});
</script>

