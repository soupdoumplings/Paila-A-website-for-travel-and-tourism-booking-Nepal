<?php
require_once __DIR__ . '/../helpers/security.php';
secure_session_start();

$base = '../';
require_once $base . 'helpers/functions.php';
require_once $base . 'config/db.php';

require_login();

$message = $_GET['message'] ?? null;
$error = $_GET['error'] ?? null;
$detailed_errors = $_SESSION['import_errors'] ?? [];
unset($_SESSION['import_errors']);

$pageTitle = 'Import Packages | Admin';
include $base . 'includes/header.php';
?>

<style>
.import-container {
    max-width: 1000px;
    margin: -2rem auto 0;
    padding: 0 2rem 5rem;
    position: relative;
    z-index: 10;
}
.import-card {
    background: white;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: var(--luxury-shadow);
    border: 1px solid var(--luxury-border);
    margin-bottom: 2rem;
}
.import-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-stone-900);
}
.import-card p {
    color: var(--color-stone-600);
}
.upload-zone {
    border: 2px dashed var(--color-stone-300);
    border-radius: 1rem;
    padding: 4rem 2rem;
    text-align: center;
    background: var(--color-stone-50);
    transition: border-color 0.25s ease, background 0.25s ease, transform 0.25s ease;
    cursor: pointer;
}
.upload-zone:hover,
.upload-zone.dragover {
    border-color: var(--color-gold-deep);
    background: #fffbeb;
    transform: translateY(-2px);
}
.file-input-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1.4rem;
    border-radius: 0.5rem;
    background: var(--color-stone-900);
    color: white;
    cursor: pointer;
    font-weight: 600;
}
.import-options {
    margin-top: 2.5rem;
    display: grid;
    gap: 1rem;
}
.import-option {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem;
    border: 1px solid var(--color-stone-200);
    border-radius: 0.75rem;
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.import-option:hover {
    border-color: var(--color-gold-deep);
    background: var(--color-stone-50);
}
.import-option input[type="radio"] {
    width: 22px;
    height: 22px;
    accent-color: var(--color-emerald-700);
}
.success-msg {
    background: var(--color-emerald-50);
    border: 1px solid rgba(4, 120, 87, 0.22);
    color: var(--color-teal-900);
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    margin: 1.5rem 0;
}
.error-msg {
    background: #fee2e2;
    border: 1px solid var(--color-red-500);
    color: #991b1b;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    margin: 1.5rem 0;
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
            <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Import Packages</h1>
        </div>
    </section>
</div>

<main class="import-container">
    <a href="export.php" style="color: #78716c; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
        <i class="fa-solid fa-arrow-left"></i> Back to Export
    </a>

    <section class="import-card">
        <h2>Import Tours from JSON</h2>
        <p>Upload a trusted JSON export to add or update tour packages.</p>

        <?php if ($message): ?>
            <div class="success-msg"><?php echo e($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo e($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($detailed_errors)): ?>
            <div class="error-msg" style="background: #fff1f2; border-color: #fda4af;">
                <strong style="display: block; margin-bottom: 0.5rem;">The following rows need attention:</strong>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem;">
                    <?php foreach ($detailed_errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="process_import.php" method="POST" enctype="multipart/form-data" id="importForm">
            <?php echo csrf_field(); ?>
            <div class="upload-zone" id="uploadZone">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: #78716c; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">Drag and drop your JSON file here</p>
                <p style="font-size: 0.9rem; color: #a8a29e; margin-bottom: 1.5rem;">or</p>
                <label for="jsonFile" class="file-input-label">
                    <i class="fa-solid fa-file-arrow-up"></i> Choose File
                </label>
                <input type="file" id="jsonFile" name="jsonFile" accept=".json,application/json" style="display: none;" required>
                <p id="fileName" style="margin-top: 1rem; font-weight: 600; color: var(--color-teal-900);"></p>
            </div>

            <div class="import-options">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.25rem; color: #1c1917;">Import Options</h3>

                <label class="import-option">
                    <input type="radio" name="import_mode" value="skip" checked>
                    <div>
                        <strong>Skip Duplicates</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #78716c;">Skip tours that already exist based on title.</p>
                    </div>
                </label>

                <label class="import-option">
                    <input type="radio" name="import_mode" value="update">
                    <div>
                        <strong>Update Duplicates</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #78716c;">Update existing tours with new data.</p>
                    </div>
                </label>

                <label class="import-option">
                    <input type="radio" name="import_mode" value="replace">
                    <div>
                        <strong>Replace All</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #78716c;">Delete existing tours and import a fresh collection.</p>
                    </div>
                </label>
            </div>

            <button type="submit" class="admin-btn btn-green" id="importBtn" disabled style="width: 100%; margin-top: 2rem; padding: 1.25rem;">
                <i class="fa-solid fa-download"></i> Import Tours Now
            </button>
        </form>
    </section>

    <section class="import-card">
        <h2>Import Checklist</h2>
        <ul style="color: #57534e; line-height: 2; margin: 1rem 0 0; padding-left: 1.25rem;">
            <li>Use JSON files exported from this project whenever possible.</li>
            <li>Export a backup before using Replace All.</li>
            <li>Invalid records are skipped and shown as row-level errors.</li>
            <li>Uploaded files are validated as JSON before database changes run.</li>
        </ul>
    </section>
</main>

<script>
(function() {
    var uploadZone = document.getElementById('uploadZone');
    var fileInput = document.getElementById('jsonFile');
    var fileName = document.getElementById('fileName');
    var importBtn = document.getElementById('importBtn');

    if (!uploadZone || !fileInput || !fileName || !importBtn) return;

    function acceptFile(fileList) {
        var file = fileList && fileList[0];
        if (!file) return;

        if (file.name.toLowerCase().endsWith('.json')) {
            fileName.textContent = file.name;
            importBtn.disabled = false;
            return;
        }

        alert('Please upload a JSON file');
    }

    fileInput.addEventListener('change', function(e) {
        acceptFile(e.target.files);
    });

    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        fileInput.files = e.dataTransfer.files;
        acceptFile(e.dataTransfer.files);
    });
})();
</script>

<?php include $base . 'includes/footer.php'; ?>
