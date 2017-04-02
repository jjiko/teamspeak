<form action="{{ route('ts_admin') }}" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">
            <h2>Upload your teamspeak identity file</h2>
            <input type="hidden" name="action" value="storeIdentity">
            <div class="input-group">
                <input type="file" name="identity">
                <div class="input-group-btn">
                    <button class="btn btn-success">Upload</button>
                </div>
            </div>
        </div>
    </div>
</form>
