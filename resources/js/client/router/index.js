import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import ListingsView from '../views/ListingsView.vue';
import PostPropertyView from '../views/PostPropertyView.vue';
import BrokerPortalView from '../views/BrokerPortalView.vue';

const routes = [
    {
        path: '/',
        name: 'client.home',
        component: HomeView,
    },
    {
        path: '/listings',
        name: 'client.listings',
        component: ListingsView,
    },
    {
        path: '/post-property',
        name: 'client.post-property',
        component: PostPropertyView,
    },
    {
        path: '/profile',
        name: 'client.profile',
        component: () => import('../views/ProfileView.vue'),
    },
    {
        path: '/brokers',
        name: 'client.brokers',
        component: BrokerPortalView,
    },
    {
        path: '/property/:slug',
        name: 'client.property-detail',
        component: () => import('../views/PropertyDetailView.vue'),
    },
    {
        path: '/terms',
        name: 'client.terms',
        component: () => import('../views/LegalPageView.vue'),
    },
    {
        path: '/privacy',
        name: 'client.privacy',
        component: () => import('../views/LegalPageView.vue'),
    },
    {
        path: '/rera-disclaimer',
        name: 'client.rera-disclaimer',
        component: () => import('../views/LegalPageView.vue'),
    },
    {
        path: '/refund-policy',
        name: 'client.refund-policy',
        component: () => import('../views/LegalPageView.vue'),
    },
    {
        path: '/about-us',
        name: 'client.about-us',
        component: () => import('../views/AboutUsView.vue'),
    },
    {
        path: '/contact-us',
        name: 'client.contact-us',
        component: () => import('../views/ContactUsView.vue'),
    },
    {
        path: '/careers',
        name: 'client.careers',
        component: () => import('../views/CareersView.vue'),
    },
    {
        path: '/request-info',
        name: 'client.request-info',
        component: () => import('../views/InquiryFormsView.vue'),
    },
    {
        path: '/feedback',
        name: 'client.feedback',
        component: () => import('../views/InquiryFormsView.vue'),
    },
    {
        path: '/report-problem',
        name: 'client.report-problem',
        component: () => import('../views/InquiryFormsView.vue'),
    },
    {
        path: '/testimonials',
        name: 'client.testimonials',
        component: () => import('../views/TestimonialsView.vue'),
    },
    {
        path: '/safety-guide',
        name: 'client.safety-guide',
        component: () => import('../views/SafetyGuideView.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory('/'),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
