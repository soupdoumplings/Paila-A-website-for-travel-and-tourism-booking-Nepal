<?php
require_once __DIR__ . '/../../helpers/security.php';
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    secure_session_start();
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
$category_label = $category ? ucwords(str_replace(['_', '-'], ' ', $category)) : 'Journey';
$difficulty = isset($tour['difficulty']) && $tour['difficulty'] ? $tour['difficulty'] : null;
$max_group = isset($tour['max_group']) && $tour['max_group'] ? $tour['max_group'] : null;
$traveler_max = $max_group ? max(1, (int) $max_group) : 20;
$highlights = isset($tour['highlights']) && $tour['highlights'] ? $tour['highlights'] : '';
$highlights_list = array_filter(array_map('trim', explode("\n", $highlights)));

// Nepal-specific fields
$best_season = isset($tour['best_season']) && $tour['best_season'] ? $tour['best_season'] : null;
$altitude_max = isset($tour['altitude_max']) && $tour['altitude_max'] ? (int)$tour['altitude_max'] : null;
$permits = isset($tour['permit_requirements']) && $tour['permit_requirements'] ? $tour['permit_requirements'] : null;
$permits_list = $permits ? array_filter(array_map('trim', explode(",", $permits))) : [];

// Parse itinerary list
$itinerary_text = isset($tour['itinerary']) && $tour['itinerary'] ? $tour['itinerary'] : '';
$itinerary_days = array_filter(array_map('trim', explode("\n", $itinerary_text)));

// Parse included/excluded
$inclusions_text = isset($tour['inclusions']) && $tour['inclusions'] ? $tour['inclusions'] : '';
$inclusions_list = array_filter(array_map('trim', explode("\n", $inclusions_text)));
$exclusions_text = isset($tour['exclusions']) && $tour['exclusions'] ? $tour['exclusions'] : '';
$exclusions_list = array_filter(array_map('trim', explode("\n", $exclusions_text)));
$hero_lede = trim(preg_replace('/\s+/', ' ', (string) ($tour['description'] ?? '')));
if (strlen($hero_lede) > 180) {
    $hero_lede = substr($hero_lede, 0, 177) . '...';
}

$pageTitle = e($tour['title']) . ' | Nepal Tours';
include $base . 'includes/header.php';
?>
<style>
/* ——— Hero (matches second image) ——— */
.pd-hero {
    min-height: 82vh;
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 8rem 0 5rem;
    overflow: hidden;
}
.pd-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 18% 28%, rgba(251, 191, 36, 0.18), transparent 30%),
        linear-gradient(90deg, rgba(0, 0, 0, 0.58) 0%, rgba(0, 0, 0, 0.22) 44%, rgba(12, 10, 9, 0.14) 100%),
        linear-gradient(to top, rgba(12, 10, 9, 0.82) 0%, rgba(12, 10, 9, 0.12) 62%, rgba(12, 10, 9, 0.18) 100%);
}
.pd-hero::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: -1px;
    height: 8rem;
    background: linear-gradient(to top, #fafaf9, rgba(250, 250, 249, 0));
    pointer-events: none;
}
.pd-hero-inner {
    position: relative;
    z-index: 1;
    max-width: 920px;
    margin-left: 5vw;
}
.pd-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    width: fit-content;
    color: rgba(255,255,255,0.9);
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom: 1.75rem;
    text-decoration: none;
    padding: 0.55rem 0.85rem;
    border: 1px solid rgba(255, 253, 247, 0.26);
    border-radius: 999px;
    background: rgba(255, 253, 247, 0.08);
    backdrop-filter: blur(10px);
    transition: opacity 0.3s, transform 0.3s, border-color 0.3s;
}
.pd-back:hover {
    opacity: 1;
    transform: translateX(-3px);
    border-color: rgba(251, 191, 36, 0.54);
}
.pd-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    width: fit-content;
    margin-bottom: 1rem;
    padding: 0.55rem 0.95rem;
    border: 1px solid rgba(251, 191, 36, 0.42);
    border-radius: 999px;
    background: rgba(4, 47, 46, 0.78);
    color: #fffdf7;
    box-shadow: 0 14px 34px rgba(4, 47, 46, 0.24);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.13em;
}
.pd-category-badge::before {
    content: '';
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 50%;
    background: var(--color-amber-400);
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.16);
}
.pd-title {
    max-width: 880px;
    font-size: clamp(3.4rem, 8vw, 7.2rem);
    color: white;
    margin-bottom: 1.35rem;
    line-height: 0.92;
    font-weight: 500;
    text-shadow: 0 18px 45px rgba(0, 0, 0, 0.38);
}
.pd-hero-lede {
    max-width: 680px;
    color: rgba(255, 253, 247, 0.9);
    font-size: 1.05rem;
    line-height: 1.8;
    margin: 0 0 1.8rem;
}
.pd-meta { display: flex; flex-wrap: wrap; gap: 0.65rem; color: rgba(255,255,255,0.95); font-size: 0.86rem; }
.pd-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.58rem 0.82rem;
    border: 1px solid rgba(255, 253, 247, 0.18);
    border-radius: 999px;
    background: rgba(255, 253, 247, 0.1);
    backdrop-filter: blur(10px);
}

/* ——— Left column: About / Experience Highlights ——— */
.pd-content {
    padding: 5rem 0 6rem;
    background:
        linear-gradient(rgba(68, 64, 60, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(68, 64, 60, 0.035) 1px, transparent 1px),
        radial-gradient(circle at 82% 10%, rgba(251, 191, 36, 0.12), transparent 28%),
        #fafaf9;
    background-size: 92px 92px, 92px 92px, auto, auto;
}
.pd-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(320px, 0.75fr);
    gap: clamp(2rem, 4vw, 4rem);
    align-items: start;
    width: min(var(--site-width), calc(100% - 2rem));
    max-width: none;
    margin: 0 auto;
    padding: 0;
}
.pd-main {
    text-align: left;
    display: grid;
    gap: 1.35rem;
}
.pd-main h2 {
    font-size: clamp(2rem, 4vw, 3.35rem);
    font-weight: 500;
    color: #1c1917;
    margin-bottom: 1rem;
    font-family: var(--font-serif), serif;
    font-style: italic;
    line-height: 1.02;
}
.pd-main p {
    color: #57534e;
    line-height: 1.72;
    font-size: 1.22rem;
    font-family: "Cormorant Garamond", Georgia, serif !important;
    font-style: italic !important;
    font-weight: 500;
    margin-bottom: 0;
}
.pd-editorial-panel > p:not(.pd-kicker),
.pd-section-card > p:not(.pd-kicker),
.pd-inclusion-box .pd-check-list li span {
    font-family: "Cormorant Garamond", Georgia, serif !important;
    font-style: italic !important;
    font-weight: 500;
    letter-spacing: 0;
}
.pd-main ul { list-style: none; padding: 0; margin: 0; }
.pd-main li {
    color: #57534e;
    line-height: 1.65;
    font-size: 1rem;
    padding: 0.5rem 0;
    padding-left: 0;
    position: relative;
}
.pd-main li::before { content: none; }
.pd-editorial-panel,
.pd-section-card {
    background: rgba(255, 253, 249, 0.86);
    border: 1px solid rgba(68, 64, 60, 0.12);
    border-radius: 8px;
    box-shadow: 0 24px 60px rgba(28, 25, 23, 0.08);
    backdrop-filter: blur(12px);
}
.pd-editorial-panel {
    padding: clamp(2rem, 4vw, 3.25rem);
    position: relative;
    overflow: hidden;
}
.pd-editorial-panel::after {
    content: '';
    position: absolute;
    top: 1.1rem;
    right: 1.1rem;
    width: 5rem;
    height: 5rem;
    border-top: 1px solid rgba(251, 191, 36, 0.42);
    border-right: 1px solid rgba(251, 191, 36, 0.42);
    pointer-events: none;
}
.pd-section-card {
    padding: clamp(1.6rem, 3vw, 2.35rem);
}
.pd-kicker {
    margin: 0 0 0.8rem !important;
    color: var(--color-teal-900) !important;
    font-size: 0.72rem !important;
    font-family: var(--font-sans), sans-serif !important;
    font-style: normal !important;
    font-weight: 900;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.pd-overview-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1px;
    margin-top: 2rem;
    overflow: hidden;
    border: 1px solid rgba(68, 64, 60, 0.1);
    border-radius: 8px;
    background: rgba(68, 64, 60, 0.1);
}
.pd-overview-item {
    background: rgba(250, 250, 249, 0.92);
    padding: 1rem;
}
.pd-overview-item span {
    display: block;
    color: var(--color-stone-500);
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    margin-bottom: 0.35rem;
}
.pd-overview-item strong {
    color: var(--color-stone-900);
    font-size: 1rem;
}
.pd-feature-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.9rem;
}
.pd-feature-list li {
    display: grid;
    grid-template-columns: auto 1fr;
    align-items: start;
    gap: 0.75rem;
    padding: 0.95rem !important;
    border: 1px solid rgba(68, 64, 60, 0.1);
    border-radius: 8px;
    background: rgba(250, 250, 249, 0.82);
}
.pd-feature-list em {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 50%;
    background: var(--color-teal-900);
    color: var(--color-amber-400);
    font-style: normal;
    font-size: 0.72rem;
    font-weight: 900;
}
.pd-timeline-list {
    display: grid;
    gap: 0.85rem;
    list-style: none;
    padding: 0;
    margin: 0;
}
.pd-timeline-list li {
    display: grid;
    grid-template-columns: 5.4rem 1fr;
    gap: 1rem;
    align-items: start;
    padding: 1rem 0 !important;
    border-bottom: 1px solid rgba(68, 64, 60, 0.1);
}
.pd-timeline-list li:last-child { border-bottom: 0; }
.pd-day-pill {
    display: inline-flex;
    justify-content: center;
    padding: 0.42rem 0.65rem;
    border-radius: 999px;
    background: rgba(4, 47, 46, 0.08);
    color: var(--color-teal-900);
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}
.pd-inclusion-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.2rem;
}
.pd-inclusion-box {
    padding: 1.25rem;
    border-radius: 8px;
    background: rgba(250, 250, 249, 0.88);
    border: 1px solid rgba(68, 64, 60, 0.1);
}
.pd-inclusion-box h3 {
    font-size: 1.5rem;
    color: var(--color-teal-900);
    margin-bottom: 0.75rem;
    font-family: "Cormorant Garamond", Georgia, serif !important;
    font-style: italic !important;
    font-weight: 700;
}
.pd-inclusion-box.excluded h3 { color: #991b1b; }
.pd-check-list li {
    display: flex;
    gap: 0.55rem;
    align-items: flex-start;
    padding: 0.34rem 0 !important;
}
.pd-check-list li i {
    margin-top: 0.3rem;
    color: var(--color-amber-500);
    font-size: 0.74rem;
}
.pd-check-list li span {
    font-family: "Cormorant Garamond", Georgia, serif !important;
    font-size: 1.25rem;
    font-style: italic !important;
    font-weight: 500;
    line-height: 1.45;
}
.pd-requirement-note {
    padding: 1.15rem;
    border-radius: 8px;
    background: rgba(251, 191, 36, 0.14);
    border: 1px solid rgba(251, 191, 36, 0.36);
    margin-bottom: 1rem;
}
.pd-requirement-note strong {
    display: block;
    color: #78350f;
    margin-bottom: 0.35rem;
}
.pd-permit-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    margin-top: 0.9rem !important;
}
.pd-permit-list li {
    padding: 0.45rem 0.7rem !important;
    border-radius: 999px;
    background: rgba(4, 47, 46, 0.08);
    color: var(--color-teal-900);
    font-size: 0.82rem;
    font-weight: 700;
}

/* ——— Right column: booking card (third image) ——— */
.pd-card {
    background: var(--gallery-paper, #fffdf9);
    border-radius: 8px;
    box-shadow: 0 28px 80px rgba(28, 25, 23, 0.15);
    overflow: hidden;
    position: sticky;
    top: 100px;
    border: 1px solid rgba(251, 191, 36, 0.24);
}
.pd-card-price {
    background:
        radial-gradient(circle at top left, rgba(251, 191, 36, 0.2), transparent 42%),
        var(--color-teal-900);
    color: white;
    padding: 2rem 1.5rem 1.85rem;
    text-align: center;
}
.pd-card-price .label {
    font-size: 0.875rem;
    opacity: 0.95;
    text-transform: none;
    letter-spacing: 0;
    display: block;
}
.pd-card-price .price-display.large-price {
    display: inline-flex;
    justify-content: center;
    align-items: baseline;
    color: #fffdf7;
    margin: 0.35rem auto 0.2rem;
    font-size: clamp(2rem, 3vw, 2.55rem);
    text-shadow: 0 10px 24px rgba(0, 0, 0, 0.24);
}
.pd-card-price .price-display.large-price .currency {
    color: var(--color-amber-400);
    font-weight: 900;
}
.pd-card-price .price-display.large-price .amount {
    color: #fffdf7;
}
.pd-card-price .price-display.large-price::after {
    color: rgba(255, 253, 247, 0.86);
}
.pd-card-price .per {
    display: block;
    font-size: 0.875rem;
    opacity: 0.95;
}
.pd-card-body { padding: 1.6rem; }
.pd-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 0;
    border-bottom: 1px solid rgba(68, 64, 60, 0.1);
    font-size: 0.95rem;
}
.pd-card-row:last-of-type { border-bottom: none; }
.pd-card-row .k { color: #57534e; font-weight: 400; }
.pd-card-row .v { font-weight: 800; color: #292524; text-align: right; }
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
    border-radius: 8px;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.2s;
    width: 100%;
    font-family: var(--font-sans), sans-serif;
}
.pd-btn-primary {
    background: var(--color-teal-900);
    color: white;
    box-shadow: 0 16px 34px rgba(4, 47, 46, 0.22);
}
.pd-btn-primary:hover {
    background: var(--color-teal-900);
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
    width: min(var(--site-width), calc(100% - 2rem));
    max-width: none;
    margin: 0 auto;
    padding: 0;
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
    color: var(--color-teal-900);
}
@media (max-width: 900px) {
    .pd-grid { grid-template-columns: 1fr; gap: 2rem; }
    .pd-card { position: static; }
    .pd-sticky-book-inner { flex-direction: column; align-items: stretch; }
    .pd-hero { min-height: 74vh; }
    .pd-hero-inner { margin-left: auto; margin-right: auto; }
    .pd-overview-strip,
    .pd-feature-list,
    .pd-inclusion-grid {
        grid-template-columns: 1fr;
    }
    .pd-timeline-list li {
        grid-template-columns: 1fr;
        gap: 0.45rem;
    }
}

@media (max-width: 640px) {
    .pd-hero {
        min-height: 88svh;
        padding: 7rem 0 4.2rem;
        background-position: center;
    }

    .pd-hero::before {
        background:
            radial-gradient(circle at 22% 18%, rgba(251, 191, 36, 0.18), transparent 34%),
            linear-gradient(90deg, rgba(0, 0, 0, 0.58) 0%, rgba(0, 0, 0, 0.24) 58%, rgba(12, 10, 9, 0.28) 100%),
            linear-gradient(to top, rgba(12, 10, 9, 0.86) 0%, rgba(12, 10, 9, 0.16) 62%, rgba(12, 10, 9, 0.22) 100%);
    }

    .pd-back {
        margin-bottom: 1.1rem;
        font-size: 0.68rem;
        padding: 0.5rem 0.72rem;
    }

    .pd-category-badge {
        font-size: 0.64rem;
        letter-spacing: 0.11em;
        padding: 0.5rem 0.76rem;
    }

    .pd-title {
        font-size: clamp(3rem, 16vw, 4.35rem);
        line-height: 0.9;
        margin-bottom: 1rem;
    }

    .pd-hero-lede {
        font-size: 0.95rem;
        line-height: 1.65;
        margin-bottom: 1.25rem;
    }

    .pd-meta {
        gap: 0.45rem;
    }

    .pd-meta span {
        width: 100%;
        justify-content: flex-start;
        border-radius: 8px;
        padding: 0.58rem 0.72rem;
    }

    .pd-content {
        padding: 3rem 0 5.5rem;
    }

    .pd-grid {
        width: calc(100% - 1.25rem);
        gap: 1.25rem;
    }

    .pd-editorial-panel,
    .pd-section-card {
        padding: 1.25rem;
    }

    .pd-editorial-panel::after {
        width: 3.3rem;
        height: 3.3rem;
    }

    .pd-main h2 {
        font-size: clamp(2rem, 11vw, 3rem);
    }

    .pd-overview-item {
        padding: 0.85rem;
    }

    .pd-feature-list li {
        grid-template-columns: 1.55rem 1fr;
        gap: 0.65rem;
        padding: 0.82rem !important;
    }

    .pd-feature-list em {
        width: 1.55rem;
        height: 1.55rem;
        font-size: 0.64rem;
    }

    .pd-card-price {
        padding: 1.5rem 1rem;
    }

    .pd-card-price .price-display.large-price {
        font-size: clamp(1.85rem, 10vw, 2.35rem);
        gap: 0.2rem;
    }

    .pd-card-body {
        padding: 1.15rem;
    }

    .pd-card-row {
        font-size: 0.88rem;
        padding: 0.78rem 0;
    }

    .pd-sticky-book {
        display: none;
    }

    .pd-modal-overlay {
        align-items: flex-end;
        padding: 0.75rem;
    }

    .pd-modal {
        max-height: calc(100svh - 1.5rem);
        border-radius: 10px;
    }

    .pd-modal-body {
        padding: 1.35rem;
    }
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
.pd-modal-total .value { font-weight: 700; color: var(--color-teal-900); font-size: 1.2rem; }
.pd-modal-submit {
    width: 100%;
    padding: 0.9rem 1.25rem;
    background: var(--color-teal-900);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.pd-modal-submit:hover { background: var(--color-teal-900); }
</style>

<!-- Hero -->
<section class="pd-hero" style="background-image: url('<?php echo e($img); ?>');">
    <div class="container pd-hero-inner">
        <a href="<?php echo url('public/collection.php'); ?>" class="pd-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Collection
        </a>
        <?php if ($category): ?>
            <div class="pd-category-badge"><?php echo e($category_label); ?></div>
        <?php endif; ?>
        <h1 class="pd-title"><?php echo e($tour['title']); ?></h1>
        <p class="pd-hero-lede"><?php echo e($hero_lede); ?></p>
        <div class="pd-meta">
            <span><i class="fa-solid fa-location-dot"></i> <?php echo e($tour['location']); ?></span>
            <span><i class="fa-regular fa-clock"></i> <?php echo e($tour['duration']); ?></span>
            <?php if ($difficulty): ?>
                <span><i class="fa-solid fa-mountain-sun"></i> <?php echo e($difficulty); ?></span>
            <?php endif; ?>
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
            <section class="pd-editorial-panel">
                <p class="pd-kicker">Curated journey</p>
                <h2><?php echo e($tour['title']); ?></h2>
                <p><?php echo nl2br(e($tour['description'])); ?></p>

                <div class="pd-overview-strip">
                    <div class="pd-overview-item">
                        <span>Region</span>
                        <strong><?php echo e($tour['location']); ?></strong>
                    </div>
                    <div class="pd-overview-item">
                        <span>Style</span>
                        <strong><?php echo e($category_label); ?></strong>
                    </div>
                    <div class="pd-overview-item">
                        <span>Season</span>
                        <strong><?php echo $best_season ? e($best_season) : 'Year-round'; ?></strong>
                    </div>
                </div>
            </section>

            <?php if (!empty($highlights_list)): ?>
            <section class="pd-section-card">
                <p class="pd-kicker">Signature moments</p>
                <h2>Experience Highlights</h2>
                <ul class="pd-feature-list">
                    <?php foreach ($highlights_list as $index => $h): ?>
                        <li><em><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></em><span><?php echo e($h); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

            <?php if (!empty($itinerary_days)): ?>
            <section class="pd-section-card">
                <p class="pd-kicker">Route composition</p>
                <h2>Itinerary</h2>
                <ol class="pd-timeline-list">
                    <?php foreach ($itinerary_days as $index => $day): ?>
                        <?php $dayText = preg_replace('/^Day\s*\d+\s*:\s*/i', '', $day); ?>
                        <li>
                            <span class="pd-day-pill">Day <?php echo $index + 1; ?></span>
                            <p><?php echo e($dayText); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
            <?php endif; ?>

            <?php if (!empty($inclusions_list) || !empty($exclusions_list)): ?>
            <section class="pd-section-card">
                <p class="pd-kicker">Package details</p>
                <h2>Included & Excluded</h2>
                <div class="pd-inclusion-grid">
                    <?php if (!empty($inclusions_list)): ?>
                    <div class="pd-inclusion-box">
                        <h3>Included</h3>
                        <ul class="pd-check-list">
                            <?php foreach ($inclusions_list as $inc): ?>
                                <li><i class="fa-solid fa-check"></i><span><?php echo e($inc); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($exclusions_list)): ?>
                    <div class="pd-inclusion-box excluded">
                        <h3>Not Included</h3>
                        <ul class="pd-check-list">
                            <?php foreach ($exclusions_list as $exc): ?>
                                <li><i class="fa-solid fa-minus"></i><span><?php echo e($exc); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($altitude_max || !empty($permits_list)): ?>
            <section class="pd-section-card">
                <p class="pd-kicker">Safety & access</p>
                <h2>Requirements</h2>
                <?php if ($altitude_max && $altitude_max > 3500): ?>
                <div class="pd-requirement-note">
                    <strong>High Altitude Advisory</strong>
                    <p>This journey reaches <?php echo number_format($altitude_max); ?>m. Proper acclimatization is essential, and guests should consult a doctor before booking.</p>
                </div>
                <?php endif; ?>
                <?php if (!empty($permits_list)): ?>
                    <p>All required permits are arranged by the Paila team and included in the package coordination.</p>
                    <ul class="pd-permit-list">
                        <?php foreach ($permits_list as $permit): ?>
                            <li><?php echo e($permit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </div>

        <div class="pd-card">
            <div class="pd-card-price">
                <span class="label">Starting from</span>
                <span class="price-display large-price"><span class="currency">रू</span><span class="amount"><?php echo number_format((float)$tour['price'], 2); ?></span></span>
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
                <?php echo csrf_field(); ?>
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
                    <input type="number" name="travelers" value="1" min="1" max="<?php echo $traveler_max; ?>" data-rules="required">
                    <small style="display: block; margin-top: 0.35rem; color: var(--color-stone-500);">Up to <?php echo $traveler_max; ?> guests for this departure.</small>
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" placeholder="Any special requirements or questions?"></textarea>
                </div>
                <div class="pd-modal-total">
                    <span class="label">Total Estimate</span>
                    <span class="value price-display card-price" id="pd-total-estimate"><span class="currency">रू</span><span class="amount"><?php echo number_format((float)$tour['price'], 2); ?></span></span>
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
            <span class="price price-display card-price"><span class="currency">रू</span><span class="amount"><?php echo number_format((float)$tour['price'], 2); ?></span></span> <span style="font-size: 0.9rem; color: #78716c;">per person</span>
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

    var travelerInput = document.querySelector('#pd-booking-form input[name="travelers"]');
    var totalEstimate = document.getElementById('pd-total-estimate');
    var pricePerPerson = <?php echo json_encode((float) $tour['price']); ?>;

    function formatNpr(value) {
        return '<span class="currency">रू</span><span class="amount">' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>';
    }

    function updateEstimate() {
        if (!travelerInput || !totalEstimate) return;

        var max = parseInt(travelerInput.getAttribute('max'), 10) || 20;
        var travelers = parseInt(travelerInput.value, 10) || 1;
        travelers = Math.max(1, Math.min(max, travelers));

        if (String(travelers) !== travelerInput.value) {
            travelerInput.value = travelers;
        }

        totalEstimate.innerHTML = formatNpr(pricePerPerson * travelers);
    }

    if (travelerInput) {
        travelerInput.addEventListener('input', updateEstimate);
        travelerInput.addEventListener('change', updateEstimate);
        updateEstimate();
    }

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
