<template>
  <div>
    <!-- Global SVG Gradient Definitions for Shortlist Heart Icon -->
    <svg aria-hidden="true" class="absolute w-0 h-0 overflow-hidden pointer-events-none" style="position: absolute; width: 0; height: 0;">
      <defs>
        <linearGradient id="realhem-heart-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#ff3366" />
          <stop offset="45%" stop-color="#f43f5e" />
          <stop offset="100%" stop-color="#be123c" />
        </linearGradient>
      </defs>
    </svg>

    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const {
  companyName,
  companyTagline,
  companyFaviconUrl,
  brandPrimaryColor,
  brandSecondaryColor,
  brandAccentColor,
  themeFontFamily,
  themeBorderRadius,
} = useCompanyBranding();

const fontName = computed(() => (themeFontFamily.value || 'Instrument Sans').replace(/\s+/g, '+'));

// Inject theme CSS variables and fonts directly into initial SSR <head>
useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk
      ? `${titleChunk} • ${companyName.value}`
      : `${companyName.value} • ${companyTagline.value}`;
  },
  style: [
    {
      id: 'realhem-theme-vars',
      innerHTML: computed(() => `
        :root {
          --brand-primary: ${brandPrimaryColor.value};
          --brand-secondary: ${brandSecondaryColor.value};
          --brand-accent: ${brandAccentColor.value};
          --theme-radius: ${themeBorderRadius.value};
          --theme-font: '${themeFontFamily.value}', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
          font-family: var(--theme-font);
        }
      `),
    },
  ],
  link: computed(() => [
    {
      rel: 'icon',
      href: companyFaviconUrl.value || '/favicon.ico',
    },
    ...(themeFontFamily.value && themeFontFamily.value !== 'Instrument Sans'
      ? [
          {
            rel: 'stylesheet',
            href: `https://fonts.googleapis.com/css2?family=${fontName.value}:wght@400;500;600;700;800;900&display=swap`,
          },
        ]
      : []),
  ]),
});
</script>
