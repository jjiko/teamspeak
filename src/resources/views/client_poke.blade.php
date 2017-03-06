<td>
    <form action="/admin/teamspeak" method="post">
        <input type="hidden" name="action" value="clientPoke">
        <input type="hidden" name="client_unique_identifier"
               value="{{ (string) $client->getProperty('client_unique_identifier') }}">
        <input type="text" name="message" value="You suck :)">
        <button class="btn">Poke</button>
    </form>
</td>