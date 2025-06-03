<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <a class="navbar-brand brand-logo" href="{{ url('superadmin/dashboard') }}"><img src="{{asset('mailler/img/pc24.png')}}" alt="logo" style="width: 200px; height: auto;" /></a>
        <a class="navbar-brand brand-logo-mini" href="{{ url('superadmin/dashboard') }}"><img src="{{asset('mailler/img/pc24.png')}}" alt="logo" /></a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>



        <ul class="navbar-nav navbar-nav-right">
            <!-- Dropdown Surat -->
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="mdi mdi-email-outline"></i>
                </a>

                <div class="dropdown-menu navbar-dropdown" aria-labelledby="messageDropdown">
                    <a class="dropdown-item" href="{{ url('superadmin/chat') }}">
                        <i class="mdi mdi-message-text me-2 text-success"></i> Chat
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ url('superadmin/users') }}">
                        <i class="mdi mdi-account me-2 text-primary"></i> User
                    </a>
                </div>
            </li>


            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="nav-profile-img">
                        <img src="{{asset('/dist/assets/images/faces/2.png')}}" alt="image">
                    </div>
                    <div class="nav-profile-text text-truncate" style="max-width: 90px;">
                        <p class="mb-1 text-black mb-0 text-truncate">{{ Auth::user()->name }}</p>
                    </div>
                </a>


                <form action="">
                    @csrf
                    <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                        <!-- <a class="dropdown-item" href="#">
                                    <i class="mdi mdi-cached me-2 text-success"></i> Activity Log </a> -->
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item btn-logout" href="{{url('logout')}}" id="logout">
                            <i class="mdi mdi-logout me-2 text-primary"></i> Logout </a>
                    </div>
                </form>
            </li>

        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>