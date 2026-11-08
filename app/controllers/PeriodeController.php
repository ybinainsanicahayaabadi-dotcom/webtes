<?php
class PeriodeController extends Controller
{
    /** Simpan pilihan semester global ke sesi */
    public function pilih(): void
    {
        $id = (int) input('semester_id', '0');
        if ($id > 0 && Periode::setSemester($id)) {
            flash('success', 'Periode diganti.');
        }
        $this->back('/dashboard');
    }
}
