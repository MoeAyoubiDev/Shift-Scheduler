(() => {
    const notices = document.querySelectorAll('.notice');
    notices.forEach((notice) => {
        notice.addEventListener('click', () => notice.classList.add('is-hidden'));
    });
})();
