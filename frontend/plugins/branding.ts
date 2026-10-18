import { defaultBranding, applyThemeToDom } from '~/composables/useCompanyBranding';

export default defineNuxtPlugin(async (nuxtApp) => {
  const companySettings = useState<Record<string, any>>('realhem_branding', () => ({ ...defaultBranding }));

  // On server, fetch live database settings before any page/layout renders
  if (import.meta.server) {
    try {
      const config = useRuntimeConfig();
      const apiBase = config.public.apiBase || 'http://localhost:8000';
      const res: any = await $fetch(`${apiBase}/api/settings`);
      if (res && res.settings) {
        companySettings.value = { ...defaultBranding, ...res.settings };
      }
    } catch (err) {
      // Keep defaults if backend unreachable
    }
  }

  // On client, apply DOM styles and keep in sync with live admin settings
  if (import.meta.client) {
    // If not yet loaded on server (e.g. SPA navigation), fetch directly from API
    if (!companySettings.value.brand_primary_color || companySettings.value.brand_primary_color === defaultBranding.brand_primary_color) {
      try {
        const res: any = await $fetch('/api/settings');
        if (res && res.settings) {
          companySettings.value = { ...defaultBranding, ...res.settings };
        }
      } catch {
        // ignore
      }
    }

    applyThemeToDom(companySettings.value);

    // Listen for real-time updates dispatched from Admin Settings tab
    window.addEventListener('realhem-branding-updated', (event: any) => {
      if (event.detail) {
        companySettings.value = { ...defaultBranding, ...event.detail };
        applyThemeToDom(companySettings.value);
      }
    });
  }
});
