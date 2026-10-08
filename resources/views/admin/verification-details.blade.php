@extends('admin.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/adminDashboard.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="fas fa-user-check mr-2"></i>Verification Request Details
    </h1>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="verification-form-container">
        <div class="seller-info-container">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-group">
                        <div class="info-label">Name</div>
                        <div class="info-value">{{ $verification->seller->name }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $verification->seller->email }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $verification->phone }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Business Name</div>
                        <div class="info-value">{{ $verification->business_name }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $verification->address }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Tax ID</div>
                        <div class="info-value">{{ $verification->tax_id }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Joined</div>
                        <div class="info-value">{{ $verification->seller->created_at->format('F j, Y') }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Verification Status</div>
                        <div class="info-value">
                            @if($verification->status == 'pending')
                                <span class="status-badge status-pending">Pending Review</span>
                            @elseif($verification->status == 'approved')
                                <span class="status-badge status-completed">Approved</span>
                            @elseif($verification->status == 'rejected')
                                <span class="status-badge status-cancelled">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-group mt-4">
                <div class="info-label">Business Description</div>
                <div class="info-value">{{ $verification->description }}</div>
            </div>
        </div>

        <hr>
        
        <h5 class="mb-4"><i class="fas fa-file-alt mr-2"></i>Verification Documents</h5>
        
        <div class="row mb-4">
            @if($verification->id_document)
            <div class="col-md-6 mb-3">
                <div class="document-preview">
                    <div class="info-label mb-2">ID Document</div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="document-title">ID Document</div>
                        <a href="{{ asset('storage/' . $verification->id_document) }}" class="btn btn-sm btn-primary mt-2" target="_blank">
                            <i class="fas fa-eye mr-1"></i> View Document
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            @if($verification->business_document)
            <div class="col-md-6 mb-3">
                <div class="document-preview">
                    <div class="info-label mb-2">Business Document</div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="document-title">Business License/Registration</div>
                        <a href="{{ asset('storage/' . $verification->business_document) }}" class="btn btn-sm btn-primary mt-2" target="_blank">
                            <i class="fas fa-eye mr-1"></i> View Document
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            @if($verification->tax_document)
            <div class="col-md-6 mb-3">
                <div class="document-preview">
                    <div class="info-label mb-2">Tax Document</div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="document-title">Tax Registration</div>
                        <a href="{{ asset('storage/' . $verification->tax_document) }}" class="btn btn-sm btn-primary mt-2" target="_blank">
                            <i class="fas fa-eye mr-1"></i> View Document
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            @if($verification->additional_document)
            <div class="col-md-6 mb-3">
                <div class="document-preview">
                    <div class="info-label mb-2">Additional Document</div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="document-title">Additional Supporting Document</div>
                        <a href="{{ asset('storage/' . $verification->additional_document) }}" class="btn btn-sm btn-primary mt-2" target="_blank">
                            <i class="fas fa-eye mr-1"></i> View Document
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        @if($verification->status == 'pending')
        <form action="{{ route('admin.verifications.update', $verification->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="action-buttons">
                <button type="submit" name="status" value="approved" class="btn btn-success">
                    <i class="fas fa-check mr-1"></i> Approve Verification
                </button>
                <button type="button" id="rejectBtn" class="btn btn-danger">
                    <i class="fas fa-times mr-1"></i> Reject Verification
                </button>
            </div>
            
            <div class="rejection-reason-container" id="rejectionContainer">
                <div class="form-group mt-3">
                    <label for="rejection_reason">Rejection Reason</label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Please provide a reason for rejection"></textarea>
                </div>
                <button type="submit" name="status" value="rejected" class="btn btn-danger">
                    <i class="fas fa-paper-plane mr-1"></i> Submit Rejection
                </button>
            </div>
        </form>
        @elseif($verification->status == 'rejected')
        <div class="mt-4">
            <div class="info-label">Rejection Reason</div>
            <div class="alert alert-danger">
                {{ $verification->rejection_reason }}
            </div>
            
            <form action="{{ route('admin.verifications.update', $verification->id) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" name="status" value="approved" class="btn btn-success">
                    <i class="fas fa-sync-alt mr-1"></i> Reconsider and Approve
                </button>
            </form>
        </div>
        @elseif($verification->status == 'approved')
        <div class="alert alert-success mt-4">
            <i class="fas fa-check-circle mr-2"></i> This seller has been verified and can now offer services on the platform.
        </div>
        
        <form action="{{ route('admin.verifications.update', $verification->id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" name="status" value="rejected" class="btn btn-warning">
                <i class="fas fa-ban mr-1"></i> Revoke Verification
            </button>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle rejection reason container
        const rejectBtn = document.getElementById('rejectBtn');
        const rejectionContainer = document.getElementById('rejectionContainer');
        
        if (rejectBtn && rejectionContainer) {
            rejectBtn.addEventListener('click', function() {
                rejectionContainer.classList.toggle('active');
            });
        }
    });
</script>
@endsection 