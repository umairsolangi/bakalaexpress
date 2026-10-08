# Adding Verification Management to Admin Dashboard

In your admin dashboard sidebar or navigation, add the following link to access the seller verification management section:

```php
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.verifications.index') }}">
        <i class="fas fa-certificate"></i>
        <span>Verification Requests</span>
        @php
            $pendingCount = \App\Models\SellerVerification::where('status', 'pending')->count();
        @endphp
        @if($pendingCount > 0)
            <span class="badge badge-danger badge-counter">{{ $pendingCount }}</span>
        @endif
    </a>
</li>
```

This will add a link to the verification management page with a badge showing the number of pending verification requests.

# Admin Navigation Integration

Find your admin layout file (likely in `resources/views/admin/layouts/app.blade.php` or similar) and add the verification management link to the sidebar navigation.

Alternatively, you can add this link to any of your admin dashboard views where you have navigation links displayed.

# Additional Setup for Admin

To complete the integration with the admin dashboard:

1. Ensure the admin user has proper permissions to manage seller verifications
2. Add notifications for new verification requests
3. Consider adding a verification section to the admin dashboard overview page

This setup will allow administrators to efficiently manage seller verification requests and maintain the quality and trust of your marketplace. 