
                        PAILA - NEPAL TOURS & TRAVEL WEBSITE

PROJECT OVERVIEW
----------------
A travel booking platform for Nepal tours and trekking
built using PHP, MySQL, HTML, CSS, and JavaScript with AJAX for real-time searching.


SUPER ADMIN ACCESS
------------------
Username: ujShresthadmin
Email:    2461787@paila.admin
Password: np03cs4a240006(Hashed in the database using bcrypt)

DATABASE ACCESS (Server Configuration)
--------------------------------------
Database Host: localhost
Database User: np03cs4a240006
Database Pass: SvoFQrw1PP
Database Name: np03cs4a240006

DATABASE ACCESS (Local configuration: Docker)
-------------------------------------------
Database Host: localhost
Database User: root
Database Pass: 
Database Name: nepal_tours

================================================================================
                          SETUP INSTRUCTIONS (DOCKER)
================================================================================

PREREQUISITES
-------------
- Docker Desktop
- Web browser

QUICK START STEPS
-----------------

1. START SERVICES
   - Open terminal in the project root
   - Run: `docker-compose up -d`

2. ACCESS THE APPLICATION
   - Website: http://localhost:8080/
   - Admin Panel: http://localhost:8080/admin/
   - Use the Super Admin credentials

3. STOP SERVICES
   - Run: `docker-compose down`

Points to be Noted:
---------------
- Ensure port 8080 is not in use by other applications
- Check the `database/` folder SQL files, to initialize the database.  

================================================================================
                         FEATURES IMPLEMENTED
================================================================================

Public User Features
- Dynamic homepage
- Featured tour packages
- Search and filtering (AJAX)
- Multi-step booking system
- Booking status tracking
- User dashboard
- Profile management
- Private journeys access

Admin Panel Features
- Admin dashboard
- Real-time statistics
- Tour management (CRUD)
- Booking management
- User management
- Guide management
- Role-based access control
- Requests and inquiries handling
- CSV import/export

Technical Features
- Bcrypt password hashing
- Prepared statements
- CSRF protection
- Responsive design
- Glassmorphism UI
- Micro-animations
- Optimized SQL queries
- AJAX-driven interactions
- Modular PHP code



================================================================================
                            KNOWN ISSUES
================================================================================

MINOR ISSUES
------------
1. Email Functionality will fall silently if SMTP server setup and PHP mail() configuration is not done.
2. Image Upload Path issues such as not working on some servers and folder permissions.
3. Browser Compatibility issues.
4. Mobile media queries could be handled better.
5. Notification issues.
6. Hard coded data in some places.

ISSUES & FUTURE ENHANCEMENTS
---------------------------
- Email & Image uploads required SMTP/Folder permissions for full functionality.
- Authentication(connect with google, apple, outlook).
- Optimize Performance.
- Media query could be handled better.
- Payment gateway.
- Multi-language support.  
- Proper functional Real-time chat.

================================================================================
                            PROJECT STRUCTURE
================================================================================

paila-traveling-2461787/
│
├── actions/              # Server-side
├── admin/               # Admin panel
├── assets/              # styling
├── config/              # Database and configuration
├── data/                # JSON files
├── database/            # SQL files(schema)
├── helpers/             # Reusable PHP helper functions
├── includes/            # Header, footer, navigation
├── public/              # Public pages(user side)
│   ├── authentication/  # Login, registration pages
│   └── uploads/         # Uploaded content
├── index.php            # Homepage
└── .htaccess           # Apache configuration
