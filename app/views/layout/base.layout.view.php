<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Apprenants ODC</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <?php if (isset($flash) && $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>
        
        <?= $content ?>
    </div>
</body>
</html>