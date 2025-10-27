@extends('layouts.app')

@section('title', 'All Tasks - Todo Tracker')
@section('page-title', 'All Tasks')

@section('content')
<div x-data="{ search: '', sortBy: 'date', filterStatus: 'all' }" style="max-width: 1400px; margin: 0 auto;">
    <!-- Header Actions -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" x-model="search" placeholder="Search tasks..." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
        </div>
        <div style="display: flex; gap: 10px;">
            <select x-model="filterStatus" style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
            <a href="{{ route('tasks.create') }}" style="background: #667eea; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">+ New Task</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
    @endif

    <!-- Tasks Table -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); overflow: hidden;">
        @if($todos->count() > 0)
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 16px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #ddd;">Status</th>
                    <th style="padding: 16px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #ddd;">Title</th>
                    <th style="padding: 16px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #ddd;">Priority</th>
                    <th style="padding: 16px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #ddd;">Due Date</th>
                    <th style="padding: 16px; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todos as $todo)
                <tr style="border-bottom: 1px solid #eee; transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                    <td style="padding: 16px;">
                        <select onchange="updateStatus({{ $todo->id }}, this.value)" style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; cursor: pointer;">
                            <option value="pending" {{ $todo->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $todo->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $todo->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </td>
                    <td style="padding: 16px;">
                        <strong style="color: #333;">{{ $todo->title }}</strong>
                        @if($todo->description)
                        <p style="color: #666; font-size: 13px; margin-top: 4px;">{{ Str::limit($todo->description, 50) }}</p>
                        @endif
                    </td>
                    <td style="padding: 16px;">
                        @php
                            $priorityColors = [
                                'low' => '#10b981',
                                'medium' => '#f59e0b',
                                'high' => '#ef4444',
                                'urgent' => '#dc2626'
                            ];
                            $color = $priorityColors[$todo->priority] ?? '#6b7280';
                        @endphp
                        <span style="background: {{ $color }}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; text-transform: capitalize;">
                            {{ $todo->priority }}
                        </span>
                    </td>
                    <td style="padding: 16px; color: #666;">
                        @if($todo->due_date)
                            {{ \Carbon\Carbon::parse($todo->due_date)->format('M d, Y') }}
                        @else
                            <span style="color: #999;">No due date</span>
                        @endif
                    </td>
                    <td style="padding: 16px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="{{ route('tasks.edit', $todo->id) }}" style="background: #10b981; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 500;">Edit</a>
                            <form action="{{ route('tasks.destroy', $todo->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #ef4444; color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding: 60px; text-align: center; color: #999;">
            <div style="font-size: 64px; margin-bottom: 16px; opacity: 0.5;">📝</div>
            <p style="font-size: 18px; margin-bottom: 8px;">No tasks yet</p>
            <p style="font-size: 14px; margin-bottom: 20px;">Create your first task to get started!</p>
            <a href="{{ route('tasks.create') }}" style="background: #667eea; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; display: inline-block;">Create Task</a>
        </div>
        @endif
    </div>
</div>

<script>
function updateStatus(todoId, status) {
    fetch(`/tasks/${todoId}/update-status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection
