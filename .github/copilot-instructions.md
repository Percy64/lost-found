# Lost & Found Pet Management System - AI Instructions

## Project Overview
This is a PHP-based web application for managing lost and found pets, built for XAMPP/WAMP local development. The system uses a MySQL database with PDO connections and follows a simple MVC-like pattern with direct SQL queries. **Full authentication system is now implemented** with session management.

## Database Architecture
- **Primary Database**: `mascotas_db` with MySQL/MariaDB
- **Connection Pattern**: All PHP files use `require 'conexion.php'` for PDO connection
- **Key Tables**:
  - `usuarios` - User accounts with hashed passwords and profile photos
  - `mascotas` - Pet records linked to users via foreign key `id` (references usuarios.id)
  - `codigos_qr` - QR codes for pet identification (unique codes)
  - `historial_medico` - Medical history linked to pets
  - `fotos_mascotas` - Legacy table (photos now stored as files with `foto_url` field)

## File Structure & Patterns
```
├── conexion.php          # PDO database connection (localhost, root, no password)
├── home.php             # Main feed with session checks and hardcoded pet data
├── iniciosesion.php     # Login form with full authentication backend
├── logout.php           # Session destruction and redirect
├── busqueda.php         # Search interface with database integration and Google Maps
├── perfil_usuario.php   # User profile with pet listings (requires auth)
├── editar_perfil.php    # Profile editing functionality
├── registro_usuario.php # User registration with file-based photo upload
├── registro_mascota.php # Pet registration with file-based image storage
├── perfil_mascota.php   # Pet profile with GET parameter ?id=
├── debug_mascotas.php   # Debug utility for troubleshooting database/session issues
├── nousuario.php        # Static pet profile page
├── home2_02/            # Legacy search UI directory (integrated into main app)
│   ├── busqueda.php     # Old search interface (replaced by main busqueda.php)
│   └── styles.css       # Legacy search styles (moved to assets/css/busqueda.css)
└── assets/              # Static assets organized by type
    ├── css/             # Stylesheets for different pages
    │   ├── busqueda.css # Search page styles with carousel and map integration
    │   └── ...          # Other stylesheets
    └── images/
        ├── mascotas/    # Pet photos stored as files (mascota_{userid}_{timestamp}.ext)
        ├── usuarios/    # User profile photos (usuario_{userid}_{timestamp}.ext)
        └── *.svg        # Placeholder icons for different animal types
```

## Development Environment
- **Server**: XAMPP (Apache + MySQL) on Windows
- **Database**: Access via `http://localhost/phpmyadmin`
- **Local URL**: `http://localhost/lost-found/`
- **No build tools**: Direct PHP execution, no compilation needed

## Code Patterns & Conventions

### Database Queries
```php
// Standard pattern used throughout
require 'conexion.php'; // or require_once
$sql = "SELECT * FROM mascotas WHERE id_mascota = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_mascota]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
```

### Form Validation Pattern
All forms follow this structure in `registro_usuario.php` and `registro_mascota.php`:
```php
$error = false;
$msg_field = '';

if(isset($_POST['btn_name'])){
    if(isset($_POST['field'])){
        $field = trim($_POST['field']);
        if(empty($field)){
            $msg_field = 'Error message';
            $error = true;
        }
    }
}
```

### Image Handling
- **Upload Method**: `$_FILES` with `move_uploaded_file()` to file system
- **Storage**: File-based storage in `assets/images/{mascotas|usuarios}/`
- **Naming Pattern**: `{type}_{user_id}_{timestamp}.{extension}`
- **Fallback**: Database still supports BLOB storage via `foto_url` field
- **Preview**: JavaScript for client-side image preview before upload

## Key Implementation Details

### Authentication
- **Current State**: Full authentication system implemented with session management
- **Login**: `iniciosesion.php` handles credential verification and session creation
- **Sessions**: `$_SESSION['usuario_id']`, `$_SESSION['usuario_nombre']`, `$_SESSION['usuario_email']`
- **Protection**: Pages like `perfil_usuario.php` check session and redirect to login if not authenticated
- **Logout**: `logout.php` destroys session and redirects to login
- **Password Hashing**: Uses `password_hash()` and `password_verify()` for secure authentication

### QR Code System
- **Purpose**: Each pet gets a unique QR code for identification
- **Format**: URLs like `https://miproyecto.com/mascota/{id}`
- **Integration**: Links to `perfil_mascota.php?id={pet_id}`

### UI/UX Patterns
- **Color Scheme**: Consistent #FAF3B5 (light yellow) background
- **Mobile-First**: Max-width 430px containers
- **Navigation**: Bottom navigation bar with SVG icons (placeholder alerts)
- **Form Style**: Centered forms with rounded corners and purple accent #c9a7f5

## Critical Workflows

### Adding New Pets
1. User fills `registro_mascota.php` form (requires authentication)
2. Image processed via `move_uploaded_file()` to file system with unique naming
3. Data validated and inserted to `mascotas` table with `foto_url` field pointing to file path
4. QR code generated and linked

### Search and Navigation Integration
1. `busqueda.php` provides full-text search across pet database
2. Search by name, species, breed, or color with real-time results
3. Google Maps integration for location context
4. Carousel navigation with smooth scrolling for pet cards
5. Unified navigation bar across all main pages (home, search, profile)

### User Profile Management
1. `perfil_usuario.php` displays user info and their registered pets
2. Session-protected with automatic redirect to login if not authenticated
3. Queries pets using `id` foreign key to link with `usuarios` table
4. `editar_perfil.php` allows updating user information and profile photo

### Debugging Database Issues
- Use `debug_mascotas.php` to troubleshoot session, database connection, and data retrieval issues
- Shows session state, user verification, table structure, and query results
- Helpful for diagnosing foreign key relationships and data inconsistencies

### Database Setup
```sql
-- Import the complete schema
mysql -u root mascotas_db < mascotas.sql
```

### Development Testing
- **Local Testing**: Use `http://localhost/lost-found/home.php`
- **Database**: Pre-populated with sample users and pets
- **Image Testing**: Upload forms expect image files, store as BLOB

## Missing Implementations
When extending this system, note these are placeholder/incomplete:
- Navigation functionality (currently `alert()` placeholders for Info and Settings)
- Email integration for lost pet notifications
- QR code generation and scanning functionality
- Camera integration for search by photo feature

## Database Relationships
```
usuarios (1) ←→ (N) mascotas ←→ (1) codigos_qr
    ↓
mascotas (1) ←→ (N) historial_medico
    ↓
mascotas (1) ←→ (N) fotos_mascotas (unused)
```

Always use prepared statements and maintain the existing validation patterns when adding new features.