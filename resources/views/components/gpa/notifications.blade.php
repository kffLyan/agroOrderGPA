{{-- Notifikasi Global Sistem AgroOrder GPA (Integrasi d:\UKK\notifcations) --}}
@once
<script>
    window.showGpaNotification = function (options) {
        if (!options) return;
        window.dispatchEvent(new CustomEvent('gpa:toast', {
            detail: {
                title: options.title || 'Pemberitahuan',
                message: options.description || options.message || '',
                tone: options.type || options.tone || 'info',
                time: options.time || 'Baru saja'
            }
        }));
    };

    window.addEventListener('gpa:notify', function (e) {
        if (e.detail) window.showGpaNotification(e.detail);
    });
</script>
@endonce
