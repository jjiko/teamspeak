<?php
namespace Jiko\Teamspeak\Http\Controllers;

use Jiko\Admin\Http\Controllers\AdminController as Controller;
use Illuminate\Support\Facades\Input;
use TeamSpeak3;

class TeamspeakAdminController extends Controller
{
  protected $layout = 'admin::layouts.default';
  public function __construct()
  {
    parent::__construct();

    $conn = sprintf("serverquery://%s:%s@192.168.86.180:10011/?server_port=9987&nickname=%s",
      getenv("TS3_USER"),
      getenv("TS3_PASS"),
      urlencode("Supreme Overlord")
    );
    $this->ts3 = TeamSpeak3::factory($conn);
  }

  public function kick($uid, Array $properties)
  {
    $client = $this->ts3->clientGetByUid($uid);
    return $client->kick(TeamSpeak3::KICK_SERVER, "FEEL MY WRATH");
  }

  public function kickVashton()
  {
    $messages = ['LOL BYE', 'You suck!', 'STFU', 'No one wants you'];
    try {
      if ($vashton = $this->ts3->clientGetByName('Vashton')) {
        $vashton->kick(TeamSpeak3::KICK_SERVER, $messages[array_rand($messages, 1)]);
      };
    } catch (\Exception $e) {
      return $e->getMessage();
    }
    return redirect('/admin/teamspeak');
  }

  public function clientPoke($uid, Array $properties)
  {
    $this->ts3->clientGetByUid($uid)->poke($properties['message']);
  }

  public function clientModify($uid, Array $properties)
  {
    $this->ts3->clientGetByUid($uid)->modify(['CLIENT_NICKNAME' => 'seogiusdoigjhsoidgj']);
  }

  public function clientMove($uid, Array $properties)
  {
    $this->ts3->clientGetByUid($uid)->move($properties['channel_id']);
  }

  public function actions()
  {
    $action = Input::get('action');
    $uid = Input::get('client_unique_identifier');
    $data = Input::has('properties') ? Input::get('properties') : [];
    $properties = [];
    foreach ($data as $property) {
      list($key, $value) = explode(':', $property);
      $properties[$key] = $value;
    }

    switch ($action) {
      case "clientPoke":
        $properties['message'] = Input::get('message');
        break;
    }

    $this->{$action}($uid, $properties);
    return redirect('/admin/teamspeak');
  }

  public function info()
  {
    $client = $this->ts3->clientGetByName('Jiko');
    dd($client->infoDb());
  }

  public function bot()
  {
    $this->content('teamspeak::admin_bot', []);
  }

  public function test()
  {
    $client_list = $this->ts3->clientList();
    $errantGodGId = $this->ts3->serverGroupGetByName('Errant God')->getProperty('sgid');
    try {
      $vashton = $this->ts3->clientGetByName('Vashton');
    } catch (Ts3Exception $e) {
      $vashton = null;
    }
    $this->content('teamspeak::admin', [
      'client_list' => $client_list,
      'vashton' => $vashton
    ]);
  }

  public function telnet()
  {
    echo "begin Telnet..";
    $ip = '75.115.63.3';
    $result = '';
    try {
      $fp = fsockopen($ip, 10011);
      echo fgets($fp);
      fputs($fp, "login serveradmin ycRnFz0q");
      fputs($fp, "use sid=1");
      fputs($fp, "hostinfo");
      $result = fread($fp, 1024);
      fclose($fp);
      echo nl2br($result);
    } catch (Exception $e) {
      echo $e->getMessage();
    }
  }
}