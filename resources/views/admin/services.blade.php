@extends('admin.layouts.app')

@section('title', 'Manage Services')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- <h1 class="h3 mb-0 text-gray-800">Manage Services</h1> --}}
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Services</li>
        </ol>
    </div>
    
    <!-- Services Card -->
    <div class="data-card">
        <div class="data-card-header">
            <h5 class="data-card-title">
                <i class="fas fa-box text-primary me-2"></i>All Services
            </h5>
            <div class="d-flex gap-2">
                <form class="input-group" style="width: 250px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Search services..." name="search">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="data-card-body">
            @if($services->isEmpty())
                <div class="data-card-empty">
                    <i class="fas fa-box-open"></i>
                    <p>No services available at the moment.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="admin-table w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service Name</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                                <tr>
                                    <td class="table-id">#{{ $service->id }}</td>
                                    <td>{{ $service->service_name }}</td>
                                    <td>{{ $service->seller ? $service->seller->name : 'N/A' }}</td>
                                    <td>{{ number_format($service->service_price, 2) }} PKR</td>
                                    <td>
                                        @if($service->is_approved == 1)
                                            <span class="status-badge status-completed">
                                                <i class="fas fa-check-circle"></i> Approved
                                            </span>
                                        @else
                                            <span class="status-badge status-pending">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $service->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($service->is_approved == 0)
                                                <form action="{{ route('admin.approveService', $service->id) }}" method="POST" class="approval-form approve-form" data-entity-type="Service">
                                                    @csrf
                                                    <button type="submit" class="action-btn approve" data-bs-toggle="tooltip" title="Approve Service">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.rejectService', $service->id) }}" method="POST" class="approval-form reject-form" data-entity-type="Service">
                                                    @csrf
                                                    <button type="submit" class="action-btn reject" data-bs-toggle="tooltip" title="Reject Service">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="action-btn view" data-bs-toggle="modal" data-bs-target="#serviceModal{{ $service->id }}" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Service Detail Modal -->
                                <div class="modal fade" id="serviceModal{{ $service->id }}" tabindex="-1" aria-labelledby="serviceModalLabel{{ $service->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="serviceModalLabel{{ $service->id }}">Service Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6 class="fw-bold">{{ $service->service_name }}</h6>
                                                <div class="mb-3">
                                                    <span class="badge bg-primary">{{ number_format($service->service_price, 2) }} PKR</span>
                                                    <span class="badge {{ $service->is_approved ? 'bg-success' : 'bg-warning' }}">
                                                        {{ $service->is_approved ? 'Approved' : 'Pending' }}
                                                    </span>
                                                </div>
                                                
                                                <h6 class="fw-bold">Description</h6>
                                                <p>{{ $service->service_description }}</p>
                                                
                                                <h6 class="fw-bold">Seller Information</h6>
                                                <p>{{ $service->seller ? $service->seller->name : 'N/A' }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                @if($service->is_approved == 0)
                                                    <form action="{{ route('admin.approveService', $service->id) }}" method="POST" class="me-2 approval-form approve-form" data-entity-type="Service">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-check me-1"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.rejectService', $service->id) }}" method="POST" class="approval-form reject-form" data-entity-type="Service">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-times me-1"></i> Reject
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="btn btn-secondary ms-auto" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if(method_exists($services, 'hasPages') && $services->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $services->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection