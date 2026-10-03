import { ref } from 'vue';

const isVoiceModalOpen = ref(false);
const voiceCallback = ref(null);
const voiceSearchContext = ref({
    type: 'buy',
    city: 'Delhi NCR',
});

export function useVoiceSearch() {
    /**
     * Open voice search popup modal.
     * @param {Function} onResult Callback with final recognized text string
     * @param {Object} context Optional context (e.g. current property type, city)
     */
    const openVoiceModal = (onResult, context = {}) => {
        voiceCallback.value = onResult;
        voiceSearchContext.value = {
            type: context.type || 'buy',
            city: context.city || 'Delhi NCR',
        };
        isVoiceModalOpen.value = true;
    };

    const closeVoiceModal = () => {
        isVoiceModalOpen.value = false;
        voiceCallback.value = null;
    };

    const deliverResult = (transcript) => {
        if (voiceCallback.value && typeof voiceCallback.value === 'function') {
            voiceCallback.value(transcript);
        }
        closeVoiceModal();
    };

    return {
        isVoiceModalOpen,
        voiceSearchContext,
        openVoiceModal,
        closeVoiceModal,
        deliverResult,
    };
}
