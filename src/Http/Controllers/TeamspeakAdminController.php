<?php

namespace Jiko\Teamspeak\Http\Controllers;

use Jiko\Admin\Http\Controllers\AdminController as Controller;
use Illuminate\Support\Facades\Input;
use Jiko\Auth\TSUser;
use TeamSpeak3;

class TeamspeakAdminController extends Controller
{
  protected $layout = 'admin::layouts.default';

  public function __construct()
  {
    parent::__construct();

//    if (!request()->session()->exists('ts3')) {
    $nicknames = ['God', 'Satan', 'かめさま', 'Supreme Overlord'];
    $conn = vsprintf('serverquery://%1$s:%2$s@%3$s:%4$s/?server_port=%5$s&nickname=%6$s', [
      'user' => urlencode(env('TS3_USER')),
      'pass' => urlencode(env('TS3_PASS')),
      'server' => urlencode(env('TS3_SERVER')),
      'server_query_port' => urlencode(env('TS3_SQPORT')),
      'port' => urlencode(env('TS3_PORT')),
      'nickname' => urlencode($nicknames[array_rand($nicknames, 1)])
    ]);
    $this->ts3 = TeamSpeak3::factory($conn);
//      request()->session()->put('ts3', serialize($this->ts3));
//    } else {
//      $this->ts3 = unserialize(request()->session()->get('ts3'));
//    }
  }

  protected function buildMessage($message)
  {
    return $message . " >> blame " . auth()->user()->TSUser->nickname;
  }

  public function kick($uid, Array $properties)
  {
    $client = $this->ts3->clientGetByUid($uid);
    return $client->kick(TeamSpeak3::KICK_SERVER, $this->buildMessage("FEEL MY WRATH"));
  }

  public function kickVashton()
  {
    try {
      if ($vashton = $this->ts3->clientGetByName('Vashton')) {
        $messages = ['LOL BYE', 'You suck!', 'STFU', 'No one wants you'];
        $message = $messages[array_rand($messages, 1)];
        $vashton->kick(TeamSpeak3::KICK_SERVER, $this->buildMessage($message));
      };
    } catch (\Exception $e) {
      return $e->getMessage();
    }
    return redirect(route('ts_admin'));
  }

  public function serverMessage($uid = '', $properties)
  {
    $this->ts3->message($this->buildMessage($properties['message']));
  }

  public function clientMessageAll($clients = [], $message)
  {
    foreach ($clients as $client) {
      $client->message($this->buildMessage($message));
    }
  }

  public function clientPoke($uid, Array $properties)
  {
    $this->ts3->clientGetByUid($uid)->poke($this->buildMessage($properties['message']));
  }

  /**
   * @note doesn't work
   * @param $uid
   * @param array $properties
   */
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
    $uid = Input::has('client_unique_identifier') ? Input::get('client_unique_identifier') : $this->ts3->clientList();
    $data = Input::has('properties') ? Input::get('properties') : [];
    $properties = [];
    foreach ($data as $property) {
      list($key, $value) = explode(':', $property);
      $properties[$key] = $value;
    }

    switch ($action) {
      case "serverMessage":
      case "clientMessageAll":
      case "clientPoke":
        $properties['message'] = Input::get('message');
        break;
    }

    $this->{$action}($uid, $properties);
    return redirect(route('ts_admin'));
  }

  public function info()
  {
    $client = $this->ts3->clientGetByName('Jiko');
    return response()->json($client->infoDb());
  }

  public function bot()
  {
    $this->content('teamspeak::admin_bot', []);
  }

  public function createPrivilegeKey()
  {
    /**
     * $existing_keys = $this->ts3->privilegeKeyList();
     * // token_type => 0 (server)
     * // token_id1 => 6 (group id?)
     * $ts_server_client = $this->ts3->clientGetbyName(request()->user()->TSUser->nickname);
     * $server_groups = explode(',', $ts_server_client->client_servergroups->toString());
     * foreach($server_groups as $group_id) {
     * $group = $this->ts3->serverGroupGetById($group_id);
     * echo $group->name->toString()." ".$group->privilegeKeyCreate()."<br>";
     * }
     * dd();
     * //foreach($this->ts3->serverGroupGetById();
     * dd($ts_server_client->privilegeKeyCreate());
     * $privilege_key = 'sdgsdgsdgsdg';
     * $this->content('teamspeak::my_privelege_key', ['privilege_key' => $privilege_key]);
     * dd($privilege_key);
     */
  }

  public function storeIdentity()
  {
    $user = request()->user();
    $ini = parse_ini_file(request()->file('identity')->getPathName());
    $ts_user = new TSUser([
      'user_id' => $user->id,
      'identity' => $ini['identity'],
      'nickname' => $ini['nickname']
    ]);
    $ts_user->save();
    return redirect(route('ts_admin'));
  }

  public function db()
  {
    //    dd($this->ts3->clientInfoDb(2));
    //    dd($this->ts3->clientListDb());
  }

  public function complaints()
  {
    $complaints = $this->ts3->complaintList();
  }

  public function groups()
  {
    $groups = $this->ts3->serverGroupList();
    dd($groups);
    foreach ($groups as $group) {
      dd($this->ts3->serverGroupPermList($group->sgid, true));
    }
  }

  public function index()
  {
    if (!$ts_user = request()->user()->TSUser) {
      return $this->content('teamspeak::my_identity');
    }

    try {

      // update user info
      $ts_server_client = $this->ts3->clientGetByDbid($ts_user->user_id);
      $ts_user->groups = $ts_server_client->client_servergroups->toString();
      $ts_user->save();

    } catch (\TeamSpeak3_Adapter_ServerQuery_Exception $e) {
      // do nothing
      echo $e->getMessage();
    }
    $client_list = $this->ts3->clientList();
    $errantGodGId = $this->ts3->serverGroupGetByName('Errant God')->getProperty('sgid');
    try {
      $vashton = $this->ts3->clientGetByName('Vashton');
    } catch (\TeamSpeak3_Adapter_ServerQuery_Exception $e) {
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