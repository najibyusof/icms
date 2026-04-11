<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            WorkflowDefinitionSeeder::class,
            ScenarioSeeder::class,
        ]);

        User::query()->firstOrCreate([
            'email' => 'lecturer@academic.local',
        ], [
            'name' => 'Lecturer User',
            'password' => Hash::make('password'),
        ]);
    }
}
