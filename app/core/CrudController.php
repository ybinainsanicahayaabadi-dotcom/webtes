<?php
/**
 * Controller dasar untuk modul CRUD berbasis modal + AJAX.
 * Konvensi respons JSON:
 *   sukses : {"ok": true,  "message": "...", ...data tambahan}
 *   gagal  : {"ok": false, "message": "...", "errors": {"field": "pesan"}}  (status 422/404/409)
 */
abstract class CrudController extends Controller
{
    protected function ok(string $message = '', array $extra = []): void
    {
        $this->json(['ok' => true, 'message' => $message] + $extra);
    }

    protected function fail(string $message, array $errors = [], int $status = 422): void
    {
        $this->json(['ok' => false, 'message' => $message, 'errors' => (object) $errors], $status);
    }

    /** Validasi $_POST; bila gagal langsung membalas 422. Mengembalikan data bersih. */
    protected function validate(array $rules, array $labels = []): array
    {
        $v = Validator::make($_POST, $rules, $labels);
        if ($v->fails()) {
            $this->fail('Periksa kembali isian Anda.', $v->errors());
        }
        return $v->data();
    }

    /** Balas 404 JSON bila data kosong */
    protected function found(?array $row, string $label = 'Data'): array
    {
        if (!$row) {
            $this->fail("$label tidak ditemukan.", [], 404);
        }
        return $row;
    }
}
