@extends('admin.layouts.app')

@section('title', 'Seller Verification Requests')

@section('content')
    <div class="mb-8">
        <p class="text-sm text-gray-400 font-medium mb-1">Approvals</p>
        <h1 class="text-2xl font-extrabold text-gray-950 font-display tracking-tight">Seller Verification Requests</h1>
        <p class="text-gray-400 mt-1 text-sm">Review business profiles and uploaded verification documents submitted by sellers.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Header & Filter -->
        <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-check-circle text-primary"></i> Verification Requests
            </h3>

            <div class="flex gap-1.5">
                <a href="{{ route('admin.verifications.index', ['status' => 'pending']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'pending' ? 'bg-primary text-white shadow-sm' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-100' }}">
                    Pending
                </a>
                <a href="{{ route('admin.verifications.index', ['status' => 'approved']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'approved' ? 'bg-primary text-white shadow-sm' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-100' }}">
                    Approved
                </a>
                <a href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'rejected' ? 'bg-primary text-white shadow-sm' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-100' }}">
                    Rejected
                </a>
                <a href="{{ route('admin.verifications.index', ['status' => 'all']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'all' || $status === '' ? 'bg-primary text-white shadow-sm' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-100' }}">
                    All
                </a>
            </div>
        </div>

        <div class="p-0">
            @if($verificationRequests->isEmpty())
                <div class="p-14 text-center text-gray-400">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium">No verification requests found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seller Name</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Submitted</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Documents</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @foreach($verificationRequests as $request)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-primary">#{{ $request->id }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $request->seller->name }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">{{ $request->seller->email }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->submitted_at ? $request->submitted_at->format('M d, Y') : $request->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $docs = $request->documents;
                                            if (is_string($docs)) {
                                                $docs = json_decode($docs, true);
                                            }
                                            $docCount = is_array($docs) ? count($docs) : 0;
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $docCount }} Document(s)
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($request->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-50 text-yellow-700">
                                                <i class="fas fa-clock text-[9px]"></i> Pending
                                            </span>
                                        @elseif($request->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700">
                                                <i class="fas fa-check-circle text-[9px]"></i> Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-700">
                                                <i class="fas fa-times-circle text-[9px]"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.verifications.show', $request) }}" class="px-3 py-1.5 text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors flex items-center gap-1">
                                                <i class="fas fa-eye"></i> View
                                            </a>

                                            @if($request->status === 'pending')
                                                <form action="{{ route('admin.verifications.approve', $request) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition-colors flex items-center gap-1">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>

                                                <button type="button" class="px-3 py-1.5 text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-700 rounded-lg transition-colors flex items-center gap-1" onclick="toggleModal('rejectModal{{ $request->id }}')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>

                                                <!-- Reject Modal -->
                                                <div id="rejectModal{{ $request->id }}" class="fixed inset-0 z-50 overflow-y-auto hidden text-left" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="toggleModal('rejectModal{{ $request->id }}')"></div>
                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                                        <div class="inline-block align-middle bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                                                            <form action="{{ route('admin.verifications.reject', $request) }}" method="POST">
                                                                @csrf
                                                                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                                                        <h3 class="text-lg font-bold text-gray-900">Reject Verification Request</h3>
                                                                        <button type="button" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors" onclick="toggleModal('rejectModal{{ $request->id }}')">
                                                                            <i class="fas fa-times text-sm"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div class="mt-4">
                                                                        <label for="rejection_reason" class="block text-sm font-semibold text-gray-700 mb-1">Reason for Rejection</label>
                                                                        <textarea id="rejection_reason" name="rejection_reason" rows="4" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50" required></textarea>
                                                                        <p class="text-xs text-gray-400 mt-1">This feedback message will be shared with the seller.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bg-gray-50 px-6 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                                                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto">
                                                                        Reject Request
                                                                    </button>
                                                                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto" onclick="toggleModal('rejectModal{{ $request->id }}')">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($verificationRequests, 'hasPages') && $verificationRequests->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">
                        {{ $verificationRequests->links() }}
                    </div>
                @endif
            @endif
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