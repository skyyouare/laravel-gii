<?php
Route::any('/gii/model','\Skyyouare\Gii\Controllers\ModelController@index');
Route::get('/gii/model/fields','\Skyyouare\Gii\Controllers\ModelController@fields');
Route::any('/gii/crud','\Skyyouare\Gii\Controllers\CrudController@index');

