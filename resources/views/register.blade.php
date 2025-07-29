<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Pengguna - Apartemen Bona Vista</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            min-height: 100vh;
            margin: 0;
        }
        .register-box {
            max-width: 370px;
            margin: 7vh auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px #0002;
            padding: 36px 28px 28px 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .register-box h2 {
            margin-bottom: 10px;
            font-weight: 600;
            color: #2d3748;
            text-align: center;
        }
        .form-group {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 1em;
            color: #374151;
        }
        .form-group input {
            width: 100%;
            max-width: 260px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1em;
            background: #f9fafb;
            transition: border 0.2s;
            box-sizing: border-box;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .form-group input:focus {
            border-color: #6366f1;
            outline: none;
            background: #fff;
        }
        .btn {
            background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            padding: 12px 0;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
            width: 100%;
            max-width: 260px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .btn:hover {
            background: linear-gradient(90deg, #4f46e5 0%, #2563eb 100%);
        }
        .text-center { text-align: center; }
        .mt-2 { margin-top: 12px; }
        @media (max-width: 480px) {
            .register-box {
                max-width: 98vw;
                padding: 22px 4vw 18px 4vw;
            }
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Registrasi Pengguna</h2>
        <form method="POST" action="{{ route('register.submit') }}" id="registerForm" autocomplete="off">
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" required autocomplete="name" placeholder="Nama Lengkap" value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label for="whatsapp">No. WhatsApp</label>
                <input type="text" name="whatsapp" id="whatsapp" required autocomplete="tel" placeholder="08xxxxxxxxxx" value="{{ old('whatsapp') }}">
            </div>
            <div class="form-group">
                <label for="unit">No. Unit</label>
                <input type="text" name="unit" id="unit" required autocomplete="off" placeholder="Contoh: A-12" value="{{ old('unit') }}">
            </div>
            <div class="form-group">
                <label for="username">ID Pengguna</label>
                <input type="text" name="username" id="username" required autocomplete="username" placeholder="0812-1234-5678" value="{{ old('username') }}">
            </div>
            <div class="form-group">
                <label for="password">Password (min. 6 karakter)</label>
                <input type="password" name="password" id="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
            </div>
            <button class="btn" type="submit">Daftar</button>
        </form>
        <div class="text-center mt-2">
            Sudah punya akun? <a href="{{ route('login') }}" style="color:#6366f1;text-decoration:none;">Login</a>
        </div>
    </div>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Tampilkan error dari backend (jika ada)
    <?php if($errors->any()): ?>
        Swal.fire({
            icon: 'error',
            title: 'Registrasi Gagal',
            html: `<?php echo implode('<br>', $errors->all()); ?>`,
            confirmButtonColor: '#6366f1'
        });
    <?php endif; ?>

    // Optional: Validasi frontend sebelum submit
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const password2 = document.getElementById('password_confirmation').value.trim();
        if (!username || !password || !password2) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Lengkapi Data',
                text: 'Semua field wajib diisi.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }
        if (password.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Password Terlalu Pendek',
                text: 'Password minimal 6 karakter.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }
        if (password !== password2) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi Password Salah',
                text: 'Password dan konfirmasi password harus sama.',
                confirmButtonColor: '#6366f1'
            });
        }
    });
    </script>
</body>
</html>