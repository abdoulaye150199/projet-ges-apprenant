<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>list ref</title>
    <link rel="stylesheet" href="/assets/css/referentiels.css">
</head>
<body>

<!-- app/views/referentiel/list.html.php -->
<!-- app/views/referentiel/list.html.php -->

<!-- app/views/referentiel/list.html.php -->
<div class="container">
    <div class="header">
        <h1>Référentiels de <?= htmlspecialchars($current_promotion['name']) ?></h1>
        <p>Gérer les référentiels de la promotion</p>
    </div>
    
    <div class="search-section">
        <form action="" method="GET" class="search-bar">
            <input type="hidden" name="page" value="referentiels">
            <input type="text" 
                   name="search" 
                   placeholder="Rechercher un référentiel..." 
                   value="<?= htmlspecialchars($search ?? '') ?>"
                   autocomplete="off">
            <i class="fas fa-search search-icon"></i>
        </form>
        
        <button class="btn btn-orange" onclick="window.location.href='?page=all-referentiels'">
            <i class="fas fa-list"></i>
            <span>Tous les référentiels</span>
        </button>
        
        <button class="btn btn-teal" onclick="window.location.href='?page=assign-referentiels'">
            <i class="fas fa-plus"></i>
            <span>Ajouter à la promotion</span>
        </button>
    </div>
    
    <div class="actions-group">
        <!-- Pour le dropdown de téléchargement -->
        <div class="dropdown">
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item pdf" href="?page=download-list&format=pdf">
                        <i class="far fa-file-pdf"></i> PDF
                    </a>
                </li>
                <li>
                    <a class="dropdown-item excel" href="?page=download-list&format=excel">
                        <i class="far fa-file-excel"></i> Excel
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Autres boutons... -->
    </div>
    
    <div class="cards-container">
        <?php if (empty($referentiels)): ?>
            <div class="no-data">Aucun référentiel n'est assigné à cette promotion</div>
        <?php else: ?>
            <?php foreach ($referentiels as $ref): ?>
                <div class="card">
                    <div class="card-image">
                        <img src="<?= $ref['image'] ? '/' . $ref['image'] : '/assets/images/referentiels/default.jpg' ?>" 
                             alt="<?= htmlspecialchars($ref['name']) ?>">
                    </div>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($ref['name']) ?></h3>
                        <p class="description"><?= htmlspecialchars($ref['description']) ?></p>
                        <div class="stats">
                            <a href="?page=apprenants&ref_filter=<?= $ref['id'] ?>" class="stat-item">
                                <i class="fas fa-users"></i>
                                <span class="count"><?= count($ref['apprenants'] ?? []) ?></span>
                                <span class="label">apprenants</span>
                            </a>
                            <!-- Autres stats... -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>

</html>