<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
if (!is_file(STORAGE_PATH . '/installed.lock')) { header('Location: ' . url('install.php')); exit; }
Router::dispatch(array_merge(
 require ROOT_PATH . '/config/routes.php',
 require ROOT_PATH . '/config/routes_admin.php',
 require ROOT_PATH . '/config/routes_standar.php',
 require ROOT_PATH . '/config/routes_supervisi_master.php'
));
