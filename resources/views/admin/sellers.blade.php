@extends('admin.layouts.app')

@section('title', 'Manage Sellers')

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8 gap-4">
        <div>
            <p class="text-sm text-gray-400 font-medium mb-1">Management</p>
            <h1 class="text-3xl font-extrabold text-gray-950 font-display tracking-tight">Manage Sellers</h1>
            <p class="text-gray-400 mt-1 text-sm">View, search, and impersonate or manage registered merchant accounts.</p>
        </div>
    </div>

    <!-- Sellers Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Card Header with Search -->
        <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-store text-primary"></i> Registered Sellers
            </h3>
            <div class="w-full sm:w-auto">
                <form class="flex gap-2 w-full sm:w-auto" method="GET" action="{{ route('admin.sellers') }}">
                    <div class="relative flex-1 sm:flex-none">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email..."
                            class="pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary w-full sm:w-64 transition-all bg-gray-50/50 hover:bg-white">
                    </div>
                    <button class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow hover:shadow-md" type="submit">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.sellers') }}" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="p-0">
            @if($sellers->isEmpty())
                <div class="p-16 text-center text-gray-400">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-store-slash text-2xl text-gray-300"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Sellers Found</h3>
                    <p class="text-xs text-gray-400 mt-1">We couldn't find any merchant accounts matching your criteria.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Merchant</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email Address</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Business Name</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Joined Date</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @foreach($sellers as $seller)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-primary">#{{ $seller->id }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-xl bg-primary/10 overflow-hidden flex items-center justify-center text-primary font-bold text-sm">
                                                {{ substr($seller->name, 0, 1) }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-950">{{ $seller->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">{{ $seller->email }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold">{{ $seller->business_name ?? 'N/A' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($seller->accountIsApproved == 1)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700">
                                                <i class="fas fa-check-circle text-[9px]"></i> Approved
                                            </span>
                                        @elseif($seller->is_deleted == 1)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-700">
                                                <i class="fas fa-ban text-[9px]"></i> Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-50 text-yellow-700">
                                                <i class="fas fa-clock text-[9px]"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $seller->created_at->format('M d, Y') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end gap-2">
                                            <form action="{{ route('admin.loginSeller', $seller->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors flex items-center gap-1 shadow-sm" title="Impersonate Seller">
                                                    <i class="fas fa-sign-in-alt"></i> Login As Seller
                                                </button>
                                            </form>

                                            @if($seller->accountIsApproved == 0 && $seller->is_deleted == 0)
                                                <form action="{{ route('admin.approveSeller', $seller->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition-colors" title="Approve Seller">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.rejectSeller', $seller->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Reject Seller">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($sellers, 'hasPages') && $sellers->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">
                        {{ $sellers->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection