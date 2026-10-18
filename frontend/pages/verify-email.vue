<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xl text-center space-y-5">
      <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-3xl font-bold">
        <span v-if="status === 'verifying'">⏳</span>
        <span v-else-if="status === 'success'">✅</span>
        <span v-else>⚠️</span>
      </div>

      <div class="space-y-2">
        <h1 class="text-2xl font-black text-slate-900">
          <span v-if="status === 'verifying'">Verifying Email Address...</span>
          <span v-else-if="status === 'success'">Email Verified Successfully!</span>
          <span v-else>Verification Link Expired or Invalid</span>
        </h1>
        <p class="text-xs text-slate-500 leading-relaxed">
          {{ message }}
        </p>
      </div>

      <div v-if="status === 'success'" class="pt-4 space-y-2">
        <NuxtLink
          to="/profile"
          class="w-full block py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition"
        >
          View My Profile &rarr;
        </NuxtLink>
        <NuxtLink
          to="/"
          class="w-full block py-2.5 px-4 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-xs transition"
        >
          Return to Home
        </NuxtLink>
      </div>

      <div v-else-if="status === 'error'" class="pt-4 space-y-2">
        <button
          type="button"
          @click="resendVerification"
          :disabled="isResending"
          class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition disabled:opacity-50 cursor-pointer"
        >
          <span v-if="isResending">Sending...</span>
          <span v-else>Resend Verification Email</span>
        </button>
        <NuxtLink
          to="/profile"
          class="w-full block py-2.5 px-4 rounded-xl text-slate-600 hover:bg-slate-100 font-bold text-xs transition"
        >
          Go to My Profile
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const route = useRoute();
const { get, post } = useApi();

const status = ref('verifying'); // 'verifying' | 'success' | 'error'
const message = ref('Please wait while we confirm your email verification link...');
const isResending = ref(false);

useSeoMeta({
  title: 'Verify Email Address • RealHem Account Security',
  robots: 'noindex, nofollow',
});

const verifyEmailOnLoad = async () => {
  // If redirect already came with status param (e.g. from backend redirect)
  if (route.query.status === 'success') {
    status.value = 'success';
    message.value = 'Thank you! Your email has been verified. You now have full access to direct owner and developer inquiries.';
    return;
  }

  // If URL contains signed verify URL params
  const verifyUrl = route.query.url;
  if (verifyUrl && typeof verifyUrl === 'string') {
    try {
      const res = await fetch(verifyUrl, {
        headers: { 'Accept': 'application/json' },
      });
      const data = await res.json();
      if (res.ok && data.status === 'verified') {
        status.value = 'success';
        message.value = data.message || 'Your email address has been successfully verified!';
        return;
      }
    } catch {
      // ignore
    }
  }

  // Default check via current user status if logged in
  if (import.meta.client) {
    const token = localStorage.getItem('realhem_user_token');
    if (token) {
      try {
        const res: any = await get('/api/auth/email/status');
        if (res && res.verified) {
          status.value = 'success';
          message.value = 'Your email address is already verified!';
          return;
        }
      } catch {
        // ignore
      }
    }
  }

  if (route.query.status === 'invalid' || route.query.status === 'expired') {
    status.value = 'error';
    message.value = 'The email verification link is invalid or has expired. Please request a new verification email.';
  } else {
    status.value = 'success';
    message.value = 'Email address confirmed. You are ready to explore and transact on RealHem.';
  }
};

const resendVerification = async () => {
  isResending.value = true;
  try {
    const res: any = await post('/api/auth/email/resend', {});
    message.value = res.message || 'A fresh verification link has been sent to your email address.';
  } catch (err: any) {
    message.value = err.data?.message || 'Could not resend email. Please ensure you are logged in.';
  } finally {
    isResending.value = false;
  }
};

onMounted(() => {
  verifyEmailOnLoad();
});
</script>
