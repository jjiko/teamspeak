<?php
require_once(realpath(__DIR__)."/../../../../vendor/autoload.php");

use Jiko\Teamspeak\Com\Bots\BotManager;

/**
 * Main entry of MultipleBotType example's bot service
 *
 * @created:  4nd August 2016
 * @author:   Botorabi
 */
class App {

  /**
   * @var string Log tag
   */
  protected static $TAG = "App";

  /**
   *
   * @var string App version
   */
  protected $appVersion = "";

  /**
   *
   * @var array Search paths for finding php files
   */
  protected $SEARCH_PATHS = [];

  /**
   * Construct the application, it initializes all necessary resources.
   */
  public function __construct() {
    $this->init();
  }

  /**
   * Initialize the application
   */
  protected function init() {

  }

  /**
   * Start the application
   */
  public function start() {

    echo self::$TAG . " starting the bot service\n";
    echo self::$TAG . " setup the bot manager\n";
    //Log::info(self::$TAG . phpinfo());
    $botmanager = new BotManager();
    if (!$botmanager->initialize()) {
      echo self::$TAG . " could not initialize the bot manager!\n";
      return;
    }

    // this allows communication between the bot and web service (e.g. query server status or notify about bot update in database)
    $botsrvquery = new ServerQuery;
    if ($botsrvquery->initialize($botmanager) === false) {
      echo self::$TAG . " could not initialize bot server query!\n";
      return false;
    }

    // register jikobot
    $botmanager->registerBotClass("Jiko/Teamspeak/Bots/JikoBot/JikoBot");
    // load all bots from database
    $botmanager->loadBots();

    echo self::$TAG . " starting the service \n";

    while(!$botsrvquery->terminate()) {
      try {
        $botmanager->update();
        $botsrvquery->update();
      }
      catch(\Exception $e) {
        // an exception during shutdown is not useful, ignore it!
        if (!$botsrvquery->terminate()) {
          //Log::warning(self::$TAG . " an exception occured: " . $e->getMessage());
          //Log::warning(self::$TAG . "   backtrance: " . $e->getTraceAsString());
        }
        break;
      }
    }

    //Log::info(self::$TAG . " shutdown the service");
    $botmanager->shutdown();
    $botsrvquery->shutdown();
    //Log::info(self::$TAG . " bye");
  }
}

// let's go
$app = new App();
$app->start();