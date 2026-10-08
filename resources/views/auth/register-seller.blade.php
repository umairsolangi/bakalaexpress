@extends('layouts.app')

@section('title', 'Become a Seller - Bakala Express')

@section('styles')
    <style>
        .seller-input-group {
            position: relative;
        }

        .seller-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 15px;
            pointer-events: none;
            z-index: 2;
        }

        .seller-textarea-icon {
            position: absolute;
            left: 14px;
            top: 14px;
            color: #9CA3AF;
            font-size: 15px;
            pointer-events: none;
            z-index: 2;
        }

        .seller-form-control {
            height: 48px;
            width: 100%;
            border: 1px solid #D1D5DB;
            border-radius: 0.5rem;
            padding: 10px 14px 10px 42px;
            font-size: 14px;
            color: #1F2937;
            background-color: #FFFFFF;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;
        }

        .seller-form-control:focus {
            outline: none;
            border-color: #00A651;
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.15);
        }

        .seller-form-textarea {
            width: 100%;
            border: 1px solid #D1D5DB;
            border-radius: 0.5rem;
            padding: 12px 14px 12px 42px;
            font-size: 14px;
            color: #1F2937;
            background-color: #FFFFFF;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;
            min-height: 85px;
            resize: vertical;
        }

        .seller-form-textarea:focus {
            outline: none;
            border-color: #00A651;
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.15);
        }

        .near-area-tile {
            user-select: none;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .near-area-tile:hover {
            border-color: #00A651;
            background-color: #F0FDF4;
        }

        .near-area-tile.is-checked {
            border-color: #00A651;
            background-color: #F0FDF4;
            color: #008c44;
            font-weight: 500;
        }

        .upload-dropzone {
            border: 2px dashed #D1D5DB;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .upload-dropzone:hover {
            border-color: #00A651;
            background-color: #F9FAFB;
        }

        @media (max-width: 640px) {
            .near-area-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 380px) {
            .near-area-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="flex items-center justify-center py-6 sm:py-10 px-3 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-6 sm:space-y-8">
            <div class="text-center px-2">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
                    Join our Partner Network
                </h2>
                <p class="mt-1.5 sm:mt-2 text-sm sm:text-base text-gray-600">
                    Start selling to your neighborhood today
                </p>
            </div>

            <div class="bg-white py-6 px-4 sm:py-8 sm:px-8 md:px-10 shadow-xl rounded-2xl border border-gray-100 relative overflow-hidden w-full">
                <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-primary to-primary-dark"></div>

                <form class="space-y-6" action="{{ route('register.seller') }}" method="POST" enctype="multipart/form-data"
                    id="sellerRegistrationForm">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-xl bg-red-50 p-4 mb-4 border border-red-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-semibold text-red-800">
                                        There were errors with your submission
                                    </h3>
                                    <div class="mt-1.5 text-xs sm:text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4 sm:space-y-5">
                            <!-- Full Name -->
                            <div>
                                <label for="name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <div class="seller-input-group">
                                    <i class="fas fa-user seller-input-icon"></i>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}" 
                                           required
                                           class="seller-form-control"
                                           placeholder="Business Owner Name">
                                </div>
                                <p id="nameError" class="mt-1 text-xs text-red-600 hidden"></p>
                            </div>

                            <!-- Email Address -->
                            <div>
                                <label for="email" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <div class="seller-input-group">
                                    <i class="fas fa-envelope seller-input-icon"></i>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email') }}" 
                                           required
                                           class="seller-form-control"
                                           placeholder="you@example.com">
                                </div>
                                <p id="emailError" class="mt-1 text-xs text-red-600 hidden"></p>
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <div class="seller-input-group">
                                    <i class="fas fa-lock seller-input-icon"></i>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           required
                                           minlength="8"
                                           class="seller-form-control"
                                           placeholder="Minimum 8 characters">
                                </div>
                                <p id="passwordError" class="mt-1 text-xs text-red-600 hidden"></p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <div class="seller-input-group">
                                    <i class="fas fa-lock seller-input-icon"></i>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation" 
                                           required
                                           minlength="8"
                                           class="seller-form-control"
                                           placeholder="Re-enter password">
                                </div>
                                <p id="confirmPasswordError" class="mt-1 text-xs text-red-600 hidden"></p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4 sm:space-y-5">
                            <!-- Shop/Profile Image -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                    Shop / Profile Image <span class="text-red-500">*</span>
                                </label>
                                <div class="upload-dropzone p-4 text-center"
                                     onclick="document.getElementById('profile_image').click()">
                                    <input id="profile_image" 
                                           name="profile_image" 
                                           type="file" 
                                           class="hidden"
                                           accept="image/*" 
                                           onchange="previewImage(this)">
                                    
                                    <div id="uploadPrompt" class="space-y-1">
                                        <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center mx-auto text-primary">
                                            <i class="fas fa-camera text-base"></i>
                                        </div>
                                        <p class="text-xs sm:text-sm font-medium text-gray-700 mt-2">
                                            <span class="text-primary hover:underline">Choose file</span> or take photo
                                        </p>
                                        <p class="text-xs text-gray-400">PNG, JPG, WebP (auto-compressed)</p>
                                    </div>

                                    <div id="imagePreviewContainer" class="hidden mt-1 flex items-center justify-center gap-3">
                                        <img id="imagePreview" src="#" alt="Preview" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                                        <div class="text-left text-xs">
                                            <p id="fileName" class="font-medium text-gray-800 truncate max-w-[180px]"></p>
                                            <p class="text-primary text-[11px]">Click to change image</p>
                                        </div>
                                    </div>
                                </div>
                                <p id="imageError" class="mt-1 text-xs text-red-600 hidden"></p>
                            </div>

                            <!-- Hidden City -->
                            <input type="hidden" name="city" value="Karachi">

                            <!-- Area -->
                            <div>
                                <label for="area" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                    Area <span class="text-red-500">*</span>
                                </label>
                                <div class="seller-input-group">
                                    <i class="fas fa-map-marker-alt seller-input-icon"></i>
                                    <select name="area" id="area" required class="seller-form-control appearance-none">
                                        <option value="Baldia Town" selected>Baldia Town (Karachi)</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[11px] text-gray-500">Currently serving Baldia Town neighborhood.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <!-- Sector -->
                                <div>
                                    <label for="sector" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                        Sector <span class="text-red-500">*</span>
                                    </label>
                                    <div class="seller-input-group">
                                        <i class="fas fa-layer-group seller-input-icon"></i>
                                        <select name="sector" id="sector" required class="seller-form-control appearance-none">
                                            <option value="" disabled {{ old('sector') ? '' : 'selected' }}>Select Sector</option>
                                            <option value="4A" {{ old('sector') === '4A' ? 'selected' : '' }}>Sector 4A</option>
                                            <option value="4B" {{ old('sector') === '4B' ? 'selected' : '' }}>Sector 4B</option>
                                            <option value="4C" {{ old('sector') === '4C' ? 'selected' : '' }}>Sector 4C</option>
                                        </select>
                                    </div>
                                    <p id="sectorError" class="mt-1 text-xs text-red-600 hidden"></p>
                                </div>

                                <!-- Shop Category -->
                                <div>
                                    <label for="catalog_category_id" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <div class="seller-input-group">
                                        <i class="fas fa-store seller-input-icon"></i>
                                        <select name="catalog_category_id" id="catalog_category_id" required class="seller-form-control appearance-none">
                                            <option value="" disabled {{ old('catalog_category_id') ? '' : 'selected' }}>Select Category</option>
                                            @foreach($catalogCategories as $catalogCategory)
                                                <option value="{{ $catalogCategory->id }}" {{ (string) old('catalog_category_id') === (string) $catalogCategory->id ? 'selected' : '' }}>
                                                    {{ $catalogCategory->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Near Areas Selection -->
                    <div class="pt-2">
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">
                            Near Landmarks / Areas <span class="text-xs font-normal text-gray-500">(Select one or more)</span>
                        </label>
                        @php
                            $nearAreaOptions = [
                                'ABC Swimming Pool',
                                'Tajli Noor Masjid',
                                'Lahori Hotel',
                                'Ali Chowk',
                                'Family Park',
                                'Carido Hospital',
                                'Rubi Mor',
                            ];

                            $oldNear = old('near_areas', []);
                            if (!is_array($oldNear)) {
                                $oldNear = [];
                            }
                        @endphp

                        <div class="near-area-grid grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-2.5 mt-2">
                            @foreach($nearAreaOptions as $opt)
                                @php $isChecked = in_array($opt, $oldNear, true); @endphp
                                <label class="near-area-tile flex items-center gap-2 sm:gap-2.5 p-2 sm:p-2.5 border border-gray-200 rounded-lg text-xs sm:text-sm {{ $isChecked ? 'is-checked' : '' }}">
                                    <input
                                        type="checkbox"
                                        name="near_areas[]"
                                        value="{{ $opt }}"
                                        class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary cursor-pointer accent-[#00A651]"
                                        {{ $isChecked ? 'checked' : '' }}
                                        onchange="toggleNearAreaTile(this)"
                                    >
                                    <span class="truncate">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1.5">Helps neighborhood customers find your shop faster.</p>
                    </div>

                    <!-- Shop Full Address -->
                    <div>
                        <label for="full_address" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">
                            Complete Shop Address <span class="text-red-500">*</span>
                        </label>
                        <div class="seller-input-group">
                            <i class="fas fa-location-dot seller-textarea-icon"></i>
                            <textarea
                                name="full_address"
                                id="full_address"
                                rows="2"
                                required
                                class="seller-form-textarea"
                                placeholder="e.g. Shop #5, Sector 4B, near Lahori Hotel, Baldia Town"
                            >{{ old('full_address') }}</textarea>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start gap-2.5 pt-1">
                        <input id="terms" 
                               name="terms" 
                               type="checkbox" 
                               required
                               class="mt-1 h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary cursor-pointer accent-[#00A651]">
                        <label for="terms" class="text-xs sm:text-sm text-gray-700 cursor-pointer">
                            I agree to the <a href="#" class="text-primary font-medium hover:underline">Terms and Conditions</a> of Bakala Express Partner Network.
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                id="sellerSubmitBtn"
                                class="w-full flex items-center justify-center py-3.5 px-4 text-sm sm:text-base font-semibold rounded-xl text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-lg hover:shadow-primary/40 transition-all duration-200 cursor-pointer">
                            <i class="fas fa-store mr-2"></i> Register My Shop
                        </button>
                    </div>

                    <!-- Footer Link -->
                    <div class="text-center pt-2">
                        <p class="text-xs sm:text-sm text-gray-600">
                            Already have a seller account?
                            <a href="{{ route('login.seller') }}" class="font-semibold text-primary hover:text-primary-dark hover:underline transition-colors ml-1">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('fileName').textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        document.getElementById('imagePreview').src = img.src;
                        document.getElementById('imagePreviewContainer').classList.remove('hidden');
                        document.getElementById('uploadPrompt').classList.add('hidden');

                        // Client-side compression if image is large
                        const maxDim = 1600;
                        let w = img.width;
                        let h = img.height;
                        if (w > maxDim || h > maxDim || file.size > 1.5 * 1024 * 1024) {
                            if (w > maxDim || h > maxDim) {
                                if (w >= h) {
                                    h = Math.round((h / w) * maxDim);
                                    w = maxDim;
                                } else {
                                    w = Math.round((w / h) * maxDim);
                                    h = maxDim;
                                }
                            }
                            const canvas = document.createElement('canvas');
                            canvas.width = w;
                            canvas.height = h;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, w, h);
                            canvas.toBlob(function(blob) {
                                if (blob && window.DataTransfer) {
                                    try {
                                        const dt = new DataTransfer();
                                        const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, ".jpg"), {
                                            type: 'image/jpeg',
                                            lastModified: Date.now()
                                        });
                                        dt.items.add(compressedFile);
                                        input.files = dt.files;
                                    } catch (err) {
                                        // Ignore fallback to server compression
                                    }
                                }
                            }, 'image/jpeg', 0.82);
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        function toggleNearAreaTile(checkbox) {
            const tile = checkbox.closest('.near-area-tile');
            if (checkbox.checked) {
                tile.classList.add('is-checked');
            } else {
                tile.classList.remove('is-checked');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('sellerRegistrationForm');

            form.addEventListener('submit', function (event) {
                let isValid = true;

                const password = document.getElementById('password');
                const confirm = document.getElementById('password_confirmation');
                const confirmError = document.getElementById('confirmPasswordError');

                if (password.value !== confirm.value) {
                    event.preventDefault();
                    confirmError.textContent = "Passwords do not match";
                    confirmError.classList.remove('hidden');
                    isValid = false;
                } else {
                    confirmError.classList.add('hidden');
                }

                if (isValid) {
                    const btn = document.getElementById('sellerSubmitBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Processing Registration...';
                }
            });
        });
    </script>
@endsection