<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($errors['general']) ?>
    </div>
<?php endif; ?>

<?php if (isset($flash) && $flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= $flash['message'] ?>
    </div>
<?php endif; ?>
<div class="container">
    <div class="header">
        <div class="header-title">
            <h1>Créer une nouvelle promotion</h1>
            <a href="?page=promotions" class="btn btn-back">Retour aux promotions</a>
        </div>
    </div>

    <form class="promotion-form" action="?page=add-promotion-process" method="POST" enctype="multipart/form-data">
        <div class="form-section">
            <div class="form-group">
                <label for="promotion-name">Nom de la promotion*</label>
                <input type="text" 
                       id="promotion-name" 
                       name="name" 
                       placeholder="Ex: Promotion 2025" 
                       value="<?= htmlspecialchars($name ?? '') ?>">
                <?php if (isset($errors['name'])): ?>
                    <span class="error-message"><?= $errors['name'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_debut">Date de début (JJ-MM-AAAA)*</label>
                    <input type="text" 
                           id="date_debut" 
                           name="date_debut" 
                           placeholder="15-01-2025"
                           value="<?= htmlspecialchars($date_debut ?? '') ?>">
                    <?php if (isset($errors['date_debut'])): ?>
                        <span class="error-message">
                            <?= htmlspecialchars($errors['date_debut']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="date_fin">Date de fin (JJ-MM-AAAA)*</label>
                    <input type="text" 
                           id="date_fin" 
                           name="date_fin" 
                           placeholder="31-12-2025"
                           value="<?= htmlspecialchars($date_fin ?? '') ?>">
                    <?php if (isset($errors['date_fin'])): ?>
                        <span class="error-message">
                            <?= htmlspecialchars($errors['date_fin']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Photo de la promotion*</label>
                <input type="file" 
                       name="image" 
                       accept="image/png,image/jpeg">
                <p class="file-restrictions">Format JPG, PNG. Taille max 2MB</p>
                <?php if (isset($errors['image'])): ?>
                    <span class="error-message"><?= $errors['image'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Référentiels disponibles</label>
                <div class="referentiels-list">
                    <?php foreach ($referentiels as $ref): ?>
                        <div class="referentiel-item">
                            <input type="checkbox" 
                                   name="referentiels[]" 
                                   value="<?= $ref['id'] ?>" 
                                   id="ref_<?= $ref['id'] ?>"
                                   <?= (isset($selected_referentiels) && in_array($ref['id'], $selected_referentiels)) ? 'checked' : '' ?>>
                            <label for="ref_<?= $ref['id'] ?>">
                                <?= htmlspecialchars($ref['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['referentiels'])): ?>
                    <span class="error-message"><?= $errors['referentiels'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-buttons">
                <a href="?page=promotions" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Créer la promotion</button>
            </div>
        </div>
    </form>
</div>