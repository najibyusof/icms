# Group Management Module - Testing & Validation Guide

## Quick Start

### Prerequisites

- Laravel 12.x installed and configured
- Database configured and connected
- Spatie Laravel Permission installed
- Bootstrap 5 included in layout

### Installation Steps

#### Step 1: Run Migration

```bash
php artisan migrate
```

This will create:

- `groups` table
- `group_courses` table (if not existing)
- `group_users` table (with pivot data)

#### Step 2: Create Permissions

```bash
php artisan tinker
```

Inside tinker:

```php
use Spatie\Permission\Models\Permission, Role;

# Create permissions
Permission::firstOrCreate(['name' => 'group.view']);
Permission::firstOrCreate(['name' => 'group.create']);
Permission::firstOrCreate(['name' => 'group.edit']);
Permission::firstOrCreate(['name' => 'group.delete']);

# Assign to admin role (or your admin role name)
$admin = Role::firstOrCreate(['name' => 'admin']);
$admin->givePermissionTo(['group.view', 'group.create', 'group.edit', 'group.delete']);

# Optionally assign to coordinator role
$coordinator = Role::firstOrCreate(['name' => 'coordinator']);
$coordinator->givePermissionTo(['group.view', 'group.edit']);
```

#### Step 3: Clear Cache

```bash
php artisan optimize:clear
```

## Verification Checklist

### ✅ File Structure

- [x] `/Modules/Group/Models/AcademicGroup.php` exists
- [x] `/Modules/Group/Services/GroupService.php` exists
- [x] `/Modules/Group/Policies/GroupPolicy.php` exists
- [x] `/Modules/Group/Http/Controllers/GroupController.php` exists
- [x] `/Modules/Group/Routes/web.php` exists
- [x] `/Modules/Group/Providers/GroupServiceProvider.php` exists
- [x] `/Modules/Group/Http/Requests/*.php` (4 files)
- [x] `/Modules/Group/Resources/views/*.blade.php` (4 views)
- [x] `/Modules/Group/Resources/views/partials/tabs/*.blade.php` (3 partials)

### ✅ Configuration

- [x] `GroupServiceProvider` registered in `bootstrap/providers.php`
- [x] `GroupPolicy` registered in `App\Providers\ModuleServiceProvider`

### ✅ Syntax Validation

- [x] GroupController.php - No errors
- [x] AcademicGroup.php - No errors
- [x] GroupService.php - No errors
- [x] GroupPolicy.php - No errors

## Usage Testing

### Test 1: Group Listing

**URL**: `http://localhost:8000/groups`
**Expected**:

- Displays group listing page with cards showing totals
- "Create Group" button visible (if authorized)
- Table/list of existing groups

**Steps**:

1. Navigate to `/groups`
2. Verify page loads without errors
3. Check authentication is required

### Test 2: Create Group

**URL**: `http://localhost:8000/groups/create`
**Expected**:

- Form with fields: programme, name, intake_year, semester, coordinator, is_active
- All fields visible and functional
- Form validates on submit

**Steps**:

1. Click "Create Group" button
2. Fill in form with test data
3. Select a programme
4. Enter group name (e.g., "CS 2024 Batch A")
5. Set intake year and semester
6. Optionally assign coordinator
7. Click "Create Group"
8. Should redirect to group show page with success message

### Test 3: Group Detail View

**URL**: `http://localhost:8000/groups/{id}`
**Expected**:

- Three tabs visible: "Basic Info", "Courses", "Members"
- Stats cards display correctly
- Tab switching works
- Authorization-based on user permissions

**Steps**:

1. Click on created group
2. Verify Basic Info tab shows group data
3. Check stats cards (members, courses, coordinators, status)
4. Click "Courses" tab
5. Click "Members" tab
6. Verify all content loads

### Test 4: Course Assignment (Dual-List Selector)

**URL**: `http://localhost:8000/groups/{id}` → Courses Tab
**Expected**:

- Two lists side-by-side: Available Courses, Assigned Courses
- Courses can be dragged between lists
- Transfer buttons work (chevron left/right)
- Save button submits via AJAX
- Success notification appears after saving

**Steps**:

1. Open group detail page
2. Click "Courses" tab
3. Verify available courses on left
4. Drag a course to the right list (Assigned)
5. OR click the transfer button to move course
6. Verify course moves to right list
7. Click "Save Changes" button
8. Verify success notification appears
9. Refresh page to confirm persistence

**AJAX Test**:

- Open browser DevTools (F12)
- Go to Network tab
- Perform course assignment
- Verify PUT request to `/groups/{id}/courses`
- Response should be JSON with success: true

### Test 5: Member Management (Dual-List Selector with Roles)

**URL**: `http://localhost:8000/groups/{id}` → Members Tab
**Expected**:

- Two lists: Available Users, Assigned Members
- Users can be dragged between lists
- Role selector dropdown visible for assigned members
- Search functionality works
- Save button submits via AJAX with role data

**Steps**:

1. Open group detail page
2. Click "Members" tab
3. Verify available users on left
4. Drag a user to right list (or click transfer button)
5. Role dropdown appears with options: Member, Assistant, Coordinator
6. Select a role
7. Add more users as needed
8. Click "Save Changes"
9. Verify success notification
10. Refresh to confirm persistence

**Role Testing**:

1. Assign user as "Coordinator"
2. Refresh page
3. User should still show as "Coordinator" in dropdown
4. Test changing role and saving again

### Test 6: Edit Group

**URL**: `http://localhost:8000/groups/{id}/edit`
**Expected**:

- Form pre-filled with group data
- All fields editable
- Save button updates group

**Steps**:

1. From group detail page, click "Edit" button
2. Verify form is pre-filled
3. Change group name
4. Change semester
5. Click "Update Group"
6. Verify redirect back to group detail
7. Verify changes are persisted

### Test 7: Delete Group

**Expected**:

- Delete button visible to authorized users
- Confirmation dialog appears
- Group is deleted from list
- Soft delete maintains referential integrity

**Steps**:

1. From group detail page, click "Delete" button
2. Confirm deletion in dialog
3. Verify redirect to group list
4. Verify deleted group no longer in listing

### Test 8: Authorization & Permissions

**Test Scenarios**:

**Scenario A: Admin User**

- Can view all groups
- Can create groups
- Can edit any group
- Can delete any group
- Can manage courses and members for any group

**Scenario B: Coordinator User**

- Can view groups
- Cannot create groups (unless `group.create` permission)
- Can edit groups they coordinate
- Cannot delete groups (unless `group.delete` permission)
- Can manage members/courses for their group

**Scenario C: Regular User**

- Can view groups they're member of
- Cannot create groups
- Cannot edit groups
- Cannot delete groups
- Cannot manage courses/members

## Browser Testing

### Desktop (Chrome/Firefox/Safari)

- [x] Responsive layout works
- [x] Tabs switch properly
- [x] Drag-and-drop functions
- [x] Buttons respond to clicks
- [x] Forms validate and submit
- [x] Success messages display

### Mobile (Phone/Tablet)

- [x] Layout is responsive
- [x] Forms are usable on small screens
- [x] Buttons are touch-friendly
- [x] Dual-list selector is usable on mobile
- [x] No console errors

## Performance Testing

### Check N+1 Queries

1. Enable query logging:

```php
// In .env or locally
DB::enableQueryLog();
```

2. Load group show page
3. Check query count (should be < 5 for show operation)
4. Verify no duplicate queries for relationships

### Check AJAX Performance

1. Open DevTools Network tab
2. Perform course assignment
3. Check request time (should be < 1000ms)
4. Verify no unnecessary requests

## Database Verification

### Verify Tables Created

```bash
php artisan tinker
```

```php
# Check tables exist
Schema::hasTable('groups') // true
Schema::hasTable('group_users') // true
Schema::hasTable('group_courses') // true

# Check group_users pivot table structure
Schema::getColumns('group_users')
# Should show: id, group_id, user_id, role, created_at, updated_at
```

### Verify Relationships

```php
# In tinker
$group = Group::first();
$group->users // BelongsToMany collection
$group->courses // BelongsToMany collection
$group->programme // BelongsTo instance
$group->coordinator // BelongsTo instance
```

## API Endpoint Testing

### Using cURL or Postman

#### Get Available Courses

```bash
curl -X GET \
  http://localhost:8000/groups/1/courses/available \
  -H "Authorization: Bearer {token}"
```

#### Update Courses

```bash
curl -X PUT \
  http://localhost:8000/groups/1/courses \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Content-Type: application/json" \
  -d '{"course_ids": [1, 2, 3]}'
```

#### Assign Users

```bash
curl -X POST \
  http://localhost:8000/groups/1/users \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Content-Type: application/json" \
  -d '{"user_ids": [1, 2, 3], "role": "member"}'
```

## Common Issues & Solutions

### Issue: Views Not Found

**Error**: `View [group::...] not found`
**Solution**:

1. Verify GroupServiceProvider in bootstrap/providers.php
2. Run `php artisan optimize:clear`
3. Check view path: `Modules/Group/Resources/views/`

### Issue: Route Not Found

**Error**: `Route [groups.index] not found`
**Solution**:

1. Run `php artisan route:list | grep groups`
2. Verify routes file at `Modules/Group/Routes/web.php`
3. Check routes are in auth middleware group

### Issue: Authorization Failure

**Error**: `This action is unauthorized`
**Solution**:

1. Verify user has `group.view` or `group.create` permission
2. Check GroupPolicy in ModuleServiceProvider
3. Run permissions creation script in tinker

### Issue: AJAX Submission Fails

**Error**: Network error or 422 response
**Solution**:

1. Check CSRF token is included in headers
2. Verify request Content-Type is application/json
3. Check browser console for JavaScript errors
4. Verify response from controller includes success/message

### Issue: Drag-Drop Not Working

**Error**: Items don't move when dragging
**Solution**:

1. Verify Bootstrap 5 CSS is loaded
2. Check browser supports HTML5 drag-and-drop
3. Look for JavaScript errors in console
4. Try using transfer buttons instead

## Unit Test Examples

### Test Group Creation

```php
public function test_can_create_group()
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    $programme = Programme::factory()->create();

    $response = $this->actingAs($user)->post('/groups', [
        'name' => 'Test Group',
        'programme_id' => $programme->id,
        'intake_year' => 2024,
        'semester' => 1,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('groups', ['name' => 'Test Group']);
}
```

### Test Course Assignment

```php
public function test_can_assign_courses()
{
    $group = Group::factory()->create();
    $courses = Course::factory()->count(3)->create();

    $response = $this->actingAs($group->coordinator)->put(
        "/groups/{$group->id}/courses",
        ['course_ids' => $courses->pluck('id')->toArray()]
    );

    $response->assertJsonStructure(['success', 'message']);
    $this->assertEquals(3, $group->courses()->count());
}
```

### Test Member Assignment

```php
public function test_can_assign_users()
{
    $group = Group::factory()->create();
    $users = User::factory()->count(2)->create();

    $response = $this->actingAs($group->coordinator)->post(
        "/groups/{$group->id}/users",
        [
            'user_ids' => $users->pluck('id')->toArray(),
            'role' => 'member',
        ]
    );

    $response->assertJsonStructure(['success', 'message']);
    $this->assertEquals(2, $group->users()->count());
}
```

## Debugging Tips

### Enable SQL Query Log

```php
// In controller or route
DB::enableQueryLog();

// ... perform operations ...

// Check queries
dd(DB::getQueryLog());
```

### Check Authorization

```php
// In controller
dd($this->authorize('update', $group));
```

### Inspect Relationships

```php
// In tinker
$group = Group::find(1);
dd($group->users()->toSql()); // Check the query
dd($group->users()->get()); // Execute and see results
```

### Test AJAX Response

```javascript
// In browser console
fetch("/groups/1/courses/available")
    .then((r) => r.json())
    .then((d) => console.log(d));
```

## Performance Optimization Tips

1. **Add Caching**:

```php
$programmes = cache()->remember('programmes', 3600, fn() =>
    Programme::where('is_active', true)->get()
);
```

2. **Paginate Large Lists**:

```php
$groups = Group::paginate(15);
```

3. **Index Database**:

```php
// In migration
$table->index(['programme_id', 'is_active']);
```

4. **Use Middleware Caching**:

```php
Route::middleware(['auth', 'cache.headers:public;max_age=3600'])->get('/groups', ...);
```

## Deployment Checklist

- [ ] Run migrations on production: `php artisan migrate`
- [ ] Create permissions in production database
- [ ] Clear cache: `php artisan optimize:clear`
- [ ] Test all endpoints in production environment
- [ ] Verify authorization checks work with production users
- [ ] Monitor error logs for any issues
- [ ] Test AJAX submissions with production CSRF tokens
- [ ] Verify responsive design on production domain
- [ ] Check CDN/asset loading (Bootstrap, icons)
- [ ] Set up automated backups

## Support & Documentation

- **Full Documentation**: `/Modules/Group/README.md`
- **Implementation Summary**: `/Modules/Group/IMPLEMENTATION_SUMMARY.md`
- **Controller Code**: `/Modules/Group/Http/Controllers/GroupController.php`
- **Service Code**: `/Modules/Group/Services/GroupService.php`
- **Policy Code**: `/Modules/Group/Policies/GroupPolicy.php`

---

**Last Updated**: 2024
**Module Status**: Production Ready
**Version**: 1.0
