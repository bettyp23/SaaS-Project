@extends('layouts.app')

@section('title', 'Edit Task - Todo Tracker')
@section('page-title', 'Edit Task')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #333; font-size: 28px; margin-bottom: 30px;">Edit Task</h2>
        
        <form action="{{ route('tasks.update', $todo->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; gap: 24px;">
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Task Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $todo->title) }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>

                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; resize: vertical;">{{ old('description', $todo->description) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Status</label>
                        <select name="status" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                            <option value="pending" {{ $todo->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $todo->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $todo->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Priority</label>
                        <select name="priority" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                            <option value="low" {{ $todo->priority == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $todo->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $todo->priority == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $todo->due_date ? \Carbon\Carbon::parse($todo->due_date)->format('Y-m-d') : '') }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <a href="{{ route('tasks.index') }}" style="background: #6b7280; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">Cancel</a>
                    <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">Update Task</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
