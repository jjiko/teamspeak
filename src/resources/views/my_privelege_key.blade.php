@if(!isset($privilege_key))
    <div class="row">
        <div class="col-md-12">
            <div class="pull-right">
                <form action="{{ route('ts_admin') }}" method="post">
                    <input type="hidden" value="createPrivilegeKey" name="action">
                    <button class="btn btn-warning">Generate privilege key</button>
                </form>
            </div>
        </div>
    </div>
@else
    {{ $privilege_key }}
@endif