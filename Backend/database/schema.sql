-- RV BUILDERS Database Schema
-- MySQL Database Setup Script

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS rv_builders
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE rv_builders;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL DEFAULT 'residential',
    client_name VARCHAR(255),
    location VARCHAR(255),
    project_date DATE,
    completion_status ENUM('completed', 'in_progress', 'upcoming') DEFAULT 'completed',
    featured TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_featured (featured),
    INDEX idx_status (completion_status),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project images table
CREATE TABLE IF NOT EXISTS project_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project categories table (for dynamic categories)
CREATE TABLE IF NOT EXISTS project_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO project_categories (name, slug, description, display_order) VALUES
('All', 'all', 'All Projects', 0),
('Residential', 'residential', 'Residential Construction Projects', 1),
('Commercial', 'commercial', 'Commercial Construction Projects', 2),
('Renovation', 'renovation', 'Renovation and Remodeling Projects', 3),
('Interior', 'interior', 'Interior Design Projects', 4)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insert sample projects (optional - for testing)
INSERT INTO projects (title, description, category, client_name, location, project_date, completion_status, featured, display_order) VALUES
('Modern Villa Construction', 'A luxurious 4-bedroom villa with contemporary design featuring spacious living areas, modern kitchen, and landscaped garden.', 'residential', 'Mr. Rajesh Kumar', 'Chennai, Tamil Nadu', '2024-06-15', 'completed', 1, 1),
('Commercial Complex', 'Multi-story commercial building with retail spaces on ground floor and office spaces above. Features modern amenities and parking facility.', 'commercial', 'ABC Enterprises', 'Coimbatore, Tamil Nadu', '2024-08-20', 'completed', 1, 2),
('Home Renovation Project', 'Complete renovation of a 20-year-old residence including structural repairs, modern interiors, and energy-efficient upgrades.', 'renovation', 'Mrs. Lakshmi Devi', 'Madurai, Tamil Nadu', '2024-09-10', 'completed', 0, 3),
('Apartment Interior Design', 'Premium interior design for a 3BHK apartment featuring contemporary furniture, custom lighting, and smart home integration.', 'interior', 'Mr. Suresh Babu', 'Trichy, Tamil Nadu', '2024-10-05', 'completed', 0, 4),
('Industrial Warehouse', 'Large-scale industrial warehouse construction with loading docks, high ceilings, and advanced ventilation systems.', 'commercial', 'XYZ Industries', 'Salem, Tamil Nadu', '2024-11-01', 'in_progress', 1, 5);
