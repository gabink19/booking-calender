@extends('admin.layouts.app')

@section('content')
<div class="card log-notif">
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Log Send Notification
    </h2>
    <div class="table-responsive">
        <table id="logTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Message</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Last Update</th>
                </tr>
            </thead>
            <tbody id="log-notif-body">
                @if(isset($logs) && count($logs))
                    @foreach($logs as $log)
                        <tr>
                            <td></td> <!-- Nomor otomatis oleh DataTables -->
                            <td>{{ $log->messages }}</td>
                            <td>{{ $log->unit }}</td>
                            <td>
                                @if($log->status=='sent')
                                    <span class="label label-green">Sent</span>
                                @else
                                    <span class="label label-red">Failed</span>
                                @endif
                            </td>
                            <td>{{ $log->updated_at }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center">{{ __('user_admin.no_data') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('head')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    #logTable thead th {
        background: #3041b7 !important;
        color: #fff !important;
    }
    .action-btn.primary {
        background: linear-gradient(90deg, #5c7cff 60%, #91e0fd 100%);
        color: #fff;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 12px;
        cursor: pointer;
        transition: background 0.15s, color 0.15s, box-shadow 0.15s;
        box-shadow: 0 1px 4px rgba(60,80,180,0.07);
        text-decoration: none !important;
        min-width: 0;
    }
    .action-btn.primary:hover, .action-btn.primary:focus {
        filter: brightness(0.97);
        box-shadow: 0 2px 8px rgba(60,80,180,0.13);
    }
</style>
@endpush

@push('scripts')
<!-- jQuery, DataTables, SweetAlert2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var t = $('#logTable').DataTable({
            "language": {
                "search": "{{ __('user_admin.search') }}",
                "lengthMenu": "{{ __('user_admin.length_menu') }}",
                "info": "{{ __('user_admin.info') }}",
                "paginate": {
                    "first": "{{ __('user_admin.first') }}",
                    "last": "{{ __('user_admin.last') }}",
                    "next": "{{ __('user_admin.next') }}",
                    "previous": "{{ __('user_admin.previous') }}"
                },
                "zeroRecords": "{{ __('user_admin.zero_records') }}",
            },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": 0 },
                { "orderable": false, "searchable": false, "targets": 1 },
                { "orderable": false, "searchable": false, "targets": 2 },
                { "orderable": false, "searchable": false, "targets": 3 },
                { "orderable": false, "searchable": false, "targets": 4 },
            ]
        });

        // Nomor otomatis pada kolom pertama
        t.on('order.dt search.dt', function () {
            t.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
        
    });
</script>
@endpush