<?php
require_once __DIR__ . '/../helpers/security.php';
secure_session_start();

$base = '../';
require_once $base . 'helpers/functions.php';
require_once $base . 'config/db.php';

require_login();

$success = $_GET['success'] ?? null;

try {
    $total_tours = (int) $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
    $total_bookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pending_bookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    $total_tours = $total_bookings = $pending_bookings = 0;
}

$pageTitle = 'Export Center | Admin';
include $base . 'includes/header.php';
?>

<style>
.export-container {
    max-width: 1000px;
    margin: -2rem auto 0;
    padding: 0 2rem 5rem;
    position: relative;
    z-index: 10;
}
.export-card {
    background: white;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: var(--luxury-shadow);
    border: 1px solid var(--luxury-border);
    margin-bottom: 2rem;
}
.export-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-stone-900);
}
.export-card p {
    color: var(--color-stone-600);
}
.export-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    margin-top: 2rem;
}
.export-option-form {
    margin: 0;
}
.export-option {
    min-height: 100%;
    width: 100%;
    border: 1px solid var(--color-stone-200);
    border-radius: 0.75rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    background: white;
    color: inherit;
    text-align: left;
    font-family: inherit;
}
.export-option:hover,
.export-option:focus-visible {
    border-color: var(--color-gold-deep);
    transform: translateY(-4px);
    box-shadow: 0 18px 45px rgba(28, 25, 23, 0.12);
    outline: none;
}
.export-option .icon {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--color-emerald-700);
}
.export-option h3 {
    font-size: 1.15rem;
    margin-bottom: 0.65rem;
    color: var(--color-stone-900);
}
.export-option .desc {
    font-size: 0.92rem;
    color: var(--color-stone-600);
    line-height: 1.6;
    margin: 0;
}
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}
.stat-card {
    background: var(--color-emerald-50);
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(4, 120, 87, 0.18);
}
.stat-card .label {
    color: var(--color-stone-600);
    font-size: 0.85rem;
    margin-bottom: 0.35rem;
}
.stat-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-stone-900);
}
.success-msg {
    background: var(--color-emerald-50);
    border: 1px solid rgba(4, 120, 87, 0.22);
    color: #065f46;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    margin-top: 1.5rem;
}
</style>

<div class="admin-hero">
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

<main class="export-container">
    <section class="export-card">
        <h2>Backup and Share Tour Data</h2>
        <p>Download package data for backups, partner sharing, migration, or analysis.</p>

        <?php if ($success): ?>
            <div class="success-msg">
                <?php if ($success === 'season'): ?>
                    Season files were generated successfully in the data folder.
                <?php elseif ($success === 'json'): ?>
                    JSON export generated successfully.
                <?php elseif ($success === 'csv'): ?>
                    CSV export generated successfully.
                <?php else: ?>
                    Export action completed successfully.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="export-options">
            <a href="process_export.php?format=json" class="export-option">
                <div class="icon"><i class="fa-regular fa-file-code"></i></div>
                <h3>Export to JSON</h3>
                <p class="desc">Download all tours as a structured JSON backup.</p>
            </a>

            <a href="process_export.php?format=csv" class="export-option">
                <div class="icon"><i class="fa-solid fa-table"></i></div>
                <h3>Export to CSV</h3>
                <p class="desc">Download a spreadsheet-friendly file for analysis.</p>
            </a>

            <form action="process_export.php" method="POST" class="export-option-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="format" value="season_json">
                <button type="submit" class="export-option">
                    <div class="icon"><i class="fa-solid fa-seedling"></i></div>
                    <h3>Generate Season Files</h3>
                    <p class="desc">Create seasonal JSON files for Spring, Autumn, Monsoon, and year-round packages.</p>
                </button>
            </form>

            <a href="import.php" class="export-option" style="border-color: rgba(4,120,87,0.35); background: #f0fdf4;">
                <div class="icon"><i class="fa-solid fa-file-import"></i></div>
                <h3>Import from JSON</h3>
                <p class="desc">Restore or migrate tours using a trusted JSON export.</p>
            </a>
        </div>

        <div class="stats">
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
    </section>

    <section class="export-card">
        <h2>Use Cases</h2>
        <ul style="color: #57534e; line-height: 2; margin: 1rem 0 0; padding-left: 1.25rem;">
            <li><strong>Backup:</strong> Keep a copy of package inventory outside the database.</li>
            <li><strong>Migration:</strong> Move package content between environments.</li>
            <li><strong>Partners:</strong> Share curated Nepal tour data with travel partners.</li>
            <li><strong>Analysis:</strong> Review pricing, seasons, and demand in spreadsheets.</li>
        </ul>
    </section>
</main>

<?php include $base . 'includes/footer.php'; ?>
