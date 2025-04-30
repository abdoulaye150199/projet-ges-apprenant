<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Apprenants ODC</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/promo.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/add-referentiel.css">
    <link rel="stylesheet" href="/assets/css/apprenants.css">
    <link rel="stylesheet" href="assets/css/student-details.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <?php
    // Chargement conditionnel des CSS
    $page = $_GET['page'] ?? 'dashboard';
    switch($page) {
        case 'add_promotion_form':
            echo '<link rel="stylesheet" href="assets/css/add-promotion.css">';
            break;
        case 'promotions':
            echo '<link rel="stylesheet" href="assets/css/promo.css">';
            break;
        // ...autres cas...
    }
    ?>
    
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="logo-container">
                <div class="logo">
                    <img style="width: 100px; display: block; margin: auto;" src="assets/images/sonatel-logo.png" alt="Sonatel Academy">
                    <div class="logo-text"></div>
                </div>
                <!-- Afficher la promotion active -->
                <p class="promotion">
                    <?php if (isset($active_promotion) && $active_promotion): ?>
                        <?= htmlspecialchars($active_promotion['name']) ?>
                    <?php else: ?>
                        Promotion - 2025
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Menu de navigation avec icônes -->
            <nav class="main-nav">
                <ul>
                    <li class="<?= isset($active_menu) && $active_menu === 'dashboard' ? 'active' : '' ?>">
                        <a href="?page=dashboard">
                            <span class="icon"><i class="fas fa-chart-pie"></i></span>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="<?= isset($active_menu) && $active_menu === 'promotions' ? 'active' : '' ?>">
                        <a href="?page=promotions">
                            <span class="icon"><i class="fas fa-graduation-cap"></i></span>
                            <span>Promotions</span>
                        </a>
                    </li>
                    <li class="<?= isset($active_menu) && $active_menu === 'referentiels' ? 'active' : '' ?>">
                        <a href="?page=referentiels">
                            <span class="icon"><i class="fas fa-folder"></i></span>
                            <span>Référentiels</span>
                        </a>
                    </li>
                    <li class="<?= isset($active_menu) && $active_menu === 'apprenants' ? 'active' : '' ?>">
                        <a href="?page=apprenants">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            <span>Apprenants</span>
                        </a>
                    </li>
                    <li class="<?= isset($active_menu) && $active_menu === 'presences' ? 'active' : '' ?>">
                        <a href="?page=presences">
                            <span class="icon"><i class="fas fa-clipboard-check"></i></span>
                            <span>Gestion des présences</span>
                        </a>
                    </li>
                    <li class="<?= isset($active_menu) && $active_menu === 'kits' ? 'active' : '' ?>">
                        <a href="?page=kits">
                            <span class="icon"><i class="fas fa-laptop"></i></span>
                            <span>Kits & Laptops</span>
                        </a>
                    </li>
                    <li class="<?= isset($active_menu) && $active_menu === 'rapports' ? 'active' : '' ?>">
                        <a href="?page=rapports">
                            <span class="icon"><i class="fas fa-chart-bar"></i></span>
                            <span>Rapports & Stats</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div id="bouton">
                <a href="?page=logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
        
        <div class="main-content">
            <header class="top-header">
                <div class="search-bar">
                    <i id="icon" class="fa-solid fa-magnifying-glass"></i>
                    <input id="inp" type="text" placeholder="Rechercher...">
                </div>
                
                <div class="user-menu">
                    <div class="notifications">
                        <i class="fa-regular fa-bell"></i>
                    </div>
                    <div class="user-profile">
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                            <span class="user-role"><?= htmlspecialchars($_SESSION['user']['profile']) ?></span>
                        </div>
                        <div class="avatar">
                            <img src="<?= $_SESSION['user']['image'] ?? 'assets/images/tof1.png' ?>" 
                                 alt="Photo de profil"
                                 onerror="this.src='assets/images/mama.jpeg'">
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Messages flash -->
            <?php if (isset($flash) && $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= $flash['message'] ?>
                </div>
            <?php endif; ?>
            
            <!-- Contenu de la page -->
            <div class="page-content">
                <?= $content ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>