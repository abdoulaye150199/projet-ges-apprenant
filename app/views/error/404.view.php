<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page non trouvée</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            color: #333;
        }
        
        .container {
            text-align: center;
            max-width: 500px;
            padding: 20px;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #FF7900;
            margin: 0;
            line-height: 1;
        }
        
        .error-message {
            font-size: 24px;
            margin: 20px 0;
            color: #FF7900;
        }
        
        .sticker {
            width: 120px;
            height: 120px;
            margin: 20px auto;
            background-color:
            #0e8f7e;
            border-radius: 50%;
            position: relative;
            animation: bounce 2s infinite;
        }
        
        .face {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .eyes {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        
        .eye {
            width: 12px;
            height: 12px;
            background-color: white;
            border-radius: 50%;
        }
        
        .mouth {
            width: 40px;
            height: 20px;
            border-bottom: 4px solid white;
            border-radius: 0 0 20px 20px;
            margin: 0 auto;
        }
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .buttons {
            margin-top: 30px;
        }
        
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #FF7900;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .button:hover {
            background-color: #E56A00;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        
        <div class="sticker">
            <div class="face">
                <div class="eyes">
                    <div class="eye"></div>
                    <div class="eye"></div>
                </div>
                <div class="mouth"></div>
            </div>
        </div>
        
        <div class="error-message">Fi nga dougou bakhoul delloul</div>
        
        <div class="buttons">
            <a href="javascript:history.back()" class="button">Retour</a>
            <a href="?page=dashboard" class="button">Accueil</a>
        </div>
    </div>
</body>
</html>