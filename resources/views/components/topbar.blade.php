{{-- Sneat --}}
<!-- Navbar -->

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0   d-xl-none ">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
        </a>
    </div>


    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

        <div class="navbar-nav align-items-center me-auto">
            <div class="nav-item d-flex align-items-center">
                <h3 class="fw-bold text-primary fst-italic m-0"
                    style="font-family: Poppins, serif; text-transform: uppercase;">@yield('title', 'INVENGO')</h3>
            </div>
        </div>
        <ul class="navbar-nav flex-row align-items-center ms-md-auto">


            <!-- Place this tag where you want the button to render. -->
            {{-- <li class="nav-item lh-1 me-4">
          <a class="github-button" href="https://github.com/themeselection/sneat-bootstrap-html-admin-template-free" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Star</a>
        </li> --}}

            <!-- Notification -->
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1 ">
                <a class="nav-link dropdown-toggle hide-arrow position-relative" style="width: 30px" href="{{ route('notifications.overdue') }}">
                    <i class="bx bx-bell bx-sm"></i>
                    @php
                        $overdueCount = \App\Models\Borrow::where('status', 'Dipinjam')
                            ->where('max_return_date', '<', now()->format('Y-m-d'))
                            ->count();
                    @endphp
                    @if($overdueCount > 0)
                        <span class="badge bg-danger rounded-pill badge-notifications position-absolute top-0 end-0">{{ $overdueCount }}</span>
                    @endif
                </a>
            </li>
            <!--/ Notification -->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ ucfirst(Auth::user()->name) }}</h6>
                                    <small class="text-body-secondary">{{ ucfirst(Auth::user()->role->name) }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#logoutModal">
                            <i class="icon-base bx bx-power-off icon-md me-3"></i>
                            <span>Keluar</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->

        </ul>
    </div>

</nav>
