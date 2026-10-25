<template>
    <Teleport to="body">
        <transition name="report-modal">
            <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div
                    @click="closeModal"
                    class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-300"
                ></div>

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
                    <div
                        @click.stop
                        class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-slate-100 my-6 flex flex-col max-h-[92vh]"
                    >
                        <!-- Modal Header -->
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-black text-lg shadow-2xs border border-rose-100">
                                    <ShieldAlert class="w-5 h-5 text-rose-600" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Report Property</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Trust &amp; Safety
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 truncate max-w-xs sm:max-w-sm">
                                        Ref #{{ property?.id || 'LISTING' }} · Help keep listings authentic &amp; verified
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="closeModal"
                                class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition cursor-pointer"
                                title="Close"
                            >
                                <X class="w-4 h-4" :stroke-width="2.5" />
                            </button>
                        </div>

                        <!-- Success State Screen -->
                        <div v-if="isSuccess" class="p-8 text-center space-y-5 my-auto">
                            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-sm border border-emerald-100">
                                <CheckCircle2 class="w-9 h-9" />
                            </div>
                            <div class="space-y-2">
                                <h4 class="text-lg font-black text-slate-900">Report Submitted Successfully</h4>
                                <p class="text-xs text-slate-600 max-w-sm mx-auto leading-relaxed">
                                    {{ serverMessage || 'Thank you for reporting this issue. Our trust & safety moderation desk will review this listing shortly and take appropriate action.' }}
                                </p>
                                <div v-if="referenceId" class="inline-block mt-2 px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-mono text-[11px] font-bold">
                                    Inquiry Ref: #{{ referenceId }}
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="closeModal"
                                class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer shadow-md"
                            >
                                Done
                            </button>
                        </div>

                        <!-- Form Workspace -->
                        <form v-else @submit.prevent="submitReport" class="p-6 overflow-y-auto space-y-5 flex-1 text-slate-800">
                            <!-- Property Mini Preview Card -->
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-200 overflow-hidden flex-shrink-0 relative">
                                    <img
                                        v-if="property?.photos && property.photos.length > 0"
                                        :src="property.photos[0]"
                                        :alt="property.title"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                        <Building2 class="w-6 h-6" />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ property?.title || 'Selected Property' }}</div>
                                    <div class="text-[11px] text-slate-500 truncate flex items-center gap-1 mt-0.5">
                                        <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                                        <span>{{ property?.locality || '' }}{{ property?.city ? ', ' + property.city : '' }}</span>
                                        <span v-if="property?.price" class="font-extrabold text-slate-800 ml-1">· {{ property.price }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Alert -->
                            <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center justify-between">
                                <span>{{ errorMessage }}</span>
                                <button type="button" @click="errorMessage = ''" class="text-rose-500 hover:text-rose-800 font-bold ml-2">✕</button>
                            </div>

                            <!-- Reason for Reporting -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 block">
                                    Reason for Reporting <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <button
                                        v-for="r in reportReasons"
                                        :key="r.id"
                                        type="button"
                                        @click="selectedReason = r.label"
                                        class="text-left p-2.5 rounded-xl border text-xs font-medium transition cursor-pointer flex items-center justify-between gap-2"
                                        :class="selectedReason === r.label
                                            ? 'bg-rose-50 border-rose-400 text-rose-700 font-bold shadow-2xs'
                                            : 'bg-slate-50/80 border-slate-200/90 text-slate-600 hover:bg-slate-100'"
                                    >
                                        <span class="truncate">{{ r.label }}</span>
                                        <span v-if="selectedReason === r.label" class="text-rose-600 shrink-0">✓</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Additional Details -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 block">
                                    Additional Details <span class="text-rose-500">*</span>
                                </label>
                                <textarea
                                    v-model="details"
                                    required
                                    rows="3"
                                    placeholder="Please describe what is incorrect or what happened (e.g. owner quoted higher price offline, already rented out, broker demanding commission)..."
                                    class="w-full p-3 rounded-xl border border-slate-200 text-xs font-normal bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 resize-none"
                                ></textarea>
                            </div>

                            <!-- Reporter Contact Details -->
                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Your Contact Information</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-600">Your Name <span class="text-rose-500">*</span></label>
                                        <input
                                            v-model="name"
                                            type="text"
                                            required
                                            placeholder="e.g. Rahul Sharma"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-600">Your Email <span class="text-rose-500">*</span></label>
                                        <input
                                            v-model="email"
                                            type="email"
                                            required
                                            placeholder="rahul@example.com"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-slate-600">Mobile Number (Optional)</label>
                                    <input
                                        v-model="phone"
                                        type="tel"
                                        placeholder="+91 98765 43210"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                                    />
                                </div>
                            </div>

                            <!-- Footer Submit Button -->
                            <div class="pt-3 flex items-center justify-end gap-2 shrink-0">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition cursor-pointer"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting || !details.trim() || !name.trim() || !email.trim()"
                                    class="py-2.5 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isSubmitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <ShieldAlert v-else class="w-3.5 h-3.5" />
                                    <span>{{ isSubmitting ? 'Submitting Report...' : 'Submit Report' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { ShieldAlert, X, CheckCircle2, Building2, MapPin } from 'lucide-vue-next';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    property: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['close', 'reported']);

const reportReasons = [
    { id: 'price', label: 'Fake or Inaccurate Price' },
    { id: 'sold', label: 'Listing Already Sold / Rented' },
    { id: 'broker', label: 'Broker Impersonating Direct Owner' },
    { id: 'photos', label: 'Misleading or Fake Photos' },
    { id: 'fraud', label: 'Suspicious / Advance Money Scam' },
    { id: 'location', label: 'Incorrect Location or Specs' },
    { id: 'unresponsive', label: 'Unresponsive / Refusing Visit' },
    { id: 'other', label: 'Other Policy Violation' },
];

const selectedReason = ref('Fake or Inaccurate Price');
const details = ref('');
const name = ref('');
const email = ref('');
const phone = ref('');
const isSubmitting = ref(false);
const isSuccess = ref(false);
const serverMessage = ref('');
const referenceId = ref<number | null>(null);
const errorMessage = ref('');

const populateUserData = () => {
    if (typeof window === 'undefined') return;
    try {
        const stored = localStorage.getItem('realhem_user');
        if (stored) {
            const user = JSON.parse(stored);
            if (user?.name) name.value = user.name;
            if (user?.email) email.value = user.email;
            if (user?.mobile) phone.value = user.mobile;
        }
    } catch {
        // Ignore JSON parse errors
    }
};

onMounted(() => {
    populateUserData();
});

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            isSuccess.value = false;
            errorMessage.value = '';
            serverMessage.value = '';
            referenceId.value = null;
            details.value = '';
            populateUserData();
        }
    }
);

const closeModal = () => {
    emit('close');
};

const submitReport = async () => {
    if (!props.property?.id && !props.property?.title) {
        errorMessage.value = 'Property reference is missing.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const payload: Record<string, any> = {
            type: 'report_problem',
            name: name.value.trim(),
            email: email.value.trim(),
            phone: phone.value.trim() || null,
            subject: selectedReason.value,
            message: details.value.trim(),
            property_id: props.property?.id ? Number(props.property.id) : null,
            metadata: {
                property_title: props.property?.title || '',
                property_city: props.property?.city || '',
                property_locality: props.property?.locality || '',
                property_price: props.property?.price || '',
                seller_name: props.property?.sellerName || props.property?.user?.name || props.property?.postedBy || '',
                reported_at: new Date().toISOString(),
            },
        };

        const res = await fetch('/api/inquiries', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (res.ok) {
            isSuccess.value = true;
            serverMessage.value = data.message || 'Report submitted successfully.';
            if (data.inquiry?.id) {
                referenceId.value = data.inquiry.id;
            }
            emit('reported', { propertyId: props.property?.id, inquiry: data.inquiry });
        } else {
            errorMessage.value = data.message || 'Could not submit report. Please check your information.';
        }
    } catch (err) {
        errorMessage.value = 'Network error while submitting report. Please check your internet connection.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
.report-modal-enter-active,
.report-modal-leave-active {
    transition: opacity 0.25s ease;
}
.report-modal-enter-from,
.report-modal-leave-to {
    opacity: 0;
}
</style>

