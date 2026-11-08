<?php
class PerangkatController extends Controller
{
    public function index(): void
    {
        $this->render('perangkat/index', [
            'pageTitle' => 'Perangkat Login',
            'perangkat' => Auth::devices(Auth::id()),
            'saatIni'   => Auth::currentSelector(),
            'pageJs'    => ['perangkat.js'],
        ]);
    }

    public function cabut(int $id): void
    {
        $uid = Auth::id();
        $row = Database::row('SELECT selector FROM user_token WHERE id = ? AND user_id = ?', [$id, $uid]);
        if (!$row) {
            flash('danger', 'Perangkat tidak ditemukan.');
            redirect('/perangkat');
        }
        if ($row['selector'] === Auth::currentSelector()) {
            Auth::logout();
            redirect('/login');
        }
        Auth::revokeToken($id, $uid);
        audit('cabut_perangkat', 'user_token', $id);
        flash('success', 'Perangkat dikeluarkan.');
        redirect('/perangkat');
    }

    public function keluarLainnya(): void
    {
        $n = Auth::logoutOthers(Auth::id());
        audit('keluar_semua_lain', 'user_token', null);
        flash('success', $n > 0 ? "$n perangkat lain dikeluarkan." : 'Tidak ada perangkat lain.');
        redirect('/perangkat');
    }
}
