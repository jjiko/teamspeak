<div class="row">
    <div class="col-md-6">
        <h2>Send message to server channel</h2>
        <form action="/admin/teamspeak" method="post">
            <input type="hidden" name="action" value="serverMessage">
            <div class="input-group">
                <input class="form-control" type="text" name="message"/>
                <div class="input-group-btn">
                    <button class="btn btn-success">Send message</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <h2>Private message all connected clients</h2>
        <form action="/admin/teamspeak" method="post">
            <input type="hidden" name="action" value="clientMessageAll">
            <div class="input-group">
                <input class="form-control" type="text" name="message"/>
                <div class="input-group-btn">
                    <button class="btn btn-success">Send message</button>
                </div>
            </div>
        </form>
    </div>
</div>