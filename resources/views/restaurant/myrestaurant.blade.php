@extends('layouts.app')

@php
    $vendor = $vendor ?? null;
    $selectedCategories = old('category_ids', $vendor?->categoryID ?? []);
    if (! is_array($selectedCategories)) {
        $selectedCategories = array_filter((array) json_decode($selectedCategories, true));
    }
    $galleryImages = $vendor?->photos ?? [];
    $adminCommission = $vendor?->adminCommission ?? [];
@endphp

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.myrestaurant_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.myrestaurant_plural') }}</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <h6 class="mb-2">Please fix the following issues:</h6>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('restaurant.update') }}" enctype="multipart/form-data" class="restaurant-profile-form">
                @csrf

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Restaurant Overview</h4>

                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Restaurant Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="title"
                                            value="{{ old('title', $vendor?->title ?? $user->name) }}"
                                            data-slug-source required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Wallet Balance</label>
                                        <input type="text" class="form-control" value="{{ number_format($user->wallet_amount ?? 0, 2) }}" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Restaurant Slug <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="restaurant_slug"
                                            value="{{ old('restaurant_slug', $vendor?->restaurant_slug) }}"
                                            data-slug-target required>
                                        <small class="text-muted d-block">Used for your public ordering page URL.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone', $vendor?->phonenumber ?? $user->phoneNumber) }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4">{{ old('description', $vendor?->description) }}</textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Restaurant Cost (avg.)</label>
                                        <input type="number" class="form-control" min="0" step="0.01" name="restaurant_cost"
                                               value="{{ old('restaurant_cost', $vendor?->restaurantCost) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Open Time</label>
                                        <input type="time" class="form-control" name="open_dine_time"
                                               value="{{ old('open_dine_time', $vendor?->openDineTime) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Close Time</label>
                                        <input type="time" class="form-control" name="close_dine_time"
                                               value="{{ old('close_dine_time', $vendor?->closeDineTime) }}">
                                    </div>
                                </div>

                                <div class="row gy-3">
                                    <div class="col-md-4">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_open" name="is_open" value="1"
                                                {{ old('is_open', $vendor?->isOpen) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_open">Restaurant is open</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="enabled_dine_in_future" name="enabled_dine_in_future" value="1"
                                                {{ old('enabled_dine_in_future', $vendor?->enabledDiveInFuture) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enabled_dine_in_future">Enable dine-in future</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Admin Commission (%)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" class="form-control" name="admin_commission"
                                                   value="{{ old('admin_commission', $adminCommission['fix_commission'] ?? null) }}">
                                            <select class="form-select" name="admin_commission_type">
                                                @php $type = old('admin_commission_type', $adminCommission['commissionType'] ?? 'Percent'); @endphp
                                                <option value="Percent" {{ $type === 'Percent' ? 'selected' : '' }}>Percent</option>
                                                <option value="Fixed" {{ $type === 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Location & Zones</h4>
                                <div class="mb-3">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="location"
                                           value="{{ old('location', $vendor?->location) }}" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Zone</label>
                                        <select class="form-control" name="zone_id" id="zone-select">
                                            <option value="">Select zone</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone->id }}"
                                                    {{ old('zone_id', $vendor?->zoneId) === $zone->id ? 'selected' : '' }}
                                                    data-zone-name="{{ $zone->name }}">
                                                    {{ $zone->name ?? $zone->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Used for delivery distance validation.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Zone Slug</label>
                                        <input type="text" class="form-control" name="zone_slug"
                                               value="{{ old('zone_slug', $vendor?->zone_slug) }}" id="zone-slug">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude</label>
                                        <input type="text" class="form-control" name="latitude"
                                               value="{{ old('latitude', $vendor?->latitude) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude</label>
                                        <input type="text" class="form-control" name="longitude"
                                               value="{{ old('longitude', $vendor?->longitude) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Menu & Preferences</h4>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Cuisine</label>
                                        <select class="form-control" name="cuisine_id">
                                            <option value="">Select cuisine</option>
                                            @foreach($cuisines as $cuisine)
                                                <option value="{{ $cuisine->id }}"
                                                    {{ old('cuisine_id', $vendor?->cuisineID) === $cuisine->id ? 'selected' : '' }}>
                                                    {{ $cuisine->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Categories</label>
                                        <select class="form-control chosen-select" name="category_ids[]" multiple>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ in_array($category->id, $selectedCategories ?? []) ? 'selected' : '' }}>
                                                    {{ $category->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Hold CTRL/CMD to select multiple.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Media</h4>
                                <div class="row mb-4">
                                    <div class="col-md-4 text-center">
                                        <div class="mb-3">
                                            <label class="form-label d-block">Primary Photo</label>
                                            <div class="border rounded p-3">
                                                <img src="{{ $vendor?->photo ?? asset('images/placeholder.png') }}"
                                                     class="img-fluid" alt="Restaurant photo">
                                            </div>
                                        </div>
                                        <input type="file" class="form-control" name="photo" accept="image/*">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Gallery</label>
                                        <input type="file" class="form-control mb-3" name="gallery[]" accept="image/*" multiple>
                                        @if(!empty($galleryImages))
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($galleryImages as $image)
                                                    <img src="{{ $image }}" alt="Gallery image" class="rounded" style="width: 110px; height: 110px; object-fit: cover;">
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No gallery images uploaded yet.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Status Summary</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Restaurant ID
                                        <span class="fw-semibold">{{ $vendor?->id ?? 'Pending' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Vendor Code
                                        <span class="fw-semibold">{{ $user->vendorID ?? 'Not linked' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Subscription Plan
                                        <span class="fw-semibold">{{ $user->subscription_plan['name'] ?? 'Not assigned' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Subscription Expiry
                                        <span class="fw-semibold">{{ $user->subscriptionExpiryDate ?? '—' }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Actions</h4>
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fa fa-save me-2"></i>Save Restaurant
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fa fa-undo me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const sourceInput = document.querySelector('[data-slug-source]');
            const targetInput = document.querySelector('[data-slug-target]');
            const zoneSelect = document.getElementById('zone-select');
            const zoneSlugInput = document.getElementById('zone-slug');

            function slugify(value) {
                return value
                    .toString()
                    .trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .replace(/-{2,}/g, '-');
            }

            if (sourceInput && targetInput) {
                sourceInput.addEventListener('input', function () {
                    if (!targetInput.dataset.userEdited) {
                        targetInput.value = slugify(this.value);
                    }
                });

                targetInput.addEventListener('input', function () {
                    this.dataset.userEdited = 'true';
                    this.value = slugify(this.value);
                });
            }

            if (zoneSelect && zoneSlugInput) {
                zoneSelect.addEventListener('change', function () {
                    const option = this.options[this.selectedIndex];
                    if (option && option.dataset.zoneName) {
                        zoneSlugInput.value = slugify(option.dataset.zoneName);
                    }
                });
            }
        })();
    </script>
@endsection

