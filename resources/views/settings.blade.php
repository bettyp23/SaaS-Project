@extends('layouts.app')

@section('title', 'Settings - Todo Tracker')
@section('page-title', 'Settings')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <!-- Profile Settings -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 24px; margin-bottom: 24px;">👤 Profile Settings</h2>
        
        @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px; font-weight: 500;">Full Name</label>
                    <input type="text" name="name" value="{{ $user->name }}" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px; font-weight: 500;">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px; font-weight: 500;">Timezone</label>
                    <input type="text" name="timezone" value="{{ $user->timezone }}" placeholder="e.g., America/New_York" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">Update Profile</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 24px; margin-bottom: 24px;">🔒 Change Password</h2>
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px; font-weight: 500;">Current Password</label>
                    <input type="password" name="current_password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                    @error('current_password')
                        <p style="color: #ef4444; font-size: 13px; margin-top: 6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px; font-weight: 500;">New Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; color: #666; font-size: 14px; margin-bottom: 6px; font-weight: 500;">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <button type="submit" style="background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">Change Password</button>
                </div>
            </div>
        </form>
    </div>

    <!-- App Preferences -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #333; font-size: 24px; margin-bottom: 24px;">⚙️ App Preferences</h2>
        <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; color: #666;">
            <p>Additional preferences coming soon...</p>
        </div>
    </div>
</div>
@endsection
