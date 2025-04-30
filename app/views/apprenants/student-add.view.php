<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="text-center">Ajout apprenant</h3>
        </div>
        <div class="card-body">
            <form action="?page=add-apprenant-process" method="POST" enctype="multipart/form-data">
                
                <!-- Informations de l'apprenant -->
                <div class="section-title mb-4">
                    <h4 class="d-flex align-items-center">
                        <i class="fas fa-user me-2"></i>
                        Informations de l'apprenant
                    </h4>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Prénom(s)</label>
                            <input type="text" name="prenom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Nom</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Date de naissance</label>
                            <input type="text" name="date_naissance" class="form-control" placeholder="JJ/MM/AAAA" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Adresse</label>
                            <input type="text" name="adresse" class="form-control" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Téléphone</label>
                            <input type="text" name="telephone" class="form-control" required>
                        </div>
                    </div>

                    
                        
                        <div class="form-group mb-3">
                            <label>Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" class="form-control" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Photo</label>
                            <div class="upload-photo-container">
                                <input type="file" name="photo" id="photoInput" class="photo-input" accept="image/*">
                                <div class="upload-photo-content" id="uploadArea">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="upload-text">
                                        <span class="main-text">Glissez et déposez votre photo</span>
                                        <span class="sub-text">ou</span>
                                        <button type="button" class="btn-browse" id="uploadBtn">Parcourir</button>
                                    </div>
                                    <div class="file-info" id="fileInfo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations du tuteur -->
                <div class="section-title mb-4">
                    <h4 class="d-flex align-items-center">
                        <i class="fas fa-user-tie me-2"></i>
                        Informations du tuteur
                    </h4>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Prénom & Nom</label>
                            <input type="text" name="tuteur_nom" class="form-control">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Adresse</label>
                            <input type="text" name="tuteur_adresse" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Lien de parenté</label>
                            <input type="text" name="tuteur_lien" class="form-control">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Téléphone</label>
                            <input type="text" name="tuteur_telephone" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4 action-buttons">
                    <a href="?page=apprenants" class="btn btn-warning me-2">Annuler</a>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadBtn').addEventListener('click', function() {
    document.getElementById('photoInput').click();
});

document.getElementById('photoInput').addEventListener('change', function() {
    const fileName = this.files[0]?.name;
    if (fileName) {
        document.getElementById('uploadBtn').textContent = fileName;
    }
});
</script>
