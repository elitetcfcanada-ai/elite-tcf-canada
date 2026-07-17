
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const taskButtons = document.querySelectorAll('.task-btn');
    const sections = document.querySelectorAll('.task-section');
    
    // Fonction pour changer de section
    function switchSection(targetId) {
        // Mettre à jour les boutons
        taskButtons.forEach(btn => {
            if (btn.getAttribute('data-target') === targetId) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Afficher la section cible et masquer les autres
        sections.forEach(section => {
            if (section.id === 'section-' + targetId) {
                section.classList.add('active');
            } else {
                section.classList.remove('active');
            }
        });
    }
    
    // Ajouter les écouteurs d'événements aux boutons
    taskButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            switchSection(targetId);
        });
    });
    
    // Ajouter des animations aux cartes de tâches
    const taskCards = document.querySelectorAll('.task-card');
    
    taskCards.forEach((card, index) => {
        // Animation d'apparition séquentielle
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
        
        // Effet au survol amélioré
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = '0 12px 20px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
});