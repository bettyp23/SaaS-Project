<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Todo Tracker')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-header h1 { font-size: 24px; font-weight: 600; }
        .sidebar-nav { padding: 20px 0; }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }
        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: white;
            font-weight: 600;
        }
        .nav-item .icon { margin-right: 12px; font-size: 18px; }
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: #667eea;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
        }
        .header {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-name { font-weight: 500; color: #333; }
        .btn-logout {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-logout:hover { background: #d32f2f; }
        .content-wrapper { padding: 40px; }
        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .content-wrapper { padding: 20px; }
            .header { padding: 20px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1>Todo Tracker</h1>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="/tasks" class="nav-item {{ request()->is('tasks*') ? 'active' : '' }}">
                <span class="icon">✅</span>
                <span>All Tasks</span>
            </a>
            <a href="/kanban" class="nav-item {{ request()->is('kanban') ? 'active' : '' }}">
                <span class="icon">📌</span>
                <span>Kanban Board</span>
            </a>
            <a href="/calendar" class="nav-item {{ request()->is('calendar') ? 'active' : '' }}">
                <span class="icon">📅</span>
                <span>Calendar</span>
            </a>
            <a href="/categories" class="nav-item {{ request()->is('categories*') ? 'active' : '' }}">
                <span class="icon">🏷️</span>
                <span>Categories</span>
            </a>
            <a href="/archive" class="nav-item {{ request()->is('archive') ? 'active' : '' }}">
                <span class="icon">📦</span>
                <span>Archive</span>
            </a>
            <a href="/settings" class="nav-item {{ request()->is('settings') ? 'active' : '' }}">
                <span class="icon">⚙️</span>
                <span>Settings</span>
            </a>
        </nav>
    </aside>
    <div class="main-content">
        @if(auth()->check())
        <header class="header">
            <h2>@yield('page-title', 'Dashboard')</h2>
            <div class="user-info">
                <span class="user-name">Welcome, {{ auth()->user()->name ?? 'User' }}!</span>
                <form method="POST" action="/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>
        </header>
        @endif
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('open');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
