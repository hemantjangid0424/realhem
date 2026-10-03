import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import PropertiesView from '../views/PropertiesView.vue';
import UsersView from '../views/UsersView.vue';
import SettingsView from '../views/SettingsView.vue';

const routes = [
    {
        path: '/login',
        name: 'admin.login',
        component: LoginView,
        meta: { guestOnly: true },
    },
    {
        path: '/',
        redirect: '/dashboard',
    },
    {
        path: '/dashboard',
        name: 'admin.dashboard',
        component: DashboardView,
        meta: { requiresAuth: true },
    },
    {
        path: '/properties',
        name: 'admin.properties',
        component: PropertiesView,
        meta: { requiresAuth: true },
    },
    {
        path: '/users',
        name: 'admin.users',
        component: UsersView,
        meta: { requiresAuth: true },
    },
    {
        path: '/settings',
        name: 'admin.settings',
        component: SettingsView,
        meta: { requiresAuth: true },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard',
    },
];

const router = createRouter({
    history: createWebHistory('/admin'),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('realhem_admin_token');

    if (to.meta.requiresAuth && !token) {
        return next({ name: 'admin.login' });
    }

    if (to.meta.guestOnly && token) {
        return next({ name: 'admin.dashboard' });
    }

    next();
});

export default router;
