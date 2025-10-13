<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\VendorUsers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OptimizedFoodController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Optimized index method with caching
     */
    public function index()
    {
        $user = Auth::user();
        $id = Auth::id();
        
        // Cache vendor lookup for 5 minutes
        $cacheKey = "vendor_user_{$id}";
        $exist = Cache::remember($cacheKey, 300, function() use ($id) {
            return VendorUsers::where('user_id', $id)->first();
        });
        
        if (!$exist) {
            return redirect()->back()->with('error', 'Vendor not found');
        }
        
        $id = $exist->uuid;
        return view("foods.index")->with('id', $id);
    }

    /**
     * Optimized Excel import with memory management
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xls,xlsx|max:2048'
            ]);

            $file = $request->file('file');
            
            // Process in smaller chunks to reduce memory usage
            $chunkSize = 25; // Reduced from 50
            $maxMemoryUsage = 50 * 1024 * 1024; // 50MB limit
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            $headers = array_shift($rows);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Process in smaller chunks
            $chunks = array_chunk($rows, $chunkSize);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                // Check memory usage
                if (memory_get_usage(true) > $maxMemoryUsage) {
                    Log::warning('Memory limit approaching during import', [
                        'memory_usage' => memory_get_usage(true),
                        'chunk_index' => $chunkIndex
                    ]);
                    
                    // Force garbage collection
                    gc_collect_cycles();
                    
                    // If still over limit, break
                    if (memory_get_usage(true) > $maxMemoryUsage) {
                        $errors[] = "Import stopped due to memory constraints at chunk " . ($chunkIndex + 1);
                        break;
                    }
                }
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
                
                foreach ($chunk as $rowIndex => $row) {
                    $actualIndex = ($chunkIndex * $chunkSize) + $rowIndex;
                    
                    try {
                        if (empty(array_filter($row))) {
                            continue; // Skip empty rows
                        }

                        $data = array_combine($headers, $row);
                        
                        // Validate required fields
                        if (empty($data['name']) || empty($data['price'])) {
                            $errors[] = "Row " . ($actualIndex + 2) . ": Name and price are required";
                            $errorCount++;
                            continue;
                        }

                        // Prepare food data
                        $foodData = [
                            'name' => $data['name'],
                            'price' => floatval($data['price']),
                            'description' => $data['description'] ?? '',
                            'disPrice' => !empty($data['disPrice']) ? floatval($data['disPrice']) : 0,
                            'publish' => !empty($data['publish']),
                            'nonveg' => !empty($data['nonveg']),
                            'isAvailable' => !empty($data['isAvailable']),
                            'photo' => $data['photo'] ?? '',
                            'createdAt' => new \DateTime(),
                            'updatedAt' => new \DateTime()
                        ];

                        // Handle vendor ID/name
                        if (!empty($data['vendorID'])) {
                            $foodData['vendorID'] = $data['vendorID'];
                        } elseif (!empty($data['vendorName'])) {
                            $foodData['vendorID'] = $data['vendorName'];
                        }

                        // Handle category ID/name
                        if (!empty($data['categoryID'])) {
                            $foodData['categoryID'] = $data['categoryID'];
                        } elseif (!empty($data['categoryName'])) {
                            $foodData['categoryID'] = $data['categoryName'];
                        }

                        // Use optimized Firebase creation
                        $this->createFoodViaRestApiOptimized($foodData);
                        $successCount++;

                    } catch (\Exception $e) {
                        $errors[] = "Row " . ($actualIndex + 2) . ": " . $e->getMessage();
                        $errorCount++;
                    }
                }
                
                // Clear memory after each chunk
                unset($chunk);
                gc_collect_cycles();
            }

            // Clear the spreadsheet from memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            gc_collect_cycles();

            $message = "Import completed. Successfully imported {$successCount} items.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} items failed to import.";
            }

            return redirect()->back()->with('success', $message)->with('import_errors', $errors);

        } catch (\Exception $e) {
            Log::error('Import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Optimized Firebase creation with caching and rate limiting
     */
    private function createFoodViaRestApiOptimized($foodData)
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        $apiKey = env('FIREBASE_APIKEY');
        
        if (!$projectId || !$apiKey) {
            throw new \Exception('Firebase configuration not found');
        }

        // Rate limiting for Firebase operations
        $rateLimitKey = 'firebase_operations_' . request()->ip();
        $operations = Cache::get($rateLimitKey, 0);
        
        if ($operations >= 5) { // Max 5 operations per minute per IP
            throw new \Exception('Rate limit exceeded. Please wait before trying again.');
        }
        
        Cache::put($rateLimitKey, $operations + 1, 60); // 1 minute

        // Convert data to Firebase format
        $firebaseData = [];
        foreach ($foodData as $key => $value) {
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
        
        // Use timeout to prevent hanging requests
        $response = Http::timeout(10)->post($url, [
            'fields' => $firebaseData
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to create food item: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Optimized inline update with caching
     */
    public function inlineUpdate(Request $request, $id)
    {
        try {
            $field = $request->input('field');
            $value = $request->input('value');

            // Validate field
            if (!in_array($field, ['price', 'disPrice'])) {
                return response()->json(['success' => false, 'message' => 'Invalid field. Only price and disPrice are allowed.'], 400);
            }

            // Enhanced value validation
            if (!is_numeric($value) || $value < 0) {
                return response()->json(['success' => false, 'message' => 'Invalid price value. Price must be a positive number.'], 400);
            }

            // Additional validation for maximum price
            if ($value > 999999) {
                return response()->json(['success' => false, 'message' => 'Price cannot exceed 999,999'], 400);
            }

            // Cache validation result to prevent duplicate processing
            $cacheKey = "validation_{$id}_{$field}_{$value}";
            if (Cache::has($cacheKey)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Validation passed. Proceeding with update.',
                    'data' => [
                        'field' => $field,
                        'value' => $value
                    ]
                ]);
            }
            
            Cache::put($cacheKey, true, 60); // Cache for 1 minute

            return response()->json([
                'success' => true,
                'message' => 'Validation passed. Proceeding with update.',
                'data' => [
                    'field' => $field,
                    'value' => $value
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Food inline update validation failed', [
                'id' => $id,
                'field' => $request->input('field'),
                'value' => $request->input('value'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Validation failed. Please check your input and try again.'
            ], 400);
        }
    }
}
