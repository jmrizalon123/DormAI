<div
    class="modal fade da-confirm-modal"
    id="daConfirmModal"
    tabindex="-1"
    aria-labelledby="daConfirmModalTitle"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4">
                <div class="d-flex gap-3">
                    <div class="da-confirm-modal__icon flex-shrink-0" id="daConfirmModalIcon" aria-hidden="true">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <h2 class="h6 mb-2" id="daConfirmModalTitle">Confirm action</h2>
                        <p class="text-muted small mb-0" id="daConfirmModalMessage">Are you sure you want to continue?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="daConfirmModalCancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="daConfirmModalConfirm">Confirm</button>
            </div>
        </div>
    </div>
</div>
