@extends('admin.layouts.app')

@section('title', 'Pending Riders')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-2">
            <div>
                <p class="text-sm text-gray-400 font-medium mb-1">Approvals</p>
                <h1 class="text-3xl font-extrabold text-gray-950 font-display tracking-tight">Pending Riders</h1>
                <p class="text-gray-400 text-sm mt-1">Review identity credentials and vehicle registrations to authorize new courier accounts.</p>
            </div>
        </div>

        <!-- Riders Grid -->
        <div class="grid grid-cols-1 gap-6">
            @forelse($pendingRiders as $rider)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200">
                    <div class="p-6 sm:p-8">
                        <!-- Rider Info Header -->
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 pb-6 border-b border-gray-50">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 overflow-hidden border border-gray-100 flex-shrink-0 flex items-center justify-center">
                                    @if($rider->profile_image)
                                        <img src="{{ asset('storage/' . $rider->profile_image) }}" alt="{{ $rider->name }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-primary/10 text-primary">
                                            <i class="fas fa-motorcycle text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $rider->name }}</h3>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-gray-500">
                                        <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-gray-400 text-xs"></i> {{ $rider->email }}</span>
                                        <span class="flex items-center gap-1.5"><i class="fas fa-phone text-gray-400 text-xs"></i> {{ $rider->phone }}</span>
                                        <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-gray-400 text-xs"></i> {{ $rider->address }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.rider.approve', $rider->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-all shadow hover:shadow-md flex items-center gap-1.5">
                                        <i class="fas fa-check"></i> Approve Rider
                                    </button>
                                </form>
                                <form action="{{ route('admin.rider.reject', $rider->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to reject this rider request?');">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold rounded-xl transition-colors flex items-center gap-1.5 border border-red-200">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Credentials & Documents Section -->
                        <div class="pt-6">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-4">Verification Files</h4>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <!-- Vehicle & ID Information Column -->
                                <div class="md:col-span-1 space-y-4 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Vehicle Type</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $rider->vehicle_type }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">License Number / Plate</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $rider->vehicle_number }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">CNIC Number</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $rider->cnic_number }}</div>
                                    </div>
                                </div>

                                <!-- Documents Thumbnails Grid -->
                                <div class="md:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <!-- CNIC Front -->
                                    <div class="group relative aspect-video bg-gray-50 rounded-xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:shadow transition-shadow"
                                        onclick="window.open('{{ asset('storage/' . $rider->cnic_front) }}', '_blank')">
                                        <img src="{{ asset('storage/' . $rider->cnic_front) }}"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="CNIC Front">
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2.5 flex items-end justify-between">
                                            <span class="text-white text-[10px] font-bold">CNIC Front</span>
                                            <i class="fas fa-expand text-white text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>

                                    <!-- CNIC Back -->
                                    <div class="group relative aspect-video bg-gray-50 rounded-xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:shadow transition-shadow"
                                        onclick="window.open('{{ asset('storage/' . $rider->cnic_back) }}', '_blank')">
                                        <img src="{{ asset('storage/' . $rider->cnic_back) }}"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="CNIC Back">
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2.5 flex items-end justify-between">
                                            <span class="text-white text-[10px] font-bold">CNIC Back</span>
                                            <i class="fas fa-expand text-white text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>

                                    <!-- License Image -->
                                    <div class="group relative aspect-video bg-gray-50 rounded-xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:shadow transition-shadow"
                                        onclick="window.open('{{ asset('storage/' . $rider->license_image) }}', '_blank')">
                                        <img src="{{ asset('storage/' . $rider->license_image) }}"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="License">
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2.5 flex items-end justify-between">
                                            <span class="text-white text-[10px] font-bold">Driver License</span>
                                            <i class="fas fa-expand text-white text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>

                                    <!-- Vehicle Image -->
                                    <div class="group relative aspect-video bg-gray-50 rounded-xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:shadow transition-shadow"
                                        onclick="window.open('{{ asset('storage/' . $rider->vehicle_image) }}', '_blank')">
                                        <img src="{{ asset('storage/' . $rider->vehicle_image) }}"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="Vehicle">
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2.5 flex items-end justify-between">
                                            <span class="text-white text-[10px] font-bold">Vehicle Photo</span>
                                            <i class="fas fa-expand text-white text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>

                                    <!-- Reg Book (Optional) -->
                                    @if($rider->registration_book)
                                        <div class="group relative aspect-video bg-gray-50 rounded-xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:shadow transition-shadow"
                                            onclick="window.open('{{ asset('storage/' . $rider->registration_book) }}', '_blank')">
                                            <img src="{{ asset('storage/' . $rider->registration_book) }}"
                                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="Reg Book">
                                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2.5 flex items-end justify-between">
                                                <span class="text-white text-[10px] font-bold">Registration Book</span>
                                                <i class="fas fa-expand text-white text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <i class="fas fa-motorcycle text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Pending Courier Applications</h3>
                    <p class="text-xs text-gray-400 mt-1">There are currently no courier verification applications pending review.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection