export default defineNuxtRouteMiddleware((to, from) => {
  if (import.meta.client) {
    const token = localStorage.getItem('realhem_admin_token');
    if (!token && to.path !== '/admin/login') {
      return navigateTo('/admin/login');
    }
    if (token && to.path === '/admin/login') {
      return navigateTo('/admin/dashboard');
    }
  }
});

