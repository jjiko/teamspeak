<td>
    <form action="/admin/teamspeak" method="post">
        <input type="hidden" name="action" value="clientPoke">
        <input type="hidden" name="client_unique_identifier"
               value="{{ (string) $client->getProperty('client_unique_identifier') }}">
        <div class="input-group">
            <input class="form-control" type="text" name="message" value="You suck :)">
            <div class="input-group-btn">
                <button class="btn">Poke</button>
            </div>
        </div>
    </form>
</td>