<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MfaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MfaController extends Controller
{
    public function __construct(
        private readonly MfaService $mfaService,
    ) {}

    /**
     * Start MFA setup — returns secret + recovery codes.
     *
     * POST /api/v1/mfa/enable
     */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->mfa_enabled) {
            return response()->json([
                'message' => 'MFA is already enabled.',
            ], 422);
        }

        $result = $this->mfaService->enable($user);

        return response()->json([
            'message' => 'MFA setup initiated. Scan the QR code and confirm with a 6-digit code.',
            'secret' => $result['secret'],
            'provisioning_uri' => $result['provisioning_uri'],
            'recovery_codes' => $result['recovery_codes'],
        ]);
    }

    /**
     * Confirm MFA setup with a TOTP code.
     *
     * POST /api/v1/mfa/confirm
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $confirmed = $this->mfaService->confirm($user, $request->input('code'));

        if (!$confirmed) {
            return response()->json([
                'message' => 'Invalid verification code. Please try again.',
            ], 422);
        }

        return response()->json([
            'message' => 'MFA has been enabled successfully.',
        ]);
    }

    /**
     * Verify MFA code during login.
     *
     * POST /api/v1/mfa/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $verified = $this->mfaService->verify($user, $request->input('code'));

        if (!$verified) {
            return response()->json([
                'message' => 'Invalid MFA code.',
            ], 422);
        }

        return response()->json([
            'message' => 'MFA verification successful.',
            'verified' => true,
        ]);
    }

    /**
     * Disable MFA.
     *
     * POST /api/v1/mfa/disable
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        // Verify current MFA code before disabling
        if ($user->mfa_enabled && !$this->mfaService->verify($user, $request->input('code'))) {
            return response()->json([
                'message' => 'Invalid MFA code. Cannot disable MFA.',
            ], 422);
        }

        $this->mfaService->disable($user);

        return response()->json([
            'message' => 'MFA has been disabled.',
        ]);
    }

    /**
     * Get MFA status for current user.
     *
     * GET /api/v1/mfa/status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'mfa_enabled' => $user->mfa_enabled,
            'mfa_confirmed_at' => $user->mfa_confirmed_at?->toIso8601String(),
        ]);
    }

    /**
     * Regenerate recovery codes.
     *
     * POST /api/v1/mfa/recovery-codes
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if (!$this->mfaService->verify($user, $request->input('code'))) {
            return response()->json([
                'message' => 'Invalid MFA code.',
            ], 422);
        }

        $codes = $this->mfaService->regenerateRecoveryCodes($user);

        return response()->json([
            'message' => 'Recovery codes regenerated.',
            'recovery_codes' => $codes,
        ]);
    }
}
