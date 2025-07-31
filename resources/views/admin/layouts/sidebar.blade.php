<nav class="sidebar" id="sidebar">
    <div class="logo" style="display: flex; align-items: center; gap: 10px;">
        <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Logo" style="width:48px; height:48px; object-fit:contain;"/>
        <span style="font-size:1.1rem;">Apartemen Bona Vista</span>
        <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle Sidebar" style="margin-left:auto;display:none;">
            <span class="material-icons">menu</span>
        </button>
    </div>
    <ul>
        <a href="{{ route('admin.dashboard') }}">
            <li class="{{ request()->is('admin') ? 'active' : '' }}">Dashboard</li>
        </a>
        <a href="{{ route('admin.booking.index') }}">
            <li class="{{ request()->is('admin/booking*') ? 'active' : '' }}">Data Booking</li>
        </a>
        <a href="{{ route('admin.user.index') }}">
            <li class="{{ request()->is('admin/user*') ? 'active' : '' }}">Manajemen User</li>
        </a>
        <a href="{{ route('admin.settings') }}">
            <li class="{{ request()->is('admin/settings*') ? 'active' : '' }}">Pengaturan</li>
        </a>
        <a href="{{ route('admin.logout') }}">
            <li>Logout</li>
        </a>
    </ul>
</nav>

@push('head')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
    .sidebar {
        transition: left 0.3s, box-shadow 0.3s;
    }
    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            left: -220px;
            top: 0;
            width: 220px;
            height: 100vh;
            background: #fff;
            z-index: 1001;
            box-shadow: 2px 0 12px rgba(0,0,0,0.08);
        }
        .sidebar.open {
            left: 0;
        }
        .sidebar-toggle {
            display: inline-flex !important;
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #3041b7;
        }
        body.sidebar-open {
            overflow: hidden;
        }
        /* Overlay for sidebar */
        #sidebar-overlay {
            display: block;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.18);
            z-index: 1000;
        }
    }
    @media (min-width: 769px) {
        #sidebar-overlay, .sidebar-toggle {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.getElementById('sidebar');
        var toggle = document.getElementById('sidebar-toggle');
        var overlay;

        function openSidebar() {
            sidebar.classList.add('open');
            document.body.classList.add('sidebar-open');
            overlay = document.createElement('div');
            overlay.id = 'sidebar-overlay';
            overlay.onclick = closeSidebar;
            document.body.appendChild(overlay);
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
            if (overlay) overlay.remove();
        }
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }
        // Optional: close sidebar when resizing to desktop
        window.addEventListener('resize', function() {
            if(window.innerWidth > 768) closeSidebar();
        });
    });
</script>
@endpush