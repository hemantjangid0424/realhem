import { ref, computed } from 'vue';

const defaultBranding = {
    company_name: 'RealHem',
    company_short_name: 'RH',
    company_tagline: '99acres Real Estate Architecture & Portal',
    company_sub_tagline: 'Buy, Rent, PG, Commercial & Builder Projects',
    company_logo_url: '',
    company_phone: '1800-41-9999',
    company_email: 'contact@realhem.com',
    brand_primary_color: '#005ca8',
    brand_secondary_color: '#1e40af',
    brand_accent_color: '#ff6b35',
    theme_font_family: 'Instrument Sans',
    theme_border_radius: '16px',
    header_style: 'clean_white',
    hero_style: 'midnight_blue',
    banner_enabled: 'true',
    banner_text: "India's Biggest Property Fest 2026: Zero Brokerage on 10,000+ Verified Homes",
    banner_badge: 'MEGA LAUNCH',
    banner_link: '/listings?badge=fest',
    footer_style: 'dark_slate',
    footer_about: 'RealHem is India\'s premier full-stack real estate discovery and property transaction portal, connecting verified property owners, top developers, and licensed brokers with over 5 Lakh+ buyers and tenants every week.',
    copyright_text: '© 2026 RealHem India Pvt. Ltd. All rights reserved.',
};

const getCachedBranding = () => {
    try {
        const stored = localStorage.getItem('realhem_branding');
        return stored ? { ...defaultBranding, ...JSON.parse(stored) } : { ...defaultBranding };
    } catch {
        return { ...defaultBranding };
    }
};

const companySettings = ref(getCachedBranding());

/**
 * Dynamically apply theme CSS variables and Google Fonts to the DOM
 */
export function applyThemeToDom(s) {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    const primary = s.brand_primary_color || '#005ca8';
    const secondary = s.brand_secondary_color || '#1e40af';
    const accent = s.brand_accent_color || '#ff6b35';
    const radius = s.theme_border_radius || '16px';
    const font = s.theme_font_family || 'Instrument Sans';

    root.style.setProperty('--brand-primary', primary);
    root.style.setProperty('--brand-secondary', secondary);
    root.style.setProperty('--brand-accent', accent);
    root.style.setProperty('--theme-radius', radius);
    root.style.setProperty('--theme-font', `'${font}', -apple-system, BlinkMacSystemFont, sans-serif`);

    document.body.style.fontFamily = `var(--theme-font)`;

    // Dynamically inject Google Font if not Instrument Sans
    if (font && font !== 'Instrument Sans') {
        const fontName = font.replace(/\s+/g, '+');
        const linkId = 'theme-google-font';
        let link = document.getElementById(linkId);
        if (!link) {
            link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }
        link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@400;500;600;700;800;900&display=swap`;
    }
}

// Initial theme application
if (typeof window !== 'undefined') {
    applyThemeToDom(companySettings.value);

    // Global listener for settings updates dispatched by Admin SPA or same tab
    window.addEventListener('realhem-branding-updated', (e) => {
        if (e.detail) {
            companySettings.value = { ...companySettings.value, ...e.detail };
            applyThemeToDom(companySettings.value);
        }
    });

    window.addEventListener('storage', (e) => {
        if (e.key === 'realhem_branding' && e.newValue) {
            try {
                companySettings.value = { ...defaultBranding, ...JSON.parse(e.newValue) };
                applyThemeToDom(companySettings.value);
            } catch {
                // ignore
            }
        }
    });
}

export function useCompanyBranding() {
    const companyName = computed(() => companySettings.value.company_name || 'RealHem');
    const companyShortName = computed(() => companySettings.value.company_short_name || 'RH');
    const companyTagline = computed(() => companySettings.value.company_tagline || 'Real Estate Portal');
    const companySubTagline = computed(() => companySettings.value.company_sub_tagline || 'Buy, Rent, PG, Commercial & Builder Projects');
    const companyLogoUrl = computed(() => companySettings.value.company_logo_url || '');
    const brandPrimaryColor = computed(() => companySettings.value.brand_primary_color || '#005ca8');
    const brandSecondaryColor = computed(() => companySettings.value.brand_secondary_color || '#1e40af');
    const brandAccentColor = computed(() => companySettings.value.brand_accent_color || '#ff6b35');
    const themeFontFamily = computed(() => companySettings.value.theme_font_family || 'Instrument Sans');
    const themeBorderRadius = computed(() => companySettings.value.theme_border_radius || '16px');
    const headerStyle = computed(() => companySettings.value.header_style || 'clean_white');
    const heroStyle = computed(() => companySettings.value.hero_style || 'midnight_blue');
    const bannerEnabled = computed(() => companySettings.value.banner_enabled === 'true' || companySettings.value.banner_enabled === true);
    const bannerText = computed(() => companySettings.value.banner_text || "India's Biggest Property Fest 2026: Zero Brokerage on 10,000+ Verified Homes");
    const bannerBadge = computed(() => companySettings.value.banner_badge || 'MEGA LAUNCH');
    const bannerLink = computed(() => companySettings.value.banner_link || '/listings?badge=fest');
    const footerStyle = computed(() => companySettings.value.footer_style || 'dark_slate');
    const companyPhone = computed(() => companySettings.value.company_phone || '1800-41-9999');
    const companyEmail = computed(() => companySettings.value.company_email || 'contact@realhem.com');
    const footerAbout = computed(() => companySettings.value.footer_about || defaultBranding.footer_about);
    const copyrightText = computed(() => companySettings.value.copyright_text || defaultBranding.copyright_text);

    // Compute dynamic Hero Gradient based on Primary / Secondary / Accent
    const heroGradient = computed(() => {
        const p = brandPrimaryColor.value;
        const s = brandSecondaryColor.value;
        if (heroStyle.value === 'coral_sunset') {
            return 'linear-gradient(135deg, #ea580c 0%, #f97316 50%, #7c2d12 100%)';
        }
        if (heroStyle.value === 'emerald_luxury') {
            return 'linear-gradient(135deg, #064e3b 0%, #059669 50%, #022c22 100%)';
        }
        if (heroStyle.value === 'royal_indigo') {
            return 'linear-gradient(135deg, #312e81 0%, #4338ca 50%, #1e1b4b 100%)';
        }
        if (heroStyle.value === 'minimal_white') {
            return 'linear-gradient(180deg, #f8fafc 0%, #ffffff 100%)';
        }
        // default / matching brand:
        return `linear-gradient(135deg, #0f172a 0%, ${p} 50%, ${s} 100%)`;
    });

    const loadBrandingSettings = async () => {
        try {
            const res = await fetch('/api/settings');
            const data = await res.json();
            if (data.settings) {
                companySettings.value = { ...defaultBranding, ...data.settings };
                localStorage.setItem('realhem_branding', JSON.stringify(companySettings.value));
                applyThemeToDom(companySettings.value);
            }
        } catch (err) {
            console.warn('Failed to load dynamic branding settings, using cached/defaults:', err);
        }
    };

    return {
        companySettings,
        companyName,
        companyShortName,
        companyTagline,
        companySubTagline,
        companyLogoUrl,
        brandPrimaryColor,
        brandSecondaryColor,
        brandAccentColor,
        themeFontFamily,
        themeBorderRadius,
        headerStyle,
        heroStyle,
        heroGradient,
        bannerEnabled,
        bannerText,
        bannerBadge,
        bannerLink,
        footerStyle,
        companyPhone,
        companyEmail,
        footerAbout,
        copyrightText,
        loadBrandingSettings,
        applyThemeToDom,
    };
}
