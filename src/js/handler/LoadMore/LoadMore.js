import gsap from 'gsap';

class LoadMore {
    constructor(payload = {}) {
        const { element, action, perPage, container, template, nonce, postType, taxonomy, term } = payload;

        if (!element) {
            console.error('LoadMore: element is required');
            return;
        }

        // DOM elements
        this.DOM = {
            trigger: element,
            container: document.querySelector(`#${container}`),
        }

        // Config
        this.config = {
            action,
            perPage,
            template,
            nonce,
            postType,
            taxonomy,
            term,
        }

        // State
        this.page = 1;
        this.loading = false;

        this.events();
    }

    events() {
        this.DOM.trigger?.addEventListener('click', () => this.loadMore());
    }

    async loadMore() {
        if (this.loading) return;

        this.loading = true;

        this.onStart({ page: this.page });

        const formData = new FormData();
        formData.append('action', this.config.action);
        formData.append('page', this.page);
        formData.append('per_page', this.config.perPage);
        formData.append('template', this.config.template);
        formData.append('post_type', this.config.postType);

        // Security nonce (from data attribute or global)
        const nonce = this.config.nonce || window.base_wp_api?.nonces?.[this.config.action] || '';
        if (nonce) {
            formData.append('nonce', nonce);
        }

        // Optional taxonomy filter
        if (this.config.taxonomy && this.config.term) {
            formData.append('taxonomy', this.config.taxonomy);
            formData.append('term', this.config.term);
        }

        try {
            const response = await fetch(window.base_wp_api.ajax_url, {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                const { html, has_more, page, total } = result.data;
                const previousCount = this.DOM.container.querySelectorAll('[data-card]').length;

                this.DOM.container.insertAdjacentHTML('beforeend', html);

                const allCards = this.DOM.container.querySelectorAll('[data-card]');
                const newCards = Array.from(allCards).slice(previousCount);

                this.page++;

                this.onSuccess({ cards: newCards, page: this.page, hasMore: has_more, total });

                if (!has_more) {
                    this.DOM.trigger.style.display = 'none';
                }
            } else {
                // Handle error response
                const error = result.error || { message: 'Unknown error' };
                this.onError({ code: error.code, message: error.message });
            }
        } catch (error) {
            console.error('LoadMore error:', error);
            this.onError({ code: 'network_error', message: error.message });
        }

        this.loading = false;
    }

    onStart({ page }) {
        // Callback before fetch - override in subclass or assign
        this.DOM.trigger.classList.add('is-loading');
    }

    onSuccess({ cards, page, hasMore, total }) {
        this.DOM.trigger.classList.remove('is-loading');

        gsap.from(cards, {
            opacity: 0,
            y: 20,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power2.out',
        });
    }

    onError({ code, message }) {
        this.DOM.trigger.classList.remove('is-loading');
        console.error(`LoadMore [${code}]: ${message}`);
    }

    destroy() {
        this.DOM = null;
    }
}

export default LoadMore;
