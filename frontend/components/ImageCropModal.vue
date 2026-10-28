<template>
    <Teleport to="body">
        <transition name="crop-modal">
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
                                    <Crop class="w-5 h-5 text-blue-600" />
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
                                <X class="w-4 h-4" :stroke-width="2.5" />
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
                                        <div class="border-r border-white"></div>
                                        <div class="border-r border-white"></div>
                                        <div></div>
                                    </div>
                                </div>

                                <!-- Loading Spinner while image initializes -->
                                <div v-if="!imageLoaded" class="absolute inset-0 flex items-center justify-center bg-slate-950/90 text-white">
                                    <Loader2 class="w-8 h-8 animate-spin text-blue-500" />
                                </div>
                            </div>

                            <p class="text-[11px] text-slate-400 mt-3 flex items-center gap-1.5">
                                <Move class="w-3 h-3 text-slate-400 shrink-0" />
                                <span>Click and drag to pan &middot; Scroll or use slider to zoom</span>
                            </p>
                        </div>

                        <!-- Controls Toolbar -->
                        <div class="p-5 bg-white border-t border-slate-100 space-y-4">
                            <!-- Zoom Slider -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                    <span class="flex items-center gap-1.5">
                                        <ZoomIn class="w-3.5 h-3.5 text-slate-500" />
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
                                        <ZoomOut class="w-3.5 h-3.5" />
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
                                        <ZoomIn class="w-3.5 h-3.5" />
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
                                        <RotateCcw class="w-3.5 h-3.5 transition-transform duration-300 group-hover:-rotate-45" />
                                        <span class="hidden sm:inline">-90°</span>
                                    </button>

                                    <!-- Rotate Clockwise -->
                                    <button
                                        type="button"
                                        @click="rotateBy(90)"
                                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-90 active:bg-blue-100 active:text-blue-700 text-slate-700 text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1 group"
                                        title="Rotate 90° Right"
                                    >
                                        <RotateCw class="w-3.5 h-3.5 transition-transform duration-300 group-hover:rotate-45" />
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
                                        <FlipHorizontal class="w-3.5 h-3.5 transition-transform duration-300 group-hover:scale-x-[-1]" />
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
                                <Loader2 v-if="isProcessing" class="w-4 h-4 animate-spin" />
                                <Check v-else class="w-4 h-4" />
                                <span>{{ isProcessing ? 'Saving Picture...' : 'Apply & Save Picture' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
    Crop,
    X,
    Check,
    RotateCw,
    RotateCcw,
    FlipHorizontal,
    ZoomIn,
    ZoomOut,
    Move,
    Loader2,
} from 'lucide-vue-next';

const props = defineProps<{
    isOpen: boolean;
    imageSrc: string;
    isSaving?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'crop', payload: { file: File; dataUrl: string; blob: Blob }): void;
}>();

const viewportRef = ref<HTMLDivElement | null>(null);
const imageRef = ref<HTMLImageElement | null>(null);

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
const cropShape = ref<'circle' | 'square'>('circle');

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
    const vpSize = 288; // Default CSS viewport reference (sm: 288px)
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

const rotateBy = (deg: number) => {
    rotation.value += deg;
};

const handleWheel = (e: WheelEvent) => {
    const delta = e.deltaY < 0 ? 0.08 : -0.08;
    zoom.value = Math.min(3.0, Math.max(0.5, +(zoom.value + delta).toFixed(2)));
};

// Mouse Drag
const handleMouseDown = (e: MouseEvent) => {
    isDragging.value = true;
    dragStart.x = e.clientX;
    dragStart.y = e.clientY;
    panStart.x = panX.value;
    panStart.y = panY.value;

    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', handleMouseUp);
};

const handleMouseMove = (e: MouseEvent) => {
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
const handleTouchStart = (e: TouchEvent) => {
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

const handleTouchMove = (e: TouchEvent) => {
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
const loadImage = (src: string) => {
    if (!src) return;
    imageLoaded.value = false;
    resetTransforms();

    const img = new Image();
    // Allow crossOrigin for URLs
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
        const outputSize = 512; // Export standard high-res square avatar
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

        await new Promise<void>((resolve, reject) => {
            img.onload = () => resolve();
            img.onerror = (e) => reject(e);
            img.src = props.imageSrc;
        });

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        ctx.save();
        // Translate to canvas center
        ctx.translate(outputSize / 2, outputSize / 2);
        // Apply pan scaled to canvas
        ctx.translate(panX.value * scaleRatio, panY.value * scaleRatio);
        // Apply rotation
        ctx.rotate((rotation.value * Math.PI) / 180);
        // Apply flip
        ctx.scale(flipH.value ? -1 : 1, 1);

        // Draw image centered with base scale and user zoom
        const drawScale = baseFitScale.value * zoom.value * scaleRatio;
        const drawW = naturalWidth.value * drawScale;
        const drawH = naturalHeight.value * drawScale;
        ctx.drawImage(img, -drawW / 2, -drawH / 2, drawW, drawH);
        ctx.restore();

        // Convert canvas to Blob & File
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

<style scoped>
.crop-modal-enter-active,
.crop-modal-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.crop-modal-enter-from,
.crop-modal-leave-to {
    opacity: 0;
    transform: scale(0.97);
}
</style>

