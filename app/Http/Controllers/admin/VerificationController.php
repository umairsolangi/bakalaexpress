<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerVerification;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Display a listing of verification requests.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $verificationRequests = SellerVerification::with('seller')
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->latest('submitted_at')
            ->paginate(10);
            
        return view('admin.verifications.index', compact('verificationRequests', 'status'));
    }
    
    /**
     * Show verification request details.
     *
     * @param  \App\Models\SellerVerification  $verification
     * @return \Illuminate\View\View
     */
    public function show(SellerVerification $verification)
    {
        $verification->load('seller');
        return view('admin.verifications.show', compact('verification'));
    }
    
    /**
     * Approve a verification request.
     *
     * @param  \App\Models\SellerVerification  $verification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(SellerVerification $verification)
    {
        $verification->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        
        // Notify the seller about approval (you can implement this later)
        
        return redirect()->route('admin.verifications.index')
            ->with('success', 'Seller verification request has been approved.');
    }
    
    /**
     * Reject a verification request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SellerVerification  $verification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, SellerVerification $verification)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);
        
        $verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        
        // Notify the seller about rejection (you can implement this later)
        
        return redirect()->route('admin.verifications.index')
            ->with('success', 'Seller verification request has been rejected.');
    }
}
