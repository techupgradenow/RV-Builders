# RV BUILDERS - Backend API

PHP REST API for managing projects and images for the RV BUILDERS website.

## Folder Structure

```
Backend/
├── api/
│   ├── index.php          # Main API entry point & router
│   └── .htaccess           # API URL rewriting
├── config/
│   ├── config.php          # Application configuration
│   └── Database.php        # Database connection class
├── controllers/
│   ├── ProjectController.php
│   └── CategoryController.php
├── database/
│   └── schema.sql          # MySQL database schema
├── models/
│   ├── Project.php
│   └── Category.php
├── services/
│   ├── ProjectService.php
│   └── CategoryService.php
├── uploads/
│   └── projects/           # Project images storage
├── utils/
│   ├── ImageUploader.php   # Image upload handler
│   └── Response.php        # JSON response utility
├── logs/                   # Error logs
├── .htaccess               # Apache configuration
└── README.md
```

## Setup Instructions

### 1. Database Setup

1. Create MySQL database:
```sql
CREATE DATABASE rv_builders;
```

2. Import the schema:
```bash
mysql -u root -p rv_builders < database/schema.sql
```

Or run the SQL file directly in phpMyAdmin.

### 2. Configure Database Connection

Edit `config/Database.php` and update:
- `$host` - Database host (default: localhost)
- `$db_name` - Database name (default: rv_builders)
- `$username` - MySQL username (default: root)
- `$password` - MySQL password

### 3. Configure Base URL

Edit `config/config.php` and update:
- `BASE_URL` - Your server's base URL for the backend

### 4. File Permissions

Ensure the `uploads/` directory is writable:
```bash
chmod -R 755 uploads/
```

## API Endpoints

### Projects

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/projects` | Get all projects |
| GET | `/api/projects?category=residential` | Filter by category |
| GET | `/api/projects?limit=10&offset=0` | Pagination |
| GET | `/api/projects/featured` | Get featured projects |
| GET | `/api/projects/{id}` | Get single project |
| POST | `/api/projects` | Create new project |
| PUT/POST | `/api/projects/{id}` | Update project |
| DELETE | `/api/projects/{id}` | Delete project |

### Project Images

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/projects/{id}/images` | Add images (max 5 total) |
| DELETE | `/api/projects/images/{imageId}` | Delete image |
| PUT | `/api/projects/{id}/images/{imageId}/primary` | Set primary image |

### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | Get all categories |
| GET | `/api/categories/{slug}` | Get category by slug |
| POST | `/api/categories` | Create new category |

### Health Check

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | API health check |

## Request Examples

### Create Project with Images

```bash
curl -X POST http://localhost/RV-Builders/Backend/api/projects \
  -F "title=New Villa Project" \
  -F "description=Beautiful modern villa" \
  -F "category=residential" \
  -F "client_name=John Doe" \
  -F "location=Chennai, Tamil Nadu" \
  -F "project_date=2024-12-01" \
  -F "completion_status=completed" \
  -F "featured=1" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

### Get All Projects (JSON)

```bash
curl -X GET http://localhost/RV-Builders/Backend/api/projects
```

### Add Images to Existing Project

```bash
curl -X POST http://localhost/RV-Builders/Backend/api/projects/1/images \
  -F "images[]=@/path/to/image.jpg"
```

## Response Format

### Success Response

```json
{
    "success": true,
    "message": "Success message",
    "data": { ... }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error message",
    "error_code": 400
}
```

## Image Upload Constraints

- Maximum 5 images per project
- Maximum file size: 5MB per image
- Allowed formats: JPEG, PNG, GIF, WebP
- First image automatically set as primary

## Categories

Default categories:
- `all` - All Projects
- `residential` - Residential Construction
- `commercial` - Commercial Construction
- `renovation` - Renovation Projects
- `interior` - Interior Design

## Security Features

- Input sanitization
- Prepared statements (SQL injection prevention)
- File type validation
- File size limits
- CORS headers
- Error logging

## Error Handling

All errors are logged to `logs/error.log`. In production, set `display_errors` to 0 in `config/config.php`.
