import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Alpine = Alpine;

Alpine.store('theme', {
    init() {
        const savedTheme = localStorage.getItem('theme');

        this.theme = savedTheme === 'dark' ? 'dark' : 'light';
        this.updateTheme();
    },

    theme: 'light',
    resolvedTheme: 'light',

    set(value) {
        value = value === 'dark' ? 'dark' : 'light';

        this.theme = value;
        localStorage.setItem('theme', value);

        this.updateTheme();

        window.dispatchEvent(
            new CustomEvent('theme-changed', {
                detail: value
            })
        );
    },

    toggle() {
        this.set(
            this.resolvedTheme === 'dark'
                ? 'light'
                : 'dark'
        );
    },

    updateTheme() {
        const html = document.documentElement;
        const isDark = this.theme === 'dark';

        if (isDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        this.resolvedTheme = isDark ? 'dark' : 'light';

        html.setAttribute(
            'data-color-scheme',
            this.resolvedTheme
        );

        html.dataset.theme = this.resolvedTheme;
        html.style.colorScheme = this.resolvedTheme;

        if (document.body) {
            document.body.dataset.theme = this.resolvedTheme;
            document.body.style.colorScheme = this.resolvedTheme;
        }
    }
});

Alpine.store('sidebar', {
    isExpanded: true,
    isMobileOpen: false,
    isHovered: false,

    init() {
        const savedState =
            localStorage.getItem('sidebarExpanded');

        if (window.innerWidth >= 1280) {
            this.isExpanded = savedState !== 'false';
        } else {
            this.isExpanded = false;
        }

        this.isMobileOpen = false;

        window.addEventListener('resize', () => {
            this.handleResize();
        });
    },

    handleResize() {
        if (window.innerWidth < 1280) {
            this.isMobileOpen = false;
        } else {
            this.isMobileOpen = false;

            const savedState =
                localStorage.getItem('sidebarExpanded');

            this.isExpanded = savedState !== 'false';
        }
    },

    toggleExpanded() {
        this.isExpanded = !this.isExpanded;
        this.isMobileOpen = false;

        if (window.innerWidth >= 1280) {
            localStorage.setItem(
                'sidebarExpanded',
                this.isExpanded
            );
        }
    },

    toggleMobileOpen() {
        this.isMobileOpen = !this.isMobileOpen;
    },

    setMobileOpen(value) {
        this.isMobileOpen = value;
    },

    setHovered(value) {
        if (
            window.innerWidth >= 1280 &&
            !this.isExpanded
        ) {
            this.isHovered = value;
        }
    }
});

Livewire.start();