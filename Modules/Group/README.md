# Group Management Module

## Overview

The Group Management module provides comprehensive functionality for managing academic groups/cohorts, including course assignment and member management with role-based access control.

## Features

### Core Features

- **Create & Manage Groups**: Create new academic groups with customizable properties (name, programme, intake year, semester)
- **Course Assignment**: Assign and manage courses for groups with intuitive dual-list selector UI
- **Member Management**: Add/remove group members with role-based assignment (coordinator, assistant, member)
- **Role-Based Permissions**: Complete permission model with global, role-based, and resource-level authorization
- **Group Statistics**: View key metrics (total members, assigned courses, coordinators, etc.)
- **Programme Association**: Link groups to academic programmes

### User Interface

- **Index View**: Paginated list of all groups with quick stats
- **Create/Edit Views**: Bootstrap 5 forms for group creation and modification
- **Show View**: Comprehensive 3-tab interface:
    - **Basic Info Tab**: Group details and member statistics
    - **Courses Tab**: Dual-list selector for course assignment
    - **Members Tab**: Dual-list selector for member management with role assignment
- **Dual List Selector**: Intuitive drag-and-drop and button-based UI for course/member transfers

## Architecture

### Database Schema

#### Groups Table

- `id`: Primary identifier
- `programme_id`: Foreign key to programmes
- `coordinator_id`: Foreign key to users (group coordinator)
- `name`: Group name
- `intake_year`: Year of intake
- `semester`: Semester number
- `is_active`: Status flag
- `timestamps`: Created/updated timestamps

#### Group Users Pivot Table (Many-to-Many)

- `id`: Primary identifier
- `group_id`: Foreign key to groups
- `user_id`: Foreign key to users
- `role`: User role in group (member, assistant, coordinator)
- `timestamps`: Created/updated timestamps
- **Unique Constraint**: (group_id, user_id)

#### Relations

- Groups → Programmes (BelongsTo)
- Groups → Users (BelongsToMany via group_users)
- Groups → Courses (BelongsToMany)
- Groups → Coordinator (BelongsTo User)

### Model Layer

#### AcademicGroup Model

```php
class AcademicGroup extends Model {
    // Relationships
    public function programme() // BelongsTo
    public function courses() // BelongsToMany
    public function users() // BelongsToMany with pivot role
    public function coordinator() // BelongsTo User

    // Helpers
    public function hasMember(User $user): bool
    public function getUserRole(User $user): ?string
    public function getMembersByRole(string $role): Collection
    public function getProgrammes(): Collection
}
```

### Service Layer

#### GroupService

Handles all business logic for group and member operations:

**CRUD Operations**

- `list()`: Get all groups
- `create(array $data)`: Create new group
- `getWithDetails(group)`: Get group with eager-loaded relationships
- `update(group, data)`: Update group details
- `delete(group)`: Soft delete group

**Course Management**

- `updateCourses(group, courseIds)`: Sync courses
- `addCourse(group, course)`: Add single course
- `removeCourse(group, course)`: Remove single course
- `getAvailableCourses(group)`: Get courses not yet assigned
- `getAssignedCourses(group)`: Get assigned courses

**User Management**

- `assignUsers(group, userIds, role)`: Assign users to group
- `removeUser(group, user)`: Remove user from group
- `updateUserRole(group, user, role)`: Change user's role
- `getMembers(group, role)`: Get group members (optionally filtered by role)
- `getAvailableUsers(group)`: Get users not yet in group

**Statistics**

- `getGroupStats(group)`: Get counts (members, courses, by role)
- `getAffiliatedProgrammes(group)`: Get group's programme

### Authorization Layer

#### GroupPolicy

Provides multi-level permission checks:

**Global Permissions** (checked first)

- `'group.view'`: View groups
- `'group.create'`: Create groups
- `'group.edit'`: Edit groups
- `'group.delete'`: Delete groups

**Method-Level Checks** (fallback)

- `viewAny(user)`: Check `group.view` permission
- `view(user, group)`: Check `group.view` permission
- `create(user)`: Check `group.create` permission
- `update(user, group)`: Check `group.edit` permission OR coordinator_id matches OR user is member
- `delete(user, group)`: Check `group.delete` permission OR coordinator_id matches
- `manageMembers(user, group)`: Check `group.edit` permission OR coordinator_id matches
- `manageCourses(user, group)`: Check `group.edit` permission OR coordinator_id matches

All authorization checks use cascading logic: global permission OR role-based OR resource-specific.

### Request Validation Layer

#### Form Requests

- **StoreGroupRequest**: Validate group creation (name, programme_id, intake_year, semester)
- **UpdateGroupRequest**: Validate group updates with authorization
- **UpdateGroupCoursesRequest**: Validate course assignment
- **AssignUsersToGroupRequest**: Validate member assignment with role specification
- **RemoveUserFromGroupRequest**: Validate member removal

### Controller Layer

#### GroupController

30+ endpoints organized into sections:

**View Endpoints** (return Views)

- `index()`: Display group listing
- `create()`: Show create form
- `show()`: Display group detail with tabs
- `edit()`: Show edit form

**CRUD Operations** (return redirects)

- `store()`: Create group
- `update()`: Update group
- `destroy()`: Delete group

**Course Management** (mix of views/JSON)

- `updateCourses()`: Sync courses via AJAX
- `getAvailableCourses()`: Return JSON list
- `getAssignedCourses()`: Return JSON list
- `addCourse()`: Add course
- `removeCourse()`: Remove course

**Member Management** (mix of views/JSON)

- `assignUsers()`: Assign users via AJAX
- `removeUser()`: Remove user via AJAX
- `updateUserRole()`: Update user role via AJAX
- `getAvailableUsers()`: Return JSON list
- `getMembers()`: Return JSON list

All endpoints check authorization via GroupPolicy.

### View Layer

#### Main Views

- **index.blade.php**: Group listing with stats cards and action buttons
- **create.blade.php**: Group creation form (programme, name, coordinator, intake_year, semester)
- **edit.blade.php**: Group edit form (pre-filled with existing data)
- **show.blade.php**: Group detail with 3-tab interface

#### Tab Partials

- **partials/tabs/info.blade.php**: Basic information and statistics display
- **partials/tabs/courses.blade.php**: Dual-list selector for course assignment with drag-and-drop
- **partials/tabs/users.blade.php**: Dual-list selector for member management with role assignment

### Routing

All routes use `auth` middleware and are grouped under `/groups`:

```
GET    /groups              - List groups
POST   /groups              - Create group
GET    /groups/create       - Show create form
GET    /groups/{id}         - Show group detail
GET    /groups/{id}/edit    - Show edit form
PUT    /groups/{id}         - Update group
DELETE /groups/{id}         - Delete group

PUT    /groups/{id}/courses                  - Update courses
GET    /groups/{id}/courses/available        - Get available courses
GET    /groups/{id}/courses/assigned         - Get assigned courses
POST   /groups/{id}/courses/{courseId}       - Add course
DELETE /groups/{id}/courses/{courseId}       - Remove course

POST   /groups/{id}/users                    - Assign users
DELETE /groups/{id}/users                    - Remove user
GET    /groups/{id}/users/available          - Get available users
GET    /groups/{id}/users                    - Get members
PUT    /groups/{id}/users/{userId}/role/{role} - Update user role

GET    /groups/{id}/stats                    - Get statistics
GET    /groups/{id}/programmes               - Get affiliated programme
```

## Usage

### Creating a Group

1. Navigate to `/groups`
2. Click "Create Group"
3. Fill in programme, name, intake year, semester
4. Optionally assign coordinator
5. Click "Create Group"
6. Group will be accessible for course/member assignment

### Assigning Courses

1. Open group detail (show view)
2. Click "Courses" tab
3. Available courses shown on left, assigned on right
4. Drag courses between lists OR click transfer buttons
5. Click "Save Changes"
6. AJAX request updates assignment

### Managing Members

1. Open group detail (show view)
2. Click "Members" tab
3. Available users shown on left, assigned on right
4. Drag users between lists OR click transfer buttons
5. Assign roles via dropdown (member, assistant, coordinator)
6. Click "Save Changes"
7. AJAX request updates membership and roles

### Permissions

#### Grant Global Permissions (via artisan or UI)

```php
// Create permission
Permission::create(['name' => 'group.view']);
Permission::create(['name' => 'group.create']);
Permission::create(['name' => 'group.edit']);
Permission::create(['name' => 'group.delete']);

// Assign to roles
Role::find('admin')->givePermissionTo(['group.view', 'group.create', 'group.edit', 'group.delete']);
Role::find('coordinator')->givePermissionTo(['group.view', 'group.edit']);
```

#### Resource-Level Permissions

- Coordinators can manage (edit courses/members) groups they coordinate
- Group members can view their group
- Admins can manage all groups

## File Structure

```
Modules/Group/
├── Models/
│   └── AcademicGroup.php
├── Services/
│   └── GroupService.php
├── Policies/
│   └── GroupPolicy.php
├── Http/
│   ├── Controllers/
│   │   └── GroupController.php
│   └── Requests/
│       ├── StoreGroupRequest.php
│       ├── UpdateGroupRequest.php
│       ├── UpdateGroupCoursesRequest.php
│       ├── AssignUsersToGroupRequest.php
│       └── RemoveUserFromGroupRequest.php
├── Routes/
│   └── web.php
├── Providers/
│   └── GroupServiceProvider.php
└── Resources/
    └── views/
        ├── index.blade.php
        ├── create.blade.php
        ├── edit.blade.php
        ├── show.blade.php
        └── partials/
            └── tabs/
                ├── info.blade.php
                ├── courses.blade.php
                └── users.blade.php
```

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

This creates:

- `groups` table
- `group_courses` table (if not already existing)
- `group_users` pivot table

### 2. Create Permissions

```bash
php artisan tinker
# Or add to a seeder

Permission::create(['name' => 'group.view']);
Permission::create(['name' => 'group.create']);
Permission::create(['name' => 'group.edit']);
Permission::create(['name' => 'group.delete']);

// Assign to roles as needed
```

### 3. Verify Routes

```bash
php artisan route:list | grep groups
```

### 4. Clear Cache

```bash
php artisan optimize:clear
```

## API Endpoints

### List Groups

```
GET /groups
```

### Get Available Courses (JSON)

```
GET /groups/{id}/courses/available
```

### Update Courses

```
PUT /groups/{id}/courses
Content-Type: application/json

{
  "course_ids": [1, 2, 3]
}
```

### Get Available Users (JSON)

```
GET /groups/{id}/users/available
```

### Assign Users

```
POST /groups/{id}/users
Content-Type: application/json

{
  "user_ids": [1, 2, 3],
  "role": "member"
}
```

### Update User Role

```
PUT /groups/{id}/users/{userId}/role/{role}
```

## Performance Considerations

- **N+1 Query Prevention**: Service methods use eager loading with `with()`
- **Transactional Operations**: Course/user assignment wrapped in `DB::transaction()`
- **Caching Potential**: Programme and available courses lists could be cached
- **Pagination**: Index view should implement pagination for large datasets
- **Soft Deletes**: Groups can be soft-deleted to maintain referential integrity

## Future Enhancements

1. **Group Templates**: Create group templates for common configurations
2. **Bulk Operations**: Import/export groups and members
3. **Scheduling**: Automate group creation based on academic calendar
4. **Analytics**: Dashboard showing group statistics and trends
5. **Notifications**: Notify users when added to groups
6. **Workflow Integration**: Connect groups to academic workflow systems
7. **Multi-Semester Groups**: Support groups spanning multiple semesters
8. **Group Hierarchies**: Support parent-child group relationships

## Testing

### Feature Tests (To be implemented)

- Test group CRUD operations
- Test authorization checks via GroupPolicy
- Test course assignment and removal
- Test member assignment and role changes
- Test soft deletion

### Unit Tests (To be implemented)

- Test GroupService methods
- Test AcademicGroup model relationships
- Test GroupPolicy authorization logic

## Troubleshooting

### Views Not Found

- Ensure `GroupServiceProvider` is registered in `bootstrap/providers.php`
- Clear cache: `php artisan optimize:clear`
- Check view path: `Modules/Group/Resources/views/`

### Authorization Failures

- Verify `GroupPolicy` is registered in `ModuleServiceProvider`
- Check permissions are created: `php artisan tinker` → `Permission::all()`
- Verify user has assigned roles/permissions

### Routes Not Registered

- Check `Modules/Group/Routes/web.php` is properly defined
- Test route registration: `php artisan route:list | grep groups`

### Drag-and-Drop Not Working

- Ensure browser supports HTML5 drag-and-drop API
- Check browser console for JavaScript errors
- Verify Bootstrap 5 CSS is loaded
- Check Bootstrap Icons package is available

## Related Modules

- **Programme Management**: Provides programme data linked to groups
- **Course Management**: Provides courses for assignment
- **User Management**: Base user system for member management
- **Permission & Roles**: Spatie Laravel Permission for authorization

## Support

For issues or questions, refer to:

- `/Modules/Group/README.md` (this file)
- Routes: `/Modules/Group/Routes/web.php`
- Controller: `/Modules/Group/Http/Controllers/GroupController.php`
- Service: `/Modules/Group/Services/GroupService.php`
- Policy: `/Modules/Group/Policies/GroupPolicy.php`
