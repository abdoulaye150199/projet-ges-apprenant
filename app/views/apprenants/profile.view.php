    <!-- Grille des statistiques -->
    <div class="card-row">
        <!-- Carte des présences -->
        <div class="card">
            <div class="card-title">
                <div class="card-icon orange-icon">📊</div>
                <span>Présences</span>
            </div>
            <div class="presence-stats">
                <div class="stat-item present-bg">
                    <div class="stat-number present">40</div>
                    <div class="stat-label">Présent</div>
                </div>
                <div class="stat-item retard-bg">
                    <div class="stat-number retard">7</div>
                    <div class="stat-label">Retard</div>
                </div>
                <div class="stat-item absent-bg">
                    <div class="stat-number absent">0</div>
                    <div class="stat-label">Absent</div>
                </div>
            </div>
        </div>

        <!-- Graphique de répartition -->
        <div class="card">
            <div class="card-title">
                <div class="card-icon orange-icon">⏱️</div>
                <span>Répartition</span>
            </div>
            <div class="chart-container">
                <svg class="donut-chart" width="150" height="150" viewBox="0 0 150 150">
                    <circle cx="75" cy="75" r="60" fill="transparent" 
                            stroke="#28a745" stroke-width="20" 
                            stroke-dasharray="330 377" stroke-dashoffset="-47"/>
                    <circle cx="75" cy="75" r="60" fill="transparent" 
                            stroke="#ffc107" stroke-width="20" 
                            stroke-dasharray="47 377" stroke-dashoffset="0"/>
                </svg>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color" style="background: #28a745"></span>
                    <span>Présents</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #ffc107"></span>
                    <span>Retards</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #dc3545"></span>
                    <span>Absents</span>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="card">
            <div class="qr-section">
                <div class="qr-title">Scanner pour la présence</div>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $apprenant['matricule'] ?>" 
                     alt="QR Code" 
                     class="qr-code">
                <div class="qr-subtitle">Code de présence personnel</div>
                <div class="qr-id"><?= htmlspecialchars($apprenant['matricule']) ?></div>
            </div>
        </div>
    </div>

    <!-- Historique des présences -->
    <div class="card">
        <div class="card-title">
            <div class="card-icon orange-icon">🕒</div>
            <span>Historique de présence</span>
        </div>

        <div class="search-filters">
            <input type="text" class="search-input" placeholder="Rechercher...">
            <div class="filter-dropdown">
                <button class="filter-button">Tous les statuts</button>
            </div>
        </div>

        <div class="presence-history">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Exemple d'entrées -->
                    <tr>
                        <td>28/02/2023 07:43:06</td>
                        <td><span class="status-badge">PRESENT</span></td>
                    </tr>
                    <!-- ... autres entrées ... -->
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">
                Affichage de 1 à 5 sur 40 entrées
            </div>
            <div class="pagination-controls">
                <button class="pagination-button active">1</button>
                <button class="pagination-button">2</button>
                <button class="pagination-button">3</button>
                <button class="pagination-button">4</button>
                <button class="pagination-button">5</button>
                <button class="pagination-button">›</button>
            </div>
        </div>
    </div>
</div>