@extends('layouts.app')

@section('title', 'Calendar - Todo Tracker')
@section('page-title', 'Calendar')

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: #333; font-size: 24px; margin-bottom: 10px;">📅 Calendar View</h2>
            <p style="color: #666;">Tasks with due dates are shown below</p>
        </div>

        @if($todos->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                @foreach($todos as $todo)
                    <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; border-left: 4px solid #667eea;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                            <h3 style="color: #333; font-size: 16px; font-weight: 600;">{{ $todo->title }}</h3>
                            @php
                                $priorityColors = ['low' => '#10b981', 'medium' => '#f59e0b', 'high' => '#ef4444'];
                                $color = $priorityColors[$todo->priority] ?? '#6b7280';
                            @endphp
                            <span style="background: {{ $color }}; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px;">
                                {{ ucfirst($todo->priority) }}
                            </span>
                        </div>
                        
                        @if($todo->description)
                            <p style="color: #666; font-size: 13px; margin-bottom: 12px;">{{ Str::limit($todo->description, 80) }}</p>
                        @endif

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="color: #667eea; font-size: 14px; font-weight: 500;">
                                📅 {{ \Carbon\Carbon::parse($todo->due_date)->format('M d, Y') }}
                            </div>
                            <span style="background: {{ $todo->status === 'completed' ? '#10b981' : ($todo->status === 'in_progress' ? '#f59e0b' : '#6b7280') }}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; text-transform: capitalize;">
                                {{ str_replace('_', ' ', $todo->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: 60px; text-align: center; color: #999;">
                <div style="font-size: 64px; margin-bottom: 16px; opacity: 0.5;">📅</div>
                <p style="font-size: 18px; margin-bottom: 8px;">No tasks with due dates</p>
                <p style="font-size: 14px;">Add due dates to your tasks to see them here</p>
            </div>
        @endif
    </div>
</div>
@endsection
