const slides = document.querySelectorAll('.slide');
const dots = document.getElementById('dots');
let current = 0;

const goTo = i => {
    [slides[current], dots.children[current]].forEach((el, j) =>
        j ? el.style.background = '#ccc' : el.style.display = 'none'
    );
    current = (i + slides.length) % slides.length;
    slides[current].style.display = 'block';
    dots.children[current].style.background = '#485fc7';
};

slides.forEach((_, i) => {
    const dot = Object.assign(document.createElement('span'), {
        className: 'dot',
        onclick: () => goTo(i)
    });
    dot.style.background = i === 0 ? '#485fc7' : '#ccc';
    dots.appendChild(dot);
});

document.getElementById('nextBtn').onclick = () => goTo(current + 1);
document.getElementById('prevBtn').onclick = () => goTo(current - 1);
setInterval(() => goTo(current + 1), 5000);

const drawer = document.getElementById('drawer');
const overlay = document.getElementById('overlay');
const toggle = show => {
    drawer.style.right = show ? '0' : '-100%';
    overlay.style.display = show ? 'block' : 'none';
};

document.querySelectorAll('.voir-projet').forEach(btn =>
    btn.addEventListener('click', e => {
        e.preventDefault();
        const content = document.getElementById(`projet-${btn.dataset.projet}-content`);
        const clone = content.cloneNode(true);
        const titre = clone.querySelector('h3');
        document.getElementById('drawer-titre').textContent = titre.textContent;
        titre.remove();
        document.getElementById('drawer-contenu').innerHTML = clone.innerHTML;
        toggle(true);
    })
);

[document.getElementById('closeDrawer'), overlay].forEach(el => el.onclick = () => toggle(false));

const modal = document.getElementById('modal-dessins');
document.getElementById('open-dessins').onclick = e => { e.preventDefault(); modal.classList.add('is-active'); };
[document.getElementById('close-modal-dessins'), modal.querySelector('.modal-background')]
    .forEach(el => el.onclick = () => modal.classList.remove('is-active'));

const track = document.getElementById('marquee-track');
const cards = Array.from(track.children);
const w = track.scrollWidth;
Array.from({ length: Math.ceil(window.innerWidth * 2 / w) }, () =>
    cards.forEach(c => { const cl = c.cloneNode(true); cl.setAttribute('aria-hidden', 'true'); track.appendChild(cl); })
);
track.style.setProperty('--original-width', w + 'px');