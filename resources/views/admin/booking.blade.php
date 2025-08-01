@extends('admin.layouts.app')

@section('content')
<div class="card today-booking">
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Seluruh Data Booking
        <button id="btn-tambah-booking" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-icons" style="vertical-align:middle;">add_circle</span>
            <span class="d-none d-md-inline">Jadwalkan Pemeliharaan</span>
        </button>
    </h2>
    <div class="table-responsive">
        <table id="bookingTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Nama</th>
                    <th>No. Unit</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="booking-today-body">
                @if(isset($bookings) && count($bookings))
                    @foreach($bookings as $booking)
                        <tr data-id="{{ $booking->id }}">
                            <td></td> <!-- Nomor otomatis oleh DataTables -->
                            <td>{{ $booking->date }}</td>
                            <td>{{ $booking->hour }}</td>
                            <td>{{ $booking->name }}</td>
                            @if($booking->is_admin)
                                <td> Pemeliharaan </td>
                            @else
                                <td>{{ $booking->unit }}</td>
                            @endif
                            <td>
                                @php
                                    $bookingDateTime = \Carbon\Carbon::parse($booking->date . ' ' . $booking->hour.':59');
                                @endphp
                                @if($booking->status === 'active')
                                    @if($bookingDateTime->lt(now()))
                                        <span class="label label-grey">Terlewat</span>
                                    @else
                                        <span class="label label-green">Aktif</span>
                                    @endif
                                @elseif($booking->status === 'cancelled')
                                    <span class="label label-red">Dibatalkan</span>
                                @else
                                    <span class="label label-default">{{ ucfirst($booking->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    @if($booking->status === 'active' && !$bookingDateTime->lt(now()))
                                        <button class="action-btn cancel" title="Batalkan" style="margin-left:4px;">
                                            <span class="material-icons" style="vertical-align:middle;">cancel</span>
                                            <span class="d-none d-md-inline">Batalkan</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada booking hari ini.</td>
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
        var rowCount = $('#bookingTable tbody tr[data-id]').length;
        if (rowCount > 0) {
            var t = $('#bookingTable').DataTable({
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    },
                    "zeroRecords": "Tidak ada data ditemukan",
                },
                "columnDefs": [
                    { "orderable": false, "searchable": false, "targets": 0 }
                ],
                "order": [[1, 'desc']],
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'action-btn primary',
                        exportOptions: {
                            columns: [1,2,3,4,5] // Kolom tanpa "Aksi"
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        className: 'action-btn primary',
                        exportOptions: {
                            columns: [1,2,3,4,5] // Kolom tanpa "Aksi"
                        },
                        customize: function (doc) {
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                        }
                    }
                ]
            });

            // Nomor otomatis pada kolom pertama
            t.on('order.dt search.dt', function () {
                t.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
        }

        // SweetAlert konfirmasi batalkan booking
        $('#bookingTable').on('click', '.action-btn.cancel', function(e) {
            e.preventDefault();
            var $tr = $(this).closest('tr');
            // Pastikan ada data-id booking di <tr>
            var bookingId = $tr.data('id') || $tr.attr('data-id');
            if (!bookingId) {
                Swal.fire('Error', 'ID booking tidak ditemukan.', 'error');
                return;
            }
            Swal.fire({
                title: 'Batalkan Booking?',
                text: "Apakah Anda yakin ingin membatalkan booking ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/admin/booking/cancel') }}/" + bookingId,
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire('Berhasil', 'Booking berhasil dibatalkan!', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat membatalkan booking.', 'error');
                        }
                    });
                }
            });
        });

        // Modal SweetAlert2 untuk tambah booking
        $('#btn-tambah-booking').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Jadwalkan Pemeliharaan',
                html:
                    `<div style="overflow:auto;max-height:430px;">
                        <iframe src="{{ route('admin.booking.inframe') }}" width="100%" height="400" frameborder="0" style="display:block;border:0;"></iframe>
                    </div>`,
                customClass: {
                    popup: 'swal2-modal-custom-height'
                },
                focusConfirm: false,
                showCancelButton: false,
                showConfirmButton: false,
                didClose: () => {
                    location.reload();
                }
                // Hanya tombol close (X) di pojok kanan atas
            });
        });
    });
</script>
@endpush