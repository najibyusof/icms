# Programme Management Module - Complete Documentation

## Overview

The **Programme Management Module** provides a comprehensive system for managing academic programmes, learning outcomes, study plans, and CLO-PLO mappings. It features a modern multi-tab interface, full workflow support, and integration with the existing ICMS infrastructure.

## Module Structure

```
Modules/Programme/
├── Models/
│   ├── Programme.php                    (Enhanced with new relationships)
│   ├── ProgrammePLO.php                 (Programme Learning Outcomes)
│   ├── ProgrammePEO.php                 (Programme Educational Objectives)
│   ├── StudyPlan.php                    (Study plans with semester structure)
│   ├── StudyPlanCourse.php              (Courses in study plans)
│   ├── ProgrammeCourse.php              (Programme-to-course mappings)
│   └── CLOPLOMapping.php                (CLO → PLO alignment)
├── Http/
│   ├── Controllers/
│   │   └── ProgrammeController.php      (All endpoints)
│   └── Requests/
│       ├── StoreProgrammeRequest.php
│       ├── UpdateProgrammeRequest.php
│       ├── StoreProgrammePLORequest.php
│       ├── StoreProgrammePEORequest.php
│       ├── StoreStudyPlanRequest.php
│       └── StoreCLOPLOMappingRequest.php
├── Services/
│   ├── ProgrammeService.php             (30+ business logic methods)
│   └── MappingService.php               (CLO-PLO operations)
├── Routes/
│   └── web.php                          (All RESTful routes)
└── Resources/
    └── views/
        ├── index.blade.php              (Programme list)
        ├── create.blade.php             (Create form)
        ├── edit.blade.php               (Edit form)
        ├── show.blade.php               (7-tab detail view)
        └── partials/tabs/
            ├── main-info.blade.php
            ├── programme-info.blade.php
            ├── peo.blade.php
            ├── plo.blade.php
            ├── courses.blade.php
            ├── study-plan.blade.php
            └── mapping.blade.php
```

## Database Schema

### Key Tables Created (Migration: 2026_04_11_000000)

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| `programmes` | Main programme records | code, name, level, duration_semesters, status, programme_chair_id |
| `programme_plos` | Programme Learning Outcomes | code, description, sequence_order |
| `programme_peos` | Programme Educational Objectives | code, description, sequence_order |
| `programme_courses` | Programme-course mappings | year, semester, is_mandatory |
| `study_plans` | Study plan definitions | name, total_years, semesters_per_year |
| `study_plan_courses` | Courses in study plans | year, semester, is_mandatory |
| `clo_plo_mappings` | CLO-PLO alignments | clo_code, bloom_level (1-6), alignment_notes |

### Programme Statuses
- `draft` - Not yet submitted
- `submitted` - Awaiting review
- `in_review` - Currently being reviewed
- `approved` - Approved and active
- `rejected` - Returned for revision

## Features

### ✅ Core CRUD Operations
- Create, read, update, delete programmes
- Manage PLOs (e.g., "Apply programming skills")
- Manage PEOs (e.g., "Pursue professional IT careers")
- Create and manage multiple study plans per programme

### ✅ Study Plans with Year & Semester Support
```php
$studyPlan = StudyPlan::create([
    'programme_id' => 1,
    'name' => 'Standard 4-Year Plan',
    'total_years' => 4,
    'semesters_per_year' => 2,
]);

// Add courses to specific semesters
$studyPlan->courses()->create([
    'course_id' => 5,
    'year' => 1,
    'semester' => 1,
    'is_mandatory' => true,
]);
```

### ✅ CLO-PLO Mapping with Bloom's Taxonomy
Map Course Learning Outcomes to Programme Learning Outcomes with 6 levels:
1. **Remember** - Recall facts and basic concepts
2. **Understand** - Explain ideas or concepts
3. **Apply** - Use information in new situations
4. **Analyze** - Draw connections among ideas
5. **Evaluate** - Justify decision or choices
6. **Create** - Produce new or original work

```php
CLOPLOMapping::create([
    'course_id' => 10,
    'programme_plo_id' => 3,
    'clo_code' => 'CLO-2.1',
    'bloom_level' => 3, // Apply level
    'alignment_notes' => 'Students apply OOP concepts...',
]);
```

### ✅ Programme Chair Assignment
```php
$programme->assign ProgrammeChair($user);
// Or via API:
POST /programmes/{programme}/assign-chair/{userId}
```

### ✅ Approval Workflow
```php
// Submit for approval
POST /programmes/{programme}/submit-for-approval

// Updates status from 'draft' to 'submitted'
// Prevents further edits until reviewed
```

### ✅ Reporting & Analytics
```php
// CLO Coverage percentage
$coverage = $mappingService->getCLOCoveragePercentage($programme);

// Bloom level distribution
$distribution = $mappingService->getBloomLevelDistribution($course);

// PLO achievement summary
$summary = $mappingService->getPLOAchievementSummary($plo);

// Mapping matrix (PLO × Course grid)
$matrix = $mappingService->getMappingMatrix($programme);
```

## API Endpoints

### Main (POST = create, GET = list, PUT = update, DELETE = remove)

```
GET|HEAD    /programmes                                    # List all
POST        /programmes                                    # Create
GET         /programmes/api/list                          # List (JSON)
GET         /programmes/create                            # Create form
GET|HEAD    /programmes/{programme}                       # View
PUT         /programmes/{programme}                       # Update
DELETE      /programmes/{programme}                       # Delete
GET         /programmes/{programme}/edit                  # Edit form
```

### PLOs (Programme Learning Outcomes)
```
POST        /programmes/{programme}/plos                  # Create
PUT         /programmes/plos/{plo}                        # Update
DELETE      /programmes/plos/{plo}                        # Delete
```

### PEOs (Programme Educational Objectives)
```
POST        /programmes/{programme}/peos                  # Create
PUT         /programmes/peos/{peo}                        # Update
DELETE      /programmes/peos/{peo}                        # Delete
```

### Study Plans
```
POST        /programmes/{programme}/study-plans           # Create
PUT         /programmes/study-plans/{studyPlan}          # Update
DELETE      /programmes/study-plans/{studyPlan}          # Delete
GET         /programmes/study-plans/{studyPlan}/courses  # Get courses by semester
```

### CLO-PLO Mappings
```
POST        /programmes/mappings                          # Create mapping
GET         /programmes/{programme}/mappings/courses/{courseId}
DELETE      /programmes/mappings/{mapping}               # Delete mapping
GET         /programmes/{programme}/mappings/matrix       # Get mapping matrix
GET         /programmes/{programme}/mappings/coverage     # Get coverage report
```

### Chair & Workflow
```
POST        /programmes/{programme}/assign-chair/{userId} # Assign chair
POST        /programmes/{programme}/submit-for-approval    # Submit for approval
```

## Multi-Tab User Interface

The `show` view (programme detail) features 7 interactive tabs:

### 1. **Main Info Tab**
- Basic programme details (code, name, level, duration)
- Quick statistics dashboard
- Status badge and chair information

### 2. **Programme Info Tab**
- Detailed description and requirements
- Accreditation body
- Links to related objects (courses, PLOs, etc.)

### 3. **PEO Tab** (Programme Educational Objectives)
- List of PEOs with inline add/edit/delete modals
- Sequence ordering
- Professional outcome statements

### 4. **PLO Tab** (Programme Learning Outcomes)
- List of PLOs with inline management
- Sequence ordering
- Learning outcome descriptions

### 5. **Courses Tab**
- All courses assigned to the programme
- Credit hours display
- Links to view course-specific mappings

### 6. **Study Plan Tab**
- Create and manage multiple study plans
- Defined years and semesters per year
- View courses for each plan
- Card-based interface for easy scanning

### 7. **CLO-PLO Mapping Tab**
- Interactive mapping table
- Course ↔ CLO ↔ PLO ↔ Bloom Level
- Inline modals for creating/deleting mappings
- Coverage visualization

## Service Methods

### ProgrammeService (30+ methods)

```php
// CRUD
$programme = $service->create($data);
$programme = $service->update($programme, $data);
$service->delete($programme);
$programme = $service->getWithDetails($programme);

// PLO Management
$plo = $service->createPLO($programme, $data);
$service->updatePLO($plo, $data);
$service->deletePLO($plo);

// PEO Management
$peo = $service->createPEO($programme, $data);
$service->updatePEO($peo, $data);
$service->deletePEO($peo);

// Study Plans
$plan = $service->createStudyPlan($programme, $data);
$service->updateStudyPlan($plan, $data);
$service->deleteStudyPlan($plan);
$grouped = $service->getStudyPlanCoursesBySemester($plan);

// Program Chair
$service->assignProgrammeChair($programme, $user);
$chairs = $service->getAvailableProgrammeChairs();

// Workflow
$service->submitForApproval($programme);

// Reports
$stats = $service->getProgrammeStats($programme);
```

### MappingService

```php
// Create/Update CLO-PLO mapping
$mapping = $service->createOrUpdateMapping($data);

// Get mappings
$mappings = $service->getMappingsByCourse($course);
$mappings = $service->getMappingsByProgramme($programme);

// Reports
$matrix = $service->getMappingMatrix($programme);           // PLO × Course grid
$coverage = $service->getCLOCoveragePercentage($programme); // 0-100%
$distribution = $service->getBloomLevelDistribution($course);
$summary = $service->getPLOAchievementSummary($plo);

// Delete mapping
$service->deleteMapping($mapping);
```

## Usage Examples

### Example 1: Create Programme with PLOs
```php
use Modules\Programme\Services\ProgrammeService;

$service = app(ProgrammeService::class);

// Create programme
$programme = $service->create([
    'code' => 'CS-UG',
    'name' => 'Bachelor of Computer Science',
    'level' => 'Bachelor',
    'duration_semesters' => 8,
]);

// Add PLOs
$service->createPLO($programme, [
    'code' => 'PLO-1',
    'description' => 'Apply software engineering practices',
    'sequence_order' => 1,
]);
```

### Example 2: Create Study Plan with Courses
```php
$studyPlan = $service->createStudyPlan($programme, [
    'name' => 'Standard Plan',
    'total_years' => 4,
    'semesters_per_year' => 2,
    'courses' => [
        ['course_id' => 1, 'year' => 1, 'semester' => 1, 'is_mandatory' => true],
        ['course_id' => 2, 'year' => 1, 'semester' => 1, 'is_mandatory' => true],
    ],
]);
```

### Example 3: Map CLO to PLO
```php
use Modules\Programme\Services\MappingService;

$mappingService = app(MappingService::class);

$mapping = $mappingService->createOrUpdateMapping([
    'course_id' => 10,
    'programme_plo_id' => 3,
    'clo_code' => 'CLO-2.1',
    'bloom_level' => 3, // Apply
    'alignment_notes' => 'Students demonstrate application...',
]);
```

### Example 4: Generate Coverage Report
```php
$coverage = $mappingService->getCLOCoveragePercentage($programme);
// Returns: 75.5 (percentage of CLOs mapped to PLOs)

$matrix = $mappingService->getMappingMatrix($programme);
// Returns: {
//   courses: [...],
//   plos: [...],
//   matrix: {
//     course_1: { plo_1: {has_mapping: true, bloom_level: 3}, ... },
//     ...
//   }
// }
```

## Authorization Policies

The module uses Laravel's authorization policies with permission checks:

```php
can('programme.create')    // Create new programme
can('programme.edit')      // Edit programme
can('programme.delete')    // Delete programme
can('update', $programme)  // Update specific programme
can('delete', $programme)  // Delete specific programme
```

Users can perform operations based on their roles and permissions.

## Testing

### Seeder
Run the test seeder to populate sample data:
```bash
php artisan db:seed --class=ProgrammeManagementSeeder
```

### Manual Testing Checklist
- [ ] Create a new programme
- [ ] Add PLOs and PEOs
- [ ] Create a study plan with year/semester structure
- [ ] Add courses to study plan
- [ ] Create CLO-PLO mappings
- [ ] View mapping matrix
- [ ] Check coverage report
- [ ] Assign programme chair
- [ ] Submit for approval
- [ ] Edit programme (should fail if approved)
- [ ] Delete programme

## Modern UI Features

✨ **Responsive Design**
- Mobile-friendly Bootstrap 5 interface
- Collapsible mobile navigation
- Touch-friendly buttons and modals

✨ **Interactive Elements**
- Inline add/edit/delete with modals
- Tab-based navigation for complex data
- Live form validation
- Badge-based status visualization

✨ **Data Presentation**
- Summary statistics dashboard
- Color-coded status badges
- Grouped/sorted tables
- Card-based layouts for visual scanning

✨ **User Experience**
- Breadcrumb navigation
- Contextual help text
- Confirmation dialogs for destructive actions
- Success/error notifications

## Performance Considerations

- Database queries are optimized with eager loading (`.with()`)
- Caching support for expensive reports (via `DashboardService`)
- Indexed foreign keys for fast lookups
- Pagination-ready route structure

## Future Enhancements

- [ ] CLO similarity detection and deduplication
- [ ] Department/Faculty hierarchy support
- [ ] Automatic report generation (PDF/Excel)
- [ ] Integration with course evaluation results
- [ ] Student outcome tracking and analytics
- [ ] Version history and audit trails
- [ ] Bulk import of PLOs/PEOs from templates

## Support

For issues or questions about the Programme Management module, refer to:
1. [Laravel Documentation](https://laravel.com/docs)
2. [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
3. [Bootstrap 5 Components](https://getbootstrap.com/docs/5.0/components)

---

**Created**: 2026-04-11  
**Last Updated**: 2026-04-11  
**Status**: Production Ready ✅
