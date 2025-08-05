@extends('admin.layouts.app')

@section('content')
<div class="card user-management">
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Manajemen User
        <button id="btn-tambah-user" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-icons" style="vertical-align:middle;">person_add</span>
            <span class="d-none d-md-inline">{{ __('user_admin.add_user') }}</span>
        </button>
    </h2>
    <div class="table-responsive">
        <table id="userTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>{{ __('user_admin.no') }}</th>
                    <th>{{ __('user_admin.unit') }}</th>
                    <th>{{ __('user_admin.username') }}</th>
                    <th>{{ __('user_admin.name') }}</th>
                    <th>{{ __('user_admin.whatsapp') }}</th>
                    <th>{{ __('user_admin.role') }}</th>
                    <th>{{ __('user_admin.status') }}</th>
                    <th>{{ __('user_admin.action') }}</th>
                </tr>
            </thead>
            <tbody id="user-management-body">
                @if(isset($users) && count($users))
                    @foreach($users as $user)
                        <tr data-id="{{ $user->uuid }}">
                            <td></td> <!-- Nomor otomatis oleh DataTables -->
                            <td>{{ $user->unit }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->whatsapp }}</td>
                            <td>
                                @if($user->is_admin)
                                    <span>{{ __('user_admin.admin') }}</span>
                                @else
                                    <span>{{ __('user_admin.user') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="label label-green">{{ __('user_admin.active') }}</span>
                                @else
                                    <span class="label label-red">{{ __('user_admin.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(!$user->is_admin)
                                <div class="actions">
                                    <button class="action-btn detail" title="Edit User">
                                        <span class="material-icons" style="vertical-align:middle;">edit</span>
                                        <span class="d-none d-md-inline">{{ __('user_admin.edit') }}</span>
                                    </button>
                                </div>
                                @endif
                            </td>
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
    #userTable thead th {
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
        var t = $('#userTable').DataTable({
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
                { "orderable": false, "searchable": false, "targets": 0 }
            ],
            "order": [[1, 'asc']]
        });

        // Nomor otomatis pada kolom pertama
        t.on('order.dt search.dt', function () {
            t.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        // SweetAlert2 Modal Form
        $('#btn-tambah-user').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ __("user_admin.add_user") }}',
                html:
                    `<form id="form-tambah-user" autocomplete="off" style="font-size: 0.80em !important;">
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="unit" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.unit") }}</label>
                            <input type="text" id="unit" class="swal2-input" style="width:70%;" placeholder="{{ __("user_admin.unit") }}" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="username" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.username") }}</label>
                            <input type="text" id="username" class="swal2-input" style="width:70%;" placeholder="{{ __("user_admin.username") }}" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="password" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.password") }}</label>
                            <input type="password" id="password" class="swal2-input" style="width:70%;" placeholder="{{ __("user_admin.password") }}" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="name" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.name") }}</label>
                            <input type="text" id="name" class="swal2-input" style="width:70%;" placeholder="{{ __("user_admin.name") }}" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="whatsapp" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.whatsapp") }}</label>
                            <input type="text" id="whatsapp" class="swal2-input" style="width:70%;" placeholder="{{ __("user_admin.whatsapp") }}" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="role" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.role") }}</label>
                            <select id="role" class="swal2-input" style="width:64%;height:40px;">
                                <option value="0">{{ __("user_admin.user") }}</option>
                                <option value="1">{{ __("user_admin.admin") }}</option>
                            </select>
                        </div>
                        <div style="display:flex;align-items:center;">
                            <label for="status" style="width:110px;text-align:right;margin-right:10px;">{{ __("user_admin.status") }}</label>
                            <select id="status" class="swal2-input" style="width:64%;height:40px;">
                                <option value="">{{ __("user_admin.choose_status") }}</option>
                                <option value="1">{{ __("user_admin.active") }}</option>
                                <option value="0">{{ __("user_admin.inactive") }}</option>
                            </select>
                        </div>
                    </form>`,
                confirmButtonText: '{{ __("user_admin.save") }}',
                cancelButtonText: '{{ __("user_admin.cancel") }}',
                focusConfirm: false,
                showCancelButton: true,
                preConfirm: () => {
                    return {
                        unit: $('#unit').val(),
                        username: $('#username').val(),
                        password: $('#password').val(),
                        name: $('#name').val(),
                        whatsapp: $('#whatsapp').val(),
                        role: $('#role').val(),
                        status: $('#status').val()
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim data ke server via AJAX (contoh, sesuaikan endpoint Anda)
                    $.ajax({
                        url: "{{ route('admin.user.create') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            unit: result.value.unit,
                            username: result.value.username,
                            password: result.value.password,
                            name: result.value.name,
                            whatsapp: result.value.whatsapp,
                            role: result.value.role,
                            is_active: result.value.status
                        },
                        success: function(res) {
                            Swal.fire('{{ __("user_admin.success") }}', '{{ __("user_admin.add_success") }}', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let msg = '{{ __("user_admin.add_failed") }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __("user_admin.failed") }}',
                                html: msg,
                                confirmButtonText: '{{ __("user_admin.retry") }}',
                                cancelButtonText: '{{ __("user_admin.close") }}',
                                focusConfirm: false,
                                didOpen: () => {
                                    // Isi ulang data yang sudah diinput sebelumnya
                                    $('#unit').val(result.value.unit);
                                    $('#username').val(result.value.username);
                                    $('#password').val(result.value.password);
                                    $('#name').val(result.value.name);
                                    $('#whatsapp').val(result.value.whatsapp);
                                    $('#role').val(result.value.role);
                                    $('#status').val(result.value.status);
                                }
                            }).then((retry) => {
                                if (retry.isConfirmed) {
                                    $('#btn-tambah-user').trigger('click');
                                    // Atau bisa juga panggil ulang SweetAlert2 dengan data lama
                                }
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.action-btn.detail', function(e) {
            e.preventDefault();
            // Ambil id user dari baris tabel (misal: data-id pada <tr> atau gunakan data-* pada tombol)
            let $tr = $(this).closest('tr');
            let userId = $tr.data('id') || $tr.find('td[data-id]').data('id');
            if (!userId) {
                // Jika belum ada, tambahkan data-id="{{ $user->id }}" pada <tr> di foreach
                Swal.fire('Error', 'ID user tidak ditemukan.', 'error');
                return;
            }

            // Ambil data user via AJAX
            $.get("{{ url('/admin/user') }}/" + userId, function(response) {
                if (response.success) {
                    let user = response.data;
                    Swal.fire({
                        title: '{{ __("user_admin.edit_user") }}',
                        html:
                            `<form id="form-edit-user" autocomplete="off" style="font-size: 0.80em !important;">
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                    <label for="unit" style="width:110px;text-align:right;margin-right:10px;">No. Unit</label>
                                    <input type="text" id="unit" class="swal2-input" style="width:70%;" value="${user.unit}" required>
                                </div>
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                    <label for="username" style="width:110px;text-align:right;margin-right:10px;">ID Pengguna</label>
                                    <input type="text" id="username" class="swal2-input" style="width:70%;" value="${user.username}" required>
                                </div>
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                    <label for="password" style="width:110px;text-align:right;margin-right:10px;">Password</label>
                                    <input type="password" id="password" class="swal2-input" style="width:70%;" placeholder="(Biarkan kosong jika tidak diubah)">
                                </div>
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                    <label for="name" style="width:110px;text-align:right;margin-right:10px;">Nama</label>
                                    <input type="text" id="name" class="swal2-input" style="width:70%;" value="${user.name}" required>
                                </div>
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                    <label for="whatsapp" style="width:110px;text-align:right;margin-right:10px;">WhatsApp</label>
                                    <input type="text" id="whatsapp" class="swal2-input" style="width:70%;" value="${user.whatsapp}" required>
                                </div>
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                    <label for="role" style="width:110px;text-align:right;margin-right:10px;">Role</label>
                                    <select id="role" class="swal2-input" style="width:64%;height:40px;">
                                        <option value="1" ${user.is_admin == 1 ? 'selected' : ''}>Admin</option>
                                        <option value="0" ${user.is_admin == 0 ? 'selected' : ''}>Pengguna</option>
                                    </select>
                                </div>
                                <div style="display:flex;align-items:center;">
                                    <label for="status" style="width:110px;text-align:right;margin-right:10px;">Status</label>
                                    <select id="status" class="swal2-input" style="width:64%;height:40px;">
                                        <option value="1" ${user.is_active == 1 ? 'selected' : ''}>Aktif</option>
                                        <option value="0" ${user.is_active == 0 ? 'selected' : ''}>Tidak Aktif</option>
                                    </select>
                                </div>
                            </form>`,
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: '{{ __("user_admin.save") }}',
                        cancelButtonText: '{{ __("user_admin.cancel") }}',
                        preConfirm: () => {
                            return {
                                unit: $('#unit').val(),
                                username: $('#username').val(),
                                password: $('#password').val(),
                                name: $('#name').val(),
                                whatsapp: $('#whatsapp').val(),
                                role: $('#role').val(),
                                status: $('#status').val()
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/user') }}/" + userId + "/edit",
                                method: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    unit: result.value.unit,
                                    username: result.value.username,
                                    password: result.value.password,
                                    name: result.value.name,
                                    whatsapp: result.value.whatsapp,
                                    role: result.value.role,
                                    is_active: result.value.status
                                },
                                success: function(res) {
                                    Swal.fire('Berhasil', 'Pengguna berhasil diupdate!', 'success').then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    let msg = 'Terjadi kesalahan saat mengedit pengguna.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        msg = xhr.responseJSON.message;
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        html: msg,
                                        showCancelButton: true,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Coba Lagi',
                                        cancelButtonText: 'Tutup',
                                        focusConfirm: false,
                                        didOpen: () => {
                                            $('#unit').val(result.value.unit);
                                            $('#username').val(result.value.username);
                                            $('#password').val(result.value.password);
                                            $('#name').val(result.value.name);
                                            $('#whatsapp').val(result.value.whatsapp);
                                            $('#role').val(result.value.role);
                                            $('#status').val(result.value.status);
                                        }
                                    }).then((retry) => {
                                        if (retry.isConfirmed) {
                                            $('.action-btn.detail[data-id="'+userId+'"]').trigger('click');
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire('Error', 'Data user tidak ditemukan.', 'error');
                }
            });
        });
    });
</script>
@endpush