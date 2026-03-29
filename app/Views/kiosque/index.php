<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold mb-2">
                <i class="bi bi-clock fs-1 me-3"></i>
                Kiosque de pointage
            </h1>
            <p class="text-white-50">Registre électronique des présences</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-5">
                        <!-- Horloge -->
                        <div class="text-center mb-5">
                            <div class="display-4 fw-bold mb-2" id="current-time">-- : --</div>
                            <div class="text-muted" id="current-date">--</div>
                        </div>

                        <!-- Formulaire -->
                        <form id="kiosque-form" class="mb-4">
                            <?= csrf_field() ?>

                            <div class="mb-4">
                                <label for="employe" class="form-label fw-bold">Sélectionnez votre identité</label>
                                <div class="input-group input-group-lg">
                                    <input type="hidden" id="employe_id" name="employe_id">
                                    <input type="text" class="form-control" id="employe" autocomplete="off"
                                           placeholder="Chercher par matricule, prénom ou nom..." required>
                                    <button class="btn btn-outline-secondary" type="button" id="search-btn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="search-results" class="dropdown-menu w-100 mt-1" style="display: none; position: static;">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="pin" class="form-label fw-bold">PIN (4 chiffres)</label>
                                <input type="password" class="form-control form-control-lg text-center"
                                       id="pin" name="pin" pattern="[0-9]{4}" maxlength="4"
                                       placeholder="• • • •" inputmode="numeric" required>
                                <small class="d-block text-muted mt-2">Entrez votre PIN à 4 chiffres</small>
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="button" class="btn btn-success btn-lg" id="btn-arrivee">
                                    <i class="bi bi-arrow-down-circle me-2"></i>
                                    Pointer l'arrivée
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" id="btn-depart">
                                    <i class="bi bi-arrow-up-circle me-2"></i>
                                    Pointer le départ
                                </button>
                            </div>

                            <div id="confirmation" class="alert d-none" role="alert">
                                <div id="confirmation-text"></div>
                            </div>

                            <button type="button" class="btn btn-link w-100" onclick="resetForm()">
                                Réinitialiser
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-white-50 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Assurez-vous de pointer votre arrivée le matin et votre départ en fin de journée.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Horloge en temps réel
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = 
        String(now.getHours()).padStart(2, '0') + ':' + 
        String(now.getMinutes()).padStart(2, '0');
    
    document.getElementById('current-date').textContent = 
        now.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

setInterval(updateTime, 1000);
updateTime();

// Recherche d'employé
const employe = document.getElementById('employe');
const searchResults = document.getElementById('search-results');

employe.addEventListener('input', async function() {
    const query = this.value;

    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`<?= site_url('kiosque/search') ?>?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.results && data.results.length > 0) {
            searchResults.innerHTML = '';

            data.results.forEach(emp => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'dropdown-item';
                item.textContent = `${emp.prenom} ${emp.nom} (${emp.matricule})`;
                item.onclick = (e) => {
                    e.preventDefault();
                    selectEmployee(emp.id, emp.prenom + ' ' + emp.nom);
                };
                searchResults.appendChild(item);
            });

            searchResults.style.display = 'block';
        } else {
            searchResults.innerHTML = '<div class="dropdown-item disabled">Aucun employé trouvé</div>';
            searchResults.style.display = 'block';
        }
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
});

function selectEmployee(id, name) {
    document.getElementById('employe_id').value = id;
    document.getElementById('employe').value = name;
    searchResults.style.display = 'none';
    document.getElementById('pin').focus();
}

// Pointage
async function submitPointage(type) {
    const employeId = document.getElementById('employe_id').value;
    const pin = document.getElementById('pin').value;

    if (!employeId) {
        showMessage('Veuillez sélectionner un employé', 'danger');
        return;
    }

    if (!pin || pin.length !== 4 || isNaN(pin)) {
        showMessage('PIN invalide (4 chiffres requis)', 'danger');
        return;
    }

    const url = type === 'arrivee' ? 
        '<?= site_url('kiosque/pointage-arrivee') ?>' : 
        '<?= site_url('kiosque/pointage-depart') ?>';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `employe_id=${employeId}&pin=${encodeURIComponent(pin)}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
        });

        const data = await response.json();

        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => resetForm(), 3000);
        } else {
            showMessage(data.message, 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showMessage('Erreur de connexion', 'danger');
    }
}

function showMessage(text, type) {
    const confirmation = document.getElementById('confirmation');
    const confirmationText = document.getElementById('confirmation-text');

    confirmation.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'd-none');
    confirmation.classList.add(`alert-${type}`);
    confirmationText.innerHTML = text;

    setTimeout(() => {
        confirmation.classList.add('d-none');
    }, 5000);
}

function resetForm() {
    document.getElementById('kiosque-form').reset();
    document.getElementById('employe_id').value = '';
    document.getElementById('confirmation').classList.add('d-none');
    document.getElementById('employe').focus();
}

// Event listeners
document.getElementById('btn-arrivee').addEventListener('click', () => submitPointage('arrivee'));
document.getElementById('btn-depart').addEventListener('click', () => submitPointage('depart'));

// Focus on PIN field
employe.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        document.getElementById('pin').focus();
    }
});

// Submit on PIN enter
document.getElementById('pin').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        submitPointage('arrivee');
    }
});
</script>