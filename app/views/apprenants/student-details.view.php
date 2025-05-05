<div class="apd-container">
    <div class="apd-main-content">
        <!-- Sidebar avec informations de l'apprenant -->
        <div class="apd-sidebar">
            <div class="apd-profile-card">
                <!-- Photo de profil -->
                <div class="apd-profile-img">
                    <?php if (!empty($apprenant['photo'])): ?>
                        <img src="<?= htmlspecialchars($apprenant['photo']) ?>" alt="Photo de profil">
                    <?php else: ?>
                        <img src="assets/images/default-avatar.png" alt="Photo par défaut">
                    <?php endif; ?>
                </div>

                <!-- Informations principales -->
                <div class="apd-profile-info">
                    <h2 class="apd-profile-name">
                        <?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?>
                    </h2>
                    <div class="apd-profile-matricule">
                        <?= htmlspecialchars($apprenant['matricule']) ?>
                    </div>
                    <div class="apd-status-badge <?= $apprenant['status'] ?>">
                        <?= ucfirst($apprenant['status']) ?>
                    </div>
                </div>

                <!-- Informations de contact -->
                <div class="apd-contact-info">
                    <div class="apd-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?= htmlspecialchars($apprenant['email']) ?></span>
                    </div>
                    <div class="apd-contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?= htmlspecialchars($apprenant['telephone']) ?></span>
                    </div>
                    <div class="apd-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($apprenant['adresse']) ?></span>
                    </div>
                    <div class="apd-contact-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span><?= htmlspecialchars($apprenant['referentiel_name']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="apd-dashboard">
            <!-- En-tête avec les statistiques -->
            <div class="apd-stats">
                <div class="apd-stat-card">
                    <div class="apd-stat-icon green">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="apd-stat-info">
                        <div class="apd-number">20</div>
                        <div class="apd-label">En progression</div>
                    </div>
                </div>

                <div class="apd-stat-card">
                    <div class="apd-stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="apd-stat-info">
                        <div class="apd-number">5</div>
                        <div class="apd-label">En attente</div>
                    </div>
                </div>

                <div class="apd-stat-card">
                    <div class="apd-stat-icon red">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="apd-stat-info">
                        <div class="apd-number">1</div>
                        <div class="apd-label">Terminé</div>
                    </div>
                </div>
            </div>

            <!-- Liste des modules -->
            <div class="apd-modules-section">
                <h3 class="apd-section-title">Suivi opérationel par étudiant</h3>
                
                <div class="apd-modules-grid">
                    <?php foreach ($modules ?? [] as $module): ?>
                    <div class="apd-module-card">
                        <div class="apd-module-header">
                            <span class="apd-module-label">Online</span>
                        </div>
                        <h4 class="apd-module-title"><?= htmlspecialchars($module['nom']) ?></h4>
                        <div class="apd-module-formateur">
                            Formateur: <strong><?= htmlspecialchars($module['formateur']) ?></strong>
                        </div>
                        <div class="apd-progress-bar">
                            <div class="apd-progress-fill" style="width: <?= $module['progression'] ?>%"></div>
                        </div>
                        <div class="apd-module-meta">
                            <div class="apd-meta-item">
                                <i class="far fa-calendar"></i>
                                <span><?= htmlspecialchars($module['date_debut']) ?></span>
                            </div>
                            <div class="apd-meta-item">
                                <i class="far fa-clock"></i>
                                <span>10:45 pm</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>