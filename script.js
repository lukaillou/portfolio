const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const navbarBurger = document.querySelector('.portfolio-burger');
const navbarMenu = navbarBurger ? document.getElementById(navbarBurger.dataset.target) : null;
const closeMobileMenu = () => {
    if (!navbarBurger || !navbarMenu) return;

    navbarBurger.classList.remove('is-active');
    navbarMenu.classList.remove('is-active');
    navbarBurger.setAttribute('aria-expanded', 'false');
    navbarBurger.setAttribute('aria-label', 'Ouvrir le menu');
};

if (navbarBurger && navbarMenu) {
    navbarBurger.addEventListener('click', () => {
        const isOpen = navbarBurger.classList.toggle('is-active');

        navbarMenu.classList.toggle('is-active', isOpen);
        navbarBurger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        navbarBurger.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    });

    navbarMenu.querySelectorAll('a').forEach(link =>
        link.addEventListener('click', closeMobileMenu)
    );
}

const backToTop = document.getElementById('backToTop');
const toggleBackToTop = () => {
    if (!backToTop) return;
    backToTop.classList.toggle('is-visible', window.scrollY > 520);
};

if (backToTop) {
    toggleBackToTop();
    window.addEventListener('scroll', toggleBackToTop, { passive: true });
    backToTop.addEventListener('click', closeMobileMenu);
}

const slides = document.querySelectorAll('.slide');
const dots = document.getElementById('dots');
let current = 0;

const goTo = i => {
    const next = (i + slides.length) % slides.length;
    if (next === current) return;

    const direction = next > current || (current === slides.length - 1 && next === 0) ? 'next' : 'prev';
    const previousSlide = slides[current];
    const nextSlide = slides[next];

    previousSlide.classList.remove('is-active', 'is-entering-next', 'is-entering-prev');
    previousSlide.classList.add(direction === 'next' ? 'is-leaving-next' : 'is-leaving-prev');
    previousSlide.setAttribute('aria-hidden', 'true');

    nextSlide.classList.remove('is-leaving-next', 'is-leaving-prev', 'is-entering-next', 'is-entering-prev');
    nextSlide.classList.add(direction === 'next' ? 'is-entering-next' : 'is-entering-prev');
    nextSlide.classList.add('is-active');
    nextSlide.setAttribute('aria-hidden', 'false');

    dots.children[current].style.background = '#c9c4b8';
    dots.children[next].style.background = '#3f5a8a';
    current = next;

    window.setTimeout(() => {
        previousSlide.classList.remove('is-leaving-next', 'is-leaving-prev');
        nextSlide.classList.remove('is-entering-next', 'is-entering-prev');
    }, 360);
};

slides.forEach((_, i) => {
    slides[i].classList.remove('is-entering-next', 'is-entering-prev', 'is-leaving-next', 'is-leaving-prev');
    slides[i].classList.toggle('is-active', i === 0);
    slides[i].setAttribute('aria-hidden', i === 0 ? 'false' : 'true');
    const dot = Object.assign(document.createElement('span'), {
        className: 'dot',
        onclick: () => goTo(i)
    });
    dot.style.background = i === 0 ? '#3f5a8a' : '#c9c4b8';
    dots.appendChild(dot);
});

document.getElementById('nextBtn').onclick = () => goTo(current + 1);
document.getElementById('prevBtn').onclick = () => goTo(current - 1);
if (!prefersReducedMotion) {
    setInterval(() => goTo(current + 1), 5000);
}

const drawer = document.getElementById('drawer');
const overlay = document.getElementById('overlay');
const drawerTitle = document.getElementById('drawer-titre');
const drawerMeta = document.getElementById('drawer-meta');
const drawerContent = document.getElementById('drawer-contenu');
const closeDrawer = document.getElementById('closeDrawer');
const projectLightbox = document.getElementById('project-lightbox');
const lightboxImage = document.getElementById('lightboxImage');
const lightboxCaption = document.getElementById('lightboxCaption');
const lightboxCounter = document.getElementById('lightboxCounter');
const closeLightbox = document.getElementById('closeLightbox');
const prevLightbox = document.getElementById('prevLightbox');
const nextLightbox = document.getElementById('nextLightbox');
let lightboxItems = [];
let lightboxIndex = 0;
let lastLightboxTrigger = null;
let lastProjectTrigger = null;

const setLightboxImage = index => {
    if (!lightboxItems.length || !lightboxImage) return;

    lightboxIndex = (index + lightboxItems.length) % lightboxItems.length;
    const image = lightboxItems[lightboxIndex];
    const caption = image.alt || '';
    const hasMultipleImages = lightboxItems.length > 1;

    lightboxImage.src = image.currentSrc || image.src;
    lightboxImage.alt = caption || 'Image du projet';

    if (lightboxCaption) {
        lightboxCaption.textContent = caption;
        lightboxCaption.hidden = !caption;
    }

    if (lightboxCounter) {
        lightboxCounter.textContent = `${lightboxIndex + 1} / ${lightboxItems.length}`;
    }

    [prevLightbox, nextLightbox].filter(Boolean).forEach(button => {
        button.disabled = !hasMultipleImages;
        button.setAttribute('aria-hidden', hasMultipleImages ? 'false' : 'true');
    });
};

const toggleProjectLightbox = (show, restoreFocus = true) => {
    if (!projectLightbox) return;

    projectLightbox.classList.toggle('is-open', show);
    projectLightbox.setAttribute('aria-hidden', show ? 'false' : 'true');
    document.body.classList.toggle('lightbox-open', show);

    if (show) {
        closeLightbox?.focus();
        return;
    }

    if (lightboxImage) {
        lightboxImage.removeAttribute('src');
        lightboxImage.alt = '';
    }

    if (restoreFocus && lastLightboxTrigger && drawer?.classList.contains('is-open')) {
        lastLightboxTrigger.focus();
    }
};

const openProjectLightbox = trigger => {
    if (!projectLightbox || !drawerContent) return;

    const image = trigger.querySelector('img');
    if (!image) return;

    lightboxItems = Array.from(drawerContent.querySelectorAll('.drawer-media img'));
    const selectedIndex = lightboxItems.indexOf(image);
    if (selectedIndex < 0) return;

    lastLightboxTrigger = trigger;
    setLightboxImage(selectedIndex);
    toggleProjectLightbox(true);
};

const toggle = show => {
    const wasOpen = drawer.classList.contains('is-open');
    drawer.classList.toggle('is-open', show);
    overlay.classList.toggle('is-open', show);
    drawer.setAttribute('aria-hidden', show ? 'false' : 'true');
    document.body.classList.toggle('drawer-open', show);
    if (!show) toggleProjectLightbox(false, false);
    if (show && closeDrawer) closeDrawer.focus();
    if (!show && wasOpen) lastProjectTrigger?.focus();
};

document.querySelectorAll('.voir-projet').forEach(btn =>
    btn.addEventListener('click', e => {
        e.preventDefault();
        const content = document.getElementById(`projet-${btn.dataset.projet}-content`);
        if (!content) {
            console.error(`Détail introuvable pour le projet ${btn.dataset.projet}.`);
            return;
        }

        lastProjectTrigger = btn;
        const clone = content.cloneNode(true);
        const fallbackTitle = clone.querySelector('h3');
        drawerTitle.textContent = content.dataset.title || fallbackTitle?.textContent || 'Détail du projet';
        if (fallbackTitle) fallbackTitle.remove();
        if (drawerMeta) {
            const metaParts = [content.dataset.number, content.dataset.date, content.dataset.category].filter(Boolean);
            drawerMeta.textContent = metaParts.length
                ? metaParts.join(' — ')
                : 'planche de projet';
        }
        drawerContent.innerHTML = content.dataset.title ? content.innerHTML : clone.innerHTML;
        toggle(true);
    })
);

[closeDrawer, overlay].filter(Boolean).forEach(el => el.onclick = () => toggle(false));

if (drawerContent) {
    drawerContent.addEventListener('click', e => {
        const trigger = e.target.closest('.drawer-media-button');
        if (trigger && drawerContent.contains(trigger)) {
            openProjectLightbox(trigger);
        }
    });
}

[closeLightbox, projectLightbox].filter(Boolean).forEach(el =>
    el.addEventListener('click', e => {
        if (e.currentTarget === closeLightbox || e.target === projectLightbox) {
            toggleProjectLightbox(false);
        }
    })
);

if (prevLightbox) {
    prevLightbox.onclick = () => setLightboxImage(lightboxIndex - 1);
}

if (nextLightbox) {
    nextLightbox.onclick = () => setLightboxImage(lightboxIndex + 1);
}

document.addEventListener('keydown', e => {
    if (!projectLightbox?.classList.contains('is-open')) return;

    if (e.key === 'Escape') {
        e.preventDefault();
        e.stopImmediatePropagation();
        toggleProjectLightbox(false);
    }

    if (e.key === 'ArrowLeft') {
        e.preventDefault();
        setLightboxImage(lightboxIndex - 1);
    }

    if (e.key === 'ArrowRight') {
        e.preventDefault();
        setLightboxImage(lightboxIndex + 1);
    }
});

document.addEventListener('keydown', e => {
    if (
        e.key === 'Escape'
        && drawer.classList.contains('is-open')
        && !projectLightbox?.classList.contains('is-open')
    ) toggle(false);
});

const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])'
].join(',');

const trapTabFocus = (container, event) => {
    if (event.key !== 'Tab') return;

    const focusableElements = Array.from(container.querySelectorAll(focusableSelector))
        .filter(element => element.getAttribute('aria-hidden') !== 'true' && element.getClientRects().length);
    if (!focusableElements.length) {
        event.preventDefault();
        return;
    }

    const first = focusableElements[0];
    const last = focusableElements[focusableElements.length - 1];
    const activeElement = document.activeElement;

    if (event.shiftKey && (activeElement === first || !container.contains(activeElement))) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && (activeElement === last || !container.contains(activeElement))) {
        event.preventDefault();
        first.focus();
    }
};

document.addEventListener('keydown', event => {
    const activeDialog = [
        projectLightbox?.classList.contains('is-open') && projectLightbox,
        drawer?.classList.contains('is-open') && drawer
    ].find(Boolean);

    if (activeDialog) trapTabFocus(activeDialog, event);
});

const track = document.getElementById('marquee-track');
if (track) {
    const cards = Array.from(track.children);
    const w = track.scrollWidth;
    Array.from({ length: Math.ceil(window.innerWidth * 2 / w) }, () =>
        cards.forEach(c => {
            const cl = c.cloneNode(true);
            cl.setAttribute('aria-hidden', 'true');
            track.appendChild(cl);
        })
    );
    track.style.setProperty('--original-width', w + 'px');
}

// Apparition douce des contenus au scroll (désactivée si mouvement réduit demandé)
if (!prefersReducedMotion) {
    const revealTargets = document.querySelectorAll('.reveal, .scroll-reveal');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    revealTargets.forEach(el => observer.observe(el));
}
