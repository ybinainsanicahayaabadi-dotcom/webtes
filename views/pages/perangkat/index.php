<div class="card panel">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="panel-title mb-1"><i class="fa-solid fa-laptop me-2"></i>Perangkat yang Sedang Login</h2>
                <p class="text-muted small mb-0">Sesi berlaku 30 hari. Keluarkan perangkat yang tidak Anda kenali.</p>
            </div>
            <form method="post" action="<?= e(url('/perangkat/keluar-lainnya')) ?>" data-confirm="Keluarkan semua perangkat lain?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i>Keluarkan perangkat lain</button>
            </form>
        </div>

        <table id="tabel-perangkat" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th></th>
                    <th class="all">Perangkat</th>
                    <th class="min-tablet">Alamat IP</th>
                    <th class="min-desktop">Terakhir dipakai</th>
                    <th class="min-desktop">Berlaku sampai</th>
                    <th class="all text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($perangkat as $p): $ini = $p['selector'] === $saatIni; ?>
                <tr>
                    <td></td>
                    <td>
                        <?= e(ua_label($p['user_agent'])) ?>
                        <?php if ($ini): ?><span class="badge text-bg-success ms-1">Perangkat ini</span><?php endif; ?>
                    </td>
                    <td><?= e($p['ip_address']) ?></td>
                    <td data-order="<?= (int) strtotime((string) ($p['last_used_at'] ?? $p['created_at'])) ?>"><?= e(tanggal_id($p['last_used_at'] ?? $p['created_at'], true)) ?></td>
                    <td data-order="<?= (int) strtotime($p['expires_at']) ?>"><?= e(tanggal_id($p['expires_at'])) ?></td>
                    <td class="text-end">
                        <form method="post" action="<?= e(url('/perangkat/cabut/' . (int) $p['id'])) ?>" data-confirm="<?= $ini ? 'Keluar dari perangkat ini?' : 'Keluarkan perangkat ini?' ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i><span class="d-none d-sm-inline ms-1"><?= $ini ? 'Keluar' : 'Cabut' ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
