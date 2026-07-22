(function () {
    if (window.__tcfMobileNavInit) return;
    window.__tcfMobileNavInit = true;

    var MOBILE_MAX = 991;
    var nav = document.getElementById('tcfMobileNav');
    if (!nav) return;

    var overlay = document.getElementById('tcfMobileSheetOverlay');
    var openSheet = null;
    var openTrigger = null;
    var lockScrollY = 0;

    function isMobile() {
        return window.innerWidth <= MOBILE_MAX;
    }

    function syncMobileNavClass() {
        document.body.classList.toggle('tcf-has-mobile-nav', isMobile());
    }

    function allNavItems() {
        return [].slice.call(nav.querySelectorAll('.tcf-mobile-nav__item'));
    }

    function clearActive() {
        allNavItems().forEach(function (item) {
            item.classList.remove('is-active');
        });
    }

    function setActiveItem(item) {
        if (!item) return;
        clearActive();
        item.classList.add('is-active');
    }

    function restorePageActive() {
        clearActive();
        allNavItems().forEach(function (item) {
            if (item.getAttribute('data-tcf-page-active') === '1') {
                item.classList.add('is-active');
            }
        });
    }

    function markPageActiveFromDom() {
        allNavItems().forEach(function (item) {
            item.removeAttribute('data-tcf-page-active');
            if (item.classList.contains('is-active')) {
                item.setAttribute('data-tcf-page-active', '1');
            }
        });
    }

    markPageActiveFromDom();

    function triggerProfile() {
        var profileBtn = document.getElementById('showProfile');
        if (profileBtn) {
            profileBtn.click();
            return true;
        }
        var login = nav.getAttribute('data-tcf-login');
        if (login) {
            window.location.assign(login);
            return true;
        }
        return false;
    }

    function setTriggerExpanded(btn, expanded) {
        if (!btn) return;
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function lockBodyScroll() {
        lockScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        document.body.classList.add('tcf-mobile-sheet-open');
        document.body.style.top = '-' + lockScrollY + 'px';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.left = '0';
        document.body.style.right = '0';
    }

    function unlockBodyScroll() {
        document.body.classList.remove('tcf-mobile-sheet-open');
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        document.body.style.left = '';
        document.body.style.right = '';
        window.scrollTo(0, lockScrollY || 0);
    }

    function closeSheetQuiet() {
        if (!openSheet) return;
        openSheet.classList.remove('is-open');
        openSheet.hidden = true;
        if (overlay) {
            overlay.classList.remove('is-visible');
            overlay.hidden = true;
        }
        setTriggerExpanded(openTrigger, false);
        openSheet = null;
        openTrigger = null;
        unlockBodyScroll();
    }

    function closeSheet() {
        if (!openSheet) {
            restorePageActive();
            return;
        }
        closeSheetQuiet();
        restorePageActive();
    }

    function showSheet(sheetId, trigger) {
        if (!isMobile()) return;
        var sheet = document.getElementById(sheetId);
        if (!sheet || !overlay) return;

        if (openSheet && openSheet !== sheet) {
            openSheet.classList.remove('is-open');
            openSheet.hidden = true;
            setTriggerExpanded(openTrigger, false);
        }

        setActiveItem(trigger);
        openSheet = sheet;
        openTrigger = trigger || null;

        sheet.hidden = false;
        sheet.classList.add('is-open');
        overlay.hidden = false;
        overlay.classList.add('is-visible');
        setTriggerExpanded(trigger, true);
        lockBodyScroll();
    }

    /**
     * Navigation forcée : ferme le sheet (body unlock) puis redirige.
     * Évite les liens ancre qui « ne font rien » quand le scroll est verrouillé.
     */
    function navigateTo(href) {
        if (!href) return false;
        closeSheetQuiet();

        try {
            var target = new URL(href, window.location.href);
            var current = new URL(window.location.href);
            var samePath =
                target.pathname.replace(/\/+$/, '') === current.pathname.replace(/\/+$/, '');
            var sameSearch = target.search === current.search;

            if (samePath && sameSearch && target.hash) {
                var id = decodeURIComponent(target.hash.slice(1));
                var el = id ? document.getElementById(id) : null;
                if (el) {
                    if (history && history.replaceState) {
                        history.replaceState(null, '', target.hash);
                    } else {
                        window.location.hash = target.hash;
                    }
                    window.requestAnimationFrame(function () {
                        try {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } catch (e) {
                            el.scrollIntoView(true);
                        }
                    });
                    restorePageActive();
                    return true;
                }
            }

            window.location.assign(target.href);
            return true;
        } catch (err) {
            window.location.href = href;
            return true;
        }
    }

    function handleNavGoClick(e) {
        var link = e.target.closest && e.target.closest('[data-tcf-nav-go]');
        if (!link) return;
        var href = link.getAttribute('href');
        if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
        e.preventDefault();
        e.stopPropagation();
        navigateTo(href);
    }

    document.addEventListener('click', handleNavGoClick, true);

    nav.addEventListener('click', function (e) {
        var openBtn = e.target.closest && e.target.closest('[data-tcf-sheet-open]');
        if (openBtn) {
            e.preventDefault();
            e.stopPropagation();
            var targetId = openBtn.getAttribute('data-tcf-sheet-open');
            if (!targetId) return;
            if (openSheet && openSheet.id === targetId) {
                closeSheet();
            } else {
                showSheet(targetId, openBtn);
            }
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeSheet);
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('[data-tcf-sheet-close]')) {
            e.preventDefault();
            closeSheet();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSheet();
    });

    var sheetProfile = document.getElementById('tcfMobileSheetProfile');
    if (sheetProfile) {
        sheetProfile.addEventListener('click', function (e) {
            e.preventDefault();
            closeSheet();
            window.setTimeout(triggerProfile, 40);
        });
    }

    syncMobileNavClass();
    window.addEventListener('resize', function () {
        syncMobileNavClass();
        if (!isMobile()) closeSheet();
    });
})();
