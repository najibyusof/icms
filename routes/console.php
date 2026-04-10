<?php

use App\Support\CanonicalRoleName;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! function_exists('icmsDemoAccountRoles')) {
    /**
     * @return array<int, string>
     */
    function icmsDemoAccountRoles(): array
    {
        return CanonicalRoleName::all();
    }
}

if (! function_exists('icmsDemoAccountsGrouped')) {
    function icmsDemoAccountsGrouped()
    {
        return DB::table('users')
            ->join('model_has_roles', function ($join): void {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', icmsDemoAccountRoles())
            ->select(
                'roles.name as role_name',
                'users.name',
                'users.email',
                'users.staff_id',
                'users.faculty'
            )
            ->orderByRaw("case roles.name
                when 'Admin' then 1
                when 'Programme Coordinator' then 2
                when 'Lecturer' then 3
                when 'Reviewer' then 4
                when 'Approver' then 5
                else 99 end")
            ->orderBy('users.name')
            ->get()
            ->groupBy('role_name');
    }
}

if (! function_exists('icmsCanonicalRoleName')) {
    function icmsCanonicalRoleName(string $roleName): string
    {
        return CanonicalRoleName::normalize($roleName);
    }
}

if (! function_exists('icmsSeedSummaryData')) {
    /**
     * @return array<string, array<int, array<string, string|int>>>
     */
    function icmsSeedSummaryData(): array
    {
        $roleRows = [];
        if (Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
            $roleRows = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->select('users.id as user_id', 'roles.name as role_name')
                ->get()
                ->map(fn ($row): array => [
                    'user_id' => (int) $row->user_id,
                    'role_name' => icmsCanonicalRoleName((string) $row->role_name),
                ])
                ->unique(fn (array $row): string => $row['role_name'] . ':' . $row['user_id'])
                ->groupBy('role_name')
                ->map(fn ($rows, string $roleName): array => [
                    'Role' => $roleName,
                    'Users' => $rows->count(),
                ])
                ->sortBy(fn (array $row): int => CanonicalRoleName::sortOrder($row['Role']))
                ->values()
                ->all();
        }

        $courseRows = [];
        if (Schema::hasTable('courses')) {
            $courseRows = DB::table('courses')
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->orderBy('status')
                ->get()
                ->map(fn ($row): array => [
                    'Status' => (string) $row->status,
                    'Count' => (int) $row->total,
                ])
                ->all();
        }

        $examRows = [];
        if (Schema::hasTable('examinations')) {
            $examRows = DB::table('examinations')
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->orderBy('status')
                ->get()
                ->map(fn ($row): array => [
                    'Status' => (string) $row->status,
                    'Count' => (int) $row->total,
                ])
                ->all();
        }

        $workflowRows = [];
        if (Schema::hasTable('workflow_instances')) {
            $workflowRows = DB::table('workflow_instances')
                ->select('workflowable_type', 'status', DB::raw('count(*) as total'))
                ->groupBy('workflowable_type', 'status')
                ->orderBy('workflowable_type')
                ->orderBy('status')
                ->get()
                ->map(function ($row): array {
                    $entity = str_contains((string) $row->workflowable_type, 'Course')
                        ? 'Course'
                        : (str_contains((string) $row->workflowable_type, 'Examination') ? 'Examination' : (string) $row->workflowable_type);

                    return [
                        'Entity' => $entity,
                        'Status' => (string) $row->status,
                        'Count' => (int) $row->total,
                    ];
                })
                ->all();
        }

        return [
            'users_by_role' => $roleRows,
            'courses_by_status' => $courseRows,
            'examinations_by_status' => $examRows,
            'workflow_by_entity' => $workflowRows,
        ];
    }
}

if (! function_exists('icmsCsvLine')) {
    /**
     * @param array<int, string> $fields
     */
    function icmsCsvLine(array $fields): string
    {
        return implode(',', array_map(function (string $value): string {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $fields));
    }
}

if (! function_exists('icmsWriteExportOutput')) {
    function icmsWriteExportOutput(string $content, ?string $path): ?string
    {
        $path = $path !== null ? trim($path) : null;
        if ($path === null || $path === '') {
            return null;
        }

        $fullPath = str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path) === 1
            ? $path
            : base_path($path);

        $directory = dirname($fullPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($fullPath, $content);

        return $fullPath;
    }
}

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('icms:seed-summary', function () {
    if (! Schema::hasTable('users')) {
        $this->error('Missing users table. Run migrations first.');

        return self::FAILURE;
    }

    $this->info('ICMS Seed Scenario Summary');
    $this->line('');

    $summary = icmsSeedSummaryData();
    $roleRows = $summary['users_by_role'];

    $this->line('Users by role');
    if ($roleRows === []) {
        $this->warn('No role assignment data found.');
    } else {
        $this->table(['Role', 'Users'], $roleRows);
    }

    if (Schema::hasTable('courses')) {
        $courseRows = $summary['courses_by_status'];
        $this->line('Courses by status');
        $this->table(['Status', 'Count'], $courseRows);
    } else {
        $this->warn('courses table not found.');
    }

    if (Schema::hasTable('examinations')) {
        $examRows = $summary['examinations_by_status'];
        $this->line('Examinations by status');
        $this->table(['Status', 'Count'], $examRows);
    } else {
        $this->warn('examinations table not found.');
    }

    if (Schema::hasTable('workflow_instances')) {
        $workflowRows = $summary['workflow_by_entity'];
        $this->line('Workflow instances by entity and status');
        $this->table(['Entity', 'Status', 'Count'], $workflowRows);
    } else {
        $this->warn('workflow_instances table not found.');
    }

    $this->line('');
    $this->info('Use this summary after "php artisan db:seed" to verify scenario coverage quickly.');

    return self::SUCCESS;
})->purpose('Show a quick summary of seeded ICMS scenario data.');

Artisan::command('icms:demo-accounts', function () {
    if (! Schema::hasTable('users')) {
        $this->error('Missing users table. Run migrations first.');

        return self::FAILURE;
    }

    if (! Schema::hasTable('model_has_roles') || ! Schema::hasTable('roles')) {
        $this->error('Missing role assignment tables. Seed RBAC data first.');

        return self::FAILURE;
    }

    $canonicalRoles = icmsDemoAccountRoles();
    $accounts = icmsDemoAccountsGrouped();

    $this->info('ICMS Demo Login Accounts');
    $this->line('Shared password for seeded demo users: password');
    $this->line('');

    foreach ($canonicalRoles as $roleName) {
        $rows = ($accounts->get($roleName) ?? collect())
            ->map(fn ($account): array => [
                'Name' => (string) $account->name,
                'Email' => (string) $account->email,
                'Staff ID' => (string) ($account->staff_id ?? '-'),
                'Faculty' => (string) ($account->faculty ?? '-'),
            ])
            ->values()
            ->all();

        if ($rows === []) {
            continue;
        }

        $this->line($roleName);
        $this->table(['Name', 'Email', 'Staff ID', 'Faculty'], $rows);
    }

    $this->info('Use Admin for full access, Lecturer for submission flow, Reviewer/Approver for workflow demos.');

    return self::SUCCESS;
})->purpose('Print seeded demo login accounts grouped by canonical role.');

Artisan::command('icms:demo-accounts-export {format=markdown} {--path=}', function (string $format) {
    if (! Schema::hasTable('users')) {
        $this->error('Missing users table. Run migrations first.');

        return self::FAILURE;
    }

    if (! Schema::hasTable('model_has_roles') || ! Schema::hasTable('roles')) {
        $this->error('Missing role assignment tables. Seed RBAC data first.');

        return self::FAILURE;
    }

    $format = strtolower(trim($format));
    if (! in_array($format, ['markdown', 'csv'], true)) {
        $this->error('Unsupported format. Use "markdown" or "csv".');

        return self::FAILURE;
    }

    $accounts = icmsDemoAccountsGrouped();
    $canonicalRoles = icmsDemoAccountRoles();
    $lines = [];

    if ($format === 'csv') {
        $lines[] = 'Role,Name,Email,Staff ID,Faculty,Password';

        foreach ($canonicalRoles as $roleName) {
            foreach (($accounts->get($roleName) ?? collect()) as $account) {
                $fields = [
                    $roleName,
                    (string) $account->name,
                    (string) $account->email,
                    (string) ($account->staff_id ?? '-'),
                    (string) ($account->faculty ?? '-'),
                    'password',
                ];

                $lines[] = icmsCsvLine($fields);
            }
        }

        $output = implode(PHP_EOL, $lines) . PHP_EOL;
        $savedPath = icmsWriteExportOutput($output, $this->option('path'));
        if ($savedPath !== null) {
            $this->info('Export written to: ' . $savedPath);
        } else {
            $this->line(rtrim($output));
        }

        return self::SUCCESS;
    }

    $lines[] = '# ICMS Demo Login Accounts';
    $lines[] = '';
    $lines[] = 'Shared password for seeded demo users: `password`';
    $lines[] = '';

    foreach ($canonicalRoles as $roleName) {
        $rows = ($accounts->get($roleName) ?? collect())->values();
        if ($rows->isEmpty()) {
            continue;
        }

        $lines[] = '## ' . $roleName;
        $lines[] = '';
        $lines[] = '| Name | Email | Staff ID | Faculty | Password |';
        $lines[] = '| --- | --- | --- | --- | --- |';

        foreach ($rows as $account) {
            $cells = [
                str_replace('|', '\\|', (string) $account->name),
                str_replace('|', '\\|', (string) $account->email),
                str_replace('|', '\\|', (string) ($account->staff_id ?? '-')),
                str_replace('|', '\\|', (string) ($account->faculty ?? '-')),
                'password',
            ];

            $lines[] = '| ' . implode(' | ', $cells) . ' |';
        }

        $lines[] = '';
    }

    $output = implode(PHP_EOL, $lines) . PHP_EOL;
    $savedPath = icmsWriteExportOutput($output, $this->option('path'));
    if ($savedPath !== null) {
        $this->info('Export written to: ' . $savedPath);
    } else {
        $this->line(rtrim($output));
    }

    return self::SUCCESS;
})->purpose('Export seeded demo login accounts as markdown or CSV for stakeholder handover.');

Artisan::command('icms:seed-summary-export {format=markdown} {--path=}', function (string $format) {
    if (! Schema::hasTable('users')) {
        $this->error('Missing users table. Run migrations first.');

        return self::FAILURE;
    }

    $format = strtolower(trim($format));
    if (! in_array($format, ['markdown', 'csv'], true)) {
        $this->error('Unsupported format. Use "markdown" or "csv".');

        return self::FAILURE;
    }

    $summary = icmsSeedSummaryData();

    if ($format === 'csv') {
        $lines = ['Section,Column 1,Column 2,Value'];

        foreach ($summary['users_by_role'] as $row) {
            $lines[] = icmsCsvLine(['Users by role', 'Role', (string) $row['Role'], (string) $row['Users']]);
        }

        foreach ($summary['courses_by_status'] as $row) {
            $lines[] = icmsCsvLine(['Courses by status', 'Status', (string) $row['Status'], (string) $row['Count']]);
        }

        foreach ($summary['examinations_by_status'] as $row) {
            $lines[] = icmsCsvLine(['Examinations by status', 'Status', (string) $row['Status'], (string) $row['Count']]);
        }

        foreach ($summary['workflow_by_entity'] as $row) {
            $lines[] = icmsCsvLine(['Workflow instances', (string) $row['Entity'], (string) $row['Status'], (string) $row['Count']]);
        }

        $output = implode(PHP_EOL, $lines) . PHP_EOL;
        $savedPath = icmsWriteExportOutput($output, $this->option('path'));
        if ($savedPath !== null) {
            $this->info('Export written to: ' . $savedPath);
        } else {
            $this->line(rtrim($output));
        }

        return self::SUCCESS;
    }

    $lines = [
        '# ICMS Seed Scenario Summary',
        '',
    ];

    $sections = [
        'Users by role' => $summary['users_by_role'],
        'Courses by status' => $summary['courses_by_status'],
        'Examinations by status' => $summary['examinations_by_status'],
        'Workflow instances by entity and status' => $summary['workflow_by_entity'],
    ];

    foreach ($sections as $title => $rows) {
        if ($rows === []) {
            continue;
        }

        $headers = array_keys($rows[0]);
        $lines[] = '## ' . $title;
        $lines[] = '';
        $lines[] = '| ' . implode(' | ', $headers) . ' |';
        $lines[] = '| ' . implode(' | ', array_fill(0, count($headers), '---')) . ' |';

        foreach ($rows as $row) {
            $cells = array_map(function ($value): string {
                return str_replace('|', '\\|', (string) $value);
            }, array_values($row));

            $lines[] = '| ' . implode(' | ', $cells) . ' |';
        }

        $lines[] = '';
    }

    $output = implode(PHP_EOL, $lines) . PHP_EOL;
    $savedPath = icmsWriteExportOutput($output, $this->option('path'));
    if ($savedPath !== null) {
        $this->info('Export written to: ' . $savedPath);
    } else {
        $this->line(rtrim($output));
    }

    return self::SUCCESS;
})->purpose('Export the seeded ICMS scenario summary as markdown or CSV.');
