<?php
/** Master tidak boleh diubah setelah instrumen dipakai. Buat versi baru untuk revisi. */
class SupervisiMasterController extends Controller
{
    private function fail(string $message): void { Router::abort(422,$message); }
    private function instrument(int $id): array
    {
        $r=Database::row("SELECT * FROM instrumen WHERE id=? AND modul='supervisi'",[$id]);
        if (!$r) Router::abort(404); return $r;
    }
    private function used(int $id): bool
    {
        return (int)Database::value('SELECT COUNT(*) FROM penilaian WHERE instrumen_id=?',[$id])>0
          || (int)Database::value('SELECT COUNT(*) FROM jadwal_supervisi WHERE instrumen_id=?',[$id])>0;
    }
    private function text(string $name,int $max,bool $required=true): string
    {
        $v=input($name); if (($required && $v==='') || mb_strlen($v)>$max) $this->fail('Periksa isian '.$name.'.'); return $v;
    }
    public function index(): void
    {
        $forms=Database::all("SELECT * FROM instrumen WHERE modul='supervisi' ORDER BY urutan,id");
        $id=(int)($_GET['id']??($forms[0]['id']??0));
        $ins=$id?$this->instrument($id):null;
        $groups=$id?Database::all('SELECT * FROM instrumen_kelompok WHERE instrumen_id=? ORDER BY urutan,id',[$id]):[];
        $items=$id?Database::all('SELECT * FROM instrumen_butir WHERE instrumen_id=? ORDER BY urutan,id',[$id]):[];
        $opts=$id?Database::all('SELECT * FROM instrumen_opsi WHERE instrumen_id=? ORDER BY opsi_set,urutan,id',[$id]):[];
        $cats=Database::all("SELECT * FROM kategori_capaian WHERE modul='supervisi' ORDER BY min_persen DESC");
        $this->render('admin/supervisi-master/index',['pageTitle'=>'Master Supervisi','pageJs'=>['supervisi-master.js'],
            'forms'=>$forms,'ins'=>$ins,'groups'=>$groups,'items'=>$items,'opts'=>$opts,'cats'=>$cats,'used'=>$id?$this->used($id):false]);
    }
    public function simpan(): void
    {
        $kind=input('kind'); $id=(int)input('id','0'); $iid=(int)input('instrumen_id','0');
        $action=input('action','save');
        if (!in_array($kind,['instrumen','kelompok','butir','opsi','kategori'],true)) $this->fail('Jenis tidak valid.');
        Transaction::run(function () use($kind,$id,&$iid,$action) {
            if ($kind==='kategori') {
                // Kategori digunakan bersama: kunci bila sudah ada hasil, agar riwayat tidak berubah.
                if (Database::value("SELECT COUNT(*) FROM penilaian WHERE modul='supervisi'")) $this->fail('Kategori sudah digunakan dalam penilaian.');
                if (!in_array($action,['save'],true)) $this->fail('Kategori tidak dapat dihapus.');
                $row=Database::row("SELECT * FROM kategori_capaian WHERE id=? AND modul='supervisi'",[$id]);
                if (!$row) Router::abort(404);
                $label=$this->text('label',40); $min=input('min_persen');
                if (!is_numeric($min) || (float)$min<0 || (float)$min>100) $this->fail('Ambang harus 0–100.');
                if ((float)$row['min_persen']===0.0 && (float)$min!==0.0) $this->fail('Ambang kategori terendah harus tetap 0.');
                if (Database::value("SELECT COUNT(*) FROM kategori_capaian WHERE modul='supervisi' AND min_persen=? AND id<>?",[$min,$id])) $this->fail('Ambang kategori tidak boleh sama.');
                Database::exec('UPDATE kategori_capaian SET label=?,min_persen=? WHERE id=?',[$label,round((float)$min,2),$id]);
                // Sinkronkan max_persen sebagai metadata; mesin nilai memakai min_persen.
                $cats=Database::all("SELECT id,min_persen FROM kategori_capaian WHERE modul='supervisi' ORDER BY min_persen DESC");
                $upper=100.0; foreach ($cats as $c) {Database::exec('UPDATE kategori_capaian SET max_persen=? WHERE id=?',[$upper,$c['id']]);$upper=max(0,(float)$c['min_persen']-0.01);}
                return;
            }
            if ($kind==='instrumen' && !$id) {
                $code=$this->text('kode',40);$name=$this->text('nama',200);$stage=input('tahap');
                if (!in_array($stage,['perencanaan','pelaksanaan','asesmen'],true)) $this->fail('Tahap tidak valid.');
                if (Database::value('SELECT COUNT(*) FROM instrumen WHERE kode=?',[$code])) $this->fail('Kode sudah digunakan.');
                $iid=Database::insert("INSERT INTO instrumen(modul,tahap,kode,nama,skor_maks_butir,rumus,is_aktif) VALUES ('supervisi',?,?,?,2,'persen_maks',0)",[$stage,$code,$name]);
                foreach ([0,1,2] as $v) Database::exec("INSERT INTO instrumen_opsi(instrumen_id,opsi_set,label,skor,urutan) VALUES (?,'default',?,?,?)",[$iid,['Tidak sesuai','Sebagian','Sesuai'][$v],$v,$v]);
                return;
            }
            if ($kind==='instrumen') $iid=$id;
            $ins=$this->instrument($iid);
            // Serialisasi penulisan master untuk instrumen yang sama.
            Database::row('SELECT id FROM instrumen WHERE id=? FOR UPDATE',[$iid]);
            if ($this->used($iid)) $this->fail('Instrumen sudah dijadwalkan/dinilai. Buat instrumen versi baru; master lama terkunci.');
            $table=['instrumen'=>'instrumen','kelompok'=>'instrumen_kelompok','butir'=>'instrumen_butir','opsi'=>'instrumen_opsi'][$kind];
            $old=$id?Database::row("SELECT * FROM $table WHERE id=?".($kind==='instrumen'?'':' AND instrumen_id=?'),$kind==='instrumen'?[$id]:[$id,$iid]):null;
            if ($id && !$old) Router::abort(404);
            if ($action==='delete') {
                if (!$id) $this->fail('ID wajib.');
                if ($kind==='instrumen') $this->fail('Nonaktifkan instrumen, jangan hapus induknya.');
                if ($kind==='kelompok' && (Database::value('SELECT COUNT(*) FROM instrumen_butir WHERE kelompok_id=?',[$id]) || Database::value('SELECT COUNT(*) FROM instrumen_kelompok WHERE parent_id=?',[$id]))) $this->fail('Kelompok masih berisi data.');
                if ($kind==='opsi' && Database::value('SELECT COUNT(*) FROM penilaian_detail WHERE opsi_id=?',[$id])) $this->fail('Opsi sudah digunakan.');
                Database::exec("DELETE FROM $table WHERE id=?",[$id]); return;
            }
            if ($action!=='save') $this->fail('Aksi tidak valid.');
            if ($kind==='instrumen') {
                $name=$this->text('nama',200); $code=$this->text('kode',40); $stage=input('tahap');
                if (!in_array($stage,['perencanaan','pelaksanaan','asesmen'],true)) $this->fail('Tahap tidak valid.');
                if (Database::value('SELECT COUNT(*) FROM instrumen WHERE kode=? AND id<>?',[$code,$id])) $this->fail('Kode sudah ada.');
                if (isset($_POST['is_aktif'])) {
                    $probe=SupervisiScore::calculate($iid,[]);
                    if (!$probe['jumlah_butir']) $this->fail('Belum ada butir.');
                }
                Database::exec('UPDATE instrumen SET nama=?,kode=?,tahap=?,catatan_form=?,is_aktif=? WHERE id=?',[$name,$code,$stage,$this->text('catatan_form',5000,false),isset($_POST['is_aktif'])?1:0,$id]);
            } elseif ($kind==='kelompok') {
                $args=[$this->text('kode',20,false),$this->text('nama',255),(int)input('urutan','0')];
                if ($id) Database::exec('UPDATE instrumen_kelompok SET kode=?,nama=?,urutan=? WHERE id=?',array_merge($args,[$id]));
                else Database::exec('INSERT INTO instrumen_kelompok(kode,nama,urutan,instrumen_id) VALUES (?,?,?,?)',array_merge($args,[$iid]));
            } elseif ($kind==='butir') {
                $gid=(int)input('kelompok_id','0');
                if ($gid && !Database::value('SELECT COUNT(*) FROM instrumen_kelompok WHERE id=? AND instrumen_id=?',[$gid,$iid])) $this->fail('Kelompok tidak cocok.');
                $set=$this->text('opsi_set',40); if (!Database::value('SELECT COUNT(*) FROM instrumen_opsi WHERE instrumen_id=? AND opsi_set=?',[$iid,$set])) $this->fail('Set opsi belum tersedia.');
                $args=[$gid?:null,$this->text('nomor',20,false),$this->text('pernyataan',10000),$set,(int)input('urutan','0'),isset($_POST['is_aktif'])?1:0];
                if ($id) Database::exec('UPDATE instrumen_butir SET kelompok_id=?,nomor=?,pernyataan=?,opsi_set=?,urutan=?,is_aktif=? WHERE id=?',array_merge($args,[$id]));
                else Database::exec('INSERT INTO instrumen_butir(kelompok_id,nomor,pernyataan,opsi_set,urutan,is_aktif,instrumen_id) VALUES (?,?,?,?,?,?,?)',array_merge($args,[$iid]));
            } else {
                $score=input('skor'); if (!in_array($score,['0','1','2'],true)) $this->fail('Skor supervisi hanya 0, 1, 2.');
                $set=$this->text('opsi_set',40);
                if ($id && $set!==$old['opsi_set'] && Database::value('SELECT COUNT(*) FROM instrumen_butir WHERE instrumen_id=? AND opsi_set=?',[$iid,$old['opsi_set']])) $this->fail('Set lama masih dirujuk butir.');
                $args=[$set,$this->text('label',60),(int)$score,(int)input('urutan','0')];
                if ($id) Database::exec('UPDATE instrumen_opsi SET opsi_set=?,label=?,skor=?,urutan=? WHERE id=?',array_merge($args,[$id]));
                else Database::exec('INSERT INTO instrumen_opsi(opsi_set,label,skor,urutan,instrumen_id) VALUES (?,?,?,?,?)',array_merge($args,[$iid]));
            }
        });
        audit('ubah_master_supervisi',$kind,$id?:null);
        flash('success','Master disimpan.');redirect('/admin/instrumen-supervisi?id='.$iid);
    }
    public function simulasi(): void
    {
        $id=(int)($_GET['id']??0);$this->instrument($id);
        try {
            $rows=Database::all('SELECT id,opsi_set FROM instrumen_butir WHERE instrumen_id=? AND is_aktif=1',[$id]);
            $tests=[];
            foreach ([0,1,2] as $v) {
                $answers=[];
                foreach ($rows as $b) {
                    $oid=Database::value('SELECT id FROM instrumen_opsi WHERE instrumen_id=? AND opsi_set=? AND skor=? LIMIT 1',[$id,$b['opsi_set']?:'default',$v]);
                    if (!$oid) throw new InvalidArgumentException('Set opsi harus menyediakan 0, 1, 2 untuk uji ini.');
                    $answers[$b['id']]=$oid;
                }
                $r=SupervisiScore::calculate($id,$answers);unset($r['detail']);$tests['semua_'.$v]=$r;
            }
            $this->json(['ok'=>true,'tests'=>$tests,'kosong'=>SupervisiScore::calculate($id,[])]);
        } catch (InvalidArgumentException $e) {$this->json(['ok'=>false,'message'=>$e->getMessage()],422);}
    }
}
