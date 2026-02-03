<?php
require_once '../helpers/functions.php';
require_once '../config/db.php';

// Parse request params
$tour_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tour_title = isset($_GET['title']) ? trim($_GET['title']) : '';

$tour = null;
$error = null;

// Load tour by ID
if ($tour_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = :id");
        $stmt->execute(['id' => $tour_id]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = "DB Error: " . $e->getMessage();
    }
}

// Load tour by title
if (!$tour && $tour_title) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE title = :title");
        $stmt->execute(['title' => $tour_title]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
    }
}

if (!$tour) {
    header("Location: " . url('public/collection.php'));
    exit();
}

// Forward to detail
$tid = isset($tour['id']) ? (int)$tour['id'] : 0;
if ($tid > 0) {
    header("Location: " . url('public/package_detail/?id=' . $tid));
    exit();
}

$tour_price = isset($tour['price']) ? (float)$tour['price'] : 0;
$tour_duration = isset($tour['duration']) ? $tour['duration'] : '';
$tourImg = !empty($tour['image']) ? $tour['image'] : '';
if ($tourImg) {
    if (filter_var($tourImg, FILTER_VALIDATE_URL)) {
        $img = $tourImg;
    } else {
        $localPath = __DIR__ . '/uploads/' . $tourImg;
        if (file_exists($localPath)) {
            $img = url('public/uploads/' . $tourImg);
        } else {
            $img = 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1920&q=80';
        }
    }
} else {
    $img = 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1920&q=80';
}
$tour_location = isset($tour['location']) ? $tour['location'] : ($tour['destination_name'] ?? '');

include '../includes/header.php';
?>

<style>
.td-hero {
    min-height: 50vh;
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding-bottom: 3rem;
}
.td-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 50%, transparent 100%);
}
.td-hero-inner { position: relative; z-index: 1; }
.td-hero .loc { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.2em; color: rgba(255,255,255,0.9); margin-bottom: 0.5rem; }
.td-hero h1 { font-size: 2.5rem; color: white; margin-bottom: 0.5rem; }
.td-hero .meta { color: rgba(255,255,255,0.95); font-size: 1rem; }
.td-content { padding: 5rem 0 6rem; background: #fafaf9; }
.td-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 4rem; align-items: start; max-width: 1160px; margin: 0 auto; padding: 0 2rem; }
.td-main h2 { font-size: 1.5rem; font-weight: 700; color: #047857; margin-bottom: 1rem; }
.td-main p { color: #57534e; line-height: 1.85; font-size: 1rem; margin-bottom: 1.75rem; }
.td-highlights { margin-top: 2.5rem; padding-top: 2.5rem; border-top: 1px solid #e7e5e4; }
.td-highlights h3 { font-size: 1.25rem; font-weight: 700; color: #1c1917; margin-bottom: 1rem; }
.td-highlights ul { list-style: none; padding: 0; margin: 0; }
.td-highlights li { padding: 0.4rem 0; padding-left: 1.5rem; position: relative; color: #57534e; line-height: 1.8; }
.td-highlights li::before { content: '✓'; position: absolute; left: 0; color: #059669; font-weight: 700; }
.td-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    position: sticky;
    top: 100px;
    border: 1px solid #f5f5f4;
}
.td-card-title { font-size: 1.1rem; font-weight: 700; color: #1c1917; margin: 0 0 1.5rem 0; text-align: center; }
.td-card-body { padding: 1.5rem; }
.td-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.9rem 1.25rem; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; border: none; width: 100%; font-family: inherit; background: #047857; color: white; transition: background 0.2s; }
.td-btn:hover { background: #059669; }
.td-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 2rem; opacity: 0; visibility: hidden; transition: opacity 0.25s, visibility 0.25s; pointer-events: none; }
.td-modal-overlay.is-open { opacity: 1; visibility: visible; pointer-events: auto; }
.td-modal { background: #fff; border-radius: 12px; max-width: 480px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); position: relative; transform: scale(0.96); transition: transform 0.25s; }
.td-modal-overlay.is-open .td-modal { transform: scale(1); }
.td-modal-close { position: absolute; top: 1rem; right: 1rem; width: 2rem; height: 2rem; border: none; background: transparent; color: #78716c; font-size: 1.5rem; line-height: 1; cursor: pointer; padding: 0; }
.td-modal-close:hover { color: #1c1917; }
.td-modal-title { font-size: 1.35rem; font-weight: 700; color: #1c1917; margin: 0 0 1.5rem 0; padding-right: 2.5rem; }
.td-modal-body { padding: 2rem; }
.td-modal .form-group { margin-bottom: 1.25rem; }
.td-modal .form-group label { display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.4rem; color: #44403c; }
.td-modal .form-group input { width: 100%; padding: 0.7rem 0.85rem; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 0.95rem; }
.td-modal-total { display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0 1.25rem 0; padding: 0.75rem 0; border-top: 1px solid #e7e5e4; font-size: 1rem; }
.td-modal-total .label { font-weight: 600; color: #44403c; }
.td-modal-total .value { font-weight: 700; color: #047857; font-size: 1.2rem; }
.td-modal-submit { width: 100%; padding: 0.9rem 1.25rem; background: #047857; color: white; border: none; border-radius: 6px; font-size: 0.95rem; font-weight: 600; cursor: pointer; }
.td-modal-submit:hover { background: #059669; }
@media (max-width: 900px) { .td-grid { grid-template-columns: 1fr; } .td-card { position: static; } }
</style>

<section class="td-hero" style="background-image: url('<?php echo e($img); ?>');">
    <div class="container td-hero-inner">
        <p class="loc"><?php echo e($tour_location); ?></p>
        <h1><?php echo e($tour['title']); ?></h1>
        <div class="meta"><?php echo e($tour_duration); ?> – Rs <?php echo number_format($tour_price, 0); ?></div>
    </div>
</section>

<section class="td-content">
    <div class="td-grid">
        <div class="td-main">
            <h2>About This Journey</h2>
            <p><?php echo nl2br(e($tour['description'])); ?></p>
            <div class="td-highlights">
                <h3>Tour Highlights</h3>
                <ul>
                    <li>Professional local guide</li>
                    <li>All permits and entrance fees</li>
                    <li>Accommodation included</li>
                    <li>Transportation arranged</li>
                </ul>
            </div>
        </div>
        <div class="td-card">
            <div class="td-card-body">
                <h3 class="td-card-title">Book This Tour</h3>
                <div class="td-card-total" style="display: flex; justify-content: space-between; align-items: center; margin: 1rem 0 1.25rem 0; padding: 0.75rem 0; border-bottom: 1px solid #e7e5e4;">
                    <span style="font-weight: 600; color: #44403c;">Total Price:</span>
                    <span style="font-weight: 700; color: #047857; font-size: 1.2rem;">Rs <?php echo number_format($tour_price, 0); ?></span>
                </div>
                <button type="button" class="td-btn" id="td-open-booking">Request Booking</button>
                <p style="text-align: center; margin-top: 1rem; font-size: 0.85rem; color: #78716c;">You will receive a confirmation email</p>
            </div>
        </div>
    </div>
</section>

<div class="td-modal-overlay" id="td-booking-modal" aria-hidden="true">
    <div class="td-modal" role="dialog">
        <button type="button" class="td-modal-close" id="td-close-booking" aria-label="Close">&times;</button>
        <div class="td-modal-body">
            <h2 class="td-modal-title">Book <?php echo e($tour['title']); ?></h2>
            <p style="margin-top: -1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--color-stone-500);">No account needed to start. We'll automatically secure your booking.</p>
            <form action="<?php echo url('actions/bookings/process_booking.php'); ?>" method="POST">
                <input type="hidden" name="tour_id" value="0">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="customer_name" placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="contact_email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Travel Date *</label>
                    <input type="date" name="travel_date" required>
                </div>
                <div class="form-group">
                    <label>Number of Travelers</label>
                    <input type="number" name="travelers" min="1" value="1" required>
                </div>
                <div class="td-modal-total">
                    <span class="label">Total Estimate</span>
                    <span class="value">Rs <?php echo number_format($tour_price, 0); ?></span>
                </div>
                <button type="submit" class="td-modal-submit">Submit Booking Request</button>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    var o=document.getElementById('td-booking-modal'), openBtn=document.getElementById('td-open-booking'), closeBtn=document.getElementById('td-close-booking');
    function openModal(){ if(o){ o.classList.add('is-open'); o.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }}
    function closeModal(){ if(o){ o.classList.remove('is-open'); o.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }}
    if(openBtn) openBtn.addEventListener('click', openModal);
    if(closeBtn) closeBtn.addEventListener('click', closeModal);
    if(o) o.addEventListener('click', function(e){ if(e.target===o) closeModal(); });
    document.addEventListener('keydown', function(e){ if(e.key==='Escape'&&o&&o.classList.contains('is-open')) closeModal(); });
})();
</script>

<?php include '../includes/footer.php'; ?>
