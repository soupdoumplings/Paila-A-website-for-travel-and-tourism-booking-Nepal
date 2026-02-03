<?php
session_start();
ob_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';
require_login();

// Dashboard statistics

// Initialize stat counters
$totalTours = 0;
$totalBookings = 0;
$pendingBookings = 0;
$totalRevenue = 0;
$newInquiries = 0;

try {
    // Count total tours
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tours");
    $totalTours = $stmt->fetch()['count'];

    // Count total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $totalBookings = $stmt->fetch()['count'];

    // Count pending bookings
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $pendingBookings = $stmt->fetch()['count'];

    // Count new inquiries
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'");
    $newInquiries = $stmt->fetch()['count'];

    // Calculate total revenue
    $stmt = $pdo->query("
        SELECT SUM(b.travelers * t.price) as revenue 
        FROM bookings b 
        JOIN tours t ON b.tour_id = t.id 
        WHERE b.status = 'confirmed'
    ");
    $totalRevenue = $stmt->fetch()['revenue'] ?? 0;

} catch (Exception $e) {
    // Log database errors
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Nepal Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* CSS styles */
    </style>
</head>
<body style="background: var(--color-stone-100);">

    <!-- Admin header section -->
    <div class="admin-hero">
        <?php 
            $base = '../';
            include '../includes/header.php'; 
        ?>

        <!-- Dashboard title area -->
        <section style="padding: 6rem 0 5rem;">
            <div class="container">
                <p class="badge" style="margin-bottom: 0.75rem;">RESTRICTED AREA</p>
                <h1 style="color: var(--color-stone-900); font-family: var(--font-serif); font-size: 3.5rem; margin: 0;">Main Command</h1>
                <p style="font-size: 1.1rem; color: var(--color-stone-600); max-width: 600px; margin-top: 1rem;">
                    Oversee operations, manage bookings, and curate the finest journeys in Nepal.
                </p>
            </div>
        </section>
    </div>

    <div class="container" style="margin-top: -2rem; position: relative; z-index: 10;">
        
        <!-- Statistics cards grid -->
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 3rem;">
            <!-- Total Tours -->
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="width: 48px; height: 48px; background: #10b981; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fa-solid fa-map-location-dot" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $totalTours; ?></div>
                <div style="font-size: 0.875rem; color: var(--color-stone-600);">Total Tours</div>
            </div>

            <!-- Total Bookings -->
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="width: 48px; height: 48px; background: #f59e0b; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fa-solid fa-calendar-check" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $totalBookings; ?></div>
                <div style="font-size: 0.875rem; color: var(--color-stone-600);">Total Bookings</div>
            </div>

            <!-- Pending -->
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="width: 48px; height: 48px; background: #ef4444; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fa-regular fa-clock" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $pendingBookings; ?></div>
                <div style="font-size: 0.875rem; color: var(--color-stone-600);">Pending</div>
            </div>

            <!-- Revenue -->
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="width: 48px; height: 48px; background: #3b82f6; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fa-solid fa-sack-dollar" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;">Rs <?php echo number_format($totalRevenue); ?></div>
                <div style="font-size: 0.875rem; color: var(--color-stone-600);">Revenue</div>
            </div>

            <!-- Inquiries -->
            <a href="manage_inquiries.php" style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width: 48px; height: 48px; background: #8b5cf6; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fa-solid fa-envelope" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $newInquiries; ?></div>
                <div style="font-size: 0.875rem; color: var(--color-stone-600);">New Inquiries</div>
            </a>
        </div>

        <!-- Section tab navigation -->
        <div style="border-bottom: 1px solid var(--color-stone-300); margin-bottom: 2rem;">
            <div style="display: flex; gap: 2rem;">
                <button class="tab-btn active" data-tab="tours" style="padding: 1rem 0; border: none; background: none; font-weight: 600; color: var(--color-stone-900); border-bottom: 2px solid var(--color-stone-900); cursor: pointer;">Tours</button>
                <button class="tab-btn" data-tab="bookings" style="padding: 1rem 0; border: none; background: none; font-weight: 600; color: var(--color-stone-500); border-bottom: 2px solid transparent; cursor: pointer;">Bookings</button>
            </div>
        </div>

        <!-- Tour management section -->
        <div id="tours-section">
            <!-- Search filter input -->
            <div style="margin-bottom: 1rem; position: relative; max-width: 400px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--color-stone-400);"></i>
                <input type="text" id="admin-tour-search" placeholder="Search tours by name or location..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 0.9rem;">
            </div>

            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 2rem;">
                <div style="padding: 1rem 1.5rem; background: var(--color-emerald-50); border-bottom: 1px solid var(--color-stone-200); font-weight: 600;">Your packages (create new via form → auto-generated page)</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-stone-50);">
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Tour</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Location</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Price</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-tours-body">
                        <?php
                        // Check ajax search
                        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
                        $query = "SELECT id, title, location, price, duration, category, is_featured, created_by FROM tours";
                        $params = [];
                        
                        if ($searchTerm) {
                            $query .= " WHERE title LIKE :search OR location LIKE :search";
                            $params['search'] = "%$searchTerm%";
                        }
                        
                        $query .= " ORDER BY is_featured DESC, id DESC LIMIT 50";
                        
                        try {
                            $stmt = $pdo->prepare($query);
                            $stmt->execute($params);
                            $dbTours = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) { $dbTours = []; }
                        
                        if (empty($dbTours)) {
                            echo '<tr><td colspan="4" style="padding: 2rem; text-align: center; color: var(--color-stone-500);">No tours found.</td></tr>';
                        } else {
                            foreach ($dbTours as $t): 
                                $canEdit = is_super_admin() || (isset($t['created_by']) && $t['created_by'] == $_SESSION['user_id']);
                            ?>
                            <tr style="border-bottom: 1px solid var(--color-stone-100); <?php echo $t['is_featured'] ? 'background: rgba(14,165,233,0.02);' : ''; ?>">
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($t['title']); ?></div>
                                    <?php if($t['is_featured']): ?>
                                        <span style="font-size: 0.65rem; background: var(--color-emerald-100); color: var(--color-emerald-700); padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700; text-transform: uppercase; margin-top: 0.25rem; display: inline-block;">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: var(--color-stone-600);"><?php echo htmlspecialchars($t['location']); ?></td>
                                <td style="padding: 0.75rem 1rem;">Rs <?php echo number_format($t['price'], 0); ?></td>
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="display: flex; gap: 1rem; align-items: center;">
                                        <a href="../public/package_detail/?id=<?php echo (int)$t['id']; ?>" title="View page" style="color: var(--color-stone-600); font-size: 1.1rem; transition: color 0.2s;">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <?php if($canEdit): ?>
                                        <a href="tour_form.php?id=<?php echo (int)$t['id']; ?>" title="Edit" style="color: var(--color-stone-600); font-size: 1.1rem; transition: color 0.2s;">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" onclick="handleDeleteTour(<?php echo (int)$t['id']; ?>, '<?php echo addslashes($t['title']); ?>')" title="Delete" style="background: none; border: none; color: var(--color-red-500); font-size: 1.1rem; transition: color 0.2s; cursor: pointer;">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; 
                        }
                        
                        if (isset($_GET['ajax_search'])) {
                            exit;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Layout ends -->
        </div>

        <!-- Recent bookings section -->
        <div id="bookings-section" style="display: none;">
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Recent Bookings</h2>
                <a href="manage_bookings.php" style="background: var(--color-amber-500); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; text-decoration: none;">View All Bookings &rarr;</a>
            </div>
            
            <?php
            // Fetch recent bookings
            $recentBookings = [];
            try {
                $stmt = $pdo->query("
                    SELECT b.*, t.title as tour_title 
                    FROM bookings b
                    LEFT JOIN tours t ON b.tour_id = t.id
                    ORDER BY b.created_at DESC 
                    LIMIT 5
                ");
                $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
            ?>

            <?php if(empty($recentBookings)): ?>
                <div style="background: white; padding: 3rem; border-radius: 1rem; text-align: center; border: 1px dashed var(--color-stone-300);">
                    <p style="color: var(--color-stone-500);">No bookings found yet.</p>
                </div>
            <?php else: ?>
                <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--color-stone-50); border-bottom: 1px solid var(--color-stone-200);">
                                <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">ID</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Customer</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Tour</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Total</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Status</th>
                                <th style="padding: 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-stone-600);">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $rb): ?>
                            <tr style="border-bottom: 1px solid var(--color-stone-100);">
                                <td style="padding: 1rem; font-weight: 600; color: var(--color-stone-500);">#<?php echo $rb['id']; ?></td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($rb['customer_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--color-stone-500);"><?php echo date('M d', strtotime($rb['created_at'])); ?></div>
                                </td>
                                <td style="padding: 1rem; color: var(--color-stone-600);"><?php echo htmlspecialchars($rb['tour_title'] ?? '[Deleted Tour]'); ?></td>
                                <td style="padding: 1rem; font-variant-numeric: tabular-nums;"><?php echo $rb['travelers']; ?> pax</td>
                                <td style="padding: 1rem;">
                                    <?php if($rb['status'] == 'pending'): ?>
                                        <span style="background: #fffbeb; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; border: 1px solid #fcd34d;">Pending</span>
                                    <?php elseif($rb['status'] == 'confirmed'): ?>
                                        <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; border: 1px solid #6ee7b7;">Confirmed</span>
                                    <?php else: ?>
                                        <span style="background: #fef2f2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; border: 1px solid #fca5a5;">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <a href="manage_bookings.php" style="color: var(--color-stone-400); font-size: 1.1rem; text-decoration: none; transition: color 0.2s;" title="Manage">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="text-align: center; margin-top: 1.5rem;">
                     <a href="manage_bookings.php" style="color: var(--color-stone-500); font-weight: 500; font-size: 0.9rem; text-decoration: none; border-bottom: 1px solid var(--color-stone-300);">View all bookings in manager</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // JS logic
    </script>

    </div>

    <!-- Tour viewing modal -->
    <div id="viewTourModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <button onclick="closeModal('viewTourModal')" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-stone-600);">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div id="viewTourContent"></div>
        </div>
    </div>

    <!-- Tour editing modal -->
    <div id="editTourModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Edit Tour</h2>
                <button onclick="closeModal('editTourModal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-stone-600);">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="editTourForm" onsubmit="updateTour(event)">
                <div id="editTourFormContent"></div>
            </form>
        </div>
    </div>


    <!-- Action confirmation modal -->
    <div id="confirmModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
            <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 1.5rem;"></i>
            </div>
            <h2 id="confirmTitle" style="font-size: 1.5rem; font-weight: 700; color: var(--color-stone-900); margin-bottom: 0.5rem;">Are you sure?</h2>
            <p id="confirmMessage" style="color: var(--color-stone-500); margin-bottom: 2rem; line-height: 1.5;">This action cannot be undone. Do you really want to proceed?</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button id="confirmCancel" style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-stone-200); background: white; color: var(--color-stone-600); font-weight: 600; cursor: pointer;">Cancel</button>
                <button id="confirmProceed" style="padding: 0.75rem; border-radius: 0.5rem; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-dashboard.js"></script>
    <script>
        // Modal confirmation logic
        function showCustomConfirm(title, message, confirmText = 'Yes, Delete') {
            return new Promise((resolve) => {
                const modal = document.getElementById('confirmModal');
                const titleEl = document.getElementById('confirmTitle');
                const msgEl = document.getElementById('confirmMessage');
                const proceedBtn = document.getElementById('confirmProceed');
                const cancelBtn = document.getElementById('confirmCancel');

                titleEl.textContent = title;
                msgEl.textContent = message;
                proceedBtn.textContent = confirmText;
                
                modal.style.display = 'flex';

                const handleProceed = () => {
                    modal.style.display = 'none';
                    cleanup();
                    resolve(true);
                };

                const handleCancel = () => {
                    modal.style.display = 'none';
                    cleanup();
                    resolve(false);
                };

                const cleanup = () => {
                    proceedBtn.removeEventListener('click', handleProceed);
                    cancelBtn.removeEventListener('click', handleCancel);
                };

                proceedBtn.addEventListener('click', handleProceed);
                cancelBtn.addEventListener('click', handleCancel);
            });
        }

        async function handleDeleteTour(id, title) {
            const confirmed = await showCustomConfirm(
                'Delete Tour', 
                `Are you sure you want to delete "${title}"? This will remove the package permanently.`
            );
            if (confirmed) {
                window.location.href = `process_tour.php?action=delete&id=${id}`;
            }
        }

        // Sidebar interaction logic
        document.addEventListener('DOMContentLoaded', function () {
            const userIcon = document.getElementById('user-icon');
            const sidebar = document.getElementById('user-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const closeBtn = document.getElementById('close-sidebar');

            if (userIcon) {
                userIcon.addEventListener('click', function () {
                    sidebar.style.transform = 'translateX(0)';
                    overlay.style.opacity = '1';
                    overlay.style.visibility = 'visible';
                });
            }

            function closeSidebar() {
                sidebar.style.transform = 'translateX(100%)';
                overlay.style.opacity = '0';
                overlay.style.visibility = 'hidden';
            }

            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            // Real-time tour search
            const adminSearch = document.getElementById('admin-tour-search');
            const adminToursBody = document.getElementById('admin-tours-body');
            
            if(adminSearch && adminToursBody) {
                let searchTimeout;
                adminSearch.addEventListener('input', function() {
                    const query = this.value;
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        fetch(`index.php?ajax_search=1&search=${encodeURIComponent(query)}`)
                            .then(res => res.text())
                            .then(html => {
                                adminToursBody.innerHTML = html;
                            });
                    }, 300);
                });
            }
        });
    </script>

</body>
</html>
