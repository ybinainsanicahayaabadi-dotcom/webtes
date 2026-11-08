<?php
/**
 * Pembantu tampilan: render partial dan kerangka modal formulir.
 */
final class UI
{
    public static function partial(string $name, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        require VIEW_PATH . '/partials/' . $name . '.php';
    }

    /**
     * Modal formulir standar untuk CRUD.
     * @param string $fieldsView  view isian, relatif ke views/pages (mis. 'admin/periode/_fields_tahun')
     * @param string $size        '' | 'modal-lg' | 'modal-xl'
     * @param array  $vars        variabel tambahan untuk view isian
     */
    public static function modalForm(string $id, string $formId, string $title, string $fieldsView, string $size = '', array $vars = []): void
    {
        self::partial('modal_form', [
            'modalId' => $id, 'formId' => $formId, 'modalTitle' => $title,
            'fieldsView' => $fieldsView, 'size' => $size, 'fieldVars' => $vars,
        ]);
    }
}
