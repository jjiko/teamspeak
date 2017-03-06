<td>
    <form action="/admin/teamspeak" method="post">
        <input type="hidden" name="action" value="clientMove">
        <input type="hidden" name="client_unique_identifier"
               value="{{ (string) $client->getProperty('client_unique_identifier') }}">
        <input type="hidden" name="properties[]" value="channel_id:6">
        <button class="btn">Move to fags</button>
    </form>
</td>