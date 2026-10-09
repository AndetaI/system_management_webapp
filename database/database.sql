-- Barkea Resort
-- Database Schema

USE barkea_resort;

-- Users

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    role ENUM('admin', 'manager', 'staff') NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- TEAMS


CREATE TABLE teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;



-- VILLA GROUPS

CREATE TABLE villa_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL
) ENGINE=InnoDB;


-- SUPPLIERS

CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    contact_person VARCHAR(100) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- TEAM MEMBERS

CREATE TABLE team_members (
    team_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (team_id, user_id),

    CONSTRAINT fk_team_members_team
        FOREIGN KEY (team_id)
        REFERENCES teams(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_team_members_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- VILLA TYPOLOGIES

CREATE TABLE villa_typologies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    group_id INT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    floors INT UNSIGNED NULL,
    area DECIMAL(10,2) NULL,

    CONSTRAINT fk_villa_typologies_group
        FOREIGN KEY (group_id)
        REFERENCES villa_groups(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- VILLAS


CREATE TABLE villas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    typology_id INT UNSIGNED NOT NULL,

    name VARCHAR(50) NOT NULL UNIQUE,

    status ENUM(
        'planned',
        'in_progress',
        'completed'
    ) NOT NULL DEFAULT 'planned',

    description TEXT NULL,

    planned_date DATE NULL,
    actual_date DATE NULL,

    CONSTRAINT fk_villas_typology
        FOREIGN KEY (typology_id)
        REFERENCES villa_typologies(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- WORKS

CREATE TABLE works (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    villa_id INT UNSIGNED NOT NULL,

    title VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,

    status ENUM(
        'planned',
        'in_progress',
        'completed'
    ) NOT NULL DEFAULT 'planned',

    description TEXT NULL,

    planned_date DATE NULL,
    actual_date DATE NULL,

    CONSTRAINT fk_works_villa
        FOREIGN KEY (villa_id)
        REFERENCES villas(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- WORK ASSIGNMENTS

CREATE TABLE work_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    work_id INT UNSIGNED NOT NULL UNIQUE,

    user_id INT UNSIGNED NULL,
    team_id INT UNSIGNED NULL,

    CONSTRAINT fk_work_assignments_work
        FOREIGN KEY (work_id)
        REFERENCES works(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_work_assignments_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_work_assignments_team
        FOREIGN KEY (team_id)
        REFERENCES teams(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_work_assignment_target
        CHECK (
            (user_id IS NOT NULL AND team_id IS NULL)
            OR
            (user_id IS NULL AND team_id IS NOT NULL)
        )
) ENGINE=InnoDB;


-- MATERIALS

CREATE TABLE materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    supplier_id INT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    description TEXT NULL,

    CONSTRAINT fk_materials_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_materials_price
        CHECK (price >= 0)
) ENGINE=InnoDB;


-- MATERIAL USAGE

CREATE TABLE material_usage (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    work_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,

    quantity DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,

    CONSTRAINT fk_material_usage_work
        FOREIGN KEY (work_id)
        REFERENCES works(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_material_usage_material
        FOREIGN KEY (material_id)
        REFERENCES materials(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_material_usage_quantity
        CHECK (quantity > 0)
) ENGINE=InnoDB;



-- STOCK

CREATE TABLE stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    material_id INT UNSIGNED NOT NULL UNIQUE,

    quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_stock_material
        FOREIGN KEY (material_id)
        REFERENCES materials(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_stock_quantity
        CHECK (quantity >= 0)
) ENGINE=InnoDB;


-- STOCK MOVEMENTS

CREATE TABLE stock_movements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    material_id INT UNSIGNED NOT NULL,

    type ENUM('IN', 'OUT') NOT NULL,

    quantity DECIMAL(10,2) NOT NULL,

    date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    description VARCHAR(255) NULL,

    CONSTRAINT fk_stock_movements_material
        FOREIGN KEY (material_id)
        REFERENCES materials(id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_stock_movements_quantity
        CHECK (quantity > 0)
) ENGINE=InnoDB;

CREATE TABLE work_progress_updates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    work_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    old_status ENUM(
        'planned',
        'in_progress',
        'completed'
    ) NOT NULL,

    new_status ENUM(
        'planned',
        'in_progress',
        'completed'
    ) NOT NULL,

    note TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_progress_work
        FOREIGN KEY (work_id)
        REFERENCES works(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_progress_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    INDEX idx_progress_work_created (work_id, created_at)
) ENGINE=InnoDB;
