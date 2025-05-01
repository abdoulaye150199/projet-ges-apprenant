<div class="import-container">
    <div class="card">
        <div class="card-header">
            <h2>Importer des apprenants</h2>
        </div>
        <div class="card-body">
            <div class="template-download">
                <p>Téléchargez le modèle de fichier Excel et remplissez-le avec vos données :</p>
                <a href="?page=download-template" class="btn btn-secondary">
                    <i class="fas fa-file-excel"></i> Télécharger le modèle Excel
                </a>
            </div>

            <form action="?page=import-apprenants-process" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Sélectionnez votre fichier Excel</label>
                    <div class="file-upload-wrapper">
                        <div class="file-upload-message">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Glissez-déposez votre fichier ici ou cliquez pour sélectionner</p>
                        </div>
                        <input type="file" 
                               name="file" 
                               id="file" 
                               class="custom-file-input" 
                               accept=".xlsx,.xls" 
                               required>
                    </div>
                    <small class="form-text">Formats acceptés : .xlsx, .xls</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Importer
                </button>
            </form>
        </div>
    </div>
</div>