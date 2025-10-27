@extends('layouts.app')

@section('title', 'Archive - Todo Tracker')
@section('page-title', 'Archive')

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">
    @if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
    @endif

    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="color: #333; font-size: 24px;">📦 Archived Tasks</h2>
            <p style="color: #666;">Total: {{ $todos->count() }} archived</p>
        </div>

        @if($todos->count() > 0)
            <div style="display: grid; gap: 12px;">
                @foreach($todos as $todo)
                    <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #6b7280;">
                        <div style="flex: 1;">
                            <h3 style="color: #333; font-size: 16px; font-weight: 600; margin-bottom: 4px;">{{ $todo->title }}</h3>
                            @if($todo->description)
                                <p style="color: #666; font-size: 13px; margin-bottom: 8px;">{{ Str::limit($todo->description, 100) }}</p>
                            @endif
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #999;">
                                <span>Deleted: {{ $todo->deleted_at ? \Carbon\Carbon::parse($todo->deleted_at)->format('M d, Y') : 'N/A' }}</span>
                                @if($todo->completed_at)
                                    <span>Completed: {{ \Carbon\Carbon::parse($todo->completed_at)->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('tasks.restore', $todo->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">Restore</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: 60px; text-align: center; color: #999;">
                <div style="font-size: 64px; margin-bottom: 16px; opacity: 0.5;">📦</div>
                <p style="font-size: 18px; margin-bottom: 8px;">No archived tasks</p>
                <p style="font-size: 14px;">Deleted tasks will appear here</p>
            </div>
        @endif
    </div>
</div>
@endsection
