<h1 class="auth-title">Changer votre mot de passe</h1>

<div class="auth-description">
    Pour sécuriser votre compte, veuillez changer votre mot de passe ci-dessous.
</div>

<form class="auth-form" action="?page=change-password-process" method="post">
    <div class="form-group">
        <label for="current_password">Mot de passe actuel</label>
        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Votre mot de passe actuel">
        <?php if (isset($errors) && isset($errors['current_password'])): ?>
            <div class="error-message"><?= $errors['current_password'] ?></div>
        <?php endif; ?>
    </div>
    
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
    
    <button type="submit" class="btn-login">Changer le mot de passe</button>
</form>

<div class="form-footer">
    <a href="?page=dashboard">Retour au tableau de bord</a>
</div>