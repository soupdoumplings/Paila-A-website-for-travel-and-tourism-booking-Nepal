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
// Retrieve status messages
$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$detailed_errors = isset($_SESSION['import_errors']) ? $_SESSION['import_errors'] : [];
unset($_SESSION['import_errors']);

<style>
/* Component custom styling */
.import-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}
.import-card {
    background: white;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}
.import-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-stone-900);
}
.import-card p {
    color: var(--color-stone-600);
    margin-bottom: 2rem;
}
.upload-zone {
    border: 2px dashed var(--color-stone-300);
    border-radius: 1rem;
    padding: 4rem;
    text-align: center;
    background: var(--color-stone-50);
    transition: all 0.3s;
    cursor: pointer;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--color-emerald-700);
    background: var(--color-emerald-50);
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
    padding: 1.5rem;
    border: 1px solid var(--color-stone-200);
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}
.import-option:hover {
    border-color: var(--color-emerald-700);
    background: var(--color-stone-50);
}
.import-option input[type="radio"] {
    width: 22px;
    height: 22px;
    accent-color: var(--color-emerald-700);
}
.success-msg {
    background: var(--color-emerald-50);
    border: 1px solid var(--color-emerald-700);
    color: #065f46;
    padding: 1.25rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 2rem;
}
.error-msg {
    background: #fee2e2;
    border: 1px solid var(--color-red-500);
    color: #991b1b;
    padding: 1.25rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 2rem;
}
</style>

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

<div class="import-container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <!-- Navigation back link -->
        <a href="export.php" style="color: #78716c; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Back to Export
        </a>
    </div>

    <!-- Package import form -->
        <h2>📥 Import Tours from JSON</h2>
        <p>Upload a JSON file to import tours into your database</p>
        
        <?php if ($message): ?>
        <div class="success-msg">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error-msg">
            ✗ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($detailed_errors)): ?>
        <div class="error-msg" style="background: #fff1f2; border: 1px solid #fda4af;">
            <strong style="display: block; margin-bottom: 0.5rem;">The following errors occurred during import:</strong>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem;">
                <?php foreach ($detailed_errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form action="process_import.php" method="POST" enctype="multipart/form-data" id="importForm">
            <div class="upload-zone" id="uploadZone">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: #78716c; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">Drag and drop your JSON file here</p>
                <p style="font-size: 0.9rem; color: #a8a29e; margin-bottom: 1.5rem;">or</p>
                <label for="jsonFile" class="file-input-label">
                    <i class="fa-solid fa-file-arrow-up"></i> Choose File
                </label>
                <input type="file" id="jsonFile" name="jsonFile" accept=".json" style="display: none;" required>
                <p id="fileName" style="margin-top: 1rem; font-weight: 600; color: #047857;"></p>
            </div>
            
            <!-- Advanced import options -->
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #1c1917;">Import Options</h3>
                
                <label class="import-option">
                    <input type="radio" name="import_mode" value="skip" checked>
                    <div>
                        <strong>Skip Duplicates</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #78716c;">Skip tours that already exist (based on title)</p>
                    </div>
                </label>
                
                <label class="import-option">
                    <input type="radio" name="import_mode" value="update">
                    <div>
                        <strong>Update Duplicates</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #78716c;">Update existing tours with new data</p>
                    </div>
                </label>
                
                <label class="import-option">
                    <input type="radio" name="import_mode" value="replace">
                    <div>
                        <strong>Replace All</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #78716c;">Delete all existing tours and import fresh</p>
                    </div>
                </label>
            </div>
            
            <button type="submit" class="admin-btn btn-green" id="importBtn" disabled style="width: 100%; margin-top: 2rem; padding: 1.25rem;">
                <i class="fa-solid fa-download"></i> Import Tours Now
            </button>
        </form>
    </div>
    
    <!-- Import user guide -->
        <h2>ℹ️ Import Instructions</h2>
        <ul style="color: #57534e; line-height: 2;">
            <li><strong>JSON Format:</strong> Upload files exported from this system</li>
            <li><strong>Valid Fields:</strong> All standard tour fields will be imported</li>
            <li><strong>Validation:</strong> Invalid data will be skipped with warnings</li>
            <li><strong>Backup First:</strong> Export your current data before replacing</li>
        </ul>
    </div>
</div>

<script>
// Client file handling
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('jsonFile');
const fileName = document.getElementById('fileName');
const importBtn = document.getElementById('importBtn');

fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        fileName.textContent = file.name;
        importBtn.disabled = false;
    }
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
    
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.json')) {
        fileInput.files = e.dataTransfer.files;
        fileName.textContent = file.name;
        importBtn.disabled = false;
    } else {
        alert('Please upload a JSON file');
    }
});
</script>

<?php include $base . 'includes/footer.php'; ?>
