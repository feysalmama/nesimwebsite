/*
 * The public site's interactivity, replacing five React client components:
 * ScrollReveal, ImpactCounter, HeroSlider, Navbar and LanguageSwitcher.
 *
 * Everything is driven by data-* attributes the Blade templates already render,
 * and every visual state lives in resources/css/app.css under an attribute
 * selector. No Tailwind class string is invented here except where the literal
 * already appears in a template — Tailwind v4 generates utilities by scanning
 * source text, so a class that exists only in JavaScript is a class that does
 * not exist.
 *
 * Livewire is deliberately not used on the public pages. It injects Alpine only
 * into responses that actually contain a Livewire component, so the homepage
 * would have to carry a component it does not want just to get the runtime. The
 * admin panel does use Livewire and gets it for free.
 *
 * The last two functions are the exception on the admin side: the sidebar and
 * the login form are driven from here rather than by Alpine, because the
 * dashboard and the login page render no component at all and so never receive
 * the runtime. Every init below guards on its own root element, which is what
 * makes one bundle safe to load on all three kinds of page.
 *
 * Vite loads this as a deferred module, so the DOM is already parsed when it
 * runs and there is no DOMContentLoaded wait.
 */

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const canObserve = 'IntersectionObserver' in window;

/* ── ScrollReveal ──────────────────────────────────────────────────────────── */

function initReveal() {
    const nodes = document.querySelectorAll('[data-reveal]');

    if (nodes.length === 0) {
        return;
    }

    // The React component made these visible immediately for reduced motion and
    // left them hidden forever in a browser without IntersectionObserver. The
    // second case is a bug, not a preference, so both short-circuit to visible.
    if (reducedMotion || ! canObserve) {
        nodes.forEach((node) => node.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver((entries, self) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            const node = entry.target;
            const delay = parseInt(node.dataset.revealDelay || '0', 10) || 0;

            self.unobserve(node);
            window.setTimeout(() => node.classList.add('is-visible'), delay);
        });
    }, { threshold: 0.15 });

    nodes.forEach((node) => observer.observe(node));
}

/* ── ImpactCounter ─────────────────────────────────────────────────────────── */

function initCounters() {
    document.querySelectorAll('[data-counter]').forEach((node) => {
        const target = parseInt(node.dataset.counter, 10);
        const out = node.querySelector('[data-counter-out]');

        if (! out || ! Number.isFinite(target)) {
            return;
        }

        /*
         * The server already rendered the final figure, so under reduced motion
         * — or without an observer — the right move is to do nothing at all and
         * leave it on screen. Zeroing first would blank a number that was
         * correct a moment ago.
         */
        if (reducedMotion || ! canObserve) {
            return;
        }

        out.textContent = '0';

        let started = false;

        const observer = new IntersectionObserver((entries) => {
            const entry = entries[0];

            if (! entry.isIntersecting || started) {
                return;
            }

            started = true;
            observer.disconnect();

            const duration = 1400;
            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                // The same cubic ease-out the React component used.
                const eased = 1 - Math.pow(1 - progress, 3);

                out.textContent = Math.round(eased * target).toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        }, { threshold: 0.4 });

        observer.observe(node);
    });
}

/* ── HeroSlider ────────────────────────────────────────────────────────────── */

function initHeroSlider() {
    const root = document.querySelector('[data-hero-slider]');

    if (! root) {
        return;
    }

    const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));

    // One slide needs no timer, no dots and no copy swapping.
    if (slides.length < 2) {
        return;
    }

    const title = root.querySelector('[data-hero-title]');
    const subtitle = root.querySelector('[data-hero-subtitle]');
    const button = root.querySelector('[data-hero-button]');
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));

    /*
     * Only the first slide's copy is in the HTML, so the page keeps a single
     * <h1>. The rest arrives here. A malformed payload is not worth throwing
     * over: the images still cross-fade and the first slide's copy stays put.
     */
    let copy = [];

    try {
        copy = JSON.parse(root.querySelector('[data-hero-data]')?.textContent || '[]');
    } catch {
        copy = [];
    }

    let current = 0;
    let timer = null;

    const show = (index) => {
        current = ((index % slides.length) + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            slide.classList.toggle('opacity-0', i !== current);
            slide.classList.toggle('pointer-events-none', i !== current);
        });

        dots.forEach((dot, i) => {
            dot.dataset.active = i === current ? 'true' : 'false';
        });

        const next = copy[current];

        if (! next) {
            return;
        }

        if (title) {
            title.textContent = next.title || '';
        }

        if (subtitle) {
            subtitle.textContent = next.subtitle || '';
            subtitle.classList.toggle('hidden', ! next.subtitle);
        }

        if (button) {
            button.textContent = next.buttonText || '';
            button.classList.toggle('hidden', ! (next.buttonText && next.buttonUrl));

            if (next.buttonUrl) {
                button.setAttribute('href', next.buttonUrl);
            }
        }
    };

    const stop = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        if (timer === null && ! reducedMotion) {
            timer = window.setInterval(() => show(current + 1), 6000);
        }
    };

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            show(parseInt(dot.dataset.heroDot, 10) || 0);
            // Restart so a manual choice is not immediately overwritten.
            stop();
            start();
        });
    });

    // Pause while the hero is being read or the tab is in the background.
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

    start();
}

/* ── Navbar ────────────────────────────────────────────────────────────────── */

function initNavbar() {
    const header = document.querySelector('[data-navbar]');

    if (! header) {
        return;
    }

    const onScroll = () => {
        header.dataset.navScrolled = window.scrollY > 10 ? 'true' : 'false';
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    const toggle = header.querySelector('[data-nav-toggle]');

    const setOpen = (open) => {
        header.dataset.navOpen = open ? 'true' : 'false';
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        // The React version locked the body the same way; without it the page
        // behind the panel keeps scrolling and the backdrop slides away.
        document.body.style.overflow = open ? 'hidden' : '';
    };

    toggle?.addEventListener('click', () => setOpen(header.dataset.navOpen !== 'true'));

    header.querySelectorAll('[data-nav-close]').forEach((node) => {
        node.addEventListener('click', () => setOpen(false));
    });

    // Choosing a destination has to unlock the body, which a full page load
    // would do anyway but an in-page anchor would not.
    header.querySelectorAll('.nav-mobile-panel a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
}

/* ── Dropdowns and mobile accordions ───────────────────────────────────────── */

function initDropdowns() {
    const toggles = Array.from(document.querySelectorAll('[data-dropdown-toggle]'));

    if (toggles.length === 0) {
        return;
    }

    // .group is a desktop nav entry, .lang-wrap the language switcher.
    const wrapperOf = (node) => node.closest('.group, .lang-wrap');

    const close = (wrapper) => {
        wrapper.classList.remove('is-open');
        wrapper.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
    };

    const closeAll = (except = null) => {
        toggles.forEach((node) => {
            const wrapper = wrapperOf(node);

            if (wrapper && wrapper !== except) {
                close(wrapper);
            }
        });
    };

    toggles.forEach((node) => {
        const wrapper = wrapperOf(node);

        if (! wrapper) {
            return;
        }

        node.addEventListener('click', (event) => {
            event.preventDefault();
            // Keeps the document listener below from immediately undoing this.
            event.stopPropagation();

            const open = ! wrapper.classList.contains('is-open');

            closeAll(open ? wrapper : null);
            wrapper.classList.toggle('is-open', open);
            node.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        wrapper.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => close(wrapper));
        });
    });

    document.addEventListener('click', (event) => {
        if (! event.target.closest('.group, .lang-wrap')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    /*
     * Mobile accordions are separate: they expand in place rather than floating,
     * so several may be open at once and clicking elsewhere must not close them.
     */
    document.querySelectorAll('[data-accordion-toggle]').forEach((node) => {
        node.addEventListener('click', () => {
            const wrapper = node.parentElement;

            if (! wrapper) {
                return;
            }

            const open = ! wrapper.classList.contains('is-open');

            wrapper.classList.toggle('is-open', open);
            node.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
}

/* ── Admin sidebar ─────────────────────────────────────────────────────────── */

/**
 * The useState(openSections) from AdminSidebar.tsx, as a data attribute. The
 * two visual states — hiding the group body and rotating the chevron — are rules
 * in app.css keyed on [data-collapsed], so this only flips the flag.
 */
function initAdminSidebar() {
    document.querySelectorAll('[data-admin-group-toggle]').forEach((node) => {
        node.addEventListener('click', () => {
            const group = node.closest('[data-admin-group]');

            if (! group) {
                return;
            }

            const collapsed = group.dataset.collapsed === 'true';

            group.dataset.collapsed = collapsed ? 'false' : 'true';
            node.setAttribute('aria-expanded', collapsed ? 'true' : 'false');
        });
    });
}

/* ── Submit guards ─────────────────────────────────────────────────────────── */

/**
 * Replaces the setIsLoading(true) the React forms set before an await. Those
 * existed to cover a fetch round trip; a plain POST covers itself with
 * navigation, and all that is still needed is protection against a double click
 * sending the same submission twice.
 */
function initSubmitGuards() {
    document.querySelectorAll('form[data-submit-guard]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');

            if (! button) {
                return;
            }

            button.disabled = true;

            /*
             * A rejected submission comes back as a fresh document, so the button
             * is re-enabled by the reload itself. This timer only covers the case
             * where navigation never happens at all — and a permanently disabled
             * submit button is worse than a duplicate post, so it has to give way.
             */
            window.setTimeout(() => {
                button.disabled = false;
            }, 8000);
        });
    });
}

initReveal();
initCounters();
initHeroSlider();
initNavbar();
initDropdowns();
initAdminSidebar();
initSubmitGuards();
