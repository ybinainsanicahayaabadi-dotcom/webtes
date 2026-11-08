<?php
$edit=static function($kind,$record,$label) { echo '<button type="button" class="btn btn-sm btn-outline-primary" data-master="'.e($kind).'" data-record="'.e(json_encode($record)).'">'.e($label).'</button>'; };
$del=static function($kind,$id) use($ins) {echo '<form class="d-inline" method="post" action="'.e(url('/admin/instrumen-supervisi/simpan')).'" data-confirm="Hapus master yang belum digunakan ini?">'.csrf_field().'<input type="hidden" name="kind" value="'.e($kind).'"><input type="hidden" name="id" value="'.(int)$id.'"><input type="hidden" name="instrumen_id" value="'.(int)$ins['id'].'"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>';};
?>
<div class="alert alert-info">Instrumen awal nonaktif agar ditinjau Admin terlebih dahulu. Redaksi indikator berupa rumusan operasional ringkas; bukan salinan verbatim PDF. Nilai terbaik = 2. Asesmen turunan dilaporkan terpisah.</div>
<form method="get" class="d-flex gap-2 mb-3"><select name="id" class="form-select" aria-label="Instrumen"><?php foreach($forms as $f): ?><option value="<?= (int)$f['id'] ?>" <?= $ins && (int)$ins['id']===(int)$f['id']?'selected':'' ?>><?= e($f['kode'].' — '.$f['nama']) ?><?= $f['is_aktif']?'':' (nonaktif)' ?></option><?php endforeach; ?></select><button class="btn btn-primary">Buka</button></form>
<?php $edit('instrumen',['is_aktif'=>0],'Instrumen baru / versi baru'); ?>
<?php if($ins): ?>
<section class="card panel my-3"><div class="card-body"><h2 class="h5"><?= e($ins['nama']) ?></h2>
<p><?= e($ins['catatan_form']) ?></p><p><?= count(array_filter($items,static fn($x)=>$x['is_aktif'])) ?> butir aktif. Tahap: <?= e($ins['tahap']) ?>.</p>
<a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/instrumen-supervisi/simulasi?id='.$ins['id'])) ?>" target="_blank" rel="noopener">Uji hitung 0/1/2 (JSON)</a>
<?php if($used): ?><p class="text-warning mt-2">Sudah dipakai jadwal/penilaian: seluruh master instrumen terkunci. Buat versi baru untuk revisi.</p><?php else: ?>
<?php $edit('instrumen',$ins,'Ubah instrumen'); ?> <?php $edit('kelompok',[],'Tambah kelompok'); ?> <?php $edit('opsi',['opsi_set'=>'default'],'Tambah opsi'); ?> <?php $edit('butir',['opsi_set'=>'default','is_aktif'=>1],'Tambah butir'); ?>
<?php endif; ?></div></section>
<?php foreach(['kelompok'=>$groups,'opsi'=>$opts,'butir'=>$items] as $kind=>$list): ?>
<section class="card panel my-3"><div class="card-body"><h2 class="h5"><?= e(ucfirst($kind)) ?></h2>
<table class="table w-100 master-table"><thead><tr><th></th><th class="all">Nama / isi</th><th class="min-tablet">Keterangan</th><th class="all">Aksi</th></tr></thead><tbody>
<?php foreach($list as $r): ?><tr><td></td><td><?= nl2br(e($r['pernyataan']??$r['nama']??$r['label']??'')) ?></td><td>
<?php if($kind==='kelompok'): ?>Kode <?= e($r['kode']) ?>; urutan <?= (int)$r['urutan'] ?>
<?php elseif($kind==='opsi'): ?>Set <?= e($r['opsi_set']) ?>; skor <?= e($r['skor']) ?>
<?php else: ?>No <?= e($r['nomor']) ?>; set <?= e($r['opsi_set']) ?>; <?= $r['is_aktif']?'aktif':'nonaktif' ?>
<?php endif; ?></td><td><?php if(!$used) {$edit($kind,$r,'Ubah');echo ' ';$del($kind,$r['id']);} ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php endforeach; ?>
<?php endif; ?>
<section class="card panel my-3"><div class="card-body"><h2 class="h5">Kategori supervisi</h2><p>Ambang bawah inklusif. Pembulatan nilai 2 desimal sebelum kategori.</p>
<?php foreach($cats as $c): ?><div class="my-2"><?= e($c['label']) ?>: mulai <?= e($c['min_persen']) ?>% <?php $edit('kategori',$c,'Ubah'); ?></div><?php endforeach; ?></div></section>
<?php foreach(['instrumen','kelompok','opsi','butir','kategori'] as $kind): ?>
<div class="modal fade" id="master-<?= e($kind) ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><form method="post" action="<?= e(url('/admin/instrumen-supervisi/simpan')) ?>" class="modal-content">
<div class="modal-header"><h3 class="h5"><?= e(ucfirst($kind)) ?></h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body">
<?= csrf_field() ?><input type="hidden" name="kind" value="<?= e($kind) ?>"><input type="hidden" name="id" value="0"><input type="hidden" name="instrumen_id" value="<?= (int)($ins['id']??0) ?>">
<?php if(in_array($kind,['instrumen','kelompok'])): ?>
<label class="form-label">Kode</label><input name="kode" maxlength="<?= $kind==='instrumen'?40:20 ?>" class="form-control mb-3" <?= $kind==='instrumen'?'required':'' ?>>
<label class="form-label">Nama</label><input name="nama" class="form-control mb-3" maxlength="<?= $kind==='instrumen'?200:255 ?>" required>
<?php endif; ?>
<?php if($kind==='instrumen'): ?>
<label class="form-label">Tahap</label><select name="tahap" class="form-select mb-3"><option value="perencanaan">Perencanaan</option><option value="pelaksanaan">Pelaksanaan</option><option value="asesmen">Asesmen</option></select>
<label class="form-label">Catatan</label><textarea name="catatan_form" class="form-control mb-3" maxlength="5000"></textarea>
<p class="form-text">Instrumen baru selalu dibuat nonaktif. Isi indikator dahulu, kemudian aktifkan melalui Ubah instrumen.</p>
<?php elseif($kind==='butir'): ?>
<label class="form-label">Kelompok</label><select name="kelompok_id" class="form-select mb-3"><option value="0">Tanpa kelompok</option><?php foreach($groups as $g): ?><option value="<?= (int)$g['id'] ?>"><?= e($g['nama']) ?></option><?php endforeach; ?></select>
<label class="form-label">Nomor</label><input name="nomor" maxlength="20" class="form-control mb-3">
<label class="form-label">Pernyataan</label><textarea name="pernyataan" class="form-control mb-3" maxlength="10000" required></textarea>
<?php elseif($kind==='opsi'): ?>
<label class="form-label">Label</label><input name="label" class="form-control mb-3" maxlength="60" required>
<label class="form-label">Skor</label><select name="skor" class="form-select mb-3"><option value="0">0</option><option value="1">1</option><option value="2">2</option></select>
<?php elseif($kind==='kategori'): ?>
<label class="form-label">Label</label><input name="label" class="form-control mb-3" maxlength="40" required>
<label class="form-label">Ambang bawah</label><input name="min_persen" type="number" min="0" max="100" step="0.01" class="form-control mb-3" required>
<?php endif; ?>
<?php if(in_array($kind,['butir','opsi'])): ?><label class="form-label">Set opsi</label><input name="opsi_set" class="form-control mb-3" maxlength="40" value="default" required><?php endif; ?>
<?php if(in_array($kind,['kelompok','butir','opsi'])): ?><label class="form-label">Urutan</label><input name="urutan" type="number" min="0" max="9999" value="0" class="form-control mb-3"><?php endif; ?>
<?php if(in_array($kind,['instrumen','butir'])): ?><label><input type="checkbox" name="is_aktif" value="1"> Aktif</label><?php endif; ?>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div>
<?php endforeach; ?>
