<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class UpdateVendorProductsAvailability extends Command
{
    protected $signature = 'vendor:update-availability';
    protected $description = 'Add isAvailable field to all documents in vendor_products collection';

    public function handle()
    {
        
        $this->info('Updating vendor_products collection...');

        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/credentials.json'));
        $firestore = $factory->createFirestore();
        $database = $firestore->database();

        $collection = $database->collection('vendor_products');
        $documents = $collection->documents();

        $updatedCount = 0;
        $batchSize = 50; // Process in batches
        $currentBatch = 0;

        foreach ($documents as $document) {
            if ($document->exists()) {
                $docRef = $document->reference();
                $docRef->update([
                    ['path' => 'isAvailable', 'value' => true]
                ]);
                $updatedCount++;
                $currentBatch++;
                
                // Memory cleanup every batch
                if ($currentBatch % $batchSize === 0) {
                    $this->info("Processed {$currentBatch} documents...");
                    gc_collect_cycles(); // Force garbage collection
                }
            }
        }

        $this->info("Updated $updatedCount documents with isAvailable = true.");
        return 0;
    }
}
