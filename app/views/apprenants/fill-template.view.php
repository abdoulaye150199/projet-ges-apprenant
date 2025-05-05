<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Remplir la liste des apprenants</h2>
            <a href="?page=apprenants" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
        </div>
        <div class="card-body">
            <!-- Formulaire pour choisir le nombre d'apprenants -->
            <form action="?page=fill-template" method="GET" class="mb-4">
                <input type="hidden" name="page" value="fill-template">
                <div class="form-group">
                    <label for="num_rows">Nombre d'apprenants à ajouter:</label>
                    <input type="number" 
                           name="num_rows" 
                           id="num_rows" 
                           class="form-control" 
                           min="1" 
                           max="50" 
                           value="<?= $num_rows ?>" 
                           required>
                    <button type="submit" class="btn btn-primary mt-2">
                        <i class="fas fa-sync"></i> Mettre à jour
                    </button>
                </div>
            </form>

            <form action="?page=fill-template-process" method="POST" enctype="multipart/form-data">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-orange text-white">
                            <tr>
                                <th>Prénom</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Date de naissance</th>
                                <th>Adresse</th>
                                <th>Photo</th>
                                <th>Référentiel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i = 0; $i < $num_rows; $i++): ?>
                                <tr>
                                    <td>
                                        <input type="text" name="apprenants[<?= $i ?>][prenom]" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="text" name="apprenants[<?= $i ?>][nom]" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="email" name="apprenants[<?= $i ?>][email]" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="text" name="apprenants[<?= $i ?>][telephone]" class="form-control" pattern="^(77|78|75|70|76)[0-9]{7}$" required>
                                    </td>
                                    <td>
                                        <input type="text" name="apprenants[<?= $i ?>][date_naissance]" class="form-control" placeholder="JJ/MM/AAAA" required>
                                    </td>
                                    <td>
                                        <input type="text" name="apprenants[<?= $i ?>][adresse]" class="form-control">
                                    </td>
                                    <td>
                                        <input type="file" name="photos[<?= $i ?>]" class="form-control" accept="image/*">
                                    </td>
                                    <td>
                                        <select name="apprenants[<?= $i ?>][referentiel_id]" class="form-control" required>
                                            <option value="">Sélectionner</option>
                                            <?php foreach($referentiels as $ref): ?>
                                                <option value="<?= $ref['id'] ?>"><?= $ref['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-orange {
    background-color: #FF7900;
}
</style>