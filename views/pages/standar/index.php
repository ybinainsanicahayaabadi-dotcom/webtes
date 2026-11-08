<?php
$admin=Auth::hasRole('admin');
$write=$periode && !(int)$periode['is_terkunci'] && Rbac::can('standar_mutu','isi');
$verify=$periode && !(int)$periode['is_terkunci'] && Rbac::can('standar_mutu','verifikasi');
$context=static function () use($unitId,$periode) {
 echo csrf_field().'<input type="hidden" name="unit_id" value="'.(int)$unitId.'"><input type="hidden" name="semester_id" value="'.(int)($periode['id']??0).'">';
};
$edit=static function(string $kind,array $data,string $label) {
 echo '<button type="button" class="btn btn-sm btn-outline-primary" data-editor="'.e($kind).'" data-record="'.e(json_encode($data,JSON_UNESCAPED_UNICODE)).'">'.e($label).'</button>';
};
$attachments=static function(string $type,int $ref) use($files,$write,$context) {
 foreach($files as $f) {
  if($f['ref_tipe']!==$type || (int)$f['ref_id']!==$ref) continue;
  echo '<div class="d-flex gap-2 align-items-center my-1"><a href="'.e(url('/standar-mutu/bukti/'.(int)$f['id'])).'">'.e($f['nama_asli']).'</a><small>'.round($f['ukuran']/1024).' KB</small>';
  if($write && (Auth::hasRole('admin','kepala_sekolah') || (int)$f['diunggah_oleh']===Auth::id())) {
   echo '<form method="post" action="'.e(url('/standar-mutu/bukti-hapus')).'" data-confirm="Arsipkan bukti ini?">';
   $context(); echo '<input type="hidden" name="id" value="'.(int)$f['id'].'"><button class="btn btn-sm btn-outline-danger">Arsipkan</button></form>';
  }
  echo '</div>';
 }
};
?>
<form method="get" class="mb-3 d-flex gap-2" action="<?= e(url('/standar-mutu')) ?>">
 <select name="unit" class="form-select" aria-label="Unit sekolah">
 <?php foreach($units as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (int)$u['id']===$unitId?'selected':'' ?>><?= e($u['nama']) ?></option><?php endforeach; ?>
 </select><button class="btn btn-primary">Tampilkan</button>
</form>
<p class="text-muted">Periode: <?= e(($periode['tahun']??'-').' / '.($periode['semester']??'-')) ?>.
 <?= $periode && $periode['is_terkunci']?'Semester terkunci.':'' ?></p>
<?php if($admin): ?>
<div class="mb-3"><?php $edit('standar',['is_aktif'=>1],'Tambah standar'); ?> <?php $edit('butir',['is_aktif'=>1],'Tambah indikator'); ?></div>
<?php endif; ?>
<?php if(!$unitId || !$periode): ?><div class="alert alert-warning">Unit/periode belum tersedia. Siapkan di menu Admin.</div><?php endif; ?>
<?php foreach($standards as $s):
 if(!$admin && !$s['is_aktif']) continue;
 $rows=array_values(array_filter($items,static fn($b)=>(int)$b['standar_id']===(int)$s['id']));
 $active=array_values(array_filter($rows,static fn($b)=>(bool)$b['is_aktif']));
 $met=count(array_filter($active,static fn($b)=>$b['status_pemenuhan']==='terpenuhi'));
 $percent=count($active)?round($met/count($active)*100,1):0;
?>
<section class="card panel mb-3"><div class="card-body">
 <div class="d-flex flex-wrap gap-2 justify-content-between"><h2 class="h5"><?= e($s['nama']) ?> <?= !$s['is_aktif']?'(Nonaktif)':'' ?></h2>
 <?php if($admin) $edit('standar',$s,'Ubah standar'); ?></div>
 <p><?= nl2br(e($s['deskripsi'])) ?></p>
 <p class="small mb-1"><?= $met ?>/<?= count($active) ?> indikator terpenuhi (<?= $percent ?>%). Ini progres pemenuhan, bukan nilai sertifikasi.</p>
 <progress value="<?= $percent ?>" max="100" class="w-100 mb-2"><?= $percent ?>%</progress>
 <?php if($write && $s['is_aktif']) $edit('bukti',['ref_tipe'=>'standar_mutu','ref_id'=>$s['id']],'Unggah dokumen standar'); ?>
 <?php $attachments('standar_mutu',(int)$s['id']); ?>
 <table class="table align-middle w-100 standar-table">
 <thead><tr><th></th><th class="all">Indikator</th><th class="min-tablet">Pemenuhan</th><th class="min-desktop">Skor</th><th class="min-desktop">Catatan & bukti</th><th class="all">Aksi</th></tr></thead>
 <tbody>
 <?php foreach($rows as $b): if(!$admin && !$b['is_aktif'])continue; ?>
 <tr><td></td><td><?= e($b['nomor']) ?> <?= nl2br(e($b['indikator'])) ?><?= !$b['is_aktif']?' (Nonaktif)':'' ?><small class="d-block text-muted"><?= e($b['bukti_dibutuhkan']) ?></small></td>
 <td><?= e($b['status_pemenuhan']??'belum') ?><small class="d-block"><?= $b['diverifikasi_oleh']?'Terverifikasi':'Belum diverifikasi' ?></small></td>
 <td><?= e($b['skor']??'-') ?></td><td><?= nl2br(e($b['catatan'])) ?><?php $attachments('standar_butir',(int)$b['id']); ?></td>
 <td><div class="d-flex flex-wrap gap-1">
 <?php
 if($admin) $edit('butir',$b,'Ubah indikator');
 if($write && $b['is_aktif'] && $s['is_aktif']) {
  $edit('isian',['butir_id'=>$b['id'],'status'=>$b['status_pemenuhan']??'belum','skor'=>$b['skor'],'catatan'=>$b['catatan']],'Isi');
  $edit('bukti',['ref_tipe'=>'standar_butir','ref_id'=>$b['id']],'Bukti');
 }
 ?>
 <?php if($verify && $b['isian_id']): ?>
 <form method="post" action="<?= e(url('/standar-mutu/verifikasi')) ?>"><?php $context(); ?>
 <input type="hidden" name="isian_id" value="<?= (int)$b['isian_id'] ?>">
 <?php if($b['diverifikasi_oleh']): ?><input type="hidden" name="batalkan" value="1"><?php endif; ?>
 <button class="btn btn-sm btn-outline-success"><?= $b['diverifikasi_oleh']?'Batal verifikasi':'Verifikasi' ?></button></form>
 <?php endif; ?></div></td></tr>
 <?php endforeach; ?></tbody></table>
</div></section>
<?php endforeach; ?>

<?php foreach(['standar','butir','isian','bukti'] as $kind):
 if(in_array($kind,['standar','butir']) && !$admin) continue;
 if(in_array($kind,['isian','bukti']) && !$write) continue;
?>
<div class="modal fade" id="editor-<?= e($kind) ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
<form method="post" enctype="multipart/form-data" class="modal-content" action="<?= e(url('/standar-mutu/'.$kind)) ?>">
<div class="modal-header"><h3 class="modal-title h5"><?= e(ucfirst($kind)) ?></h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
<div class="modal-body">
<?php $context(); ?>
<input type="hidden" name="id" value="0">
<?php if($kind==='standar'): ?>
<label class="form-label">Kode</label><input class="form-control mb-3" name="kode" required maxlength="20">
<label class="form-label">Nama</label><input class="form-control mb-3" name="nama" required maxlength="150">
<label class="form-label">Deskripsi</label><textarea class="form-control mb-3" name="deskripsi" maxlength="5000"></textarea>
<?php elseif($kind==='butir'): ?>
<label class="form-label">Standar induk</label><select class="form-select mb-3" name="standar_id" required><?php foreach($standards as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['nama']) ?></option><?php endforeach; ?></select>
<label class="form-label">Nomor</label><input class="form-control mb-3" name="nomor" maxlength="20">
<label class="form-label">Indikator</label><textarea class="form-control mb-3" name="indikator" required maxlength="10000"></textarea>
<label class="form-label">Bukti yang dibutuhkan</label><textarea class="form-control mb-3" name="bukti_dibutuhkan" maxlength="5000"></textarea>
<?php elseif($kind==='isian'): ?>
<input type="hidden" name="butir_id">
<label class="form-label">Status</label><select class="form-select mb-3" name="status"><?php foreach(['belum','sebagian','terpenuhi','tidak_terpenuhi'] as $st): ?><option value="<?= e($st) ?>"><?= e($st) ?></option><?php endforeach; ?></select>
<label class="form-label">Skor internal (opsional, 0–100)</label><input type="number" min="0" max="100" step="0.01" name="skor" class="form-control mb-3">
<label class="form-label">Catatan</label><textarea name="catatan" class="form-control" maxlength="10000"></textarea>
<?php else: ?>
<input type="hidden" name="ref_tipe"><input type="hidden" name="ref_id">
<label class="form-label">Berkas bukti (PDF/JPG/PNG)</label><input type="file" name="bukti" class="form-control" accept="application/pdf,image/jpeg,image/png" required>
<p class="form-text">Batas aplikasi <?= (int)setting('upload_maks_mb',5) ?> MB. Disimpan privat di server lokal.</p>
<?php endif; ?>
<?php if(in_array($kind,['standar','butir'])): ?>
<label class="form-label">Urutan</label><input class="form-control mb-3" name="urutan" type="number" value="0" min="0" max="9999">
<label><input type="checkbox" name="is_aktif" value="1" checked> Aktif</label>
<p class="form-text">Nonaktifkan untuk menyembunyikan master, bukan menghapus riwayatnya.</p>
<?php endif; ?>
</div><div class="modal-footer"><button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endforeach; ?>
