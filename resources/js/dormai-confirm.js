(function () {
    var modalEl;
    var modal;
    var titleEl;
    var messageEl;
    var iconEl;
    var confirmBtn;
    var cancelBtn;
    var pendingResolve = null;

    var riskyKeywords = [
        'delete',
        'remove',
        'void',
        'reject',
        'cancel',
        'deactivate',
        'reset',
        'sign out',
        'move-out',
        'move out',
        'record move-out',
        'confirm rejection',
        'mark all read',
        'approve',
        'suspend',
        'issue invoice',
        'issue',
        'check in',
        'mark deposit',
        'mark completed',
        'submit for',
        'renew',
        'allocate',
        'calculate allocation',
        'create draft',
        'create work order',
        'record payment',
    ];

    var destructiveKeywords = [
        'delete',
        'remove',
        'void',
        'reject',
        'cancel',
        'suspend',
        'sign out',
        'reset',
        'move-out',
        'move out',
        'record move-out',
        'confirm rejection',
    ];

    function init() {
        modalEl = document.getElementById('daConfirmModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return false;
        }

        titleEl = document.getElementById('daConfirmModalTitle');
        messageEl = document.getElementById('daConfirmModalMessage');
        iconEl = document.getElementById('daConfirmModalIcon');
        confirmBtn = document.getElementById('daConfirmModalConfirm');
        cancelBtn = document.getElementById('daConfirmModalCancel');
        modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        confirmBtn.addEventListener('click', function () {
            if (pendingResolve) {
                pendingResolve(true);
                pendingResolve = null;
            }
            modal.hide();
        });

        cancelBtn.addEventListener('click', function () {
            if (pendingResolve) {
                pendingResolve(false);
                pendingResolve = null;
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (pendingResolve) {
                pendingResolve(false);
                pendingResolve = null;
            }
        });

        return true;
    }

    function setIcon(destructive) {
        if (!iconEl) {
            return;
        }

        iconEl.classList.toggle('da-confirm-modal__icon--danger', !!destructive);
        iconEl.innerHTML = destructive
            ? '<i class="bi bi-exclamation-triangle-fill"></i>'
            : '<i class="bi bi-question-circle-fill"></i>';
    }

    function ask(options) {
        options = options || {};

        return new Promise(function (resolve) {
            if (!modalEl && !init()) {
                resolve(window.confirm(options.message || 'Continue?'));
                return;
            }

            pendingResolve = resolve;

            var destructive = !!options.destructive;
            setIcon(destructive);

            titleEl.textContent = options.title || (destructive ? 'Confirm destructive action' : 'Confirm action');
            messageEl.textContent =
                options.message || 'Are you sure you want to continue? This action may not be easy to undo.';
            confirmBtn.textContent = options.confirmLabel || (destructive ? 'Yes, proceed' : 'Yes, continue');
            confirmBtn.className = 'btn ' + (destructive ? 'btn-danger' : 'btn-primary');

            modal.show();
        });
    }

    function resolveSubmitter(form, submitter) {
        if (submitter) {
            return submitter;
        }

        var active = document.activeElement;
        if (!active || !form.contains(active)) {
            return null;
        }

        if (active.tagName === 'SELECT' || active.tagName === 'BUTTON' || active.tagName === 'INPUT') {
            return active;
        }

        return null;
    }

    function isDeleteForm(form) {
        var methodInput = form.querySelector('input[name="_method"]');
        return methodInput && String(methodInput.value).toUpperCase() === 'DELETE';
    }

    function isMutatingForm(form) {
        var method = (form.getAttribute('method') || 'get').toLowerCase();
        return method !== 'get';
    }

    function submitterIsDanger(submitter) {
        if (!submitter || submitter.type === 'button') {
            return false;
        }

        return (
            submitter.classList.contains('btn-danger') ||
            submitter.classList.contains('btn-outline-danger')
        );
    }

    function submitterLabel(submitter) {
        if (!submitter) {
            return '';
        }

        if (submitter.tagName === 'SELECT') {
            var option = submitter.options[submitter.selectedIndex];
            return option ? option.textContent.trim() : '';
        }

        return (submitter.getAttribute('value') || submitter.textContent || '').trim();
    }

    function labelMatchesKeywords(label, keywords) {
        if (!label) {
            return false;
        }

        var lower = label.toLowerCase();

        return keywords.some(function (keyword) {
            return lower.indexOf(keyword) !== -1;
        });
    }

    function submitterIsRisky(submitter) {
        return labelMatchesKeywords(submitterLabel(submitter), riskyKeywords);
    }

    function isDestructiveAction(form, submitter) {
        if (isDeleteForm(form) || submitterIsDanger(submitter)) {
            return true;
        }

        return labelMatchesKeywords(submitterLabel(submitter), destructiveKeywords);
    }

    function confirmMessageFor(form, submitter) {
        if (submitter && submitter.hasAttribute('data-da-confirm')) {
            return submitter.getAttribute('data-da-confirm');
        }

        if (form.hasAttribute('data-da-confirm')) {
            return form.getAttribute('data-da-confirm');
        }

        var label = submitterLabel(submitter);

        if (isDeleteForm(form)) {
            return label && label !== '×'
                ? 'Delete "' + label + '"? This cannot be undone.'
                : 'Delete this item? This cannot be undone.';
        }

        if (label && labelMatchesKeywords(label, destructiveKeywords)) {
            return 'Are you sure you want to "' + label + '"?';
        }

        if (label && labelMatchesKeywords(label, riskyKeywords)) {
            return 'Proceed with "' + label + '"?';
        }

        if (submitterIsDanger(submitter)) {
            return label
                ? 'Are you sure you want to "' + label + '"?'
                : 'Are you sure you want to continue?';
        }

        if (submitter && submitter.tagName === 'SELECT') {
            return 'Update status to "' + label + '"?';
        }

        if (label) {
            return 'Continue with "' + label + '"?';
        }

        return 'Are you sure you want to save these changes?';
    }

    function isExcludedForm(form) {
        if (form.hasAttribute('data-da-no-confirm')) {
            return true;
        }

        if (form.closest('.da-filter-bar')) {
            return true;
        }

        if (form.closest('.da-auth-card')) {
            return true;
        }

        return false;
    }

    function needsConfirm(form, submitter) {
        if (!(form instanceof HTMLFormElement)) {
            return false;
        }

        if (isExcludedForm(form)) {
            return false;
        }

        if (form.getAttribute('data-da-confirm') || (submitter && submitter.getAttribute('data-da-confirm'))) {
            return true;
        }

        if (!isMutatingForm(form)) {
            return false;
        }

        return true;
    }

    function confirmTitleFor(form, submitter, destructive) {
        if (form.getAttribute('data-da-confirm-title')) {
            return form.getAttribute('data-da-confirm-title');
        }

        if (submitter && submitter.getAttribute('data-da-confirm-title')) {
            return submitter.getAttribute('data-da-confirm-title');
        }

        if (destructive) {
            return 'Confirm destructive action';
        }

        return 'Confirm action';
    }

    function bindSelectAutoSubmit() {
        document.addEventListener(
            'focusin',
            function (event) {
                if (event.target.tagName === 'SELECT') {
                    event.target.dataset.daPriorValue = event.target.value;
                }
            },
            true
        );

        document.querySelectorAll('select[data-da-submit-on-change]').forEach(function (select) {
            var form = select.form;
            if (!form) {
                return;
            }

            select.addEventListener('change', function () {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(select);
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            });
        });
    }

    function revertSelectIfCancelled(submitter) {
        if (!submitter || submitter.tagName !== 'SELECT') {
            return;
        }

        if (submitter.dataset.daPriorValue !== undefined) {
            submitter.value = submitter.dataset.daPriorValue;
        }
    }

    function bindForms() {
        document.addEventListener(
            'submit',
            function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (form.dataset.daConfirmBypass === '1') {
                    delete form.dataset.daConfirmBypass;
                    return;
                }

                var submitter = resolveSubmitter(form, event.submitter || null);

                if (!needsConfirm(form, submitter)) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                var destructive = isDestructiveAction(form, submitter);

                ask({
                    title: confirmTitleFor(form, submitter, destructive),
                    message: confirmMessageFor(form, submitter),
                    destructive: destructive,
                    confirmLabel: destructive ? 'Yes, proceed' : 'Yes, continue',
                }).then(function (confirmed) {
                    if (!confirmed) {
                        revertSelectIfCancelled(submitter);
                        return;
                    }

                    form.dataset.daConfirmBypass = '1';

                    if (submitter && typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter);
                    } else {
                        form.submit();
                    }
                });
            },
            true
        );
    }

    function bindLinks() {
        document.addEventListener('click', function (event) {
            var link = event.target.closest('a[data-da-confirm]');
            if (!link) {
                return;
            }

            event.preventDefault();

            ask({
                title: link.getAttribute('data-da-confirm-title') || 'Confirm action',
                message: link.getAttribute('data-da-confirm') || 'Are you sure you want to continue?',
                destructive: link.hasAttribute('data-da-confirm-destructive'),
                confirmLabel: link.getAttribute('data-da-confirm-label') || 'Continue',
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                var href = link.getAttribute('href');
                if (href) {
                    window.location.href = href;
                }
            });
        });
    }

    window.DormaiConfirm = {
        ask: ask,
    };

    document.addEventListener('DOMContentLoaded', function () {
        init();
        bindSelectAutoSubmit();
        bindForms();
        bindLinks();
    });
})();
