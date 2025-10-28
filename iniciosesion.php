

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Alert - Iniciar Sesión</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #FAF3B5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            position: relative;
            width: 430px;
            height: 932px;
            background: #FAF3B5;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 40px;
            box-sizing: border-box;
        }

        .logo-container {
            margin-bottom: 40px;
            margin-top: 80px;
            text-align: center;
        }

        .logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            border: none;
        }

        .page-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            text-align: center;
        }

        .page-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 40px;
            text-align: center;
        }

        .form-container {
            width: 100%;
            max-width: 350px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            color: #333;
            box-sizing: border-box;
        }

        .input-group input::placeholder {
            color: #999;
        }

        .input-group .icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: #999;
            border-radius: 50%;
        }

        .password-input {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: #999;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }

        .login-button {
            background: #C77DFF;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            text-transform: none;
        }

        .login-button:hover {
            background: #B968FF;
        }

        .bottom-links {
            margin-top: 30px;
            text-align: center;
        }

        .bottom-links span {
            color: #666;
            font-size: 14px;
        }

        .bottom-links a {
            color: #FF6B35;
            text-decoration: none;
            font-weight: 500;
        }

        
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="image 27.png" alt="Logo Lost&Found" class="logo">
        </div>

        <h1 class="page-title">Iniciar sesión</h1>
        <p class="page-subtitle">Por favor regístrese para continuar.</p>

        <form class="form-container">
            <div class="input-group">
                <div class="icon user-icon"></div>
                <input type="text" placeholder="Username" required>
            </div>

            <div class="input-group password-input">
                <div class="icon lock-icon"></div>
                <input type="password" id="password" placeholder="••••••••••••" required>
                <button type="button" class="password-toggle" onclick="togglePassword()"></button>
            </div>

            <button type="submit" class="login-button">Iniciar sesion</button>
        </form>

        <div class="bottom-links">
            <span>¿No tienes cuenta? </span>
            <a href="#">Registrarse</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Iniciando sesión...');
        });
    </script>
</body>
</html>
