<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentMethod;
use Stripe\Webhook;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Get available subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::active()->orderBy('price')->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get current user's subscription.
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active subscription.',
            ]);
        }

        $subscription->load('plan');

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_method_id' => 'required|string',
        ]);

        $plan = SubscriptionPlan::find($validated['plan_id']);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.',
            ], 404);
        }

        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create or get Stripe customer
            $customer = $this->getOrCreateStripeCustomer($user);

            // Create Stripe subscription
            $stripeSubscription = Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    [
                        'price_data' => [
                            'currency' => $plan->currency,
                            'product_data' => [
                                'name' => $plan->name,
                                'description' => $plan->description,
                            ],
                            'unit_amount' => $plan->price * 100, // Convert to cents
                            'recurring' => [
                                'interval' => $plan->interval,
                            ],
                        ],
                    ],
                ],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // Create local subscription record
            $subscription = $user->subscription()->create([
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscription->id,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->add($plan->interval === 'yearly' ? 1 : 0, 'year')->add($plan->interval === 'monthly' ? 1 : 0, 'month'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully.',
                'data' => $subscription,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Cancel Stripe subscription
            if ($subscription->stripe_subscription_id) {
                $stripeSubscription = Subscription::retrieve($subscription->stripe_subscription_id);
                $stripeSubscription->cancel();
            }

            // Update local subscription
            $subscription->cancel();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reactivate subscription.
     */
    public function reactivate(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found.',
            ], 404);
        }

        if ($subscription->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is not cancelled.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Reactivate Stripe subscription
            if ($subscription->stripe_subscription_id) {
                $stripeSubscription = Subscription::retrieve($subscription->stripe_subscription_id);
                $stripeSubscription->resume();
            }

            // Update local subscription
            $subscription->reactivate();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription reactivated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate subscription.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subscription invoices.
     */
    public function invoices(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found.',
            ], 404);
        }

        try {
            // In a real app, you'd fetch invoices from Stripe
            $invoices = [
                [
                    'id' => 'inv_123',
                    'amount' => $subscription->plan->price,
                    'currency' => $subscription->plan->currency,
                    'status' => 'paid',
                    'created' => $subscription->created_at->timestamp,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $invoices,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subscription usage.
     */
    public function usage(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;
        $plan = $subscription?->plan;

        $usage = [
            'todos' => [
                'used' => $user->todos()->count(),
                'limit' => $plan?->max_todos ?? 50,
                'unlimited' => !$plan?->max_todos,
            ],
            'teams' => [
                'used' => $user->ownedTeams()->count(),
                'limit' => $plan?->max_team_members ?? 1,
                'unlimited' => !$plan?->max_team_members,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $usage,
        ]);
    }

    /**
     * Handle Stripe webhooks.
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            switch ($event->type) {
                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($event->data->object);
                    break;
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get or create Stripe customer.
     */
    private function getOrCreateStripeCustomer($user)
    {
        if ($user->stripe_customer_id) {
            return Customer::retrieve($user->stripe_customer_id);
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Handle subscription created webhook.
     */
    private function handleSubscriptionCreated($subscription)
    {
        // Implementation for subscription created
    }

    /**
     * Handle subscription updated webhook.
     */
    private function handleSubscriptionUpdated($subscription)
    {
        $userSubscription = UserSubscription::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($userSubscription) {
            $userSubscription->update([
                'status' => $subscription->status,
                'current_period_start' => now()->createFromTimestamp($subscription->current_period_start),
                'current_period_end' => now()->createFromTimestamp($subscription->current_period_end),
            ]);
        }
    }

    /**
     * Handle subscription deleted webhook.
     */
    private function handleSubscriptionDeleted($subscription)
    {
        $userSubscription = UserSubscription::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($userSubscription) {
            $userSubscription->update(['status' => 'cancelled']);
        }
    }

    /**
     * Handle payment succeeded webhook.
     */
    private function handlePaymentSucceeded($invoice)
    {
        // Implementation for payment succeeded
    }

    /**
     * Handle payment failed webhook.
     */
    private function handlePaymentFailed($invoice)
    {
        // Implementation for payment failed
    }
}
