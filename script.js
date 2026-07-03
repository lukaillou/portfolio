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
    drawer.classList.toggle('is-open', show);
    overlay.classList.toggle('is-open', show);
    drawer.setAttribute('aria-hidden', show ? 'false' : 'true');
    document.body.classList.toggle('drawer-open', show);
    if (!show) toggleProjectLightbox(false, false);
    if (show && closeDrawer) closeDrawer.focus();
};

document.querySelectorAll('.voir-projet').forEach(btn =>
    btn.addEventListener('click', e => {
        e.preventDefault();
        const content = document.getElementById(`projet-${btn.dataset.projet}-content`);
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

const modal = document.getElementById('modal-dessins');
const openDrawings = document.getElementById('open-dessins');
const closeDrawings = document.getElementById('close-modal-dessins');
const drawingsBackground = modal?.querySelector('.modal-background');
const drawingsLightbox = document.getElementById('drawings-lightbox');
const drawingLightboxImage = document.getElementById('drawingLightboxImage');
const drawingLightboxCaption = document.getElementById('drawingLightboxCaption');
const drawingLightboxCounter = document.getElementById('drawingLightboxCounter');
const closeDrawingLightbox = document.getElementById('closeDrawingLightbox');
const prevDrawingLightbox = document.getElementById('prevDrawingLightbox');
const nextDrawingLightbox = document.getElementById('nextDrawingLightbox');
let drawingItems = [];
let drawingIndex = 0;
let lastDrawingTrigger = null;
let drawingThumbsWarmed = false;

const warmDrawingThumbs = (immediate = false) => {
    if (!modal || drawingThumbsWarmed) return;

    const thumbs = Array.from(modal.querySelectorAll('.sketch-card img'));
    const preload = () => {
        if (drawingThumbsWarmed) return;

        drawingThumbsWarmed = true;
        thumbs.forEach(img => {
            const source = img.currentSrc || img.src;

            if (!source) return;

            const cachedImage = new Image();
            cachedImage.decoding = 'async';
            cachedImage.src = source;
        });
    };

    if (immediate) {
        preload();
        return;
    }

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(preload, { timeout: 1200 });
        return;
    }

    window.setTimeout(preload, 450);
};

const setDrawingLightboxImage = index => {
    if (!drawingItems.length || !drawingLightboxImage) return;

    drawingIndex = (index + drawingItems.length) % drawingItems.length;
    const trigger = drawingItems[drawingIndex];
    const image = trigger.querySelector('img');
    const caption = trigger.closest('.sketch-card')?.querySelector('figcaption')?.textContent || image?.alt || '';
    const hasMultipleDrawings = drawingItems.length > 1;

    if (!image) return;

    drawingLightboxImage.src = trigger.dataset.full || image.currentSrc || image.src;
    drawingLightboxImage.alt = image.alt || caption || 'Dessin agrandi';

    if (drawingLightboxCaption) {
        drawingLightboxCaption.textContent = caption;
        drawingLightboxCaption.hidden = !caption;
    }

    if (drawingLightboxCounter) {
        drawingLightboxCounter.textContent = `${drawingIndex + 1} / ${drawingItems.length}`;
    }

    [prevDrawingLightbox, nextDrawingLightbox].filter(Boolean).forEach(button => {
        button.disabled = !hasMultipleDrawings;
        button.setAttribute('aria-hidden', hasMultipleDrawings ? 'false' : 'true');
    });
};

const toggleDrawingLightbox = (show, restoreFocus = true) => {
    if (!drawingsLightbox) return;

    drawingsLightbox.classList.toggle('is-open', show);
    drawingsLightbox.setAttribute('aria-hidden', show ? 'false' : 'true');
    document.body.classList.toggle('lightbox-open', show);

    if (show) {
        closeDrawingLightbox?.focus();
        return;
    }

    if (drawingLightboxImage) {
        drawingLightboxImage.removeAttribute('src');
        drawingLightboxImage.alt = '';
    }

    if (restoreFocus && lastDrawingTrigger && modal?.classList.contains('is-active')) {
        lastDrawingTrigger.focus();
    }
};

const openDrawingLightbox = trigger => {
    if (!drawingsLightbox || !modal) return;

    drawingItems = Array.from(modal.querySelectorAll('.sketch-card-button'));
    const selectedIndex = drawingItems.indexOf(trigger);
    if (selectedIndex < 0) return;

    lastDrawingTrigger = trigger;
    setDrawingLightboxImage(selectedIndex);
    toggleDrawingLightbox(true);
};

const toggleDrawings = show => {
    if (!modal) return;
    modal.classList.toggle('is-active', show);
    modal.setAttribute('aria-hidden', show ? 'false' : 'true');
    document.body.classList.toggle('modal-open', show);
    if (!show) toggleDrawingLightbox(false, false);
    if (show) {
        warmDrawingThumbs(true);
        closeDrawings?.focus();
    }
};

if (modal) {
    window.addEventListener('load', () => warmDrawingThumbs(), { once: true });
}

if (openDrawings) {
    openDrawings.onclick = e => {
        e.preventDefault();
        toggleDrawings(true);
    };
}

[closeDrawings, drawingsBackground].filter(Boolean).forEach(el =>
    el.onclick = () => toggleDrawings(false)
);

if (modal) {
    modal.addEventListener('click', e => {
        const trigger = e.target.closest('.sketch-card-button');
        if (trigger && modal.contains(trigger)) {
            openDrawingLightbox(trigger);
        }
    });
}

[closeDrawingLightbox, drawingsLightbox].filter(Boolean).forEach(el =>
    el.addEventListener('click', e => {
        if (e.currentTarget === closeDrawingLightbox || e.target === drawingsLightbox) {
            toggleDrawingLightbox(false);
        }
    })
);

if (prevDrawingLightbox) {
    prevDrawingLightbox.onclick = () => setDrawingLightboxImage(drawingIndex - 1);
}

if (nextDrawingLightbox) {
    nextDrawingLightbox.onclick = () => setDrawingLightboxImage(drawingIndex + 1);
}

document.addEventListener('keydown', e => {
    if (!drawingsLightbox?.classList.contains('is-open')) return;

    if (e.key === 'Escape') {
        e.preventDefault();
        e.stopImmediatePropagation();
        toggleDrawingLightbox(false);
    }

    if (e.key === 'ArrowLeft') {
        e.preventDefault();
        e.stopImmediatePropagation();
        setDrawingLightboxImage(drawingIndex - 1);
    }

    if (e.key === 'ArrowRight') {
        e.preventDefault();
        e.stopImmediatePropagation();
        setDrawingLightboxImage(drawingIndex + 1);
    }
});

document.addEventListener('keydown', e => {
    if (
        e.key === 'Escape'
        && modal?.classList.contains('is-active')
        && !drawingsLightbox?.classList.contains('is-open')
    ) toggleDrawings(false);
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
