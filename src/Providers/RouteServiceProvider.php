<?php

namespace Jiko\Teamspeak\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
  public function boot()
  {
    parent::boot();

    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'teamspeak');
  }

  public function register()
  {

  }

  public function map(Router $router)
  {
    require_once(__DIR__.'/../Http/routes.php');
  }
}