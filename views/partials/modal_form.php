<?php
/** Kerangka modal formulir. Variabel: $modalId, $formId, $modalTitle, $fieldsView, $size, $fieldVars */
extract($fieldVars, EXTR_SKIP);
?>
<div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable <?= e($size) ?>">
        <form class="modal-content" id="<?= e($formId) ?>" method="post" enctype="multipart/form-data" novalidate autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="modal-header">
                <h5 class="modal-title" data-title-add="Tambah <?= e($modalTitle) ?>" data-title-edit="Ubah <?= e($modalTitle) ?>">Tambah <?= e($modalTitle) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?php require VIEW_PATH . '/pages/' . $fieldsView . '.php'; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-gradient"><i class="fa-regular fa-floppy-disk me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
