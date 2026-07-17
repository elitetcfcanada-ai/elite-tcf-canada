(function () {
    'use strict';

    var notificationIcon = document.getElementById('showNotifications');
    if (!notificationIcon) return;

    var notificationsPanel = null;
    var notificationsEndpoint = window.TCF_NOTIFICATIONS_ENDPOINT || 'notifications_api.php';

    function createPanel() {
        if (notificationsPanel) return notificationsPanel;

        var panel = document.createElement('div');
        panel.className = 'tcf-notifications-panel';
        panel.id = 'tcf-notifications-panel';
        panel.innerHTML = `
            <div class="tcf-notifications-header">
                <h3>Notifications</h3>
                <button type="button" class="tcf-notifications-close" id="tcf-notifications-close">&times;</button>
            </div>
            <div class="tcf-notifications-content" id="tcf-notifications-content">
                <div class="tcf-notifications-loading">Chargement...</div>
            </div>
            <div class="tcf-notifications-footer">
                <button type="button" class="tcf-notifications-mark-read" id="tcf-notifications-mark-read">Tout marquer comme lu</button>
            </div>
        `;

        document.body.appendChild(panel);

        // Event listeners
        panel.querySelector('#tcf-notifications-close').addEventListener('click', closePanel);
        panel.querySelector('#tcf-notifications-mark-read').addEventListener('click', markAllRead);

        notificationsPanel = panel;
        return panel;
    }

    function openPanel() {
        var panel = createPanel();
        panel.classList.add('tcf-notifications-panel--open');
        loadNotifications();
    }

    function closePanel() {
        if (notificationsPanel) {
            notificationsPanel.classList.remove('tcf-notifications-panel--open');
        }
    }

    function loadNotifications() {
        var content = document.getElementById('tcf-notifications-content');
        if (!content) return;

        content.innerHTML = '<div class="tcf-notifications-loading">Chargement...</div>';

        fetch(notificationsEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'list' })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                content.innerHTML = '<div class="tcf-notifications-error">Erreur lors du chargement des notifications.</div>';
                return;
            }

            var notifications = data.notifications || [];
            if (notifications.length === 0) {
                content.innerHTML = '<div class="tcf-notifications-empty">Aucune notification.</div>';
                return;
            }

            var html = '';
            notifications.forEach(function (notif) {
                var isUnread = !notif.is_read;
                var link = notif.deep_link || '#';
                var date = new Date(notif.created_at);
                var dateStr = date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });

                html += `
                    <a href="${link}" class="tcf-notification-item ${isUnread ? 'tcf-notification-item--unread' : ''}" data-id="${notif.id}">
                        <div class="tcf-notification-content">
                            <div class="tcf-notification-title">${escapeHtml(notif.title)}</div>
                            <div class="tcf-notification-message">${escapeHtml(notif.content)}</div>
                            <div class="tcf-notification-date">${dateStr}</div>
                        </div>
                        ${isUnread ? '<div class="tcf-notification-indicator"></div>' : ''}
                    </a>
                `;
            });

            content.innerHTML = html;

            // Add click handlers for marking as read
            content.querySelectorAll('.tcf-notification-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    var id = item.getAttribute('data-id');
                    if (id) {
                        markAsRead(id);
                    }
                });
            });

            // Update badge
            updateBadge(data.unread_count || 0);
        })
        .catch(function (error) {
            content.innerHTML = '<div class="tcf-notifications-error">Erreur de connexion.</div>';
        });
    }

    function markAsRead(id) {
        fetch(notificationsEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'mark_read', id: id })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                var item = document.querySelector('.tcf-notification-item[data-id="' + id + '"]');
                if (item) {
                    item.classList.remove('tcf-notification-item--unread');
                    var indicator = item.querySelector('.tcf-notification-indicator');
                    if (indicator) indicator.remove();
                }
                updateBadge();
            }
        })
        .catch(function (error) {
            console.error('Error marking notification as read:', error);
        });
    }

    function markAllRead() {
        fetch(notificationsEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'mark_all_read' })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                var items = document.querySelectorAll('.tcf-notification-item--unread');
                items.forEach(function (item) {
                    item.classList.remove('tcf-notification-item--unread');
                    var indicator = item.querySelector('.tcf-notification-indicator');
                    if (indicator) indicator.remove();
                });
                updateBadge(0);
            }
        })
        .catch(function (error) {
            console.error('Error marking all notifications as read:', error);
        });
    }

    function updateBadge(count) {
        var badge = document.querySelector('.notification-badge');
        if (count !== undefined) {
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'notification-badge';
                    notificationIcon.appendChild(badge);
                }
                badge.textContent = count;
                badge.style.display = 'block';
            } else {
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        } else {
            // Reload to get current count
            fetch(notificationsEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'list' })
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    updateBadge(data.unread_count || 0);
                }
            })
            .catch(function (error) {
                console.error('Error updating badge:', error);
            });
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Toggle panel on icon click
    notificationIcon.addEventListener('click', function (e) {
        e.preventDefault();
        if (notificationsPanel && notificationsPanel.classList.contains('tcf-notifications-panel--open')) {
            closePanel();
        } else {
            openPanel();
        }
    });

    // Close panel when clicking outside
    document.addEventListener('click', function (e) {
        if (!notificationsPanel || !notificationsPanel.classList.contains('tcf-notifications-panel--open')) return;
        if (e.target.closest('.tcf-notifications-panel')) return;
        if (e.target.closest('#showNotifications')) return;
        closePanel();
    });

    // Periodically update badge
    setInterval(function () {
        updateBadge();
    }, 60000); // Every minute
})();
