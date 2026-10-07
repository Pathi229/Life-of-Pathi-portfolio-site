import './bootstrap';

document.documentElement.classList.add('js-nav');
const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#main-navigation');
menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') !== 'true';
    menuButton.setAttribute('aria-expanded', String(open));
    navigation.classList.toggle('is-open', open);
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && navigation?.classList.contains('is-open')) {
        navigation.classList.remove('is-open');
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.focus();
    }
});

document.querySelectorAll('[data-tree]').forEach(tree => {
    const nodes = [...tree.querySelectorAll('[data-tree-select]')];
    const shell = tree.querySelector('[data-preview-shell]');
    let selected = null;
    const highlight = id => tree.querySelectorAll('[data-branch-glow]').forEach(path => {
        path.classList.toggle('is-highlighted', path.dataset.branchGlow === id);
    });
    const close = (restoreFocus = false) => {
        const previous = selected;
        selected = null;
        nodes.forEach(node => {
            node.classList.remove('is-selected');
            node.setAttribute('aria-expanded', 'false');
            tree.querySelector(`#${node.getAttribute('aria-controls')}`).hidden = true;
        });
        shell.hidden = true;
        highlight(null);
        if (restoreFocus) nodes.find(node => node.dataset.treeSelect === previous)?.focus();
    };
    nodes.forEach(node => {
        node.addEventListener('click', () => {
            const id = node.dataset.treeSelect;
            const same = selected === id;
            close();
            if (same) return;
            selected = id;
            node.classList.add('is-selected');
            node.setAttribute('aria-expanded', 'true');
            tree.querySelector(`#${node.getAttribute('aria-controls')}`).hidden = false;
            shell.hidden = false;
            highlight(id);
        });
        ['pointerenter', 'focus'].forEach(type => node.addEventListener(type, () => highlight(node.dataset.treeSelect)));
        ['pointerleave', 'blur'].forEach(type => node.addEventListener(type, () => highlight(selected)));
    });
    tree.querySelector('[data-close-branch]')?.addEventListener('click', () => close(true));
    tree.addEventListener('keydown', event => {
        if (event.key === 'Escape' && selected) close(true);
    });
    tree.querySelectorAll('[data-tree-page]').forEach(button => button.addEventListener('click', () => {
        close();
        tree.querySelectorAll('[data-tree-group]').forEach(group => {
            group.hidden = group.dataset.treeGroup !== button.dataset.treePage;
        });
        tree.querySelectorAll('[data-tree-page]').forEach(page => page.setAttribute('aria-pressed', String(page === button)));
    }));
});

// Scenery moves; document flow and reading content remain still. No continuous JS loop.
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
const finePointer = matchMedia('(hover: hover) and (pointer: fine)');
const scenes = [...document.querySelectorAll('[data-woodland-scene]')];
const visibleScenes = new Set();
let frame = 0;
const resetMotion = () => {
    document.documentElement.style.removeProperty('--forest-scroll');
    scenes.forEach(scene => {
        ['--pointer-x', '--pointer-y', '--scene-scroll'].forEach(property => scene.style.removeProperty(property));
        scene.classList.toggle('scene-paused', reducedMotion.matches || document.hidden || !visibleScenes.has(scene));
    });
};
const renderScenery = () => {
    frame = 0;
    if (reducedMotion.matches || document.hidden) return;
    if (!document.body.classList.contains('reading-page')) {
        document.documentElement.style.setProperty('--forest-scroll', `${-Math.min(scrollY * .025, 90)}px`);
        document.documentElement.style.setProperty('--haze-opacity', String(.35 + Math.min(scrollY / 3000, .4)));
    }
    visibleScenes.forEach(scene => {
        const bounds = scene.getBoundingClientRect();
        const progress = Math.max(-1, Math.min(1, -bounds.top / bounds.height));
        scene.style.setProperty('--scene-scroll', `${progress * 100}px`);
    });
};
const scheduleMotion = () => {
    if (!frame && !reducedMotion.matches && !document.hidden) frame = requestAnimationFrame(renderScenery);
};
const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) visibleScenes.add(entry.target);
    else visibleScenes.delete(entry.target);
    entry.target.classList.toggle('scene-paused', !entry.isIntersecting || document.hidden || reducedMotion.matches);
    scheduleMotion();
}), { threshold: 0 });
scenes.forEach(scene => {
    observer.observe(scene);
    scene.addEventListener('pointermove', event => {
        if (reducedMotion.matches || !finePointer.matches || event.pointerType === 'touch' || document.hidden || !visibleScenes.has(scene)) return;
        const bounds = scene.getBoundingClientRect();
        scene.style.setProperty('--pointer-x', `${((event.clientX - bounds.left) / bounds.width - .5) * 18}px`);
        scene.style.setProperty('--pointer-y', `${((event.clientY - bounds.top) / bounds.height - .5) * 12}px`);
    }, { passive: true });
    scene.addEventListener('pointerleave', () => {
        scene.style.setProperty('--pointer-x', '0px');
        scene.style.setProperty('--pointer-y', '0px');
    });
});
window.addEventListener('scroll', scheduleMotion, { passive: true });
window.addEventListener('resize', scheduleMotion, { passive: true });
reducedMotion.addEventListener('change', () => { resetMotion(); scheduleMotion(); });
document.addEventListener('visibilitychange', () => { resetMotion(); scheduleMotion(); });
resetMotion();
scheduleMotion();

const article = document.querySelector('#article-body');
const contents = document.querySelector('#contents');
if (article && contents) article.querySelectorAll('h2,h3').forEach((heading, index) => {
    heading.id = `section-${index}`;
    const link = document.createElement('a');
    link.href = `#${heading.id}`;
    link.textContent = heading.textContent;
    contents.append(link);
});
if (location.pathname === '/explore') sessionStorage.setItem('pathi-explore', location.pathname + location.search);
document.querySelectorAll('[data-back-to-explore]').forEach(link => {
    const path = sessionStorage.getItem('pathi-explore');
    if (path?.startsWith('/explore')) link.href = path;
});
