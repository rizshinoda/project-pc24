<nav
    class="navbar navbar-expand-lg fixed-top navbar-light px-4 px-lg-5 py-3 py-lg-0">
    <a href="/" class="navbar-brand p-0">

        <img src="{{asset('mailler/img/pc24.png')}}" alt="Logo">

    </a>
    <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">

        <div class="navbar-nav ms-auto py-0">
            @if (Auth::check())
            @if (Auth::user()->is_role==0)
            <a
                href="{{ url('superadmin/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>
            @elseif (Auth::user()->is_role==1)
            <a
                href="{{ url('admin/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>
            @elseif (Auth::user()->is_role==2)
            <a
                href="{{ url('ga/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>
            @elseif (Auth::user()->is_role==3)
            <a
                href="{{ url('helpdesk/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>
            @elseif (Auth::user()->is_role==4)
            <a
                href="{{ url('noc/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>
            @elseif (Auth::user()->is_role==5)
            <a
                href="{{ url('psb/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>
            @elseif (Auth::user()->is_role==6)
            <a
                href="{{ url('na/dashboard') }}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Dashboard</a>

            @endif
            @else
            <a
                href="{{url('login')}}"
                class="btn btn-primary rounded-pill text-white py-2 px-4">Sign In</a>


            @endif
        </div>

    </div>
</nav>