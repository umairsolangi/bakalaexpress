<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Update Profile - Bakala Express</title>
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
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
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
                    <form action="{{ route('logout') }}" method="POST" class="d-inline-block me-2">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
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
            <div class="profile-container">
                <div class="profile-card animate__animated animate__fadeIn">
                    <div class="profile-header">
                        <h1 class="profile-title">Update Your Profile</h1>
                        <p class="profile-subtitle">Keep your account information up to date</p>
                    </div>
                    
                    <div class="profile-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                        @endif

                        <div class="avatar-section">
                            <div class="avatar-container">
                                @if ($profileUpdate && $profileUpdate->profile_image)
                                    <img src="{{ asset('storage/' . $profileUpdate->profile_image) }}" alt="Profile Image" class="avatar-image">
                                @else
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <label for="profile_image_display" class="avatar-upload">
                                    <i class="fas fa-camera"></i>
                                    <input type="file" id="profile_image_display" onchange="displaySelectedImage(this)" accept="image/jpeg,image/png,image/gif">
                                </label>
                            </div>
                            <h2 class="user-name">{{ $user->name }}</h2>
                            <p class="user-email">{{ $user->email }}</p>
                        </div>

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
                            @csrf
                            
                            @if ($errors->has('profile_image'))
                                <div class="alert alert-danger">{{ $errors->first('profile_image') }}</div>
                            @elseif ($errors->has('error'))
                                <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                            @endif
                            
                            <input type="file" id="profile_image" name="profile_image" class="d-none">
                            
                            <div class="form-row">
                                <div class="form-group form-group-half">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group form-group-half">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group form-group-half">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control">
                                    <div class="form-text">Leave blank to keep your current password</div>
                                    @error('password')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group form-group-half">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                                    <div class="form-text">Confirm your new password</div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <a href="{{ route('home') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Home
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
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
    
    <script>
        function displaySelectedImage(input) {
            if (input.files && input.files[0]) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!allowedTypes.includes(input.files[0].type)) {
                    alert('Please select a valid image file (JPG, JPEG, PNG, GIF)');
                    input.value = '';
                    return;
                }

                // Validate file size (2MB)
                if (input.files[0].size > 2 * 1024 * 1024) {
                    alert('File size should be less than 2MB');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Update the visible image
                    const avatarContainer = document.querySelector('.avatar-container');
                    const existingImg = avatarContainer.querySelector('.avatar-image');
                    const placeholder = avatarContainer.querySelector('.avatar-placeholder');
                    
                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else if (placeholder) {
                        placeholder.style.display = 'none'; // Hide placeholder
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Profile Image';
                        newImg.className = 'avatar-image';
                        avatarContainer.insertBefore(newImg, placeholder);
                    }
                    
                    // Update the hidden form input
                    const hiddenInput = document.getElementById('profile_image');
                    
                    // Use DataTransfer to create a proper FileList
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(input.files[0]);
                    hiddenInput.files = dataTransfer.files;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>