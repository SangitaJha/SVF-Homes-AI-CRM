SET NAMES utf8mb4;
SET time_zone = '+05:30';

CREATE DATABASE IF NOT EXISTS svf_homes_ai_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE svf_homes_ai_crm;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Admin',
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    remember_token VARCHAR(100) NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(30) NOT NULL,
    email VARCHAR(190) NULL,
    budget DECIMAL(14,2) NULL,
    property_type VARCHAR(100) NULL,
    location VARCHAR(150) NULL,
    source VARCHAR(100) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'New',
    assigned_to VARCHAR(150) NULL,
    ai_score INT NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_leads_status (status),
    INDEX idx_leads_ai_score (ai_score),
    INDEX idx_leads_assigned_to (assigned_to),
    INDEX idx_leads_created_at (created_at),
    CONSTRAINT fk_leads_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    aadhaar VARCHAR(20) NULL,
    pan VARCHAR(20) NULL,
    occupation VARCHAR(120) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_mobile (mobile),
    INDEX idx_customers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    builder VARCHAR(150) NULL,
    location VARCHAR(150) NULL,
    amenities TEXT NULL,
    brochure VARCHAR(255) NULL,
    gallery VARCHAR(255) NULL,
    master_plan VARCHAR(255) NULL,
    description TEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Planning',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_projects_status (status),
    INDEX idx_projects_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,
    flat_number VARCHAR(50) NOT NULL,
    floor VARCHAR(50) NULL,
    area DECIMAL(12,2) NULL,
    facing VARCHAR(50) NULL,
    price DECIMAL(14,2) NULL,
    parking VARCHAR(100) NULL,
    availability VARCHAR(20) NOT NULL DEFAULT 'Available',
    image_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_flats_project (project_id),
    INDEX idx_flats_availability (availability),
    CONSTRAINT fk_flats_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    executive_id BIGINT UNSIGNED NULL,
    scheduled_at DATETIME NULL,
    feedback TEXT NULL,
    next_followup_at DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Scheduled',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_site_visits_status (status),
    INDEX idx_site_visits_scheduled_at (scheduled_at),
    CONSTRAINT fk_site_visits_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_site_visits_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_site_visits_executive FOREIGN KEY (executive_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    flat_id BIGINT UNSIGNED NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    valid_until DATE NULL,
    notes TEXT NULL,
    pdf_path VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quotations_status (status),
    CONSTRAINT fk_quotations_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_quotations_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_quotations_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_quotations_flat FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    flat_id BIGINT UNSIGNED NULL,
    quotation_id BIGINT UNSIGNED NULL,
    booking_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Booked',
    booking_date DATE NULL,
    agreement_date DATE NULL,
    registration_date DATE NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_booking_date (booking_date),
    CONSTRAINT fk_bookings_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_flat FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    payment_mode VARCHAR(50) NULL,
    due_date DATE NULL,
    paid_at DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    receipt_no VARCHAR(100) NULL,
    invoice_path VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_status (status),
    INDEX idx_payments_due_date (due_date),
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    CONSTRAINT fk_payments_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NULL,
    booking_id BIGINT UNSIGNED NULL,
    document_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NULL,
    notes TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_documents_type (document_type),
    CONSTRAINT fk_documents_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_documents_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS followups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    assigned_to VARCHAR(150) NULL,
    followup_at DATETIME NULL,
    notes TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_followups_status (status),
    INDEX idx_followups_followup_at (followup_at),
    CONSTRAINT fk_followups_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_followups_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    channel VARCHAR(20) NOT NULL DEFAULT 'In-App',
    title VARCHAR(190) NOT NULL,
    message TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Queued',
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_notifications_status (status),
    INDEX idx_notifications_channel (channel),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    module VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activities_module (module),
    INDEX idx_activities_created_at (created_at),
    CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contractors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    trade VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contractors_status (status),
    INDEX idx_contractors_trade (trade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS labours (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    labour_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(30) NULL,
    address TEXT NULL,
    aadhaar VARCHAR(20) NULL,
    contractor_id BIGINT UNSIGNED NULL,
    trade VARCHAR(100) NOT NULL,
    daily_wage DECIMAL(12,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    joining_date DATE NULL,
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_labours_contractor (contractor_id),
    INDEX idx_labours_trade (trade),
    INDEX idx_labours_status (status),
    CONSTRAINT fk_labours_contractor FOREIGN KEY (contractor_id) REFERENCES contractors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS labour_attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attendance_id VARCHAR(50) NOT NULL UNIQUE,
    labour_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    site VARCHAR(150) NULL,
    contractor_id BIGINT UNSIGNED NULL,
    trade VARCHAR(100) NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    working_hours DECIMAL(6,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(6,2) NOT NULL DEFAULT 0,
    attendance_status VARCHAR(20) NOT NULL DEFAULT 'Present',
    daily_wage DECIMAL(12,2) NOT NULL DEFAULT 0,
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_labour_attendance_date (date),
    INDEX idx_labour_attendance_project (project_id),
    INDEX idx_labour_attendance_contractor (contractor_id),
    INDEX idx_labour_attendance_status (attendance_status),
    CONSTRAINT fk_labour_attendance_labour FOREIGN KEY (labour_id) REFERENCES labours(id) ON DELETE CASCADE,
    CONSTRAINT fk_labour_attendance_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_labour_attendance_contractor FOREIGN KEY (contractor_id) REFERENCES contractors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_work_completed (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    work_id VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    block VARCHAR(100) NULL,
    floor VARCHAR(100) NULL,
    activity VARCHAR(150) NOT NULL,
    description TEXT NULL,
    labour_count INT UNSIGNED NOT NULL DEFAULT 0,
    contractor_id BIGINT UNSIGNED NULL,
    supervisor VARCHAR(150) NULL,
    planned_quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    completed_quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    unit VARCHAR(30) NULL,
    completion_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    before_image VARCHAR(255) NULL,
    after_image VARCHAR(255) NULL,
    materials_used TEXT NULL,
    issues TEXT NULL,
    next_day_plan TEXT NULL,
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_daily_work_date (date),
    INDEX idx_daily_work_project (project_id),
    INDEX idx_daily_work_contractor (contractor_id),
    INDEX idx_daily_work_status (status),
    CONSTRAINT fk_daily_work_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_daily_work_contractor FOREIGN KEY (contractor_id) REFERENCES contractors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(30) NULL,
    department VARCHAR(100) NULL,
    designation VARCHAR(100) NULL,
    manager VARCHAR(150) NULL,
    salary DECIMAL(14,2) NOT NULL DEFAULT 0,
    joining_date DATE NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employees_status (status),
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INT UNSIGNED NOT NULL DEFAULT 1,
    reason TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    manager_comment TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_leave_status (status),
    CONSTRAINT fk_leave_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payrolls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    pay_period VARCHAR(50) NOT NULL,
    basic_salary DECIMAL(14,2) NOT NULL DEFAULT 0,
    allowances DECIMAL(14,2) NOT NULL DEFAULT 0,
    deductions DECIMAL(14,2) NOT NULL DEFAULT 0,
    net_salary DECIMAL(14,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payroll_period (pay_period),
    CONSTRAINT fk_payroll_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150) NULL,
    mobile VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    category VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_suppliers_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NULL,
    unit VARCHAR(50) NULL,
    unit_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    stock_quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_materials_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    location VARCHAR(150) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Available',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_inventory_status (status),
    CONSTRAINT fk_inventory_material FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id BIGINT UNSIGNED NULL,
    material_id BIGINT UNSIGNED NULL,
    purchase_date DATE NOT NULL,
    quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_purchase_status (status),
    CONSTRAINT fk_purchase_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    CONSTRAINT fk_purchase_material FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    assigned_to VARCHAR(150) NULL,
    due_date DATE NULL,
    priority VARCHAR(20) NOT NULL DEFAULT 'Medium',
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tasks_status (status),
    INDEX idx_tasks_due_date (due_date),
    CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS meetings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    meeting_date DATETIME NOT NULL,
    location VARCHAR(150) NULL,
    attendees TEXT NULL,
    agenda TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Scheduled',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_meetings_date (meeting_date),
    INDEX idx_meetings_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sop_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NULL,
    content TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sop_templates_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED NULL,
    title VARCHAR(190) NOT NULL,
    department VARCHAR(100) NULL,
    owner VARCHAR(150) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft',
    content TEXT NULL,
    attachments VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sops_status (status),
    CONSTRAINT fk_sops_template FOREIGN KEY (template_id) REFERENCES sop_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NULL,
    `group` VARCHAR(100) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    module VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_module (module),
    INDEX idx_audit_created_at (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_requirements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requirement_name VARCHAR(190) NOT NULL,
    preferred_location VARCHAR(190) NULL,
    area_required VARCHAR(100) NULL,
    unit VARCHAR(50) NULL,
    road_width VARCHAR(100) NULL,
    budget_range VARCHAR(100) NULL,
    preferred_zone VARCHAR(100) NULL,
    project_type VARCHAR(100) NULL,
    expected_units INT UNSIGNED NULL,
    priority VARCHAR(50) NULL,
    assigned_employee VARCHAR(150) NULL,
    expected_purchase_date DATE NULL,
    remarks TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_requirements_status (status),
    INDEX idx_land_requirements_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_name VARCHAR(190) NOT NULL,
    broker_name VARCHAR(190) NULL,
    mobile VARCHAR(30) NOT NULL,
    email VARCHAR(190) NULL,
    property_location VARCHAR(190) NULL,
    survey_number VARCHAR(100) NULL,
    village VARCHAR(100) NULL,
    taluk VARCHAR(100) NULL,
    district VARCHAR(100) NULL,
    extent VARCHAR(100) NULL,
    expected_price DECIMAL(14,2) NULL,
    price_per_sqft DECIMAL(14,2) NULL,
    source VARCHAR(100) NULL,
    remarks TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'New Lead',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_leads_status (status),
    INDEX idx_land_leads_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_owners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_name VARCHAR(190) NOT NULL,
    father_name VARCHAR(190) NULL,
    address TEXT NULL,
    mobile VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    pan VARCHAR(30) NULL,
    aadhaar VARCHAR(30) NULL,
    gst_number VARCHAR(30) NULL,
    bank_details TEXT NULL,
    previous_ownership_details TEXT NULL,
    patta_path VARCHAR(255) NULL,
    ec_path VARCHAR(255) NULL,
    sale_deed_path VARCHAR(255) NULL,
    tax_receipt_path VARCHAR(255) NULL,
    fmb_sketch_path VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_owners_status (status),
    INDEX idx_land_owners_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_site_visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visit_id VARCHAR(100) NULL,
    visit_date DATE NULL,
    visit_time VARCHAR(50) NULL,
    location VARCHAR(190) NULL,
    gps_coordinates VARCHAR(100) NULL,
    assigned_employee VARCHAR(150) NULL,
    owner_meeting VARCHAR(10) NULL,
    visit_notes TEXT NULL,
    photos_path VARCHAR(255) NULL,
    videos_path VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Scheduled',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_site_visits_status (status),
    INDEX idx_land_site_visits_date (visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_document_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_name VARCHAR(190) NULL,
    patta_verification VARCHAR(50) NULL,
    ec_verification VARCHAR(50) NULL,
    parent_document_verification VARCHAR(50) NULL,
    ownership_verification VARCHAR(50) NULL,
    survey_verification VARCHAR(50) NULL,
    encumbrance_check VARCHAR(50) NULL,
    government_approval_possibility VARCHAR(50) NULL,
    litigation_check VARCHAR(50) NULL,
    verification_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    legal_remarks TEXT NULL,
    support_document_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_document_verifications_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_name VARCHAR(190) NULL,
    land_area VARCHAR(100) NULL,
    purchase_cost DECIMAL(14,2) NULL,
    construction_cost DECIMAL(14,2) NULL,
    marketing_cost DECIMAL(14,2) NULL,
    legal_cost DECIMAL(14,2) NULL,
    registration_charges DECIMAL(14,2) NULL,
    working_capital DECIMAL(14,2) NULL,
    other_expenses DECIMAL(14,2) NULL,
    expected_units INT UNSIGNED NULL,
    estimated_revenue DECIMAL(14,2) NULL,
    profit DECIMAL(14,2) NULL,
    roi_percent DECIMAL(8,2) NULL,
    construction_timeline VARCHAR(100) NULL,
    risk_score INT UNSIGNED NULL,
    evaluation_notes TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Review',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_evaluations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_negotiations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_name VARCHAR(190) NULL,
    owner_asking_price DECIMAL(14,2) NULL,
    current_market_price DECIMAL(14,2) NULL,
    svf_offer_price DECIMAL(14,2) NULL,
    counter_offer DECIMAL(14,2) NULL,
    final_agreed_price DECIMAL(14,2) NULL,
    advance_amount DECIMAL(14,2) NULL,
    payment_schedule VARCHAR(190) NULL,
    negotiation_notes TEXT NULL,
    meeting_history TEXT NULL,
    negotiation_status VARCHAR(50) NOT NULL DEFAULT 'Open',
    strategy_recommendation TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_negotiations_status (negotiation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_agreements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_name VARCHAR(190) NULL,
    agreement_type VARCHAR(100) NULL,
    owner_share_percent DECIMAL(8,2) NULL,
    builder_share_percent DECIMAL(8,2) NULL,
    deposit DECIMAL(14,2) NULL,
    construction_responsibility VARCHAR(190) NULL,
    timeline VARCHAR(100) NULL,
    agreement_terms TEXT NULL,
    sale_price DECIMAL(14,2) NULL,
    advance DECIMAL(14,2) NULL,
    registration_date DATE NULL,
    balance_payment DECIMAL(14,2) NULL,
    registration_details TEXT NULL,
    agreement_documents VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_agreements_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_name VARCHAR(190) NULL,
    approval_stage VARCHAR(100) NULL,
    approval_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    comments TEXT NULL,
    email_notifications VARCHAR(20) NULL,
    system_notifications VARCHAR(20) NULL,
    approval_history TEXT NULL,
    digital_approval_status VARCHAR(20) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_approvals_status (approval_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS land_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_name VARCHAR(190) NULL,
    payment_type VARCHAR(100) NULL,
    paid_amount DECIMAL(14,2) NULL,
    pending_amount DECIMAL(14,2) NULL,
    due_amount DECIMAL(14,2) NULL,
    due_date DATE NULL,
    payment_history TEXT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_land_payments_status (payment_status),
    INDEX idx_land_payments_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;