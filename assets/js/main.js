// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Image Preview for Car Upload
    const imageUpload = document.getElementById('car-images');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageUpload && imagePreview) {
        imageUpload.addEventListener('change', function(event) {
            imagePreview.innerHTML = '';
            const files = event.target.files;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) continue;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-thumbnail';
                    imagePreview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Search Form Submission
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            window.location.href = `search.php?${params}`;
        });
    }

    // Confirm before deleting
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });

    // Make Model Dependency
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');
    
    if (makeSelect && modelSelect) {
        const makeModels = {
            'Toyota': ['Corolla', 'Camry', 'Rav4', 'Highlander', 'Prius'],
            'Honda': ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey'],
            'Ford': ['F-150', 'Escape', 'Explorer', 'Mustang', 'Focus'],
            'Chevrolet': ['Silverado', 'Equinox', 'Tahoe', 'Camaro', 'Malibu'],
            'Nissan': ['Altima', 'Rogue', 'Sentra', 'Pathfinder', 'Maxima']
        };
        
        makeSelect.addEventListener('change', function() {
            const selectedMake = this.value;
            modelSelect.innerHTML = '<option value="">Select Model</option>';
            
            if (selectedMake && makeModels[selectedMake]) {
                makeModels[selectedMake].forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    modelSelect.appendChild(option);
                });
            }
        });
    }
});
