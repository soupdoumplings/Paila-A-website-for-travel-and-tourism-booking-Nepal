<?php
session_start();
$base = '../';
require_once $base . 'helpers/functions.php';
require_once $base . 'config/db.php';

// Verify login
require_login();
?>

<div class="admin-hero">
<?php include $base . 'includes/header.php'; ?>
// View status signals
$success = isset($_GET['success']) ? $_GET['success'] : null;

<style>
/* Component custom styling */
.export-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}
.export-card {
    background: white;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}
.export-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-stone-900);
}
.export-card p {
    color: var(--color-stone-600);
    margin-bottom: 2rem;
}
.export-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}
.export-option {
    border: 2px solid var(--color-stone-200);
    border-radius: 1rem;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    background: white;
}
.export-option:hover {
    border-color: var(--color-emerald-700);
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.export-option .icon {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    color: var(--color-emerald-700);
}
.export-option h3 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
    color: var(--color-stone-900);
}
.export-option .desc {
    font-size: 0.95rem;
    color: var(--color-stone-600);
    line-height: 1.6;
    flex-grow: 1;
}
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}
.stat-card {
    background: var(--color-emerald-50);
    padding: 1.5rem;
    border-radius: 1rem;
    border: 1px solid var(--color-emerald-700);
}
</style>

<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <span style="opacity: 0.3;">/</span>
            <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Data Management</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Export Center</h1>
    </div>
</section>
</div>

<div class="export-container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <div class="export-card">
        <!-- Export dashboard header -->
        <p>Backup your tours data for migration, sharing with partners, or data analysis</p>
        
        <?php if ($success === 'json'): ?>
        <div class="success-msg">
            ✓ JSON file generated successfully! Check your downloads folder.
        </div>
        <?php elseif ($success === 'csv'): ?>
        <div class="success-msg">
            ✓ CSV file generated successfully! Check your downloads folder.
        </div>
        <?php endif; ?>
        
        <!-- Selection export formats -->
            <a href="process_export.php?format=json" class="export-option">
                <div class="icon">📄</div>
                <h3>Export to JSON</h3>
                <p class="desc">Download all tours as a JSON file. Perfect for data migration, backups, or sharing with partners.</p>
            </a>
            
            <a href="process_export.php?format=csv" class="export-option">
                <div class="icon">📊</div>
                <h3>Export to CSV</h3>
                <p class="desc">Download tours as CSV spreadsheet. Great for analysis in Excel or Google Sheets.</p>
            </a>
            
            <a href="process_export.php?format=season_json" class="export-option">
                <div class="icon">🌸</div>
                <h3>Export by Season</h3>
                <p class="desc">Generate 4 separate JSON files organized by season (Spring, Autumn, Monsoon, Year-round).</p>
            </a>
            
            <a href="import.php" class="export-option" style="border-color: #047857; background: #f0fdf4;">
                <div class="icon" style="color: #047857;">📥</div>
                <h3>Import from JSON</h3>
                <p class="desc">Upload a JSON file to restore tours or migrate data from another system.</p>
            </a>
        </div>
        
        <?php
        // Retrieve data statistics
        try {
            $total_tours = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
            $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
            $pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
        } catch (Exception $e) {
            $total_tours = $total_bookings = $pending_bookings = 0;
        }
        ?>
        
        <!-- Display summary metrics -->
            <div class="stat-card">
                <div class="label">Total Tours</div>
                <div class="value"><?php echo $total_tours; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Total Bookings</div>
                <div class="value"><?php echo $total_bookings; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Pending Bookings</div>
                <div class="value"><?php echo $pending_bookings; ?></div>
            </div>
        </div>
    </div>
    
    <div class="export-card">
        <!-- Export usage cases -->
        <ul style="color: #57534e; line-height: 2;">
            <li><strong>Backup:</strong> Regular exports protect your data from loss</li>
            <li><strong>Migration:</strong> Move tours to another system or database</li>
            <li><strong>Partners:</strong> Share tour data with travel partners</li>
            <li><strong>Analysis:</strong> Import into Excel for business intelligence</li>
            <li><strong>Development:</strong> Test new features with real data</li>
        </ul>
    </div>
</div>

<?php include $base . 'includes/footer.php'; ?>
