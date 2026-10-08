<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Feedback - Bakala Express</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/styleHome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/logo.css') }}">
    <link rel="stylesheet" href="{{ asset('css/feedback.css') }}">
</head>
<body>
    <!-- Header -->
    <header class="header bg-white shadow-sm py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('home') }}">
                        @include('components.logo')
                    </a>
                </div>
                <div>
                    <a href="{{ route('order.all') }}" class="btn-contact btn-sm me-2">
                        <i class="fas fa-box"></i> My Orders
                    </a>
                    <a href="{{ route('home') }}" class="btn-contact btn-sm">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="feedback-container">
                <div class="feedback-card animate__animated animate__fadeIn">
                    <div class="feedback-header">
                        <h1 class="feedback-title">Share Your Experience</h1>
                        <p class="feedback-subtitle">Order #{{ $order->id }}</p>
                    </div>
                    
                    <div class="feedback-body">
                        <!-- Order summary -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt me-2" style="color: var(--primary-color); font-size: 18px;"></i>
                                    <div>
                                        <h6 class="mb-0">Order Date</h6>
                                        <p class="mb-0">{{ $order->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-store me-2" style="color: var(--primary-color); font-size: 18px;"></i>
                                    <div>
                                        <h6 class="mb-0">Service Provider</h6>
                                        <p class="mb-0">{{ $order->seller->name ?? 'Unknown Seller' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($order->feedback)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Feedback Already Submitted</strong>
                                    <p class="mb-0">You have already provided feedback for this order. Thank you for sharing your experience!</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-4 bg-light rounded">
                                <h5 class="mb-3">Your Feedback</h5>
                                <p class="mb-0">{{ $order->feedback->feedback }}</p>
                                <div class="text-end mt-3">
                                    <small class="text-muted">Submitted on {{ $order->feedback->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="{{ route('order.all') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Orders
                                </a>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <div>Your feedback helps us improve our services and assists other customers in making informed decisions.</div>
                            </div>
                            
                            <form action="{{ route('order.feedback.submit', $order->id) }}" method="POST" class="feedback-form">
                                @csrf
                                <div class="form-group">
                                    <label for="rating" class="form-label">Rating</label>
                                    <select class="form-control mb-3" id="rating" name="rating" required>
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Good</option>
                                        <option value="3">3 - Average</option>
                                        <option value="2">2 - Poor</option>
                                        <option value="1">1 - Very poor</option>
                                    </select>
                                    @error('rating')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    <label for="feedback" class="form-label">Your Feedback</label>
                                    <textarea 
                                        class="form-control" 
                                        id="feedback" 
                                        name="feedback" 
                                        rows="5" 
                                        placeholder="Please share your experience with this service..." 
                                        required
                                    ></textarea>
                                    @error('feedback')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="{{ route('order.all') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} Bakala Express. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
