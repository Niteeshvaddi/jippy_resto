<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\VendorCuisine;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RestaurantProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $vendor = $this->findVendorForUser($user);

        return view('restaurant.myrestaurant', [
            'user' => $user,
            'vendor' => $vendor,
            'zones' => Zone::active()->orderBy('name')->get(),
            'cuisines' => VendorCuisine::active()->orderBy('title')->get(),
            'categories' => VendorCategory::active()->orderBy('title')->get(),
        ]);
    }

    public function update(UpdateRestaurantRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($user, $request) {
            $vendor = $this->ensureVendorExists($user);

            $payload = [
                'title' => $request->string('title')->trim(),
                'restaurant_slug' => $request->input('restaurant_slug'),
                'zone_slug' => $request->input('zone_slug'),
                'zoneId' => $request->input('zone_id'),
                'phonenumber' => $request->input('phone'),
                'description' => $request->input('description'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'location' => $request->input('location'),
                'restaurantCost' => $request->input('restaurant_cost'),
                'openDineTime' => $request->input('open_dine_time'),
                'closeDineTime' => $request->input('close_dine_time'),
                'isOpen' => $request->boolean('is_open'),
                'enabledDiveInFuture' => $request->boolean('enabled_dine_in_future'),
                'categoryID' => array_values($request->input('category_ids', [])),
                'cuisineID' => $request->input('cuisine_id'),
            ];

            if ($request->filled('category_ids')) {
                $payload['categoryTitle'] = VendorCategory::whereIn('id', $request->category_ids)
                    ->pluck('title')
                    ->filter()
                    ->values()
                    ->all();
            }

            if ($request->filled('cuisine_id')) {
                $payload['cuisineTitle'] = optional(VendorCuisine::find($request->cuisine_id))->title;
            }

            if ($request->filled('admin_commission')) {
                $payload['adminCommission'] = [
                    'commissionType' => $request->input('admin_commission_type', 'Percent'),
                    'fix_commission' => $request->input('admin_commission'),
                    'isEnabled' => true,
                ];
            }

            if ($request->hasFile('photo')) {
                $payload['photo'] = $this->storeImage($request->file('photo'), 'restaurants');
            }

            if ($request->hasFile('gallery')) {
                $existingGallery = $vendor->photos ?? [];
                foreach ($request->file('gallery') as $file) {
                    $existingGallery[] = $this->storeImage($file, 'restaurants/gallery');
                }
                $payload['photos'] = array_values(array_filter($existingGallery));
            }

            $vendor->fill($payload);
            $vendor->save();
        });

        return redirect()
            ->route('restaurant')
            ->with('success', 'Restaurant details updated successfully.');
    }

    protected function storeImage($file, string $path): string
    {
        $stored = $file->store($path, 'public');

        return Storage::disk('public')->url($stored);
    }

    protected function findVendorForUser(User $user): ?Vendor
    {
        $vendorId = $user->vendorID ?? $user->getvendorId();

        return $vendorId ? Vendor::find($vendorId) : null;
    }

    protected function ensureVendorExists(User $user): Vendor
    {
        $vendor = $this->findVendorForUser($user);

        if ($vendor) {
            return $vendor;
        }

        $vendorId = $user->vendorID ?? $user->getvendorId() ?? (string) Str::uuid();

        $vendor = Vendor::create([
            'id' => $vendorId,
            'author' => $user->firebase_id ?? $user->_id ?? (string) $user->id,
            'title' => $user->name ?? '',
            'phonenumber' => $user->phoneNumber ?? null,
        ]);

        if (empty($user->vendorID)) {
            $user->vendorID = $vendorId;
            $user->save();
        }

        return $vendor;
    }
}

