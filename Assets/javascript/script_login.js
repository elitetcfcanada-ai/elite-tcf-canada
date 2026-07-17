// script_login.js
const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

// Basculer entre connexion et inscription
registerBtn.addEventListener('click', () => {
    container.classList.add('active');
    // Réinitialiser les messages d'erreur
    clearErrorMessages();
});

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
    // Réinitialiser les messages d'erreur
    clearErrorMessages();
});

/** Aligné sur includes/user_validation.php (tcf_validate_password_strength). */
function tcfPasswordMeetsStrength(pw) {
    if (!pw || pw.length < 8) return false;
    if (!/[A-Z]/.test(pw)) return false;
    if (!/[a-z]/.test(pw)) return false;
    if (!/\d/.test(pw)) return false;
    try {
        return /[^\p{L}\p{N}]/u.test(pw);
    } catch (err) {
        return /[^A-Za-z0-9]/.test(pw);
    }
}

// Validation côté client du formulaire de connexion
loginForm.addEventListener('submit', function(e) {
    const email = document.getElementById('loginEmail');
    const password = document.getElementById('loginPassword');
    let isValid = true;

    clearErrorStyles();

    if (!email.value.includes('@') || !email.value.includes('.')) {
        e.preventDefault();
        showError('loginEmail', 'Veuillez entrer un email valide');
        isValid = false;
    }

    if (password.value.length === 0) {
        e.preventDefault();
        showError('loginPassword', 'Le mot de passe est obligatoire');
        isValid = false;
    }

    if (isValid) {
        showLoading('loginForm');
    }
});

// Validation côté client du formulaire d'inscription
registerForm.addEventListener('submit', function(e) {
    const nameField = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    let isValid = true;
    
    // Réinitialiser les styles d'erreur
    clearErrorStyles();
    
    // Validation du nom
    if (!nameField || nameField.value.length < 4) {
        e.preventDefault();
        showError('name', 'Minimum 4 caractères');
        isValid = false;
    }
    
    // Validation de l'email
    if (!email.value.includes('@') || !email.value.includes('.')) {
        e.preventDefault();
        showError('email', 'Email invalide');
        isValid = false;
    }
    
    if (!tcfPasswordMeetsStrength(password.value)) {
        e.preventDefault();
        showError('password', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un symbole');
        isValid = false;
    }
    
    // Confirmation du mot de passe
    if (password.value !== confirmPassword.value) {
        e.preventDefault();
        showError('confirmPassword', 'Les mots de passe ne correspondent pas');
        isValid = false;
    }
    
    // Si validation OK, le formulaire se soumet normalement
    if (isValid) {
        showLoading('registerForm');
        // Ne pas empêcher la soumission - laisser le formulaire se soumettre vers le PHP
    }
});

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const box = field.closest('.input-box');
    let errorElement = box ? box.querySelector('.error-message') : null;
    if (!errorElement && box) {
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        errorElement.setAttribute('role', 'alert');
        box.appendChild(errorElement);
    }
    if (!errorElement) return;

    errorElement.textContent = message;
    errorElement.style.display = 'block';
    field.style.border = '1px solid var(--main-color)';
    field.style.backgroundColor = '#ffe6e6';

    field.addEventListener('input', function onClear() {
        errorElement.style.display = 'none';
        field.style.border = 'none';
        field.style.backgroundColor = '#eee';
    }, { once: true });
}

// Réinitialiser tous les messages d'erreur
function clearErrorMessages() {
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(error => {
        error.style.display = 'none';
    });
}

// Réinitialiser les styles d'erreur
function clearErrorStyles() {
    const inputs = document.querySelectorAll('.input-box input');
    inputs.forEach(input => {
        input.style.border = 'none';
        input.style.backgroundColor = '#eee';
    });
}

// Afficher un indicateur de chargement
function showLoading(formId) {
    const form = document.getElementById(formId);
    const submitButton = form.querySelector('.btn');
    const originalText = submitButton.textContent;
    
    // Changer le texte pour indiquer le traitement
    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Traitement...';
    submitButton.style.opacity = '0.8';
    
    // Ne pas désactiver le bouton pour permettre la soumission du formulaire
    // Réactiver après 5 secondes maximum (au cas où la redirection échoue)
    setTimeout(() => {
        submitButton.textContent = originalText;
        submitButton.style.opacity = '1';
    }, 5000);
}

// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    // Validation de l'email en temps réel
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (!this.value.includes('@') || !this.value.includes('.')) {
                showError('email', 'Email invalide');
            }
        });
    }
    
    // Validation du mot de passe en temps réel
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0 && !tcfPasswordMeetsStrength(this.value)) {
                showError('password', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un symbole');
            }
        });
    }
    
    // Confirmation du mot de passe en temps réel
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordField = document.getElementById('password');
    if (confirmPasswordInput && passwordField) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordField.value) {
                showError('confirmPassword', 'Les mots de passe ne correspondent pas');
            }
        });
    }
});

// Fonction pour basculer entre les formulaires depuis d'autres scripts
function showLoginForm() {
    container.classList.remove('active');
    clearErrorMessages();
}

function showRegisterForm() {
    container.classList.add('active');
    clearErrorMessages();
}

// Exposer les fonctions globalement
window.showLoginForm = showLoginForm;
window.showRegisterForm = showRegisterForm;