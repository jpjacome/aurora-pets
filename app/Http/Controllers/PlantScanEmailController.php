<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PlantScanResultWithImageMail;

class PlantScanEmailController extends Controller
{
    /**
     * Send plant scan result image via email
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'image' => 'required|file|mimes:png,jpeg,jpg|max:5120', // 5MB max
            'owner_name' => 'nullable|string|max:255',
            'pet_name' => 'nullable|string|max:255',
            'plant_name' => 'nullable|string|max:255',
        ]);

        try {
            // Get file contents without storing permanently
            $file = $request->file('image');
            $imageContents = $file->get();
            $imageMime = $file->getMimeType();

            // Send email immediately with in-memory attachment (not queued to avoid JSON serialization issues)
            // Use a safe mailer: if the configured default mailer is not defined in config.mail.mailers,
            // fall back to 'smtp' (if available) to avoid "Mailer [brevo] is not defined" runtime errors.
            $defaultMailer = config('mail.default');
            $available = config('mail.mailers', []);
            $useMailer = array_key_exists($defaultMailer, $available) ? $defaultMailer : (array_key_exists('smtp', $available) ? 'smtp' : $defaultMailer);

            Mail::mailer($useMailer)->to($data['email'])->send(
                new PlantScanResultWithImageMail([
                    'email' => $data['email'],
                    'owner_name' => $data['owner_name'] ?? null,
                    'pet_name' => $data['pet_name'] ?? 'tu mascota',
                    'plant_name' => $data['plant_name'] ?? 'tu planta',
                    'image_contents' => $imageContents,
                    'image_mime' => $imageMime,
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Email queued successfully'
            ], 200);
        } catch (\Throwable $e) {
            logger()->error('Failed to queue PlantScan email with image: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email'
            ], 500);
        }
    }
}
