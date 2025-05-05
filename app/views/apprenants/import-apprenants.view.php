<div class="import-container">
    <div class="card">
        <div class="card-header">
            <h2>Importer des apprenants</h2>
        </div>
        <div class="card-body">
            <div class="template-download mb-4">
                <p>Remplissez directement la liste des apprenants :</p>
                <a href="?page=fill-template" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Remplir la liste des apprenants
                </a>
            </div>

            <form action="?page=import-apprenants-process" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Fichier Excel</label>
                    <input type="file" 
                           name="file" 
                           id="file" 
                           class="form-control" 
                           accept=".xlsx,.xls" 
                           required>
                    <small class="form-text text-muted">Formats acceptés : .xlsx, .xls</small>
                </div>
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-upload"></i> Importer
                </button>
            </form>
        </div>
    </div>
</div>