/**
 * FiguSphere - Main JavaScript for UI Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Image Preview for File Upload Form
    const imageInput = document.getElementById('foto_figure');
    const imagePreviewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const uploadPlaceholder = document.getElementById('upload-placeholder');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function () {
                    if (imagePreview) {
                        imagePreview.setAttribute('src', this.result);
                        if (imagePreviewContainer) {
                            imagePreviewContainer.classList.remove('hidden');
                        }
                        if (uploadPlaceholder) {
                            uploadPlaceholder.classList.add('hidden');
                        }
                    }
                });
                
                reader.readAsDataURL(file);
            }
        });
    }

    // Auto-close alert notifications after 4 seconds
    const alerts = document.querySelectorAll('.alert-box');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 4000);
    });
});

// 2. Custom Delete Confirmation Modal Logic
function openDeleteModal(deleteUrl, figureName) {
    const modal = document.getElementById('delete-modal');
    const deleteBtn = document.getElementById('confirm-delete-btn');
    const figureNameSpan = document.getElementById('delete-figure-name');

    if (modal && deleteBtn && figureNameSpan) {
        figureNameSpan.textContent = figureName;
        deleteBtn.setAttribute('href', deleteUrl);
        
        // Show modal with animation
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Add zoom-in effect to the inner card
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }
        
        // Disable body scroll when modal is open
        document.body.classList.add('overflow-hidden');
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) {
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
        }
        
        // Delay hiding the modal to allow transition to finish
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }, 200);
    }
}

// 3. Custom Image Lightbox Modal Logic
function openImageModal(imgSrc, figureName, character, series, price, scale, brand) {
    const modal = document.getElementById('image-lightbox-modal');
    if (!modal) return;

    // Fill elements
    document.getElementById('lightbox-img').src = imgSrc;
    document.getElementById('lightbox-img').alt = figureName;
    document.getElementById('lightbox-brand').textContent = brand;
    document.getElementById('lightbox-series').textContent = series;
    document.getElementById('lightbox-name').textContent = figureName;
    document.getElementById('lightbox-char').textContent = character;
    document.getElementById('lightbox-scale').textContent = scale || 'Non-scale';
    document.getElementById('lightbox-price').textContent = price;

    // Show modal with animation
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        const innerCard = modal.querySelector('div');
        if (innerCard) {
            innerCard.classList.remove('scale-95', 'opacity-0');
            innerCard.classList.add('scale-100', 'opacity-100');
        }
    }, 10);

    document.body.classList.add('overflow-hidden');
}

function closeImageModal(event) {
    // If event is passed, only close if click is on the backdrop wrapper directly
    if (event && event.target !== document.getElementById('image-lightbox-modal')) {
        return;
    }
    
    const modal = document.getElementById('image-lightbox-modal');
    if (!modal) return;

    const innerCard = modal.querySelector('div');
    if (innerCard) {
        innerCard.classList.remove('scale-100', 'opacity-100');
        innerCard.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }, 200);
}

// 4. Quick Account Switcher Toggle Logic (Mobile-friendly Click to Toggle)
document.addEventListener('DOMContentLoaded', () => {
    const switcherBtn = document.getElementById('switcher-btn');
    const switcherDropdown = document.getElementById('switcher-dropdown');

    if (switcherBtn && switcherDropdown) {
        switcherBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = switcherDropdown.classList.contains('hidden');
            
            if (isHidden) {
                switcherDropdown.classList.remove('hidden');
                setTimeout(() => {
                    switcherDropdown.classList.remove('opacity-0');
                    switcherDropdown.classList.add('opacity-100');
                }, 10);
            } else {
                switcherDropdown.classList.remove('opacity-100');
                switcherDropdown.classList.add('opacity-0');
                setTimeout(() => {
                    switcherDropdown.classList.add('hidden');
                }, 200);
            }
        });

        document.addEventListener('click', (e) => {
            if (!switcherBtn.contains(e.target) && !switcherDropdown.contains(e.target)) {
                switcherDropdown.classList.remove('opacity-100');
                switcherDropdown.classList.add('opacity-0');
                setTimeout(() => {
                    switcherDropdown.classList.add('hidden');
                }, 200);
            }
        });
    }
});
