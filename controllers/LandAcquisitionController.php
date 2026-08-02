<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\LandAcquisitionModel;

final class LandAcquisitionController extends Controller
{
    public function dashboard(): void
    {
        require_auth();

        $requirements = $this->loadRecords('requirements');
        $leads = $this->loadRecords('leads');
        $owners = $this->loadRecords('owners');
        $siteVisits = $this->loadRecords('site_visits');
        $payments = $this->loadRecords('payments');
        $approvals = $this->loadRecords('approvals');
        $evaluations = $this->loadRecords('land_evaluation');

        $metrics = [
            ['label' => 'Land Requirements', 'value' => count($requirements), 'icon' => 'fa-map-location-dot', 'tone' => 'primary'],
            ['label' => 'Land Leads', 'value' => count($leads), 'icon' => 'fa-bullhorn', 'tone' => 'success'],
            ['label' => 'Active Site Visits', 'value' => count(array_filter($siteVisits, static fn(array $row): bool => ($row['status'] ?? '') !== 'Completed')), 'icon' => 'fa-location-dot', 'tone' => 'info'],
            ['label' => 'Pending Verifications', 'value' => count(array_filter($this->loadRecords('document_verification'), static fn(array $row): bool => ($row['verification_status'] ?? 'Pending') === 'Pending')), 'icon' => 'fa-file-contract', 'tone' => 'warning'],
            ['label' => 'Ongoing Negotiations', 'value' => count(array_filter($this->loadRecords('negotiation'), static fn(array $row): bool => ($row['negotiation_status'] ?? 'Open') !== 'Closed')), 'icon' => 'fa-handshake', 'tone' => 'danger'],
            ['label' => 'Approved Lands', 'value' => count(array_filter($leads, static fn(array $row): bool => ($row['status'] ?? '') === 'Approved')), 'icon' => 'fa-circle-check', 'tone' => 'success'],
            ['label' => 'Rejected Lands', 'value' => count(array_filter($leads, static fn(array $row): bool => ($row['status'] ?? '') === 'Rejected')), 'icon' => 'fa-circle-xmark', 'tone' => 'danger'],
            ['label' => 'Total Investment', 'value' => format_currency($this->sumNumeric($evaluations, 'purchase_cost') + $this->sumNumeric($evaluations, 'construction_cost')), 'icon' => 'fa-indian-rupee-sign', 'tone' => 'primary'],
            ['label' => 'Upcoming Payments', 'value' => count(array_filter($payments, static fn(array $row): bool => ($row['payment_status'] ?? 'Pending') !== 'Paid')), 'icon' => 'fa-wallet', 'tone' => 'warning'],
            ['label' => 'Projects Ready', 'value' => count(array_filter($approvals, static fn(array $row): bool => ($row['approval_status'] ?? '') === 'Final Approval')), 'icon' => 'fa-city', 'tone' => 'info'],
        ];

        $monthlyTrend = [
            ['month' => 'Jan', 'value' => 6],
            ['month' => 'Feb', 'value' => 8],
            ['month' => 'Mar', 'value' => 12],
            ['month' => 'Apr', 'value' => 10],
            ['month' => 'May', 'value' => 15],
            ['month' => 'Jun', 'value' => 18],
        ];

        $investmentTrend = [
            ['month' => 'Jan', 'value' => 2.4],
            ['month' => 'Feb', 'value' => 3.1],
            ['month' => 'Mar', 'value' => 4.7],
            ['month' => 'Apr', 'value' => 5.2],
            ['month' => 'May', 'value' => 6.8],
            ['month' => 'Jun', 'value' => 7.9],
        ];

        $locationDistribution = [
            ['label' => 'Mysuru', 'value' => 24],
            ['label' => 'Bengaluru', 'value' => 14],
            ['label' => 'Coimbatore', 'value' => 10],
            ['label' => 'Chennai', 'value' => 9],
        ];

        $activityFeed = [
            ['title' => 'New land lead captured', 'detail' => 'Direct owner request from Mysuru East zone.'],
            ['title' => 'Document verification pending', 'detail' => '2 legal packages require owner evidence upload.'],
            ['title' => 'Approval routed to Director', 'detail' => 'Negotiation closed for a 10-ground parcel.'],
        ];

        $this->render('land-acquisition/dashboard', compact('metrics', 'monthlyTrend', 'investmentTrend', 'locationDistribution', 'activityFeed'));
    }

    public function module(string $section): void
    {
        require_auth();

        $section = $this->normalizeSection($section);
        $module = $this->moduleConfig($section);
        $records = $this->loadRecords($section);
        $records = $this->filterRecords($records);
        $editRecord = null;

        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $editRecord = $this->findRecord($records, (int)$_GET['edit']);
        }

        $summaryCards = $this->buildSummaryCards($section, $records);
        $pipelineStages = $section === 'leads' ? $this->buildLeadPipeline($records) : [];

        $this->render('land-acquisition/module', compact('section', 'module', 'records', 'editRecord', 'summaryCards', 'pipelineStages'));
    }

    public function storeModule(string $section): void
    {
        require_auth();
        verify_csrf();

        $section = $this->normalizeSection($section);
        $module = $this->moduleConfig($section);
        $records = $this->loadRecords($section);
        $editId = isset($_POST['edit_id']) && $_POST['edit_id'] !== '' ? (int)$_POST['edit_id'] : null;

        $record = [
            'id' => $editId ?? $this->nextId($records),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        foreach ($module['fields'] as $field) {
            $name = (string)$field['name'];
            $value = $_POST[$name] ?? '';

            if (($field['type'] ?? 'text') === 'file') {
                $existing = $_POST['existing_' . $name] ?? '';
                $value = $this->handleUpload($name, $existing);
            }

            $record[$name] = $this->normalizeValue($field, $value);
        }

        if ($editId !== null) {
            $index = $this->findIndex($records, $editId);
            if ($index !== null) {
                $record['created_at'] = $records[$index]['created_at'] ?? $record['created_at'];
                $records[$index] = $record;
            }
        } else {
            $records[] = $record;
        }

        $this->saveRecords($section, $records);
        flash('success', 'Land acquisition record saved successfully.');
        redirect('/land-acquisition/index.php?section=' . $section);
    }

    public function deleteModule(string $section, int $id): void
    {
        require_auth();
        verify_csrf();

        $section = $this->normalizeSection($section);
        $records = $this->loadRecords($section);
        $records = array_values(array_filter($records, static fn(array $row): bool => (int)($row['id'] ?? 0) !== $id));
        $this->saveRecords($section, $records);
        flash('success', 'Record removed.');
        redirect('/land-acquisition/index.php?section=' . $section);
    }

    private function moduleConfig(string $section): array
    {
        $modules = [
            'requirements' => [
                'label' => 'Land Requirements',
                'description' => 'Create and manage land acquisition requirements for new regional projects.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'requirement_name', 'label' => 'Requirement Name', 'type' => 'text', 'required' => true],
                    ['name' => 'preferred_location', 'label' => 'Preferred Location', 'type' => 'text', 'required' => true],
                    ['name' => 'area_required', 'label' => 'Area Required', 'type' => 'text'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'select', 'options' => ['Sq.ft', 'Ground', 'Acre']],
                    ['name' => 'road_width', 'label' => 'Road Width', 'type' => 'text'],
                    ['name' => 'budget_range', 'label' => 'Budget Range', 'type' => 'text'],
                    ['name' => 'preferred_zone', 'label' => 'Preferred Zone', 'type' => 'text'],
                    ['name' => 'project_type', 'label' => 'Project Type', 'type' => 'select', 'options' => ['Apartment', 'Villa', 'Commercial', 'Layout']],
                    ['name' => 'expected_units', 'label' => 'Expected Number of Units', 'type' => 'number'],
                    ['name' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => ['High', 'Medium', 'Low']],
                    ['name' => 'assigned_employee', 'label' => 'Assigned Employee', 'type' => 'text'],
                    ['name' => 'expected_purchase_date', 'label' => 'Expected Purchase Date', 'type' => 'date'],
                    ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Open', 'Shortlisted', 'In Progress', 'Approved', 'Closed']],
                ],
                'columns' => ['requirement_name', 'preferred_location', 'budget_range', 'priority', 'status'],
                'defaultStatus' => 'Open',
            ],
            'leads' => [
                'label' => 'Land Leads',
                'description' => 'Manage land opportunities from first enquiry to final approval.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'owner_name', 'label' => 'Owner Name', 'type' => 'text', 'required' => true],
                    ['name' => 'broker_name', 'label' => 'Broker Name', 'type' => 'text'],
                    ['name' => 'mobile', 'label' => 'Mobile Number', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'property_location', 'label' => 'Property Location', 'type' => 'text'],
                    ['name' => 'survey_number', 'label' => 'Survey Number', 'type' => 'text'],
                    ['name' => 'village', 'label' => 'Village', 'type' => 'text'],
                    ['name' => 'taluk', 'label' => 'Taluk', 'type' => 'text'],
                    ['name' => 'district', 'label' => 'District', 'type' => 'text'],
                    ['name' => 'extent', 'label' => 'Extent', 'type' => 'text'],
                    ['name' => 'expected_price', 'label' => 'Expected Price', 'type' => 'number'],
                    ['name' => 'price_per_sqft', 'label' => 'Price Per Sq.ft', 'type' => 'number'],
                    ['name' => 'source', 'label' => 'Source', 'type' => 'select', 'options' => ['Direct Owner', 'Broker', 'Referral', 'Online Enquiry', 'Advertisement']],
                    ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['New Lead', 'Contacted', 'Site Visit Planned', 'Document Verification', 'Negotiation', 'Approved', 'Rejected']],
                ],
                'columns' => ['owner_name', 'property_location', 'source', 'expected_price', 'status'],
                'defaultStatus' => 'New Lead',
                'pipelineStatuses' => ['New Lead', 'Contacted', 'Site Visit Planned', 'Document Verification', 'Negotiation', 'Approved', 'Rejected'],
            ],
            'owners' => [
                'label' => 'Owner Database',
                'description' => 'Store complete owner information and supporting documents.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'owner_name', 'label' => 'Owner Name', 'type' => 'text', 'required' => true],
                    ['name' => 'father_name', 'label' => 'Father / Husband Name', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                    ['name' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'pan', 'label' => 'PAN', 'type' => 'text'],
                    ['name' => 'aadhaar', 'label' => 'Aadhaar', 'type' => 'text'],
                    ['name' => 'gst_number', 'label' => 'GST Number', 'type' => 'text'],
                    ['name' => 'bank_details', 'label' => 'Bank Details', 'type' => 'textarea'],
                    ['name' => 'previous_ownership_details', 'label' => 'Previous Ownership Details', 'type' => 'textarea'],
                    ['name' => 'patta_path', 'label' => 'Patta', 'type' => 'file'],
                    ['name' => 'ec_path', 'label' => 'EC', 'type' => 'file'],
                    ['name' => 'sale_deed_path', 'label' => 'Sale Deed', 'type' => 'file'],
                    ['name' => 'tax_receipt_path', 'label' => 'Tax Receipt', 'type' => 'file'],
                    ['name' => 'fmb_sketch_path', 'label' => 'FMB Sketch', 'type' => 'file'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Draft', 'Verified', 'Pending']],
                ],
                'columns' => ['owner_name', 'mobile', 'pan', 'status'],
                'defaultStatus' => 'Pending',
            ],
            'site_visits' => [
                'label' => 'Site Visits',
                'description' => 'Plan site visits, track GPS coordinates, and capture field intelligence.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'visit_id', 'label' => 'Visit ID', 'type' => 'text'],
                    ['name' => 'visit_date', 'label' => 'Date', 'type' => 'date'],
                    ['name' => 'visit_time', 'label' => 'Time', 'type' => 'text'],
                    ['name' => 'location', 'label' => 'Location', 'type' => 'text'],
                    ['name' => 'gps_coordinates', 'label' => 'GPS Coordinates', 'type' => 'text'],
                    ['name' => 'assigned_employee', 'label' => 'Assigned Employee', 'type' => 'text'],
                    ['name' => 'owner_meeting', 'label' => 'Owner Meeting', 'type' => 'select', 'options' => ['Yes', 'No']],
                    ['name' => 'visit_notes', 'label' => 'Visit Notes', 'type' => 'textarea'],
                    ['name' => 'photos_path', 'label' => 'Photos', 'type' => 'file'],
                    ['name' => 'videos_path', 'label' => 'Videos', 'type' => 'file'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Scheduled', 'Completed', 'Missed']],
                ],
                'columns' => ['visit_id', 'location', 'assigned_employee', 'status'],
                'defaultStatus' => 'Scheduled',
            ],
            'document_verification' => [
                'label' => 'Document Verification',
                'description' => 'Track legal verification tasks and keep the legal checklist current.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'lead_name', 'label' => 'Lead Name', 'type' => 'text'],
                    ['name' => 'patta_verification', 'label' => 'Patta Verification', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'ec_verification', 'label' => 'EC Verification', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'parent_document_verification', 'label' => 'Parent Documents', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'ownership_verification', 'label' => 'Ownership Verification', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'survey_verification', 'label' => 'Survey Verification', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'encumbrance_check', 'label' => 'Encumbrance Check', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'government_approval_possibility', 'label' => 'Government Approval Possibility', 'type' => 'select', 'options' => ['High', 'Medium', 'Low']],
                    ['name' => 'litigation_check', 'label' => 'Litigation Check', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'verification_status', 'label' => 'Status', 'type' => 'select', 'options' => ['Verified', 'Pending', 'Risk']],
                    ['name' => 'legal_remarks', 'label' => 'Legal Remarks', 'type' => 'textarea'],
                    ['name' => 'support_document_path', 'label' => 'Supporting Documents', 'type' => 'file'],
                ],
                'columns' => ['lead_name', 'verification_status', 'patta_verification', 'encumbrance_check'],
                'defaultStatus' => 'Pending',
            ],
            'land_evaluation' => [
                'label' => 'Land Evaluation',
                'description' => 'Run an AI-assisted investment feasibility analysis for every land opportunity.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'lead_name', 'label' => 'Lead Name', 'type' => 'text'],
                    ['name' => 'land_area', 'label' => 'Land Area', 'type' => 'text'],
                    ['name' => 'purchase_cost', 'label' => 'Purchase Cost', 'type' => 'number'],
                    ['name' => 'construction_cost', 'label' => 'Construction Cost', 'type' => 'number'],
                    ['name' => 'marketing_cost', 'label' => 'Marketing Cost', 'type' => 'number'],
                    ['name' => 'legal_cost', 'label' => 'Legal Cost', 'type' => 'number'],
                    ['name' => 'registration_charges', 'label' => 'Registration Charges', 'type' => 'number'],
                    ['name' => 'working_capital', 'label' => 'Working Capital', 'type' => 'number'],
                    ['name' => 'other_expenses', 'label' => 'Other Expenses', 'type' => 'number'],
                    ['name' => 'expected_units', 'label' => 'Expected Units', 'type' => 'number'],
                    ['name' => 'estimated_revenue', 'label' => 'Estimated Revenue', 'type' => 'number'],
                    ['name' => 'profit', 'label' => 'Profit', 'type' => 'number'],
                    ['name' => 'roi_percent', 'label' => 'ROI %', 'type' => 'number'],
                    ['name' => 'construction_timeline', 'label' => 'Construction Timeline', 'type' => 'text'],
                    ['name' => 'risk_score', 'label' => 'Risk Score', 'type' => 'number'],
                    ['name' => 'evaluation_notes', 'label' => 'Notes', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Recommended', 'Review', 'Hold']],
                ],
                'columns' => ['lead_name', 'expected_units', 'roi_percent', 'status'],
                'defaultStatus' => 'Review',
            ],
            'negotiation' => [
                'label' => 'Negotiation',
                'description' => 'Track negotiation outcomes and receive AI-based strategy suggestions.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'lead_name', 'label' => 'Lead Name', 'type' => 'text'],
                    ['name' => 'owner_asking_price', 'label' => 'Owner Asking Price', 'type' => 'number'],
                    ['name' => 'current_market_price', 'label' => 'Current Market Price', 'type' => 'number'],
                    ['name' => 'svf_offer_price', 'label' => 'SVF Offer Price', 'type' => 'number'],
                    ['name' => 'counter_offer', 'label' => 'Counter Offer', 'type' => 'number'],
                    ['name' => 'final_agreed_price', 'label' => 'Final Agreed Price', 'type' => 'number'],
                    ['name' => 'advance_amount', 'label' => 'Advance Amount', 'type' => 'number'],
                    ['name' => 'payment_schedule', 'label' => 'Payment Schedule', 'type' => 'text'],
                    ['name' => 'negotiation_notes', 'label' => 'Negotiation Notes', 'type' => 'textarea'],
                    ['name' => 'meeting_history', 'label' => 'Meeting History', 'type' => 'textarea'],
                    ['name' => 'negotiation_status', 'label' => 'Negotiation Status', 'type' => 'select', 'options' => ['Open', 'In Progress', 'Closed']],
                    ['name' => 'strategy_recommendation', 'label' => 'AI Strategy Recommendation', 'type' => 'textarea'],
                ],
                'columns' => ['lead_name', 'final_agreed_price', 'negotiation_status'],
                'defaultStatus' => 'Open',
            ],
            'agreements' => [
                'label' => 'JDA / Sale Agreement',
                'description' => 'Capture agreement details for joint development or outright purchase models.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'lead_name', 'label' => 'Lead Name', 'type' => 'text'],
                    ['name' => 'agreement_type', 'label' => 'Agreement Type', 'type' => 'select', 'options' => ['JDA', 'Sale Agreement']],
                    ['name' => 'owner_share_percent', 'label' => 'Owner Share %', 'type' => 'number'],
                    ['name' => 'builder_share_percent', 'label' => 'Builder Share %', 'type' => 'number'],
                    ['name' => 'deposit', 'label' => 'Deposit', 'type' => 'number'],
                    ['name' => 'construction_responsibility', 'label' => 'Construction Responsibility', 'type' => 'text'],
                    ['name' => 'timeline', 'label' => 'Timeline', 'type' => 'text'],
                    ['name' => 'agreement_terms', 'label' => 'Agreement Terms', 'type' => 'textarea'],
                    ['name' => 'sale_price', 'label' => 'Sale Price', 'type' => 'number'],
                    ['name' => 'advance', 'label' => 'Advance', 'type' => 'number'],
                    ['name' => 'registration_date', 'label' => 'Registration Date', 'type' => 'date'],
                    ['name' => 'balance_payment', 'label' => 'Balance Payment', 'type' => 'number'],
                    ['name' => 'registration_details', 'label' => 'Registration Details', 'type' => 'textarea'],
                    ['name' => 'agreement_documents', 'label' => 'Agreement Documents', 'type' => 'file'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Draft', 'In Review', 'Signed']],
                ],
                'columns' => ['lead_name', 'agreement_type', 'status'],
                'defaultStatus' => 'Draft',
            ],
            'approvals' => [
                'label' => 'Approval Workflow',
                'description' => 'Coordinate multi-level approvals from the rural land team to the director.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'lead_name', 'label' => 'Lead Name', 'type' => 'text'],
                    ['name' => 'approval_stage', 'label' => 'Approval Stage', 'type' => 'select', 'options' => ['Land Executive', 'Land Manager', 'Legal Team', 'Accounts Department', 'Director', 'Final Approval']],
                    ['name' => 'approval_status', 'label' => 'Approval Status', 'type' => 'select', 'options' => ['Pending', 'Approved', 'Rejected']],
                    ['name' => 'comments', 'label' => 'Comments', 'type' => 'textarea'],
                    ['name' => 'email_notifications', 'label' => 'Email Notifications', 'type' => 'select', 'options' => ['Enabled', 'Disabled']],
                    ['name' => 'system_notifications', 'label' => 'System Notifications', 'type' => 'select', 'options' => ['Enabled', 'Disabled']],
                    ['name' => 'approval_history', 'label' => 'Approval History', 'type' => 'textarea'],
                    ['name' => 'digital_approval_status', 'label' => 'Digital Approval Status', 'type' => 'select', 'options' => ['Pending', 'Signed']],
                ],
                'columns' => ['lead_name', 'approval_stage', 'approval_status'],
                'defaultStatus' => 'Pending',
            ],
            'payments' => [
                'label' => 'Payment Tracking',
                'description' => 'Track every land acquisition payment with due-date reminders and outstanding balances.',
                'showForm' => true,
                'fields' => [
                    ['name' => 'lead_name', 'label' => 'Lead Name', 'type' => 'text'],
                    ['name' => 'payment_type', 'label' => 'Payment Type', 'type' => 'select', 'options' => ['Token Advance', 'Agreement Advance', 'Registration Payment', 'Final Payment', 'Miscellaneous Expenses']],
                    ['name' => 'paid_amount', 'label' => 'Paid Amount', 'type' => 'number'],
                    ['name' => 'pending_amount', 'label' => 'Pending Amount', 'type' => 'number'],
                    ['name' => 'due_amount', 'label' => 'Due Amount', 'type' => 'number'],
                    ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
                    ['name' => 'payment_history', 'label' => 'Payment History', 'type' => 'textarea'],
                    ['name' => 'payment_status', 'label' => 'Payment Status', 'type' => 'select', 'options' => ['Paid', 'Pending', 'Overdue']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
                'columns' => ['lead_name', 'payment_type', 'due_date', 'payment_status'],
                'defaultStatus' => 'Pending',
            ],
            'reports' => [
                'label' => 'Reports',
                'description' => 'Generate executive-ready reports and export them for leadership reviews.',
                'showForm' => false,
                'fields' => [],
                'columns' => [],
            ],
            'ai-assistant' => [
                'label' => 'AI Land Assistant',
                'description' => 'Receive AI-led property recommendations, risk alerts, and investment suggestions.',
                'showForm' => false,
                'fields' => [],
                'columns' => [],
            ],
        ];

        return $modules[$section] ?? $modules['requirements'];
    }

    private function normalizeSection(string $section): string
    {
        return match ($section) {
            'requirements' => 'requirements',
            'leads' => 'leads',
            'owners' => 'owners',
            'site-visits', 'site_visits' => 'site_visits',
            'document-verification', 'document_verification' => 'document_verification',
            'land-evaluation', 'land_evaluation' => 'land_evaluation',
            'negotiation', 'negotiations' => 'negotiation',
            'agreements', 'jda', 'sale-agreement' => 'agreements',
            'approvals' => 'approvals',
            'payments' => 'payments',
            'reports' => 'reports',
            'ai-assistant', 'ai_assistant' => 'ai-assistant',
            default => 'requirements',
        };
    }

    private function loadRecords(string $section): array
    {
        $table = $this->tableForSection($section);
        if ($table !== null) {
            try {
                $model = new LandAcquisitionModel($table);
                return $model->listRecords('id DESC');
            } catch (\Throwable) {
                // Fall back to JSON storage if the table is unavailable.
            }
        }

        $path = storage_path('land-acquisition/' . $section . '.json');
        if (!is_file($path)) {
            $seed = $this->seedRecords($section);
            $this->saveRecords($section, $seed);
            return $seed;
        }

        $data = storage_json_read($path, []);
        return is_array($data) ? $data : [];
    }

    private function saveRecords(string $section, array $records): void
    {
        $table = $this->tableForSection($section);
        if ($table !== null) {
            try {
                $model = new LandAcquisitionModel($table);
                $existingIds = [];
                foreach ($records as $record) {
                    $id = (int)($record['id'] ?? 0);
                    if ($id > 0) {
                        $existingIds[] = $id;
                    }
                }
                $existing = $model->listRecords('id DESC');
                $existingById = [];
                foreach ($existing as $row) {
                    $existingById[(int)($row['id'] ?? 0)] = $row;
                }

                foreach ($records as $record) {
                    $id = (int)($record['id'] ?? 0);
                    if ($id > 0 && isset($existingById[$id])) {
                        $model->updateRecord($id, $this->normalizeRecordForTable($section, $record));
                        continue;
                    }

                    $model->createRecord($this->normalizeRecordForTable($section, $record));
                }

                foreach ($existingById as $id => $row) {
                    if (!in_array($id, $existingIds, true)) {
                        $model->deleteRecord($id);
                    }
                }

                return;
            } catch (\Throwable) {
                // Fall back to JSON storage if the table is unavailable.
            }
        }

        $path = storage_path('land-acquisition/' . $section . '.json');
        ensure_directory(dirname($path));
        storage_json_write($path, array_values($records));
    }

    private function seedRecords(string $section): array
    {
        return match ($section) {
            'requirements' => [
                ['id' => 1, 'requirement_name' => 'Mysuru Growth Corridor', 'preferred_location' => 'Mysuru', 'area_required' => '12 Grounds', 'unit' => 'Ground', 'road_width' => '30 ft', 'budget_range' => '₹3.5 Crore - ₹4.5 Crore', 'preferred_zone' => 'North', 'project_type' => 'Apartment', 'expected_units' => 120, 'priority' => 'High', 'assigned_employee' => 'Ravi Kumar', 'expected_purchase_date' => '2026-10-01', 'remarks' => 'Priority project for Q4 launch.', 'status' => 'In Progress'],
                ['id' => 2, 'requirement_name' => 'Coimbatore Logistics Park', 'preferred_location' => 'Coimbatore', 'area_required' => '20 Grounds', 'unit' => 'Ground', 'road_width' => '40 ft', 'budget_range' => '₹5 Crore - ₹6 Crore', 'preferred_zone' => 'East', 'project_type' => 'Commercial', 'expected_units' => 60, 'priority' => 'Medium', 'assigned_employee' => 'Asha Menon', 'expected_purchase_date' => '2026-11-15', 'remarks' => 'Commercial mix with warehouse and office.', 'status' => 'Shortlisted'],
            ],
            'leads' => [
                ['id' => 1, 'owner_name' => 'Mahesh Rao', 'broker_name' => 'Deepak', 'mobile' => '9876543210', 'email' => 'mahesh@example.com', 'property_location' => 'Mysuru East', 'survey_number' => '88/3', 'village' => 'Bogadi', 'taluk' => 'Mysuru', 'district' => 'Mysuru', 'extent' => '10 Grounds', 'expected_price' => 40000000, 'price_per_sqft' => 3200, 'source' => 'Direct Owner', 'remarks' => 'Strong owner motivation.', 'status' => 'Approved'],
                ['id' => 2, 'owner_name' => 'Suresh Nair', 'broker_name' => 'Lal', 'mobile' => '9988776655', 'email' => 'suresh@example.com', 'property_location' => 'Coimbatore South', 'survey_number' => '11/2', 'village' => 'Kovaipudur', 'taluk' => 'Coimbatore', 'district' => 'Coimbatore', 'extent' => '16 Grounds', 'expected_price' => 60000000, 'price_per_sqft' => 2800, 'source' => 'Broker', 'remarks' => 'Needs legal due diligence.', 'status' => 'Negotiation'],
                ['id' => 3, 'owner_name' => 'Anitha Babu', 'broker_name' => '', 'mobile' => '9123456789', 'email' => 'anitha@example.com', 'property_location' => 'Bengaluru Outer Ring', 'survey_number' => '21/5', 'village' => 'Hoskote', 'taluk' => 'Bengaluru', 'district' => 'Bengaluru', 'extent' => '18 Grounds', 'expected_price' => 75000000, 'price_per_sqft' => 3100, 'source' => 'Referral', 'remarks' => 'High demand corridor.', 'status' => 'Site Visit Planned'],
            ],
            'owners' => [
                ['id' => 1, 'owner_name' => 'Mahesh Rao', 'father_name' => 'Ravi Rao', 'address' => 'Bogadi, Mysuru', 'mobile' => '9876543210', 'email' => 'mahesh@example.com', 'pan' => 'ABCDE1234F', 'aadhaar' => '1234 5678 9012', 'gst_number' => '29ABCDE1234F1Z6', 'bank_details' => 'Axis Bank • 123456789012', 'previous_ownership_details' => 'Private family ownership bundle', 'status' => 'Verified'],
            ],
            'site_visits' => [
                ['id' => 1, 'visit_id' => 'SV-101', 'visit_date' => '2026-07-31', 'visit_time' => '10:00 AM', 'location' => 'Mysuru East', 'gps_coordinates' => '12.2958,76.6394', 'assigned_employee' => 'Ravi Kumar', 'owner_meeting' => 'Yes', 'visit_notes' => 'Road connectivity is strong and the terrain is suitable.', 'status' => 'Scheduled'],
            ],
            'document_verification' => [
                ['id' => 1, 'lead_name' => 'Mahesh Rao', 'patta_verification' => 'Verified', 'ec_verification' => 'Verified', 'parent_document_verification' => 'Verified', 'ownership_verification' => 'Verified', 'survey_verification' => 'Verified', 'encumbrance_check' => 'Verified', 'government_approval_possibility' => 'High', 'litigation_check' => 'Verified', 'verification_status' => 'Verified', 'legal_remarks' => 'All documents consistent and ready for approval.', 'support_document_path' => ''],
            ],
            'land_evaluation' => [
                ['id' => 1, 'lead_name' => 'Mahesh Rao', 'land_area' => '10 Grounds', 'purchase_cost' => 40000000, 'construction_cost' => 50000000, 'marketing_cost' => 5000000, 'legal_cost' => 2500000, 'registration_charges' => 3000000, 'working_capital' => 5000000, 'other_expenses' => 1200000, 'expected_units' => 80, 'estimated_revenue' => 120000000, 'profit' => 30000000, 'roi_percent' => 30, 'construction_timeline' => '12 Months', 'risk_score' => 22, 'evaluation_notes' => 'Healthy margin with moderate legal risk.', 'status' => 'Recommended'],
            ],
            'negotiation' => [
                ['id' => 1, 'lead_name' => 'Suresh Nair', 'owner_asking_price' => 65000000, 'current_market_price' => 62000000, 'svf_offer_price' => 59000000, 'counter_offer' => 60500000, 'final_agreed_price' => 60000000, 'advance_amount' => 10000000, 'payment_schedule' => '50/50 split', 'negotiation_notes' => 'Owner requested 2 weeks for consent.', 'meeting_history' => 'Meeting 1 on 15 July; Meeting 2 on 22 July', 'negotiation_status' => 'In Progress', 'strategy_recommendation' => 'Edge: show recent local transactions and offer a flexible milestone structure.'],
            ],
            'agreements' => [
                ['id' => 1, 'lead_name' => 'Mahesh Rao', 'agreement_type' => 'Sale Agreement', 'owner_share_percent' => 0, 'builder_share_percent' => 0, 'deposit' => 10000000, 'construction_responsibility' => 'SVF', 'timeline' => '3 months', 'agreement_terms' => 'Full legal closure with registration support.', 'sale_price' => 40000000, 'advance' => 10000000, 'registration_date' => '2026-09-15', 'balance_payment' => 30000000, 'registration_details' => 'Registrar office, Mysuru', 'status' => 'Signed'],
            ],
            'approvals' => [
                ['id' => 1, 'lead_name' => 'Mahesh Rao', 'approval_stage' => 'Director', 'approval_status' => 'Approved', 'comments' => 'Proceed to final conversion', 'email_notifications' => 'Enabled', 'system_notifications' => 'Enabled', 'approval_history' => 'Land Executive > Land Manager > Legal Team > Director', 'digital_approval_status' => 'Signed'],
            ],
            'payments' => [
                ['id' => 1, 'lead_name' => 'Mahesh Rao', 'payment_type' => 'Registration Payment', 'paid_amount' => 10000000, 'pending_amount' => 20000000, 'due_amount' => 20000000, 'due_date' => '2026-08-20', 'payment_history' => 'Token advance paid; registration pending', 'payment_status' => 'Pending', 'notes' => 'Reminder due in 7 days.'],
            ],
            default => [],
        };
    }

    private function filterRecords(array $records): array
    {
        $query = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        return array_values(array_filter($records, static function (array $record) use ($query, $status): bool {
            if ($query !== '') {
                $combined = implode(' ', array_map(static fn($value): string => (string)$value, $record));
                if (!str_contains(strtolower($combined), strtolower($query))) {
                    return false;
                }
            }

            if ($status !== '') {
                return (($record['status'] ?? '') === $status) || (($record['verification_status'] ?? '') === $status) || (($record['negotiation_status'] ?? '') === $status) || (($record['payment_status'] ?? '') === $status) || (($record['approval_status'] ?? '') === $status);
            }

            return true;
        }));
    }

    private function buildSummaryCards(string $section, array $records): array
    {
        return match ($section) {
            'requirements' => [
                ['label' => 'Open Requirements', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Open')), 'icon' => 'fa-clipboard-list'],
                ['label' => 'High Priority', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['priority'] ?? '') === 'High')), 'icon' => 'fa-arrow-up'],
                ['label' => 'Shortlisted', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Shortlisted')), 'icon' => 'fa-magnifying-glass'],
            ],
            'leads' => [
                ['label' => 'New Leads', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'New Lead')), 'icon' => 'fa-star'],
                ['label' => 'Contacted', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Contacted')), 'icon' => 'fa-phone'],
                ['label' => 'Approved', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Approved')), 'icon' => 'fa-thumbs-up'],
            ],
            'owners' => [
                ['label' => 'Verified Owners', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Verified')), 'icon' => 'fa-shield'],
                ['label' => 'Pending Owners', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Pending')), 'icon' => 'fa-clock'],
            ],
            'site_visits' => [
                ['label' => 'Scheduled', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Scheduled')), 'icon' => 'fa-calendar-day'],
                ['label' => 'Completed', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Completed')), 'icon' => 'fa-circle-check'],
            ],
            'document_verification' => [
                ['label' => 'Verified', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['verification_status'] ?? '') === 'Verified')), 'icon' => 'fa-check-double'],
                ['label' => 'Risk', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['verification_status'] ?? '') === 'Risk')), 'icon' => 'fa-triangle-exclamation'],
            ],
            'land_evaluation' => [
                ['label' => 'Recommended', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Recommended')), 'icon' => 'fa-chart-line'],
                ['label' => 'Avg ROI', 'value' => number_format(($this->averageValue($records, 'roi_percent')), 1) . '%', 'icon' => 'fa-percent'],
            ],
            'negotiation' => [
                ['label' => 'Open', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['negotiation_status'] ?? '') === 'Open')), 'icon' => 'fa-handshake'],
                ['label' => 'Closed', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['negotiation_status'] ?? '') === 'Closed')), 'icon' => 'fa-lock'],
            ],
            'agreements' => [
                ['label' => 'Signed', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Signed')), 'icon' => 'fa-file-signature'],
                ['label' => 'Draft', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === 'Draft')), 'icon' => 'fa-pen-nib'],
            ],
            'approvals' => [
                ['label' => 'Pending', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['approval_status'] ?? '') === 'Pending')), 'icon' => 'fa-hourglass-half'],
                ['label' => 'Approved', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['approval_status'] ?? '') === 'Approved')), 'icon' => 'fa-check-to-slot'],
            ],
            'payments' => [
                ['label' => 'Pending', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['payment_status'] ?? '') === 'Pending')), 'icon' => 'fa-wallet'],
                ['label' => 'Overdue', 'value' => count(array_filter($records, static fn(array $row): bool => ($row['payment_status'] ?? '') === 'Overdue')), 'icon' => 'fa-triangle-exclamation'],
            ],
            default => [],
        };
    }

    private function buildLeadPipeline(array $records): array
    {
        $statuses = ['New Lead', 'Contacted', 'Site Visit Planned', 'Document Verification', 'Negotiation', 'Approved', 'Rejected'];
        $stages = [];

        foreach ($statuses as $status) {
            $items = array_values(array_filter($records, static fn(array $row): bool => ($row['status'] ?? '') === $status));
            $stages[] = ['status' => $status, 'count' => count($items), 'items' => $items];
        }

        return $stages;
    }

    private function handleUpload(string $name, string $existing = ''): string
    {
        if (!isset($_FILES[$name]) || !is_array($_FILES[$name])) {
            return $existing;
        }

        $file = $_FILES[$name];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $existing;
        }

        return upload_file($file, 'land-acquisition/' . date('Y/m'), ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);
    }

    private function normalizeValue(array $field, mixed $value): mixed
    {
        if ($value === null) {
            return '';
        }

        $type = $field['type'] ?? 'text';
        if ($type === 'number') {
            return is_numeric((string)$value) ? (float)$value : 0;
        }

        return (string)$value;
    }

    private function findRecord(array $records, int $id): ?array
    {
        foreach ($records as $record) {
            if ((int)($record['id'] ?? 0) === $id) {
                return $record;
            }
        }

        return null;
    }

    private function findIndex(array $records, int $id): ?int
    {
        foreach ($records as $index => $record) {
            if ((int)($record['id'] ?? 0) === $id) {
                return $index;
            }
        }

        return null;
    }

    private function nextId(array $records): int
    {
        $max = 0;
        foreach ($records as $record) {
            $max = max($max, (int)($record['id'] ?? 0));
        }

        return $max + 1;
    }

    private function tableForSection(string $section): ?string
    {
        return match ($section) {
            'requirements' => 'land_requirements',
            'leads' => 'land_leads',
            'owners' => 'land_owners',
            'site_visits' => 'land_site_visits',
            'document_verification' => 'land_document_verifications',
            'land_evaluation' => 'land_evaluations',
            'negotiation' => 'land_negotiations',
            'agreements' => 'land_agreements',
            'approvals' => 'land_approvals',
            'payments' => 'land_payments',
            default => null,
        };
    }

    private function normalizeRecordForTable(string $section, array $record): array
    {
        $data = $record;
        unset($data['id']);
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    private function averageValue(array $records, string $field): float
    {
        $values = array_values(array_filter(array_map(static fn(array $row): float => (float)($row[$field] ?? 0), $records)));
        if ($values === []) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }

    private function sumNumeric(array $records, string $field): float
    {
        $sum = 0.0;
        foreach ($records as $record) {
            $sum += (float)($record[$field] ?? 0);
        }

        return $sum;
    }
}
