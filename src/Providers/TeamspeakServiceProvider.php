<?php

namespace Jiko\Teamspeak\Providers;

use Illuminate\Support\ServiceProvider;

class TeamspeakServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->register('Jiko\Teamspeak\Providers\RouteServiceProvider');
    $this->app->singleton(\Teamspeak3::class, function ($app) {
      $nicknames = ['God', 'Satan', 'かめさま', 'Supreme Overlord'];

      return \TeamSpeak3::factory(vsprintf('serverquery://%1$s:%2$s@%3$s:%4$s/?server_port=%5$s&nickname=%6$s', [
        'user' => urlencode(env('TS3_USER')),
        'pass' => urlencode(env('TS3_PASS')),
        'server' => urlencode(env('TS3_SERVER')),
        'server_query_port' => urlencode(env('TS3_SQPORT')),
        'port' => urlencode(env('TS3_PORT')),
        'nickname' => urlencode($nicknames[array_rand($nicknames, 1)])
      ]));
    });

  }
}