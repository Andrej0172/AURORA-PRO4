<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="account-auth-wrapper">
    <div class="account-auth-card">
        <h1>Inloggen</h1>
        <p class="account-auth-subtitle">Log in op je Aurora Theater account.</p>

        <?php if (!empty($data['foutmelding'])) : ?>
            <div class="account-alert account-alert-error"><?= htmlspecialchars($data['foutmelding']); ?></div>
        <?php endif; ?>

        <form action="<?= URLROOT; ?>AccountsController/login" method="POST" class="account-form">
            <div class="form-groep">
                <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars(isset($data['email']) ? $data['email'] : ''); ?>" placeholder="naam@voorbeeld.nl">
            </div>

            <div class="form-groep">
                <label for="wachtwoord">Wachtwoord</label>
                <div class="wachtwoord-wrapper">
                    <input type="password" id="wachtwoord" name="wachtwoord" required placeholder="Je wachtwoord">
                    <button type="button" class="wachtwoord-toggle" id="wachtwoordToggle" aria-label="Wachtwoord tonen">
                        <svg class="oog-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="oog-dicht" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-groep onthoud-mij">
                <label>
                    <input type="checkbox" name="onthoud_mij" value="1">
                    Onthoud mij
                </label>
            </div>

            <button type="submit" class="btn btn-primary login-knop">Inloggen</button>
        </form>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>


