<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center mt-5 mb-3" href="">
        
           <img class="" src="{{asset('images/logo-invengo.png')}}" alt="" width="150">
        
        
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    @hasPermission('VIEW_DASHBOARD')
    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-solid fa-cubes"></i>
            <span>Dashboard</span>
        </a>
    </li>
    @endhasPermission

    <!-- Nav Item - Inventaris -->
    @hasPermission('VIEW_INVENTARIS')
    <li class="nav-item {{ request()->routeIs('inventaris.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('inventaris.index') }}">
           <i class="fa-solid fa-house"></i>
            <span>Inventaris</span>
        </a>
    </li>
    @endhasPermission


    <!-- Nav Item - Peminjaman -->
    @hasPermission('VIEW_BORROW')
    <li class="nav-item {{ request()->routeIs('borrow.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('borrow.index') }}">
            <i class="fas fa-fw fa-solid fa-hand-holding"></i>
            <span>Peminjaman</span></a>
    </li>
    @endhasPermission

    <!-- Nav Item - Scan -->
    @hasPermission('VIEW_SCAN')
    <li class="nav-item {{ request()->routeIs('qr.scan') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('qr.scan') }}">
            <i class="fas fa-fw fa-solid fa-qrcode"></i>
            <span>Scan</span></a>
    </li>
    @endhasPermission

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Interface
    </div>

    <!-- Nav Item - Pengaturan -->
    @hasPermission(['VIEW_USERS', 'VIEW_ROLES'])
    <li class="nav-item {{ request()->routeIs('users.index') || request()->routeIs('roles.index') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Pengaturan</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                {{-- <h6 class="collapse-header">Custom Components:</h6> --}}
                @hasPermission('VIEW_USERS')
                <a class="collapse-item {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">Pengguna</a>
                @endhasPermission
                @hasPermission('VIEW_ROLES')
                <a class="collapse-item {{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">Jabatan</a>
                @endhasPermission
            </div>
        </div>
    </li>
    @endhasPermission

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>