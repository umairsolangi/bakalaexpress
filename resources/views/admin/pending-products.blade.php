@extends('admin.layouts.app')

@section('title', 'Pending Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pending Products</li>
        </ol>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h5 class="data-card-title">
                <i class="fas fa-box text-primary me-2"></i>Pending Product Approvals
            </h5>
        </div>
        <div class="data-card-body">
            @if($pendingProducts->isEmpty())
                <div class="data-card-empty">
                    <i class="fas fa-box-open"></i>
                    <p>No pending products.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="admin-table w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingProducts as $product)
                                <tr>
                                    <td>#{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ optional($product->seller)->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($product->price, 2) }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <form action="{{ route('admin.approveProduct', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="action-btn approve" title="Approve Product">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.rejectProduct', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="action-btn reject" title="Reject Product">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
