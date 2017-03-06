<?php
namespace Jiko\Teamspeak\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use TeamSpeak3\TeamSpeak3;

class TestController extends Controller
{
  public function test()
  {
    return "nothing to see here";
  }
}