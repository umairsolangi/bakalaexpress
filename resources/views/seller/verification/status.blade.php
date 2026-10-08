@extends('seller.layouts.app')

@section('page-title', 'Verification Status')
@section('page-subtitle', 'Track your verification request')

@section('content')
<div class="animate-stagger">
    <div class="max-w-3xl mx-auto">
        @if(!isset($verificationRequest) || !$verificationRequest)
            <!-- No Request -->
            <div class="card-elevated p-10 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
                    <i class="fas fa-shield-halved text-gray-300 text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 font-display mb-2">No Verification Request Found</h3>
                <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">You haven't submitted a verification request yet. Get verified to unlock premium features.</p>
                <a href="{{ route('seller.verification.apply') }}" class="btn-primary-custom">
                    <i class="fas fa-shield-halved"></i> Apply for Verification
                </a>
            </div>
        @else
            <!-- Status Header -->
            <div class="card-elevated overflow-hidden mb-8">
                <div class="p-8 text-center border-b border-gray-100">
                    @if($verificationRequest->status === 'pending')
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-500/10">
                            <i class="fas fa-clock text-amber-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 font-display mb-1">Pending Review</h3>
                        <p class="text-sm text-gray-400">Submitted on {{ $verificationRequest->submitted_at->format('M d, Y') }}</p>
                        <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 border border-amber-200">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                            </span>
                            <span class="text-xs font-semibold text-amber-700">Review in progress (1-3 business days)</span>
                        </div>
                    @elseif($verificationRequest->status === 'approved')
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/10">
                            <i class="fas fa-check-circle text-emerald-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 font-display mb-1">Verification Approved! 🎉</h3>
                        <p class="text-sm text-gray-400">Approved on {{ $verificationRequest->reviewed_at ? $verificationRequest->reviewed_at->format('M d, Y') : 'N/A' }}</p>
                    @else
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-red-500/10">
                            <i class="fas fa-times-circle text-red-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 font-display mb-1">Verification Rejected</h3>
                        <p class="text-sm text-gray-400">Rejected on {{ $verificationRequest->reviewed_at ? $verificationRequest->reviewed_at->format('M d, Y') : 'N/A' }}</p>
                    @endif
                </div>

                <!-- Request Details -->
                <div class="p-6">
                    <h4 class="text-sm font-bold text-gray-900 mb-4">Request Details</h4>
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-gray-50">
                            <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide mb-1.5">Business Description</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $verificationRequest->business_description }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-gray-50">
                            <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide mb-1.5">Reason for Verification</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $verificationRequest->reason_for_verification }}</p>
                        </div>

                        @if($verificationRequest->status === 'rejected')
                            <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                                <p class="text-[0.65rem] text-red-500 font-semibold uppercase tracking-wide mb-1.5">Rejection Reason</p>
                                <p class="text-sm text-red-700 leading-relaxed font-medium">{{ $verificationRequest->rejection_reason }}</p>
                            </div>
                        @endif

                        <!-- Documents -->
                        <div>
                            <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide mb-3">Submitted Documents</p>
                            @if(is_array($verificationRequest->documents) && count($verificationRequest->documents) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($verificationRequest->documents as $index => $document)
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:border-gray-200 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                                                    <i class="fas fa-file-alt text-blue-500 text-sm"></i>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700">Document #{{ $index + 1 }}</span>
                                            </div>
                                            <a href="{{ Storage::url($document) }}" target="_blank"
                                                class="text-xs font-semibold text-primary hover:text-primary-dark px-3 py-1.5 rounded-lg bg-primary/5 hover:bg-primary/10 transition-colors">
                                                View <i class="fas fa-external-link-alt text-[0.6rem] ml-1"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400">No documents available</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t border-gray-100">
                        @if($verificationRequest->status === 'rejected')
                            <a href="{{ route('seller.verification.apply') }}" class="btn-primary-custom justify-center">
                                <i class="fas fa-redo"></i> Submit New Request
                            </a>
                        @endif
                        <a href="{{ route('seller.panel') }}" class="btn-secondary-custom justify-center">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection