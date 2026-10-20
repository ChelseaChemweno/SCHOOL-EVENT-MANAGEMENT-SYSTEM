-- Users Table Admin and Student
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(150) NOT NULL,

    email VARCHAR(190) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    role ENUM('admin', 'student')
        NOT NULL DEFAULT 'student',

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;

-- Events table (Stores Events created by the admin)
CREATE TABLE events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(150) NOT NULL,

    description TEXT NULL,

    venue VARCHAR(200) NOT NULL,

    start_date DATETIME NOT NULL,

    end_date DATETIME NOT NULL,

    registration_deadline DATETIME NULL,

    capacity INT UNSIGNED NULL,

    status ENUM('draft', 'published', 'cancelled')
        NOT NULL DEFAULT 'draft',

    created_by INT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT chk_event_dates
        CHECK (end_date > start_date),

    CONSTRAINT chk_event_deadline
        CHECK (
            registration_deadline IS NULL
            OR registration_deadline <= start_date
        ),

    CONSTRAINT fk_events_admin
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_events_status_start (status, start_date),
    INDEX idx_events_created_by (created_by)
) ENGINE=InnoDB;


--Registrations Connects to Events 

CREATE TABLE registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    event_id INT UNSIGNED NOT NULL,

    registered_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    status ENUM('registered', 'cancelled')
        NOT NULL DEFAULT 'registered',

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_registrations_student
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_registrations_event
        FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_user_event
        UNIQUE (user_id, event_id),

    INDEX idx_registrations_event_status (event_id, status),
    INDEX idx_registrations_user_status (user_id, status)
) ENGINE=InnoDB;

--Attendance (Records the students who attended the event)
CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    registration_id INT UNSIGNED NOT NULL UNIQUE,

    attendance_status ENUM('present', 'absent', 'excused')
        NOT NULL DEFAULT 'present',

    marked_by INT UNSIGNED NULL,

    marked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    notes VARCHAR(500) NULL,

    CONSTRAINT fk_attendance_registration
        FOREIGN KEY (registration_id)
        REFERENCES registrations(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_attendance_admin
        FOREIGN KEY (marked_by)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_attendance_status (attendance_status)
) ENGINE=InnoDB;