<?php

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
 */

Route::get('/resource/{model}/{id?}', 'damianbal\\Restpal\\RestpalController@restGet');
Route::post('/resource/{model}', 'damianbal\\Restpal\\RestpalController@restPost');
Route::patch('/resource/{model}/{id}', 'damianbal\\Restpal\\RestpalController@restPatch');
// Route::put('/resource/{model}/{id}', 'damianbal\\Restpal\\RestpalController@restPut');
Route::delete('/resource/{model}/{id}', 'damianbal\\Restpal\\RestpalController@restDelete');

// Relational
Route::get('/resource/{model}/{id}/{relation}', 'damianbal\\Restpal\\RestpalController@restRelationGet');
Route::post('/resource/{model}/{id}/{relation}', 'damianbal\\Restpal\\RestpalController@restRelationPost');
