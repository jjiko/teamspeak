<td>
    <form method="post" action="/admin/teamspeak">
        <input type="hidden" name="action" value="kick">
        <input type="hidden" name="client_unique_identifier"
               value="{{ (string) $client->getProperty('client_unique_identifier') }}">
        <button class="btn btn-danger">Kick this bitch</button>
    </form>
</td>