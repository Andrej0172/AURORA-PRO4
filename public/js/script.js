// Kleine UI-interacties die op meerdere pagina's terugkomen.
document.addEventListener('DOMContentLoaded', function () {
    const menuKnop = document.querySelector('.menu-knop');
    const nav = document.querySelector('.nav');

    if (menuKnop && nav) {
        // Mobiel menu open/dicht zetten.
        menuKnop.addEventListener('click', function () {
            nav.classList.toggle('menu-open');
            menuKnop.classList.toggle('active');
        });

        // Sluit menu bij klik op een link
        document.querySelectorAll('.nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('menu-open');
                menuKnop.classList.remove('active');
            });
        });
    }

    // Profiel dropdown
    var profielKnop = document.getElementById('profielKnop');
    var profielDropdown = document.getElementById('profielDropdown');

    if (profielKnop && profielDropdown) {
        // Dropdown blijft open tot je buiten het menu klikt.
        profielKnop.addEventListener('click', function (e) {
            e.stopPropagation();
            profielDropdown.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (!profielDropdown.contains(e.target) && e.target !== profielKnop) {
                profielDropdown.classList.remove('open');
            }
        });
    }

    // Wachtwoord oogje toggle
    var wachtwoordToggle = document.getElementById('wachtwoordToggle');
    var wachtwoordInput = document.getElementById('wachtwoord');

    if (wachtwoordToggle && wachtwoordInput) {
        // Wissel inputtype + icoon zodat gebruiker het wachtwoord kan controleren.
        wachtwoordToggle.addEventListener('click', function () {
            var isPassword = wachtwoordInput.type === 'password';
            wachtwoordInput.type = isPassword ? 'text' : 'password';

            var oogOpen = wachtwoordToggle.querySelector('.oog-open');
            var oogDicht = wachtwoordToggle.querySelector('.oog-dicht');

            oogOpen.style.display = isPassword ? 'none' : 'block';
            oogDicht.style.display = isPassword ? 'block' : 'none';
        });
    }
});

