-- ============================================================
--  Care Plus Hospital Management System — Database Schema
--  Import this file first: phpMyAdmin > Import, or:
--  mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_management;

-- ------------------------------------------------------------
-- admins : separate login table for hospital administrators
-- ------------------------------------------------------------
CREATE TABLE admins (
    admin_id      INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('super_admin','staff') NOT NULL DEFAULT 'staff',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- users : patient-facing login accounts
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(120) NOT NULL UNIQUE,
    phone         VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    status        ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- patients : clinical record, linked 1-to-1 (optional) with a user login
-- ------------------------------------------------------------
CREATE TABLE patients (
    patient_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NULL,                       -- linked login account, may be NULL for walk-ins
    patient_code   VARCHAR(20) NOT NULL UNIQUE,     -- e.g. PT-2026-0001
    full_name      VARCHAR(100) NOT NULL,
    date_of_birth  DATE,
    gender         ENUM('Male','Female','Other') NOT NULL,
    phone          VARCHAR(20),
    email          VARCHAR(120),
    address        VARCHAR(255),
    blood_group    VARCHAR(5),
    registered_by  INT NULL,                        -- admin_id who registered the patient
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (registered_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- doctors
-- ------------------------------------------------------------
CREATE TABLE doctors (
    doctor_id       INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100) NOT NULL,
    registration_no VARCHAR(50),
    qualification   VARCHAR(150),
    specialization  VARCHAR(150),
    clinic_name     VARCHAR(150),
    phone           VARCHAR(20),
    address         VARCHAR(255),
    description     TEXT,                 -- clinical description / bio
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- medical_references : configurable normal ranges per test
-- ------------------------------------------------------------
CREATE TABLE medical_references (
    reference_id   INT AUTO_INCREMENT PRIMARY KEY,
    test_key       VARCHAR(50) NOT NULL UNIQUE,   -- e.g. blood_sugar_fasting
    test_name      VARCHAR(100) NOT NULL,
    unit           VARCHAR(20) NOT NULL,
    low_value      DECIMAL(10,2) NOT NULL,
    high_value     DECIMAL(10,2) NOT NULL,
    interpretation TEXT,                          -- general educational note
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- health_reports : one row per generated report
-- ------------------------------------------------------------
CREATE TABLE health_reports (
    report_id        INT AUTO_INCREMENT PRIMARY KEY,
    report_code       VARCHAR(30) NOT NULL UNIQUE,  -- public-facing e.g. RPT-20260811-0007
    patient_id        INT NOT NULL,
    doctor_id         INT NULL,
    created_by_admin  INT NULL,
    report_date       DATE NOT NULL,
    doctor_notes       TEXT,
    recommendations    TEXT,
    follow_up          TEXT,
    status             ENUM('draft','final') NOT NULL DEFAULT 'final',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_admin) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- test_results : individual test line items belonging to a report
-- ------------------------------------------------------------
CREATE TABLE test_results (
    result_id     INT AUTO_INCREMENT PRIMARY KEY,
    report_id     INT NOT NULL,
    test_key      VARCHAR(50) NOT NULL,
    test_name     VARCHAR(100) NOT NULL,
    value         DECIMAL(10,2) NOT NULL,
    unit          VARCHAR(20),
    ref_low       DECIMAL(10,2),
    ref_high      DECIMAL(10,2),
    status        ENUM('Low','Normal','High') NOT NULL,
    FOREIGN KEY (report_id) REFERENCES health_reports(report_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- activity_log : lightweight audit trail (who did what)
-- ------------------------------------------------------------
CREATE TABLE activity_log (
    log_id      INT AUTO_INCREMENT PRIMARY KEY,
    actor_type  ENUM('admin','user') NOT NULL,
    actor_id    INT NOT NULL,
    action      VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- appointments : Book My Care bookings made by patients
-- One doctor can only have one booking per date+time (UNIQUE key),
-- so two patients can never be given the same slot.
-- ------------------------------------------------------------
CREATE TABLE appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    appointment_code VARCHAR(24) NOT NULL UNIQUE,      -- e.g. BMC-20261012-0003
    patient_id       INT NOT NULL,
    doctor_id        INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason           VARCHAR(255),
    status           ENUM('booked','cancelled') NOT NULL DEFAULT 'booked',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_slot (doctor_id, appointment_date, appointment_time),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(doctor_id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin login -> username: admin   password: Admin@123
-- (hash generated with PHP password_hash, bcrypt)
INSERT INTO admins (full_name, username, email, password_hash, role) VALUES
('Hospital Administrator', 'admin', 'admin@careplus.local',
 '$2b$10$jJThSPF2GqXeteKdKLDNyeBX3j31wxlJT.1A9VuJlcM5PvsurWjqq', 'super_admin');

-- Doctor supplied by the user
INSERT INTO doctors (full_name, registration_no, qualification, specialization, clinic_name, phone, address, description) VALUES
('Dr. Sumit Ujgaonkar', 'DIM8579', 'BHMS', 'General Practitioner',
 'Care Plus', '0712 264 1304',
 'Kalmeshwar, Nagpur, Maharashtra',
 'Dr. Sumit Ujgaonkar is a General Practitioner (BHMS) at Care Plus, Kalmeshwar, Nagpur, with a focus on family and general medicine, routine diagnostics, and preventive care.');

-- Configurable reference ranges for the ten required tests
INSERT INTO medical_references (test_key, test_name, unit, low_value, high_value, interpretation) VALUES
('blood_sugar', 'Blood Sugar (Fasting)', 'mg/dL', 70, 100, 'Fasting glucose outside this range may indicate hypoglycemia or a risk of diabetes and should be reviewed by a physician.'),
('blood_pressure_sys', 'Blood Pressure (Systolic)', 'mmHg', 90, 120, 'Systolic pressure reflects the force in arteries during a heartbeat; sustained high readings are linked to hypertension.'),
('cbc', 'Complete Blood Count (WBC index)', 'x10^3/uL', 4.0, 11.0, 'A general CBC index used alongside individual WBC/RBC counts to screen for infection or blood disorders.'),
('hemoglobin', 'Hemoglobin', 'g/dL', 13.0, 17.0, 'Hemoglobin carries oxygen in the blood; low levels may indicate anemia, high levels may indicate dehydration or other conditions.'),
('wbc', 'White Blood Cells (WBC)', 'x10^3/uL', 4.0, 11.0, 'WBCs are part of the immune system; elevated counts can suggest infection or inflammation.'),
('rbc', 'Red Blood Cells (RBC)', 'million/uL', 4.2, 5.9, 'RBC count relates to oxygen-carrying capacity; abnormal values can point to anemia or polycythemia.'),
('platelets', 'Platelets', 'x10^3/uL', 150, 450, 'Platelets are essential for clotting; abnormal counts can indicate bleeding or clotting disorders.'),
('pulse_rate', 'Pulse Rate', 'bpm', 60, 100, 'Resting pulse rate outside this range may reflect cardiovascular stress, fitness level, or arrhythmia.'),
('body_temperature', 'Body Temperature', '°F', 97.0, 99.0, 'Body temperature outside the normal range may indicate infection, inflammation, or other underlying conditions.'),
('cholesterol', 'Total Cholesterol', 'mg/dL', 125, 200, 'Elevated total cholesterol is a known risk factor for cardiovascular disease.');

-- ------------------------------------------------------------
-- Demo patient login -> username: patient1   password: User@123
-- ------------------------------------------------------------
INSERT INTO users (full_name, username, email, phone, password_hash, status) VALUES
('Anita Sharma', 'patient1', 'anita.sharma@example.com', '9876543210',
 '$2b$10$HmlVE0fS74P3OxrdYVoBF.0.h5DVZgCJFkMr3xGTqCcT5/w.G/6yq', 'active');

INSERT INTO patients (user_id, patient_code, full_name, date_of_birth, gender, phone, email, address, blood_group, registered_by) VALUES
(1, 'PT-2026-0001', 'Anita Sharma', '1994-03-12', 'Female', '9876543210', 'anita.sharma@example.com',
 'Ashok Nagar, Nagpur, Maharashtra', 'B+', 1);

-- Sample finalized report so admin/user dashboards have data on first login
INSERT INTO health_reports (report_code, patient_id, doctor_id, created_by_admin, report_date, doctor_notes, recommendations, follow_up, status) VALUES
('RPT-20260811-0001', 1, 1, 1, '2026-08-11',
 'Overall vitals are within expected limits with mildly elevated fasting blood sugar. No acute concerns noted on examination.',
 'Reduce refined sugar intake, maintain regular physical activity, and recheck fasting blood sugar in 3 months.',
 'Follow up in 3 months or sooner if symptoms such as excessive thirst or fatigue develop.',
 'final');

INSERT INTO test_results (report_id, test_key, test_name, value, unit, ref_low, ref_high, status) VALUES
(1, 'blood_sugar', 'Blood Sugar (Fasting)', 108, 'mg/dL', 70, 100, 'High'),
(1, 'blood_pressure_sys', 'Blood Pressure (Systolic)', 118, 'mmHg', 90, 120, 'Normal'),
(1, 'hemoglobin', 'Hemoglobin', 13.4, 'g/dL', 13.0, 17.0, 'Normal'),
(1, 'wbc', 'White Blood Cells (WBC)', 7.2, 'x10^3/uL', 4.0, 11.0, 'Normal'),
(1, 'rbc', 'Red Blood Cells (RBC)', 4.8, 'million/uL', 4.2, 5.9, 'Normal'),
(1, 'platelets', 'Platelets', 260, 'x10^3/uL', 150, 450, 'Normal'),
(1, 'pulse_rate', 'Pulse Rate', 76, 'bpm', 60, 100, 'Normal'),
(1, 'body_temperature', 'Body Temperature', 98.4, '°F', 97.0, 99.0, 'Normal'),
(1, 'cholesterol', 'Total Cholesterol', 214, 'mg/dL', 125, 200, 'High');
