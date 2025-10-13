@extends('layouts.app')
@section('content')
<style>
    .text-success {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
    color: #218838;
}
    .editable-price {
        transition: all 0.2s ease;
        border-radius: 3px;
        padding: 2px 4px;
    }
    .editable-price:hover {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .editable-price.text-success {
        background-color: #d4edda !important;
        border-color: #c3e6cb !important;
    }
    .editable-price.text-danger {
        background-color: #f8d7da !important;
        border-color: #f5c6cb !important;
    }
    .editable-price input {
        border: 2px solid #007bff;
        border-radius: 3px;
        padding: 2px 4px;
        font-size: inherit;
    }
</style>
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.food_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.food_plural') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="row px-5 mb-2">
            <div class="col-12">
                <span class="font-weight-bold text-danger food-limit-note"></span>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                                <li class="nav-item active">
                                    <a class="nav-link" href="{!! route('foods') !!}"><i
                                            class="fa fa-list mr-2"></i>{{ trans('lang.food_table') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('foods.create') !!}"><i
                                            class="fa fa-plus mr-2"></i>{{ trans('lang.food_create') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="example24"
                                       class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                       cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active">
                                                <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label>
                                        </th>
                                        <th>{{ trans('lang.food_image') }}</th>
                                        <th>{{ trans('lang.food_name') }}</th>
                                        <th>{{ trans('lang.food_price') }}</th>
                                        <th>Discount Price</th>
                                        <th>{{ trans('lang.food_category_id') }}</th>
                                        <th>{{ trans('lang.date') }}</th>
                                        <th>{{ trans('lang.food_publish') }}</th>
                                        <th>{{ trans('lang.food_available') }}</th>
                                        <th>{{ trans('lang.actions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var database = firebase.firestore();
        var offest = 1;
        var pagesize = 10;
        var end = null;
        var endarray = [];
        var start = null;
        var user_number = [];
        var vendorUserId = "<?php echo $id; ?>";
        var vendorId;
        var ref;
        var append_list = '';
        var placeholderImage = '';
        ref = database.collection('vendor_products');
        var activeCurrencyref = database.collection('currencies').where('isActive', "==", true);
        var activeCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        activeCurrencyref.get().then(async function(currencySnapshots) {
            currencySnapshotsdata = currencySnapshots.docs[0].data();
            activeCurrency = currencySnapshotsdata.symbol;
            currencyAtRight = currencySnapshotsdata.symbolAtRight;
            if (currencySnapshotsdata.decimal_degits) {
                decimal_degits = currencySnapshotsdata.decimal_degits;
            }
        })
        $(document).ready(function() {
            $('#category_search_dropdown').hide();
            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            var placeholder = database.collection('settings').doc('placeHolderImage');
            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })
            const table = $('#example24').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: async function(data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    const orderableColumns = ['', '', 'name', 'finalPrice', 'disPrice', 'categoryName', 'createdAt',
                        '', ''
                    ]; // Ensure this matches the actual column names
                    const orderByField = orderableColumns[orderColumnIndex];
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    try {
                        const Vendor = await getVendorId(vendorUserId);
                        const querySnapshot = await ref.where('vendorID', "==", Vendor).get();
                        if (!querySnapshot || querySnapshot.empty) {
                            console.error("No data found in Firestore.");
                            $('#data-table_processing').hide(); // Hide loader
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: [] // No data
                            });
                            return;
                        }
                        let records = [];
                        let filteredRecords = [];
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                            childData.id = doc
                                .id; // Ensure the document ID is included in the data
                            var finalPrice = 0;
                            if (childData.hasOwnProperty('disPrice') && childData
                                .disPrice != '' && childData.disPrice != '0') {
                                finalPrice = childData.disPrice;
                            } else {
                                finalPrice = childData.price;
                            }
                            childData.finalPrice = parseInt(finalPrice);
                            childData.categoryName = await productCategory(childData
                                .categoryID);
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt") && childData
                                .expiresAt != '') {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate()
                                        .toLocaleTimeString('en-US');
                                } catch (err) {}
                            }
                            var createdAt = date + ' ' + time;
                            childData.createDateTime=createdAt;
                            if (searchValue) {
                                if (
                                    (childData.name && childData.name.toString()
                                        .toLowerCase().includes(searchValue)) ||
                                    (childData.finalPrice && childData.finalPrice
                                            .toString().toLowerCase().includes(searchValue)
                                    ) ||
                                    (childData.categoryName && childData.categoryName
                                            .toString().toLowerCase().includes(
                                                searchValue) ||
                                        (createdAt && createdAt.toString().toLowerCase()
                                            .indexOf(searchValue) > -1)
                                    )
                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }));
                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField];
                            let bValue = b[orderByField];
                            if (orderByField === 'createdAt' && a[orderByField] != '' && b[
                                orderByField] != '') {
                                try {
                                    aValue = a[orderByField] ? new Date(a[orderByField]
                                        .toDate()).getTime() : 0;
                                    bValue = b[orderByField] ? new Date(b[orderByField]
                                        .toDate()).getTime() : 0;
                                } catch (err) {}
                            }
                            if (orderByField === 'finalPrice') {
                                aValue = a[orderByField] ? parseInt(a[orderByField]) : 0;
                                bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                            } else {
                                aValue = a[orderByField] ? a[orderByField].toString()
                                    .toLowerCase().trim() : '';
                                bValue = b[orderByField] ? b[orderByField].toString()
                                    .toLowerCase().trim() : '';
                            }
                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });
                        const totalRecords = filteredRecords.length;
                        const paginatedRecords = filteredRecords.slice(start, start + length);
                        const formattedRecords = await Promise.all(paginatedRecords.map(async (
                            childData) => {
                            return await buildHTML(childData);
                        }));
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords,
                            recordsFiltered: totalRecords,
                            data: formattedRecords
                        });
                    } catch (error) {
                        console.error("Error fetching data from Firestore:", error);
                        jQuery('#overlay').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                    }
                },
                order: [5, 'asc'],
                columnDefs: [{
                    orderable: false,
                    targets: [0, 1, 7, 8]
                },
                    {
                        targets: 6,
                        type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                ],
                "language": {
                    "zeroRecords": "{{ trans('lang.no_record_found') }}",
                    "emptyTable": "{{ trans('lang.no_record_found') }}"
                },
            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
        });
        $(document.body).on('change', '#selected_search', function() {
            if (jQuery(this).val() == 'category') {
                var ref_category = database.collection('vendor_categories');
                ref_category.get().then(async function(snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        $('#category_search_dropdown').append($("<option></option").attr(
                            "value", data.id).text(data.title));
                    });
                });
                jQuery('#search').hide();
                jQuery('#category_search_dropdown').show();
            } else {
                jQuery('#search').show();
                jQuery('#category_search_dropdown').hide();
            }
        });
        async function buildHTML(val) {
            var html = [];
            var id = val.id;
            var route1 = '{{ route('foods.edit', ':id') }}';
            route1 = route1.replace(':id', id);
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' +
                id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
            if (val.photo == '' && val.photo == null) {
                html.push('<td><img class="rounded" style="width:50px" src="' + placeholderImage +
                    '" alt="image"></td>');
            } else {
                html.push('<td><img onerror="this.onerror=null;this.src=\'' + placeholderImage +
                    '\'" class="rounded" style="width:50px" src="' + val.photo + '" alt="image"></td>');
            }
            html.push('<td data-url="' + route1 + '" class="redirecttopage">' + val.name + '</td>');
            // Enhanced price display logic with proper validation
            var hasDiscount = val.hasOwnProperty('disPrice') && 
                             val.disPrice != '' && 
                             val.disPrice != '0' && 
                             val.disPrice != null && 
                             parseFloat(val.disPrice) > 0 && 
                             parseFloat(val.disPrice) < parseFloat(val.price);

            if (hasDiscount) {
                // Has valid discount - show original price with strikethrough
                if (currencyAtRight) {
                    html.push('<td><span class="editable-price text-muted" style="text-decoration: line-through; cursor: pointer;" data-id="' + val.id + '" data-field="price" data-value="' + val.price + '">' + parseFloat(val.price).toFixed(decimal_degits) + '' + activeCurrency + '</span></td>');
                } else {
                    html.push('<td><span class="editable-price text-muted" style="text-decoration: line-through; cursor: pointer;" data-id="' + val.id + '" data-field="price" data-value="' + val.price + '">' + activeCurrency + '' + parseFloat(val.price).toFixed(decimal_degits) + '</span></td>');
                }
                // Show discount price in green - editable
                if (currencyAtRight) {
                    html.push('<td><span class="editable-price text-success" style="cursor: pointer;" data-id="' + val.id + '" data-field="disPrice" data-value="' + val.disPrice + '">' + parseFloat(val.disPrice).toFixed(decimal_degits) + '' + activeCurrency + '</span></td>');
                } else {
                    html.push('<td><span class="editable-price text-success" style="cursor: pointer;" data-id="' + val.id + '" data-field="disPrice" data-value="' + val.disPrice + '">' + activeCurrency + '' + parseFloat(val.disPrice).toFixed(decimal_degits) + '</span></td>');
                }
            } else {
                // No valid discount - show regular price - editable
                if (currencyAtRight) {
                    html.push('<td><span class="editable-price text-success" style="cursor: pointer;" data-id="' + val.id + '" data-field="price" data-value="' + val.price + '">' + parseFloat(val.price).toFixed(decimal_degits) + '' + activeCurrency + '</span></td>');
                } else {
                    html.push('<td><span class="editable-price text-success" style="cursor: pointer;" data-id="' + val.id + '" data-field="price" data-value="' + val.price + '">' + activeCurrency + '' + parseFloat(val.price).toFixed(decimal_degits) + '</span></td>');
                }
                // Empty cell where discount price would be - editable
                html.push('<td><span class="editable-price text-muted" style="cursor: pointer;" data-id="' + val.id + '" data-field="disPrice" data-value="0">-</span></td>');
            }

            html.push('<td class="category_' + val.categoryID + '">' + val.categoryName + '</td>');
            html.push('<td>'+val.createDateTime+'</td>');
            if (val.publish) {
                html.push('<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>');
            } else {
                html.push('<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>');
            }
            if (val.isAvailable) {
                html.push('<td><label class="switch"><input type="checkbox" checked id="available_' + val.id + '" name="isAvailable"><span class="slider round"></span></label></td>');
            } else {
                html.push('<td><label class="switch"><input type="checkbox" id="available_' + val.id + '" name="isAvailable"><span class="slider round"></span></label></td>');
            }
            var action = '';
            action = action + '<span class="action-btn"><a href="' + route1 + '"><i class="fa fa-edit"></i></a>';
            action = action + '<a id="' + val.id +
                '" class="do_not_delete" name="food-delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
            action = action + '</span>';
            html.push(action);
            return html;
        }
        $(document).on("click", "input[name='publish']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            if (ischeck) {
                database.collection('vendor_products').doc(id).update({
                    'publish': true
                }).then(function(result) {});
            } else {
                database.collection('vendor_products').doc(id).update({
                    'publish': false
                }).then(function(result) {});
            }
        });
        $(document).on("click", "input[name='isAvailable']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id.replace('available_', '');
            database.collection('vendor_products').doc(id).update({
                'isAvailable': ischeck
            }).then(function(result) {});
        });
        $("#is_active").click(function() {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm('Are You Sure want to Delete Selected Data ?')) {
                    jQuery("#data-table_processing").show();
                    $('#example24 .is_open:checked').each(async function() {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('vendor_products', dataId, 'photo', 'photos');
                        window.location.reload();
                    });
                }
            } else {
                alert('Please Select Any One Record .');
            }
        });
        async function productCategory(category) {
            var productCategory = '';
            await database.collection('vendor_categories').where("id", "==", category).get().then(async function(
                snapshotss) {
                if (snapshotss.docs[0]) {
                    var category_data = snapshotss.docs[0].data();
                    productCategory = category_data.title;
                }
            });
            return productCategory;
        }
        $(document).on("click", "a[name='food-delete']", async function(e) {
            var id = this.id;
            await deleteDocumentWithImage('vendor_products', id, 'photo', 'photos');
            window.location.reload();
        });
        async function getVendorId(vendorUser) {
            var vendorId = '';
            var ref;
            await database.collection('vendors').where('author', "==", vendorUser).get().then(async function(
                vendorSnapshots) {
                var vendorData = vendorSnapshots.docs[0].data();
                vendorId = vendorData.id;
                var subscriptionModel = false;
                var subscriptionBusinessModel = database.collection('settings').doc("restaurant");
                await subscriptionBusinessModel.get().then(async function(snapshots) {
                    var subscriptionSetting = snapshots.data();
                    if (subscriptionSetting.subscription_model == true) {
                        subscriptionModel = true;
                    }
                });
                if (subscriptionModel) {
                    if (vendorData.hasOwnProperty('subscription_plan') && vendorData.subscription_plan !=
                        null && vendorData.subscription_plan != '') {
                        itemLimit = vendorData.subscription_plan.itemLimit;
                        if (itemLimit != '-1') {
                            $('.food-limit-note').html(
                                '{{ trans('lang.note') }} : {{ trans('lang.your_food_limit_is') }} ' +
                                itemLimit + ' {{ trans('lang.so_only_first') }} ' + itemLimit +
                                ' {{ trans('lang.foods_will_visible_to_customer') }}')
                        }
                    }
                }

            })
            return vendorId;
        }

        // Inline editing functionality for prices - using backend validation
        $(document).on('click', '.editable-price', function() {
            var $this = $(this);
            var currentValue = $this.data('value');
            var field = $this.data('field');
            var id = $this.data('id');

            console.log('Inline edit clicked:', { id: id, field: field, currentValue: currentValue });

            // Create input field
            var input = $('<input>', {
                type: 'number',
                step: '0.01',
                min: '0',
                class: 'form-control form-control-sm',
                value: currentValue,
                style: 'width: 80px; display: inline-block;'
            });

            // Replace span with input
            $this.hide();
            $this.after(input);
            input.focus();

            // Handle save on enter or blur
            function saveValue() {
                var newValue = parseFloat(input.val());
                if (isNaN(newValue) || newValue < 0) {
                    newValue = 0;
                }

                // Remove input and show span
                input.remove();
                $this.show();

                // Show loading indicator
                $this.addClass('text-info');
                $this.text('Updating...');

                // Send AJAX request to backend for proper validation and data consistency
                $.ajax({
                    url: '{{ route("foods.inlineUpdate", ":id") }}'.replace(':id', id),
                    method: 'PATCH',
                    data: {
                        field: field,
                        value: newValue,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Backend validation passed, now update Firebase directly like publish toggle
                            var updateData = {};
                            updateData[field] = newValue.toString(); // Convert to string like edit page

                            // If updating price, check if discount price needs to be reset
                            if (field === 'price') {
                                // Get current discount price from the row
                                var discountCell = $this.closest('tr').find('.editable-price[data-field="disPrice"]');
                                if (discountCell.length > 0) {
                                    var currentDiscountValue = parseFloat(discountCell.data('value')) || 0;
                                    if (currentDiscountValue > newValue) {
                                        updateData['disPrice'] = '';
                                    }
                                }
                            }

                            // Update Firebase directly like the publish toggle
                            database.collection('vendor_products').doc(id).update(updateData).then(function(result) {
                                // Update the data attribute
                                $this.data('value', newValue);

                                // Update the display
                                var displayValue = newValue.toFixed(decimal_degits);
                                if (currencyAtRight) {
                                    $this.text(displayValue + activeCurrency);
                                } else {
                                    $this.text(activeCurrency + displayValue);
                                }

                                // Show success indicator
                                $this.removeClass('text-info').addClass('text-success');
                                setTimeout(function() {
                                    $this.removeClass('text-success');
                                }, 1000);

                                // If discount price was reset, update the UI
                                if (updateData.hasOwnProperty('disPrice') && updateData['disPrice'] === '') {
                                    var discountCell = $this.closest('tr').find('.editable-price[data-field="disPrice"]');
                                    if (discountCell.length > 0) {
                                        discountCell.data('value', 0);
                                        discountCell.text('-');
                                        discountCell.removeClass('text-success').addClass('text-muted');
                                    }
                                }
                            }).catch(function(error) {
                                console.error('Firebase update failed:', error);
                                alert('Update failed. Please try again.');
                                
                                // Revert to original value
                                var originalValue = currentValue;
                                var displayValue = originalValue.toFixed(decimal_degits);
                                if (currencyAtRight) {
                                    $this.text(displayValue + activeCurrency);
                                } else {
                                    $this.text(activeCurrency + displayValue);
                                }
                                $this.removeClass('text-info');
                            });
                        } else {
                            // Backend validation failed
                            alert('Update failed: ' + response.message);
                            // Revert to original value
                            var originalValue = currentValue;
                            var displayValue = originalValue.toFixed(decimal_degits);
                            if (currencyAtRight) {
                                $this.text(displayValue + activeCurrency);
                            } else {
                                $this.text(activeCurrency + displayValue);
                            }
                            $this.removeClass('text-info');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Update failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);

                        // Revert to original value
                        var originalValue = currentValue;
                        var displayValue = originalValue.toFixed(decimal_degits);
                        if (currencyAtRight) {
                            $this.text(displayValue + activeCurrency);
                        } else {
                            $this.text(activeCurrency + displayValue);
                        }
                        $this.removeClass('text-info');
                    }
                });
            }

            input.on('blur', saveValue);
            input.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    saveValue();
                }
            });

            // Handle escape key
            input.on('keydown', function(e) {
                if (e.which === 27) { // Escape key
                    input.remove();
                    $this.show();
                }
            });
        });
    </script>
@endsection
