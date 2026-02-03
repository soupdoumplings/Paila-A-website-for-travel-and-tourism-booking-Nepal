// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const tab = this.dataset.tab;

        // Update UI
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.color = 'var(--color-stone-500)';
            b.style.borderBottom = '2px solid transparent';
        });
        this.classList.add('active');
        this.style.color = 'var(--color-stone-900)';
        this.style.borderBottom = '2px solid var(--color-stone-900)';

        // Toggle sections
        if (tab === 'tours') {
            document.getElementById('tours-section').style.display = 'block';
            document.getElementById('bookings-section').style.display = 'none';
        } else {
            document.getElementById('tours-section').style.display = 'none';
            document.getElementById('bookings-section').style.display = 'block';
        }
    });
});

// View modal
function viewTour(tour) {
    const modal = document.getElementById('viewTourModal');
    const content = document.getElementById('viewTourContent');

    // Icons
    const categoryIcons = {
        'trekking': 'fa-solid fa-mountain',
        'cultural': 'fa-solid fa-landmark',
        'adventure': 'fa-solid fa-person-hiking',
        'wellness': 'fa-solid fa-spa',
        'photography': 'fa-solid fa-camera',
        'luxury': 'fa-solid fa-gem',
        'family': 'fa-solid fa-people-group',
        'weekend': 'fa-solid fa-calendar-days',
        'budget': 'fa-solid fa-tag'
    };

    const icon = categoryIcons[tour.category.toLowerCase()] || 'fa-solid fa-map-location-dot';

    // Images
    const imageMap = {
        'trekking': 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1200&q=80',
        'cultural': 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=1200&q=80',
        'adventure': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
        'family': 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=1200&q=80',
        'luxury': 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=80',
        'wellness': 'https://images.unsplash.com/photo-1545389336-cf090694435e?w=1200&q=80',
        'photography': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
        'weekend': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
        'budget': 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1200&q=80'
    };

    const img = imageMap[tour.category.toLowerCase()] || imageMap['trekking'];

    content.innerHTML = `
        <div style="position: relative; height: 400px; border-radius: 1rem; overflow: hidden; margin-bottom: 2rem;">
            <img src="${img}" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.7)); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem; color: white;">
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <span style="background: var(--color-emerald-600); padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                        ${tour.category}
                    </span>
                    <span style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; text-transform: lowercase;">
                        ${tour.difficulty}
                    </span>
                </div>
                <h2 style="font-size: 2.5rem; font-family: var(--font-serif); margin-bottom: 1rem;">${tour.title}</h2>
                <div style="display: flex; gap: 2rem; font-size: 0.9rem;">
                    <span><i class="fa-solid fa-location-dot"></i> ${tour.destination_name}</span>
                    <span><i class="fa-regular fa-clock"></i> ${tour.duration}</span>
                    <span><i class="fa-solid fa-users"></i> Max ${tour.max_group_size} people</span>
                </div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <div>
                <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 1rem;">About This Journey</h3>
                <p style="line-height: 1.8; color: var(--color-stone-700); margin-bottom: 2rem;">${tour.description}</p>
                
                <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 1rem;">Experience Highlights</h3>
                <p style="line-height: 1.8; color: var(--color-stone-700);">${tour.highlights || 'Explore breathtaking landscapes and immerse yourself in local culture.'}</p>
            </div>
            
            <div>
                <div style="background: var(--color-emerald-700); color: white; padding: 2rem; border-radius: 1rem; margin-bottom: 2rem;">
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Starting from</div>
                    <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">$${tour.price}</div>
                    <div style="font-size: 0.875rem; opacity: 0.9;">per person</div>
                </div>
                
                <div style="background: var(--color-stone-50); padding: 1.5rem; border-radius: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: var(--color-stone-600); margin-bottom: 0.25rem;">Duration</div>
                        <div style="font-weight: 600;">${tour.duration}</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: var(--color-stone-600); margin-bottom: 0.25rem;">Difficulty</div>
                        <div style="font-weight: 600; text-transform: capitalize;">${tour.difficulty}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: var(--color-stone-600); margin-bottom: 0.25rem;">Max Group</div>
                        <div style="font-weight: 600;">${tour.max_group_size} people</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    modal.style.display = 'flex';
}

// Edit modal
function editTour(tour) {
    const modal = document.getElementById('editTourModal');
    const content = document.getElementById('editTourFormContent');

    content.innerHTML = `
        <input type="hidden" name="original_title" value="${tour.title}">
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Title</label>
            <input type="text" name="title" value="${tour.title}" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Category</label>
                <select name="category" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
                    <option value="trekking" ${tour.category === 'trekking' ? 'selected' : ''}>Trekking</option>
                    <option value="cultural" ${tour.category === 'cultural' ? 'selected' : ''}>Cultural</option>
                    <option value="adventure" ${tour.category === 'adventure' ? 'selected' : ''}>Adventure</option>
                    <option value="wellness" ${tour.category === 'wellness' ? 'selected' : ''}>Wellness</option>
                    <option value="photography" ${tour.category === 'photography' ? 'selected' : ''}>Photography</option>
                    <option value="luxury" ${tour.category === 'luxury' ? 'selected' : ''}>Luxury</option>
                    <option value="family" ${tour.category === 'family' ? 'selected' : ''}>Family</option>
                    <option value="weekend" ${tour.category === 'weekend' ? 'selected' : ''}>Weekend</option>
                    <option value="budget" ${tour.category === 'budget' ? 'selected' : ''}>Budget</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Destination</label>
                <input type="text" name="destination" value="${tour.destination_name}" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Duration (days)</label>
                <input type="number" name="duration" value="${parseInt(tour.duration)}" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Price ($)</label>
                <input type="number" name="price" value="${tour.price}" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Difficulty</label>
                <select name="difficulty" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
                    <option value="easy" ${tour.difficulty === 'easy' ? 'selected' : ''}>Easy</option>
                    <option value="moderate" ${tour.difficulty === 'moderate' ? 'selected' : ''}>Moderate</option>
                    <option value="challenging" ${tour.difficulty === 'challenging' ? 'selected' : ''}>Challenging</option>
                    <option value="extreme" ${tour.difficulty === 'extreme' ? 'selected' : ''}>Extreme</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Max Group Size</label>
                <input type="number" name="max_group_size" value="${tour.max_group_size}" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem;">
            </div>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Image</label>
            <div style="border: 2px dashed var(--color-stone-300); border-radius: 0.5rem; padding: 2rem; text-align: center;">
                <input type="file" id="tourImageFile" name="image" accept="image/*" style="display: none;" onchange="handleImagePreview(event)">
                <div id="imagePreview" style="margin-bottom: 1rem;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: var(--color-stone-400);"></i>
                </div>
                <button type="button" onclick="document.getElementById('tourImageFile').click()" style="background: var(--color-stone-100); color: var(--color-stone-700); padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid var(--color-stone-300); cursor: pointer; font-weight: 500; margin-bottom: 0.5rem;">
                    Choose Image from Device
                </button>
                <p style="font-size: 0.875rem; color: var(--color-stone-500); margin-top: 0.5rem;">or enter URL below</p>
                <input type="url" name="image_url" placeholder="https://example.com/image.jpg" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 0.9rem; margin-top: 0.5rem;">
            </div>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Description</label>
            <textarea name="description" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 1rem; resize: vertical;">${tour.description}</textarea>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="featured" ${tour.featured ? 'checked' : ''} style="width: 1.25rem; height: 1.25rem;">
                <span style="font-weight: 600;">Featured</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="private_access" style="width: 1.25rem; height: 1.25rem;">
                <span style="font-weight: 600;">Private Access Only</span>
            </label>
        </div>
        
        <button type="submit" style="width: 100%; background: var(--color-emerald-700); color: white; padding: 1rem; border-radius: 0.5rem; border: none; font-weight: 600; font-size: 1rem; cursor: pointer;">
            Update Tour
        </button>
    `;

    modal.style.display = 'flex';
}

// Preview
function handleImagePreview(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('imagePreview').innerHTML = `
                <img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 0.5rem;">
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Update
function updateTour(event) {
    event.preventDefault();
    alert('Tour update functionality will be implemented with backend integration.');
    closeModal('editTourModal');
}

// Delete
function deleteTour(title) {
    if (confirm(`Are you sure you want to delete "${title}"?`)) {
        alert('Tour delete functionality will be implemented with backend integration.');
    }
}

// Close
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Outside click
window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Add modal
function openAddTourModal() {
    alert('Add Tour functionality will be implemented with backend integration.');
}
