<?php

namespace Database\Seeders;

use App\Models\Department;
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

        $adminRole = Role::where('slug', 'system-administrator')->first();
        $ict = Department::where('code', 'ICT')->first();

        User::factory()->create([
            'name' => 'Prototype Administrator',
            'email' => 'admin@example.com',
            'role_id' => $adminRole?->id,
            'department_id' => $ict?->id,
            'job_title' => 'System Administrator',
            'is_active' => true,
        ]);
    }
}
