@extends('admin.layouts.app')

@section('title', 'Verification Request Details')

@section('content')
<div class="mb-8">
    <p class="text-sm text-gray-400 font-medium mb-1">Approvals</p>
    <h1 class="text-2xl font-extrabold text-gray-950 font-display tracking-tight">Verification Request Details</h1>
    <p class="text-gray-400 mt-1 text-sm">Review seller details, credentials, and documentation submissions.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Seller Information & Documents -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-user-check text-primary"></i> Seller Information
                </h2>
                <div>
                    @if($verification->status === 'pending')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-50 text-yellow-700">
                            <i class="fas fa-clock text-[9px]"></i> Pending
                        </span>
                    @elseif($verification->status === 'approved')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700">
                            <i class="fas fa-check-circle text-[9px]"></i> Approved
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-700">
                            <i class="fas fa-times-circle text-[9px]"></i> Rejected
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Seller Name</label>
                        <div class="text-sm font-semibold text-gray-900 mt-0.5">{{ $verification->seller->name }}</div>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Email Address</label>
                        <div class="text-sm font-semibold text-gray-900 mt-0.5">{{ $verification->seller->email }}</div>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Submitted On</label>
                        <div class="text-sm font-semibold text-gray-900 mt-0.5">
                            {{ $verification->submitted_at ? $verification->submitted_at->format('M d, Y h:i A') : $verification->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-50 pt-4">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Business Description</label>
                    <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-xl mt-1 whitespace-pre-line border border-gray-100">
                        {{ $verification->business_description }}
                    </div>
                </div>

                <div class="border-t border-gray-50 pt-4">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Reason For Verification</label>
                    <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-xl mt-1 whitespace-pre-line border border-gray-100">
                        {{ $verification->reason_for_verification }}
                    </div>
                </div>

                <div class="border-t border-gray-50 pt-4">
                    <label class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Supporting Documents</label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                        @php
                            $docs = $verification->documents;
                            if (is_string($docs)) {
                                $docs = json_decode($docs, true);
                            }
                        @endphp

                        @if(is_array($docs) && count($docs) > 0)
                            @foreach($docs as $index => $path)
                                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden flex flex-col shadow-sm hover:shadow transition-shadow group">
                                    <div class="relative bg-gray-50 flex-1 flex items-center justify-center min-h-[150px]">
                                        @php
                                            $extension = pathinfo($path, PATHINFO_EXTENSION);
                                            $isPdf = strtolower($extension) === 'pdf';
                                        @endphp

                                        @if($isPdf)
                                            <div class="flex flex-col items-center justify-center p-4">
                                                <i class="fas fa-file-pdf text-red-500 text-4xl"></i>
                                                <span class="text-xs text-gray-500 mt-2 font-medium">PDF Document</span>
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $path) }}" alt="Document {{ $index + 1 }}" class="w-full h-[150px] object-cover transition-transform duration-300 group-hover:scale-105">
                                        @endif

                                        <a href="{{ asset('storage/' . $path) }}" target="_blank" class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm rounded-lg p-1.5 shadow hover:bg-white transition-colors opacity-0 group-hover:opacity-100" title="Expand Document">
                                            <i class="fas fa-expand text-primary text-xs"></i>
                                        </a>
                                    </div>
                                    <div class="bg-gray-50/50 p-2.5 border-t border-gray-100 text-center">
                                        <span class="text-[10px] text-gray-500 font-semibold">Doc {{ $index + 1 }} ({{ strtoupper($extension) }})</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full">
                                <div class="p-4 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 text-sm flex items-center gap-2">
                                    <i class="fas fa-info-circle"></i> No documents uploaded.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($verification->status === 'pending')
                    <div class="border-t border-gray-50 pt-6 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold rounded-xl transition-colors flex items-center gap-1.5" onclick="toggleModal('rejectModal')">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                        <form action="{{ route('admin.verifications.approve', $verification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                                <i class="fas fa-check-circle"></i> Approve Verification
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-history text-primary"></i> Request History
                </h2>
            </div>
            <div class="p-6">
                <div class="relative border-l-2 border-gray-100 pl-6 space-y-8">
                    <!-- Timeline: Created -->
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0.5 bg-green-500 w-4 h-4 rounded-full flex items-center justify-center ring-4 ring-white">
                            <i class="fas fa-plus text-[8px] text-white"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Request Submitted</h4>
                            <span class="text-[11px] text-gray-400">{{ $verification->created_at->format('M d, Y h:i A') }}</span>
                            <p class="text-xs text-gray-400 mt-1">Verification application submitted by seller.</p>
                        </div>
                    </div>

                    <!-- Timeline: Current Status -->
                    @if($verification->status !== 'pending')
                        <div class="relative">
                            <div class="absolute -left-[31px] top-0.5 {{ $verification->status === 'approved' ? 'bg-green-500' : 'bg-red-500' }} w-4 h-4 rounded-full flex items-center justify-center ring-4 ring-white">
                                <i class="fas fa-{{ $verification->status === 'approved' ? 'check' : 'times' }} text-[8px] text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Request {{ ucfirst($verification->status) }}</h4>
                                <span class="text-[11px] text-gray-400">{{ $verification->updated_at->format('M d, Y h:i A') }}</span>
                                <p class="text-xs text-gray-400 mt-1">
                                    @if($verification->status === 'approved')
                                        Verification request approved by administrator.
                                    @else
                                        Verification request rejected. Reason: <span class="font-medium text-gray-700">{{ $verification->rejection_reason }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="toggleModal('rejectModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <form action="{{ route('admin.verifications.reject', $verification->id) }}" method="POST">
                @csrf
                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4 text-left">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Reject Verification Request</h3>
                        <button type="button" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors" onclick="toggleModal('rejectModal')">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div class="mt-4">
                        <label for="rejection_reason" class="block text-sm font-semibold text-gray-700 mb-1">Reason for Rejection</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50" required></textarea>
                        <div class="text-xs text-gray-400 mt-1">Provide detailed feedback explaining why the request is being rejected.</div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 text-left">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto">
                        Reject Request
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto" onclick="toggleModal('rejectModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            }
        }
    </script>
@endsection