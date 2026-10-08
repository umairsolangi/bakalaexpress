<?php

namespace App\Http\Controllers;

use App\Models\SellerVerification;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class SellerVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Authentication middleware will be applied in routes file
    }
    
    /**
     * Show the verification request form.
     *
     * @return \Illuminate\View\View
     */
    public function showVerificationForm()
    {
        $seller = Auth::guard('seller')->user();
        
        // Check if the seller already has a verification request
        $verificationRequest = SellerVerification::where('seller_id', $seller->id)->first();
        
        return view('seller.verification.apply', compact('seller', 'verificationRequest'));
    }
    
    /**
     * Submit a verification request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitVerificationRequest(Request $request)
    {
        $request->validate([
            'business_description' => 'required|string|min:50',
            'reason_for_verification' => 'required|string|min:50',
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB per document
            'verification_agreement' => 'required',
        ]);
        
        $seller = Auth::guard('seller')->user();
        
        // Check if seller already has a pending or approved verification
        $existingVerification = SellerVerification::where('seller_id', $seller->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();
            
        if ($existingVerification) {
            return back()->with('error', 'You already have a ' . $existingVerification->status . ' verification request.');
        }
        
        // Handle document uploads
        $documentPaths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $path = $document->store('verification_documents/' . $seller->id, 'public');
                $documentPaths[] = $path;
            }
        }
        
        // Create or update verification request
        $verificationData = [
            'seller_id' => $seller->id,
            'status' => 'pending',
            'documents' => $documentPaths,
            'business_description' => $request->business_description,
            'reason_for_verification' => $request->reason_for_verification,
            'submitted_at' => now(),
        ];
        
        $existingRequest = SellerVerification::where('seller_id', $seller->id)->first();
        
        if ($existingRequest) {
            $existingRequest->update($verificationData);
        } else {
            SellerVerification::create($verificationData);
        }
        
        // Notify admin about the verification request (you can implement this later)
        
        return redirect()->route('seller.verification.status')
            ->with('success', 'Your verification request has been submitted successfully. We will review your request shortly.');
    }
    
    /**
     * Show the verification status.
     *
     * @return \Illuminate\View\View
     */
    public function showVerificationStatus()
    {
        $seller = Auth::guard('seller')->user();
        $verificationRequest = SellerVerification::where('seller_id', $seller->id)->first();
        
        if (!$verificationRequest) {
            return redirect()->route('seller.verification.apply')
                ->with('info', 'You have not submitted a verification request yet.');
        }
        
        return view('seller.verification.status', compact('seller', 'verificationRequest'));
    }
}
