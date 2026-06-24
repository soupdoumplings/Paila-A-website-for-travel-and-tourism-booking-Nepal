# Paila Project Defense Guide

Date: June 24, 2026

## Project Summary

Paila is a Nepal travel and tourism booking website built around the idea of "nature as luxury." The project presents trekking, cultural, premium, and private Himalayan experiences with a refined visual system, booking flows, admin management, seeded tour data, secure sessions, CSRF protection, and animated page interactions.

The main goal of the design is to make Nepal feel premium without removing the natural identity of the destination. The UI uses mountain video, large editorial headings, jade green, amber gold, gallery-like cards, refined spacing, and controlled motion.

## Core Technologies

- PHP for server-side pages, routing, sessions, form handling, and database actions.
- MySQL for users, roles, tours, bookings, private access requests, messages, inquiries, and notifications.
- Docker Compose for local web and database services.
- HTML, CSS, and JavaScript for layout, responsive UI, animation, validation, sliders, video transitions, and AJAX.
- Font Awesome for icons.
- Google Fonts for the current typography pair: `Libre Baskerville` for headings and `Inter` for body/UI.

## Important Files

- `public/home.php`: Homepage, hero, featured tours, category slider, destination sections, private journey, and contact form.
- `assets/css/style.css`: Global theme tokens, layout, shared typography, header, buttons, cards, animations, and responsive rules.
- `assets/css/home.css`: Homepage-specific parallax hero, category presentation, private journey section, contact animation, and responsive homepage polish.
- `assets/css/tours.css`: Tour cards and category slider UI.
- `assets/js/main.js`: CSRF auto-injection, sidebar behavior, hero video playlist, fallback image logic, and reveal animation observer.
- `assets/js/scroll-effects.js`: Header scroll state and homepage parallax fade logic.
- `helpers/security.php`: Secure session configuration, security headers, CSRF tokens, POST enforcement, and cookie hardening.
- `database/schema.sql`: Database table structure.
- `database/insert_data.sql`: Local seed data, roles, super admin, package data, featured tours, and sample bookings.
- `admin/`: Admin dashboard, package management, bookings, requests, imports, exports, guides, and admin users.

## Main Features

- Homepage hero with rotating mountain videos and parallax storytelling.
- Featured tour cards powered by database records.
- Category slider for interest-based browsing.
- Collection page with filtering and AJAX search support.
- Tour/package detail pages with booking forms and live total estimates.
- Premium/private journeys with access-code gated experiences.
- Private access request workflow with admin approval.
- Admin dashboard for packages, bookings, inquiries, private requests, import/export, guides, and admin management.
- Notifications and message threads for bookings and access requests.
- Contact and inquiry forms connected to admin notifications.
- Local seed data for quick demo restoration.

## Security Features

Security is centralized in `helpers/security.php`.

- Custom session cookie name: `PAILA_SESSID`.
- `HttpOnly` cookies to reduce script access to session cookies.
- `SameSite=Lax` cookies to reduce cross-site request abuse.
- HTTPS-aware `Secure` cookie flag when the site is served over HTTPS.
- Strict session mode and cookie-only sessions.
- Session ID regeneration every 30 minutes.
- Idle session timeout after 2 hours.
- CSRF token generation with `csrf_token()`.
- Hidden CSRF fields with `csrf_field()`.
- Server-side CSRF enforcement with `require_csrf_token()`.
- Security headers including frame protection, content-type protection, referrer policy, permissions policy, and a content security policy.

## Animation System

The animation approach is layered and intentionally simple so it is easy to explain and maintain.

### 1. Header Scroll Animation

The header starts as transparent glass over the hero. JavaScript listens for scroll events and toggles a `.scrolled` class:

- File: `assets/js/main.js`
- File: `assets/js/scroll-effects.js`
- CSS target: `.navbar` and `.navbar.scrolled` in `assets/css/style.css`

When the page scrolls past the threshold, the header becomes a jade glass bar with blur, shadow, and tighter padding. CSS transitions animate the change.

### 2. Homepage Video Hero

The homepage uses two video elements:

- `#hero-video-1`
- `#hero-video-2`

JavaScript in `assets/js/main.js` loads clips from `data-clips` in `public/home.php`. It alternates between the two video elements, fades one out and the next one in, and retries or falls back gracefully if autoplay fails.

This gives the impression of a cinematic video playlist without needing a heavy video library.

### 3. Parallax Story Transition

The homepage hero and story section sit inside `#parallax-container`.

- File: `public/home.php`
- Style: `assets/css/home.css`
- Scroll logic: `assets/js/scroll-effects.js`

The background stays fixed while the content fades based on scroll progress:

- `#hero-content` fades out as the user scrolls.
- `#about-content` fades in after the hero begins leaving.
- `#parallax-bg` fades away after the parallax story range.

This creates the feeling that the mountain scene is continuous while the foreground content changes.

### 4. Scroll Cue Arrow

The hero arrow is a small scroll cue linked to `#collection`.

- Markup: `public/home.php`
- Styling: `.hero-scroll-cue` in `assets/css/home.css`

It uses a subtle vertical line and chevron. The chevron has a small keyframe animation named `heroArrowFloat`, which moves the icon slightly down and back up.

### 5. Reveal Animations

The reusable reveal system uses CSS classes and `IntersectionObserver`.

- CSS: `.luxury-reveal`, `.luxury-reveal.is-visible`, `.luxury-float`
- JS: `assets/js/main.js`

JavaScript watches selected page sections and cards. When they enter the viewport, it adds `.is-visible`, allowing CSS to transition opacity and vertical position. This keeps animations smooth and avoids running heavy JavaScript on every frame.

### 6. Hover Animations

Cards use CSS transitions for:

- Slight upward movement.
- Image zoom.
- Shadow increase.
- Border/accent changes.

This is used on tour cards, destination cards, category cards, premium cards, and contact cards. The logic is mostly CSS-only, which makes it fast and reliable.

### 7. Contact Section Motion

The contact section uses layered CSS backgrounds and keyframes.

- `contactLightSweep` moves a light sweep across the section.
- `contactCopyRise` brings the left text into view.
- `contactCardLift` brings the form card upward with opacity.

This creates motion without requiring JavaScript for that section.

## Category Slider Implementation

The "Find Your Adventure" slider is built with native horizontal scrolling and JavaScript controls.

### Markup

In `public/home.php`, the slider has:

- A wrapper: `.category-slider-wrapper`
- A scroll rail: `#category-slider`
- Cards: `.category-card`
- Previous and next buttons: `#cat-prev`, `#cat-next`
- Dots container: `#cat-dots`

### CSS

In `assets/css/tours.css`:

- The rail uses `display: flex`, `overflow-x: auto`, and `scroll-snap-type`.
- Cards use `scroll-snap-align` so they settle neatly.
- The wrapper hides overflow and adds side gradients.
- Arrows are positioned over the rail instead of taking layout space.
- Dots are horizontal bars, not tiny circles, so the control feels more premium.

### JavaScript

In `public/home.php`, the slider script:

- Calculates the maximum scroll width.
- Calculates a page width based on the visible slider width.
- Builds dots dynamically from the real scrollable distance.
- Scrolls by a responsive amount when arrows are clicked.
- Updates the active dot while the user scrolls.
- Disables arrows at the beginning or end.
- Rebuilds dots on resize so it works on desktop and mobile.

This makes the slider dependable without using an external carousel library.

## UI Design Direction

The visual system is based on these choices:

- Jade green from the header as the single green identity color.
- Amber gold as the luxury accent.
- White and warm stone backgrounds for gallery-like sections.
- Dark premium pages for old-money private travel.
- 8px card radius for a cleaner gallery feel.
- Large but controlled serif headings.
- Inter body text for readability.
- Wide content rail around 85 to 90 percent of the screen.
- Subtle shadows and borders instead of loud gradients.

## Database And Admin Notes

- Database name: `nepal_tours`
- Seed file: `database/insert_data.sql`
- Local admin URL: `http://localhost:8080/admin/login.php`
- Local super admin username: `ujShresthadmin`
- Local super admin email: `2461787@paila.admin`
- Local super admin password: `PailaAdmin@2026`

For demo restoration:

```powershell
Get-Content -Raw database\insert_data.sql | docker compose exec -T db mysql -unepal_user -pnepal_pass nepal_tours
```

Change the admin password before any real deployment.

## How To Defend The Project

Use these points when explaining the project:

- The website is not just static UI. It is connected to a MySQL database and has real package, booking, request, admin, and notification workflows.
- The animation system is mostly CSS transitions plus small JavaScript triggers, which keeps it lightweight.
- The hero video uses two video elements and opacity transitions to avoid a blank gap between clips.
- The slider uses native scrolling and scroll snapping, which is more stable than a custom heavy carousel.
- Security was added centrally, then reused across forms and actions with CSRF helpers and secure sessions.
- Admin workflows are separated from public pages and protected by role/session checks.
- The design concept is consistent: Nepal nature presented with luxury cues, not generic travel colors.
- The code uses reusable helpers for URLs, escaping, sessions, CSRF fields, notifications, and messages.

## Current Verification Checklist

- Homepage route returns `200`.
- Homepage PHP lints clean in Docker.
- Header, footer, premium, collection, and auth pages use the updated shared theme.
- Category slider has responsive arrows, dots, and scroll snapping.
- Local seed data includes tours and admin access for demo.
- Security headers and CSRF protection are implemented through `helpers/security.php`.

