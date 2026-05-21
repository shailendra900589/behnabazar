(function () {
    function initCarousel(root) {
        const slides = root.querySelectorAll('.bb-gallery-slide, .bb-card-carousel-slide');
        if (slides.length < 2) {
            return;
        }

        const dotsWrap = root.querySelector('.bb-gallery-dots, .bb-card-carousel-dots');
        let index = 0;
        let timer = null;

        const show = (i) => {
            index = (i + slides.length) % slides.length;
            slides.forEach((slide, n) => {
                slide.classList.toggle('is-active', n === index);
            });
            root.querySelectorAll('.bb-gallery-dot, .bb-card-carousel-dot').forEach((dot, n) => {
                dot.classList.toggle('is-active', n === index);
            });
        };

        if (dotsWrap) {
            dotsWrap.innerHTML = '';
            slides.forEach((_, n) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'bb-gallery-dot' + (root.classList.contains('bb-card-carousel') ? ' bb-card-carousel-dot' : '');
                dot.setAttribute('aria-label', 'Image ' + (n + 1));
                if (n === 0) {
                    dot.classList.add('is-active');
                }
                dot.addEventListener('click', () => {
                    show(n);
                    restart();
                });
                dotsWrap.appendChild(dot);
            });
        }

        const interval = parseInt(root.dataset.interval || '4000', 10) || 4000;

        const restart = () => {
            if (timer) {
                clearInterval(timer);
            }
            timer = setInterval(() => show(index + 1), interval);
        };

        root.addEventListener('mouseenter', () => {
            if (timer) {
                clearInterval(timer);
            }
        });
        root.addEventListener('mouseleave', restart);

        show(0);
        restart();
    }

    function initProductPageGallery() {
        const root = document.querySelector('[data-product-gallery]');
        if (!root) {
            return;
        }

        initCarousel(root);

        root.querySelectorAll('[data-gallery-thumb]').forEach((thumb, thumbIndex) => {
            thumb.addEventListener('click', () => {
                const slides = root.querySelectorAll('.bb-gallery-slide');
                slides.forEach((slide, n) => slide.classList.toggle('is-active', n === thumbIndex));
                root.querySelectorAll('.bb-gallery-dot').forEach((dot, n) => dot.classList.toggle('is-active', n === thumbIndex));
                document.querySelectorAll('[data-gallery-thumb]').forEach((t) => {
                    t.classList.remove('border-bloom', 'border-2');
                    t.style.opacity = '0.75';
                });
                thumb.classList.add('border-bloom', 'border-2');
                thumb.style.opacity = '1';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-card-carousel]').forEach(initCarousel);
        initProductPageGallery();
    });
})();
