<div class="student-details">
    <div class="card">
        <div class="header-section">
            <div class="actions-top">
                <a href="?page=apprenants" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
            <h2 class="import-title">Importer des apprenants</h2>
        </div>
        
        <div class="import-section">
            <form action="?page=import-apprenants-process" method="POST" enctype="multipart/form-data">
                <div class="upload-zone">
                    <i class="fas fa-file-excel"></i>
                    <p>Déposez votre fichier Excel ici ou</p>
                    <label class="upload-btn">
                        Choisir un fichier
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required>
                    </label>
                    <p class="file-info">Formats acceptés: .xlsx, .xls</p>
                </div>

                <?php if (isset($import_errors) && !empty($import_errors)): ?>
                    <div class="alert alert-danger mt-3">
                        <h5><i class="fas fa-exclamation-triangle"></i> Erreurs d'importation:</h5>
                        <ul>
                            <?php foreach ($import_errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success" >
                        <i class="fas fa-upload"></i> Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>