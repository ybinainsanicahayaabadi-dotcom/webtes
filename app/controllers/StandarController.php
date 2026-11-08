<?php
/** Standar Mutu: setiap request divalidasi kembali di server. */
class StandarController extends Controller
{
    private function pesan(string $pesan, string $type = 'success'): void
    {
        flash($type, $pesan);
        redirect('/standar-mutu?unit=' . (int) ($_POST['unit_id'] ?? 0));
    }

    /** Konteks unit dan semester wajib cocok dengan filter sesi. */
    private function konteks(bool $tulis = false): array
    {
        $p = Periode::current();
        $unit = (int) ($_POST['unit_id'] ?? $_GET['unit'] ?? 0);
        if (!$p || !$unit || !Rbac::canAccessUnit($unit)) { Router::abort(403); }
        $u = Database::row("SELECT * FROM unit_sekolah WHERE id=? AND jenjang<>'YAYASAN' AND deleted_at IS NULL", [$unit]);
        if (!$u) { Router::abort(404); }
        if ($tulis) {
            if ((int) ($_POST['semester_id'] ?? 0) !== (int) $p['id']) {
                Router::abort(409, 'Periode berubah di tab lain. Muat ulang halaman.');
            }
            $locked = Database::value('SELECT is_terkunci FROM semester WHERE id=?', [$p['id']]);
            if ((int) $locked === 1) { Router::abort(409, 'Semester terkunci. Buka melalui menu Periode terlebih dahulu.'); }
        }
        return [$u, $p];
    }

    public function index(): void
    {
        $scope = Rbac::unitScope();
        $units = Database::all("SELECT id,nama FROM unit_sekolah WHERE jenjang<>'YAYASAN' AND deleted_at IS NULL AND is_aktif=1"
            . ($scope === null ? '' : ' AND id=' . (int) $scope) . ' ORDER BY urutan,id');
        $unitId = (int) ($_GET['unit'] ?? ($units[0]['id'] ?? 0));
        $p = Periode::current();
        $standards = Database::all('SELECT * FROM standar_mutu ORDER BY urutan,id');
        $items = []; $files = [];
        if ($unitId && $p) {
            if (!Rbac::canAccessUnit($unitId)) { Router::abort(403); }
            $items = Database::all('SELECT b.*,v.id AS isian_id,v.status AS status_pemenuhan,v.skor,v.catatan,v.diisi_oleh,v.diverifikasi_oleh
                FROM standar_butir b LEFT JOIN standar_penilaian v ON v.butir_id=b.id AND v.unit_id=? AND v.semester_id=?
                ORDER BY b.urutan,b.id', [$unitId,$p['id']]);
            $files = Database::all("SELECT * FROM bukti_file WHERE unit_id=? AND semester_id=?
                AND ref_tipe IN ('standar_butir','standar_mutu') AND deleted_at IS NULL ORDER BY id DESC", [$unitId,$p['id']]);
        }
        $this->render('standar/index', ['pageTitle'=>'Standar Mutu','pageJs'=>['standar.js'],
            'units'=>$units,'unitId'=>$unitId,'periode'=>$p,'standards'=>$standards,'items'=>$items,'files'=>$files]);
    }

    /** Master memakai nonaktifkan, tidak menghapus indikator historis. */
    public function standar(): void
    {
        $id = (int) input('id','0');
        if ($id && !Database::row('SELECT id FROM standar_mutu WHERE id=?',[$id])) { Router::abort(404); }
        $kode=input('kode'); $nama=input('nama'); $desc=input('deskripsi');
        if ($kode==='' || $nama==='' || mb_strlen($kode)>20 || mb_strlen($nama)>150 || mb_strlen($desc)>5000) {
            $this->pesan('Kode/nama wajib dan panjangnya harus sesuai batas.', 'danger');
        }
        if (Database::value('SELECT COUNT(*) FROM standar_mutu WHERE kode=? AND id<>?',[$kode,$id])) {
            $this->pesan('Kode standar sudah digunakan.', 'danger');
        }
        $args=[$kode,$nama,$desc,(int) input('urutan','0'),isset($_POST['is_aktif'])?1:0];
        if ($id) { Database::exec('UPDATE standar_mutu SET kode=?,nama=?,deskripsi=?,urutan=?,is_aktif=? WHERE id=?',array_merge($args,[$id])); }
        else { $id=Database::insert('INSERT INTO standar_mutu(kode,nama,deskripsi,urutan,is_aktif) VALUES (?,?,?,?,?)',$args); }
        audit('simpan_master','standar_mutu',$id); $this->pesan('Master standar disimpan.');
    }

    public function butir(): void
    {
        $id=(int) input('id','0'); $sid=(int) input('standar_id','0');
        $old=$id ? Database::row('SELECT * FROM standar_butir WHERE id=?',[$id]) : null;
        if ($id && !$old) { Router::abort(404); }
        if (!Database::value('SELECT COUNT(*) FROM standar_mutu WHERE id=?',[$sid])) { Router::abort(422); }
        // Jangan memindahkan butir yang telah dipakai ke standar lain.
        if ($old && (int)$old['standar_id']!==$sid && Database::value('SELECT COUNT(*) FROM standar_penilaian WHERE butir_id=?',[$id])) {
            $this->pesan('Butir sudah dipakai; standar induknya tidak boleh dipindah.','danger');
        }
        $indikator=input('indikator'); $nomor=input('nomor'); $bukti=input('bukti_dibutuhkan');
        if ($indikator==='' || mb_strlen($indikator)>10000 || mb_strlen($nomor)>20 || mb_strlen($bukti)>5000) {
            $this->pesan('Periksa panjang dan kelengkapan indikator.','danger');
        }
        $args=[$sid,$nomor,$indikator,$bukti,(int)input('urutan','0'),isset($_POST['is_aktif'])?1:0];
        if ($id) { Database::exec('UPDATE standar_butir SET standar_id=?,nomor=?,indikator=?,bukti_dibutuhkan=?,urutan=?,is_aktif=? WHERE id=?',array_merge($args,[$id])); }
        else { $id=Database::insert('INSERT INTO standar_butir(standar_id,nomor,indikator,bukti_dibutuhkan,urutan,is_aktif) VALUES (?,?,?,?,?,?)',$args); }
        audit('simpan_master','standar_butir',$id); $this->pesan('Indikator disimpan.');
    }

    public function isian(): void
    {
        [$u,$p]=$this->konteks(true);
        $bid=(int)input('butir_id','0');
        if (!Database::value('SELECT COUNT(*) FROM standar_butir b JOIN standar_mutu s ON s.id=b.standar_id WHERE b.id=? AND b.is_aktif=1 AND s.is_aktif=1',[$bid])) { Router::abort(422); }
        $status=input('status'); $score=input('skor'); $note=input('catatan');
        if (!in_array($status,['belum','sebagian','terpenuhi','tidak_terpenuhi'],true)
            || ($score!=='' && (!is_numeric($score) || (float)$score<0 || (float)$score>100)) || mb_strlen($note)>10000) {
            $this->pesan('Status/skor tidak valid. Skor opsional 0–100.','danger');
        }
        Database::exec('INSERT INTO standar_penilaian(unit_id,semester_id,butir_id,status,skor,catatan,diisi_oleh)
            VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),skor=VALUES(skor),catatan=VALUES(catatan),diisi_oleh=VALUES(diisi_oleh),diverifikasi_oleh=NULL',
            [$u['id'],$p['id'],$bid,$status,$score===''?null:round((float)$score,2),$note,Auth::id()]);
        audit('isi','standar_penilaian',null,null,['unit'=>$u['id'],'semester'=>$p['id'],'butir'=>$bid]);
        $this->pesan('Isian disimpan; verifikasi lama dibatalkan karena isi berubah.');
    }

    public function verifikasi(): void
    {
        [$u,$p]=$this->konteks(true); $id=(int)input('isian_id','0');
        $row=Database::row('SELECT * FROM standar_penilaian WHERE id=? AND unit_id=? AND semester_id=?',[$id,$u['id'],$p['id']]);
        if (!$row) { Router::abort(404); }
        Database::exec('UPDATE standar_penilaian SET diverifikasi_oleh=? WHERE id=?',[isset($_POST['batalkan'])?null:Auth::id(),$id]);
        audit('verifikasi','standar_penilaian',$id); $this->pesan('Status verifikasi diperbarui.');
    }

    private function referensi(string $type,int $id): void
    {
        $table=['standar_mutu'=>'standar_mutu','standar_butir'=>'standar_butir'][$type]??null;
        if (!$table || !Database::value("SELECT COUNT(*) FROM $table WHERE id=?",[$id])) { Router::abort(422); }
    }

    public function bukti(): void
    {
        [$u,$p]=$this->konteks(true); $type=input('ref_tipe'); $ref=(int)input('ref_id','0');
        $this->referensi($type,$ref);
        // Tahap 4 menggunakan penyimpanan privat lokal; driver Nextcloud menyusul Tahap 7.
        $f=$_FILES['bukti']??null;
        if (!$f || is_array($f['error']) || $f['error']!==UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            $this->pesan('Unggahan gagal. Pilih berkas dan periksa batas PHP server.','danger');
        }
        $limit=max(1,min(50,(int)setting('upload_maks_mb',5)))*1048576;
        if ($f['size']>$limit) { $this->pesan('Ukuran berkas melebihi batas aplikasi.','danger'); }
        // Daftar konservatif: dokumen Office belum diterima sampai pemeriksaan paket ZIP diterapkan.
        $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
        $allowed=['pdf'=>'application/pdf','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg'];
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $configured=array_map('trim',explode(',',strtolower((string)setting('upload_ekstensi','pdf,jpg,jpeg,png'))));
        if (!isset($allowed[$ext]) || $allowed[$ext]!==$mime || !in_array($ext,$configured,true)) {
            $this->pesan('Bukti tahap ini menerima PDF, JPG, atau PNG yang diizinkan pengaturan aplikasi.','danger');
        }
        $folder='bukti/'.$u['id'].'/'.$p['id']; $dir=STORAGE_PATH.'/uploads/'.$folder;
        if (!is_dir($dir) && !mkdir($dir,0750,true)) { throw new RuntimeException('Folder bukti tidak dapat dibuat.'); }
        $name=bin2hex(random_bytes(20)).'.'.$ext; $full=$dir.'/'.$name;
        if ($mime==='application/pdf') {
            $fh=fopen($f['tmp_name'],'rb'); $head=fread($fh,5); fclose($fh);
            if ($head!=='%PDF-') { $this->pesan('Header PDF tidak valid.','danger'); }
            if (!move_uploaded_file($f['tmp_name'],$full)) { throw new RuntimeException('Gagal menyimpan bukti.'); }
        } else {
            // Re-encode gambar lewat helper Tahap 3B.
            $name=ImageUpload::save('bukti',$dir,'bukti',['maxBytes'=>$limit,'maxW'=>2400,'maxH'=>2400]);
            $full=$dir.'/'.$name;
        }
        try {
            $id=Transaction::run(function () use($u,$p,$type,$ref,$folder,$name,$full,$f) {
                $id=Database::insert("INSERT INTO bukti_file(unit_id,semester_id,ref_tipe,ref_id,driver,path,nama_asli,mime,ukuran,checksum,diunggah_oleh) VALUES (?,?,?,?,'lokal',?,?,?,?,?,?)",
                    [$u['id'],$p['id'],$type,$ref,$folder.'/'.$name,mb_substr(basename($f['name']),0,255),
                    (new finfo(FILEINFO_MIME_TYPE))->file($full),filesize($full),hash_file('sha256',$full),Auth::id()]);
                $this->batalVerifikasi($u['id'],$p['id'],$type,$ref);
                return $id;
            });
        } catch (Throwable $e) { @unlink($full); throw $e; }
        audit('unggah','bukti_file',$id); $this->pesan('Bukti disimpan privat; verifikasi terkait perlu diulang.');
    }

    private function batalVerifikasi(int $unit,int $sem,string $type,int $ref): void
    {
        if ($type==='standar_butir') {
            Database::exec('UPDATE standar_penilaian SET diverifikasi_oleh=NULL WHERE unit_id=? AND semester_id=? AND butir_id=?',[$unit,$sem,$ref]);
        } else {
            Database::exec('UPDATE standar_penilaian SET diverifikasi_oleh=NULL WHERE unit_id=? AND semester_id=? AND butir_id IN (SELECT id FROM standar_butir WHERE standar_id=?)',[$unit,$sem,$ref]);
        }
    }

    public function hapusBukti(): void
    {
        [$u,$p]=$this->konteks(true); $id=(int)input('id','0');
        $f=Database::row("SELECT * FROM bukti_file WHERE id=? AND unit_id=? AND semester_id=? AND deleted_at IS NULL AND ref_tipe IN ('standar_butir','standar_mutu')",[$id,$u['id'],$p['id']]);
        if (!$f) { Router::abort(404); }
        if (!Auth::hasRole('admin','kepala_sekolah') && (int)$f['diunggah_oleh']!==Auth::id()) { Router::abort(403); }
        Transaction::run(function () use($id,$u,$p,$f) {
            Database::exec('UPDATE bukti_file SET deleted_at=NOW() WHERE id=?',[$id]);
            $this->batalVerifikasi($u['id'],$p['id'],$f['ref_tipe'],(int)$f['ref_id']);
        });
        audit('hapus_lunak','bukti_file',$id); $this->pesan('Bukti diarsipkan; verifikasi terkait dibatalkan.');
    }

    public function unduh(int $id): void
    {
        $f=Database::row("SELECT * FROM bukti_file WHERE id=? AND deleted_at IS NULL AND ref_tipe IN ('standar_butir','standar_mutu')",[$id]);
        if (!$f) { Router::abort(404); }
        if (!Rbac::canAccessUnit((int)$f['unit_id'])) { Router::abort(403); }
        if ($f['driver']!=='lokal') { Router::abort(409,'Driver bukti belum didukung pada tahap ini.'); }
        $base=realpath(STORAGE_PATH.'/uploads'); $path=realpath(STORAGE_PATH.'/uploads/'.$f['path']);
        if (!$base || !$path || strpos($path,$base.DIRECTORY_SEPARATOR)!==0 || !is_file($path)) { Router::abort(404); }
        audit('unduh','bukti_file',$id);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="bukti-'.(int)$id.'.'.pathinfo($path,PATHINFO_EXTENSION).'"');
        header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, no-store');
        header('Content-Length: '.filesize($path)); session_write_close(); readfile($path); exit;
    }
}
