const mobileNavigation = document.querySelector('[data-mobile-nav]');

if (mobileNavigation) {
    const toggleMobileNavigation = (isOpen) => {
        mobileNavigation.classList.toggle('hidden', !isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);

        document.querySelectorAll('[data-mobile-nav-open]').forEach((button) => {
            button.setAttribute('aria-expanded', String(isOpen));
        });
    };

    document.querySelectorAll('[data-mobile-nav-open]').forEach((button) => {
        button.addEventListener('click', () => toggleMobileNavigation(true));
    });

    mobileNavigation.querySelectorAll('[data-mobile-nav-close]').forEach((button) => {
        button.addEventListener('click', () => toggleMobileNavigation(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            toggleMobileNavigation(false);
        }
    });
}
