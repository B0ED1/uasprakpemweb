// FiguSphere - Main UI Interactions & AJAX Helpers
let activeFigureId = null;

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

    // Tutup alert otomatis dalam 4 detik
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

    // Inisialisasi event listener tombol Like pada modal detail
    const lightboxLikeBtn = document.getElementById('lightbox-like-btn');
    if (lightboxLikeBtn) {
        lightboxLikeBtn.addEventListener('click', function () {
            if (activeFigureId) {
                apiToggleLike(activeFigureId);
            }
        });
    }
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
function openImageModal(imgSrc, figureName, character, series, price, scale, brand, figureId, isLiked, likesCount) {
    const modal = document.getElementById('image-lightbox-modal');
    if (!modal) return;

    activeFigureId = figureId;

    // Handle foto kosong/fallback
    const imgEl = document.getElementById('lightbox-img');
    let fallbackEl = document.getElementById('lightbox-img-fallback');
    
    if (!imgSrc) {
        imgEl.classList.add('hidden');
        if (!fallbackEl) {
            fallbackEl = document.createElement('div');
            fallbackEl.id = 'lightbox-img-fallback';
            fallbackEl.className = 'w-full h-full flex flex-col items-center justify-center text-brand-400 bg-gradient-to-tr from-brand-950 to-indigo-950 p-6 min-h-[300px] md:min-h-[500px]';
            fallbackEl.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-bold uppercase tracking-widest text-brand-400 opacity-80">FiguSphere figure</span>
            `;
            imgEl.parentNode.appendChild(fallbackEl);
        } else {
            fallbackEl.classList.remove('hidden');
        }
    } else {
        imgEl.src = imgSrc;
        imgEl.alt = figureName;
        imgEl.classList.remove('hidden');
        if (fallbackEl) {
            fallbackEl.classList.add('hidden');
        }
    }

    document.getElementById('lightbox-brand').textContent = brand;
    document.getElementById('lightbox-series').textContent = series;
    document.getElementById('lightbox-name').textContent = figureName;
    document.getElementById('lightbox-char').textContent = character;
    document.getElementById('lightbox-scale').textContent = scale || 'Non-scale';
    document.getElementById('lightbox-price').textContent = price;

    // Reset dan atur status Like & Komentar
    updateLightboxLikeUI(isLiked, likesCount);
    loadComments(figureId);

    // Tampilkan modal
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
        activeFigureId = null;
        
        // Reset comment input
        const commentInput = document.getElementById('lightbox-comment-input');
        if (commentInput) commentInput.value = '';
    }, 200);
}

// 3a. Like AJAX Handler
function toggleLike(event, figureId) {
    if (event) {
        event.stopPropagation(); // Mencegah terbukanya modal detail
    }
    apiToggleLike(figureId);
}

function apiToggleLike(figureId) {
    const formData = new FormData();
    formData.append('figure_id', figureId);

    fetch('api_like.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Perbarui visual Like pada kartu Dashboard
            const cardIcon = document.getElementById(`like-icon-card-${figureId}`);
            const cardCount = document.getElementById(`like-count-card-${figureId}`);
            if (cardIcon && cardCount) {
                if (data.liked) {
                    cardIcon.classList.add('fill-rose-500', 'text-rose-500');
                    cardIcon.setAttribute('fill', 'currentColor');
                } else {
                    cardIcon.classList.remove('fill-rose-500', 'text-rose-500');
                    cardIcon.setAttribute('fill', 'none');
                }
                cardCount.textContent = data.likes_count;
            }

            // Perbarui visual Like di dalam modal Lightbox jika figure sedang dibuka
            if (activeFigureId === figureId) {
                updateLightboxLikeUI(data.liked, data.likes_count);
            }
        } else {
            alert(data.error || 'Gagal merubah status Like.');
        }
    })
    .catch(err => {
        console.error('Error toggling like:', err);
    });
}

function updateLightboxLikeUI(isLiked, likesCount) {
    const likeIcon = document.getElementById('lightbox-like-icon');
    const likeLabel = document.getElementById('lightbox-like-label');
    const likeCount = document.getElementById('lightbox-likes-count');

    if (likeIcon && likeLabel && likeCount) {
        if (isLiked) {
            likeIcon.classList.add('fill-rose-500', 'text-rose-500');
            likeIcon.classList.remove('text-slate-400');
            likeIcon.setAttribute('fill', 'currentColor');
            likeLabel.textContent = 'Batal Suka';
        } else {
            likeIcon.classList.remove('fill-rose-500', 'text-rose-500');
            likeIcon.classList.add('text-slate-400');
            likeIcon.setAttribute('fill', 'none');
            likeLabel.textContent = 'Suka';
        }
        likeCount.textContent = `${likesCount} Menyukai`;
    }
}

// 3b. Comments AJAX Handler
function loadComments(figureId) {
    const commentsList = document.getElementById('lightbox-comments-list');
    if (!commentsList) return;

    commentsList.innerHTML = '<p class="text-xs text-slate-500 italic text-center py-2">Memuat komentar...</p>';

    fetch(`api_comments.php?figure_id=${figureId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCommentsList(data.comments);
        } else {
            commentsList.innerHTML = `<p class="text-xs text-rose-400 italic text-center py-2">${data.error}</p>`;
        }
    })
    .catch(err => {
        console.error('Error loading comments:', err);
        commentsList.innerHTML = '<p class="text-xs text-rose-400 italic text-center py-2">Gagal memuat komentar.</p>';
    });
}

function renderCommentsList(comments) {
    const commentsList = document.getElementById('lightbox-comments-list');
    if (!commentsList) return;

    if (comments.length === 0) {
        commentsList.innerHTML = '<p class="text-xs text-slate-500 italic text-center py-2">Belum ada komentar.</p>';
        return;
    }

    commentsList.innerHTML = comments.map(comment => {
        const initial = (comment.nama_lengkap || comment.username || '?').charAt(0).toUpperCase();
        const escapedKomentar = escapeHTML(comment.komentar);
        const escapedName = escapeHTML(comment.nama_lengkap);

        return `
            <div class="flex items-start space-x-2 text-xs animate-fade-in">
                <div class="w-6 h-6 rounded-full bg-brand-600/30 text-brand-300 font-bold flex items-center justify-center flex-shrink-0 border border-brand-500/20 text-[10px]">
                    ${initial}
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-2 flex-grow min-w-0">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="font-bold text-slate-200 truncate max-w-[120px]" title="${escapedName}">${escapedName}</span>
                        <span class="text-[8px] text-slate-500 flex-shrink-0 ml-1">${comment.formatted_time}</span>
                    </div>
                    <p class="text-slate-300 break-words leading-relaxed">${escapedKomentar}</p>
                </div>
            </div>
        `;
    }).join('');

    commentsList.scrollTop = commentsList.scrollHeight;
}

function submitComment(event) {
    event.preventDefault();
    if (!activeFigureId) return;

    const input = document.getElementById('lightbox-comment-input');
    if (!input) return;

    const komentar = input.value.trim();
    if (!komentar) return;

    const formData = new FormData();
    formData.append('figure_id', activeFigureId);
    formData.append('komentar', komentar);

    input.disabled = true;

    fetch('api_comments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        input.disabled = false;
        if (data.success) {
            input.value = '';
            renderCommentsList(data.comments);
            
            // Perbarui jumlah komentar pada kartu Dashboard
            const cardCommentCount = document.getElementById(`comment-count-card-${activeFigureId}`);
            if (cardCommentCount) {
                cardCommentCount.textContent = data.comments.length;
            }
        } else {
            alert(data.error || 'Gagal mengirim komentar.');
        }
    })
    .catch(err => {
        input.disabled = false;
        console.error('Error posting comment:', err);
    });
}

function escapeHTML(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
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
