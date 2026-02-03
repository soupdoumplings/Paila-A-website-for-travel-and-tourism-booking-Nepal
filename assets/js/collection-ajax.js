/* Filters */
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const toursGrid = document.getElementById('toursGrid');
    const journeyCount = document.getElementById('journey-count');
    const sortSelect = document.getElementById('sortSelect');

    if (!filterForm || !toursGrid) return;

    // Base path
    const getBasePath = () => {
        const path = window.location.pathname;
        if (path.includes('/public/')) return '../';
        return '';
    };

    const basePath = getBasePath();

    // Debounce
    let timeout = null;
    const debounce = (func, delay = 300) => {
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    };

    const updateFilters = () => {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        // Add fields
        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }

        // Add sort
        if (sortSelect) {
            params.append('sort', sortSelect.value);
        }

        // Loading
        toursGrid.style.opacity = '0.5';
        toursGrid.style.transition = 'opacity 0.2s ease';

        fetch(`${basePath}actions/tours/filter.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                toursGrid.innerHTML = data.html;
                if (journeyCount) journeyCount.innerText = data.count;
                toursGrid.style.opacity = '1';

                // Update URL
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
            })
            .catch(error => {
                console.error('Error fetching filtered tours:', error);
                toursGrid.style.opacity = '1';
            });
    };

    // Listeners
    const formInputs = filterForm.querySelectorAll('input, select');
    formInputs.forEach(input => {
        if (input.type === 'text') {
            input.addEventListener('input', debounce(updateFilters));
        } else {
            input.addEventListener('change', updateFilters);
        }
    });

    // Range listener
    const rangeInput = filterForm.querySelector('input[type="range"]');
    if (rangeInput) {
        rangeInput.addEventListener('input', debounce(updateFilters, 100));
    }

    // Sort listener
    if (sortSelect) {
        sortSelect.addEventListener('change', updateFilters);
    }

    // Prevent submit
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        updateFilters();
    });
});
