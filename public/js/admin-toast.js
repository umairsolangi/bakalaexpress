/**
 * Bakala Express Admin Toast Notifications
 * Modern toast notification system
 */

// Main Toast class
class Toast {
  constructor() {
    this.container = document.getElementById('toast-container');
    
    // Create container if it doesn't exist
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'toast-container';
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  }
  
  /**
   * Show a toast notification
   * @param {Object} options - Toast options
   * @param {string} options.type - Toast type (success, error, warning, info)
   * @param {string} options.title - Toast title (optional)
   * @param {string} options.message - Toast message
   * @param {number} options.duration - Duration in ms (default: 5000)
   */
  show(options) {
    const { type = 'info', title = '', message, duration = 5000 } = options;
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Icon based on type
    let icon = '';
    switch (type) {
      case 'success':
        icon = 'fa-check-circle';
        break;
      case 'error':
        icon = 'fa-exclamation-circle';
        break;
      case 'warning':
        icon = 'fa-exclamation-triangle';
        break;
      case 'info':
      default:
        icon = 'fa-info-circle';
    }
    
    // Build toast content
    toast.innerHTML = `
      <div class="toast-icon">
        <i class="fas ${icon}"></i>
      </div>
      <div class="toast-content">
        ${title ? `<div class="toast-title">${title}</div>` : ''}
        <div class="toast-message">${message}</div>
      </div>
      <button class="toast-close" aria-label="Close">
        <i class="fas fa-times"></i>
      </button>
      <div class="toast-progress"></div>
    `;
    
    // Add to container
    this.container.appendChild(toast);
    
    // Set progress bar animation
    const progressBar = toast.querySelector('.toast-progress');
    progressBar.style.animationDuration = `${duration}ms`;
    
    // Set auto-remove
    const timeout = setTimeout(() => {
      this.removeToast(toast);
    }, duration);
    
    // Close button handler
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
      clearTimeout(timeout);
      this.removeToast(toast);
    });
    
    return toast;
  }
  
  /**
   * Remove a toast with animation
   * @param {HTMLElement} toast - Toast element to remove
   */
  removeToast(toast) {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(30px)';
    
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }
  
  /**
   * Show a success toast
   * @param {string} message - Toast message
   * @param {string} title - Toast title (optional)
   */
  success(message, title = '') {
    return this.show({ type: 'success', title, message });
  }
  
  /**
   * Show an error toast
   * @param {string} message - Toast message
   * @param {string} title - Toast title (optional)
   */
  error(message, title = '') {
    return this.show({ type: 'error', title, message });
  }
  
  /**
   * Show a warning toast
   * @param {string} message - Toast message
   * @param {string} title - Toast title (optional)
   */
  warning(message, title = '') {
    return this.show({ type: 'warning', title, message });
  }
  
  /**
   * Show an info toast
   * @param {string} message - Toast message
   * @param {string} title - Toast title (optional)
   */
  info(message, title = '') {
    return this.show({ type: 'info', title, message });
  }
}

// Initialize toast system
const toast = new Toast();

// Handle form submissions for approval/rejection with toast notifications
document.addEventListener('DOMContentLoaded', function() {
  // Find all approval forms
  const approvalForms = document.querySelectorAll('.approval-form');
  
  approvalForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      // Allow the form to submit normally
      
      // Store the action type from data attribute or form class
      const isApproval = form.classList.contains('approve-form');
      const entityType = form.getAttribute('data-entity-type') || 'item';
      
      // Show a toast based on action type
      if (isApproval) {
        toast.success(`${entityType} has been approved successfully!`, 'Approved');
      } else {
        toast.error(`${entityType} has been rejected.`, 'Rejected');
      }
    });
  });
  
  // Check for success/error messages in URL and show toast
  const urlParams = new URLSearchParams(window.location.search);
  const successMsg = urlParams.get('success');
  const errorMsg = urlParams.get('error');
  
  if (successMsg) {
    toast.success(decodeURIComponent(successMsg));
  }
  
  if (errorMsg) {
    toast.error(decodeURIComponent(errorMsg));
  }
  
  // Check for flash messages and display them
  if (typeof flashMessages !== 'undefined') {
    if (flashMessages.success) {
      toast.success(flashMessages.success);
    }
    if (flashMessages.error) {
      toast.error(flashMessages.error);
    }
    if (flashMessages.warning) {
      toast.warning(flashMessages.warning);
    }
    if (flashMessages.info) {
      toast.info(flashMessages.info);
    }
  }
}); 