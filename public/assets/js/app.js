/**
 * QR Attendance System - Application JavaScript
 * Fonctionnalités communes et utilitaires
 */

class QRAttendanceApp {
    constructor() {
        this.baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.showWelcomeAnimation();
    }

    setupEventListeners() {
        // Gestion des formulaires
        document.addEventListener('submit', this.handleFormSubmit.bind(this));
        
        // Gestion des boutons avec loading
        document.addEventListener('click', this.handleButtonClick.bind(this));
        
        // Auto-refresh pour certaines pages
        if (window.location.pathname.includes('dashboard')) {
            this.setupAutoRefresh();
        }
    }

    initializeComponents() {
        // Initialiser les tooltips
        this.initTooltips();
        
        // Initialiser les modales
        this.initModals();
        
        // Initialiser les animations au scroll
        this.initScrollAnimations();
    }

    showWelcomeAnimation() {
        // Animation d'entrée pour les éléments
        const elements = document.querySelectorAll('.animate-on-load');
        elements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('animate-fadeIn');
            }, index * 100);
        });
    }

    handleFormSubmit(event) {
        const form = event.target;
        if (!form.classList.contains('ajax-form')) return;

        event.preventDefault();
        this.submitFormAjax(form);
    }

    async submitFormAjax(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Afficher le loading
        this.setButtonLoading(submitBtn, true);
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action || window.location.href, {
                method: form.method || 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.ok) {
                this.showNotification('Succès !', result.message || 'Opération réussie', 'success');
                if (result.redirect) {
                    setTimeout(() => window.location.href = result.redirect, 1500);
                }
            } else {
                this.showNotification('Erreur', result.error || 'Une erreur est survenue', 'error');
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
            this.showNotification('Erreur', 'Erreur de communication avec le serveur', 'error');
        } finally {
            this.setButtonLoading(submitBtn, false, originalText);
        }
    }

    handleButtonClick(event) {
        const button = event.target.closest('button');
        if (!button) return;

        // Gestion des boutons avec confirmation
        if (button.dataset.confirm) {
            event.preventDefault();
            this.showConfirmDialog(button.dataset.confirm, () => {
                this.executeButtonAction(button);
            });
        }

        // Gestion des boutons AJAX
        if (button.dataset.ajax) {
            event.preventDefault();
            this.executeAjaxAction(button);
        }
    }

    async executeAjaxAction(button) {
        const url = button.dataset.ajax;
        const method = button.dataset.method || 'GET';
        
        this.setButtonLoading(button, true);
        
        try {
            const response = await fetch(url, { method });
            const result = await response.json();
            
            if (result.ok) {
                this.showNotification('Succès', result.message, 'success');
                if (result.reload) {
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
                this.showNotification('Erreur', result.error, 'error');
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
            this.showNotification('Erreur', 'Erreur de communication', 'error');
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    setButtonLoading(button, loading, originalText = null) {
        if (loading) {
            button.disabled = true;
            button.innerHTML = `
                <div class="loading-spinner"></div>
                <span>Chargement...</span>
            `;
        } else {
            button.disabled = false;
            button.innerHTML = originalText || button.dataset.originalText || button.innerHTML.replace(/<div class="loading-spinner"><\/div>\s*<span>.*<\/span>/, '');
        }
    }

    showNotification(title, message, type = 'info') {
        // Créer la notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} animate-fadeIn`;
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    ${this.getNotificationIcon(type)}
                </div>
                <div class="notification-text">
                    <div class="notification-title">${title}</div>
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
        `;

        // Ajouter au DOM
        let container = document.getElementById('notifications');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications';
            container.className = 'notifications-container';
            document.body.appendChild(container);
        }

        container.appendChild(notification);

        // Auto-remove après 5 secondes
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>`,
            error: `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>`,
            warning: `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
            </svg>`,
            info: `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
            </svg>`
        };
        return icons[type] || icons.info;
    }

    showConfirmDialog(message, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'modal-backdrop';
        modal.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Confirmation</h3>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="this.closest('.modal-backdrop').remove()">
                        Annuler
                    </button>
                    <button class="btn btn-danger" id="confirm-btn">
                        Confirmer
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector('#confirm-btn').onclick = () => {
            modal.remove();
            onConfirm();
        };

        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }

    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', this.showTooltip.bind(this));
            element.addEventListener('mouseleave', this.hideTooltip.bind(this));
        });
    }

    showTooltip(event) {
        const element = event.target;
        const text = element.dataset.tooltip;
        
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';

        element._tooltip = tooltip;
    }

    hideTooltip(event) {
        const element = event.target;
        if (element._tooltip) {
            element._tooltip.remove();
            delete element._tooltip;
        }
    }

    initModals() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal]')) {
                e.preventDefault();
                const modalId = e.target.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                }
            }

            if (e.target.matches('.modal-backdrop') || e.target.matches('.modal-close')) {
                const modal = e.target.closest('.modal-backdrop');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        });
    }

    initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeIn');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    setupAutoRefresh() {
        // Rafraîchir les données toutes les 30 secondes
        setInterval(() => {
            this.refreshDashboardData();
        }, 30000);
    }

    async refreshDashboardData() {
        try {
            const response = await fetch(`${this.baseUrl}/api/dashboard`);
            const data = await response.json();
            
            if (data.ok) {
                this.updateDashboardStats(data.stats);
            }
        } catch (error) {
            console.error('Erreur lors du rafraîchissement:', error);
        }
    }

    updateDashboardStats(stats) {
        // Mettre à jour les statistiques dans le dashboard
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                element.textContent = stats[key];
                element.classList.add('animate-pulse');
                setTimeout(() => element.classList.remove('animate-pulse'), 1000);
            }
        });
    }

    // Utilitaires
    formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    }

    formatTime(time) {
        return new Intl.DateTimeFormat('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(`1970-01-01T${time}`));
    }

    formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Styles pour les notifications
const notificationStyles = `
.notifications-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
}

.notification {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--secondary-200);
    overflow: hidden;
    transform: translateX(100%);
    transition: all 0.3s ease;
}

.notification.animate-fadeIn {
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    gap: 12px;
}

.notification-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
}

.notification-success .notification-icon {
    color: var(--success-600);
}

.notification-error .notification-icon {
    color: var(--error-600);
}

.notification-warning .notification-icon {
    color: var(--warning-600);
}

.notification-info .notification-icon {
    color: var(--primary-600);
}

.notification-text {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: var(--secondary-900);
    margin-bottom: 4px;
}

.notification-message {
    font-size: 13px;
    color: var(--secondary-600);
    line-height: 1.4;
}

.notification-close {
    background: none;
    border: none;
    color: var(--secondary-400);
    cursor: pointer;
    padding: 0;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    transition: color 0.2s;
}

.notification-close:hover {
    color: var(--secondary-600);
}

.tooltip {
    position: absolute;
    background: var(--secondary-800);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 9999;
    pointer-events: none;
    opacity: 0;
    animation: fadeIn 0.2s ease forwards;
}

.tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: var(--secondary-800);
}

@media (max-width: 768px) {
    .notifications-container {
        left: 20px;
        right: 20px;
        max-width: none;
    }
    
    .notification {
        transform: translateY(-100%);
    }
    
    .notification.animate-fadeIn {
        transform: translateY(0);
    }
}
`;

// Injecter les styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);

// Initialiser l'application au chargement
document.addEventListener('DOMContentLoaded', () => {
    window.qrApp = new QRAttendanceApp();
});

// Export pour utilisation dans d'autres scripts
window.QRAttendanceApp = QRAttendanceApp;