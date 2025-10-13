<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessFirebaseFoodCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3; // Retry 3 times

    protected $foodData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $foodData)
    {
        $this->foodData = $foodData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $projectId = env('FIREBASE_PROJECT_ID');
            $apiKey = env('FIREBASE_APIKEY');
            
            if (!$projectId || !$apiKey) {
                throw new \Exception('Firebase configuration not found');
            }

            // Convert data to Firebase format
            $firebaseData = [];
            foreach ($this->foodData as $key => $value) {
                if (is_bool($value)) {
                    $firebaseData[$key] = ['booleanValue' => $value];
                } elseif (is_numeric($value)) {
                    $firebaseData[$key] = ['doubleValue' => $value];
                } elseif (is_string($value)) {
                    $firebaseData[$key] = ['stringValue' => $value];
                } elseif ($value instanceof \DateTime) {
                    $firebaseData[$key] = ['timestampValue' => $value->format('c')];
                }
            }

            $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/vendor_products?key={$apiKey}";
            
            $response = Http::timeout(30)->post($url, [
                'fields' => $firebaseData
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to create food item: ' . $response->body());
            }

            Log::info('Food item created successfully via queue', [
                'food_name' => $this->foodData['name'] ?? 'Unknown',
                'response_status' => $response->status()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create food item via queue', [
                'error' => $e->getMessage(),
                'food_data' => $this->foodData
            ]);
            
            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
