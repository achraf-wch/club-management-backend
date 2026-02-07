<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
        }
        .content {
            padding: 20px 0;
        }
        .event-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .event-details p {
            margin: 10px 0;
        }
        .event-details strong {
            color: #4CAF50;
        }
        .attachment-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .attachment-notice h2 {
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .attachment-notice p {
            margin: 10px 0;
            font-size: 16px;
        }
        .pdf-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instructions h3 {
            margin-top: 0;
            color: #1976D2;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #ddd;
            color: #777;
            font-size: 12px;
        }
        .tip-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .tip-box strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎟️ Your Event Ticket</h1>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $memberName }}</strong>,</p>
            <p>Great news! You have been registered for the following event:</p>

            <div class="event-details">
                <p><strong>📅 Event:</strong> {{ $eventTitle }}</p>
                <p><strong>🗓️ Date:</strong> {{ \Carbon\Carbon::parse($eventDate)->format('l, F j, Y - g:i A') }}</p>
                <p><strong>📍 Location:</strong> {{ $eventLocation }}</p>
                @if(!empty($eventDescription))
                    <p><strong>ℹ️ Description:</strong> {{ $eventDescription }}</p>
                @endif
            </div>

            <div class="attachment-notice">
                <div class="pdf-icon">📄</div>
                <h2>Your Ticket is Attached!</h2>
                <p style="font-size: 18px; margin: 15px 0;">
                    <strong>Check your email attachments</strong>
                </p>
                <p style="font-size: 14px; opacity: 0.9;">
                    Download the PDF ticket and save it on your device
                </p>
            </div>

            <div class="instructions">
                <h3>📱 How to Use Your Ticket:</h3>
                <ol>
                    <li>Download the attached PDF file (<strong>event-ticket.pdf</strong>)</li>
                    <li>Save it to your phone or print it out</li>
                    <li>Present the QR code at the event entrance</li>
                    <li>The staff will scan your QR code to verify your ticket</li>
                </ol>
            </div>

            <div class="tip-box">
                <p style="margin: 0;">
                    <strong>💡 Pro Tip:</strong> Save the PDF to your phone's files or photos for easy access at the event entrance!
                </p>
            </div>

            <p style="text-align: center; color: #666; margin-top: 30px;">
                Looking forward to seeing you at the event! 🎉
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} Cluversity. All rights reserved.</p>
        </div>
    </div>
</body>
</html>