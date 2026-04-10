<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Services\UserService;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Canonical role names (excludes legacy lowercase aliases). */
    private const CANONICAL_ROLES = ['Admin', 'Lecturer', 'Programme Coordinator', 'Reviewer', 'Approver'];

    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->paginated($request->only('search', 'role', 'faculty'));
        $roles = Role::whereIn('name', self::CANONICAL_ROLES)->orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->userService->create($request->validated());

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $users    = $this->userService->paginated(request()->only('search', 'role', 'faculty'));
        $roles    = Role::whereIn('name', self::CANONICAL_ROLES)->orderBy('name')->get();
        $editUser = $user->load('roles');

        return view('users.index', compact('users', 'roles', 'editUser'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->update($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}

