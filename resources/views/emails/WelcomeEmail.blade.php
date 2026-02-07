<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; border-radius: 5px; }
        .content { padding: 20px; }
        .credentials-box { background-color: #f8f9fa; border: 2px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .credentials-box strong { color: #007bff; }
        .button { display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .footer { color: #666; font-size: 12px; margin-top: 20px; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue au club {{ $club->name }}! 🎉</h1>
        </div>
        
        <div class="content">
            <h2>Bonjour {{ $person->first_name }} {{ $person->last_name }},</h2>
            
            <p>Nous sommes heureux de vous accueillir dans notre club!</p>
            
            @if($password)
            <div class="credentials-box">
                <h3>🔐 Vos identifiants de connexion:</h3>
                <p><strong>Email:</strong> {{ $person->email }}</p>
                <p><strong>Mot de passe:</strong> {{ $password }}</p>
            </div>
            
            <div class="warning">
                ⚠️ <strong>Important:</strong> Pour votre sécurité, veuillez changer votre mot de passe lors de votre première connexion.
            </div>
            @endif
            
            <h3>Détails de votre adhésion:</h3>
            <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td><strong>Club:</strong></td>
                    <td>{{ $club->name }}</td>
                </tr>
                <tr>
                    <td><strong>Votre rôle:</strong></td>
                    <td>{{ ucfirst($role) }}</td>
                </tr>
                <tr>
                    <td><strong>Email enregistré:</strong></td>
                    <td>{{ $person->email }}</td>
                </tr>
                <tr>
                    <td><strong>Date d'adhésion:</strong></td>
                    <td>{{ now()->format('d/m/Y') }}</td>
                </tr>
            </table>
            
            <p>Vous pouvez maintenant accéder à tous les services et événements du club.</p>
            
            <a href="{{ env('APP_URL') }}/login" class="button">
                Se connecter maintenant
            </a>
            
            <a href="{{ env('APP_URL') }}/clubs/{{ $club->id }}" class="button">
                Accéder au club
            </a>
            
            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            
            <p>Cordialement,<br><strong>L'équipe du {{ $club->name }}</strong></p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé à {{ $person->email }}. Si vous pensez que c'est une erreur, veuillez nous contacter.</p>
        </div>
    </div>
</body>
</html>