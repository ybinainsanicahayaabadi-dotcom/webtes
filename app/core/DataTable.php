<?php
/**
 * Pembantu DataTables server-side (pencarian, urutan, paging) dengan daftar kolom yang di-whitelist.
 *
 * DataTable::respond([
 *     'select'     => 't.id, t.nama, t.is_aktif',
 *     'from'       => 'tahun_ajaran t',                    // boleh berisi JOIN
 *     'where'      => 't.deleted_at IS NULL',              // opsional, bagian tetap
 *     'params'     => [],                                  // parameter untuk 'where'
 *     'columns'    => ['nama' => 't.nama', ...],           // nama kolom JS (columns[].data) => ekspresi SQL
 *     'searchable' => ['nama'],                            // kolom yang dicari
 *     'order'      => 't.nama DESC',                       // urutan bawaan
 * ]);
 */
final class DataTable
{
    public static function respond(array $cfg): void
    {
        $p = $_GET;
        $draw = (int) ($p['draw'] ?? 0);
        $start = max(0, (int) ($p['start'] ?? 0));
        $len = (int) ($p['length'] ?? 10);
        if ($len < 1 || $len > 100) {
            $len = 10;
        }

        $cols = $cfg['columns'];
        $base = ' FROM ' . $cfg['from'] . ' WHERE ' . ($cfg['where'] ?? '1=1');
        $params = $cfg['params'] ?? [];

        $total = (int) Database::value('SELECT COUNT(*)' . $base, $params);

        // Pencarian
        $filter = '';
        $fParams = $params;
        $q = trim((string) ($p['search']['value'] ?? ''));
        if ($q !== '' && !empty($cfg['searchable'])) {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $parts = [];
            foreach ($cfg['searchable'] as $alias) {
                if (isset($cols[$alias])) {
                    $parts[] = $cols[$alias] . ' LIKE ?';
                    $fParams[] = $like;
                }
            }
            if ($parts) {
                $filter = ' AND (' . implode(' OR ', $parts) . ')';
            }
        }
        $filtered = $filter === '' ? $total : (int) Database::value('SELECT COUNT(*)' . $base . $filter, $fParams);

        // Urutan: hanya kolom yang ada di whitelist
        $order = $cfg['order'] ?? '1';
        $o = $p['order'][0] ?? null;
        if (is_array($o)) {
            $idx = (int) ($o['column'] ?? -1);
            $name = (string) ($p['columns'][$idx]['data'] ?? '');
            if (isset($cols[$name])) {
                $dir = strtolower((string) ($o['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
                $order = $cols[$name] . ' ' . $dir . (isset($cfg['order']) ? ', ' . $cfg['order'] : '');
            }
        }

        $sql = 'SELECT ' . $cfg['select'] . $base . $filter . ' ORDER BY ' . $order . ' LIMIT ' . $len . ' OFFSET ' . $start;
        $rows = Database::all($sql, $fParams);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
