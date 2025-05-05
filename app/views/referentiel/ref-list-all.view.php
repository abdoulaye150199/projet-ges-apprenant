<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les référentiels</title>
    <link rel="stylesheet" href="/assets/css/referentiels.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tous les Référentiels</h1>
            <div class="header-actions">
                <a href="?page=referentiels" class="btn btn-back">Retour</a>
            </div>
        </div>
        
        <div class="search-section">
            <form action="" method="GET" class="search-bar">
                <div class="search-icon">🔍</div>
                <input type="hidden" name="page" value="all-referentiels">
                <input type="text" 
                       name="search" 
                       placeholder="Rechercher un référentiel..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </form>

            <a href="?page=add-referentiel" class="btn btn-teal">
                <span>+</span> Créer un nouveau Référentiel
            </a>
        </div>

        <!-- Ajouter ce code de débogage temporaire -->
        <div style="display: none;">
            Debug: 
            Page: <?= var_dump($page) ?>, 
            Pages: <?= var_dump($pages) ?>, 
            Total items: <?= var_dump(count($referentiels)) ?>
        </div>

        <div class="cards-container">
            <?php if (!empty($referentiels)): ?>
                <?php foreach ($referentiels as $ref): ?>
                    <div class="card">
                        <div class="card-image">
                            <img src="<?= $ref['image'] ?? 'assets/images/referentiels/default.jpg' ?>" 
                                 alt="<?= htmlspecialchars($ref['name']) ?>">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?= htmlspecialchars($ref['name']) ?></h3>
                            <p class="card-subtitle"><?= count($ref['modules'] ?? []) ?> modules</p>
                            <p class="card-description"><?= htmlspecialchars($ref['description']) ?></p>
                        </div>
                        <div class="card-stats">
                            <a href="?page=apprenants&ref_filter=<?= $ref['id'] ?>" class="stat-item">
                                <i class="fas fa-users"></i>
                                <span class="stat-value"><?= count($ref['apprenants'] ?? []) ?></span>
                                <span class="stat-label">apprenants</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">Aucun référentiel trouvé</div>
            <?php endif; ?>
        </div> <!-- Fin de cards-container -->

        <?php if ($pages > 0): ?>
            <div class="pagination">
                <!-- Bouton Précédent -->
                <?php if ($page > 1): ?>
                    <a href="?page=all-referentiels&page_num=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="pagination-button prev">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Précédent
                    </a>
                <?php endif; ?>

                <!-- Numéros de page -->
                <?php
                $start = max(1, min($page - 2, $pages - 4));
                $end = min($pages, max(5, $page + 2));
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="?page=all-referentiels&page_num=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="pagination-button <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Bouton Suivant -->
                <?php if ($page < $pages): ?>
                    <a href="?page=all-referentiels&page_num=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="pagination-button next">
                        Suivant
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>