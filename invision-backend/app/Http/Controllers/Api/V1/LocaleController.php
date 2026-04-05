<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function supported(): JsonResponse
    {
        return response()->json([
            'locales' => [
                ['code' => 'en', 'name' => 'English', 'rtl' => false],
                ['code' => 'ar', 'name' => 'العربية', 'rtl' => true],
                ['code' => 'fr', 'name' => 'Français', 'rtl' => false],
            ],
            'current' => app()->getLocale(),
        ]);
    }

    public function translations(Request $request, string $locale): JsonResponse
    {
        $supported = ['en', 'ar', 'fr'];
        if (!in_array($locale, $supported)) {
            return response()->json(['error' => 'Unsupported locale'], 422);
        }

        $translations = [];
        $path = lang_path("{$locale}/messages.php");

        if (file_exists($path)) {
            $translations = require $path;
        }

        return response()->json([
            'locale' => $locale,
            'translations' => $translations,
        ]);
    }

    public function updatePreference(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|in:en,ar,fr',
        ]);

        $user = $request->user();
        $user->locale = $request->input('locale');
        $user->save();

        return response()->json([
            'message' => 'Locale preference updated',
            'locale' => $user->locale,
        ]);
    }
}
