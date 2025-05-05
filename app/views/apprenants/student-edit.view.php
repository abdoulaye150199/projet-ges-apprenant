<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Modifier l'apprenant</h2>
            <a href="?page=apprenants" class="back-button">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        
        <div class="card-body">
            <form action="?page=edit-apprenant-process" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($apprenant['id']) ?>">
                
                <!-- Photo de profil actuelle -->
                <div class="text-center mb-4">
                    <?php 
                    $photo_url = !empty($apprenant['photo']) ? 
                        htmlspecialchars($apprenant['photo']) : 
                        'assets/images/default-profile.png';
                    ?>
                    <img src="<?= $photo_url ?>" 
                         class="rounded-circle profile-image" 
                         alt="Photo de profil"
                         onerror="this.src='assets/images/default-profile.png'">
                    <div class="mt-2">
                        <label class="btn btn-outline-secondary">
                            <i class="fas fa-camera"></i> Changer la photo
                            <input type="file" name="photo" class="d-none" accept="image/*">
                        </label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Prénom(s)</label>
                            <input type="text" name="prenom" class="form-control" 
                                   value="<?= htmlspecialchars($apprenant['prenom']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" name="nom" class="form-control" 
                                   value="<?= htmlspecialchars($apprenant['nom']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($apprenant['email']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" 
                                   value="<?= htmlspecialchars($apprenant['telephone']) ?>" 
                                   pattern="^(77|78|75|70|76)[0-9]{7}$" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Adresse</label>
                            <input type="text" name="adresse" class="form-control" 
                                   value="<?= htmlspecialchars($apprenant['adresse']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Référentiel</label>
                            <select name="referentiel_id" class="form-control" required>
                                <?php foreach($referentiels as $ref): 
                                    // Récupérer le referentiel_id de l'apprenant de manière sécurisée
                                    $current_referentiel = isset($apprenant['referentiel_id']) ? $apprenant['referentiel_id'] : null;
                                ?>
                                    <option value="<?= $ref['id'] ?>" 
                                            <?= $current_referentiel === $ref['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ref['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="?page=apprenants" class="btn btn-secondary me-2">Annuler</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>