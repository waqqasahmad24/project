<!DOCTYPE html>
<html>
<head>
    <title>Booking Status Update</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #4a90e2;">Booking Update</h2>
        <p>Hello,</p>
        <p>Your booking status for <strong>{{ $booking->provider->name }}</strong> has been updated.</p>
        
        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Date:</strong> {{ $booking->date }}</p>
            <p><strong>Time Slot:</strong> {{ $booking->time_slot }}</p>
            <p><strong>Status:</strong> <span style="text-transform: uppercase; font-weight: bold; color: {{ $action === 'rejected' ? '#e74c3c' : ($action === 'approved' ? '#2ecc71' : '#f39c12') }};">{{ $action }}</span></p>
        </div>

        <p>If you have any questions, please contact the service provider directly.</p>
        
        <p>Thank you,<br>Booking App Team</p>
    </div>
</body>
</html>
