@extends('layouts.app')

@section('title', 'Create Task - Todo Tracker')
@section('page-title', 'Create Task')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #333; font-size: 28px; margin-bottom: 30px;">Create New Task</h2>
        
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div style="display: grid; gap: 24px;">
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Task Title *</label>
                    <input type="text" name="title" required value="{{ old('title') }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;" placeholder="Enter task title">
                </div>

                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; resize: vertical;" placeholder="Enter task description">{{ old('description') }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Priority</label>
                        <select name="priority" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; color: #666; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <a href="{{ route('tasks.index') }}" style="background: #6b7280; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">Cancel</a>
                    <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">Create Task</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
