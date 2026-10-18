export default defineEventHandler(async (event) => {
  setHeader(event, 'content-type', 'application/xml; charset=utf-8');

  const config = useRuntimeConfig();
  const apiBase = config.public.apiBase || 'http://localhost:8000';

  const staticRoutes = [
    '',
    '/listings',
    '/brokers',
    '/about-us',
    '/contact-us',
    '/careers',
    '/testimonials',
    '/safety-guide',
    '/terms',
    '/privacy',
    '/rera-disclaimer',
    '/refund-policy',
  ];

  let properties: any[] = [];
  try {
    const data: any = await $fetch(`${apiBase}/api/properties`);
    properties = data?.data || data || [];
  } catch (err) {
    // fallback
  }

  const currentDate = new Date().toISOString();

  let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`;

  // Static routes
  for (const route of staticRoutes) {
    xml += `
  <url>
    <loc>https://realhem.com${route}</loc>
    <lastmod>${currentDate}</lastmod>
    <changefreq>${route === '' || route === '/listings' ? 'daily' : 'monthly'}</changefreq>
    <priority>${route === '' ? '1.0' : '0.8'}</priority>
  </url>`;
  }

  // Dynamic property detail routes
  if (Array.isArray(properties)) {
    for (const prop of properties) {
      const slugOrId = prop.slug || prop.id;
      const updated = prop.updated_at ? new Date(prop.updated_at).toISOString() : currentDate;
      xml += `
  <url>
    <loc>https://realhem.com/property/${slugOrId}</loc>
    <lastmod>${updated}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>`;
    }
  }

  xml += `
</urlset>`;

  return xml;
});

