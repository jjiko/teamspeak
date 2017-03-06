<?php
Route::group(['namespace' => 'Jiko\Teamspeak\Http\Controllers'], function () {
  Route::get('admin/teamspeak', 'TeamspeakAdminController@test');
  Route::post('admin/teamspeak', 'TeamspeakAdminController@actions');
  Route::get('admin/teamspeak/kick-vashton', 'TeamspeakAdminController@kickVashton');
  Route::get('admin/teamspeak/info', 'TeamspeakAdminController@info');

  Route::get('admin/teamspeak/bot', 'TeamspeakAdminController@bot');
  Route::post('admin/teamspeak/server', 'BotServerController@index');
});