(function () {
    if (window.__tcfMobileNavInit) return;
    window.__tcfMobileNavInit = true;

    var MOBILE_MAX = 991;
    var nav = document.getElementById('tcfMobileNav');
    if (!nav) return;

    var overlay = document.getElementById('tcfMobileSheetOverlay');
    var openSheet = null;
    var openTrigger = null;

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
        return false;
    }

    function setTriggerExpanded(btn, expanded) {
        if (!btn) return;
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function closeSheet() {
        if (!openSheet) {
            restorePageActive();
            return;
        }
        openSheet.classList.remove('is-open');
        openSheet.hidden = true;
        if (overlay) {
            overlay.classList.remove('is-visible');
            overlay.hidden = true;
        }
        setTriggerExpanded(openTrigger, false);
        openSheet = null;
        openTrigger = null;
        document.body.classList.remove('tcf-mobile-sheet-open');
        restorePageActive();
    }

    function showSheet(sheetId, trigger) {
        if (!isMobile()) return;
        var sheet = document.getElementById(sheetId);
        if (!sheet || !overlay) return;

        if (openSheet && openSheet !== sheet) {
            closeSheet();
        }

        setActiveItem(trigger);
        openSheet = sheet;
        openTrigger = trigger || null;
        sheet.hidden = false;
        requestAnimationFrame(function () {
            sheet.classList.add('is-open');
        });
        overlay.hidden = false;
        requestAnimationFrame(function () {
            overlay.classList.add('is-visible');
        });
        setTriggerExpanded(trigger, true);
        document.body.classList.add('tcf-mobile-sheet-open');
    }

    nav.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('a.tcf-mobile-nav__item');
        if (link) {
            setActiveItem(link);
            closeSheet();
            return;
        }

        var openBtn = e.target.closest && e.target.closest('[data-tcf-sheet-open]');
        if (openBtn) {
            e.preventDefault();
            var targetId = openBtn.getAttribute('data-tcf-sheet-open');
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
            triggerProfile();
        });
    }

    syncMobileNavClass();
    window.addEventListener('resize', function () {
        syncMobileNavClass();
        if (!isMobile()) closeSheet();
    });
})();
