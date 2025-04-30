<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Apprenant</title>
    <link rel="stylesheet" href="/assets/css/apprenant.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <h1 class="site-title">Espace Apprenant</h1>
            <nav class="nav-links">
                <a href="?page=apprenant-profile" class="nav-link">Mon Profil</a>
                <a href="?page=logout" class="nav-link">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Carte de profil -->
        <div class="profile-card">
            <div class="profile-banner">
                <img src="<?= $apprenant['photo'] ?? '/assets/images.png' ?>" 
                     alt="Photo de profil" 
                     class="profile-image">
            </div>
            <div class="profile-info">
                <h2 class="profile-name"><?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></h2>
                <p class="profile-job"><?= htmlspecialchars($referentiel['name'] ?? 'Dev WEB/mobile') ?></p>
                <div class="profile-contact">
                    <div class="contact-info">
                        <span class="contact-icon">✉</span>
                        <span><?= htmlspecialchars($apprenant['email']) ?></span>
                    </div>
                    <div class="contact-info">
                        <span class="contact-icon">🆔</span>
                        <span><?= htmlspecialchars($apprenant['matricule']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?= $content ?? '' ?>
    </main>
</body>
</html>