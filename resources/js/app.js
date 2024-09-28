import '../css/app.css';
import './bootstrap';

// Vuetify
import 'vuetify/styles';
import { createVuetify, useTheme } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import '@mdi/font/css/materialdesignicons.css';

// Core Vue and inertiajs
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

// Helpers and plugins
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';

// Stores and custom plugins
import { useUserStore } from '@/Stores/UserStore/index.js';
// import useTimestamp from '@/Plugins/useTimestamp.js';
// import useMedia from '@/Plugins/useMedia.js';
// import laravelEcho from '@/Plugins/laravelEcho';
import useSession from '@/Composables/useSession';
import useLink from '@/Plugins/Link';

// Toast
import ToastPlugin, { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-default.css';

const vuetify = createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi',
  },
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function createAppInstance({ App, props, plugin }) {
  const app = createApp({ render: () => h(App, props) })
    .use(plugin)
    .use(createPinia())
    .use(ZiggyVue)
    .use(vuetify)
    .use(ToastPlugin)
    .use(useLink);

  // app.config.globalProperties.$timestamp = useTimestamp;
  // app.config.globalProperties.$media = useMedia();

  return app;
}

function setupEventListeners(app, userStore) {
  const $toast = useToast();

  router.on('success', (event) => {
    const { status, user } = event.detail.page.props;
    userStore.setUser(user);

    if (status) {
      $toast.open({
        message: status.message,
        type: status.type || 'default',
        duration: 5000,
      });
    }
  });
}

function toggleTheme(vuetify) {
  const themeMode = useSession.get('theme', 'light');
  vuetify.theme.global.name = themeMode;

  if (themeMode === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const appInstance = createAppInstance({ App, props, plugin });

    // Set user on initial page load and listen for route changes
    const userStore = useUserStore();
    userStore.setUser(props.initialPage.props.user);

    const mountedApp = appInstance.mount(el);
    setupEventListeners(mountedApp, userStore);

    // laravelEcho.start(mountedApp, userStore);
    // Screen.setSizes({ sm: 640, md: 768, lg: 1024, xl: 1280 });

    toggleTheme(mountedApp.$vuetify);

    return mountedApp;
  },
  progress: { color: '#4B5563', showSpinner: true },
});
