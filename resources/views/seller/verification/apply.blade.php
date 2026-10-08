@extends('seller.layouts.app')

@section('page-title', 'Seller Verification')
@section('page-subtitle', 'Get verified to unlock premium features')

@section('content')
<div class="animate-stagger">
    <div class="max-w-3xl mx-auto">
        <!-- Benefits Card -->
        <div class="card-elevated overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-6 border-b border-amber-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                        <i class="fas fa-star text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 font-display">Benefits of Verification</h3>
                        <p class="text-xs text-gray-500">Unlock these features with a verified badge</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        $benefits = [
                            ['icon' => 'fas fa-shield-halved', 'color' => 'bg-blue-50 text-blue-500', 'text' => 'Gain customer trust with a verified badge'],
                            ['icon' => 'fas fa-arrow-up-wide-short', 'color' => 'bg-emerald-50 text-emerald-500', 'text' => 'Appear higher in search results'],
                            ['icon' => 'fas fa-gem', 'color' => 'bg-violet-50 text-violet-500', 'text' => 'Unlock premium selling features'],
                            ['icon' => 'fas fa-list-check', 'color' => 'bg-sky-50 text-sky-500', 'text' => 'Higher limit on service listings'],
                            ['icon' => 'fas fa-percent', 'color' => 'bg-rose-50 text-rose-500', 'text' => 'Reduced service fees'],
                        ];
                    @endphp
                    @foreach($benefits as $benefit)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 rounded-lg {{ $benefit['color'] }} flex items-center justify-center flex-shrink-0">
                                <i class="{{ $benefit['icon'] }} text-sm"></i>
                            </div>
                            <p class="text-sm text-gray-700 font-medium leading-snug">{{ $benefit['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Status Display -->
        @if(isset($verificationRequest))
            @if($verificationRequest->status == 'pending')
                <div class="card-elevated p-6 mb-8 border-l-4 border-amber-400">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-amber-500"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Verification Pending</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Your application is under review. This process typically takes 1-3 business days.</p>
                        </div>
                    </div>
                </div>
            @elseif($verificationRequest->status == 'approved')
                <div class="card-elevated p-6 mb-8 border-l-4 border-emerald-400">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Verification Approved! 🎉</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Congratulations! Your account has been successfully verified.</p>
                        </div>
                    </div>
                </div>
            @elseif($verificationRequest->status == 'rejected')
                <div class="card-elevated p-6 mb-8 border-l-4 border-red-400">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-500"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Verification Rejected</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Reason: <strong>{{ $verificationRequest->rejection_reason }}</strong></p>
                            <p class="text-xs text-gray-400 mt-1">You can submit a new application below.</p>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Verification Form -->
        @if(!isset($verificationRequest) || ($verificationRequest->status != 'approved' && $verificationRequest->status != 'pending'))
            <div class="card-elevated overflow-hidden">
                <form action="{{ route('seller.verification.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Business Details -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xs font-bold">1</span>
                            <h3 class="text-sm font-bold text-gray-900">Business Details</h3>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="business_description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                    Business Description <span class="text-red-400">*</span>
                                </label>
                                <textarea id="business_description" name="business_description" rows="4" required
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none @error('business_description') border-red-300 @enderror"
                                    placeholder="Describe your business, services, and experience...">{{ old('business_description') }}</textarea>
                                @error('business_description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-400 mt-1.5">Minimum 50 characters</p>
                            </div>

                            <div>
                                <label for="reason_for_verification" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                    Reason for Verification <span class="text-red-400">*</span>
                                </label>
                                <textarea id="reason_for_verification" name="reason_for_verification" rows="4" required
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none @error('reason_for_verification') border-red-300 @enderror"
                                    placeholder="Explain why you want to get verified...">{{ old('reason_for_verification') }}</textarea>
                                @error('reason_for_verification')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-400 mt-1.5">Minimum 50 characters</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 text-xs font-bold">2</span>
                            <h3 class="text-sm font-bold text-gray-900">Required Documents</h3>
                        </div>

                        <div class="space-y-4">
                            @php
                                $documents = [
                                    ['label' => 'Business Registration', 'icon' => 'fas fa-file-alt', 'color' => 'bg-blue-50 text-blue-500', 'info' => 'Accepted formats: PDF, JPG, PNG (max 5MB)', 'required' => true],
                                    ['label' => 'ID Proof', 'icon' => 'fas fa-id-card', 'color' => 'bg-emerald-50 text-emerald-500', 'info' => 'Government ID (passport, driver\'s license, etc.)', 'required' => true],
                                    ['label' => 'Additional Document (Optional)', 'icon' => 'fas fa-file-lines', 'color' => 'bg-amber-50 text-amber-500', 'info' => 'Any additional supporting documentation', 'required' => false],
                                ];
                            @endphp

                            @foreach($documents as $doc)
                                <div class="border border-gray-200 rounded-2xl p-5 hover:border-gray-300 transition-colors group/upload">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-9 h-9 rounded-lg {{ $doc['color'] }} flex items-center justify-center">
                                            <i class="{{ $doc['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $doc['label'] }} @if($doc['required'])<span class="text-red-400">*</span>@endif</p>
                                            <p class="text-xs text-gray-400">{{ $doc['info'] }}</p>
                                        </div>
                                    </div>
                                    <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png"
                                        {{ $doc['required'] ? 'required' : '' }}
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer @error('documents.*') border-red-300 @enderror">
                                </div>
                            @endforeach

                            @error('documents.*')
                                <p class="text-red-500 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Agreement & Submit -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 text-xs font-bold">3</span>
                            <h3 class="text-sm font-bold text-gray-900">Agreement</h3>
                        </div>

                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                            <input type="checkbox" name="verification_agreement" required
                                class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-600 leading-relaxed">
                                I confirm that all information provided is accurate and complete. I understand that providing false information may result in rejection of verification and/or account termination.
                            </span>
                        </label>
                        @error('verification_agreement')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="p-6 bg-gray-50/50 flex flex-col sm:flex-row justify-end gap-3">
                        <button type="button" onclick="history.back()" class="btn-secondary-custom justify-center">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn-primary-custom justify-center">
                            <i class="fas fa-shield-halved"></i> Submit Verification
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection