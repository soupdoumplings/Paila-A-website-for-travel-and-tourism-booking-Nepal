document.addEventListener('DOMContentLoaded', () => {
    // Sidebar
    const userIcon = document.getElementById('user-icon');
    const userSidebar = document.getElementById('user-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (userIcon && userSidebar) {
        userIcon.addEventListener('click', (e) => {
            e.preventDefault();
            userSidebar.style.transform = 'translateX(0)';
            if (sidebarOverlay) {
                sidebarOverlay.style.opacity = '1';
                sidebarOverlay.style.visibility = 'visible';
            }
        });

        const closeFunc = () => {
            userSidebar.style.transform = 'translateX(100%)';
            if (sidebarOverlay) {
                sidebarOverlay.style.opacity = '0';
                sidebarOverlay.style.visibility = 'hidden';
            }
        };

        if (closeSidebar) closeSidebar.addEventListener('click', closeFunc);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeFunc);

        // Handle links
        const sidebarMenuItems = userSidebar.querySelectorAll('.sidebar-menu-item');
        sidebarMenuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Close
                closeFunc();
            });
        });
    }

    // Sticky nav
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (navbar.classList.contains('nav-no-scroll')) return;
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    // Hero video
    const videoConfig = document.getElementById('video-config');
    if (videoConfig) {
        const player1 = document.getElementById('hero-video-1');
        const player2 = document.getElementById('hero-video-2');
        const baseUrl = videoConfig.dataset.baseUrl || '';
        const clips = JSON.parse(videoConfig.dataset.clips || '[]');

        let players = [player1, player2];
        let activePlayerIndex = 0;
        let nextClipIndex = 0;

        const loadAndSwap = () => {
            const activePlayer = players[activePlayerIndex];
            const nextPlayer = players[1 - activePlayerIndex];

            // Next clip
            if (nextClipIndex >= clips.length) nextClipIndex = 0;
            const clipUrl = baseUrl + clips[nextClipIndex];

            // Preload
            nextPlayer.src = clipUrl;
            nextPlayer.load();

            // Swap
            activePlayer.onended = () => {
                // Play
                nextPlayer.play().then(() => {
                    // Transition
                    nextPlayer.style.opacity = '1';
                    activePlayer.style.opacity = '0';

                    // Delay update
                    setTimeout(() => {
                        activePlayerIndex = 1 - activePlayerIndex;
                        nextClipIndex++;
                        loadAndSwap();
                    }, 1600); // Duration
                }).catch(e => {
                    console.warn("Retrying next clip due to error:", e);
                    nextClipIndex++;
                    loadAndSwap();
                });
            };
        };

        // Init
        if (clips.length > 0) {
            player1.src = baseUrl + clips[0];
            player1.play().then(() => {
                player1.style.opacity = '1';
                nextClipIndex = 1;
                loadAndSwap();
            });
        }
    } else {
        const heroImages = document.querySelectorAll('.hero-bg');
        if (heroImages.length > 1) {
            let currentIndex = 0;
            setInterval(() => {
                heroImages[currentIndex].style.opacity = '0';
                currentIndex = (currentIndex + 1) % heroImages.length;
                heroImages[currentIndex].style.opacity = '1';
            }, 5000);
        }
    }

    // Search
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        const resultsDiv = document.createElement('div');
        resultsDiv.id = 'search-results';
        resultsDiv.style.cssText = `
            position: absolute; top: 100%; left: 0; right: 0;
            background: white; border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            max-height: 400px; overflow-y: auto; display: none;
            margin-top: 0.5rem; z-index: 1000; border: 1px solid #e7e5e4;
        `;
        searchInput.parentElement.appendChild(resultsDiv);

        let searchTimeout;
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(async () => {
                const baseUrl = window.PAILA_CONFIG ? window.PAILA_CONFIG.baseUrl : '/';
                try {
                    const response = await fetch(`${baseUrl}actions/tours/search_ajax.php?q=${encodeURIComponent(query)}`);
                    const results = await response.json();
                    resultsDiv.innerHTML = results.length === 0
                        ? `<div style="padding: 1rem; color: #78716c; text-align: center;">No tours found</div>`
                        : results.map(tour => {
                            const imageMap = {
                                'trekking': 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80',
                                'cultural': 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80',
                                'culture': 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80',
                                'adventure': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
                                'family': 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=800&q=80',
                                'luxury': 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80',
                                'weekend': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
                                'budget': 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80'
                            };

                            const cat = (tour.category || 'trekking').toLowerCase();
                            const fallback = imageMap[cat] || imageMap['trekking'];
                            let tourImg = tour.image;

                            if (tourImg) {
                                if (!tourImg.startsWith('http')) {
                                    tourImg = baseUrl + 'public/uploads/' + tourImg;
                                }
                            } else {
                                tourImg = fallback;
                            }

                            return `
                                <a href="${baseUrl}public/package_detail/?id=${tour.id}" style="display: flex; padding: 1.25rem 1rem; border-bottom: 1px solid #e7e5e4; align-items: center; gap: 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
                                    <div style="width: 64px; height: 64px; flex-shrink: 0; border-radius: 0.75rem; overflow: hidden; background: #f5f5f4; border: 1px solid #e7e5e4;">
                                        <img src="${tourImg}" onerror="this.src='${fallback}'" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; font-size: 1rem; color: #1c1917; margin-bottom: 0.25rem;">${tour.title}</div>
                                        <div style="font-size: 0.85rem; color: #78716c; display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="fa-solid fa-location-dot" style="font-size: 0.75rem;"></i> ${tour.location}
                                            <span style="opacity: 0.5;">•</span>
                                            <i class="fa-regular fa-clock" style="font-size: 0.75rem;"></i> ${tour.duration}
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.7rem; text-transform: uppercase; color: #a8a29e; font-weight: 700; margin-bottom: 0.1rem;">FROM</div>
                                        <div style="font-weight: 700; color: #0d9488; font-size: 1.05rem;">Rs ${Number(tour.price).toLocaleString()}</div>
                                    </div>
                                </a>`;
                        }).join('');
                    resultsDiv.style.display = 'block';
                } catch (e) {
                    console.error(e);
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) resultsDiv.style.display = 'none';
        });
    }
});
