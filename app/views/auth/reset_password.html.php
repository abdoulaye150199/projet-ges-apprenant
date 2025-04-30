<h1 class="auth-title">Réinitialiser votre mot de passe</h1>

<div class="auth-description">
    Veuillez créer un nouveau mot de passe sécurisé pour votre compte.
</div>

<form class="auth-form" action="?page=reset-password-process" method="post">
    <input type="hidden" name="token" value="<?= $token ?>">
    
    <div class="form-group">
        <label for="new_password">Nouveau mot de passe</label>
        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Votre nouveau mot de passe">
        <?php if (isset($errors) && isset($errors['new_password'])): ?>
            <div class="error-message"><?= $errors['new_password'] ?></div>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="confirm_password">Confirmer le mot de passe</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirmez votre mot de passe">
        <?php if (isset($errors) && isset($errors['confirm_password'])): ?>
            <div class="error-message"><?= $errors['confirm_password'] ?></div>
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn-login">Réinitialiser le mot de passe</button>
</form>

<div class="form-footer">
    <a href="?page=login">Retour à la connexion</a>
</div>