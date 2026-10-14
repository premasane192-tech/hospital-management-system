-- ============================================================
--  Care Plus — Book My Care database (patient appointments)
--  Import in phpMyAdmin: select the hospital_management database
--  first, then Import. (Needs the patients and doctors tables
--  from schema.sql.)  Or:
--    mysql -u root -p hospital_management < book_my_care.sql
--  Fresh install? schema.sql already contains this table.
-- ============================================================
USE hospital_management;

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    appointment_code VARCHAR(24) NOT NULL UNIQUE,      -- e.g. BMC-20261012-0003
    patient_id       INT NOT NULL,
    doctor_id        INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,                    -- the time the patient reports to the hospital
    reason           VARCHAR(255),
    status           ENUM('booked','cancelled') NOT NULL DEFAULT 'booked',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_slot (doctor_id, appointment_date, appointment_time),  -- no double booking
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(doctor_id)   ON DELETE CASCADE
) ENGINE=InnoDB;
