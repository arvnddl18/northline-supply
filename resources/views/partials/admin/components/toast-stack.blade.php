<!-- Sonner-Style Emil Kowalski Toast Deck -->
<div id="nc-toast-container" class="nc-toast-container" aria-live="polite" aria-atomic="true"></div>

<script>
(function() {
    window.showToast = function(message, type = 'info', duration = 3500) {
        const container = document.getElementById('nc-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `nc-toast toast-${type}`;
        
        let iconSvg = '';
        if (type === 'success') {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 20 20" fill="#4A6B53"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>';
        } else if (type === 'error') {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 20 20" fill="#D32F2F"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>';
        } else {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 20 20" fill="#C46238"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>';
        }

        toast.innerHTML = `
            <span style="display:flex;align-items:center;">${iconSvg}</span>
            <div style="flex:1;line-height:1.4;">${message}</div>
            <button type="button" class="nc-btn-close" style="font-size:16px;padding:2px;" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
        `;

        container.appendChild(toast);

        // Micro-raf trigger for smooth spring enter
        requestAnimationFrame(() => {
            toast.classList.add('is-visible');
        });

        // Auto dismiss
        setTimeout(() => {
            toast.classList.remove('is-visible');
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 250);
        }, duration);
    };
})();
</script>
