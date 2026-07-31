<?php
$content = file_get_contents('routes/web.php');
$search = "Route::post('/asistencia', 'store')->name('asistencia.store');";
$replace = $search . "\n            Route::post('/asistencia/regularizar', 'regularizar')->name('asistencia.regularizar');";
$content = str_replace($search, $replace, $content);
file_put_contents('routes/web.php', $content);
