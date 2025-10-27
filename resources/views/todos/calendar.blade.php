@extends('layouts.app')

@section('title', 'Calendar - Todo Tracker')
@section('page-title', 'Calendar')

@section('content')
<style>
    .calendar-container {
        max-width: 1400px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .calendar-nav {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .nav-btn {
        background: #667eea;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .nav-btn:hover {
        background: #5568d3;
        transform: translateY(-2px);
    }
    
    .month-display {
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }
    
    .today-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background: #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .day-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
    }
    
    .day-cell {
        background: white;
        min-height: 120px;
        padding: 8px;
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .day-cell:hover {
        background: #f8f9fa;
        transform: scale(1.02);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .day-cell.other-month {
        background: #f9fafb;
        opacity: 0.5;
    }
    
    .day-cell.today {
        background: linear-gradient(135deg, #fef3c7, #fff);
        border: 2px solid #f59e0b;
    }
    
    .day-cell.weekend {
        background: #fef9e7;
    }
    
    .day-number {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    
    .day-number.today {
        background: #f59e0b;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .task-dots {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 4px;
    }
    
    .task-dot {
        width: 100%;
        height: 4px;
        border-radius: 2px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .task-dot:hover {
        height: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .task-count {
        position: absolute;
        top: 6px;
        right: 6px;
        background: #667eea;
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }
    
    .task-detail-tooltip {
        position: absolute;
        background: white;
        padding: 12px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        min-width: 200px;
        display: none;
        font-size: 13px;
    }
    
    .task-detail-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    
    .task-detail-info {
        font-size: 11px;
        color: #666;
    }
    
    .calendar-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }
    
    .calendar-modal.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        transition: all 0.2s;
    }
    
    .close-modal:hover {
        background: #f0f0f0;
        color: #333;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        color: #333;
        margin-bottom: 6px;
        font-size: 14px;
    }
    
    .form-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        cursor: pointer;
    }
    
    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 24px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        flex: 1;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-secondary:hover {
        background: #e0e0e0;
    }
    
    .task-list {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .task-item {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .task-item:hover {
        background: #e9ecef;
        transform: translateX(4px);
    }
    
    .task-item.completed {
        opacity: 0.6;
        text-decoration: line-through;
    }
    
    .priority-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    
    .priority-low { background: #10b981; }
    .priority-medium { background: #f59e0b; }
    .priority-high { background: #ef4444; }
    .priority-urgent { background: #dc2626; }
</style>

<div class="calendar-container" x-data="calendarState()">
    <!-- Calendar Header -->
    <div class="calendar-header">
        <div class="calendar-nav">
            <button class="nav-btn" @click="previousMonth()">‹ Prev</button>
            <h2 class="month-display">{{ $startDate->format('F Y') }}</h2>
            <button class="nav-btn" @click="nextMonth()">Next ›</button>
        </div>
        <div style="display: flex; gap: 12px;">
            <button class="today-btn" @click="goToToday()">Today</button>
            <a href="/tasks/create" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">+ Add Task</a>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="calendar-grid">
        <!-- Day Headers -->
        <div class="day-header">Sun</div>
        <div class="day-header">Mon</div>
        <div class="day-header">Tue</div>
        <div class="day-header">Wed</div>
        <div class="day-header">Thu</div>
        <div class="day-header">Fri</div>
        <div class="day-header">Sat</div>

        <!-- Previous Month Days -->
        @for($i = $firstDayOfWeek - 1; $i >= 0; $i--)
            <div class="day-cell other-month">
                <span class="day-number">{{ \Carbon\Carbon::parse($prevMonth->format('Y-m') . '-' . (\Carbon\Carbon::parse($prevMonth->format('Y-m') . '-01')->daysInMonth - $i))->format('j') }}</span>
            </div>
        @endfor

        <!-- Current Month Days -->
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $date = \Carbon\Carbon::parse($month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT))->format('Y-m-d');
                $isToday = $date === now()->format('Y-m-d');
                $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                $dayTasks = $tasksByDate[$date] ?? [];
                $dayCount = $dailyCounts[$date] ?? ['total' => 0, 'pending' => 0, 'completed' => 0, 'overdue' => 0];
                $firstDayOfWeekNum = \Carbon\Carbon::parse($date)->dayOfWeek;
            @endphp
            
            <div class="day-cell {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                 @click="openDayModal('{{ $date }}', {{ json_encode($dayTasks) }})">
                <span class="day-number {{ $isToday ? 'today' : '' }}">{{ $day }}</span>
                
                @if($dayCount['total'] > 0)
                    <span class="task-count">{{ $dayCount['total'] }}</span>
                @endif
                
                <div class="task-dots">
                    @foreach(array_slice($dayTasks, 0, 5) as $todo)
                        @php
                            $priorityColors = [
                                'low' => '#10b981',
                                'medium' => '#f59e0b',
                                'high' => '#ef4444',
                                'urgent' => '#dc2626',
                            ];
                            $color = $priorityColors[$todo->priority] ?? '#667eea';
                        @endphp
                        <div class="task-dot" 
                             style="background: {{ $color }};"
                             @mouseover="showTaskDetail(event, {{ json_encode($todo) }})"
                             @mouseout="hideTaskDetail()">
                        </div>
                    @endforeach
                </div>
                
                @if(count($dayTasks) > 5)
                    <div style="font-size: 10px; color: #999; margin-top: auto;">+{{ count($dayTasks) - 5 }} more</div>
                @endif
            </div>
        @endfor

        <!-- Next Month Days -->
        @php
            $lastDayOfWeek = \Carbon\Carbon::parse($month . '-' . $daysInMonth)->dayOfWeek;
            $nextMonthDays = 6 - $lastDayOfWeek;
        @endphp
        @for($day = 1; $day <= $nextMonthDays; $day++)
            <div class="day-cell other-month">
                <span class="day-number">{{ $day }}</span>
            </div>
        @endfor
    </div>
</div>

<!-- Task Detail Modal -->
<div class="calendar-modal" id="dayModal" x-show="dayModalOpen" @click.self="closeDayModal()">
    <div class="modal-content" @click.stop>
        <div class="modal-header">
            <h3 class="modal-title" x-text="selectedDate"></h3>
            <button class="close-modal" @click="closeDayModal()">×</button>
        </div>
        
        <div class="task-list">
            <template x-for="task in selectedTasks">
                <div class="task-item" :class="{ 'completed': task.status === 'completed' }">
                    <span class="priority-dot" :class="'priority-' + task.priority"></span>
                    <strong x-text="task.title"></strong>
                </div>
            </template>
        </div>
        
        <div class="modal-actions">
            <button class="btn-secondary" @click="closeDayModal()">Close</button>
            <a :href="'/tasks/create?due_date=' + selectedDate" class="btn-primary">Add Task</a>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function calendarState() {
        return {
            dayModalOpen: false,
            selectedDate: '',
            selectedTasks: [],
            
            openDayModal(date, tasks) {
                this.selectedDate = new Date(date).toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                this.selectedTasks = tasks;
                this.dayModalOpen = true;
            },
            
            closeDayModal() {
                this.dayModalOpen = false;
            },
            
            previousMonth() {
                const date = new URLSearchParams(window.location.search).get('month');
                const current = new Date(date + '-01');
                current.setMonth(current.getMonth() - 1);
                window.location.href = '?month=' + current.toISOString().substring(0, 7);
            },
            
            nextMonth() {
                const date = new URLSearchParams(window.location.search).get('month') || new Date().toISOString().substring(0, 7);
                const current = new Date(date + '-01');
                current.setMonth(current.getMonth() + 1);
                window.location.href = '?month=' + current.toISOString().substring(0, 7);
            },
            
            goToToday() {
                window.location.href = '?month=' + new Date().toISOString().substring(0, 7);
            },
            
            showTaskDetail(event, task) {
                // Show tooltip with task details
                const tooltip = document.createElement('div');
                tooltip.className = 'task-detail-tooltip';
                tooltip.innerHTML = `
                    <div class="task-detail-title">${task.title}</div>
                    <div class="task-detail-info">Priority: ${task.priority}</div>
                    <div class="task-detail-info">Status: ${task.status}</div>
                `;
                document.body.appendChild(tooltip);
                
                const rect = event.target.getBoundingClientRect();
                tooltip.style.left = rect.left + 'px';
                tooltip.style.top = (rect.bottom + 10) + 'px';
                tooltip.style.display = 'block';
                
                // Clean up on mouseout
                event.target.addEventListener('mouseleave', function cleanup() {
                    tooltip.remove();
                    event.target.removeEventListener('mouseleave', cleanup);
                }, { once: true });
            },
            
            hideTaskDetail() {
                // Tooltip cleanup handled in showTaskDetail
            }
        }
    }
</script>
@endsection