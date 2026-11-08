<?php
/** Perhitungan server-side. Tidak menerima skor/total yang dikirim klien. */
final class SupervisiScore
{
    public static function calculate(int $instrumentId, array $answers): array
    {
        $ins=Database::row("SELECT * FROM instrumen WHERE id=? AND modul='supervisi'",[$instrumentId]);
        if (!$ins) throw new InvalidArgumentException('Instrumen tidak ditemukan.');
        if ($ins['rumus']!=='persen_maks') throw new InvalidArgumentException('Rumus supervisi wajib persen_maks.');
        $items=Database::all('SELECT * FROM instrumen_butir WHERE instrumen_id=? AND is_aktif=1 ORDER BY urutan,id',[$instrumentId]);
        if (!$items) throw new InvalidArgumentException('Instrumen belum memiliki butir aktif.');
        $options=Database::all('SELECT * FROM instrumen_opsi WHERE instrumen_id=?',[$instrumentId]);
        $sets=[]; $byId=[];
        foreach ($options as $o) { $sets[$o['opsi_set']][]=$o; $byId[(int)$o['id']]=$o; }
        $valid=array_map('strval',array_column($items,'id'));
        foreach ($answers as $key=>$value) if (!in_array((string)$key,$valid,true)) throw new InvalidArgumentException('Jawaban memuat butir di luar instrumen.');
        $total=0.0; $max=0.0; $filled=0; $detail=[];
        foreach ($items as $b) {
            $set=$b['opsi_set']?:'default'; $opts=$sets[$set]??[];
            if (!$opts) throw new InvalidArgumentException('Set opsi butir belum tersedia.');
            $m=max(array_map('floatval',array_column($opts,'skor')));
            if ($m<=0) throw new InvalidArgumentException('Skor maksimal butir harus positif.');
            $max+=$m;
            $raw=$answers[$b['id']]??null;
            if ($raw===null || $raw==='') continue;
            if (!is_scalar($raw) || !ctype_digit((string)$raw)) throw new InvalidArgumentException('ID opsi tidak valid.');
            $o=$byId[(int)$raw]??null;
            if (!$o || $o['opsi_set']!==$set) throw new InvalidArgumentException('Opsi tidak cocok dengan butir.');
            $score=(float)$o['skor']; if ($score<0) throw new InvalidArgumentException('Skor negatif tidak diizinkan.');
            $total+=$score; $filled++;
            $detail[]=['butir_id'=>(int)$b['id'],'opsi_id'=>(int)$o['id'],'skor'=>$score];
        }
        $complete=$filled===count($items);
        $pct=$complete ? round($total/$max*100,2) : null;
        // Ambang bawah inklusif; tidak ada celah nilai pecahan 80.99–81 atau 90.99–91.
        $label=null;
        if ($complete) {
            $cats=Database::all("SELECT label,min_persen FROM kategori_capaian WHERE modul='supervisi' ORDER BY min_persen DESC");
            foreach ($cats as $c) if ($pct>=(float)$c['min_persen']) { $label=$c['label']; break; }
            if ($label===null) throw new InvalidArgumentException('Kategori capaian belum lengkap.');
        }
        return ['jumlah_butir'=>count($items),'terisi'=>$filled,'lengkap'=>$complete,'skor_total'=>$total,
            'skor_maks'=>$max,'nilai_akhir'=>$pct,'kategori'=>$label,'detail'=>$detail];
    }
}
