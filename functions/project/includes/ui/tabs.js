/**
 * c--tabs
 * Generic tab component for WordPress admin pages.
 *
 * Supports:
 * - Multiple independent tab groups on the same page
 * - URL hash linking: ?tab=taxonomies or #taxonomies
 * - Updates URL on tab click without page reload
 *
 * Usage:
 *   <ul class="c--tabs__nav">
 *     <li class="c--tabs__nav-item is-active" data-tab="one">Tab One</li>
 *     <li class="c--tabs__nav-item" data-tab="two">Tab Two</li>
 *   </ul>
 *   <div class="c--tabs__content">
 *     <div class="c--tabs__panel is-active" data-panel="one">...</div>
 *     <div class="c--tabs__panel" data-panel="two">...</div>
 *   </div>
 */
(function () {
    function getTabFromURL() {
        var params = new URLSearchParams(window.location.search);
        return params.get('tab') || window.location.hash.replace('#', '') || null;
    }

    function updateURL(tab) {
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        url.hash = '';
        history.replaceState(null, '', url);
    }

    function activateTab(nav, content, target) {
        var items = nav.querySelectorAll('.c--tabs__nav-item');
        var panels = content.querySelectorAll('.c--tabs__panel');

        items.forEach(function (i) { i.classList.remove('is-active'); });
        panels.forEach(function (p) { p.classList.remove('is-active'); });

        var activeItem = nav.querySelector('[data-tab="' + target + '"]');
        var activePanel = content.querySelector('[data-panel="' + target + '"]');

        if (activeItem) activeItem.classList.add('is-active');
        if (activePanel) activePanel.classList.add('is-active');

        return !!(activeItem && activePanel);
    }

    function initTabs(nav) {
        var content = nav.nextElementSibling;
        if (!content || !content.classList.contains('c--tabs__content')) return;

        var items = nav.querySelectorAll('.c--tabs__nav-item');

        // Activate tab from URL if present
        var urlTab = getTabFromURL();
        if (urlTab) {
            activateTab(nav, content, urlTab);
        }

        items.forEach(function (item) {
            item.addEventListener('click', function () {
                var target = this.getAttribute('data-tab');
                activateTab(nav, content, target);
                updateURL(target);
            });
        });
    }

    document.querySelectorAll('.c--tabs__nav').forEach(initTabs);
})();
