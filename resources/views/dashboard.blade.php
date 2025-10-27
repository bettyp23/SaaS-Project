<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Todo Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .btn-refresh {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-refresh:hover {
            background: #5568d3;
        }
        .refresh-indicator {
            font-size: 11px;
            color: #999;
            margin-right: 8px;
        }
        .container {
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 20px;
        }
        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .stat-card.overdue::before {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        .stat-card.completed::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        .stat-label {
            color: #666;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        .stat-card.completed .stat-value {
            color: #10b981;
        }
        .stat-card.overdue .stat-value {
            color: #ef4444;
        }
        .stat-trend {
            font-size: 12px;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .stat-trend.negative {
            color: #ef4444;
        }
        .completion-rate {
            width: 120px;
            height: 120px;
            margin: 10px auto;
        }
        /* Widget Grid */
        .widget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        .widget {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: move;
        }
        .widget:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }
        .widget-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .widget-icon {
            font-size: 20px;
        }
        .widget-content {
            min-height: 200px;
        }
        /* Chart Styles */
        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 20px;
        }
        /* Upcoming Deadlines */
        .deadline-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .deadline-item:hover {
            background: #e9ecef;
            transform: translateX(4px);
        }
        .deadline-info {
            flex: 1;
        }
        .deadline-title {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }
        .deadline-date {
            font-size: 12px;
            color: #666;
        }
        .deadline-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-urgent {
            background: #fee2e2;
            color: #dc2626;
        }
        .badge-today {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-soon {
            background: #dbeafe;
            color: #2563eb;
        }
        /* Priority Distribution */
        .priority-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .priority-item:hover {
            background: #f8f9fa;
            transform: translateX(4px);
        }
        .priority-bar {
            flex: 1;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            margin: 0 16px;
            overflow: hidden;
        }
        .priority-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        .fill-low { background: #10b981; }
        .fill-medium { background: #f59e0b; }
        .fill-high { background: #ef4444; }
        .fill-urgent { background: #dc2626; }
        /* Recent Tasks */
        .recent-task {
            display: flex;
            align-items: center;
            padding: 14px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s;
        }
        .recent-task:hover {
            background: #f8f9fa;
        }
        .recent-task:last-child {
            border-bottom: none;
        }
        .task-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            cursor: pointer;
        }
        .task-title {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        .task-priority {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            margin-right: 8px;
        }
        /* High Priority Tasks */
        .high-priority-item {
            padding: 14px;
            background: linear-gradient(135deg, #fee2e2 0%, #fff 100%);
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .high-priority-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }
        .high-priority-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }
        .high-priority-date {
            font-size: 12px;
            color: #666;
        }
        /* Empty States */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .empty-state-text {
            font-size: 16px;
            margin-bottom: 8px;
        }
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        .quick-action {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .quick-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .quick-action-icon {
            font-size: 32px;
        }
        /* Category Tags */
        .category-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .category-tag {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .category-count {
            background: rgba(255, 255, 255, 0.3);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        /* Mini Kanban Preview */
        .mini-kanban {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .mini-column {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .mini-column-header {
            font-size: 11px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .mini-column-count {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }
        /* Last Updated */
        .last-updated {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
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
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 2000;
            animation: slideInRight 0.3s ease;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
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
            .widget-grid {
                grid-template-columns: 1fr;
            }
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body x-data="dashboardState()">
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
                <span class="refresh-indicator" x-text="lastUpdated"></span>
                <button class="btn-refresh" @click="refreshData()">🔄 Refresh</button>
                <span class="user-name">Welcome, {{ Auth::user()->name }}!</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <div class="container">
            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-card">
                    <div class="stat-label">Total Tasks</div>
                    <div class="stat-value" x-text="totalTodos">{{ $totalTodos }}</div>
                    <div class="stat-trend">📈 Active</div>
                </div>
                <div class="stat-card completed">
                    <div class="stat-label">Completion Rate</div>
                    <div class="stat-value" x-text="completionRate + '%'">{{ $completionRate }}%</div>
                    <div class="stat-trend">✓ {{ $completedTodos }} completed</div>
                </div>
                <div class="stat-card overdue" x-show="{{ $overdueTasks }} > 0">
                    <div class="stat-label">Overdue Tasks</div>
                    <div class="stat-value" x-text="overdueTasks">{{ $overdueTasks }}</div>
                    <div class="stat-trend negative">⚠️ Needs attention</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value" x-text="inProgressTodos">{{ $inProgressTodos }}</div>
                    <div class="stat-trend">🔄 Active work</div>
                </div>
            </div>

            <!-- Widget Grid -->
            <div class="widget-grid" id="widget-grid">
                <!-- Priority Distribution Chart -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">📊</span>
                            Priority Distribution
                        </div>
                    </div>
                    <div class="widget-content">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>

                <!-- Upcoming Deadlines -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">⏰</span>
                            Upcoming Deadlines
                        </div>
                    </div>
                    <div class="widget-content">
                        @if($upcomingDeadlines->count() > 0)
                            @foreach($upcomingDeadlines as $todo)
                                @php
                                    $daysUntil = \Carbon\Carbon::parse($todo->due_date)->diffInDays(now());
                                    $badgeClass = $daysUntil == 0 ? 'badge-today' : ($daysUntil <= 2 ? 'badge-urgent' : 'badge-soon');
                                @endphp
                                <div class="deadline-item">
                                    <div class="deadline-info">
                                        <div class="deadline-title">{{ $todo->title }}</div>
                                        <div class="deadline-date">{{ \Carbon\Carbon::parse($todo->due_date)->format('M d, Y') }}</div>
                                    </div>
                                    <span class="deadline-badge {{ $badgeClass }}">
                                        {{ $daysUntil == 0 ? 'Today' : ($daysUntil . ' days') }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">📅</div>
                                <div class="empty-state-text">No upcoming deadlines</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Productivity Trend -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">📈</span>
                            Productivity Trend
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="chart-container">
                            <canvas id="productivityChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">🏷️</span>
                            Categories
                        </div>
                    </div>
                    <div class="widget-content">
                        @if($userTags->count() > 0)
                            <div class="category-cloud">
                                @foreach($userTags as $tag)
                                    <span class="category-tag">
                                        {{ $tag->name }}
                                        <span class="category-count">{{ $tag->todos_count }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">🏷️</div>
                                <div class="empty-state-text">No categories yet</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Mini Kanban Preview -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">📌</span>
                            Kanban Board
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="mini-kanban">
                            <div class="mini-column">
                                <div class="mini-column-header">To Do</div>
                                <div class="mini-column-count">{{ $pendingTodos }}</div>
                            </div>
                            <div class="mini-column">
                                <div class="mini-column-header">In Progress</div>
                                <div class="mini-column-count">{{ $inProgressTodos }}</div>
                            </div>
                            <div class="mini-column">
                                <div class="mini-column-header">Done</div>
                                <div class="mini-column-count">{{ $completedTodos }}</div>
                            </div>
                        </div>
                        <a href="/kanban" style="display: block; text-align: center; margin-top: 16px; color: #667eea; text-decoration: none; font-weight: 500;">View Full Board →</a>
                    </div>
                </div>

                <!-- Priority Distribution Bars -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">⚡</span>
                            Priority Breakdown
                        </div>
                    </div>
                    <div class="widget-content">
                        @if($priorityDistribution['low'] > 0 || $priorityDistribution['medium'] > 0 || $priorityDistribution['high'] > 0)
                            <div class="priority-item">
                                <span>🟢 Low</span>
                                <div class="priority-bar"><div class="priority-fill fill-low" style="width: {{ $totalTodos > 0 ? ($priorityDistribution['low'] / $totalTodos * 100) : 0 }}%"></div></div>
                                <span style="font-weight: 600;">{{ $priorityDistribution['low'] }}</span>
                            </div>
                            <div class="priority-item">
                                <span>🟡 Medium</span>
                                <div class="priority-bar"><div class="priority-fill fill-medium" style="width: {{ $totalTodos > 0 ? ($priorityDistribution['medium'] / $totalTodos * 100) : 0 }}%"></div></div>
                                <span style="font-weight: 600;">{{ $priorityDistribution['medium'] }}</span>
                            </div>
                            <div class="priority-item">
                                <span>🟠 High</span>
                                <div class="priority-bar"><div class="priority-fill fill-high" style="width: {{ $totalTodos > 0 ? ($priorityDistribution['high'] / $totalTodos * 100) : 0 }}%"></div></div>
                                <span style="font-weight: 600;">{{ $priorityDistribution['high'] }}</span>
                            </div>
                            <div class="priority-item">
                                <span>🔴 Urgent</span>
                                <div class="priority-bar"><div class="priority-fill fill-urgent" style="width: {{ $totalTodos > 0 ? ($priorityDistribution['urgent'] / $totalTodos * 100) : 0 }}%"></div></div>
                                <span style="font-weight: 600;">{{ $priorityDistribution['urgent'] }}</span>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">⚡</div>
                                <div class="empty-state-text">No tasks with priority</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- High Priority Tasks -->
                @if($highPriorityTasks->count() > 0)
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">🔴</span>
                            High Priority Tasks
                        </div>
                    </div>
                    <div class="widget-content">
                        @foreach($highPriorityTasks as $todo)
                            <div class="high-priority-item">
                                <div class="high-priority-title">{{ $todo->title }}</div>
                                @if($todo->due_date)
                                    <div class="high-priority-date">Due: {{ \Carbon\Carbon::parse($todo->due_date)->format('M d, Y') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-icon">⚡</span>
                            Quick Actions
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="quick-actions">
                            <a href="/tasks/create" class="quick-action" style="text-decoration: none; color: white;">
                                <div class="quick-action-icon">➕</div>
                                <div>New Task</div>
                            </a>
                            <a href="/tasks?status=pending" class="quick-action" style="text-decoration: none; color: white; background: linear-gradient(135deg, #10b981, #059669);">
                                <div class="quick-action-icon">📋</div>
                                <div>View Pending</div>
                            </a>
                            <a href="/kanban" class="quick-action" style="text-decoration: none; color: white; background: linear-gradient(135deg, #f59e0b, #d97706);">
                                <div class="quick-action-icon">📌</div>
                                <div>Kanban View</div>
                            </a>
                            <a href="/calendar" class="quick-action" style="text-decoration: none; color: white; background: linear-gradient(135deg, #3b82f6, #2563eb);">
                                <div class="quick-action-icon">📅</div>
                                <div>Calendar</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tasks -->
            @if($recentTodos->count() > 0)
            <div class="recent-section widget">
                <div class="widget-header">
                    <div class="widget-title">
                        <span class="widget-icon">🕐</span>
                        Recent Tasks
                    </div>
                </div>
                <div class="widget-content">
                    @foreach($recentTodos as $todo)
                        <div class="recent-task">
                            <input type="checkbox" class="task-checkbox" @change="toggleComplete({{ $todo->id }})" {{ $todo->status === 'completed' ? 'checked' : '' }}>
                            <div class="task-title">{{ $todo->title }}</div>
                            @php
                                $priorityColors = ['low' => '#10b981', 'medium' => '#f59e0b', 'high' => '#ef4444'];
                                $color = $priorityColors[$todo->priority] ?? '#6b7280';
                            @endphp
                            <span class="task-priority" style="background: {{ $color }}; color: white;">{{ ucfirst($todo->priority) }}</span>
                        </div>
                    @endforeach
                    <div style="text-align: center; margin-top: 16px;">
                        <a href="/tasks" style="color: #667eea; text-decoration: none; font-weight: 500;">View All Tasks →</a>
                    </div>
                </div>
            </div>
            @endif

            <div class="last-updated">
                Last updated: <span x-text="lastUpdated"></span>
            </div>
        </div>
    </div>

    <script>
        function dashboardState() {
            return {
                totalTodos: {{ $totalTodos }},
                completedTodos: {{ $completedTodos }},
                inProgressTodos: {{ $inProgressTodos }},
                completionRate: {{ $completionRate }},
                overdueTasks: {{ $overdueTasks }},
                lastUpdated: new Date().toLocaleTimeString(),
                
                refreshData() {
                    fetch('/api/dashboard/stats', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.totalTodos = data.totalTodos;
                        this.completedTodos = data.completedTodos;
                        this.inProgressTodos = data.inProgressTodos;
                        this.completionRate = data.completionRate;
                        this.overdueTasks = data.overdueTasks;
                        this.lastUpdated = new Date().toLocaleTimeString();
                        
                        // Update charts
                        updatePriorityChart(data.priorityDistribution);
                        updateProductivityChart(data.productivityTrend);
                        
                        showNotification('Data refreshed successfully');
                    })
                    .catch(error => {
                        console.error('Error refreshing data:', error);
                        showNotification('Error refreshing data', 'error');
                    });
                },
                
                toggleComplete(todoId) {
                    // Toggle complete status
                    fetch(`/tasks/${todoId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.refreshData();
                        }
                    });
                }
            }
        }
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'error' ? '#ef4444' : '#10b981';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Initialize Priority Chart
        const priorityCtx = document.getElementById('priorityChart');
        let priorityChart = null;
        
        function updatePriorityChart(distribution) {
            const data = {
                labels: ['Low', 'Medium', 'High', 'Urgent'],
                datasets: [{
                    data: [
                        distribution.low || 0,
                        distribution.medium || 0,
                        distribution.high || 0,
                        distribution.urgent || 0
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#dc2626'
                    ]
                }]
            };
            
            if (priorityChart) {
                priorityChart.data = data;
                priorityChart.update();
            } else {
                priorityChart = new Chart(priorityCtx, {
                    type: 'doughnut',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }
        
        // Initialize Productivity Chart
        const productivityCtx = document.getElementById('productivityChart');
        let productivityChart = null;
        
        function updateProductivityChart(trend) {
            const data = {
                labels: trend.map(t => t.day),
                datasets: [{
                    label: 'Tasks Completed',
                    data: trend.map(t => t.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            };
            
            if (productivityChart) {
                productivityChart.data = data;
                productivityChart.update();
            } else {
                productivityChart = new Chart(productivityCtx, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePriorityChart(@json($priorityDistribution));
            updateProductivityChart(@json($productivityTrend));
        });
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            const dashboard = Alpine.store('x-data');
            if (dashboard && dashboard.refreshData) {
                dashboard.refreshData();
            }
        }, 30000);
        
        // Click outside to close sidebar on mobile
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