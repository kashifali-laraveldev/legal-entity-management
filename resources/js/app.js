import './bootstrap';

window.GrmUI = {
    openDrawer(id) {
        const el = document.getElementById(id);
        if (!el || !window.bootstrap) return;
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(el);
        offcanvas.show();
    },
    closeDrawer(id) {
        const el = document.getElementById(id);
        if (!el || !window.bootstrap) return;
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(el);
        offcanvas.hide();
    },
    showLoader(title = 'Please wait...') {
        if (!window.Swal) return;
        Swal.fire({
            title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
    },
    showSuccess(message = 'Operation completed') {
        if (!window.Swal) return;
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#157a6e',
        });
    },
    showError(message = 'Operation failed') {
        if (!window.Swal) return;
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#6c757d',
        });
    },
    showWarning(message = '') {
        if (!window.Swal) return;
        Swal.close();
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#157a6e',
        });
    },
    processPageFlash() {
        if (!window.Swal) return;
        const el = document.getElementById('grm-page-flash');
        if (!el) return;
        let data;
        try {
            data = JSON.parse(el.textContent);
        } catch {
            return;
        }
        const flash = data.flash || {};
        const validation = Array.isArray(data.validation) ? data.validation : [];
        if (validation.length > 0) {
            const esc = (s) => {
                const d = document.createElement('div');
                d.textContent = String(s);
                return d.innerHTML;
            };
            const html =
                '<ul class="text-start mb-0 ps-3">' +
                validation.map((m) => '<li>' + esc(m) + '</li>').join('') +
                '</ul>';
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Please fix the errors',
                html,
                confirmButtonText: 'OK',
                confirmButtonColor: '#6c757d',
            });
            return;
        }
        if (flash.error) {
            window.GrmUI.showError(flash.error);
            return;
        }
        if (flash.warning) {
            window.GrmUI.showWarning(flash.warning);
            return;
        }
        if (flash.success) {
            window.GrmUI.showSuccess(flash.success);
            return;
        }
        if (flash.status) {
            window.GrmUI.showSuccess(flash.status);
        }
    },
    confirmDelete(message = 'Delete this record?') {
        if (!window.Swal) return Promise.resolve(confirm(message));
        return Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: message,
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it'
        }).then((r) => r.isConfirmed);
    },
    confirmAction(options = {}) {
        const title = options.title || 'Are you sure?';
        const text = options.text || '';
        const confirmButtonText = options.confirmButtonText || 'Yes, continue';
        const confirmButtonColor = options.confirmButtonColor || '#157a6e';
        const icon = options.icon || 'question';
        if (!window.Swal) {
            return Promise.resolve(confirm(text || title));
        }
        return Swal.fire({
            icon,
            title,
            text,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText: 'Cancel',
            confirmButtonColor,
            cancelButtonColor: '#6c757d',
        }).then((r) => r.isConfirmed);
    },
};

document.addEventListener('DOMContentLoaded', function () {
    window.GrmUI.processPageFlash();

    document.addEventListener(
        'submit',
        function (e) {
            if (e.defaultPrevented) return;
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;
            const method = (form.getAttribute('method') || 'get').toLowerCase();
            if (method !== 'post') return;
            if (form.dataset.noGrmLoader === 'true') return;
            if (!window.GrmUI || !window.Swal) return;
            window.GrmUI.showLoader('Please wait...');
        },
        false
    );

    document.querySelectorAll('form[data-delete-form="true"]').forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (form.dataset.isSubmitting === 'true') return;
            const ok = await window.GrmUI.confirmDelete(form.dataset.deleteMessage || 'Delete this record?');
            if (!ok) return;
            form.dataset.isSubmitting = 'true';
            const submitButton = form.querySelector('button[type="submit"],input[type="submit"]');
            if (submitButton) submitButton.disabled = true;
            window.GrmUI.showLoader('Deleting...');
            form.submit();
        });
    });

    document.querySelectorAll('form[data-grm-confirm-submit="true"]').forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const ok = await window.GrmUI.confirmAction({
                title: form.dataset.grmConfirmTitle || 'Confirm',
                text: form.dataset.grmConfirmText || '',
                confirmButtonText: form.dataset.grmConfirmButton || 'Yes, continue',
                confirmButtonColor: form.dataset.grmConfirmColor || '#157a6e',
                icon: form.dataset.grmConfirmIcon || 'question',
            });
            if (!ok) return;
            window.GrmUI.showLoader('Please wait...');
            form.submit();
        });
    });

    document.querySelectorAll('form[data-ajax-form="true"]').forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            try {
                window.GrmUI.showLoader('Saving...');
                const method = (form.method || 'POST').toUpperCase();
                const response = await fetch(form.action, {
                    method,
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                let payload = {};
                try {
                    payload = await response.json();
                } catch {
                    throw new Error(response.statusText || 'Invalid response');
                }
                if (!response.ok || payload.ok !== true) {
                    throw new Error(payload.message || 'Save failed');
                }
                window.GrmUI.showSuccess(payload.message || 'Saved');
                setTimeout(() => window.location.reload(), 900);
            } catch (err) {
                window.GrmUI.showError(err.message || 'Request failed');
            }
        });
    });
});
