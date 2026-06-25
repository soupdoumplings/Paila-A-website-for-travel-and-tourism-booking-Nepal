/* Filters */
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const toursGrid = document.getElementById('toursGrid');
    const journeyCount = document.getElementById('journey-count');
    const sortSelect = document.getElementById('sortSelect');
    const pricePresets = filterForm ? filterForm.querySelectorAll('.price-preset') : [];
    const priceMin = document.getElementById('price-min');
    const priceMax = document.getElementById('price-max');
    const priceSummary = document.getElementById('price-summary');

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

    const formatPrice = (value) => {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    const updatePriceUi = () => {
        if (!priceMin || !priceMax) return;

        const min = Number(priceMin.value || 0);
        const max = Number(priceMax.value || 1000000);

        pricePresets.forEach((preset) => {
            preset.classList.toggle(
                'active',
                Number(preset.dataset.min) === min && Number(preset.dataset.max) === max
            );
        });

        if (priceSummary) {
            priceSummary.innerHTML = min <= 0 && max >= 1000000
                ? 'Any price'
                : `<span class="price-display card-price"><span class="currency">रू</span><span class="amount">${formatPrice(min)}</span></span><span class="price-separator">to</span><span class="price-display card-price"><span class="currency">रू</span><span class="amount">${formatPrice(max)}</span></span>`;
        }
    };

    // Listeners
    const formInputs = filterForm.querySelectorAll('input, select');
    formInputs.forEach(input => {
        if (input.type === 'text' || input.type === 'number') {
            input.addEventListener('input', debounce(updateFilters));
            input.addEventListener('input', debounce(updatePriceUi, 80));
        } else {
            input.addEventListener('change', updateFilters);
        }
    });

    pricePresets.forEach((preset) => {
        preset.addEventListener('click', () => {
            if (priceMin) priceMin.value = preset.dataset.min || 0;
            if (priceMax) priceMax.value = preset.dataset.max || 1000000;
            updatePriceUi();
            updateFilters();
        });
    });

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
