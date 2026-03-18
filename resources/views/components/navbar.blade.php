<nav class="navbar navbar-light bg-light px-3">

    <span class="navbar-brand">Dashboard</span>

    <div>
        <span class="me-3">{{ auth()->user()->name }}</span>

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button class="btn btn-danger btn-sm">Logout</button>
        </form>
    </div>

</nav>