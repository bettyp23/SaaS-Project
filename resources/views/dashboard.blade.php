<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Todo Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
        }
        /* Sidebar Styles */
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
        .sidebar-header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        .sidebar-nav {
            padding: 20px 0;
        }
        .nav-item {
            display: block;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
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
        .nav-item .icon {
            margin-right: 12px;
            font-size: 18px;
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
        .user-name {
            font-weight: 500;
            color: #333;
        }
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
        .btn-logout:hover {
            background: #d32f2f;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .welcome-card h2 {
            color: #333;
            font-size: 32px;
            margin-bottom: 16px;
        }
        .welcome-card p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .number {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
        }
        .recent-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        .recent-section h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .todo-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #eee;
        }
        .todo-item:last-child {
            border-bottom: none;
        }
        .todo-title {
            flex: 1;
            color: #333;
        }
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
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1>Todo Tracker</h1>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-item active">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="/tasks" class="nav-item">
                <span class="icon">✅</span>
                <span>All Tasks</span>
            </a>
            <a href="/kanban" class="nav-item">
                <span class="icon">📌</span>
                <span>Kanban Board</span>
            </a>
            <a href="/calendar" class="nav-item">
                <span class="icon">📅</span>
                <span>Calendar</span>
            </a>
            <a href="/categories" class="nav-item">
                <span class="icon">🏷️</span>
                <span>Categories</span>
            </a>
            <a href="/archive" class="nav-item">
                <span class="icon">📦</span>
                <span>Archive</span>
            </a>
            <a href="/settings" class="nav-item">
                <span class="icon">⚙️</span>
                <span>Settings</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Dashboard</h1>
            <div class="user-info">
                <span class="user-name">Welcome, {{ Auth::user()->name }}!</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <div class="container">
            <div class="welcome-card">
                <h2>🎉 Welcome to Your Dashboard!</h2>
                <p>Your Todo Tracker SaaS application is now working successfully.</p>
                <p style="margin-top: 10px; color: #999;">You're logged in as <strong>{{ Auth::user()->email }}</strong></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Tasks</h3>
                    <div class="number">{{ $totalTodos }}</div>
                </div>
                <div class="stat-card">
                    <h3>Completed</h3>
                    <div class="number">{{ $completedTodos }}</div>
                </div>
                <div class="stat-card">
                    <h3>In Progress</h3>
                    <div class="number">{{ $inProgressTodos }}</div>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <div class="number">{{ $pendingTodos }}</div>
                </div>
            </div>

            @if($recentTodos->count() > 0)
            <div class="recent-section">
                <h2>Recent Tasks</h2>
                @foreach($recentTodos as $todo)
                    <div class="todo-item">
                        <div class="todo-title">{{ $todo->title }}</div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
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
    </script>
</body>
</html> 