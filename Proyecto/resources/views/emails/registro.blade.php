<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido!</h1>
        </div>
        <div class="content">
            <p>Hola {{ $usuario->nombre }},</p>
            <p>Tu cuenta ha sido creada exitosamente en nuestra plataforma.</p>
            <p>Puedes iniciar sesión con el siguiente email:</p>
            <p><strong>{{ $usuario->email }}</strong></p>
            <p>¡Esperamos que disfrutes usando nuestra plataforma!</p>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
