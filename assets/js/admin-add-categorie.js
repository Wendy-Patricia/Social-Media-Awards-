        // Gestion de la sélection rapide des plateformes
        document.querySelectorAll('.platform-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                // Retirer la sélection de tous les badges
                document.querySelectorAll('.platform-badge').forEach(b => {
                    b.classList.remove('selected');
                });
                
                // Ajouter la sélection au badge cliqué
                this.classList.add('selected');
                
                // Mettre à jour le select
                const select = document.getElementById('plateforme_cible');
                select.value = this.dataset.value;
            });
        });
        
        // Preview de l'image
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '150px';
                    img.style.marginTop = '10px';
                    img.style.borderRadius = '5px';
                    img.style.border = '2px solid #4FBDAB';
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Validation des dates
        const dateDebut = document.getElementById('date_debut_votes');
        const dateFin = document.getElementById('date_fin_votes');
        
        if (dateDebut && dateFin) {
            dateDebut.addEventListener('change', function() {
                if (this.value && dateFin.value && this.value > dateFin.value) {
                    dateFin.value = this.value;
                }
            });
            
            dateFin.addEventListener('change', function() {
                if (this.value && dateDebut.value && this.value < dateDebut.value) {
                    dateDebut.value = this.value;
                }
            });
        }
        
        // Mettre la date minimale à aujourd'hui
        const today = new Date().toISOString().slice(0, 16);
        if (dateDebut) {
            dateDebut.min = today;
        }
        if (dateFin) {
            dateFin.min = today;
        }

        // Fonctions pour les boutons d'incrémentation/décrémentation
        function incrementValue(inputId) {
            const input = document.getElementById(inputId);
            let value = parseInt(input.value) || 0;
            const max = parseInt(input.max) || 100;
            if (value < max) {
                input.value = value + 1;
                updateLimitIndicator();
            }
        }

        function decrementValue(inputId) {
            const input = document.getElementById(inputId);
            let value = parseInt(input.value) || 0;
            const min = parseInt(input.min) || 1;
            if (value > min) {
                input.value = value - 1;
                updateLimitIndicator();
            }
        }

        // Gestion des suggestions de limite
        document.querySelectorAll('.suggestion-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                document.getElementById('limite_nomines').value = value;
                updateLimitIndicator();
            });
        });

        // Mise à jour de l'indicateur de limite
        function updateLimitIndicator() {
            const input = document.getElementById('limite_nomines');
            const value = parseInt(input.value) || 10;
            const fill = document.getElementById('limitFill');
            const text = document.getElementById('limitText');
            
            // Calculer le pourcentage (1-50 => 0-100%)
            const percentage = Math.min((value / 50) * 100, 100);
            
            // Mettre à jour la barre
            fill.style.width = percentage + '%';
            
            // Mettre à jour le texte
            text.textContent = 'Limite: ' + value + ' nommés';
            
            // Changer la couleur selon la valeur
            if (value <= 5) {
                fill.style.background = '#4FBDAB'; // Teal
                text.style.color = '#4FBDAB';
            } else if (value <= 15) {
                fill.style.background = '#FFD166'; // Gold
                text.style.color = '#FFD166';
            } else {
                fill.style.background = '#FF5A79'; // Coral
                text.style.color = '#FF5A79';
            }
        }

        // Initialiser l'indicateur
        updateLimitIndicator();

        // Mettre à jour l'indicateur quand la valeur change
        document.getElementById('limite_nomines').addEventListener('input', updateLimitIndicator);
        document.getElementById('limite_nomines').addEventListener('change', updateLimitIndicator);

        // Validation du formulaire
        document.getElementById('createCategoryForm').addEventListener('submit', function(e) {
            const limiteNomines = document.getElementById('limite_nomines');
            const ordreAffichage = document.getElementById('ordre_affichage');
            
            // Validation de la limite de nommés
            if (limiteNomines.value < 1 || limiteNomines.value > 50) {
                e.preventDefault();
                alert('La limite de nommés doit être entre 1 et 50.');
                limiteNomines.focus();
                return false;
            }
            
            // Validation de l'ordre d'affichage
            if (ordreAffichage.value < 1) {
                e.preventDefault();
                alert('L\'ordre d\'affichage doit être au moins 1.');
                ordreAffichage.focus();
                return false;
            }
            
            return true;
        });