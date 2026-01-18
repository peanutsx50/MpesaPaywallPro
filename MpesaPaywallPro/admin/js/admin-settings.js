document.addEventListener('DOMContentLoaded', function () {
    // Close button functionality for the notice
    const closeBtn = document.querySelector('.mpesapaywallpro-notice-close');
    const notice = document.querySelector('.mpesapaywallpro-notice');

    if (closeBtn && notice) {
        closeBtn.addEventListener('click', function () {
            notice.style.display = 'none';
        });
    }
});