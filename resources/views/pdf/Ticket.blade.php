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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f1f5f9;
            padding: 30px;
        }
        
        .ticket-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .ticket-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.05) 100%);
            pointer-events: none;
        }
        
        .club-branding {
            position: relative;
            z-index: 1;
            margin-bottom: 25px;
        }
        
        .club-logo {
            width: 90px;
            height: 90px;
            border-radius: 16px;
            margin: 0 auto 15px;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.2);
            background: white;
            display: block;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .club-name {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .ticket-badge {
            position: relative;
            z-index: 1;
            display: inline-block;
            background: rgba(239, 68, 68, 0.2);
            color: #FEE2E2;
            padding: 8px 24px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }
        
        .ticket-title {
            position: relative;
            z-index: 1;
            font-size: 32px;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .ticket-subtitle {
            position: relative;
            z-index: 1;
            font-size: 15px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }
        
        .ticket-body {
            padding: 45px 35px;
            background: white;
        }
        
        .event-title-section {
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 3px solid #EF4444;
        }
        
        .event-title {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .event-subtitle {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .event-info {
            margin-bottom: 35px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .info-row:hover {
            border-color: #EF4444;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);
        }
        
        .info-icon {
            font-size: 26px;
            margin-right: 16px;
            flex-shrink: 0;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-weight: 700;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .info-value {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }
        
        .attendee-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 3px solid #f59e0b;
            border-radius: 16px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }
        
        .attendee-label {
            font-size: 12px;
            color: #92400e;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        
        .attendee-name {
            font-size: 24px;
            color: #78350f;
            font-weight: 800;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #cbd5e1, #cbd5e1, transparent);
            margin: 35px 0;
        }
        
        .qr-section {
            text-align: center;
            padding: 35px 0;
            background: linear-gradient(to bottom, #f8fafc, white);
            border-radius: 16px;
            margin: 25px 0;
        }
        
        .qr-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .qr-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .qr-code {
            width: 280px;
            height: 280px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: 3px solid #e2e8f0;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .ticket-code {
            text-align: center;
            margin-top: 18px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            color: #1e293b;
            font-weight: 700;
            letter-spacing: 3px;
            padding: 12px 24px;
            background: #f1f5f9;
            border-radius: 8px;
            display: inline-block;
        }
        
        .ticket-footer {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px 35px;
            border-top: 2px dashed #cbd5e1;
        }
        
        .instructions {
            font-size: 13px;
            color: #475569;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .instructions strong {
            color: #1e293b;
            font-weight: 800;
        }
        
        .terms {
            font-size: 11px;
            color: #64748b;
            text-align: center;
            line-height: 1.6;
        }
        
        .footer-branding {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #cbd5e1;
        }
        
        .footer-logo {
            font-size: 16px;
            font-weight: 800;
            color: #1e293b;
        }
        
        .footer-logo .highlight {
            color: #EF4444;
        }
        
        .warning-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
            padding: 16px 20px;
            border-radius: 10px;
            margin: 25px 0;
        }
        
        .warning-text {
            color: #1e40af;
            font-size: 13px;
            line-height: 1.6;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="ticket-header">
            <div class="club-branding">
                @if($clubLogo && file_exists($clubLogo))
                    <img src="{{ $clubLogo }}" alt="{{ $ticket->club_name }}" class="club-logo">
                @else
                    <div class="club-logo" style="display: flex; align-items: center; justify-content: center; font-size: 48px; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); color: white; border: none;">
                        🎓
                    </div>
                @endif
                <div class="club-name">{{ $ticket->club_name ?? 'Cluversity' }}</div>
            </div>
            <div class="ticket-badge">🎟️ OFFICIAL TICKET</div>
            <div class="ticket-title">Event Pass</div>
            <div class="ticket-subtitle">Entrée Officielle • Unique</div>
        </div>
        
        <!-- Body -->
        <div class="ticket-body">
            <!-- Event Title -->
            <div class="event-title-section">
                <h1 class="event-title">{{ $ticket->event_title }}</h1>
                <p class="event-subtitle">Événement exclusif</p>
            </div>
            
            <!-- Event Information -->
            <div class="event-info">
                <div class="info-row">
                    <span class="info-icon">📅</span>
                    <div class="info-content">
                        <div class="info-label">Date et heure</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($ticket->event_date)->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</div>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-icon">📍</span>
                    <div class="info-content">
                        <div class="info-label">Lieu</div>
                        <div class="info-value">{{ $ticket->event_location }}</div>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-icon">🏢</span>
                    <div class="info-content">
                        <div class="info-label">Organisé par</div>
                        <div class="info-value">{{ $ticket->club_name }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Attendee Info -->
            <div class="attendee-box">
                <div class="attendee-label">🎫 Titulaire du Ticket</div>
                <div class="attendee-name">{{ $ticket->first_name }} {{ $ticket->last_name }}</div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Important Info -->
            <div class="warning-box">
                <div class="warning-text">
                    ℹ️ <strong>Important:</strong> Présentez ce ticket à l'entrée. Le QR code sera scanné une seule fois pour valider votre accès. Arrivez 15 minutes avant le début.
                </div>
            </div>
            
            <!-- QR Code Section -->
            <div class="qr-section">
                <div class="qr-title">🎫 Code QR d'Accès</div>
                <div class="qr-subtitle">Scannez ce code à l'entrée de l'événement</div>
                <div class="qr-code">
                    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Code" style="width: 100%; height: 100%;">
                </div>
                <div class="ticket-code">{{ $ticketCode }}</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="ticket-footer">
            <div class="instructions">
                <strong>Instructions:</strong> Présentez ce ticket à l'entrée pour validation. Ce billet est strictement personnel et non transférable.
            </div>
            <div class="terms">
                Ce ticket est valide uniquement pour la personne mentionnée ci-dessus.<br>
                Veuillez arriver 15 minutes avant le début de l'événement. Conservez ce ticket en lieu sûr.<br>
                Pour toute question, contactez les organisateurs de l'événement.
            </div>
            <div class="footer-branding">
                <div class="footer-logo">
                    Clu<span class="highlight">versity</span>
                </div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 6px;">
                    Université Sidi Mohamed Ben Abdellah - EST Fès
                </div>
            </div>
        </div>
    </div>
</body>
</html>