import '../css/app.css';
import './bootstrap';
import 'flowbite';


import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { csrf } from './bootstrap';
import { createPinia } from 'pinia';

const pinia = createPinia();
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .use(pinia);

        // app.component('Link', Link);
        // app.component('Head', Head);

        csrf().finally(() => app.mount(el));
    },
    progress: { color: '#4B5563' },
});
