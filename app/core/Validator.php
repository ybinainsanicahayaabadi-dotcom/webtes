<?php
/**
 * Validator sederhana untuk input form.
 *
 * Contoh:
 *   $v = Validator::make($_POST, [
 *       'nama'  => ['required', 'max:100', 'unique:guru,nama,5'],
 *       'email' => ['nullable', 'email'],
 *       'umur'  => ['required', 'integer', 'between:17,70'],
 *       'aktif' => ['boolean'],
 *       'kode'  => ['required', function ($nilai, $semua, $label) { return $nilai === 'x' ? "$label tidak boleh x." : null; }],
 *   ], ['nama' => 'Nama lengkap']);
 *   if ($v->fails()) { $v->errors(); }  // ['nama' => 'pesan', ...]
 *   $data = $v->data();                  // nilai bersih (string ter-trim, integer di-cast, boolean jadi 0/1)
 *
 * Aturan: required, nullable, boolean, max:n, min:n, email, integer, between:a,b, in:a,b,c,
 *         date (Y-m-d), after_or_equal:field, unique:tabel,kolom[,ignoreId[,soft]], exists:tabel,kolom
 * Aturan kustom: Closure (nilai, seluruh input, label) -> pesan galat atau null.
 */
final class Validator
{
    private array $errors = [];
    private array $data = [];
    private array $input = [];
    private array $labels = [];

    public static function make(array $input, array $rules, array $labels = []): self
    {
        $v = new self();
        $v->input = $input;
        $v->labels = $labels;
        foreach ($rules as $field => $list) {
            $v->check((string) $field, is_array($list) ? $list : explode('|', (string) $list));
        }
        return $v;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function data(): array
    {
        return $this->data;
    }

    private function label(string $field): string
    {
        return $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    private function check(string $field, array $rules): void
    {
        $raw = $this->input[$field] ?? null;
        $value = is_string($raw) ? trim($raw) : $raw;
        $label = $this->label($field);

        $names = [];
        foreach ($rules as $r) {
            $names[] = is_string($r) ? explode(':', $r, 2)[0] : '';
        }

        if (in_array('boolean', $names, true)) {
            $this->data[$field] = in_array($value, ['1', 1, true, 'on', 'true'], true) ? 1 : 0;
            return;
        }

        if ($value === null || $value === '' || $value === []) {
            if (in_array('required', $names, true)) {
                $this->errors[$field] = "$label wajib diisi.";
            } else {
                $this->data[$field] = null;
            }
            return;
        }
        if (is_array($value)) {
            $this->errors[$field] = "$label tidak valid.";
            return;
        }

        foreach ($rules as $rule) {
            if ($rule instanceof Closure) {
                $msg = $rule($value, $this->input, $label);
            } else {
                [$name, $param] = array_pad(explode(':', (string) $rule, 2), 2, null);
                $msg = $this->apply($name, $param, (string) $value, $label);
            }
            if ($msg !== null) {
                $this->errors[$field] = $msg;
                return;
            }
        }
        $this->data[$field] = in_array('integer', $names, true) ? (int) $value : $value;
    }

    private function apply(string $name, ?string $param, string $value, string $label): ?string
    {
        switch ($name) {
            case 'required':
            case 'nullable':
                return null;

            case 'max':
                return mb_strlen($value) > (int) $param ? "$label maksimal $param karakter." : null;

            case 'min':
                return mb_strlen($value) < (int) $param ? "$label minimal $param karakter." : null;

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "$label bukan alamat email yang valid.";

            case 'integer':
                return preg_match('/^-?\d+$/', $value) ? null : "$label harus berupa angka bulat.";

            case 'between':
                [$a, $b] = array_map('intval', explode(',', (string) $param));
                if (!preg_match('/^-?\d+$/', $value) || (int) $value < $a || (int) $value > $b) {
                    return "$label harus antara $a dan $b.";
                }
                return null;

            case 'in':
                return in_array($value, explode(',', (string) $param), true) ? null : "$label tidak valid.";

            case 'date':
                $d = DateTime::createFromFormat('Y-m-d', $value);
                return ($d && $d->format('Y-m-d') === $value) ? null : "$label bukan tanggal yang valid.";

            case 'after_or_equal':
                $other = trim((string) ($this->input[$param] ?? ''));
                if ($other !== '' && $value < $other) {
                    return "$label tidak boleh lebih awal dari " . mb_strtolower($this->label((string) $param)) . '.';
                }
                return null;

            case 'unique':
                [$tabel, $kolom, $ignore, $soft] = array_pad(explode(',', (string) $param), 4, null);
                $this->assertIdentifier($tabel, $kolom);
                $sql = "SELECT COUNT(*) FROM `$tabel` WHERE `$kolom` = ?";
                $args = [$value];
                if ($ignore !== null && (int) $ignore > 0) {
                    $sql .= ' AND id <> ?';
                    $args[] = (int) $ignore;
                }
                if ($soft === 'soft') {
                    $sql .= ' AND deleted_at IS NULL';
                }
                return (int) Database::value($sql, $args) > 0 ? "$label sudah digunakan." : null;

            case 'exists':
                [$tabel, $kolom] = array_pad(explode(',', (string) $param), 2, 'id');
                $this->assertIdentifier($tabel, $kolom);
                return (int) Database::value("SELECT COUNT(*) FROM `$tabel` WHERE `$kolom` = ?", [$value]) > 0
                    ? null : "$label tidak ditemukan.";
        }
        throw new LogicException("Aturan validasi tidak dikenal: $name");
    }

    private function assertIdentifier(string ...$names): void
    {
        foreach ($names as $n) {
            if (!preg_match('/^\w+$/', $n)) {
                throw new LogicException('Nama tabel/kolom tidak valid pada aturan validasi.');
            }
        }
    }
}
