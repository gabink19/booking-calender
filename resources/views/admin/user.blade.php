@extends('admin.layouts.app')

@section('content')
<div class="card user-management">
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Manajemen User
        <button id="btn-tambah-user" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-icons" style="vertical-align:middle;">person_add</span>
            <span class="d-none d-md-inline">Tambah Pengguna</span>
        </button>
    </h2>
    <div class="table-responsive">
        <table id="userTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Unit</th>
                    <th>ID Pengguna</th>
                    <th>Nama</th>
                    <th>WhatsApp</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
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
                                    <span>Admin</span>
                                @else
                                    <span>Pengguna</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="label label-green">Aktif</span>
                                @else
                                    <span class="label label-red">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                @if(!$user->is_admin)
                                <div class="actions">
                                    <button class="action-btn detail" title="Edit User">
                                        <span class="material-icons" style="vertical-align:middle;">edit</span>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data user.</td>
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
                title: 'Tambah Pengguna',
                html:
                    `<form id="form-tambah-user" autocomplete="off" style="font-size: 0.80em !important;">
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="unit" style="width:110px;text-align:right;margin-right:10px;">No. Unit</label>
                            <input type="text" id="unit" class="swal2-input" style="width:70%;" placeholder="No. Unit" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="username" style="width:110px;text-align:right;margin-right:10px;">ID Pengguna</label>
                            <input type="text" id="username" class="swal2-input" style="width:70%;" placeholder="ID Pengguna" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="password" style="width:110px;text-align:right;margin-right:10px;">Password</label>
                            <input type="password" id="password" class="swal2-input" style="width:70%;" placeholder="Password" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="name" style="width:110px;text-align:right;margin-right:10px;">Nama</label>
                            <input type="text" id="name" class="swal2-input" style="width:70%;" placeholder="Nama" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="whatsapp" style="width:110px;text-align:right;margin-right:10px;">WhatsApp</label>
                            <input type="text" id="whatsapp" class="swal2-input" style="width:70%;" placeholder="WhatsApp" required>
                        </div>
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <label for="role" style="width:110px;text-align:right;margin-right:10px;">Role</label>
                            <select id="role" class="swal2-input" style="width:64%;height:40px;padding:0.75em;font-size:1em;margin-top: 10px;margin-left: 4%;">
                                <option value="0">Pengguna</option>
                            </select>
                        </div>
                        <div style="display:flex;align-items:center;">
                            <label for="status" style="width:110px;text-align:right;margin-right:10px;">Status</label>
                            <select id="status" class="swal2-input" style="width:64%;height:40px;padding:0.75em;font-size:1em;margin-top: 10px;margin-left: 4%;">
                                <option value="">Pilih Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </form>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
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
                            Swal.fire('Berhasil', 'Pengguna berhasil ditambahkan!', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            // Ambil pesan error dari response jika ada
                            let msg = 'Terjadi kesalahan saat menambah pengguna.';
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
                        title: 'Edit Pengguna',
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
                                    <select id="role" class="swal2-input" style="width:64%;height:40px;padding:0.75em;font-size:1em;margin-top: 10px;margin-left: 4%;">
                                        <option value="1" ${user.is_admin == 1 ? 'selected' : ''}>Admin</option>
                                        <option value="0" ${user.is_admin == 0 ? 'selected' : ''}>Pengguna</option>
                                    </select>
                                </div>
                                <div style="display:flex;align-items:center;">
                                    <label for="status" style="width:110px;text-align:right;margin-right:10px;">Status</label>
                                    <select id="status" class="swal2-input" style="width:64%;height:40px;padding:0.75em;font-size:1em;margin-top: 10px;margin-left: 4%;">
                                        <option value="1" ${user.is_active == 1 ? 'selected' : ''}>Aktif</option>
                                        <option value="0" ${user.is_active == 0 ? 'selected' : ''}>Tidak Aktif</option>
                                    </select>
                                </div>
                            </form>`,
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        cancelButtonText: 'Batal',
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