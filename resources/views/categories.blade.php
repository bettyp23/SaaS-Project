@extends('layouts.app')

@section('title', 'Categories - Todo Tracker')
@section('page-title', 'Categories')

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 28px;">🏷️ Categories</h2>
        <button onclick="document.getElementById('add-category-form').style.display='block'" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">+ New Category</button>
    </div>

    @if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
    @endif

    <!-- Add Category Form -->
    <div id="add-category-form" style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 30px; display: none;">
        <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Add New Category</h3>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px;">Category Name</label>
                    <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px;">Color</label>
                    <input type="color" name="color" value="#667eea" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer;">
                </div>
                <div>
                    <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; white-space: nowrap;">Add Category</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Categories Grid -->
    @php
        $categories = \App\Models\Tag::all();
    @endphp

    @if($categories->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            @foreach($categories as $tag)
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-left: 4px solid {{ $tag->color ?? '#667eea' }};">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="color: #333; font-size: 18px; font-weight: 600;">{{ $tag->name }}</h3>
                        <form action="{{ route('categories.destroy', $tag->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #ef4444; color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">Delete</button>
                        </form>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; border-radius: 50%; background: {{ $tag->color ?? '#667eea' }};"></div>
                        <span style="color: #666; font-size: 14px;">{{ $tag->todos->count() ?? 0 }} tasks</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: white; border-radius: 12px; padding: 60px; text-align: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <div style="font-size: 64px; margin-bottom: 16px; opacity: 0.5;">🏷️</div>
            <p style="font-size: 18px; margin-bottom: 8px; color: #999;">No categories yet</p>
            <p style="font-size: 14px; color: #999;">Create your first category to organize tasks</p>
        </div>
    @endif
</div>

<script>
function closeForm() {
    document.getElementById('add-category-form').style.display='none';
}
</script>
@endsection
