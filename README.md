# SVF Homes AI CRM

Production-ready PHP 8 MVC CRM for real estate leads, customers, projects, inventory, bookings, payments, documents, reports, notifications, and AI workflows. Production of the product is completed here properly.

## Installation

1. Create a MySQL database named `svf_homes_ai_crm`.
2. Import `database/schema.sql`.
3. Import `database/seed.sql`.
4. Copy `.env.example` to `.env` and update the database and API values.
5. Run `composer install`.
6. Serve the project with Apache/XAMPP and open the project folder URL.

## Demo Login

- Email: `admin@svfhomes.com`
- Password: `password123`

## Notes


- All uploaded files are stored in `uploads/` during development.
- Web routes are handled from `index.php`.
- API routes are available under `/api/v1/*`.
