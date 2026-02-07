<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .ticket-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .club-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            object-fit: cover;
            border: 3px solid white;
            background: white;
        }
        
        .ticket-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .ticket-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .ticket-body {
            padding: 40px 30px;
        }
        
        .event-info {
            margin-bottom: 30px;
        }
        
        .event-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #667eea;
            min-width: 120px;
            font-size: 14px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
            font-size: 14px;
        }
        
        .qr-section {
            text-align: center;
            padding: 30px 0;
            background: linear-gradient(to bottom, #f9f9f9, white);
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .qr-title {
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .qr-code {
            width: 250px;
            height: 250px;
            margin: 0 auto;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .ticket-code {
            text-align: center;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color: #666;
            letter-spacing: 2px;
        }
        
        .ticket-footer {
            background: #f9f9f9;
            padding: 20px 30px;
            border-top: 2px dashed #ddd;
        }
        
        .instructions {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .terms {
            font-size: 10px;
            color: #999;
            text-align: center;
            line-height: 1.5;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #667eea, transparent);
            margin: 20px 0;
        }
        
        .attendee-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .attendee-label {
            font-size: 12px;
            color: #856404;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .attendee-name {
            font-size: 18px;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="ticket-header">
            @if($clubLogo && file_exists($clubLogo))
                <img src="{{ $clubLogo }}" alt="Club Logo" class="club-logo">
            @else
                <div class="club-logo" style="display: flex; align-items: center; justify-content: center; font-size: 32px;">
                    🎓
                </div>
            @endif
            <div class="ticket-title">Event Ticket</div>
            <div class="ticket-subtitle">{{ $ticket->club_name ?? 'Cluversity' }}</div>
        </div>
        
        <!-- Body -->
        <div class="ticket-body">
            <!-- Event Title -->
            <div class="event-title">{{ $ticket->event_title }}</div>
            
            <!-- Event Information -->
            <div class="event-info">
                <div class="info-row">
                    <span class="info-label">📅 Date:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($ticket->event_date)->format('l, F j, Y - g:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📍 Location:</span>
                    <span class="info-value">{{ $ticket->event_location }}</span>
                </div>
            </div>
            
            <!-- Attendee Info -->
            <div class="attendee-box">
                <div class="attendee-label">TICKET HOLDER</div>
                <div class="attendee-name">{{ $ticket->first_name }} {{ $ticket->last_name }}</div>
            </div>
            
            <div class="divider"></div>
            
            <!-- QR Code Section - NOW SUPPORTS SVG! -->
            <div class="qr-section">
                <div class="qr-title">🎫 Scan this QR Code at entrance</div>
                <div class="qr-code">
                    <!-- SVG QR Code renders perfectly in PDFs! -->
                    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Code" style="width: 100%; height: 100%;">
                </div>
                <div class="ticket-code">{{ $ticketCode }}</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="ticket-footer">
            <div class="instructions">
                <strong>Instructions:</strong> Present this ticket at the event entrance for scanning.
            </div>
            <div class="terms">
                This ticket is non-transferable and valid only for the person named above.<br>
                Please arrive 15 minutes before the event starts. Keep this ticket safe.<br>
                For any questions, please contact the event organizer.
            </div>
        </div>
    </div>
</body>
</html>
