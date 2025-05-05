<?php
// Récupération des paramètres de filtre
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$ref_filter = isset($_GET['ref_filter']) ? $_GET['ref_filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$current_view = isset($_GET['view']) ? $_GET['view'] : 'grid';

// Trouver la promotion active pour l'afficher en premier
$active_promotion = null;
$other_promotions = array();

foreach ($promotions as $promotion) {
    if ($promotion['status'] === 'active') {
        $active_promotion = $promotion;
    } else {
        $other_promotions[] = $promotion;
    }
}

// Gestion de la soumission automatique des formulaires
if (isset($_GET['auto_submit']) && $_GET['auto_submit'] === 'grid_status') {
    $redirect_url = "?page=promotions&view=grid";
    if (!empty($search)) {
        $redirect_url .= "&search=" . urlencode($search);
    }
    if (!empty($_GET['status_filter'])) {
        $redirect_url .= "&status_filter=" . urlencode($_GET['status_filter']);
    }
    header("Location: " . $redirect_url);
    exit;
}

if (isset($_GET['auto_submit']) && $_GET['auto_submit'] === 'list_filters') {
    $redirect_url = "?page=promotions&view=list";
    if (!empty($search)) {
        $redirect_url .= "&search=" . urlencode($search);
    }
    if (!empty($_GET['status_filter'])) {
        $redirect_url .= "&status_filter=" . urlencode($_GET['status_filter']);
    }
    if (!empty($_GET['ref_filter'])) {
        $redirect_url .= "&ref_filter=" . urlencode($_GET['ref_filter']);
    }
    header("Location: " . $redirect_url);
    exit;
}
?>

<div class="container <?php echo $current_view === 'list' ? 'show-list' : ''; ?>">
    <!-- En-tête commun -->
    <div class="header">
        <div class="header-title">
            <h1>Promotion</h1>
            <div class="header-subtitle">Gérer les promotions de l'école</div>
        </div>
        <a href="?page=add_promotion_form" class="add-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Ajouter une promotion
        </a>
    </div>

    <!-- Cartes de statistiques -->
    <div class="stats-container">
        <div class="stat-card">
            <div>
                <div class="stat-number"><?php echo $stats['active_learners']; ?></div>
                <div class="stat-label">Apprenants</div>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="white">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
        </div>
        
        <div class="stat-card">
            <div>
                <div class="stat-number"><?php echo $stats['total_referentials']; ?></div>
                <div class="stat-label">Référentiels</div>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="white">
                    <path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5-1.95 0-4.05.4-5.5 1.5v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1z"/>
                </svg>
            </div>
        </div>
        
        <div class="stat-card">
            <div>
                <div class="stat-number"><?php echo $stats['active_promotions']; ?></div>
                <div class="stat-label">Promotions actives</div>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="white">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </div>
        </div>
        
        <div class="stat-card">
            <div>
                <div class="stat-number"><?php echo $stats['total_promotions']; ?></div>
                <div class="stat-label">Total promotions</div>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="white">
                    <path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtres pour le mode grille -->
    <div class="grid-filters" <?php echo $current_view === 'list' ? 'style="display:none"' : ''; ?>>
        <div class="search-container">
            <form action="?page=promotions" method="GET" class="search-form">
                <input type="hidden" name="page" value="promotions">
                <input type="hidden" name="view" value="grid">
                <?php if (!empty($status_filter)) { ?>
                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter); ?>">
                <?php } ?>
                <div class="search-with-icon">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="filter-view-container">
            <div class="select-container">
                <form action="?page=promotions" method="GET" id="grid-status-form">
                    <input type="hidden" name="page" value="promotions">
                    <input type="hidden" name="view" value="grid">
                    <input type="hidden" name="auto_submit" value="grid_status">
                    <?php if (!empty($search)) { ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php } ?>
                    <select class="filter-dropdown" name="status_filter" onchange="document.getElementById('grid-status-form').submit()">
                        <option value="">Tous</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Actives</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactives</option>
                    </select>
                </form>
            </div>
            
            <div class="view-buttons">
                <a href="?page=promotions<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter=' . urlencode($status_filter) : ''; ?><?php echo !empty($ref_filter) ? '&ref_filter=' . urlencode($ref_filter) : ''; ?>&view=grid" 
                class="view-button <?php echo ($current_view === 'grid') ? 'active' : ''; ?>">Grille</a>
                
                <a href="?page=promotions<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter=' . urlencode($status_filter) : ''; ?><?php echo !empty($ref_filter) ? '&ref_filter=' . urlencode($ref_filter) : ''; ?>&view=list" 
                class="view-button <?php echo ($current_view === 'list') ? 'active' : ''; ?>">Liste</a>
            </div>
        </div>
    </div>

    <!-- Filtres pour le mode liste -->
    <div class="list-filters" <?php echo $current_view === 'grid' ? 'style="display:none"' : ''; ?>>
        <div class="list-search">
            <form action="?page=promotions" method="GET">
                <input type="hidden" name="page" value="promotions">
                <input type="hidden" name="view" value="list">
                <?php if (!empty($status_filter)) { ?>
                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter); ?>">
                <?php } ?>
                <?php if (!empty($ref_filter)) { ?>
                    <input type="hidden" name="ref_filter" value="<?php echo htmlspecialchars($ref_filter); ?>">
                <?php } ?>
                <div class="search-with-icon">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="list-filter-options">
            <form action="?page=promotions" method="GET" id="list-filters-form">
                <input type="hidden" name="page" value="promotions">
                <input type="hidden" name="view" value="list">
                <input type="hidden" name="auto_submit" value="list_filters">
                <?php if (!empty($search)) { ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php } ?>
                
                <div class="select-container">
                    <select class="filter-dropdown" name="ref_filter" onchange="document.getElementById('list-filters-form').submit()">
                        <option value="">Filtre par classe</option>
                        <?php if (isset($referentiels) && is_array($referentiels)): ?>
                            <?php foreach ($referentiels as $ref): ?>
                                <option value="<?php echo $ref['id']; ?>" <?php echo $ref_filter === $ref['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ref['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="select-container">
                    <select class="filter-dropdown" name="status_filter" onchange="document.getElementById('list-filters-form').submit()">
                        <option value="">Filtre par status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Actives</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactives</option>
                    </select>
                </div>
            </form>
            
        </div>
    </div>

    <!-- Affichage des promotions en mode grille -->
    <div class="promotions-grid">
        <?php if (empty($promotions)) { ?>
            <div class="no-results">Aucune promotion trouvée</div>
        <?php } else { ?>
            <?php foreach ($promotions as $promotion) { ?>
                <div class="promotion-card" data-status="<?php echo $promotion['status']; ?>">
                    <div class="card-header">
                        <div class="status-badge <?php echo $promotion['status'] === 'active' ? 'active' : 'inactive'; ?>">
                            <?php echo ucfirst($promotion['status']); ?>
                        </div>
                        <form action="?page=toggle-promotion-status" method="POST">
                            <input type="hidden" name="promotion_id" value="<?php echo $promotion['id']; ?>">
                            <input type="hidden" name="view" value="<?php echo htmlspecialchars($current_view); ?>">
                            <button type="submit" class="toggle-button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                    <line x1="12" y1="2" x2="12" y2="12"></line>
                                </svg>
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-content">
                        <div class="promo-info">
                            <img src="<?= !empty($promotion['image']) ? ltrim($promotion['image'], '/') : 'assets/images/default-promotion.jpg' ?>"
                                 alt="<?= htmlspecialchars($promotion['name']) ?>"
                                 class="promotion-avatar">
                            <div class="promotion-details">
                                <h3 class="promotion-title"><?php echo htmlspecialchars($promotion['name']); ?></h3>
                                <div class="promotion-date">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <?php echo date('d/m/Y', strtotime($promotion['date_debut'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($promotion['date_fin'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="promotion-students">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <?php echo count($promotion['apprenants'] ?? []); ?> apprenants
                        </div>
                        
                        <div class="card-actions">
                            <a href="?page=promotion&id=<?php echo $promotion['id']; ?>" class="view-details">
                                Voir détails 
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <!-- Affichage des promotions en mode liste -->
    <div class="promotions-list">
        <!-- En-tête du tableau -->
        <div class="list-header">
            <div class="list-cell photo-cell">Photo</div>
            <div class="list-cell promotion-cell">Promotion</div>
            <div class="list-cell date-debut-cell">Date de début</div>
            <div class="list-cell date-fin-cell">Date de fin</div>
            <div class="list-cell referentiel-cell">Référentiel</div>
            <div class="list-cell statut-cell">Statut</div>
            <div class="list-cell actions-cell">Actions</div>
        </div>

        <!-- Lignes du tableau -->
        <?php foreach ($promotions as $promotion) { ?>
        <div class="list-row" data-status="<?php echo $promotion['status']; ?>">
            <div class="list-cell photo-cell">
                <img src="<?= !empty($promotion['image']) ? ltrim($promotion['image'], '/') : 'assets/images/default-promotion.jpg' ?>"
                     alt="<?= htmlspecialchars($promotion['name']) ?>"
                     class="promotion-avatar-list">
            </div>
            <div class="list-cell promotion-cell"><?php echo htmlspecialchars($promotion['name']); ?></div>
            <div class="list-cell date-debut-cell"><?php echo date('d/m/Y', strtotime($promotion['date_debut'])); ?></div>
            <div class="list-cell date-fin-cell"><?php echo date('d/m/Y', strtotime($promotion['date_fin'])); ?></div>
            <div class="list-cell referentiel-cell">
                <?php 
                // Afficher les référentiels associés à la promotion
                if (isset($promotion['referentiels']) && !empty($promotion['referentiels'])) {
                    $ref_badges = array(
                        "DEV WEB" => "dev-web",
                        "MOBILE" => "dev-web",
                        "REF DIG" => "ref-dig",
                        "DEV DATA" => "dev-data",
                        "AWS" => "aws",
                        "HACKEUSE" => "hackeuse"
                    );
                    
                    // On utilise les IDs des référentiels pour afficher 
                    // car on n'a pas les détails des référentiels ici
                    foreach ($promotion['referentiels'] as $ref_id) {
                        $ref_name = $ref_id; // Par défaut utiliser l'ID
                        
                        // Essayer de trouver ce référentiel dans la liste fournie
                        if (isset($referentiels_map[$ref_id])) {
                            $ref_name = $referentiels_map[$ref_id];
                        }
                        
                        // Déterminer la classe CSS du badge
                        $badge_class = "dev-web"; // Classe par défaut
                        
                        // Chercher une correspondance dans les classes prédéfinies
                        foreach ($ref_badges as $key => $class) {
                            if (stripos($ref_name, $key) !== false) {
                                $badge_class = $class;
                                break;
                            }
                        }
                        
                        echo '<span class="ref-badge ' . $badge_class . '">' . htmlspecialchars($ref_name) . '</span>';
                    }
                } else {
                    echo 'Aucun';
                }
                ?>
            </div>
            <div class="list-cell statut-cell">
                <span class="statut-badge <?php echo $promotion['status'] === 'active' ? 'active' : 'inactive'; ?>">
                    <?php echo $promotion['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
            <div class="list-cell actions-cell">
                <div class="actions-menu">
                    <button type="button" class="more-actions">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <div class="pagination">
        <?php if ($total_pages > 1) { ?>
            <!-- Bouton précédent -->
            <?php if ($current_page > 1) { ?>
                <a href="?page=promotions&page_num=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&view=<?php echo $current_view; ?><?php echo !empty($status_filter) ? '&status_filter=' . urlencode($status_filter) : ''; ?><?php echo !empty($ref_filter) ? '&ref_filter=' . urlencode($ref_filter) : ''; ?>" 
                   class="pagination-button prev">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
            <?php } ?>

            <!-- Pages numérotées -->
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <a href="?page=promotions&page_num=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&view=<?php echo $current_view; ?><?php echo !empty($status_filter) ? '&status_filter=' . urlencode($status_filter) : ''; ?><?php echo !empty($ref_filter) ? '&ref_filter=' . urlencode($ref_filter) : ''; ?>" 
                   class="pagination-button <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php } ?>

            <!-- Bouton suivant -->
            <?php if ($current_page < $total_pages) { ?>
                <a href="?page=promotions&page_num=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&view=<?php echo $current_view; ?><?php echo !empty($status_filter) ? '&status_filter=' . urlencode($status_filter) : ''; ?><?php echo !empty($ref_filter) ? '&ref_filter=' . urlencode($ref_filter) : ''; ?>" 
                   class="pagination-button next">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            <?php } ?>
        <?php } ?>
    </div>
</div>