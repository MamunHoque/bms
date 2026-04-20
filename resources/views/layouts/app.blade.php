<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #0b0d10;
            --bg: #f6f5f1;
            --card: #ffffff;
            --accent: #d4471f;
            --muted: #6b6b6b;
            --line: #e7e5df;
        }
        html, body { background: var(--bg); color: var(--ink); font-family: 'Inter', system-ui, sans-serif; }
        .font-serif { font-family: 'Instrument Serif', Georgia, serif; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; }
        .btn { padding: 0.55rem 1rem; border-radius: 10px; font-weight: 500; font-size: 0.875rem; transition: all .15s; border: 1px solid transparent; display: inline-flex; align-items: center; gap: .4rem; }
        .btn-primary { background: var(--ink); color: white; }
        .btn-primary:hover { background: #1a1d22; }
        .btn-accent { background: var(--accent); color: white; }
        .btn-accent:hover { filter: brightness(1.05); }
        .btn-ghost { background: transparent; border-color: var(--line); color: var(--ink); }
        .btn-ghost:hover { background: #f1efeb; }
        .btn-danger { background: transparent; color: #b91c1c; border-color: #fecaca; }
        .btn-danger:hover { background: #fef2f2; }
        .input, .select, .textarea {
            width: 100%; padding: 0.55rem 0.8rem; border: 1px solid var(--line); border-radius: 10px;
            background: white; font-size: 0.9rem; transition: border-color .15s;
        }
        .input:focus, .select:focus, .textarea:focus { outline: none; border-color: var(--ink); }
        .label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--muted); margin-bottom: 0.35rem; }
        .nav-link {
            display: flex; align-items: center; gap: 0.7rem; padding: 0.6rem 0.9rem; border-radius: 9px;
            color: #3a3a3a; font-size: 0.9rem; font-weight: 500; transition: all .15s;
        }
        .nav-link:hover { background: #f1efeb; color: var(--ink); }
        .nav-link.active { background: var(--ink); color: white; }
        .nav-link.active:hover { background: var(--ink); }
        .badge { display: inline-flex; padding: 0.15rem 0.6rem; border-radius: 99px; font-size: 0.72rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-red   { background: #fee2e2; color: #991b1b; }
        .badge-blue  { background: #dbeafe; color: #1e40af; }
        .badge-gray  { background: #f3f4f6; color: #374151; }
        table.bms { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        table.bms th { text-align: left; padding: 0.75rem 1rem; font-weight: 500; color: var(--muted); border-bottom: 1px solid var(--line); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; }
        table.bms td { padding: 0.85rem 1rem; border-bottom: 1px solid #f2f0ec; }
        table.bms tr:hover td { background: #faf9f6; }
        .kpi { font-family: 'Instrument Serif', serif; font-size: 2.2rem; line-height: 1; letter-spacing: -0.02em; }
        .accent-dot { width: 6px; height: 6px; background: var(--accent); border-radius: 99px; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen">

@auth
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="w-60 shrink-0 border-r border-[var(--line)] bg-white sticky top-0 h-screen hidden md:flex flex-col">
        <div class="px-5 py-5 border-b border-[var(--line)]">
            <div class="flex items-center gap-2">
                <div class="accent-dot"></div>
                <span class="font-serif text-xl leading-none">BMS</span>
            </div>
            <p class="text-xs text-[var(--muted)] mt-1 leading-tight">Building Management</p>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('buildings.index') }}" class="nav-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}">Buildings</a>
            <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">Units</a>
            <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">Tenants</a>
            <a href="{{ route('leases.index') }}" class="nav-link {{ request()->routeIs('leases.*') ? 'active' : '' }}">Leases</a>
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">Invoices</a>
            <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">Payments</a>
            <a href="{{ route('utilities.index') }}" class="nav-link {{ request()->routeIs('utilities.*') ? 'active' : '' }}">Utilities</a>
            <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">Maintenance</a>
            <div class="pt-3 mt-3 border-t border-[var(--line)]">
                <p class="px-3 text-[0.65rem] font-semibold tracking-widest text-[var(--muted)] uppercase mb-1">Reports</p>
                <a href="{{ route('reports.collection') }}" class="nav-link {{ request()->routeIs('reports.collection') ? 'active' : '' }}">Collection</a>
                <a href="{{ route('reports.dues') }}" class="nav-link {{ request()->routeIs('reports.dues') ? 'active' : '' }}">Dues / Aging</a>
                <a href="{{ route('reports.occupancy') }}" class="nav-link {{ request()->routeIs('reports.occupancy') ? 'active' : '' }}">Occupancy</a>
                <a href="{{ route('reports.utilities') }}" class="nav-link {{ request()->routeIs('reports.utilities') ? 'active' : '' }}">Utilities</a>
            </div>
        </nav>
        <div class="px-4 py-3 border-t border-[var(--line)]">
            <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
            <div class="text-xs text-[var(--muted)] mb-2">{{ auth()->user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-ghost w-full justify-center text-xs">Sign out</button>
            </form>
        </div>
    </aside>

    {{-- Mobile top bar --}}
    <header class="md:hidden fixed top-0 left-0 right-0 bg-white border-b border-[var(--line)] z-30 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="accent-dot"></div>
            <span class="font-serif text-lg">BMS</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs text-[var(--muted)]">Sign out</button></form>
    </header>

    <main class="flex-1 min-w-0 pt-14 md:pt-0">
        <div class="max-w-7xl mx-auto p-5 md:p-8">
            @if(session('status'))
                <div class="mb-5 card px-4 py-3 flex items-center gap-3 border-l-4 border-l-[var(--accent)]">
                    <div class="accent-dot"></div>
                    <span class="text-sm">{{ session('status') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 card px-4 py-3 border-l-4 border-l-red-500">
                    <p class="text-sm font-medium text-red-700 mb-1">Please review:</p>
                    <ul class="text-sm text-red-600 list-disc pl-5">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
@else
    @yield('content')
@endauth

@stack('scripts')
</body>
</html>
