<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class LocationDetectorController extends Controller
{
    public static function detectVpn()
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => env("IP_DETECTIVE_KEY"),
            ])->get(env('IP_DETECTIVE_URL'));

            if ($response->successful()) {
                $ipDetails = $response->json();

                if ($ipDetails['bot'] == true) {
                    return null;
                }

                $userCountry = $ipDetails['country_name'] ?? 'Unknown';
                $countryCurrency = match ($userCountry) {
                    'Egypt' => 'EGP',
                    'Saudi Arabia' => 'SAR',
                    default => 'USD',
                };
                
                return $countryCurrency;
            }
            logger()->error('API Response Error: ' . $response->body());
        } catch (\Exception $e) {
            logger()->error('Error calling IPDetective API: ' . $e->getMessage());
        }
    }
}
