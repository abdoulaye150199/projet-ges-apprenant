<h1 class="auth-title">Mot de passe oublié</h1>

<div class="auth-description">
    Veuillez saisir votre adresse email. Nous vous enverrons un lien pour réinitialiser votre mot de passe.
</div>

<form class="auth-form" action="?page=forgot-password-process" method="post">
    <div class="form-group">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="Votre adresse email" value="<?= isset($email) ? $email : '' ?>">
        <?php if (isset($errors) && isset($errors['email'])): ?>
            <div class="error-message"><?= $errors['email'] ?></div>
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn-login">Envoyer le lien</button>
</form>

<div class="form-footer">
    <a href="?page=login">Retour à la connexion</a>
</div>