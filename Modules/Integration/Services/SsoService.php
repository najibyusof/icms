<?php

namespace Modules\Integration\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Integration\Models\IntegrationSetting;
use Laravel\Socialite\Contracts\User as ProviderUserContract;
use Laravel\Socialite\Facades\Socialite;

class SsoService
{
    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return [
            'default_role' => (string) IntegrationSetting::get(
                'sso.default_role',
                config('integration.sso.default_role', 'Lecturer')
            ),
            'role_map' => $this->currentRoleMap(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveSettings(array $data): void
    {
        IntegrationSetting::set('sso.default_role', (string) ($data['default_role'] ?? config('integration.sso.default_role', 'Lecturer')));
        IntegrationSetting::set('sso.role_map', $data['role_map'] ?? config('integration.sso.role_map', []));
    }

    public function redirectToProvider(): RedirectResponse
    {
        $driver = Socialite::driver($this->driver());

        if ($this->isStateless()) {
            $driver = $driver->stateless();
        }

        return $driver->redirect();
    }

    public function handleCallback(): User
    {
        $driver = Socialite::driver($this->driver());

        if ($this->isStateless()) {
            $driver = $driver->stateless();
        }

        /** @var ProviderUserContract $providerUser */
        $providerUser = $driver->user();

        $user = $this->resolveUser($providerUser);
        $this->syncRoles($user, $this->extractExternalRoles($providerUser));

        $user->forceFill([
            'last_sso_login_at' => now(),
        ])->save();

        Auth::login($user, true);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    public function validateToken(string $token): array
    {
        if ($token === '') {
            return ['valid' => false, 'reason' => 'Missing token'];
        }

        $roles = $this->mapRoles(['lecturer']);

        return [
            'valid' => true,
            'subject' => 'external-user-id',
            'email' => 'user@example.edu',
            'roles' => $roles,
        ];
    }

    private function resolveUser(ProviderUserContract $providerUser): User
    {
        $subject = (string) ($providerUser->getId() ?? '');
        $email = $providerUser->getEmail();
        $raw = $this->providerUserArray($providerUser);
        $staffId = data_get($raw, 'staff_id') ?: data_get($raw, 'employee_id');

        if ($subject === '' && blank($email) && blank($staffId)) {
            throw new \RuntimeException('SSO provider did not return a usable identifier.');
        }

        $user = User::query()
            ->when($subject !== '', function ($query) use ($subject) {
                $query->where(function ($inner) use ($subject) {
                    $inner->where('sso_provider', $this->driver())
                        ->where('sso_subject', $subject);
                });
            })
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->when($staffId, fn ($query) => $query->orWhere('staff_id', $staffId))
            ->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $providerUser->getName() ?: ($email ?: 'SSO User'),
                'email' => $email,
                'staff_id' => $staffId,
                'faculty' => data_get($raw, 'faculty'),
                'sso_provider' => $this->driver(),
                'sso_subject' => $subject !== '' ? $subject : null,
                'password' => Hash::make((string) str()->uuid()),
            ]);
        } else {
            $user->forceFill([
                'name' => $providerUser->getName() ?: $user->name,
                'email' => $email ?: $user->email,
                'staff_id' => $staffId ?: $user->staff_id,
                'faculty' => data_get($raw, 'faculty', $user->faculty),
                'sso_provider' => $this->driver(),
                'sso_subject' => $subject !== '' ? $subject : $user->sso_subject,
            ])->save();
        }

        return $user;
    }

    /**
     * @param array<int, string> $externalRoles
     */
    private function syncRoles(User $user, array $externalRoles): void
    {
        $roles = $this->mapRoles($externalRoles);

        if ($roles === []) {
            $roles = [(string) IntegrationSetting::get('sso.default_role', config('integration.sso.default_role', 'Lecturer'))];
        }

        $user->syncRoles($roles);
    }

    /**
     * @param array<int, string> $externalRoles
     * @return array<int, string>
     */
    public function mapRoles(array $externalRoles): array
    {
        $roleMap = $this->currentRoleMap();

        return collect($externalRoles)
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->map(fn (string $role) => $roleMap[$role] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function extractExternalRoles(ProviderUserContract $providerUser): array
    {
        $raw = $this->providerUserArray($providerUser);

        return collect(data_get($raw, 'roles', data_get($raw, 'groups', [])))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function providerUserArray(ProviderUserContract $providerUser): array
    {
        if (property_exists($providerUser, 'user')) {
            $raw = $providerUser->user;

            return is_array($raw) ? $raw : [];
        }

        return [];
    }

    private function driver(): string
    {
        return (string) config('services.sso.driver', 'google');
    }

    private function isStateless(): bool
    {
        return (bool) config('services.sso.stateless', true);
    }

    /**
     * @return array<string, string>
     */
    private function currentRoleMap(): array
    {
        $stored = IntegrationSetting::get('sso.role_map');

        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $configMap = config('integration.sso.role_map', []);

        return is_array($configMap) ? $configMap : [];
    }
}
