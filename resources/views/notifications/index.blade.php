<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications</title>
    <style>
        .unread {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Notifications</h1>
    @foreach ($notifications as $notification)
        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
            <span>{{ $notification->data['message'] }}</span>
            <button class="mark-as-read" data-id="{{ $notification->id }}">Mark as Read</button>
        </div>
    @endforeach
</body>
<script>
    document.querySelectorAll('.mark-as-read').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.id; // Retrieve the notification ID from a data attribute
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            }).then(response => {
                if (response.ok) {
                    const notificationItem = this.closest('.notification-item');
                    notificationItem.classList.remove('unread'); 
                    notificationItem.remove(); 
                } else {
                    console.error('Failed to mark as read');
                }
            }).catch(error => console.error(error));
        });
    });
</script>
</html>