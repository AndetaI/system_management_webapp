# Barkea Resort Web Application

Secure web application for managing villas, construction works, materials,
warehouse stock, suppliers, users and reports.

## Project Structure

The project follows a layered structure:

- `public/` - Public entry point and frontend assets
- `views/` - User interface pages
- `controllers/` - Handle application requests
- `services/` - Application and business logic
- `repositories/` - Database access
- `models/` - Application data models
- `config/` - Application and database configuration
- `security/` - Authentication, authorization and CSRF protection
- `util/` - Shared utility classes
- `storage/` - Application-generated files such as logs
- `tests/` - Files for testing the PDO connection

## Naming Conventions

### PHP Classes and Files

PHP class names and their corresponding files use PascalCase.

Examples:

- `VillaController.php`
- `WorkRepository.php`
- `AuthService.php`

### Methods and Variables

Methods and variables use camelCase.

Examples:

- `findById()`
- `createWork()`
- `$workId`
- `$plannedDate`

### Database Tables and Columns

Database tables and columns use snake_case.

Examples:

- `villa_groups`
- `villa_typologies`
- `stock_movements`
- `planned_date`
- `created_at`

### Folders

Folder names use lowercase.

Examples:

- `controllers/`
- `repositories/`
- `security/`
- `views/`

## Requirements

The application requires:

- PHP 8.x
- MySQL 8.x
- Apache
- Composer
- Git

## Local Setup

### 1. Project location

The project is located on the application server under:

```text
/var/www/html/barkea-resort

