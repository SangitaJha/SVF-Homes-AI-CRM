SET NAMES utf8mb4;

INSERT INTO users (id, name, email, phone, password, role, status, created_at, updated_at) VALUES
(1, 'SVF Admin', 'admin@svfhomes.com', '9999999999', '$2y$10$BdO/RL9qvaIiM0NUcXWgVuoTsHSdVGiLQMBvgGoNhyilCHnaA3W.e', 'Super Admin', 'Active', NOW(), NOW()),
(2, 'Sales Manager', 'manager@svfhomes.com', '8888888888', '$2y$10$BdO/RL9qvaIiM0NUcXWgVuoTsHSdVGiLQMBvgGoNhyilCHnaA3W.e', 'Sales Manager', 'Active', NOW(), NOW()),
(3, 'Accountant', 'accounts@svfhomes.com', '7777777777', '$2y$10$BdO/RL9qvaIiM0NUcXWgVuoTsHSdVGiLQMBvgGoNhyilCHnaA3W.e', 'Accountant', 'Active', NOW(), NOW());

INSERT INTO projects (id, name, builder, location, amenities, description, status, created_at, updated_at) VALUES
(1, 'SVF Homes Residency', 'SVF Builders', 'Hyderabad', 'Clubhouse, Lift, Parking, Security', 'Premium residential project in a growth corridor.', 'Active', NOW(), NOW()),
(2, 'SVF Greens', 'SVF Builders', 'Bengaluru', 'Garden, Gym, CCTV, Power Backup', 'Lifestyle apartments with modern amenities.', 'Planning', NOW(), NOW());

INSERT INTO flats (id, project_id, flat_number, floor, area, facing, price, parking, availability, created_at, updated_at) VALUES
(1, 1, 'A-101', '1', 1280, 'East', 6500000, '1 Car', 'Available', NOW(), NOW()),
(2, 1, 'A-102', '1', 1260, 'West', 6400000, '1 Car', 'Reserved', NOW(), NOW()),
(3, 2, 'B-201', '2', 1450, 'North', 7200000, '2 Cars', 'Sold', NOW(), NOW());

INSERT INTO customers (id, name, mobile, email, address, occupation, notes, created_at, updated_at) VALUES
(1, 'Ravi Kumar', '9876500001', 'ravi@example.com', 'Hyderabad', 'IT Professional', 'Interested in 3 BHK units.', NOW(), NOW()),
(2, 'Anita Sharma', '9876500002', 'anita@example.com', 'Bengaluru', 'Entrepreneur', 'Looking for investment option.', NOW(), NOW());

INSERT INTO leads (id, name, mobile, email, budget, property_type, location, source, status, assigned_to, ai_score, notes, created_by, updated_by, created_at, updated_at) VALUES
(1, 'Rahul Verma', '9876500101', 'rahul@example.com', 5500000, 'Apartment', 'Hyderabad', 'Website', 'Interested', 'Sales Manager', 82, 'Needs quick follow-up and brochure.', 1, 1, NOW(), NOW()),
(2, 'Sneha Iyer', '9876500102', 'sneha@example.com', 3200000, 'Flat', 'Bengaluru', 'Facebook', 'Contacted', 'Sales Executive', 61, 'Requested site visit details.', 1, 1, NOW(), NOW());

INSERT INTO quotations (id, lead_id, customer_id, project_id, flat_id, amount, valid_until, notes, status, created_at, updated_at) VALUES
(1, 1, 1, 1, 1, 6650000, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Includes parking and registration estimate.', 'Sent', NOW(), NOW());

INSERT INTO bookings (id, lead_id, customer_id, project_id, flat_id, quotation_id, booking_amount, status, booking_date, agreement_date, registration_date, notes, created_at, updated_at) VALUES
(1, 1, 1, 1, 1, 1, 500000, 'Booked', CURDATE(), NULL, NULL, 'Token received.', NOW(), NOW());

INSERT INTO payments (id, booking_id, customer_id, amount, payment_mode, due_date, paid_at, status, receipt_no, notes, created_at, updated_at) VALUES
(1, 1, 1, 500000, 'Bank Transfer', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW(), 'Paid', 'RCPT-0001', 'Booking token credited.', NOW(), NOW());

INSERT INTO site_visits (id, lead_id, customer_id, executive_id, scheduled_at, feedback, next_followup_at, status, created_at, updated_at) VALUES
(1, 1, 1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY), 'Interested in corner unit and parking.', DATE_ADD(NOW(), INTERVAL 2 DAY), 'Scheduled', NOW(), NOW());

INSERT INTO followups (id, lead_id, customer_id, assigned_to, followup_at, notes, status, created_at, updated_at) VALUES
(1, 1, 1, 'Sales Manager', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'Send brochure and payment plan.', 'Pending', NOW(), NOW());

INSERT INTO documents (id, customer_id, booking_id, document_type, file_path, notes, status, created_at, updated_at) VALUES
(1, 1, 1, 'Aadhaar', 'uploads/documents/sample-aadhaar.pdf', 'KYC uploaded.', 'Verified', NOW(), NOW());

INSERT INTO notifications (id, user_id, channel, title, message, status, sent_at, created_at, updated_at) VALUES
(1, 1, 'In-App', 'Booking Confirmed', 'Booking token has been received.', 'Sent', NOW(), NOW(), NOW()),
(2, 1, 'WhatsApp', 'Payment Reminder', 'Upcoming installment due soon.', 'Queued', NULL, NOW(), NOW());

INSERT INTO activities (id, user_id, module, action, description, created_at, updated_at) VALUES
(1, 1, 'Leads', 'Created', 'Lead Rahul Verma created by admin.', NOW(), NOW()),
(2, 1, 'Bookings', 'Confirmed', 'Booking confirmed for flat A-101.', NOW(), NOW());