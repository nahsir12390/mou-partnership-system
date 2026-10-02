<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Partner;
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

        foreach ($roles as $role) Role::updateOrCreate(['slug' => $role['slug']], $role + ['is_system' => true]);

        $departments = [
            ['name' => 'Office of the Vice-Chancellor', 'code' => 'OVC'],
            ['name' => 'Legal Services', 'code' => 'LEGAL'],
            ['name' => 'Research & Partnerships', 'code' => 'RAP'],
            ['name' => 'ICT Directorate', 'code' => 'ICT'],
        ];
        foreach ($departments as $department) Department::updateOrCreate(['code' => $department['code']], $department);

        $adminRole = Role::where('slug', 'system-administrator')->first();
        $ict = Department::where('code', 'ICT')->first();
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Prototype Administrator', 'password' => 'password', 'email_verified_at' => now(),
            'role_id' => $adminRole?->id, 'department_id' => $ict?->id, 'job_title' => 'System Administrator', 'is_active' => true,
        ]);

        $partners = [
            ['name'=>'Global Research Institute','category'=>'Research Institution','country'=>'Nigeria','state'=>'Federal Capital Territory','city'=>'Abuja','contact_name'=>'Dr. Amina Bello','contact_email'=>'amina@example.org','contact_phone'=>'+234 800 000 1001','status'=>'active','notes'=>'Prototype record for research collaboration demonstrations.'],
            ['name'=>'West Africa Academic Network','category'=>'Academic Institution','country'=>'Ghana','city'=>'Accra','contact_name'=>'Kwame Mensah','contact_email'=>'kwame@example.org','contact_phone'=>'+233 20 000 1002','status'=>'active','notes'=>'Prototype record for academic exchange demonstrations.'],
            ['name'=>'Digital Skills Foundation','category'=>'NGO / Foundation','country'=>'Nigeria','state'=>'Lagos','city'=>'Lagos','contact_name'=>'Ifeoma Okeke','contact_email'=>'ifeoma@example.org','contact_phone'=>'+234 800 000 1003','status'=>'active','notes'=>'Prototype record for ICT capacity development demonstrations.'],
            ['name'=>'Enterprise Development Group','category'=>'Private Sector','country'=>'Nigeria','state'=>'Nasarawa','city'=>'Keffi','contact_name'=>'Musa Ibrahim','contact_email'=>'musa@example.org','contact_phone'=>'+234 800 000 1004','status'=>'prospective','notes'=>'Prototype record for industry and internship partnership demonstrations.'],
            ['name'=>'Regional Public Policy Centre','category'=>'Government Agency','country'=>'Nigeria','state'=>'Federal Capital Territory','city'=>'Abuja','contact_name'=>'Grace Adeyemi','contact_email'=>'grace@example.org','contact_phone'=>'+234 800 000 1005','status'=>'prospective','notes'=>'Prototype record for policy and institutional cooperation demonstrations.'],
        ];

        foreach ($partners as $partner) {
            Partner::updateOrCreate(['name' => $partner['name']], $partner + ['created_by' => $admin->id]);
        }
    }
}
