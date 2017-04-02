<?php
Route::group(['namespace' => 'Jiko\Teamspeak\Http\Controllers'], function () {
  Route::name('ts_admin')->get('admin/teamspeak', 'TeamspeakAdminController@index');
  Route::post('admin/teamspeak', 'TeamspeakAdminController@actions');
  Route::name('ts_kick_vashton')->get('admin/teamspeak/kick-vashton', 'TeamspeakAdminController@kickVashton');
  Route::name('ts_info')->get('admin/teamspeak/info', 'TeamspeakAdminController@info');
  Route::name('ts_groups')->get('admin/teamspeak/groups', 'TeamSpeakAdminController@groups');
  Route::name('ts_bot')->get('admin/teamspeak/bot', 'TeamspeakAdminController@bot');
  Route::post('admin/teamspeak/server', 'BotServerController@index');
});