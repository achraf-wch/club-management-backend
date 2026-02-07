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
        .button { display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .footer { color: #666; font-size: 12px; margin-top: 20px; }
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
            
            <h3>Détails de votre adhésion:</h3>
            <table border="1" cellpadding="10" style="width: 100%;">
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
            
            <a href="{{ env('APP_URL') }}/clubs/{{ $club->id }}" class="button">
                Accéder au club
            </a>
             <a href="{{ env('APP_URL') }}/clubs/{{ $club->id }}" class="button">
                Accéder au votre dashboard
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