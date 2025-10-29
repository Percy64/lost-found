# Lost & Found Pet Management System - AI Instructions

## Project Overview
This is a PHP-based web application for managing lost and found pets, built for XAMPP/WAMP local development. The system uses a MySQL database with PDO connections and follows a simple MVC-like pattern with direct SQL queries.

## Database Architecture
- **Primary Database**: `mascotas_db` with MySQL/MariaDB
- **Connection Pattern**: All PHP files use `require 'conexion.php'` for PDO connection
- **Key Tables**:
  - `usuarios` - User accounts with hashed passwords
  - `mascotas` - Pet records linked to users via foreign key `id`
  - `codigos_qr` - QR codes for pet identification (unique codes)
  - `historial_medico` - Medical history linked to pets
  - `fotos_mascotas` - Pet photos (currently unused, photos stored as BLOB in mascotas table)

## File Structure & Patterns
```
├── conexion.php          # PDO database connection (localhost, root, no password)
├── home.php             # Main feed with hardcoded pet data array
├── iniciosesion.php     # Login form (UI only, no backend auth)
├── registro_usuario.php # User registration with validation
├── registro_mascota.php # Pet registration with image upload
├── perfil_mascota.php   # Pet profile with GET parameter ?id=
├── nousuario.php        # Static pet profile page
└── assets/              # Static assets organized by type
    ├── css/
    │   ├── mascota03.css           # Shared stylesheet for forms and profiles
    │   ├── home.css                # Styles for main feed page
    │   ├── iniciosesion.css        # Styles for login page
    │   ├── nousuario.css           # Styles for pet profile display
    │   ├── registro-mascota-addon.css # Additional styles for pet registration
    │   └── registro-usuario.css    # Additional styles for user registration
    └── images/
        ├── logo.png      # Application logo
        └── image 27.png  # Login page logo
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
- **Upload Method**: `$_FILES` with `file_get_contents()` to BLOB
- **Storage**: Direct BLOB storage in database (not file system)
- **Preview**: JavaScript for client-side image preview

## Key Implementation Details

### Authentication
- **Current State**: Login form exists but no session management implemented
- **Password Hashing**: Uses `password_hash()` in registration
- **Missing**: Session handling, login verification, logout functionality

### QR Code System
- **Purpose**: Each pet gets a unique QR code for identification
- **Format**: URLs like `https://miproyecto.com/mascota/{id}`
- **Integration**: Links to `perfil_mascota.php?id={pet_id}`

### UI/UX Patterns
- **Color Scheme**: Consistent `#FAF3B5` (light yellow) background
- **Mobile-First**: Max-width 430px containers
- **Navigation**: Bottom navigation bar with SVG icons (placeholder alerts)
- **Form Style**: Centered forms with rounded corners and purple accent `#c9a7f5`

## Critical Workflows

### Adding New Pets
1. User fills `registro_mascota.php` form
2. Image processed as BLOB via `file_get_contents()`
3. Data validated and inserted to `mascotas` table
4. QR code generated and linked

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
- Session management and authentication flow
- Actual search functionality (currently hardcoded data in `home.php`)
- File-based image storage (currently BLOB only)
- Navigation functionality (currently `alert()` placeholders)
- Email integration for lost pet notifications

## Database Relationships
```
usuarios (1) ←→ (N) mascotas ←→ (1) codigos_qr
    ↓
mascotas (1) ←→ (N) historial_medico
    ↓
mascotas (1) ←→ (N) fotos_mascotas (unused)
```

Always use prepared statements and maintain the existing validation patterns when adding new features.