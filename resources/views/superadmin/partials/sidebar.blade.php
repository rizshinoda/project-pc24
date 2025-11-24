<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="nav-profile-image">
                    <img src="{{asset('/dist/assets/images/faces/2.png')}}" alt="profile" />
                    <span class="login-status online"></span>
                    <!--change to offline or busy as needed-->
                </div>
                <div class="nav-profile-text d-flex flex-column">
                    <span class="font-weight-bold mb-2">{{ Auth::user()->name }}</span>
                    <span class="text-secondary text-small">Observer</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('superadmin/dashboard') }}">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <span class="menu-title">Work Order</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-format-list-bulleted menu-icon"></i>
            </a>
            <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/survey')}}">Survey</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/instalasi')}}">Instalasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/upgrade')}}">Upgrade</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/downgrade')}}">Downgrade</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/gantivendor')}}">Ganti Vendor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/dismantle')}}">Dismantle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/relokasi')}}">Relokasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/maintenance')}}">Maintenance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('superadmin/requestbarang')}}">Request Barang</a>
                    </li>

                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{url('superadmin/OB')}}">
                <span class="menu-title">Online Billing</span>
                <i class="mdi mdi-database-outline menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('superadmin/sitedismantle') }}">
                <span class="menu-title">Site Dismantle</span>
                <i class="mdi mdi-delete-circle menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('superadmin/log') }}">
                <span class="menu-title">Log</span>
                <i class="mdi mdi-usb-c-port menu-icon"></i>
            </a>
        </li>

</nav>