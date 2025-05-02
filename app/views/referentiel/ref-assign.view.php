<div class="modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter des référentiels</h2>
            <a href="?page=referentiels" class="close-button">×</a>
        </div>
        
        <div class="form-group">
            <label>Sélectionner les référentiels</label>
            <form action="?page=assign-referentiels-process" method="POST">
                <div class="checkbox-group">
                    <?php foreach ($unassigned_referentiels as $ref): ?>
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="ref_<?= $ref['id'] ?>"
                                   name="referentiels[]" 
                                   value="<?= $ref['id'] ?>" 
                                   class="form-check-input">
                            <label for="ref_<?= $ref['id'] ?>">
                                <?= htmlspecialchars($ref['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="add-btn">Ajouter les référentiels</button>
            </form>
        </div>
        
        <div class="form-group">
            <label>Référentiels assignés</label>
            <div class="tags-container">
                <?php if (!empty($assigned_referentiels)): ?>
                    <?php $tags_colors = ['green', 'blue', 'purple', 'orange', 'pink']; ?>
                    <?php $i = 0; ?>
                    <?php foreach ($assigned_referentiels as $ref): ?>
                        <div class="tag-item tag-<?= $tags_colors[$i % count($tags_colors)] ?>">
                            <?= htmlspecialchars($ref['name']) ?>
                            <form action="?page=unassign-referentiel" method="POST" class="inline-form">
                                <input type="hidden" name="referentiel_id" value="<?= $ref['id'] ?>">
                                <button type="submit" class="tag-remove">×</button>
                            </form>
                        </div>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-tags">Aucun référentiel assigné</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="?page=referentiels" class="btn-terminer">Terminer</a>
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/referentiel-assign.css">