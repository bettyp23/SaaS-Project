@extends('layouts.app')

@section('title', 'Kanban Board - Todo Tracker')
@section('page-title', 'Kanban Board')

@section('content')
<div style="max-width: 1600px; margin: 0 auto;">
    <div id="kanban-board" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; min-height: 600px;">
        <!-- To Do Column -->
        <div style="background: #f8f9fa; border-radius: 12px; padding: 20px;">
            <h2 style="color: #333; font-size: 18px; font-weight: 600; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #ddd;">
                📋 To Do ({{ $todos->where('status', 'pending')->count() }})
            </h2>
            <div id="pending-column" style="min-height: 500px;">
                @foreach($todos->where('status', 'pending') as $todo)
                    <div class="kanban-card" draggable="true" data-id="{{ $todo->id }}" style="background: white; border-radius: 8px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: move;">
                        <h3 style="color: #333; font-size: 14px; font-weight: 600; margin-bottom: 8px;">{{ $todo->title }}</h3>
                        @if($todo->description)
                            <p style="color: #666; font-size: 12px; margin-bottom: 8px;">{{ Str::limit($todo->description, 50) }}</p>
                        @endif
                        @php
                            $priorityColors = ['low' => '#10b981', 'medium' => '#f59e0b', 'high' => '#ef4444'];
                            $color = $priorityColors[$todo->priority] ?? '#6b7280';
                        @endphp
                        <span style="background: {{ $color }}; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                            {{ ucfirst($todo->priority) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- In Progress Column -->
        <div style="background: #f8f9fa; border-radius: 12px; padding: 20px;">
            <h2 style="color: #333; font-size: 18px; font-weight: 600; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #ddd;">
                🔄 In Progress ({{ $todos->where('status', 'in_progress')->count() }})
            </h2>
            <div id="in-progress-column" style="min-height: 500px;">
                @foreach($todos->where('status', 'in_progress') as $todo)
                    <div class="kanban-card" draggable="true" data-id="{{ $todo->id }}" style="background: white; border-radius: 8px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: move;">
                        <h3 style="color: #333; font-size: 14px; font-weight: 600; margin-bottom: 8px;">{{ $todo->title }}</h3>
                        @if($todo->description)
                            <p style="color: #666; font-size: 12px; margin-bottom: 8px;">{{ Str::limit($todo->description, 50) }}</p>
                        @endif
                        @php
                            $priorityColors = ['low' => '#10b981', 'medium' => '#f59e0b', 'high' => '#ef4444'];
                            $color = $priorityColors[$todo->priority] ?? '#6b7280';
                        @endphp
                        <span style="background: {{ $color }}; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                            {{ ucfirst($todo->priority) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Done Column -->
        <div style="background: #f8f9fa; border-radius: 12px; padding: 20px;">
            <h2 style="color: #333; font-size: 18px; font-weight: 600; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #ddd;">
                ✅ Done ({{ $todos->where('status', 'completed')->count() }})
            </h2>
            <div id="completed-column" style="min-height: 500px;">
                @foreach($todos->where('status', 'completed') as $todo)
                    <div class="kanban-card" draggable="true" data-id="{{ $todo->id }}" style="background: white; border-radius: 8px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: move; opacity: 0.85;">
                        <h3 style="color: #666; font-size: 14px; font-weight: 600; margin-bottom: 8px; text-decoration: line-through;">{{ $todo->title }}</h3>
                        @if($todo->description)
                            <p style="color: #999; font-size: 12px; margin-bottom: 8px;">{{ Str::limit($todo->description, 50) }}</p>
                        @endif
                        <span style="background: #10b981; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                            Completed
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = {
        'pending-column': 'pending',
        'in-progress-column': 'in_progress',
        'completed-column': 'completed'
    };

    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    Object.keys(columns).forEach(columnId => {
        const column = document.getElementById(columnId);
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
    });

    let draggedElement = null;

    function handleDragStart(e) {
        draggedElement = this;
        this.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragEnd(e) {
        this.style.opacity = '1';
        document.querySelectorAll('.drag-over').forEach(element => {
            element.classList.remove('drag-over');
        });
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        this.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        if (draggedElement !== this) {
            const todoId = draggedElement.getAttribute('data-id');
            const targetColumn = this.closest('[id$="-column"]').id;
            const newStatus = columns[targetColumn];

            // Move DOM element
            this.appendChild(draggedElement);

            // Update server
            fetch(`/tasks/${todoId}/update-status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        return false;
    }
});
</script>

<style>
.drag-over {
    background: #e8f5e9 !important;
    border: 2px dashed #4caf50 !important;
}
</style>
@endsection
