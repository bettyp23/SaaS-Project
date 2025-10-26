<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $plans = SubscriptionPlan::all();
        $currentPlan = Auth::user()->currentSubscription;
        return view('subscriptions.index', compact('plans', 'currentPlan'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        // Subscription logic here
        return back()->with('success', 'Subscribed successfully.');
    }
}
