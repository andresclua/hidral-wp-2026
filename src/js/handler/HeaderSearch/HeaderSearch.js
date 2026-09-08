/**
 * HeaderSearch — feature class.
 *
 * Wires one `.js--header-search` element to the
 * POST /wp-json/wp/v2/tf_api/search endpoint backed by the Search_Indexer
 * framework class. Debounced fetch, dropdown of grouped results.
 *
 * Instantiated through the Manager by Handler.js (see CoreHandler pattern).
 */


class HeaderSearch {
    constructor(payload = {}) {
        const { element } = payload;
        if (!element) {
            console.error("HeaderSearch: element is required");
            return;
        }

        this.DOM = {
            element,
            input:   element.querySelector(".js--header-search-input"),
            results: element.querySelector(".js--header-search-results"),
        };

        this.min_chars   = 2;
        this.debounce_ms = 250;

        if (!this.DOM.input || !this.DOM.results) {
            console.error("HeaderSearch: input or results container missing");
            return;
        }

        this.endpoint    = this._buildEndpoint();
        this.lastKeyword = "";
        this.debouncedRun = this._debounce(this._run.bind(this), this.debounce_ms);

        this.events();
    }

    events() {
        this.handleInput          = () => {
            this.lastKeyword = this.DOM.input.value.trim();
            this.debouncedRun(this.lastKeyword);
        };
        this.handleFocus          = () => {
            if (this.DOM.results.innerHTML.trim() !== "") this.DOM.results.hidden = false;
        };
        this.handleDocumentClick  = (e) => {
            if (!this.DOM.element.contains(e.target)) this.DOM.results.hidden = true;
        };

        this.DOM.input.addEventListener("input", this.handleInput);
        this.DOM.input.addEventListener("focus", this.handleFocus);
        document.addEventListener("click", this.handleDocumentClick);
    }

    destroy() {
        this.DOM.input?.removeEventListener("input", this.handleInput);
        this.DOM.input?.removeEventListener("focus", this.handleFocus);
        document.removeEventListener("click", this.handleDocumentClick);
        this.DOM = null;
    }

    /* -------------------------------------------------------------------- */
    /* Internals                                                            */
    /* -------------------------------------------------------------------- */

    _buildEndpoint() {
        const root = window.base_wp_api?.root_url || window.location.origin;
        return `${root.replace(/\/$/, "")}/wp-json/wp/v2/tf_api/search`;
    }

    _debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    async _run(keyword) {
        if (keyword.length < this.min_chars) {
            this.DOM.results.hidden = true;
            this.DOM.results.innerHTML = "";
            return;
        }
        try {
            const items = await this._fetch(keyword);
            if (keyword !== this.lastKeyword) return; // stale
            this.DOM.results.innerHTML = this._renderResults(this._groupByType(items));
            this.DOM.results.hidden = false;
        } catch (e) {
            this.DOM.results.innerHTML = `<div class="c--header-search__content">Search failed.</div>`;
            this.DOM.results.hidden = false;
        }
    }

    async _fetch(keyword) {
        const res = await fetch(this.endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "same-origin",
            body: JSON.stringify({ keyword }),
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        return Array.isArray(json?.data) ? json.data : [];
    }

    _groupByType(items) {
        return items.reduce((acc, item) => {
            (acc[item.post_type] = acc[item.post_type] || []).push(item);
            return acc;
        }, {});
    }

    _renderResults(grouped) {
        const types = Object.keys(grouped);
        if (!types.length) {
            return `<div class="c--header-search__content">No results.</div>`;
        }
        return types.map(type => {
            const items = grouped[type].map(it => `
                <a class="c--header-search__link" href="${it.permalink}">
                    <span class="c--header-search__link__title">${it.post_title}</span>
                </a>
            `).join("");
            return `
                <div class="c--header-search__list-item">
                    <h4 class="c--header-search__list-item__title">${type}</h4>
                    ${items}
                </div>
            `;
        }).join("");
    }
}

export default HeaderSearch;
