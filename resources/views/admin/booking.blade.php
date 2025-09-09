@extends('admin.layouts.app')

@section('content')
<div class="card today-booking">
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        {{ __('booking_admin.all_booking') }}
        <button id="btn-tambah-booking" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-icons" style="vertical-align:middle;">add_circle</span>
            <span class="d-none d-md-inline">{{ __('booking_admin.schedule_maintenance') }}</span>
        </button>
    </h2>
    <div class="table-responsive">
        <table id="bookingTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>{{ __('booking_admin.no') }}</th>
                    <th>{{ __('booking_admin.date') }}</th>
                    <th>{{ __('booking_admin.hour') }}</th>
                    <th>{{ __('booking_admin.name') }}</th>
                    <th>{{ __('booking_admin.unit') }}</th>
                    <th>{{ __('booking_admin.status') }}</th>
                    <th>{{ __('booking_admin.updated_at') }}</th>
                    <th>{{ __('booking_admin.action') }}</th>
                </tr>
            </thead>
            <tbody id="booking-today-body">
                @if(isset($bookings) && count($bookings))
                    @foreach($bookings as $booking)
                        <tr data-id="{{ $booking->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $booking->date }}</td>
                            <td>{{ $booking->hour < 10 ? '0' . $booking->hour : $booking->hour }}:00</td>
                            <td>{{ $booking->name }}</td>
                            @if($booking->is_admin)
                                <td>{{ __('booking_admin.maintenance') }}</td>
                            @else
                                <td>{{ $booking->unit }}</td>
                            @endif
                            <td>
                                @php
                                    $bookingDateTime = \Carbon\Carbon::parse($booking->date . ' ' . $booking->hour.':59');
                                @endphp
                                @if($booking->status === 'active')
                                    @if($bookingDateTime->lt(now()))
                                        <span class="label label-grey">{{ __('booking_admin.past') }}</span>
                                    @else
                                        <span class="label label-green">{{ __('booking_admin.active') }}</span>
                                    @endif
                                @elseif($booking->status === 'cancelled')
                                    <span class="label label-red">{{ __('booking_admin.cancelled') }}</span>
                                @else
                                    <span class="label label-default">{{ ucfirst($booking->status) }}</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($booking->updated_at)->format('d M Y H:i') }}</td>
                            <td>
                                <div class="actions">
                                    @if($booking->status === 'active' && !$bookingDateTime->lt(now()))
                                        <button class="action-btn cancel" title="{{ __('booking_admin.cancel') }}" style="margin-left:4px;">
                                            <span class="material-icons" style="vertical-align:middle;">cancel</span>
                                            <span class="d-none d-md-inline">{{ __('booking_admin.cancel') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center">{{ __('booking_admin.no_booking_today') }}</td>
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
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css"/>
<style>
    #bookingTable thead th {
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
<!-- jQuery dan DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
    $(document).ready(function() {
        var t = $('#bookingTable').DataTable({
            "language": {
                "search": "{{ __('booking_admin.search') }}",
                "lengthMenu": "{{ __('booking_admin.length_menu') }}",
                "info": "{{ __('booking_admin.info') }}",
                "paginate": {
                    "first": "{{ __('booking_admin.first') }}",
                    "last": "{{ __('booking_admin.last') }}",
                    "next": "{{ __('booking_admin.next') }}",
                    "previous": "{{ __('booking_admin.previous') }}"
                },
                "zeroRecords": "{{ __('booking_admin.zero_records') }}",
            },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": 0 }
            ],
            "order": [[1, 'desc']],
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: "{{ __('booking_admin.export_excel') }}",
                    className: 'action-btn primary',
                    exportOptions: { columns: [1,2,3,4,5,6] }
                },
                {
                    extend: 'pdfHtml5',
                    text: "{{ __('booking_admin.export_pdf') }}",
                    className: 'action-btn primary',
                    exportOptions: { columns: [1,2,3,4,5,6] },
                    customize: function (doc) {
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    }
                }
            ]
        });

        // SweetAlert konfirmasi batalkan booking
        $('#bookingTable').on('click', '.action-btn.cancel', function(e) {
            e.preventDefault();
            var $tr = $(this).closest('tr');
            var bookingId = $tr.data('id') || $tr.attr('data-id');
            if (!bookingId) {
                Swal.fire('Error', '{{ __("booking_admin.booking_id_not_found") }}', 'error');
                return;
            }
            Swal.fire({
                title: '{{ __("booking_admin.cancel_booking_title") }}',
                text: "{{ __('booking_admin.cancel_booking_text') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __("booking_admin.confirm_cancel") }}',
                cancelButtonText: '{{ __("booking_admin.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/admin/booking/cancel') }}/" + bookingId,
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('{{ __("booking_admin.success") }}', '{{ __("booking_admin.cancel_success") }}', 'success').then(() => {
                                    location.reload();
                                });
                            }else{
                                Swal.fire('{{ __("booking_admin.failed") }}', res.error, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('{{ __("booking_admin.failed") }}', '{{ __("booking_admin.cancel_failed") }}', 'error');
                        }
                    });
                }
            });
        });

        // Modal SweetAlert2 untuk tambah booking
        $('#btn-tambah-booking').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ __("booking_admin.schedule_maintenance") }}',
                html:
                    `<div style="overflow:auto;max-height:430px;">
                        <iframe src="{{ route('admin.booking.inframe') }}" width="100%" height="400" frameborder="0" style="display:block;border:0;"></iframe>
                    </div>`,
                customClass: { popup: 'swal2-modal-custom-height' },
                focusConfirm: false,
                showCancelButton: false,
                showConfirmButton: false,
                didClose: () => { location.reload(); }
            });
        });
    });
</script>
@endpush