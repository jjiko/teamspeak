<?php

namespace Jiko\Teamspeak\Providers;

use Illuminate\Support\ServiceProvider;

class TeamspeakServiceProvider extends ServiceProvider
{
  public function boot()
  {

  }

  public function register()
  {
    $this->app->register('Jiko\Teamspeak\Providers\RouteServiceProvider');
    $this->app->singleton('teamspeak', function ($app) {
      $nicknames = ['God', 'Satan', 'Kame-Sama'];
      $nickname = $nicknames[array_rand($nicknames, 1)].rand(1000,10000);
      $opt = (object)[
        'user' => urlencode(getenv('TS3_USER')),
        'pass' => urlencode(getenv('TS3_PASS')),
        'server' => urlencode(getenv('TS3_SERVER')),
        'server_query_port' => urlencode(getenv('TS3_SQPORT')),
        'port' => urlencode(getenv('TS3_PORT'))
      ];
      return \TSFramework\Teamspeak::factory("serverquery://{$opt->user}:{$opt->pass}@{$opt->server}:{$opt->server_query_port}/?server_port={$opt->port}&nickname={$opt->nickname}");
    });
  }
}