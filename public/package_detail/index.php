<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base = '../../';
require_once $base . 'helpers/functions.php';
require_once $base . 'config/db.php';

$tour_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM tours WHERE id = :id");
$stmt->execute(['id' => $tour_id]);
$tour = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tour) {
    header('Location: ' . $base . 'index.php');
    exit;
}

$img = get_tour_image($tour);

$category = isset($tour['category']) && $tour['category'] ? $tour['category'] : null;
$difficulty = isset($tour['difficulty']) && $tour['difficulty'] ? $tour['difficulty'] : null;
$max_group = isset($tour['max_group']) && $tour['max_group'] ? $tour['max_group'] : null;
$highlights = isset($tour['highlights']) && $tour['highlights'] ? $tour['highlights'] : '';
$highlights_list = array_filter(array_map('trim', explode("\n", $highlights)));

// Nepal-specific metadata
$best_season = isset($tour['best_season']) && $tour['best_season'] ? $tour['best_season'] : null;
$altitude_max = isset($tour['altitude_max']) && $tour['altitude_max'] ? (int)$tour['altitude_max'] : null;
$permits = isset($tour['permit_requirements']) && $tour['permit_requirements'] ? $tour['permit_requirements'] : null;
$permits_list = $permits ? array_filter(array_map('trim', explode(",", $permits))) : [];

// Itinerary
$itinerary_text = isset($tour['itinerary']) && $tour['itinerary'] ? $tour['itinerary'] : '';
$itinerary_days = array_filter(array_map('trim', explode("\n", $itinerary_text)));

// Inclusions/Exclusions
$inclusions_text = isset($tour['inclusions']) && $tour['inclusions'] ? $tour['inclusions'] : '';
$inclusions_list = array_filter(array_map('trim', explode("\n", $inclusions_text)));
$exclusions_text = isset($tour['exclusions']) && $tour['exclusions'] ? $tour['exclusions'] : '';
$exclusions_list = array_filter(array_map('trim', explode("\n", $exclusions_text)));

$pageTitle = e($tour['title']) . ' | Nepal Tours';
include $base . 'includes/header.php';
?>
<style>
/* ——— Hero (matches second image) ——— */
.pd-hero {
    min-height: 70vh;
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding-bottom: 3rem;
}
.pd-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
}
.pd-hero-inner { position: relative; z-index: 1; }
.pd-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255,255,255,0.9);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    text-decoration: none;
    transition: opacity 0.3s;
}
.pd-back:hover { opacity: 0.8; }
.pd-tags { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
.pd-tag {
    padding: 0.35rem 0.9rem;
    border-radius: 0.4rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.pd-tag-cat { background: #0f766e; color: white; }
.pd-tag-diff { background: rgba(255,255,255,0.25); color: white; }
.pd-title { font-size: 2.5rem; color: white; margin-bottom: 1rem; line-height: 1.2; font-weight: 700; }
.pd-meta { display: flex; flex-wrap: wrap; gap: 1rem; color: rgba(255,255,255,0.95); font-size: 0.95rem; }
.pd-meta span { display: inline-flex; align-items: center; gap: 0.35rem; }

/* ——— Left column: About / Experience Highlights ——— */
.pd-content { padding: 5rem 0 6rem; background: #fafaf9; }
.pd-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 4rem;
    align-items: start;
    max-width: 1160px;
    margin: 0 auto;
    padding: 0 2rem;
}
.pd-main {
    text-align: left;
    padding-right: 1rem;
}
.pd-main h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1c1917;
    margin-bottom: 1rem;
    font-family: var(--font-sans), sans-serif;
}
.pd-main p {
    color: #57534e;
    line-height: 1.85;
    font-size: 1rem;
    margin-bottom: 1.75rem;
}
.pd-main ul { list-style: none; padding: 0; margin: 0; }
.pd-main li {
    color: #57534e;
    line-height: 1.8;
    font-size: 1rem;
    padding: 0.4rem 0;
    padding-left: 1.5rem;
    position: relative;
}
.pd-main li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #059669;
    font-weight: 700;
}
.pd-highlights {
    margin-top: 2.5rem;
    padding-top: 2.5rem;
    border-top: 1px solid #e7e5e4;
}
.pd-highlights h2 {
    margin-bottom: 1rem;
}

/* ——— Right column: booking card (third image) ——— */
.pd-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    position: sticky;
    top: 100px;
    border: 1px solid #f5f5f4;
}
.pd-card-price {
    background: #047857;
    color: white;
    padding: 1.75rem 1.5rem;
    text-align: center;
}
.pd-card-price .label {
    font-size: 0.875rem;
    opacity: 0.95;
    text-transform: none;
    letter-spacing: 0;
    display: block;
}
.pd-card-price .amount {
    font-size: 2.5rem;
    font-weight: 700;
    display: block;
    margin: 0.35rem 0;
    line-height: 1.1;
}
.pd-card-price .per {
    font-size: 0.875rem;
    opacity: 0.95;
}
.pd-card-body { padding: 1.5rem; }
.pd-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
    font-size: 1rem;
}
.pd-card-row:last-of-type { border-bottom: none; }
.pd-card-row .k { color: #57534e; font-weight: 400; }
.pd-card-row .v { font-weight: 600; color: #292524; }
.pd-card-actions {
    margin-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.pd-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.9rem 1.25rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.2s;
    width: 100%;
    font-family: var(--font-sans), sans-serif;
}
.pd-btn-primary {
    background: #047857;
    color: white;
}
.pd-btn-primary:hover {
    background: #059669;
    transform: translateY(-1px);
}
.pd-btn-secondary {
    background: #fff;
    color: #292524;
    border: 1px solid #d6d3d1;
}
.pd-btn-secondary:hover {
    border-color: #a8a29e;
    background: #fafaf9;
}
.pd-form {
    margin-top: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e7e5e4;
}
.pd-form .form-group { margin-bottom: 1rem; }
.pd-form .form-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #44403c;
}
.pd-form .form-group input,
.pd-form .form-group textarea {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #d6d3d1;
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: inherit;
}
.pd-form .form-group textarea { min-height: 80px; resize: vertical; }

/* Sticky Booking Button */
.pd-sticky-book {
    position: fixed;
    bottom: -100px;
    left: 0;
    right: 0;
    background: white;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    padding: 1rem 0;
    z-index: 999;
    transition: bottom 0.3s ease;
}
.pd-sticky-book.show { bottom: 0; }
.pd-sticky-book-inner {
    max-width: 1160px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
}
.pd-sticky-book-info h3 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: #1c1917;
}
.pd-sticky-book-info .price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #047857;
}
@media (max-width: 900px) {
    .pd-grid { grid-template-columns: 1fr; gap: 2rem; }
    .pd-card { position: static; }
    .pd-sticky-book-inner { flex-direction: column; align-items: stretch; }
}

/* ——— Booking modal popup ——— */
.pd-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}
.pd-modal-overlay.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.pd-modal {
    background: #fff;
    border-radius: 12px;
    max-width: 480px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    position: relative;
    transform: scale(0.96);
    transition: transform 0.25s ease;
}
.pd-modal-overlay.is-open .pd-modal {
    transform: scale(1);
}
.pd-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 2rem;
    height: 2rem;
    border: none;
    background: transparent;
    color: #78716c;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    border-radius: 4px;
    transition: color 0.2s, background 0.2s;
}
.pd-modal-close:hover {
    color: #1c1917;
    background: #f5f5f4;
}
.pd-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1c1917;
    margin: 0 0 1.5rem 0;
    padding-right: 2.5rem;
    font-family: var(--font-serif), serif;
}
.pd-modal-body { padding: 2rem; }
.pd-modal .form-group {
    margin-bottom: 1.25rem;
}
.pd-modal .form-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #44403c;
}
.pd-modal .form-group label .req { color: #dc2626; }
.pd-modal .form-group input,
.pd-modal .form-group textarea {
    width: 100%;
    padding: 0.7rem 0.85rem;
    border: 1px solid #d6d3d1;
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: inherit;
}
.pd-modal .form-group textarea { min-height: 88px; resize: vertical; }
.pd-modal-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1.5rem 0 1.25rem 0;
    padding: 0.75rem 0;
    border-top: 1px solid #e7e5e4;
    font-size: 1rem;
}
.pd-modal-total .label { font-weight: 600; color: #44403c; }
.pd-modal-total .value { font-weight: 700; color: #047857; font-size: 1.2rem; }
.pd-modal-submit {
    width: 100%;
    padding: 0.9rem 1.25rem;
    background: #047857;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.pd-modal-submit:hover { background: #059669; }
</style>

<!-- Hero -->
<section class="pd-hero" style="background-image: url('<?php echo e($img); ?>');">
    <div class="container pd-hero-inner">
        <a href="<?php echo $base; ?>collection.php" class="pd-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Collection
        </a>
        <div class="pd-tags">
            <?php if ($category): ?>
                <span class="pd-tag pd-tag-cat"><?php echo e(strtoupper($category)); ?></span>
            <?php endif; ?>
            <?php if ($difficulty): ?>
                <span class="pd-tag pd-tag-diff"><?php echo e($difficulty); ?></span>
            <?php endif; ?>
            <?php if ($best_season): ?>
                <span class="pd-tag" style="background: rgba(251,191,36,0.2); color: #fbbf24;"><?php echo e($best_season); ?></span>
            <?php endif; ?>
            <?php if ($altitude_max): ?>
                <span class="pd-tag" style="background: rgba(239,68,68,0.2); color: #ef4444;">⛰ <?php echo number_format($altitude_max); ?>m</span>
            <?php endif; ?>
        </div>
        <h1 class="pd-title"><?php echo e($tour['title']); ?></h1>
        <div class="pd-meta">
            <span><i class="fa-solid fa-location-dot"></i> <?php echo e($tour['location']); ?></span>
            <span><i class="fa-regular fa-clock"></i> <?php echo e($tour['duration']); ?></span>
            <?php if ($max_group): ?>
                <span><i class="fa-solid fa-users"></i> Max <?php echo e($max_group); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Content -->
<section class="pd-content">
    <div class="pd-grid">
        <div class="pd-main">
            <h2>About This Journey</h2>
            <p><?php echo nl2br(e($tour['description'])); ?></p>

            <?php if (!empty($highlights_list)): ?>
            <div class="pd-highlights">
                <h2>Experience Highlights</h2>
                <ul>
                    <?php foreach ($highlights_list as $h): ?>
                        <li><?php echo e($h); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($itinerary_days)): ?>
            <div class="pd-highlights">
                <h2>Itinerary</h2>
                <ul>
                    <?php foreach ($itinerary_days as $index => $day): ?>
                        <li><strong>Day <?php echo $index + 1; ?>:</strong> <?php echo e($day); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($inclusions_list) || !empty($exclusions_list)): ?>
            <div class="pd-highlights">
                <h2>What's Included & Excluded</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                    <?php if (!empty($inclusions_list)): ?>
                    <div>
                        <h3 style="font-size: 1.1rem; color: #059669; margin-bottom: 0.75rem;">✓ Included</h3>
                        <ul style="padding-left: 0;">
                            <?php foreach ($inclusions_list as $inc): ?>
                                <li style="color: #059669;"><?php echo e($inc); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($exclusions_list)): ?>
                    <div>
                        <h3 style="font-size: 1.1rem; color: #dc2626; margin-bottom: 0.75rem;">✗ Not Included</h3>
                        <ul style="padding-left: 0;">
                            <?php foreach ($exclusions_list as $exc): ?>
                                <li style="color: #dc2626; "><?php echo e($exc); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($altitude_max || !empty($permits_list)): ?>
            <div class="pd-highlights">
                <h2>Safety & Requirements</h2>
                <?php if ($altitude_max && $altitude_max > 3500): ?>
                <div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <strong style="color: #92400e;">⚠️ High Altitude Warning:</strong>
                    <p style="margin: 0.5rem 0 0 0; color: #78350f;">This trek reaches <?php echo number_format($altitude_max); ?>m. Proper acclimatization is essential. Consult with your doctor before booking.</p>
                </div>
                <?php endif; ?>
                <?php if (!empty($permits_list)): ?>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Required Permits</h3>
                    <ul>
                        <?php foreach ($permits_list as $permit): ?>
                            <li><?php echo e($permit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p style="font-size: 0.9rem; color: #78716c; margin-top: 0.5rem;">All permits are arranged by our team and included in the package price.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="pd-card">
            <div class="pd-card-price">
                <span class="label">Starting from</span>
                <span class="amount">Rs <?php echo number_format($tour['price'], 0); ?></span>
                <span class="per">per person</span>
            </div>
            <div class="pd-card-body">
                <div class="pd-card-row">
                    <span class="k">Duration</span>
                    <span class="v"><?php echo e($tour['duration']); ?></span>
                </div>
                <div class="pd-card-row">
                    <span class="k">Difficulty</span>
                    <span class="v"><?php echo $difficulty ? e($difficulty) : '—'; ?></span>
                </div>
                <div class="pd-card-row">
                    <span class="k">Max Group</span>
                    <span class="v"><?php echo $max_group ? e($max_group) : '—'; ?></span>
                </div>
                <?php if ($best_season): ?>
                <div class="pd-card-row">
                    <span class="k">Best Season</span>
                    <span class="v"><?php echo e($best_season); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($altitude_max): ?>
                <div class="pd-card-row">
                    <span class="k">Max Altitude</span>
                    <span class="v"><?php echo number_format($altitude_max); ?>m</span>
                </div>
                <?php endif; ?>

                <div class="pd-card-actions">
                    <?php if (isset($db_error) && $db_error): ?>
                        <button type="button" class="pd-btn pd-btn-secondary" style="width: 100%; cursor: not-allowed; opacity: 0.6;" disabled>
                            <i class="fa-solid fa-lock"></i> Booking Unavailable
                        </button>
                    <?php else: ?>
                        <button type="button" class="pd-btn pd-btn-primary" id="pd-open-booking">Request Booking</button>
                    <?php endif; ?>
                    <button type="button" class="pd-btn pd-btn-secondary" id="pd-share">
                        <i class="fa-solid fa-share-nodes"></i> Share This Tour
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking modal popup -->
<div class="pd-modal-overlay" id="pd-booking-modal" aria-hidden="true">
    <div class="pd-modal" role="dialog" aria-labelledby="pd-modal-title">
        <button type="button" class="pd-modal-close" id="pd-close-booking" aria-label="Close">&times;</button>
        <div class="pd-modal-body">
            <h2 class="pd-modal-title" id="pd-modal-title">Book <?php echo e($tour['title']); ?></h2>
            <p style="margin-top: -1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--color-stone-500);">No account needed to start. We'll automatically secure your booking.</p>
            <form id="pd-booking-form" action="<?php echo url('actions/bookings/process_booking.php'); ?>" method="POST" data-validate>
                <input type="hidden" name="tour_id" value="<?php echo (int) $tour['id']; ?>">
                <div class="form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="customer_name" placeholder="Your full name" data-rules="required|min:3">
                </div>
                <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="contact_email" placeholder="your@email.com" data-rules="required|email">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="+977 ...">
                </div>
                <div class="form-group">
                    <label>Travel Date <span class="req">*</span></label>
                    <input type="date" name="travel_date" data-rules="required" min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                </div>
                <div class="form-group">
                    <label>Group Size</label>
                    <input type="number" name="travelers" value="1" data-rules="required">
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" placeholder="Any special requirements or questions?"></textarea>
                </div>
                <div class="pd-modal-total">
                    <span class="label">Total Estimate</span>
                    <span class="value">Rs <?php echo number_format($tour['price'], 0); ?></span>
                </div>
                <button type="submit" class="pd-modal-submit">Submit Booking Request</button>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Booking Button -->
<div class="pd-sticky-book" id="pd-sticky-book">
    <div class="pd-sticky-book-inner">
        <div class="pd-sticky-book-info">
            <h3><?php echo e($tour['title']); ?></h3>
            <span class="price">Rs <?php echo number_format($tour['price'], 0); ?></span> <span style="font-size: 0.9rem; color: #78716c;">per person</span>
        </div>
        <button type="button" class="pd-btn pd-btn-primary" id="pd-sticky-book-btn" style="max-width: 250px;">Book Now</button>
    </div>
</div>

<script>
(function() {
    var overlay = document.getElementById('pd-booking-modal');
    var openBtn = document.getElementById('pd-open-booking');
    var closeBtn = document.getElementById('pd-close-booking');

    function openModal() {
        if (overlay) {
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeModal() {
        if (overlay) {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) closeModal();
    });

    var shareBtn = document.getElementById('pd-share');
    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: document.querySelector('.pd-title').textContent,
                    url: window.location.href
                }).catch(function() { copyLink(); });
            } else {
                copyLink();
            }
        });
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                shareBtn.innerHTML = 'Link copied!';
                setTimeout(function() { shareBtn.innerHTML = '<i class="fa-solid fa-share-nodes"></i> Share This Tour'; }, 1500);
            });
        }
    }

    // Sticky booking button
    var stickyBook = document.getElementById('pd-sticky-book');
    var stickyBookBtn = document.getElementById('pd-sticky-book-btn');
    
    if (stickyBook && stickyBookBtn) {
        // Show sticky button when scrolled past booking card
        window.addEventListener('scroll', function() {
            var scrolled = window.pageYOffset || document.documentElement.scrollTop;
            if (scrolled > 600) {
                stickyBook.classList.add('show');
            } else {
                stickyBook.classList.remove('show');
            }
        });
        
        // Connect sticky button to modal
        stickyBookBtn.addEventListener('click', openModal);
    }
})();
</script>

<?php include $base . 'includes/footer.php'; ?>
