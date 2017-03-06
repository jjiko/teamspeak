<?php
/* initialize */
TeamSpeak3::init();

try
{
  /* subscribe to various events */
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryConnected", "onConnect");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", "onTimeout");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("updateWaitTimeout", "onTimeout");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyLogin", "onLogin");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyEvent", "onEvent");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyServerselected", "onSelect");

  /* connect to server, login and get TeamSpeak3_Node_Host object by URI */
  $ts3 = TeamSpeak3::factory("serverquery://serveradmin:1tmxrEZx@127.0.0.1:10011/?server_port=9987&blocking=0");

  /* wait for events */
  while(1) $ts3->getAdapter()->wait();
}
catch(Exception $e)
{
  die("[ERROR]  " . $e->getMessage() . "\n");
}

// ================= [ BEGIN OF CALLBACK FUNCTION DEFINITIONS ] =================

/**
 * Callback method for 'serverqueryConnected' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery $adapter
 * @return void
 */
function onConnect(TeamSpeak3_Adapter_ServerQuery $adapter)
{
  echo "[SIG]\tconnected to TeamSpeak 3 Server on " . $adapter->getHost() . "\n";

  echo "[INFO]\tserver is running with version " . $adapter->getHost()->version("version") . " on " . $adapter->getHost()->version("platform") . "\n";
}

/**
 * Callback method for 'serverqueryWaitTimeout' signals.
 *
 * @param  integer $seconds
 * @return void
 */
function onTimeout($seconds, TeamSpeak3_Adapter_ServerQuery $adapter)
{
  echo "[SIG]\tno reply from the server for " . $seconds . " seconds\n";

  if($adapter->getQueryLastTimestamp() < time()-300)
  {
    echo "[INFO]\tsending keep-alive command\n";

    $adapter->request("clientupdate");
  }
}

/**
 * Callback method for 'notifyLogin' signals.
 *
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onLogin(TeamSpeak3_Node_Host $host)
{
  echo "[SIG]\tauthenticated as user " . $host->whoamiGet("client_login_name") . "\n";
}

/**
 * Callback method for 'notifyEvent' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onEvent(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
  echo "[SIG]\treceived notification " . $event->getType() . "\n\t" . $event->getMessage() . "\n";
}

/**
 * Callback method for 'notifyServerselected' signals.
 *
 * @param  string $cmd
 * @return void
 */
function onSelect(TeamSpeak3_Node_Host $host)
{
  echo "[SIG]\tselected virtual server with ID " . $host->serverSelectedId() . "\n";

  $host->serverGetSelected()->notifyRegister("server");
  $host->serverGetSelected()->notifyRegister("channel");
  $host->serverGetSelected()->notifyRegister("textserver");
  $host->serverGetSelected()->notifyRegister("textchannel");
  $host->serverGetSelected()->notifyRegister("textprivate");
}