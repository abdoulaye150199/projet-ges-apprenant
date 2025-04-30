<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - Gestion des Apprenants ODC</title>
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="/assets/images/sonatel-logo.png" alt="Logo Sonatel Academy">
            <h1>Orange Digital Center</h1>
            <h2>Ecole du code Sonatel Academy</h2>
        </div>
        
        <?php if (isset($flash) && $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>
        
        <?= $content ?>
    </div>
</body>
</html>