<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un référentiel</title>
    <link rel="stylesheet" href="/assets/css/referentiels.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <h1>Créer un nouveau référentiel</h1>
                <a href="?page=all-referentiels" class="btn btn-back">Retour aux référentiels</a>
            </div>
        </div>

        <form class="referentiel-form" action="?page=add-referentiel-process" method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <div class="image-upload">
                    <div class="upload-preview">
                        <img src="assets/images/placeholder.png" alt="Preview" id="imagePreview">
                    </div>
                    <label for="image">
                        <i class="fas fa-camera"></i>
                        Cliquer pour ajouter une photo
                    </label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept=".jpg,.jpeg,.png">
                    <small class="file-info">Format JPG, PNG - Max 2MB</small>
                    <?php if (isset($errors['image'])): ?>
                        <span class="error-message"><?= $errors['image'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="name">Nom du référentiel*</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= htmlspecialchars($name ?? '') ?>" 
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="error-message"><?= $errors['name'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">Description*</label>
                    <textarea id="description" 
                              name="description" 
                              required 
                              rows="4"><?= htmlspecialchars($description ?? '') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <span class="error-message"><?= $errors['description'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacity">Capacité*</label>
                        <input type="number" 
                               id="capacity" 
                               name="capacity" 
                               min="1" 
                               value="<?= htmlspecialchars($capacity ?? '') ?>" 
                               required>
                        <?php if (isset($errors['capacity'])): ?>
                            <span class="error-message"><?= $errors['capacity'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="sessions">Nombre de sessions*</label>
                        <select id="sessions" name="sessions" required>
                            <option value="">Sélectionnez</option>
                            <?php for($i = 1; $i <= 4; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($sessions) && $sessions == $i) ? 'selected' : '' ?>>
                                    <?= $i ?> session<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <?php if (isset($errors['sessions'])): ?>
                            <span class="error-message"><?= $errors['sessions'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le référentiel</button>
                </div>
            </div>
        </form>
    </div>

    <script src="/assets/js/referentiel.js"></script>
</body>
</html>