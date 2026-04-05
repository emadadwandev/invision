<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class MfaService
{
    /**
     * Generate a new MFA secret and recovery codes for a user.
     * Returns the secret (to be displayed as QR / key) and recovery codes.
     */
    public function enable(User $user): array
    {
        $secret = $this->generateSecret();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'mfa_secret' => encrypt($secret),
            'mfa_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'mfa_enabled' => false, // not confirmed yet
            'mfa_confirmed_at' => null,
        ]);

        return [
            'secret' => $secret,
            'recovery_codes' => $recoveryCodes,
            'provisioning_uri' => $this->getProvisioningUri($user->email, $secret),
        ];
    }

    /**
     * Confirm MFA setup by verifying a TOTP code.
     */
    public function confirm(User $user, string $code): bool
    {
        $secret = decrypt($user->mfa_secret);

        if (!$this->verifyTotp($secret, $code)) {
            return false;
        }

        $user->update([
            'mfa_enabled' => true,
            'mfa_confirmed_at' => now(),
        ]);

        AuditService::logAuth('mfa_enabled', $user->id);

        return true;
    }

    /**
     * Verify a TOTP code or recovery code during login.
     */
    public function verify(User $user, string $code): bool
    {
        if (!$user->mfa_enabled || !$user->mfa_secret) {
            return true; // MFA not enabled — always pass
        }

        $secret = decrypt($user->mfa_secret);

        // Try TOTP first
        if ($this->verifyTotp($secret, $code)) {
            return true;
        }

        // Try recovery code
        return $this->useRecoveryCode($user, $code);
    }

    /**
     * Disable MFA for a user.
     */
    public function disable(User $user): void
    {
        $user->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
            'mfa_recovery_codes' => null,
            'mfa_confirmed_at' => null,
        ]);

        AuditService::logAuth('mfa_disabled', $user->id);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = $this->generateRecoveryCodes();

        $user->update([
            'mfa_recovery_codes' => encrypt(json_encode($codes)),
        ]);

        return $codes;
    }

    /**
     * Generate a Base32 TOTP secret (160-bit).
     */
    private function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return $this->base32Encode($bytes);
    }

    /**
     * Generate 8 recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::upper(Str::random(4) . '-' . Str::random(4));
        }
        return $codes;
    }

    /**
     * Generate a TOTP provisioning URI for authenticator apps.
     */
    private function getProvisioningUri(string $email, string $secret): string
    {
        $issuer = urlencode(config('app.name', 'Invision'));
        $account = urlencode($email);
        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&digits=6&period=30&algorithm=SHA1";
    }

    /**
     * Verify a 6-digit TOTP code with time window tolerance.
     */
    private function verifyTotp(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $decodedSecret = $this->base32Decode($secret);
        $timeSlice = floor(time() / 30);

        // Check current and adjacent time windows (±1)
        for ($i = -1; $i <= 1; $i++) {
            $calculatedCode = $this->generateTotp($decodedSecret, $timeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a TOTP code for a given time slice.
     */
    private function generateTotp(string $key, int $timeSlice): string
    {
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );
        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Use a recovery code (one-time).
     */
    private function useRecoveryCode(User $user, string $code): bool
    {
        $codes = json_decode(decrypt($user->mfa_recovery_codes), true);

        if (!is_array($codes)) {
            return false;
        }

        $normalizedCode = Str::upper(trim($code));
        $index = array_search($normalizedCode, $codes, true);

        if ($index === false) {
            return false;
        }

        // Remove the used code
        unset($codes[$index]);
        $user->update([
            'mfa_recovery_codes' => encrypt(json_encode(array_values($codes))),
        ]);

        AuditService::logAuth('mfa_recovery_code_used', $user->id);

        return true;
    }

    /**
     * Base32 encode.
     */
    private function base32Encode(string $data): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binary, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= $chars[bindec($chunk)];
        }
        return $result;
    }

    /**
     * Base32 decode.
     */
    private function base32Decode(string $data): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split(Str::upper($data)) as $char) {
            $pos = strpos($chars, $char);
            if ($pos !== false) {
                $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }
        $result = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $result .= chr(bindec($byte));
            }
        }
        return $result;
    }
}
