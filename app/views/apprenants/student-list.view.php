<div class="student-container">
    <div class="student-header">
        <h2 class="app-title">Apprenants</h2>
        
        <!-- Onglets -->
        <div class="nav nav-tabs mb-4">
            <a class="nav-link <?= $current_tab === 'retained' ? 'active' : '' ?>" 
               href="?page=apprenants&tab=retained">
                Apprenants retenus
            </a>
            <a class="nav-link <?= $current_tab === 'waiting' ? 'active' : '' ?>" 
               href="?page=apprenants&tab=waiting">
                Liste d'attente
            </a>
        </div>

        <div class="search-filters-container">
            <form action="" method="GET" id="filter-form">
                <input type="hidden" name="page" value="apprenants">
                <div class="filters-group">
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Rechercher par nom ou matricule..."
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="filter-box">
                        <select name="referentiel" class="form-select" onchange="this.form.submit()">
                            <option value="">Filtre par classe</option>
                            <?php foreach($referentiels as $ref): ?>
                                <option value="<?= $ref['id'] ?>" 
                                        <?= (isset($_GET['referentiel']) && $_GET['referentiel'] == $ref['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ref['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-box">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Filtre par status</option>
                            <option value="actif" <?= (isset($_GET['status']) && $_GET['status'] == 'actif') ? 'selected' : '' ?>>
                                Actif
                            </option>
                            <option value="exclu" <?= (isset($_GET['status']) && $_GET['status'] == 'exclu') ? 'selected' : '' ?>>
                                Exclu
                            </option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="actions-group">
                <!-- Pour le dropdown de téléchargement -->
                <div class="dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Télécharger la liste
                    </button>
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

                <!-- Nouveau bouton d'import -->
                <a href="?page=import-apprenants" class="btn btn-secondary">
                    <i class="fas fa-file-import"></i> Importer
                </a>

                <!-- Bouton d'ajout existant -->
                <a href="?page=add-apprenant" style="display: inline-flex; align-items: center; gap: 6px; background-color: #6BC87C; color: white; padding: 5px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px;">
                    <i class="fas fa-plus"></i> Ajouter apprenant
                </a>
            </div>
        </div>
    </div>

    <div class="student-table">
        <div class="card">
            <div class="card-header">
                <?php if (isset($selected_referentiel) && $selected_referentiel): ?>
                    <h3>Apprenants du référentiel <?= htmlspecialchars($selected_referentiel['name']) ?></h3>
                <?php else: ?>
                    <h3>Liste des apprenants</h3>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Matricule</th> 
                            <th>Nom Complet</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Référentiel</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apprenants as $apprenant): 
                            $missing_fields = [];
                            if (empty($apprenant['prenom'])) $missing_fields[] = 'Prénom';
                            if (empty($apprenant['nom'])) $missing_fields[] = 'Nom';
                            if (empty($apprenant['email'])) $missing_fields[] = 'Email';
                            if (empty($apprenant['telephone'])) $missing_fields[] = 'Téléphone';
                            if (empty($apprenant['adresse'])) $missing_fields[] = 'Adresse';
                            if (empty($apprenant['referentiel_id'])) $missing_fields[] = 'Référentiel';
                        ?>
                        <tr>
                            <td>
                                <img src="<?= !empty($apprenant['photo']) ? htmlspecialchars($apprenant['photo']) : 'assets/images/default-profile.png' ?>" 
                                     class="rounded-circle"
                                     width="40" 
                                     height="40"
                                     alt="Photo de profil">
                            </td>
                            <td><?= htmlspecialchars($apprenant['matricule']) ?></td>
                            <td><?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></td>
                            <td><?= htmlspecialchars($apprenant['adresse']) ?></td>
                            <td><?= htmlspecialchars($apprenant['telephone']) ?></td>
                            <td>
                                <?php
                                $referentiel_id = $apprenant['referentiel_id'] ?? null;
                                $referentiel = $referentiel_id ? $model['get_referentiel_by_id']($referentiel_id) : null;
                                $refClass = '';
                                
                                if ($referentiel) {
                                    switch(strtolower($referentiel['name'])) {
                                        case 'dev web/mobile':
                                            $refClass = 'badge-dev-web';
                                            break;
                                        case 'ref dig':
                                            $refClass = 'badge-ref-dig';
                                            break;
                                        case 'dev data':
                                            $refClass = 'badge-dev-data';
                                            break;
                                        case 'aws':
                                            $refClass = 'badge-aws';
                                            break;
                                        case 'hackeuse':
                                            $refClass = 'badge-hackeuse';
                                            break;
                                        default:
                                            $refClass = 'badge-default';
                                    }
                                    ?>
                                    <span class="badge <?= $refClass ?>">
                                        <?= htmlspecialchars($referentiel['name']) ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="badge badge-default">Non défini</span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge <?= $apprenant['status'] === 'actif' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= htmlspecialchars($apprenant['status']) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <div class="dropdown actions">
                                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if ($current_tab === 'waiting'): ?>
                                            <li>
                                                <a class="dropdown-item" href="?page=edit-apprenant&id=<?= $apprenant['id'] ?>">
                                                    <i class="fas fa-edit"></i> Compléter le profil
                                                    <small class="text-danger d-block">
                                                        Informations manquantes : <?= implode(', ', $missing_fields) ?>
                                                    </small>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li>
                                                <a class="dropdown-item" href="?page=apprenant-details&id=<?= $apprenant['id'] ?>">
                                                    <i class="fas fa-eye"></i> Voir détails
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="?page=edit-apprenant&id=<?= $apprenant['id'] ?>">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="?page=delete-apprenant&id=<?= $apprenant['id'] ?>" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet apprenant ?')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <!-- Information sur la pagination -->
            <div class="text-muted">
                <?= $pagination['start'] ?> à <?= $pagination['end'] ?> apprenants sur <?= $pagination['total_items'] ?>
            </div>

            <!-- Navigation des pages -->
            <div class="pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=apprenants&page_num=<?= $pagination['current_page'] - 1 ?>&items_per_page=<?= $pagination['items_per_page'] ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['referentiel']) ? '&referentiel=' . urlencode($filters['referentiel']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>" 
                       class="pagination-button prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == 1 || $i == $pagination['total_pages'] || ($i >= $pagination['current_page'] - 2 && $i <= $pagination['current_page'] + 2)): ?>
                        <a href="?page=apprenants&page_num=<?= $i ?>&items_per_page=<?= $pagination['items_per_page'] ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['referentiel']) ? '&referentiel=' . urlencode($filters['referentiel']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>"
                           class="pagination-button <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif ($i == $pagination['current_page'] - 3 || $i == $pagination['current_page'] + 3): ?>
                        <span class="pagination-button disabled">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <a href="?page=apprenants&page_num=<?= $pagination['current_page'] + 1 ?>&items_per_page=<?= $pagination['items_per_page'] ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['referentiel']) ? '&referentiel=' . urlencode($filters['referentiel']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>" 
                       class="pagination-button next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sélecteur d'éléments par page -->
        <div>
            <select class="form-select form-select-sm" style="width: 70px" 
                    onchange="window.location.href='?page=apprenants&items_per_page=' + this.value + '<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['referentiel']) ? '&referentiel=' . urlencode($filters['referentiel']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>'">
                <option value="10" <?= $pagination['items_per_page'] == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $pagination['items_per_page'] == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $pagination['items_per_page'] == 50 ? 'selected' : '' ?>>50</option>
            </select>
        </div>
    </div>
</div>