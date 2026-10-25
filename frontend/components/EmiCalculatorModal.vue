<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div
            @click="closeModal"
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-300"
        ></div>

        <!-- Modal Content Container -->
        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
            <div
                @click.stop
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-3xl border border-slate-100 my-6 flex flex-col max-h-[92vh]"
            >
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-600/10 text-blue-600 flex items-center justify-center font-black text-lg shadow-2xs">
                            <Landmark class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Home Loan EMI Calculator</h3>
                                <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Instant Estimate
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 truncate max-w-md">
                                <span v-if="propertyTitle">{{ propertyTitle }}</span>
                                <span v-else>Plan your property purchase with accurate monthly installments</span>
                            </p>
                        </div>
                    </div>

                    <button
                        @click="closeModal"
                        class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition cursor-pointer"
                        title="Close"
                    >
                        <X class="w-4 h-4" :stroke-width="2.5" />
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1 text-slate-800">

                    <!-- Top Result Card (Dynamic EMI Banner) -->
                    <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center relative z-10">
                            <!-- Left: Monthly EMI figure -->
                            <div class="md:col-span-7 space-y-2">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/10 text-blue-200 text-[11px] font-bold backdrop-blur-xs">
                                    <span>Equated Monthly Installment</span>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white">
                                        {{ formatCurrency(calculatedEmi) }}
                                    </span>
                                    <span class="text-sm font-semibold text-slate-300">/ month</span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-300 leading-relaxed">
                                    Recommended gross household income required: <strong class="text-amber-300 font-bold">{{ formatCurrency(recommendedIncome) }}/mo</strong> (based on 50% FOIR limit).
                                </p>
                            </div>

                            <!-- Right: Key Numbers Summary Box -->
                            <div class="md:col-span-5 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 space-y-2 text-xs">
                                <div class="flex items-center justify-between pb-1.5 border-b border-white/10">
                                    <span class="text-slate-300 font-medium">Principal Loan</span>
                                    <span class="font-bold text-white">{{ formatCurrency(loanAmount) }}</span>
                                </div>
                                <div class="flex items-center justify-between pb-1.5 border-b border-white/10">
                                    <span class="text-slate-300 font-medium">Total Interest</span>
                                    <span class="font-bold text-amber-300">{{ formatCurrency(totalInterestPayable) }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-0.5">
                                    <span class="text-slate-200 font-bold">Total Amount Payable</span>
                                    <span class="font-black text-emerald-400 text-sm">{{ formatCurrency(totalAmountPayable) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar of Principal vs Interest -->
                        <div class="mt-5 space-y-1.5">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="flex items-center gap-1.5 text-blue-300">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                                    Principal Loan: {{ principalSharePercent }}%
                                </span>
                                <span class="flex items-center gap-1.5 text-amber-300">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                    Total Interest: {{ interestSharePercent }}%
                                </span>
                            </div>
                            <div class="w-full h-3 rounded-full bg-slate-800/80 overflow-hidden flex shadow-inner">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-300" :style="{ width: principalSharePercent + '%' }"></div>
                                <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 transition-all duration-300" :style="{ width: interestSharePercent + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Parameter Controls (Standard Inputs & Sliders) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- 1. Property Price / Total Value -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700">1. Property Cost</label>
                                <span class="text-xs font-extrabold text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded-md font-mono">
                                    {{ formatIndianCompact(propertyPriceVal) }}
                                </span>
                            </div>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input
                                    type="number"
                                    min="100000"
                                    max="200000000"
                                    step="50000"
                                    v-model.number="propertyPriceVal"
                                    @input="onPropertyPriceChange"
                                    class="w-full bg-white border border-slate-300 rounded-xl pl-7 pr-3 py-2 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 shadow-2xs font-mono"
                                />
                            </div>

                            <input
                                type="range"
                                min="500000"
                                max="50000000"
                                step="100000"
                                v-model.number="propertyPriceVal"
                                @input="onPropertyPriceChange"
                                class="w-full accent-blue-600 cursor-pointer"
                            />
                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold">
                                <span>₹ 5 Lakh</span>
                                <span>₹ 2.5 Cr</span>
                                <span>₹ 5 Cr</span>
                            </div>
                        </div>

                        <!-- 2. Down Payment -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700">2. Down Payment</label>
                                <span class="text-xs font-extrabold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md font-mono">
                                    {{ downPaymentPercent }}% ({{ formatIndianCompact(downPaymentAmount) }})
                                </span>
                            </div>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input
                                    type="number"
                                    min="0"
                                    :max="propertyPriceVal"
                                    step="25000"
                                    v-model.number="downPaymentAmount"
                                    @input="onDownPaymentAmountChange"
                                    class="w-full bg-white border border-slate-300 rounded-xl pl-7 pr-3 py-2 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 shadow-2xs font-mono"
                                />
                            </div>

                            <!-- Quick Percentage Chips -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button
                                    v-for="pct in [10, 20, 25, 30, 40, 50]"
                                    :key="pct"
                                    type="button"
                                    @click="setDownPaymentPercent(pct)"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
                                    :class="downPaymentPercent === pct ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                                >
                                    {{ pct }}%
                                </button>
                            </div>
                        </div>

                        <!-- 3. Loan Tenure -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700">3. Loan Tenure</label>
                                <span class="text-xs font-extrabold text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded-md font-mono">
                                    {{ tenureYears }} Years ({{ tenureYears * 12 }} Months)
                                </span>
                            </div>

                            <input
                                type="range"
                                min="1"
                                max="30"
                                step="1"
                                v-model.number="tenureYears"
                                class="w-full accent-blue-600 cursor-pointer"
                            />

                            <!-- Quick Tenure Chips -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button
                                    v-for="yr in [5, 10, 15, 20, 25, 30]"
                                    :key="yr"
                                    type="button"
                                    @click="tenureYears = yr"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
                                    :class="tenureYears === yr ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                                >
                                    {{ yr }} Yrs
                                </button>
                            </div>
                        </div>

                        <!-- 4. Interest Rate -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700">4. Interest Rate (% P.A.)</label>
                                <div class="flex items-center gap-1">
                                    <input
                                        type="number"
                                        min="5"
                                        max="20"
                                        step="0.05"
                                        v-model.number="interestRate"
                                        class="w-16 bg-white border border-slate-300 rounded-lg px-2 py-0.5 text-xs font-black text-right outline-none focus:border-blue-500 font-mono"
                                    />
                                    <span class="text-xs font-bold text-slate-600">%</span>
                                </div>
                            </div>

                            <input
                                type="range"
                                min="6.5"
                                max="15.0"
                                step="0.05"
                                v-model.number="interestRate"
                                class="w-full accent-blue-600 cursor-pointer"
                            />

                            <!-- Top Indian Bank Benchmark Presets -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Popular Bank Home Loan Rates:</span>
                                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                                    <button
                                        v-for="bank in bankPresets"
                                        :key="bank.name"
                                        type="button"
                                        @click="interestRate = bank.rate"
                                        class="px-2 py-0.5 rounded-md text-[11px] font-semibold whitespace-nowrap transition cursor-pointer border flex items-center gap-1"
                                        :class="interestRate === bank.rate ? 'border-blue-600 bg-blue-50 text-blue-800 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-100'"
                                    >
                                        <span>{{ bank.name }}</span>
                                        <span class="font-mono text-[10px] font-bold text-blue-600">{{ bank.rate }}%</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Amortization Schedule Accordion Toggle -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <button
                            type="button"
                            @click="showSchedule = !showSchedule"
                            class="w-full p-4 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-xs font-bold text-slate-800 transition cursor-pointer"
                        >
                            <div class="flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-blue-600 shrink-0" />
                                <span>Year-by-Year Loan Repayment Schedule (Amortization Table)</span>
                            </div>
                            <div class="flex items-center gap-1 text-blue-600">
                                <span>{{ showSchedule ? 'Hide Table' : 'View Schedule' }}</span>
                                <ChevronDown class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showSchedule }" />
                            </div>
                        </button>

                        <div v-if="showSchedule" class="p-4 bg-white border-t border-slate-100 overflow-x-auto max-h-72">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        <th class="py-2 px-2">Year</th>
                                        <th class="py-2 px-2">Opening Balance</th>
                                        <th class="py-2 px-2">EMI Paid (Annual)</th>
                                        <th class="py-2 px-2">Principal Repaid</th>
                                        <th class="py-2 px-2">Interest Charged</th>
                                        <th class="py-2 px-2 text-right">Closing Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                    <tr v-for="row in amortizationSchedule" :key="row.year" class="hover:bg-slate-50">
                                        <td class="py-2 px-2 font-sans font-bold text-slate-800">Yr {{ row.year }}</td>
                                        <td class="py-2 px-2 text-slate-600">{{ formatCurrency(row.openingBalance) }}</td>
                                        <td class="py-2 px-2 text-slate-800 font-semibold">{{ formatCurrency(row.annualEmi) }}</td>
                                        <td class="py-2 px-2 text-blue-600 font-semibold">{{ formatCurrency(row.principalPaid) }}</td>
                                        <td class="py-2 px-2 text-amber-600 font-semibold">{{ formatCurrency(row.interestPaid) }}</td>
                                        <td class="py-2 px-2 text-right font-bold text-slate-900">{{ formatCurrency(row.closingBalance) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Free Home Loan Assistance Lead Banner -->
                    <div class="bg-emerald-50/60 border border-emerald-200/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <Handshake class="w-4 h-4 text-emerald-700 shrink-0" />
                                <h4 class="text-xs sm:text-sm font-black text-emerald-950">Need Assistance with Pre-Approved Home Loans?</h4>
                            </div>
                            <p class="text-[11px] sm:text-xs text-emerald-800 leading-relaxed">
                                Get instant paperless sanction from SBI, HDFC, ICICI, Bank of Baroda with zero processing fee and doorstep document pickup.
                            </p>
                        </div>
                        <div class="shrink-0 w-full sm:w-auto">
                            <button
                                type="button"
                                @click="handleLoanAssistanceClick"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer flex items-center justify-center gap-1.5"
                            >
                                <span v-if="applied">✓ Request Received!</span>
                                <span v-else>Get Free Loan Quote &rarr;</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
                    <button
                        type="button"
                        @click="resetToStandardDefaults"
                        class="text-xs font-bold text-slate-500 hover:text-slate-800 transition cursor-pointer"
                    >
                        ↺ Reset to 20% Down / 20 Yrs
                    </button>
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition cursor-pointer"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {
    X,
    ChevronDown,
    Landmark,
    Calendar,
    Handshake,
} from 'lucide-vue-next';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    propertyPrice: {
        type: [Number, String],
        default: 0,
    },
    propertyTitle: {
        type: String,
        default: '',
    },
    propertyCity: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const bankPresets = [
    { name: 'SBI', rate: 8.50 },
    { name: 'HDFC', rate: 8.70 },
    { name: 'ICICI', rate: 8.75 },
    { name: 'Axis', rate: 8.75 },
    { name: 'BOB', rate: 8.40 },
    { name: 'Kotak', rate: 8.70 },
];

const propertyPriceVal = ref(5000000);
const downPaymentPercent = ref(20);
const downPaymentAmount = ref(1000000);
const tenureYears = ref(20);
const interestRate = ref(8.5);
const showSchedule = ref(false);
const applied = ref(false);

// Initialize when modal opens or price changes
watch(
    () => [props.isOpen, props.propertyPrice],
    ([open, newPrice]) => {
        if (open) {
            const num = Number(newPrice);
            if (num && num > 0) {
                propertyPriceVal.value = num;
            } else if (!propertyPriceVal.value || propertyPriceVal.value <= 0) {
                propertyPriceVal.value = 5000000;
            }
            downPaymentAmount.value = Math.round((propertyPriceVal.value * downPaymentPercent.value) / 100);
        }
    },
    { immediate: true }
);

const onPropertyPriceChange = () => {
    if (!propertyPriceVal.value || propertyPriceVal.value < 0) propertyPriceVal.value = 0;
    downPaymentAmount.value = Math.round((propertyPriceVal.value * downPaymentPercent.value) / 100);
};

const setDownPaymentPercent = (pct) => {
    downPaymentPercent.value = pct;
    downPaymentAmount.value = Math.round((propertyPriceVal.value * pct) / 100);
};

const onDownPaymentAmountChange = () => {
    if (downPaymentAmount.value > propertyPriceVal.value) {
        downPaymentAmount.value = propertyPriceVal.value;
    }
    if (propertyPriceVal.value > 0) {
        downPaymentPercent.value = Math.round((downPaymentAmount.value / propertyPriceVal.value) * 100);
    }
};

const loanAmount = computed(() => {
    return Math.max(0, propertyPriceVal.value - downPaymentAmount.value);
});

// Standard EMI calculation formula: E = P * r * (1+r)^n / ((1+r)^n - 1)
const calculatedEmi = computed(() => {
    const P = loanAmount.value;
    const r = (interestRate.value || 0) / (12 * 100);
    const n = (tenureYears.value || 0) * 12;

    if (P <= 0 || r <= 0 || n <= 0) return 0;

    const factor = Math.pow(1 + r, n);
    const emi = (P * r * factor) / (factor - 1);
    return Math.round(emi);
});

const totalAmountPayable = computed(() => {
    const months = (tenureYears.value || 0) * 12;
    return Math.round(calculatedEmi.value * months);
});

const totalInterestPayable = computed(() => {
    return Math.max(0, totalAmountPayable.value - loanAmount.value);
});

const principalSharePercent = computed(() => {
    if (totalAmountPayable.value <= 0) return 100;
    return Math.min(100, Math.max(0, Math.round((loanAmount.value / totalAmountPayable.value) * 100)));
});

const interestSharePercent = computed(() => {
    return Math.max(0, 100 - principalSharePercent.value);
});

// Income recommendation based on standard Indian bank FOIR (Fixed Obligation to Income Ratio) of 50%
const recommendedIncome = computed(() => {
    return calculatedEmi.value * 2;
});

// Year-by-year Amortization schedule
const amortizationSchedule = computed(() => {
    const P_initial = loanAmount.value;
    const annualRate = (interestRate.value || 0) / 100;
    const monthlyRate = annualRate / 12;
    const years = tenureYears.value;
    const monthlyEmi = calculatedEmi.value;

    if (P_initial <= 0 || monthlyEmi <= 0 || years <= 0) return [];

    const schedule = [];
    let balance = P_initial;

    for (let yr = 1; yr <= years; yr++) {
        const opening = balance;
        let yearlyInterest = 0;
        let yearlyPrincipal = 0;

        for (let m = 1; m <= 12; m++) {
            if (balance <= 0) break;
            const interestForMonth = balance * monthlyRate;
            const principalForMonth = Math.min(balance, monthlyEmi - interestForMonth);
            yearlyInterest += interestForMonth;
            yearlyPrincipal += principalForMonth;
            balance = Math.max(0, balance - principalForMonth);
        }

        schedule.push({
            year: yr,
            openingBalance: Math.round(opening),
            annualEmi: Math.round(monthlyEmi * 12),
            principalPaid: Math.round(yearlyPrincipal),
            interestPaid: Math.round(yearlyInterest),
            closingBalance: Math.round(balance),
        });

        if (balance <= 0) break;
    }

    return schedule;
});

const resetToStandardDefaults = () => {
    downPaymentPercent.value = 20;
    downPaymentAmount.value = Math.round((propertyPriceVal.value * 20) / 100);
    tenureYears.value = 20;
    interestRate.value = 8.5;
};

const handleLoanAssistanceClick = () => {
    applied.value = true;
    setTimeout(() => {
        applied.value = false;
    }, 4000);
};

const formatCurrency = (val) => {
    if (!val || val <= 0) return '₹ 0';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(val);
};

const formatIndianCompact = (val) => {
    if (!val || val <= 0) return '₹ 0';
    const num = Number(val);
    if (num >= 10000000) {
        const cr = (num / 10000000).toFixed(2);
        return `₹ ${cr.replace(/\.00$/, '')} Cr`;
    }
    if (num >= 100000) {
        const lk = (num / 100000).toFixed(2);
        return `₹ ${lk.replace(/\.00$/, '')} Lakh`;
    }
    return `₹ ${num.toLocaleString('en-IN')}`;
};

const closeModal = () => {
    emit('close');
};
</script>

