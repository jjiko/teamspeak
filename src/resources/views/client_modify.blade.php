<td>
    <form action="/admin/teamspeak" method="post">
        <input type="hidden" name="action" value="clientModify">
        <input type="hidden" name="client_unique_identifier"
               value="{{ (string) $client->getProperty('client_unique_identifier') }}">
        <input type="hidden" name="properties[]" value="CLIENT_NICKNAME:saoidgjioasjgoasjdgio">
        <button>Rename</button>
    </form>
</td>