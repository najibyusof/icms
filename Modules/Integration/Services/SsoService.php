<?php

namespace Modules\Integration\Services;

class SsoService
{
    /**
     * Stub validator for external SSO token integration.
     * Replace with OAuth2/OpenID Connect gateway validation in production.
     *
     * @return array<string, mixed>
     */
    public function validateToken(string $token): array
    {
        if ($token === '') {
            return ['valid' => false, 'reason' => 'Missing token'];
        }

        return [
            'valid' => true,
            'subject' => 'external-user-id',
            'email' => 'user@example.edu',
            'roles' => ['lecturer'],
        ];
    }
}
