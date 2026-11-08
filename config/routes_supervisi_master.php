<?php
return [
 ['GET','/admin/instrumen-supervisi','SupervisiMasterController@index','role:admin'],
 ['POST','/admin/instrumen-supervisi/simpan','SupervisiMasterController@simpan','role:admin'],
 ['GET','/admin/instrumen-supervisi/simulasi','SupervisiMasterController@simulasi','role:admin'],
];
