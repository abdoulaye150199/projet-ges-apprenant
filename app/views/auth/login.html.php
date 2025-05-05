<div id="bouldof">
    <h1 class="auth-title">Se connecter</h1>

    <form class="auth-form" action="?page=login-process" method="post">
        <div class="form-group">
            <label for="login">Login</label>
            <input type="text" 
                   id="login" 
                   name="login" 
                   class="form-control" 
                   placeholder="Matricule ou Email" 
                   value="<?= isset($login) ? htmlspecialchars($login) : '' ?>">
            <?php if (isset($errors['login'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['login']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control" 
                   placeholder="Mot de passe">
            <?php if (isset($errors['password'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="btn-login">Se connecter</button>

        <div class="form-footer">
            <a href="?page=forgot-password" class="forgot-password-link">Mot de passe oublié ?</a>
        </div>
    </form>
</div>