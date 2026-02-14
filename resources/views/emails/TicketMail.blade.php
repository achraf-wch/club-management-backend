<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f1f5f9;
            padding: 20px;
            line-height: 1.6;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            pointer-events: none;
        }
        
        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }
        
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: #EF4444;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }
        
        .logo-text .highlight {
            color: #EF4444;
        }
        
        .header-title {
            position: relative;
            z-index: 1;
            color: white;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .header-subtitle {
            position: relative;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
        }
        
        .email-body {
            padding: 35px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }
        
        .message {
            color: #475569;
            font-size: 15px;
            margin-bottom: 25px;
            line-height: 1.7;
        }
        
        .event-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #EF4444;
            border-radius: 12px;
            padding: 24px;
            margin: 25px 0;
        }
        
        .event-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #EF4444;
        }
        
        .event-detail {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .event-detail:last-child {
            margin-bottom: 0;
        }
        
        .event-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .event-text {
            color: #1e293b;
            font-weight: 500;
        }
        
        .attendee-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 10px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }
        
        .attendee-label {
            font-size: 11px;
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        
        .attendee-name {
            font-size: 18px;
            font-weight: 700;
            color: #78350f;
        }
        
        .pdf-notice {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            text-align: center;
        }
        
        .pdf-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .pdf-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 8px;
        }
        
        .pdf-text {
            font-size: 14px;
            color: #1e40af;
        }
        
        .instructions-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .instructions-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        
        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
            color: #475569;
        }
        
        .instruction-number {
            background: #EF4444;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 25px 0;
        }
        
        .email-footer {
            background: #f8fafc;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-text {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }
        
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                border-radius: 0;
            }
            
            .email-header,
            .email-body,
            .email-footer {
                padding: 25px 20px;
            }
            
            .header-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <div class="logo-container">
                <div class="logo">
                    <div class="logo-icon">🎓</div>
                    <span class="logo-text">Clu<span class="highlight">versity</span></span>
                </div>
            </div>
            <h1 class="header-title">🎟️ Votre Ticket</h1>
            <p class="header-subtitle">Tout est prêt pour votre événement !</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Bonjour {{ $memberName }},
            </div>
            
            <p class="message">
                Excellente nouvelle ! Vous êtes inscrit(e) à l'événement suivant. Votre ticket est joint à cet email.
            </p>
            
            <!-- Event Card -->
            <div class="event-card">
                <div class="event-title">{{ $eventTitle }}</div>
                
                <div class="event-detail">
                    <span class="event-icon">📅</span>
                    <span class="event-text">{{ \Carbon\Carbon::parse($eventDate)->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</span>
                </div>
                
                <div class="event-detail">
                    <span class="event-icon">📍</span>
                    <span class="event-text">{{ $eventLocation }}</span>
                </div>
                
                @if(!empty($eventDescription))
                <div class="event-detail">
                    <span class="event-icon">ℹ️</span>
                    <span class="event-text">{{ $eventDescription }}</span>
                </div>
                @endif
            </div>
            
            <!-- Attendee -->
            <div class="attendee-box">
                <div class="attendee-label">🎫 Titulaire</div>
                <div class="attendee-name">{{ $memberName }}</div>
            </div>
            
            <!-- PDF Notice -->
            <div class="pdf-notice">
                <div class="pdf-icon">📄</div>
                <div class="pdf-title">Ticket joint à cet email</div>
                <div class="pdf-text">Téléchargez le PDF et présentez le QR code à l'entrée</div>
            </div>
            
            <!-- Instructions -->
            <div class="instructions-box">
                <div class="instructions-title">📱 Comment utiliser votre ticket :</div>
                
                <div class="instruction-item">
                    <span class="instruction-number">1</span>
                    <span>Téléchargez le PDF joint (event-ticket.pdf)</span>
                </div>
                
                <div class="instruction-item">
                    <span class="instruction-number">2</span>
                    <span>Enregistrez-le sur votre téléphone ou imprimez-le</span>
                </div>
                
                <div class="instruction-item">
                    <span class="instruction-number">3</span>
                    <span>Présentez le QR code à l'entrée de l'événement</span>
                </div>
                
                <div class="instruction-item">
                    <span class="instruction-number">4</span>
                    <span>Notre équipe scannera votre code pour valider l'accès</span>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <p class="message" style="text-align: center; margin: 0;">
                À très bientôt ! 🎉
            </p>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p class="footer-text">
                Cet email a été envoyé automatiquement.<br>
                © {{ date('Y') }} Cluversity - Université Sidi Mohamed Ben Abdellah - EST Fès
            </p>
        </div>
    </div>
</body>
</html>