<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff; }
        .header { background: #4f46e5; color: #ffffff; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; padding: 12px 24px; background: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; }
        .order-info { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .message-box { border-left: 4px solid #4f46e5; padding-left: 20px; margin: 20px 0; font-style: italic; color: #1e293b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Support Reply</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $order->user->name ?? 'Customer' }}</strong>,</p>
            <p>You have received a new message from our support team regarding your order <strong>#{{ $order->order_serial_no }}</strong>.</p>
            
            <div class="order-info">
                <strong>Order Details:</strong><br>
                Status: {{ $order->status_name }}<br>
                Date: {{ $order->created_at->format('M d, Y') }}
            </div>

            <p><strong>Support Message:</strong></p>
            <div class="message-box">
                {!! nl2br(e($replyMessage)) !!}
            </div>

            <p>If you have any further questions, please feel free to reply to this email or visit your order details page.</p>
            
            <a href="{{ env('FRONTEND_URL') }}/profile/order/desc/{{ $order->id }}" class="btn">View Order Details</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
