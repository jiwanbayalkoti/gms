<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PaymentController
 * 
 * Handles payment management with gym-based data isolation.
 */
class PaymentController extends BaseController
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('payments.view');

        $query = Payment::with(['member', 'membershipPlan', 'gym']);
        
        // Filter by member
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        
        // Filter by status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        
        $payments = $this->applyGymFilter($query)->latest('payment_date')->get();
        
        // Get members for filter dropdown
        $user = Auth::user();
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        // If AJAX request, return JSON with table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('payments._table-body', compact('payments'))->render()
            ]);
        }

        return view('payments.index', compact('payments', 'members'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('payments.create');

        $user = Auth::user();

        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $plansQuery = MembershipPlan::where('is_active', true);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $plansQuery->where('gym_id', $user->gym_id);
        }
        $plans = $plansQuery->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('payments.create', compact('members', 'plans'))->render();
        }

        return view('payments.create-page', compact('members', 'plans'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('payments.create');

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:Cash,Card,Stripe,PayPal,Bank Transfer',
            'transaction_id' => 'nullable|string|max:255',
            'payment_status' => 'required|in:Completed,Failed,Refunded',
            'payment_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:payment_date',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $member = User::findOrFail($validated['member_id']);
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid member selected.'
            ], 422);
        }

        // If membership plan is selected, validate it belongs to same gym
        if ($validated['membership_plan_id']) {
            $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
            if (!$user->isSuperAdmin() && $plan->gym_id !== $user->gym_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid membership plan selected.'
                ], 422);
            }

            // Auto-calculate expiry date if not provided and plan has duration
            if (!$validated['expiry_date'] && $plan->duration_days) {
                $validated['expiry_date'] = now()->addDays($plan->duration_days)->toDateString();
            }

            // If amount matches original price but discount is active, use discounted price
            if ($plan->isDiscountActive() && $validated['amount'] == $plan->price) {
                $discountedPrice = $plan->getDiscountedPrice();
                $validated['amount'] = $discountedPrice;
            }
        }

        $paymentData = [
            'member_id' => $validated['member_id'],
            'membership_plan_id' => $validated['membership_plan_id'] ?? null,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payment_status' => $validated['payment_status'],
            'payment_date' => $validated['payment_date'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $paymentData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $paymentData['gym_id'] = $user->gym_id;
        }

        $paymentData['created_by'] = $user->id;

        $payment = Payment::create($paymentData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully.',
                'payment' => $payment
            ]);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment created successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('payments.view');

        $payment = Payment::with(['member', 'membershipPlan', 'gym'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'html' => view('payments.show', compact('payment'))->render()
            ]);
        }

        return view('payments.show', compact('payment'));
    }

    /**
     * Display invoice for the specified payment.
     */
    public function invoice(Request $request, string $id)
    {
        $this->authorizePermission('payments.view');

        $payment = Payment::with(['member', 'membershipPlan', 'gym'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'html' => view('payments.invoice', compact('payment'))->render()
            ]);
        }

        return view('payments.invoice', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('payments.update');

        $payment = Payment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $user = Auth::user();

        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $plansQuery = MembershipPlan::where('is_active', true);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $plansQuery->where('gym_id', $user->gym_id);
        }
        $plans = $plansQuery->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('payments.edit', compact('payment', 'members', 'plans'))->render();
        }

        return view('payments.edit-page', compact('payment', 'members', 'plans'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('payments.update');

        $payment = Payment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:Cash,Card,Stripe,PayPal,Bank Transfer',
            'transaction_id' => 'nullable|string|max:255',
            'payment_status' => 'required|in:Completed,Failed,Refunded',
            'payment_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:payment_date',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $member = User::findOrFail($validated['member_id']);
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid member selected.'
            ], 422);
        }

        $payment->member_id = $validated['member_id'];
        $payment->membership_plan_id = $validated['membership_plan_id'] ?? null;
        $payment->amount = $validated['amount'];
        $payment->payment_method = $validated['payment_method'];
        $payment->transaction_id = $validated['transaction_id'] ?? null;
        $payment->payment_status = $validated['payment_status'];
        $payment->payment_date = $validated['payment_date'];
        $payment->expiry_date = $validated['expiry_date'] ?? null;
        $payment->notes = $validated['notes'] ?? null;
        $payment->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully.',
                'payment' => $payment
            ]);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('payments.delete');

        $payment = Payment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $payment->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully.'
            ]);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Get payments for a specific member
     */
    public function memberPayments($member)
    {
        $this->authorizePermission('payments.view');

        $member = User::where('role', 'Member')->findOrFail($member);
        $this->validateGymAccess($member->gym_id);

        $payments = Payment::with('membershipPlan')
            ->where('member_id', $member->id)
            ->latest('payment_date')
            ->get();

        return view('payments.member', compact('payments', 'member'));
    }

    /**
     * Process Stripe payment
     */
    public function processStripePayment(Request $request)
    {
        // Placeholder for Stripe integration
        // This would integrate with Stripe API
        return response()->json([
            'success' => false,
            'message' => 'Stripe integration not implemented yet.'
        ], 501);
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('payments.view');

        $query = Payment::with(['member', 'membershipPlan', 'gym']);
        
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        
        $payments = $this->applyGymFilter($query)->latest('payment_date')->get();

        return $this->apiSuccess([
            'payments' => $payments,
            'count' => $payments->count()
        ], 'Payments retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('payments.view');

        $payment = Payment::with(['member', 'membershipPlan', 'gym'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        return $this->apiSuccess($payment, 'Payment retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('payments.create');

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Card,Online,Other',
            'payment_status' => 'required|in:Pending,Completed,Failed,Refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $member = User::findOrFail($validated['member_id']);
        $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);

        $this->validateGymAccess($member->gym_id);
        $this->validateGymAccess($plan->gym_id);

        $paymentData = [
            'member_id' => $validated['member_id'],
            'membership_plan_id' => $validated['membership_plan_id'],
            'gym_id' => $member->gym_id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'notes' => $validated['notes'] ?? null,
        ];

        if ($validated['payment_status'] === 'Completed') {
            $paymentData['expiry_date'] = now()->addMonths($plan->duration_months);
        }

        $payment = Payment::create($paymentData);

        return $this->apiSuccess($payment->load(['member', 'membershipPlan', 'gym']), 'Payment created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('payments.update');

        $payment = Payment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Card,Online,Other',
            'payment_status' => 'required|in:Pending,Completed,Failed,Refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = User::findOrFail($validated['member_id']);
        $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);

        $this->validateGymAccess($member->gym_id);
        $this->validateGymAccess($plan->gym_id);

        $payment->member_id = $validated['member_id'];
        $payment->membership_plan_id = $validated['membership_plan_id'];
        $payment->amount = $validated['amount'];
        $payment->payment_date = $validated['payment_date'];
        $payment->payment_method = $validated['payment_method'];
        $payment->payment_status = $validated['payment_status'];
        $payment->notes = $validated['notes'] ?? null;

        if ($validated['payment_status'] === 'Completed' && !$payment->expiry_date) {
            $payment->expiry_date = now()->addMonths($plan->duration_months);
        }

        $payment->save();

        return $this->apiSuccess($payment->load(['member', 'membershipPlan', 'gym']), 'Payment updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('payments.delete');

        $payment = Payment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $payment->delete();

        return $this->apiSuccess(null, 'Payment deleted successfully');
    }

    public function apiInvoice(Request $request, $payment)
    {
        $this->authorizePermission('payments.view');

        $payment = Payment::with(['member', 'membershipPlan', 'gym'])->findOrFail($payment);
        $this->validateGymAccess($payment->gym_id);

        return $this->apiSuccess([
            'payment' => $payment,
            'invoice_html' => view('payments.invoice', compact('payment'))->render()
        ], 'Invoice retrieved successfully');
    }

    public function apiMemberPayments(Request $request, $member)
    {
        $this->authorizePermission('payments.view');

        $member = User::where('role', 'Member')->findOrFail($member);
        $this->validateGymAccess($member->gym_id);

        $payments = Payment::with('membershipPlan')
            ->where('member_id', $member->id)
            ->latest('payment_date')
            ->get();

        return $this->apiSuccess([
            'member' => $member,
            'payments' => $payments,
            'count' => $payments->count()
        ], 'Member payments retrieved successfully');
    }

    public function apiProcessStripePayment(Request $request)
    {
        return $this->apiError('Stripe integration not implemented yet.', null, 501);
    }
}
