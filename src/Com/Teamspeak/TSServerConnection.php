<?php
/**
 * Copyright (c) 2016 by Botorabi. All rights reserved.
 * https://github.com/botorabi/TeamSpeakPHPBots
 *
 * License: MIT License (MIT), read the LICENSE text in
 *          main directory for more details.
 */

namespace Jiko\Teamspeak\Com\Teamspeak;
use Dotenv\Dotenv;

/**
 * Class managing the TeamSpeak server connection
 *
 * @package   com\tsphpbots\teamspeak
 * @created   23th August 2016
 * @author    Botorabi
 */
class TSServerConnections
{

  /**
   * @var string Class tag for logging
   */
  protected static $TAG = "TSServerConnections";

  /**
   * @var Object  TS3 server objects
   */
  protected $ts3servers = [];

  /**
   * @var int  Notification registration flag for "server"
   */
  public static $REG_FLAG_SERVER = 1;

  /**
   * @var int  Notification registration flag for "channel"
   */
  public static $REG_FLAG_CHANNEL = 2;

  /**
   * @var int  Notification registration flag for "textserver"
   */
  public static $REG_FLAG_TEXTSERVER = 4;

  /**
   * @var int  Notification registration flag for "textchannel"
   */
  public static $REG_FLAG_TEXTCHANNEL = 8;

  /**
   * @var int  Notification registration flag for "textprivate"
   */
  public static $REG_FLAG_TEXTPRIVATE = 16;

  /**
   * @var int  Default notification registration flags
   */
  public static $REG_FLAGS_DEFAULT = 26; // 2 | 8 | 16;


  /**
   * Construct the TS3 server connection manager.
   */
  public function __construct()
  {
    $dotenv = new Dotenv(__DIR__ . '/../../../../../../');
    $dotenv->load();
    $this->init();
  }

  /**
   * Initialize the TeamSpeak3 library.
   */
  protected function init()
  {
    \TeamSpeak3::init();
    \TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryConnected", array($this, "onConnect"));
    \TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyLogin", array($this, "onLogin"));
    \TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", array($this, "onTimeout"));
    \TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyEvent", array($this, "onEvent"));
  }

  /**
   * Establish a connection to TeamSpeak server.
   *
   * @param string $nickName Client's nick name, it should be unique!
   * @param Object $cbEvent A callable function for getting TS3 server events, signature: onEvent($event, $host, $stream)
   *                                Pass null if not needed.
   * @param int $registrationFlags A combination of registration flags
   * @return Object                TS3 Server
   */
  public function createConnection($nickName, $cbEvent, $registrationFlags = 26)
  {
    if (!is_null($cbEvent) && !is_callable($cbEvent, true)) {
      echo self::$TAG . "cbEvent is not callable!\n";
      return null;
    }
    $nick = str_replace(" ", "%20", trim($nickName));
    if (strlen($nick) < 1) {
      echo self::$TAG . " invalid nick name!\n";
      return null;
    }

    try {
      echo self::$TAG . " connecting the teamspeak server ...\n";
      $querytext = "serverquery://" . getenv("TS3_USER") . ":" .
        getenv('TS3_PASS') . "@" .
        getenv('TS3_SERVER') . ":" .
        getenv('TS3_PORT') . "/?server_port=" .
        getenv('TS3_SQPORT') .
        "&blocking=0" .
        "&nickname=" . $nick;
      echo $querytext."\n";

      $server = \TeamSpeak3::factory($querytext);
      $stream = $server->getParent()->getParent()->getTransport()->getStream();
      $this->ts3servers[] = [
        "stream" => $stream,
        "server" => $server,
        "cbEvent" => $cbEvent
      ];
    } catch (\Exception $e) {
      echo self::$TAG . "error occurred during initializing the teamspeak server query interface!\n";
      echo self::$TAG . " reason: " . $e->getMessage() . "\n";
      return null;
    }

    $regs = [];
    if (($registrationFlags & self::$REG_FLAG_CHANNEL) !== 0) {
      $server->notifyRegister("channel");
      $regs[] = "channel";
    }
    if (($registrationFlags & self::$REG_FLAG_SERVER) !== 0) {
      $server->notifyRegister("server");
      $regs[] = "server";
    }
    if (($registrationFlags & self::$REG_FLAG_TEXTSERVER) !== 0) {
      $server->notifyRegister("textserver");
      $regs[] = "textserver";
    }
    if (($registrationFlags & self::$REG_FLAG_TEXTCHANNEL) !== 0) {
      $server->notifyRegister("textchannel");
      $regs[] = "textchannel";
    }
    if (($registrationFlags & self::$REG_FLAG_TEXTPRIVATE) !== 0) {
      $server->notifyRegister("textprivate");
      $regs[] = "textprivate";
    }

    //Log::info(self::$TAG . " adding notify registration for: " . implode(" ", $regs));

    return $server;
  }

  /**
   * Shutdown all TeamSpeak server connections.
   */
  public function shutdown()
  {
    //Log::debug(self::$TAG . "shutting down the teamspeak connection manager");
    // close all server connections
    foreach ($this->ts3servers as $srv) {
      $srv["server"]->request("quit");
    }
    $this->ts3servers = [];
  }

  /**
   * Update the connection manager. Call this periodically.
   *
   * @param int $timeout Maximal timeout used for polling every connection. Pass 0 in order to wait for ever.
   */
  public function update($timeout = 0)
  {
    foreach ($this->ts3servers as $srv) {
      $adapter = $srv["server"]->getAdapter();
      if ($adapter->getQueryLastTimestamp() < time() - 300) {
        ////Log::debug(self::$TAG, "sending keep-alive command");
        $adapter->request("clientupdate");
      }
      // pump the events of ts server
      $fetchcount = 10;
      while ($fetchcount-- > 0) {
        if (is_null($adapter->wait($timeout))) {
          break;
        }
      }
    }
  }

  /**
   * Get the count of TeamSpeak server connections.
   *
   * @return int  Count of TeamSpeak connections
   */
  public function getCountTsConnections()
  {
    return count($this->ts3servers);
  }

  /**
   * Given a connection stream return the corresponding server entry.
   *
   * @param Object $stream Connection stream
   * @return array            The TS3 server connection entry, null if no entry exists with that stream.
   */
  protected function getTsServer($stream)
  {
    foreach ($this->ts3servers as $srv) {
      if ($srv["stream"] === $stream) {
        return $srv;
      }
    }
    return null;
  }

  // ================= [ BEGIN OF TS3Teamspeak CALLBACK FUNCTION DEFINITIONS ] =================

  /**
   * Callback method for 'serverqueryConnected' signals.
   *
   * @param  TeamSpeak3_Adapter_ServerQuery $adapter
   * @return void
   */
  public function onConnect(TeamSpeak3_Adapter_ServerQuery $adapter)
  {
    //Log::info(self::$TAG . "connected to TeamSpeak 3 Server on " . $adapter->getHost());
    $version = $adapter->getHost()->version();
    $stream = $adapter->getTransport()->getStream();
    //Log::info(self::$TAG . "  server is running with version " . $version["version"] . " on " . $version["platform"] . ", stream: " . $stream);
  }

  /**
   * Callback method for 'serverqueryWaitTimeout' signals.
   *
   * @param integer $seconds
   * @param TeamSpeak3_Adapter_ServerQuery $adapter
   * @return void
   */
  public function onTimeout($seconds, \TeamSpeak3_Adapter_ServerQuery $adapter)
  {
    if ($adapter->getQueryLastTimestamp() < time() - 300) {
      ////Log::debug(self::$TAG, "sending keep-alive command");
      $adapter->request("clientupdate");
    }
  }

  /**
   * Callback method for 'notifyLogin' signals.
   *
   * @param  TeamSpeak3_Node_Host $host
   * @return void
   */
  public function onLogin(\TeamSpeak3_Node_Host $host)
  {
    //Log::debug(self::$TAG . "authenticated as user " . $host->whoamiGet("client_login_name"));
  }

  /**
   * Callback method for 'notifyEvent' signals.
   *
   * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
   * @param  TeamSpeak3_Node_Host $host
   * @return void
   */
  public function onEvent(\TeamSpeak3_Adapter_ServerQuery_Event $event, \TeamSpeak3_Node_Host $host)
  {
    $stream = $host->getParent()->getTransport()->getStream();
    $srv = $this->getTsServer($stream);
    if (is_null($srv)) {
      //Log::warning(self::$TAG . "cannot notify about server event, stream " . $stream . " was not found!");
    } else {
      if (!empty($srv["cbEvent"])) {
        $srv["cbEvent"]($event, $host, $stream);
      }
    }
  }
}