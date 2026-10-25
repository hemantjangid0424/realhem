import { abortInFlightApiRequests } from '~/composables/useApi';

export default defineNuxtPlugin((nuxtApp) => {
  const router = useRouter();

  // Whenever user navigates to a new page or query, immediately cancel all active in-flight AJAX requests
  router.beforeEach((to, from) => {
    if (to.fullPath !== from.fullPath) {
      abortInFlightApiRequests('route_change');
    }
  });
});

