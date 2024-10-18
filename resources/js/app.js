import '../css/app.css';
import './bootstrap';

// Vuetify
import 'vuetify/styles';
import { createVuetify, useTheme } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import '@mdi/font/css/materialdesignicons.css';
import { en, uk, ru } from 'vuetify/locale';

// Core Vue and inertiajs
import { createApp, h, provide } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

// Helpers and plugins
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import { Settings } from 'luxon';

// Stores and custom plugins
import { useUserStore, useNotificationStore } from '@/Stores';
// import laravelEcho from '@/Plugins/laravelEcho';
import useMedia from '@/Plugins/useMedia.js';
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
  locale: {
    locale: import.meta.env.VITE_APP_LOCALE,
    messages: { en, uk, ru },
  },
});

Settings.defaultLocale = import.meta.env.VITE_APP_LOCALE;

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function createAppInstance({ App, props, plugin }) {
  const app = createApp({ render: () => h(App, props) })
    .use(plugin)
    .use(createPinia())
    .use(ZiggyVue)
    .use(vuetify)
    .use(ToastPlugin)
    .use(useLink);

  app.config.globalProperties.$media = useMedia();
  app.config.globalProperties.$appName = appName;

  return app;
}

function setupEventListeners(app) {
  const $toast = useToast();

  router.on('success', (event) => {
    setupStores(event.detail.page.props);

    if (status) {
      $toast.open({
        message: status.message,
        type: status.type || 'default',
        duration: 5000,
      });
    }
  });
}

function setupStores(props) {
  const { user = null, notifications_unread = 0 } = props.auth ?? {};

  const notificationStore = useNotificationStore();
  const userStore = useUserStore();

  userStore.setUser(user);
  notificationStore.setNotificationsUnread(notifications_unread);
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
  title: (title) => `${appName} - ${title}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const appInstance = createAppInstance({ App, props, plugin });
    const mountedApp = appInstance.mount(el);

    // Setup stores on initial page load and listen for route changes
    setupStores(props.initialPage.props);
    setupEventListeners(mountedApp);

    // laravelEcho.start(mountedApp, userStore);
    // Screen.setSizes({ sm: 640, md: 768, lg: 1024, xl: 1280 });

    toggleTheme(mountedApp.$vuetify);

    return mountedApp;
  },
  progress: { color: '#4B5563', showSpinner: true },
});
