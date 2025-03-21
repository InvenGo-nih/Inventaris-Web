{{-- Sneat --}}
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  
  <div class="app-brand ">
    <img class="img-fluid app-brand-link" src="{{asset('images/logo-invengo.png')}}" alt="" width="150">

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
    </a>
  </div>

  
  <div class="menu-divider mt-0  "></div>

  <div class="menu-inner-shadow"></div>

  

  <ul class="menu-inner py-1">
    
    <!-- Dashboards -->
    @hasPermission('VIEW_DASHBOARD')
    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-smile"></i>
        <div class="text-truncate" data-i18n="Basic">Dashboard</div>
      </a>
    </li>
    @endhasPermission

    @hasPermission('VIEW_INVENTARIS')
    <li class="menu-item {{ request()->routeIs('inventaris.index') ? 'active' : '' }}">
      <a href="{{ route('inventaris.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-package"></i>
        <div class="text-truncate" data-i18n="Basic">Inventaris</div>
      </a>
    </li>
    @endhasPermission

    @hasPermission('VIEW_LOCATION_INVENTARIS')
    <li class="menu-item {{ request()->routeIs('inventaris.location.index') ? 'active' : '' }}">
      <a href="{{ route('inventaris.location.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-current-location"></i>
        <div class="text-truncate" data-i18n="Basic">Lokasi Inventaris</div>
      </a>
    </li>
    @endhasPermission

    @hasPermission('VIEW_BORROW')
    <li class="menu-item {{ request()->routeIs('borrow.index') ? 'active' : '' }}">
      <a href="{{ route('borrow.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-bookmarks"></i>
        <div class="text-truncate" data-i18n="Basic">Peminjaman</div>
      </a>
    </li>
    @endhasPermission

    @hasPermission('VIEW_SCAN')
    <li class="menu-item {{ request()->routeIs('qr.scan') ? 'active' : '' }}">
      <a href="{{ route('qr.scan') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-qr-scan"></i>
        <div class="text-truncate" data-i18n="Basic">Scan</div>
      </a>
    </li>
    @endhasPermission

    @hasPermission(['VIEW_USERS', 'VIEW_ROLES'])
    <li class="menu-item {{ request()->routeIs('users.index') || request()->routeIs('roles.index') ? 'active' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div class="text-truncate" data-i18n="Pengaturan">Pengaturan</div>
      </a>

      <ul class="menu-sub">
        @hasPermission('VIEW_USERS')
        <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
          <a href="{{ route('users.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div class="text-truncate" data-i18n="Pengguna">Pengguna</div>
          </a>
        </li>
        @endhasPermission
        @hasPermission('VIEW_ROLES')
        <li class="menu-item {{ request()->routeIs('roles.index') ? 'active' : '' }}">
          <a href="{{ route('roles.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user-plus"></i>
            <div class="text-truncate" data-i18n="Jabatan">Jabatan</div>
          </a>
        </li>
        @endhasPermission
      </ul>
    </li>
    @endhasPermission
  </ul>
</aside>