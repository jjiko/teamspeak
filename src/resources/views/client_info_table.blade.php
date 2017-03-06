<table class='table table-condensed table-striped'>
    <tr>
        <th>Nickname</th>
        <th>Last Seen</th>
        <th>Admin Commands</th>
    </tr>
    @foreach($client_list as $client)
    <?php
    $whitelist = false;
    $groupList = $client->memberOf();
    foreach ($groupList as $group) {
      if ((string)$group === "Errant God") {
        $whitelist = true;
      }
    }
    if ($whitelist) continue;
    if ((string)$client->getProperty('client_unique_identifier') === "serveradmin") continue;
    ?>
        @include('teamspeak::client_info_row')
    @endforeach
</table>