<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-image">
          <img src="{{asset('/dist/assets/images/faces/2.png')}}" alt="profile" />
          <span class="login-status online"></span>
          <!--change to offline or busy as needed-->
        </div>
        <div class="nav-profile-text d-flex flex-column ">
          <span class="font-weight-bold mb-2">{{ Auth::user()->name }}</span>
          <span class="text-secondary text-small">{{ $roleText }}</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ url('ga/dashboard') }}">
        <span class="menu-title">Dashboard</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic1" aria-expanded="false" aria-controls="ui-basic1">
        <span class="menu-title">Tambah Data</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-format-list-bulleted menu-icon"></i>
      </a>
      <div class="collapse" id="ui-basic1">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/jenis')}}">Data Jenis</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/merek')}}">Data Merek</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/tipe')}}">Data Tipe</a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ url('ga/stockbarang') }}">
        <span class="menu-title">Stock Barang</span>
        <i class="mdi mdi-database-edit-outline menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic2" aria-expanded="false" aria-controls="ui-basic2">
        <span class="menu-title">Work Order</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-format-list-bulleted menu-icon"></i>
      </a>
      <div class="collapse" id="ui-basic2">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/instalasi')}}">Instalasi</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/maintenance')}}">Maintenance</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/requestbarang')}}">Request Barang</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/dismantle')}}">Dismantle</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{url('ga/relokasi')}}">Relokasi</a>
          </li>
        </ul>
      </div>
    </li>


    <li class="nav-item">
      <a class="nav-link" href="{{url('ga/OB')}}">
        <span class="menu-title">Online Billing</span>
        <i class="mdi mdi-database-outline menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('ga.sitedismantle') }}">
        <span class="menu-title">Site Dismantle</span>
        <i class="mdi mdi-delete-circle menu-icon"></i>
      </a>
    </li>

</nav>