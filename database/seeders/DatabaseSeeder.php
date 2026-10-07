<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\ApprovalAction;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\Partner;
use App\Models\RenewalRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            ['name' => 'System Administrator', 'slug' => 'system-administrator', 'description' => 'Full system administration and configuration access.'],
            ['name' => 'Management', 'slug' => 'management', 'description' => 'Executive oversight, approvals and reporting access.'],
            ['name' => 'Legal / Review Officer', 'slug' => 'legal-review-officer', 'description' => 'Reviews agreements and participates in approval workflows.'],
            ['name' => 'Department Officer', 'slug' => 'department-officer', 'description' => 'Manages agreements, obligations and milestones assigned to a unit.'],
            ['name' => 'Read-only User', 'slug' => 'read-only-user', 'description' => 'Can view authorized partnership records without modifying them.'],
        ];
        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role + ['is_system' => true]);
        }

        $departments = [
            ['name' => 'Office of the Vice-Chancellor', 'code' => 'OVC'],
            ['name' => 'Legal Services', 'code' => 'LEGAL'],
            ['name' => 'Research & Partnerships', 'code' => 'RAP'],
            ['name' => 'ICT Directorate', 'code' => 'ICT'],
        ];
        foreach ($departments as $department) {
            Department::updateOrCreate(['code' => $department['code']], $department);
        }

        $roleIds = Role::pluck('id', 'slug');
        $departmentIds = Department::pluck('id', 'code');

        // Deterministic prototype accounts make role-based demonstrations repeatable.
        // Never use these credentials in a production deployment.
        $demoUsers = [
            ['email' => 'admin@example.com', 'name' => 'Prototype Administrator', 'role' => 'system-administrator', 'department' => 'ICT', 'job_title' => 'System Administrator'],
            ['email' => 'management@example.com', 'name' => 'Dr. Aisha Mohammed', 'role' => 'management', 'department' => 'OVC', 'job_title' => 'Director, Institutional Partnerships'],
            ['email' => 'legal@example.com', 'name' => 'Barr. Daniel Okafor', 'role' => 'legal-review-officer', 'department' => 'LEGAL', 'job_title' => 'Legal Review Officer'],
            ['email' => 'officer@example.com', 'name' => 'Fatima Abdullahi', 'role' => 'department-officer', 'department' => 'RAP', 'job_title' => 'Partnership Officer'],
            ['email' => 'viewer@example.com', 'name' => 'Samuel Adeyemi', 'role' => 'read-only-user', 'department' => 'OVC', 'job_title' => 'Monitoring Officer'],
        ];

        foreach ($demoUsers as $demo) {
            User::updateOrCreate(
                ['email' => $demo['email']],
                [
                    'name' => $demo['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'role_id' => $roleIds[$demo['role']] ?? null,
                    'department_id' => $departmentIds[$demo['department']] ?? null,
                    'job_title' => $demo['job_title'],
                    'is_active' => true,
                ]
            );
        }

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $partners = [
            ['name' => 'Global Research Institute', 'category' => 'Research Institution', 'country' => 'Nigeria', 'state' => 'Federal Capital Territory', 'city' => 'Abuja', 'contact_name' => 'Dr. Amina Bello', 'contact_email' => 'amina@example.org', 'contact_phone' => '+234 800 000 1001', 'status' => 'active', 'notes' => 'Prototype record for research collaboration demonstrations.'],
            ['name' => 'West Africa Academic Network', 'category' => 'Academic Institution', 'country' => 'Ghana', 'city' => 'Accra', 'contact_name' => 'Kwame Mensah', 'contact_email' => 'kwame@example.org', 'contact_phone' => '+233 20 000 1002', 'status' => 'active', 'notes' => 'Prototype record for academic exchange demonstrations.'],
            ['name' => 'Digital Skills Foundation', 'category' => 'NGO / Foundation', 'country' => 'Nigeria', 'state' => 'Lagos', 'city' => 'Lagos', 'contact_name' => 'Ifeoma Okeke', 'contact_email' => 'ifeoma@example.org', 'contact_phone' => '+234 800 000 1003', 'status' => 'active', 'notes' => 'Prototype record for ICT capacity development demonstrations.'],
            ['name' => 'Enterprise Development Group', 'category' => 'Private Sector', 'country' => 'Nigeria', 'state' => 'Nasarawa', 'city' => 'Keffi', 'contact_name' => 'Musa Ibrahim', 'contact_email' => 'musa@example.org', 'contact_phone' => '+234 800 000 1004', 'status' => 'prospective', 'notes' => 'Prototype record for industry and internship partnership demonstrations.'],
            ['name' => 'Regional Public Policy Centre', 'category' => 'Government Agency', 'country' => 'Nigeria', 'state' => 'Federal Capital Territory', 'city' => 'Abuja', 'contact_name' => 'Grace Adeyemi', 'contact_email' => 'grace@example.org', 'contact_phone' => '+234 800 000 1005', 'status' => 'prospective', 'notes' => 'Prototype record for policy and institutional cooperation demonstrations.'],
        ];
        foreach ($partners as $partner) {
            Partner::updateOrCreate(['name' => $partner['name']], $partner + ['created_by' => $admin->id]);
        }

        $this->seedDemonstrationPortfolio($admin, $departmentIds);
    }

    private function seedDemonstrationPortfolio(User $admin, $departmentIds): void
    {
        $officer = User::where('email', 'officer@example.com')->firstOrFail();
        $legal = User::where('email', 'legal@example.com')->firstOrFail();
        $management = User::where('email', 'management@example.com')->firstOrFail();
        $partnerIds = Partner::pluck('id', 'name');

        $agreements = [
            [
                'reference_number' => 'NSUK/MOU/2026/001',
                'title' => 'Joint Research and Postgraduate Development Programme',
                'partner' => 'Global Research Institute',
                'department' => 'RAP',
                'agreement_type' => 'MoU',
                'purpose' => 'Establish joint research projects, postgraduate supervision, grant development and academic publication initiatives.',
                'status' => 'active',
                'approval_stage' => 'Active',
                'renewal_status' => 'due_soon',
                'start_date' => today()->subMonths(11),
                'expiry_date' => today()->addDays(45),
            ],
            [
                'reference_number' => 'NSUK/MOU/2026/002',
                'title' => 'West African Academic Staff Exchange',
                'partner' => 'West Africa Academic Network',
                'department' => 'RAP',
                'agreement_type' => 'Academic Exchange Agreement',
                'purpose' => 'Support staff mobility, visiting lectures, collaborative curriculum development and regional academic exchange.',
                'status' => 'submitted',
                'approval_stage' => 'Awaiting legal review',
                'renewal_status' => 'not_due',
                'start_date' => today()->addMonths(2),
                'expiry_date' => today()->addYears(3),
            ],
            [
                'reference_number' => 'NSUK/MOU/2026/003',
                'title' => 'Digital Skills and Employability Initiative',
                'partner' => 'Digital Skills Foundation',
                'department' => 'ICT',
                'agreement_type' => 'Collaboration Agreement',
                'purpose' => 'Provide industry-aligned digital skills training, certifications and employability support for students.',
                'status' => 'under_review',
                'approval_stage' => 'Legal review',
                'renewal_status' => 'not_due',
                'start_date' => today()->addMonth(),
                'expiry_date' => today()->addYears(2),
            ],
            [
                'reference_number' => 'NSUK/MOU/2026/004',
                'title' => 'Public Policy Research and Advisory Partnership',
                'partner' => 'Regional Public Policy Centre',
                'department' => 'OVC',
                'agreement_type' => 'Partnership Agreement',
                'purpose' => 'Create a framework for policy research, public-sector advisory services and evidence-based development programmes.',
                'status' => 'under_review',
                'approval_stage' => 'Management approval',
                'renewal_status' => 'not_due',
                'start_date' => today()->addMonths(2),
                'expiry_date' => today()->addYears(3),
            ],
            [
                'reference_number' => 'NSUK/MOU/2026/005',
                'title' => 'Student Internship and Enterprise Mentorship Scheme',
                'partner' => 'Enterprise Development Group',
                'department' => 'RAP',
                'agreement_type' => 'MoU',
                'purpose' => 'Provide structured internships, enterprise mentorship and workplace learning opportunities for final-year students.',
                'status' => 'approved',
                'approval_stage' => 'Approved',
                'renewal_status' => 'not_due',
                'start_date' => today()->addMonth(),
                'expiry_date' => today()->addYears(2),
            ],
            [
                'reference_number' => 'NSUK/MOU/2026/006',
                'title' => 'Campus Innovation and Entrepreneurship Hub',
                'partner' => 'Enterprise Development Group',
                'department' => 'RAP',
                'agreement_type' => 'MoU',
                'purpose' => 'Proposed partnership for incubation, startup mentoring and commercialization of student innovations.',
                'status' => 'draft',
                'approval_stage' => null,
                'renewal_status' => 'not_due',
                'start_date' => null,
                'expiry_date' => null,
            ],
            [
                'reference_number' => 'NSUK/MOU/2025/014',
                'title' => 'Community Digital Literacy Outreach',
                'partner' => 'Digital Skills Foundation',
                'department' => 'ICT',
                'agreement_type' => 'Service Agreement',
                'purpose' => 'Deliver community digital literacy training through university facilities and student volunteers.',
                'status' => 'renewed',
                'approval_stage' => 'Active — renewed',
                'renewal_status' => 'renewed',
                'start_date' => today()->subYears(2),
                'expiry_date' => today()->addYear(),
            ],
        ];

        foreach ($agreements as $record) {
            Agreement::updateOrCreate(
                ['reference_number' => $record['reference_number']],
                [
                    'title' => $record['title'],
                    'partner_id' => $partnerIds[$record['partner']],
                    'department_id' => $departmentIds[$record['department']],
                    'responsible_officer_id' => $officer->id,
                    'agreement_type' => $record['agreement_type'],
                    'purpose' => $record['purpose'],
                    'status' => $record['status'],
                    'approval_stage' => $record['approval_stage'],
                    'renewal_status' => $record['renewal_status'],
                    'start_date' => $record['start_date'],
                    'expiry_date' => $record['expiry_date'],
                    'notes' => 'Demonstration record prepared for the NSUK partnership management presentation.',
                    'created_by' => $admin->id,
                ],
            );
        }

        $this->seedApprovalHistory($legal, $management);
        $this->seedObligations($admin, $officer, $departmentIds);
        $this->seedRenewalHistory($management);
    }

    private function seedApprovalHistory(User $legal, User $management): void
    {
        $histories = [
            'NSUK/MOU/2026/002' => [
                [$legal, 'submitted', 'draft', 'submitted', 'Submitted by the originating unit for institutional review.'],
            ],
            'NSUK/MOU/2026/003' => [
                [$legal, 'submitted', 'draft', 'submitted', 'Submitted with the draft implementation schedule.'],
                [$legal, 'start_review', 'submitted', 'under_review', 'Legal review commenced.'],
            ],
            'NSUK/MOU/2026/004' => [
                [$legal, 'submitted', 'draft', 'submitted', 'Submitted for institutional consideration.'],
                [$legal, 'start_review', 'submitted', 'under_review', 'Legal review commenced.'],
                [$legal, 'legal_clear', 'under_review', 'under_review', 'Terms reviewed and cleared for management approval.'],
            ],
            'NSUK/MOU/2026/005' => [
                [$legal, 'submitted', 'draft', 'submitted', 'Submitted by Research and Partnerships.'],
                [$legal, 'start_review', 'submitted', 'under_review', 'Legal review commenced.'],
                [$legal, 'legal_clear', 'under_review', 'under_review', 'Cleared and forwarded to management.'],
                [$management, 'approve', 'under_review', 'approved', 'Approved for signature and implementation.'],
            ],
        ];

        foreach ($histories as $reference => $actions) {
            $agreement = Agreement::where('reference_number', $reference)->firstOrFail();
            foreach ($actions as [$user, $action, $from, $to, $comment]) {
                ApprovalAction::updateOrCreate(
                    ['agreement_id' => $agreement->id, 'action' => $action],
                    ['user_id' => $user->id, 'from_status' => $from, 'to_status' => $to, 'comment' => $comment],
                );
            }
        }
    }

    private function seedObligations(User $admin, User $officer, $departmentIds): void
    {
        $agreement = Agreement::where('reference_number', 'NSUK/MOU/2026/001')->firstOrFail();
        $obligations = [
            ['title' => 'Submit joint research concept notes', 'type' => 'deliverable', 'due_date' => today()->subDays(12), 'priority' => 'high', 'status' => 'in_progress', 'progress' => 70, 'description' => 'Each research cluster will submit a fundable interdisciplinary concept note.'],
            ['title' => 'Nominate postgraduate supervision teams', 'type' => 'milestone', 'due_date' => today()->addDays(20), 'priority' => 'medium', 'status' => 'in_progress', 'progress' => 40, 'description' => 'Confirm supervisors and co-supervisors for the first postgraduate cohort.'],
            ['title' => 'Quarterly partnership performance report', 'type' => 'obligation', 'due_date' => today()->addDays(55), 'priority' => 'medium', 'status' => 'not_started', 'progress' => 0, 'description' => 'Prepare the joint quarterly implementation and outcomes report.'],
            ['title' => 'Inception planning workshop', 'type' => 'milestone', 'due_date' => today()->subMonths(8), 'priority' => 'high', 'status' => 'completed', 'progress' => 100, 'description' => 'Hold the programme inception workshop and adopt the implementation plan.', 'completed_at' => today()->subMonths(8), 'completion_notes' => 'Workshop completed with representatives of both institutions.'],
        ];

        foreach ($obligations as $record) {
            Obligation::updateOrCreate(
                ['agreement_id' => $agreement->id, 'title' => $record['title']],
                $record + [
                    'department_id' => $departmentIds['RAP'],
                    'responsible_officer_id' => $officer->id,
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedRenewalHistory(User $management): void
    {
        $agreement = Agreement::where('reference_number', 'NSUK/MOU/2025/014')->firstOrFail();

        RenewalRecord::updateOrCreate(
            ['agreement_id' => $agreement->id, 'decision' => 'renewed', 'decision_date' => today()->subMonths(2)],
            [
                'previous_expiry_date' => today()->subMonth(),
                'new_expiry_date' => today()->addYear(),
                'notes' => 'Renewed following a positive implementation review and approval by management.',
                'recorded_by' => $management->id,
            ],
        );
    }
}
