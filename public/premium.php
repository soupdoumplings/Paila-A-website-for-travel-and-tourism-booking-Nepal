<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../helpers/functions.php';
require_once '../config/db.php';

// Verify premium access
$has_access = isset($_SESSION['premium_access']) && $_SESSION['premium_access'] === true;

// Get session messages
$access_success = $_SESSION['access_success'] ?? '';
$access_error = $_SESSION['access_error'] ?? '';
unset($_SESSION['access_success'], $_SESSION['access_error']);

include '../includes/header.php';
?>

<!-- Hero -->
<section style="position: relative; min-height: 100vh; display: flex; align-items: center; background: #000; overflow: hidden; color: white;">
    <!-- Video overlay -->
    <div style="position: absolute; inset: 0; z-index: 1;">
        <video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover; opacity: 0.6;">
            <source src="<?php echo url('assets/video/1.mp4'); ?>" type="video/mp4">
        </video>
        <div style="position: absolute; inset: 0; background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.2) 100%);"></div>
    </div>

    <div class="container" style="position: relative; z-index: 10; max-width: 600px;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(13, 148, 136, 0.1); color: #0d9488; padding: 0.4rem 1rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 2rem;">
            <i class="fa-solid fa-lock"></i> BY INVITATION ONLY
        </div>
        
        <h1 style="font-size: 4rem; font-family: var(--font-serif); margin-bottom: 1.5rem; line-height: 1.1; color: white;">
            Private <span style="color: var(--color-amber-400);">Journeys</span>
        </h1>
        
        <p style="font-size: 1.125rem; line-height: 1.8; color: rgba(255,255,255,0.9); margin-bottom: 2.5rem;">
            Invitation-Only Himalayan Journeys
        </p>
        
        <!-- How it works -->
        <div style="margin-bottom: 2.5rem; font-size: 0.9rem; color: rgba(255,255,255,0.8);">
            <p style="margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; color: var(--color-amber-400); font-weight: 600;">How Private Access Works</p>
            <ul style="list-style: none; padding: 0; margin: 0; line-height: 1.8;">
                <li><i class="fa-solid fa-circle-check" style="color: var(--color-amber-400); margin-right: 0.5rem; font-size: 0.7rem;"></i> Submit a private journey request</li>
                <li><i class="fa-solid fa-circle-check" style="color: var(--color-amber-400); margin-right: 0.5rem; font-size: 0.7rem;"></i> Consultation and review by the team</li>
                <li><i class="fa-solid fa-circle-check" style="color: var(--color-amber-400); margin-right: 0.5rem; font-size: 0.7rem;"></i> Approved guests receive an access code</li>
                <li><i class="fa-solid fa-circle-check" style="color: var(--color-amber-400); margin-right: 0.5rem; font-size: 0.7rem;"></i> Access unlocks bespoke, invitation-only experiences</li>
            </ul>
        </div>
        
        <?php if ($access_success): ?>
            <div style="margin-bottom: 2rem; padding: 1rem 1.5rem; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 0.5rem; color: #ecfdf5;">
                <i class="fa-solid fa-circle-check"></i> <?php echo e($access_success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($access_error): ?>
            <div style="margin-bottom: 2rem; padding: 1rem 1.5rem; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); border-radius: 0.5rem; color: #fef2f2;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($access_error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($has_access): ?>
            <div style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; color: #ecfdf5; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-unlock" style="font-size: 1.25rem;"></i>
                    <span style="font-size: 1.125rem; font-weight: 600;">Access Granted</span>
                </div>
                <p style="opacity: 0.9; font-size: 0.9rem; margin: 0;">Explore exclusive tours below</p>
            </div>
        <?php else: ?>
            <!-- Access input -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem; color: var(--color-stone-700);">Enter your private access code</label>
                <p style="font-size: 0.75rem; color: var(--color-stone-500); margin-bottom: 1rem; font-style: italic;">Access codes are issued exclusively following a personal consultation.</p>
                <form action="<?php echo url('actions/auth/validate_access.php'); ?>" method="POST" style="display: flex; gap: 1rem;">
                    <input type="text" name="access_code" placeholder="PRIVATE-XXXX-XXXX" required
                           style="flex: 1; padding: 1rem 1.5rem; background: white; border: 1px solid var(--color-stone-200); border-radius: 0.5rem; color: var(--color-stone-900); font-size: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <button type="submit" class="btn" style="background: var(--color-stone-900); color: white; padding: 1rem 2.5rem; font-weight: 600; border-radius: 50px;">
                        Access <i class="fa-solid fa-arrow-right-long" style="margin-left: 0.5rem;"></i>
                    </button>
                </form>
            </div>
            
            <p style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.5rem;">
                To begin your journey, <a href="#contact" style="color: var(--color-amber-400); text-decoration: underline;">request a consultation via our concierge</a>.
            </p>
            <p style="font-size: 0.75rem; opacity: 0.5;">
                Limited availability for the 2026 season due to regional constraints.
            </p>
        <?php endif; ?>
    </div>
</section>

<!-- Features -->
<section style="padding: 6rem 0; background: white; color: var(--color-stone-900);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 3rem; max-width: 1000px; margin: 0 auto;">
            <!-- Feature 1 -->
            <div style="text-align: left; padding: 2rem; border: 1px solid var(--color-stone-100); border-radius: 1rem; background: var(--color-stone-50);">
                <div style="font-size: 0.8rem; color: #0d9488; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Bespoke</div>
                <h3 style="font-size: 2rem; font-family: var(--font-serif); margin-bottom: 1rem; font-weight: 400;">Itineraries</h3>
                <p style="font-size: 1.1rem; color: var(--color-stone-600); line-height: 1.8;">
                    Completely customized journeys designed around your preferences and schedule
                </p>
            </div>
            
            <!-- Feature 2 -->
            <div style="text-align: left; padding: 2rem; border: 1px solid var(--color-stone-100); border-radius: 1rem; background: var(--color-stone-50);">
                <div style="font-size: 0.8rem; color: #0d9488; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Private</div>
                <h3 style="font-size: 2rem; font-family: var(--font-serif); margin-bottom: 1rem; font-weight: 400;">Guides</h3>
                <p style="font-size: 1.1rem; color: var(--color-stone-600); line-height: 1.8;">
                    One-on-one attention from our most experienced expedition leaders
                </p>
            </div>
            
            <!-- Feature 3 -->
            <div style="text-align: left; padding: 2rem; border: 1px solid var(--color-stone-100); border-radius: 1rem; background: var(--color-stone-50);">
                <div style="font-size: 0.8rem; color: #0d9488; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Exclusive</div>
                <h3 style="font-size: 2rem; font-family: var(--font-serif); margin-bottom: 1rem; font-weight: 400;">Access</h3>
                <p style="font-size: 1.1rem; color: var(--color-stone-600); line-height: 1.8;">
                    Visit restricted areas and hidden gems unavailable to regular tours
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Collection -->
<section id="holdings" style="padding: 6rem 0; background: #f8fafc; border-top: 1px solid #e2e8f0;">
    <div class="container">
        <h2 style="font-size: 3rem; font-family: var(--font-serif); text-align: center; color: var(--color-stone-900); margin-bottom: 4rem;">
            The Collection
        </h2>
        
        <?php if (!$has_access): ?>
            <div style="text-align: center; padding: 4rem 2rem; max-width: 600px; margin: 0 auto; background: white; border-radius: 2rem; border: 1px solid var(--color-stone-100); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
                <div style="font-size: 4rem; color: var(--color-stone-200); margin-bottom: 1.5rem;"><i class="fa-solid fa-lock"></i></div>
                <h3 style="color: var(--color-stone-900); font-family: var(--font-serif); font-size: 1.75rem; margin-bottom: 1rem;">Access Required</h3>
                <p style="color: var(--color-stone-500); font-size: 1.125rem; margin-bottom: 2.5rem;">Enter a valid access code above to view exclusive tour offerings.</p>
                <a href="#contact" class="btn" style="background: var(--color-stone-900); color: white; padding: 1rem 3rem; font-weight: 500; text-decoration: none; display: inline-block; border-radius: 50px;">
                    Request Access <i class="fa-solid fa-arrow-right-long" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 3rem; max-width: 1000px; margin: 0 auto;">
                <!-- Tour 1 -->
                <a href="<?php echo url('public/premium_tour_detail.php?id=helicopter'); ?>" style="border-radius: 1rem; overflow: hidden; background: white; text-decoration: none; color: inherit; transition: all 0.3s ease; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 25px -5px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)';">
                    <div style="position: relative; height: 300px;">
                        <img src="https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="padding: 2rem; color: var(--color-stone-900);">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">
                            <span style="color: #0d9488;">LUXURY</span>
                            <span style="color: var(--color-stone-300);">•</span>
                            <span style="color: var(--color-stone-500);">easy</span>
                        </div>
                        <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 1rem; color: var(--color-stone-900);">
                            Luxury Everest Helicopter Tour
                        </h3>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--color-stone-500);"><i class="fa-regular fa-clock"></i> 1 Days</span>
                            <span style="font-size: 1.5rem; font-weight: 600; color: #0d9488;">$1,999</span>
                        </div>
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; text-align: center; color: var(--color-stone-900); font-weight: 600;">
                            Book Now <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i>
                        </div>
                    </div>
                </a>
                
                <!-- Tour 2 -->
                <a href="<?php echo url('public/premium_tour_detail.php?id=mustang'); ?>" style="border-radius: 1rem; overflow: hidden; background: white; text-decoration: none; color: inherit; transition: all 0.3s ease; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 25px -5px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)';">
                    <div style="position: relative; height: 300px;">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="padding: 2rem; color: var(--color-stone-900);">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">
                            <span style="color: #0d9488;">ADVENTURE</span>
                            <span style="color: var(--color-stone-300);">•</span>
                            <span style="color: var(--color-stone-500);">moderate</span>
                        </div>
                        <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 1rem; color: var(--color-stone-900);">
                            Upper Mustang Expedition
                        </h3>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--color-stone-500);"><i class="fa-regular fa-clock"></i> 12 Days</span>
                            <span style="font-size: 1.5rem; font-weight: 600; color: #0d9488;">$7,199</span>
                        </div>
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; text-align: center; color: var(--color-stone-900); font-weight: 600;">
                            Book Now <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Request access -->
<section id="contact" style="padding: 8rem 0; background: white; color: var(--color-stone-900);">
    <div class="container" style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 3.5rem; font-family: var(--font-serif); margin-bottom: 1.5rem; text-align: center; color: var(--color-stone-900);">
            Request Private Access
        </h2>
        <p style="font-size: 1.25rem; color: var(--color-stone-500); margin-bottom: 4rem; line-height: 1.8; text-align: center; max-width: 600px; margin-left: auto; margin-right: auto;">
            Interested in exclusive experiences? Complete the form below to begin your bespoke journey consultation.
        </p>
        
        <form action="<?php echo url('actions/inquiries/submit_request.php'); ?>" method="POST" style="background: #f8fafc; padding: 3rem; border-radius: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.75rem; font-size: 0.9rem; font-weight: 600; color: var(--color-stone-700);">Full Name</label>
                <input type="text" name="full_name" required style="width: 100%; padding: 1rem; background: white; border: 1px solid var(--color-stone-200); color: var(--color-stone-900); border-radius: 0.75rem; font-size: 1rem;">
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.75rem; font-size: 0.9rem; font-weight: 600; color: var(--color-stone-700);">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 1rem; background: white; border: 1px solid var(--color-stone-200); color: var(--color-stone-900); border-radius: 0.75rem; font-size: 1rem;">
                <small style="color: var(--color-stone-400); display: block; margin-top: 0.5rem;">Use the email associated with your account if you are a registered member.</small>
            </div>
            <div style="margin-bottom: 2.5rem;">
                <label style="display: block; margin-bottom: 0.75rem; font-size: 0.9rem; font-weight: 600; color: var(--color-stone-700);">Interests / Requirements</label>
                <textarea name="details" rows="4" style="width: 100%; padding: 1rem; background: white; border: 1px solid var(--color-stone-200); color: var(--color-stone-900); border-radius: 0.75rem; font-size: 1rem;"></textarea>
            </div>
            <button type="submit" class="btn" style="width: 100%; background: var(--color-stone-900); color: white; padding: 1.25rem; font-weight: 600; font-size: 1.1rem; border: none; cursor: pointer; border-radius: 50px;">
                Submit Request <i class="fa-solid fa-paper-plane" style="margin-left: 0.75rem;"></i>
            </button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
