<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-wrench-adjustable-circle"></i>
                Bibliotheque de shifts
            </span>
            <h1 class="page-hero-title mb-2"><?= esc($title) ?></h1>
            <p class="page-hero-copy mb-0">Creez, ajustez et activez les modeles horaires utilises dans le planning hebdomadaire.</p>
        </div>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="bi bi-plus-circle me-2"></i>Nouveau shift
        </button>
    </div>
</section>

<div class="card shadow-sm border-0">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h5 mb-0">Liste des shifts</h2>
        <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2"><?= count($shifts) ?> ligne(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Horaires</th>
                    <th>Description</th>
                    <th class="text-center">Pause</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shifts as $shift): ?>
                    <tr>
                        <td><strong><?= esc($shift['nom']) ?></strong></td>
                        <td>
                            <span class="badge rounded-pill text-bg-light border text-info-emphasis">
                                <?= date('H:i', strtotime($shift['heure_debut'])) ?> - <?= date('H:i', strtotime($shift['heure_fin'])) ?>
                            </span>
                        </td>
                        <td><small><?= esc($shift['description'] ?? '-') ?></small></td>
                        <td class="text-center"><small><?= isset($shift['pause_minutes']) ? (int) $shift['pause_minutes'] . ' min' : '-' ?></small></td>
                        <td class="text-center">
                            <?php if (!empty($shift['actif'])): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group" aria-label="Actions shift">
                                <button class="btn btn-sm btn-light" onclick="editShift(<?= $shift['id'] ?>, '<?= addslashes($shift['nom']) ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-light" onclick="deleteShift(<?= $shift['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nouveau Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="shiftForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="shiftId">
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label class="form-label">Nom du Shift</label>
                        <input type="text" name="nom" id="nom" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Heure de Début</label>
                            <input type="time" name="heure_debut" id="heure_debut" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Heure de Fin</label>
                            <input type="time" name="heure_fin" id="heure_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Heure de Début Tolérance</label>
                        <input type="time" name="heure_debut_tolerence" id="heure_debut_tolerence" class="form-control">
                        <small class="text-muted">Heure avant laquelle pas considéré retard</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pauses (description)</label>
                        <input type="text" name="pauses" id="pauses" class="form-control" 
                               placeholder="Ex: 12:00-13:00 (1h), 15:30-15:45 (15min)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentShiftId = null;

function showCreateModal() {
    currentShiftId = null;
    document.getElementById('modalTitle').textContent = 'Nouveau Shift';
    document.getElementById('shiftForm').reset();
    document.getElementById('shiftId').value = '';
    document.getElementById('shiftForm').action = '<?= base_url('/admin/planning/shifts?action=create') ?>';
    new bootstrap.Modal(document.getElementById('shiftModal')).show();
}

function editShift(shiftId, shiftName) {
    currentShiftId = shiftId;
    document.getElementById('modalTitle').textContent = 'Modifier: ' + shiftName;
    
    // Load shift data via AJAX
    fetch(`<?= base_url('/api/shifts/') ?>${shiftId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const shift = data.data;
                document.getElementById('shiftId').value = shift.id;
                document.getElementById('nom').value = shift.nom;
                document.getElementById('description').value = shift.description || '';
                document.getElementById('heure_debut').value = shift.heure_debut;
                document.getElementById('heure_fin').value = shift.heure_fin;
                document.getElementById('heure_debut_tolerence').value = shift.heure_debut_tolerence || '';
                document.getElementById('pauses').value = shift.pause_minutes || '';
                document.getElementById('shiftForm').action = '<?= base_url('/admin/planning/shifts?action=edit') ?>';
                new bootstrap.Modal(document.getElementById('shiftModal')).show();
            }
        });
}

function deleteShift(shiftId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce shift?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('/admin/planning/shifts?action=delete') ?>';
        form.innerHTML = `
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="${shiftId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
