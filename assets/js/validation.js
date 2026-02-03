

const Validator = {
    // Configuration
    config: {
        errorClass: 'v-input-invalid',
        messageClass: 'v-error-message',
        showIcons: true,
        iconHtml: '<i class="fa-solid fa-circle-exclamation"></i>'
    },

    // Initialize all forms with [data-validate]
    init() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            // Disable native validation
            form.setAttribute('novalidate', '');

            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });

            // Real-time validation on blur
            form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('blur', () => this.validateField(field));
                field.addEventListener('input', () => {
                    if (field.classList.contains(this.config.errorClass)) {
                        this.validateField(field);
                    }
                });
            });
        });
    },

    // Validate entire form
    validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, textarea, select');

        // Validate in reverse to focus the first error
        for (let i = fields.length - 1; i >= 0; i--) {
            if (!this.validateField(fields[i])) {
                isValid = false;
                fields[i].focus();
            }
        }

        if (!isValid) {
            form.classList.remove('v-shake');
            void form.offsetWidth; // Trigger reflow
            form.classList.add('v-shake');
        }

        return isValid;
    },

    // Validate single field
    validateField(field) {
        const rules = field.getAttribute('data-rules');
        if (!rules) return true;

        const value = field.value.trim();
        const ruleList = rules.split('|');
        this.clearError(field);

        for (let rule of ruleList) {
            const [ruleName, ruleValue] = rule.split(':');

            // Required check
            if (ruleName === 'required') {
                const isCheckbox = field.type === 'checkbox' || field.type === 'radio';
                if ((isCheckbox && !field.checked) || (!isCheckbox && !value)) {
                    return this.showError(field, 'This field is required');
                }
            }

            if (value) {
                // Email check
                if (ruleName === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    return this.showError(field, 'Please enter a valid email address');
                }

                // Min length
                if (ruleName === 'min' && value.length < parseInt(ruleValue)) {
                    return this.showError(field, `Must be at least ${ruleValue} characters`);
                }

                // Phone check (basic)
                if (ruleName === 'phone' && !/^[0-9+()-\s]{7,}$/.test(value)) {
                    return this.showError(field, 'Please enter a valid phone number');
                }
            }
        }

        return true;
    },

    // Show error message
    showError(field, message) {
        field.classList.add(this.config.errorClass);

        // Find the best anchor for absolute positioning
        // Usually the input-wrapper or the parent div
        const anchor = field.closest('.input-wrapper') || field.parentNode;
        anchor.style.position = 'relative'; // Ensure it can anchor absolute children

        let errorMsg = anchor.querySelector(`.${this.config.messageClass}`);
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = this.config.messageClass;
            anchor.appendChild(errorMsg);
        }

        errorMsg.innerHTML = (this.config.showIcons ? this.config.iconHtml : '') + ` <span>${message}</span>`;

        // Trigger animation immediately
        requestAnimationFrame(() => errorMsg.classList.add('show'));
        return false;
    },

    // Clear error
    clearError(field) {
        field.classList.remove(this.config.errorClass);
        const anchor = field.closest('.input-wrapper') || field.parentNode;
        const errorMsg = anchor.querySelector(`.${this.config.messageClass}`);
        if (errorMsg) {
            errorMsg.classList.remove('show');
            // Remove after animation
            setTimeout(() => {
                if (!errorMsg.classList.contains('show')) {
                    errorMsg.remove();
                }
            }, 400);
        }
    }
};

// Auto-init
document.addEventListener('DOMContentLoaded', () => Validator.init());
