-- ==========================================
-- AI Resume Analyzer & ATS Checker
-- Database Schema
-- ==========================================

CREATE DATABASE IF NOT EXISTS resume_analyzer;
USE resume_analyzer;

-- ==========================
-- USERS TABLE
-- ==========================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- ADMIN TABLE
-- ==========================
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- ==========================
-- RESUMES TABLE
-- ==========================
CREATE TABLE resumes (
    resume_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resume_title VARCHAR(200),
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_resume_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- ==========================
-- ANALYSIS TABLE
-- ==========================
CREATE TABLE analysis (
    analysis_id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    ats_score INT DEFAULT 0,
    strengths TEXT,
    weaknesses TEXT,
    suggestions TEXT,
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_analysis_resume
        FOREIGN KEY (resume_id)
        REFERENCES resumes(resume_id)
        ON DELETE CASCADE
);

-- ==========================
-- SKILLS TABLE
-- ==========================
CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) UNIQUE
);

-- ==========================
-- RESUME SKILLS
-- ==========================
CREATE TABLE resume_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT,
    skill_id INT,

    CONSTRAINT fk_rs_resume
        FOREIGN KEY (resume_id)
        REFERENCES resumes(resume_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_rs_skill
        FOREIGN KEY (skill_id)
        REFERENCES skills(skill_id)
        ON DELETE CASCADE
);

-- ==========================
-- MISSING SKILLS
-- ==========================
CREATE TABLE missing_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT,
    skill_name VARCHAR(100),

    CONSTRAINT fk_missing_resume
        FOREIGN KEY (resume_id)
        REFERENCES resumes(resume_id)
        ON DELETE CASCADE
);

-- ==========================
-- JOBS
-- ==========================
CREATE TABLE jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150),
    job_title VARCHAR(150),
    required_skills TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- FEEDBACK
-- ==========================
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    rating INT,
    message TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_feedback_user
        FOREIGN KEY(user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- ==========================
-- ACTIVITY LOGS
-- ==========================
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_activity_user
        FOREIGN KEY(user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- ==========================================
-- INSERT DEFAULT SKILLS
-- ==========================================

INSERT INTO skills(skill_name) VALUES
('Java'),
('Python'),
('C'),
('C++'),
('HTML'),
('CSS'),
('JavaScript'),
('React'),
('Node.js'),
('Express'),
('PHP'),
('MySQL'),
('MongoDB'),
('Git'),
('GitHub'),
('Docker'),
('AWS'),
('Machine Learning'),
('Data Structures'),
('DBMS');
