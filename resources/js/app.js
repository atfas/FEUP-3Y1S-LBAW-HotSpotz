import './bootstrap';

/**
 * Notifications functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    const notificationCount = document.getElementById('notification-count');
    const notificationsDropdownContent = document.getElementById('notifications-dropdown-content');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    
    if (!userIdMeta) {
        // User not logged in
        return;
    }
    
    const userId = userIdMeta.content;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Function to update the notification badge count
    function updateBadgeCount(count) {
        if (count > 0) {
            notificationCount.textContent = count > 99 ? '99+' : count;
            notificationCount.style.display = 'inline';
            markAllReadBtn.style.display = 'inline';
        } else {
            notificationCount.style.display = 'none';
            markAllReadBtn.style.display = 'none';
        }
    }
    
    // Function to get notification icon based on type
    function getNotificationIcon(type) {
        const icons = {
            'BookingConfirmed': '<i class="fa-solid fa-calendar-check text-success me-2"></i>',
            'BookingCanceled': '<i class="fa-solid fa-calendar-xmark text-danger me-2"></i>',
            'PaymentReceived': '<i class="fa-solid fa-credit-card text-info me-2"></i>',
            'Remainder': '<i class="fa-solid fa-clock text-warning me-2"></i>',
            'MessageReceived': '<i class="fa-solid fa-message text-primary me-2"></i>',
            'ReviewLeft': '<i class="fa-solid fa-star text-warning me-2"></i>',
            'AdminNotice': '<i class="fa-solid fa-shield text-secondary me-2"></i>',
        };
        return icons[type] || '<i class="fa-solid fa-bell text-muted me-2"></i>';
    }
    
    // Function to render notification item in dropdown
    function renderNotificationItem(notification) {
        const isUnread = !notification.is_read;
        return `
            <li class="dropdown-item-text ${isUnread ? 'bg-light' : ''}" data-notification-id="${notification.id}">
                <div class="d-flex align-items-start">
                    ${getNotificationIcon(notification.type)}
                    <div class="flex-grow-1">
                        <p class="mb-1 small ${isUnread ? '' : 'text-muted'}">${notification.content}</p>
                        <small class="text-muted">${notification.created_at}</small>
                    </div>
                </div>
            </li>
        `;
    }
    
    // Function to load recent notifications
    function loadRecentNotifications() {
        fetch('/notifications/recent', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateBadgeCount(data.unread_count);
            
            if (data.notifications.length === 0) {
                notificationsDropdownContent.innerHTML = `
                    <li class="text-center py-3">
                        <span class="text-muted small">No notifications</span>
                    </li>
                `;
            } else {
                notificationsDropdownContent.innerHTML = data.notifications
                    .map(n => renderNotificationItem(n))
                    .join('');
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationsDropdownContent.innerHTML = `
                <li class="text-center py-3">
                    <span class="text-muted small">Error loading notifications</span>
                </li>
            `;
        });
    }
    
    // Load notifications on page load
    loadRecentNotifications();
    
    // Load notifications when dropdown is opened
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    if (notificationsDropdown) {
        notificationsDropdown.addEventListener('show.bs.dropdown', function() {
            loadRecentNotifications();
        });
    }
    
    // Mark all as read button
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBadgeCount(0);
                    loadRecentNotifications();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
    
    // Real-time notifications with Laravel Echo
    if (typeof window.Echo !== 'undefined') {
        window.Echo.private(`user.${userId}`)
            .listen('.booking.confirmed', (data) => {
                handleNewNotification(data.notification);
            })
            .listen('.booking.cancelled', (data) => {
                handleNewNotification(data.notification);
            })
            .listen('.booking.new', (data) => {
                handleNewNotification(data.notification);
            });
    }
    
    // Function to handle new notifications from Pusher
    function handleNewNotification(notification) {
        // Update badge count
        const currentCount = parseInt(notificationCount.textContent) || 0;
        updateBadgeCount(currentCount + 1);
        
        // Show toast notification
        showNotificationToast(notification);
        
        // Refresh dropdown content
        loadRecentNotifications();
    }
    
    // Function to show toast notification
    function showNotificationToast(notification) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('notification-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'notification-toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1100';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    ${getNotificationIcon(notification.type)}
                    <strong class="me-auto">New Notification</strong>
                    <small>${notification.created_at}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${notification.content}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
        
        // Remove toast from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
});
