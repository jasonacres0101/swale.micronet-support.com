document.addEventListener('click', (event) => {
    const placeholderButton = event.target.closest('[data-psa-placeholder]');

    if (! placeholderButton) {
        return;
    }

    window.alert('ConnectWise PSA ticket creation is a placeholder in this build.');
});
