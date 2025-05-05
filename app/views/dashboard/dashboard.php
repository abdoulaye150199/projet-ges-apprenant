<?php
$userName = htmlspecialchars($user['name'] ?? 'Utilisateur');
?>

<div class="dashboard-header">
    <h1>Bienvenue, <?= $userName ?></h1>
</div>

