<?php
/**
 * Copyright (c) 2016 by Botorabi. All rights reserved.
 * https://github.com/botorabi/TeamSpeakPHPBots
 *
 * License: MIT License (MIT), read the LICENSE text in
 *          main directory for more details.
 */

namespace Jiko\Teamspeak\Bots\JikoBot;
use com\tsphpbots\bots\BotBase;
use com\tsphpbots\utils\Log;


/**
 * Class for greeting bot
 *
 * @created:  2nd August 2016
 * @author:   Botorabi (boto)
 */
class JikoBot extends BotBase {

  /**
   * @var string  This tag is used for logs
   */
  protected static $TAG = "JikoBot";

  /**
   *
   * @var JikoBotModel  Database model of the bot. All bot paramerers are hold here.
   */
  protected $model = null;

  /**
   * Construct the bot.
   */
  public function __construct() {
    BotBase::__construct();
    $this->model = new JikoBotModel;
  }

  /**
   * Get all available bot IDs.
   * This is used by bot manager for loading all available bots from database.
   *
   * @implements base class method
   *
   * @return array    Array of all available bot IDs, or null if there is no corresponding table in database.
   */
  public static function getAllIDs() {
    return (new JikoBotModel)->getAllObjectIDs();
  }

  /**
   * Create a new bot instance.
   *
   * @implements base class method
   *
   * @return              New instance of the bot.
   */
  public static function create() {
    return new JikoBot();
  }

  /**
   * Load the bot from database.
   *
   * @implements base class method
   *
   * @param int $id       Bot ID (database table row ID)
   * @return boolean      Return false if the object could not be loaded, otherwise true.
   */
  public function loadData($id) {
    Log::debug(self::$TAG, "loading bot type: " . $this->getType() . ", id " . $id);
    if ($this->model->loadObject($id) === false) {
      Log::warning(self::$TAG, "could not load bot from database: id " . $id);
      return false;
    }
    Log::debug(self::$TAG, " bot succesfully loaded, name: '" . $this->getName() . "'");
    return true;
  }

  /**
   * Initialize the bo.
   *
   * @implements base class method
   *
   * @return boolean      Return true if the bot was initialized successfully, otherwise false.
   */
  public function initialize() {
    // set current channel name
    # @todo
    return true;
  }

  /**
   * Get the bot type.
   *
   * @implements base class method
   *
   * @return string       The bot type
   */
  public function getType() {
    return $this->model->botType;
  }

  /**
   * Get the bot name.
   *
   * @implements base class method
   *
   * @return string       The bot name, may be empty if the bot is still not initialized.
   */
  public function getName() {
    return $this->model->name;
  }

  /**
   * Get the unique bot ID.
   *
   * @implements base class method
   *
   * @return int          The unique bot ID > 0, or 0 if the bot is not setup
   *                       or loaded from database yet.
   */
  public function getID() {
    return $this->model->id;
  }

  /**
   * Return the database model.
   *
   * @implements base class method
   *
   * @return JikoBotModel  The database model of this bot.
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * The bot configuration was changed.
   *
   * @implements base class method
   */
  public function onConfigUpdate() {

    // this is just for being on the safe side
    if ($this->getID() > 0) {
      Log::debug(self::$TAG, "reloading bot configuration, type: " . $this->getType() . ", name: " .
        $this->getName() . ", id: " . $this->getID());

      $this->loadData($this->getID());
      $this->initialize();
    }
    else {
      Log::warning(self::$TAG, "the bot was not loaded before, cannot handle its config update!");
    }
  }

  /**
   * This method is called whenever a server event was received.
   *
   * @implements base class method
   *
   * @param Object $event        Event received from ts3 server
   * @param Object $host         Server host
   */
  public function onServerEvent($event, $host) {

    // skip updating if the bot is not active
    if ($this->model->active == 0) {
      return;
    }
    Log::verbose(self::$TAG, "server event triggered: " . $event->getType());

    if(strcmp($event->getType(), "channeledited") === 0) {
      Log::verbose(self::$TAG, "channeledited: " . var_dump($event));
      $clid = (string) $event->invokeruid;
      Log::verbose(self::$TAG, "event cid: " . $event->cid . " model cid: " . $this->model->cid);
      if($event->cid == $this->model->cid) {
        if($clid != $this->model->modUid) {
          $data = $event->getData();
          Log::verbose(self::$TAG, "channeledited data: " . var_dump($data));
          // kick that bitch out for trying to change a moderated channel!
          $client = $host->serverGetSelected()->clientGetByUid($clid);
          $client->kick(\Teamspeak3::KICK_SERVER, $this->model->kickMessage);
        }
      }
    }
  }

  /**
   * Update the bot.
   *
   * @implements base class method
   */
  public function update($deltaTime) {
    // skip updating if the bot is not active
    if ($this->model->active == 0) {
      return;
    }
  }
}
