-- ডাটাবেজ তৈরি
CREATE DATABASE IF NOT EXISTS patient_care_hospital;
USE patient_care_hospital;

-- ইউজার টেবিল (সব ধরণের ইউজারের জন্য)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(100),
    phone VARCHAR(15),
    role ENUM('admin', 'doctor', 'reception', 'patient') DEFAULT 'patient',
    status ENUM('active', 'inactive') DEFAULT 'active',
    photo VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- ডাক্তার টেবিল
CREATE TABLE doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100),
    specialization VARCHAR(100),
    qualification TEXT,
    experience INT,
    fee DECIMAL(8,2),
    bio TEXT,
    phone VARCHAR(15),
    email VARCHAR(100),
    photo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ডাক্তারের সময়সূচি
CREATE TABLE doctor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT,
    day_of_week ENUM('saturday','sunday','monday','tuesday','wednesday','thursday','friday'),
    start_time TIME,
    end_time TIME,
    max_patients INT DEFAULT 10,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- রোগী টেবিল
CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    patient_no VARCHAR(20) UNIQUE,
    name VARCHAR(100),
    phone VARCHAR(15),
    email VARCHAR(100),
    date_of_birth DATE,
    blood_group VARCHAR(5),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- অ্যাপয়েন্টমেন্ট টেবিল
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_no VARCHAR(20) UNIQUE,
    patient_id INT,
    doctor_id INT,
    appointment_date DATE,
    time_slot TIME,
    serial_no INT,
    status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
    problem_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- প্রেসক্রিপশন টেবিল
CREATE TABLE prescriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT UNIQUE,
    patient_id INT,
    doctor_id INT,
    symptoms TEXT,
    diagnosis TEXT,
    advice TEXT,
    followup_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- প্রেসক্রিপশনের ওষুধ
CREATE TABLE prescription_medicines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prescription_id INT,
    medicine_name VARCHAR(200),
    dosage VARCHAR(100),
    duration VARCHAR(100),
    instruction TEXT,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE
);

-- সেটিংস টেবিল
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'image') DEFAULT 'text'
);

-- অ্যাডমিন ইউজার (পাসওয়ার্ড: admin123)
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@patientcare.com', '$2y$10$YourHashedPasswordHere', 'সিস্টেম অ্যাডমিন', 'admin', 'active');

-- সেটিংস ডাটা
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('hospital_name', 'পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার', 'text'),
('hospital_address', '১২৩, বাংলামোটর, ঢাকা-১০০০', 'text'),
('hospital_phone', '০২-৯৬৬৬৬৬৬', 'text'),
('hospital_mobile', '০১৭১২৩৪৫৬৭৮', 'text'),
('hospital_email', 'info@patientcare.com', 'text'),
('currency', '৳', 'text');