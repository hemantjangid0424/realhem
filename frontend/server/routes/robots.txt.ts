export default defineEventHandler((event) => {
  setHeader(event, 'content-type', 'text/plain; charset=utf-8');

  return `User-agent: *
Allow: /
Allow: /listings
Allow: /property/
Allow: /about-us
Allow: /contact-us
Allow: /careers
Allow: /testimonials
Allow: /safety-guide
Allow: /terms
Allow: /privacy
Allow: /rera-disclaimer
Allow: /refund-policy
Allow: /brokers

Disallow: /admin/
Disallow: /profile
Disallow: /verify-email

Sitemap: /sitemap.xml
`;
});

