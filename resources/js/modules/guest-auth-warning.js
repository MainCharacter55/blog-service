export function initGuestAuthWarning(root = document) {
    const modal = root.querySelector('[data-guest-auth-warning]');

    if (!modal) {
        return;
    }

    const title = modal.querySelector('[data-guest-auth-warning-title]');
    const message = modal.querySelector('[data-guest-auth-warning-message]');
    const closeButtons = Array.from(modal.querySelectorAll('[data-guest-auth-warning-close]'));
    const overlay = modal.querySelector('[data-guest-auth-warning-overlay]');
    const loginButton = modal.querySelector('[data-guest-auth-warning-login]');
    const registerButton = modal.querySelector('[data-guest-auth-warning-register]');
    const triggers = Array.from(root.querySelectorAll('[data-guest-auth-warning-trigger]'));

    if (triggers.length === 0) {
        return;
    }

    const openModal = (trigger) => {
        if (title) {
            title.textContent = trigger.dataset.guestAuthWarningTitle || 'ログインが必要です';
        }

        if (message) {
            message.textContent = trigger.dataset.guestAuthWarningMessage || 'この操作を続けるにはログインまたは会員登録が必要です。';
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (loginButton) {
            loginButton.focus();
        }
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal(trigger);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal();
        });
    });

    if (overlay) {
        overlay.addEventListener('click', closeModal);
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    if (loginButton && loginButton.tagName === 'A') {
        loginButton.addEventListener('click', closeModal);
    }

    if (registerButton && registerButton.tagName === 'A') {
        registerButton.addEventListener('click', closeModal);
    }
}