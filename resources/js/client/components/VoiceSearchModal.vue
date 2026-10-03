<template>
    <div
        v-if="isVoiceModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-200"
        @click.self="handleClose"
    >
        <div
            class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full p-8 sm:p-10 relative text-center transform transition-all animate-modalPop"
        >
            <!-- Close Button (X) -->
            <button
                type="button"
                @click="handleClose"
                class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                title="Close"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Title & Status Header -->
            <div class="mb-5">
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center justify-center gap-2">
                    <span v-if="isListening" class="inline-flex items-center gap-1 text-[#005ca8]">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                        Listening...
                    </span>
                    <span v-else-if="isProcessing" class="text-emerald-600">
                        Got it!
                    </span>
                    <span v-else-if="errorMessage" class="text-slate-800">
                        Voice Search
                    </span>
                    <span v-else class="text-slate-800">
                        Tap Mic to Speak
                    </span>
                </h3>

                <!-- Try Saying Hints (Matching 99acres popup) -->
                <div v-if="!liveTranscript && !errorMessage" class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Try Saying</p>
                    <div class="flex flex-col gap-1.5 items-center">
                        <button
                            v-for="phrase in samplePhrases"
                            :key="phrase"
                            type="button"
                            @click="applyQuery(phrase)"
                            class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-[#005ca8] hover:bg-blue-50/70 px-3.5 py-1.5 rounded-xl transition cursor-pointer border border-transparent hover:border-blue-200"
                        >
                            “{{ phrase }}”
                        </button>
                    </div>
                </div>

                <!-- Live Recognized Transcript -->
                <div v-if="liveTranscript" class="mt-4 min-h-[60px] flex flex-col items-center justify-center">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">We Heard</p>
                    <p class="text-base sm:text-lg font-black text-[#005ca8] bg-blue-50/80 px-4 py-2 rounded-2xl border border-blue-200/80 shadow-xs max-w-md animate-fadeIn">
                        “{{ liveTranscript }}”
                    </p>
                </div>

                <!-- Error or Warning message -->
                <div v-if="errorMessage" class="mt-4 text-xs font-semibold text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200 text-left sm:text-center">
                    <p class="flex items-center justify-center gap-1.5 font-bold mb-1">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Voice Recognition Notice
                    </p>
                    <span>{{ errorMessage }}</span>
                </div>
            </div>

            <!-- Big Circular Microphone Button with Animated Ripple Waves -->
            <div class="my-8 flex justify-center items-center relative">
                <!-- Concentric Sound Ripple Waves (Visible when listening) -->
                <div v-if="isListening" class="absolute w-28 h-28 rounded-full bg-blue-400/20 animate-ping pointer-events-none"></div>
                <div v-if="isListening" class="absolute w-36 h-36 rounded-full border-2 border-blue-400/30 animate-pulse pointer-events-none"></div>
                <div v-if="isListening" class="absolute w-44 h-44 rounded-full border border-blue-300/20 pointer-events-none"></div>

                <!-- Main Microphone Circle -->
                <button
                    type="button"
                    @click="toggleListening"
                    class="w-20 h-20 sm:w-22 sm:h-22 rounded-full flex items-center justify-center text-white shadow-xl hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer relative z-10"
                    :class="isListening ? 'bg-[#005ca8] ring-4 ring-blue-300/60' : 'bg-[#005ca8] hover:bg-[#004e8f]'"
                    :title="isListening ? 'Tap to stop' : 'Tap to speak'"
                >
                    <!-- Mic Icon -->
                    <svg class="w-8 h-8 sm:w-9 sm:h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>
            </div>

            <!-- Footer Action & Status helper -->
            <div class="mt-4 flex flex-col items-center gap-2">
                <p class="text-xs text-slate-500 font-medium">
                    <span v-if="isListening">Speak now — we're listening to your property query</span>
                    <span v-else-if="isProcessing">Searching properties matching your voice...</span>
                    <span v-else>Tap the blue microphone to start voice search</span>
                </p>

                <!-- Quick manual search fallback option -->
                <button
                    v-if="errorMessage"
                    type="button"
                    @click="handleClose"
                    class="mt-2 text-xs font-bold text-[#005ca8] hover:underline"
                >
                    Or type in the search bar instead &rarr;
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue';
import { useVoiceSearch } from '../composables/useVoiceSearch';

const { isVoiceModalOpen, voiceSearchContext, closeVoiceModal, deliverResult } = useVoiceSearch();

const isListening = ref(false);
const isProcessing = ref(false);
const liveTranscript = ref('');
const errorMessage = ref('');
let recognitionInstance = null;

const samplePhrases = ref([
    'Find 2BHK flats in Sector 100 Noida',
    '3BHK Villas near me',
    'Flats in SG Highway Ahmedabad',
    'Ready to move 2 BHK in Whitefield Bangalore',
]);

const initSpeechRecognition = () => {
    errorMessage.value = '';
    liveTranscript.value = '';
    isProcessing.value = false;

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        errorMessage.value = 'Speech recognition is not supported in this browser. Please use Chrome, Safari or Edge, or select one of the suggested queries above.';
        isListening.value = false;
        return;
    }

    try {
        if (recognitionInstance) {
            recognitionInstance.abort();
        }

        const recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.maxAlternatives = 1;
        // en-IN recognizes Indian accents and real estate vocabulary (BHK, Crores, Localities) accurately
        recognition.lang = 'en-IN';

        recognition.onstart = () => {
            isListening.value = true;
            errorMessage.value = '';
        };

        recognition.onresult = (event) => {
            let finalTranscript = '';
            let interim = '';

            for (let i = event.resultIndex; i < event.results.length; ++i) {
                const transcriptPiece = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcriptPiece;
                } else {
                    interim += transcriptPiece;
                }
            }

            liveTranscript.value = finalTranscript || interim;

            if (finalTranscript) {
                applyFinalResult(finalTranscript);
            }
        };

        recognition.onerror = (event) => {
            console.warn('Speech Recognition Event Error:', event.error);
            isListening.value = false;

            if (event.error === 'not-allowed') {
                errorMessage.value = 'Microphone permission was denied. Please allow microphone access in your browser address bar.';
            } else if (event.error === 'no-speech') {
                errorMessage.value = 'We didn\'t hear anything. Tap the microphone and try speaking again.';
            } else if (event.error === 'network') {
                errorMessage.value = 'Network connection issue with speech recognition service.';
            } else {
                errorMessage.value = `Voice error: ${event.error}. You can also pick a sample query above.`;
            }
        };

        recognition.onend = () => {
            isListening.value = false;
        };

        recognitionInstance = recognition;
        recognition.start();
    } catch (err) {
        console.error('Failed to start speech recognition:', err);
        isListening.value = false;
        errorMessage.value = 'Could not access the microphone. Please tap the mic or choose a suggestion.';
    }
};

const stopSpeechRecognition = () => {
    if (recognitionInstance) {
        try {
            recognitionInstance.abort();
        } catch {
            // ignore
        }
        recognitionInstance = null;
    }
    isListening.value = false;
};

const toggleListening = () => {
    if (isListening.value) {
        stopSpeechRecognition();
    } else {
        initSpeechRecognition();
    }
};

const applyFinalResult = (transcript) => {
    const cleaned = transcript.trim();
    if (!cleaned) return;

    stopSpeechRecognition();
    isProcessing.value = true;

    // Small delay so user sees what was recognized before modal closes and searches
    setTimeout(() => {
        deliverResult(cleaned);
        isProcessing.value = false;
        liveTranscript.value = '';
    }, 600);
};

const applyQuery = (phrase) => {
    stopSpeechRecognition();
    liveTranscript.value = phrase;
    isProcessing.value = true;
    setTimeout(() => {
        deliverResult(phrase);
        isProcessing.value = false;
        liveTranscript.value = '';
    }, 300);
};

const handleClose = () => {
    stopSpeechRecognition();
    liveTranscript.value = '';
    errorMessage.value = '';
    closeVoiceModal();
};

// Auto-start listening whenever modal opens
watch(
    () => isVoiceModalOpen.value,
    (newVal) => {
        if (newVal) {
            // Contextual sample phrases based on selected city or type
            const city = voiceSearchContext.value?.city || 'Ahmedabad';
            samplePhrases.value = [
                `3 BHK in ${city}`,
                `3BHK Flat in SG Highway ${city}`,
                `Ready to move 2 BHK in ${city}`,
                `3 BHK Villas in ${city}`,
            ];
            initSpeechRecognition();
        } else {
            stopSpeechRecognition();
        }
    }
);

onUnmounted(() => {
    stopSpeechRecognition();
});
</script>

<style scoped>
@keyframes modalPop {
    0% {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-modalPop {
    animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fadeIn {
    animation: fadeIn 0.18s ease-out forwards;
}
</style>
