<?php
namespace Jiko\Teamspeak\Http\Controllers;

use Jiko\Admin\Http\Controllers\AdminController as Controller;
use Jiko\Teamspeak\Com\Service\ClientQuery;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class BotServerController extends Controller
{
  protected $layout = 'admin::layouts.default';

  protected static $TAG = 'JikoBot ';

  public function __construct()
  {
    parent::__construct();

  }

  public function index()
  {
    switch (Input::get('cmd')) {
      case "status":
        return $this->cmdServerStatus();

      case "start":
        return $this->cmdStartBotServer();

      case "stop":
        return $this->cmdStopBotServer();
    }

    return abort(400);
  }

  /**
   * Create a JSON response for the bot server status.
   */
  protected function cmdServerStatus()
  {

    //Log::debug(self::$TAG . "get bot service status...");

    $servicequery = new ClientQuery;
    if ($servicequery->connect() === false) {
      return response()->json(["result" => "nok"]);
    }

    $state = $servicequery->getStatus();
    $servicequery->shutdown();

    if ($state) {
      $res = ["result" => "ok", "data" => json_decode($state)];
      return response()->json($res);
    }

    return response()->json(["result" => "nok"]);
  }

  /**
   * Start the bot server. Ignores the call if the server is already running.
   */
  protected function cmdStartBotServer()
  {

    Log::debug(self::$TAG . "starting the bot service...");

    $servicequery = new ClientQuery;
    $servicequery->connect();
    if ($servicequery->connect() !== false) {
      return response()->json(["result" => "nok"]);
    }
    $state = $servicequery->getStatus();
    // check if the server is already running
    if (!is_null($state)) {
      Log::debug(self::$TAG . "bot service is already running!");
      return response()->json(["result" => "nok"]);
    }
    $startcmd = getenv('TS3_CMD_START');
    Log::debug(self::$TAG . "starting: " . $startcmd);
    // on ms windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $out = "";
      $ret = 0;
      exec("'" . $startcmd . "'", $out, $ret);
    } else {
      // on linux or mac
      $out = shell_exec($startcmd);
    }
    return response()->json(["result" => $out]);
  }

  /**
   * Stop the bot server.
   */
  protected function cmdStopBotServer()
  {

    Log::debug(self::$TAG . "stopping the bot service...");

    $servicequery = new ClientQuery;
    $servicequery->connect();
    if ($servicequery->connect() === false) {
      return response()->json(["result" => "nok"]);
    }

    $state = $servicequery->stopService();

    if (is_null($state)) {
      Log::debug(self::$TAG . "bot service seems not running!");
      return response()->json(["result" => "nok"]);
    }

    return response()->json(["result" => "ok"]);
  }

  /**
   * Add a bot given its type and ID.
   *
   * @param string $botType Bot type
   * @param int $id Bot ID
   * @return json status
   */
  protected function cmdBotAdd($botType, $id)
  {

    Log::debug(self::$TAG . "adding bot, id: " . $id);

    $servicequery = new ClientQuery;
    $servicequery->connect();
    if ($servicequery->connect() === false) {
      return response()->json(["result" => "nok"]);
    }

    $result = $servicequery->botAdd($botType, $id);

    if (is_null($result)) {
      Log::debug(self::$TAG . " could not add bot");
      return response()->json(["result" => "nok"]);
    }

    return response()->json(["result" => "ok"]);
  }

  /**
   * Update the bot given its ID and type.
   *
   * @param string $botType Bot type
   * @param int $id Bot ID
   * @return json status
   */
  protected function cmdBotUpdate($botType, $id)
  {

    Log::debug(self::$TAG . "updating bot, id: " . $id);

    $servicequery = new ClientQuery;
    $servicequery->connect();
    if ($servicequery->connect() === false) {
      return response()->json(["result" => "nok"]);
    }

    $result = $servicequery->botUpdate($botType, $id);

    if (is_null($result)) {
      Log::debug(self::$TAG . " could not update bot");
      return response()->json(["result" => "nok"]);
    }

    return response()->json(["result" => "ok"]);
  }

  /**
   * Delete the bot given its ID and type.
   *
   * @param string $botType Bot type
   * @param int $id Bot ID
   * @return json status
   */
  protected function cmdBotDelete($botType, $id)
  {

    Log::debug(self::$TAG . "deleting bot, id: " . $id);

    $servicequery = new ClientQuery;
    $servicequery->connect();
    if ($servicequery->connect() === false) {
      return response()->json(["result" => "nok"]);
    }

    $result = $servicequery->botDelete($botType, $id);

    if (is_null($result)) {
      Log::debug(self::$TAG . " could not delete bot");
      return response()->json(["result" => "nok"]);
    }

    return response()->json(["result" => "ok"]);
  }

  /**
   * Send a message to the bot given its ID and type.
   *
   * @param string $botType Bot type
   * @param int $id Bot ID
   * @param string $text Message text
   * @return json status
   */
  protected function cmdBotMessage($botType, $id, $text)
  {

    Log::debug(self::$TAG . "sending message to bot, id: " . $id);

    $servicequery = new ClientQuery;
    $servicequery->connect();
    if ($servicequery->connect() === false) {
      return result()->json(["result" => "nok"]);
    }

    $result = $servicequery->botMessage($botType, $id, $text);

    if (is_null($result)) {
      Log::debug(self::$TAG . " could not send message to bot");
      return ["result" => "nok"];
    }

    return response()->json(["result" => "ok"]);
  }
}