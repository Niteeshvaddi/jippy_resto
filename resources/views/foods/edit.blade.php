@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.food_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">{{ trans('lang.dashboard') }}</a></li>
                    <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                    <li class="breadcrumb-item"><a
                            href="{{ route('restaurants.foods', $_GET['eid']) }}">{{ trans('lang.food_plural') }}</a>
                    </li>
                    <?php }else{ ?>
                    <li class="breadcrumb-item"><a href="{!! route('foods') !!}">{{ trans('lang.food_plural') }}</a></li>
                    <?php } ?>
                    <li class="breadcrumb-item active">{{ trans('lang.food_edit') }}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.food_information') }}</legend>
                            <div class="form-group row width-100" id="admin_commision_info" style="display:none">
                                <div class="m-3">
                                    <div class="form-text font-weight-bold text-danger h6">
                                        {{ trans('lang.price_instruction') }}</div>
                                    <div class="form-text font-weight-bold text-danger h6" id="admin_commision"></div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_name') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control food_name" required>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.food_name_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_price') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control food_price" required>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.food_price_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.food_discount') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control food_discount">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.food_discount_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50 food_restaurant_div" style="display: none;">
                                <label class="col-3 control-label">{{ trans('lang.food_restaurant_id') }}</label>
                                <div class="col-7">
                                    <select id="food_restaurant" class="form-control" required>
                                        <option value="">{{ trans('lang.select_restaurant') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.food_restaurant_id_help') }}
                                    </div>
                                </div>
                            </div>
                            <!--<div class="form-group row width-100">-->
                            <!--    <label class="col-3 control-label">{{ trans('lang.food_category_id') }}</label>-->
                            <!--    <div class="col-7">-->
                            <!--        <select id='food_category' class="form-control" required>-->
                            <!--            <option value="">{{ trans('lang.select_category') }}</option>-->
                            <!--        </select>-->
                            <!--        <div class="form-text text-muted">-->
                            <!--            {{ trans('lang.food_category_id_help') }}-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                              <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.food_category_id')}}</label>
                                <div class="col-7">
                                    <div id="selected_categories" class="mb-2"></div>
                                    <input type="text" id="food_category_search" class="form-control mb-2" placeholder="Search categories...">
                                    <select id='food_category' class="form-control" multiple required>
                                        <option value="">Select categories</option>
                                        <!-- options populated dynamically -->
                                    </select>
                                    <div class="form-text text-muted">
                                     {{ trans('lang.food_category_id_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_quantity') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_quantity" value="-1">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_quantity_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100" id="attributes_div" style="display: none;">
                                <label class="col-3 control-label">{{ trans('lang.item_attribute_id') }}</label>
                                <div class="col-7">
                                    <select id='item_attribute' class="form-control chosen-select" required
                                        multiple="multiple" onchange="selectAttribute();"></select>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <div class="item_attributes" id="item_attributes"></div>
                                <div class="item_variants" id="item_variants"></div>
                                <input type="hidden" id="attributes" value="" />
                                <input type="hidden" id="variants" value="" />
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.food_image') }}</label>
                                <div class="col-7">
                                    <input type="file" id="product_image">
                                    <div class="placeholder_img_thumb product_image"></div>
                                    <div id="uploding_image"></div>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.food_image_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.food_description') }}</label>
                                <div class="col-7">
                                    <textarea rows="8" class="form-control food_description" id="food_description"></textarea>
                                </div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="food_publish" id="food_publish">
                                <label class="col-3 control-label"
                                    for="food_publish">{{ trans('lang.food_publish') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="food_nonveg" id="food_nonveg">
                                <label class="col-3 control-label" for="food_nonveg">{{ trans('lang.non_veg') }}</label>
                            </div>
                            <div class="form-check width-100" style="display: none">
                                <input type="checkbox" disabled class="food_take_away_option" id="food_take_away_option">
                                <label class="col-3 control-label"
                                    for="food_take_away_option">{{ trans('lang.food_take_away') }}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="food_available" id="food_available">
                                <label class="col-3 control-label" for="food_available">{{ trans('lang.food_available') }}</label>
                            </div>
                        </fieldset>
                        <fieldset style="display: none;">
                            <legend>{{ trans('lang.ingredients') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.calories') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_calories">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.grams') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_grams">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.fats') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_fats">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.proteins') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_proteins">
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{ trans('lang.food_add_one') }}</legend>
                            <div class="form-group add_ons_list extra-row">
                            </div>
                            <div class="form-group row width-100">
                                <div class="col-7">
                                    <button type="button" onclick="addOneFunction()" class="btn btn-primary"
                                        id="add_one_btn">{{ trans('lang.food_add_one') }}</button>
                                </div>
                            </div>
                            <div class="form-group row width-100" id="add_ones_div" style="display:none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{ trans('lang.food_title') }}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control add_ons_title">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{ trans('lang.food_price') }}</label>
                                        <div class="col-7">
                                            <input type="number" class="form-control add_ons_price">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row save_add_one_btn width-100" style="display:none">
                                <div class="col-7">
                                    <button type="button" onclick="saveAddOneFunction()"
                                        class="btn btn-primary">{{ trans('lang.save_add_ones') }}</button>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{ trans('lang.product_specification') }}</legend>
                            <div class="form-group product_specification extra-row">
                                <div class="row" id="product_specification_heading" style="display: none;">
                                    <div class="col-6">
                                        <label class="col-2 control-label">{{ trans('lang.lable') }}</label>
                                    </div>
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{ trans('lang.value') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <div class="col-7">
                                    <button type="button" onclick="addProductSpecificationFunction()"
                                        class="btn btn-primary" id="add_one_btn">
                                        {{ trans('lang.add_product_specification') }}</button>
                                </div>
                            </div>
                            <div class="form-group row width-100" id="add_product_specification_div"
                                style="display:none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="col-2 control-label">{{ trans('lang.lable') }}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control add_label">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{ trans('lang.value') }}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control add_value">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row save_product_specification_btn width-100" style="display:none">
                                <div class="col-7">
                                    <button type="button" onclick="saveProductSpecificationFunction()"
                                        class="btn btn-primary">{{ trans('lang.save_product_specification') }}</button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  save_food_btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}</button>
                    <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                    <a href="{{ route('restaurants.foods', $_GET['eid']) }}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    <?php }else{ ?>
                    <a href="{!! route('foods') !!}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var id = "<?php echo $id; ?>";
        var database = firebase.firestore();
        var ref = database.collection('vendor_products').doc(id);
        var storageRef = firebase.storage().ref('images');
        var storage = firebase.storage();
        var photo = "";
        var addOnesTitle = [];
        var addOnesPrice = [];
        var sizeTitle = [];
        var sizePrice = [];
        var attributes_list = [];
        var categories_list = [];
        var restaurant_list = [];
        var photos = [];
        var new_added_photos = [];
        var new_added_photos_filename = [];
        var photosToDelete = [];
        var product_specification = {};
        var placeholderImage = '';
        var productImagesCount = 0;
        var variant_photos = [];
        var variant_filename = [];
        var variantImageToDelete = [];
        var variant_vIds = [];
        var product_image_filename = [];
        var currencyAtRight = false;
        var refCurrency = database.collection('currencies').where('isActive', '==', true);
        var decimal_degits = 0;
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        $(document).ready(function() {
            <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
            $(".food_restaurant_div").hide();
            <?php } else{?>
            $(".food_restaurant_div").show();
            <?php } ?>
            $("#attributes_div").show();
            jQuery(document).on("click", ".mdi-cloud-upload", function() {
                var variant = jQuery(this).data('variant');
                var fileurl = $('[id="variant_' + variant + '_url"]').val();
                if (fileurl) {
                    variantImageToDelete.push(fileurl);
                }
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex, 1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex, 1); // 2nd parameter means remove one item only
                }
                $('[id="variant_' + variant + '_url"]').val('');
                $('[id="file_' + variant + '"]').click();
            });
            jQuery(document).on("click", ".mdi-delete", function() {
                var variant = jQuery(this).data('variant');
                var fileurl = $('[id="variant_' + variant + '_url"]').val();
                if (fileurl) {
                    variantImageToDelete.push(fileurl);
                }
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex, 1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex, 1); // 2nd parameter means remove one item only
                }
                $('[id="variant_' + variant + '_image"]').empty();
                $('[id="variant_' + variant + '_url"]').val('');
            });
            jQuery("#data-table_processing").show();
            ref.get().then(async function(snapshots) {
                var product = snapshots.data();
                await database.collection('vendors').orderBy('title', 'asc').get().then(async function(
                    snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        restaurant_list.push(data);
                        if (data.id == product.vendorID) {
                            $('#food_restaurant').append($(
                                    "<option selected></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        } else {
                            $('#food_restaurant').append($("<option></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        }
                    })
                });
                await database.collection('vendor_categories').where('publish', '==', true).get().then(
                    async function(snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();
                            categories_list.push(data);
                            if (data.id == product.categoryID) {
                                $('#food_category').append($(
                                        "<option selected></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            } else {
                                $('#food_category').append($("<option></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            }
                            updateSelectedFoodCategoryTags();
                        })
                    });
                var selected_attributes = [];
                if (product.item_attribute != null) {
                    $("#attributes_div").show();
                    $.each(product.item_attribute.attributes, function(index, attribute) {
                        selected_attributes.push(attribute.attribute_id);
                    });
                    $('#attributes').val(JSON.stringify(product.item_attribute.attributes));
                    $('#variants').val(JSON.stringify(product.item_attribute.variants));
                }
                var attributes = database.collection('vendor_attributes');
                attributes.get().then(async function(snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        attributes_list.push(data);
                        var selected = '';
                        if ($.inArray(data.id, selected_attributes) !== -1) {
                            var selected = 'selected="selected"';
                        }
                        var option = '<option value="' + data.id + '" ' + selected +
                            '>' + data.title + '</option>';
                        $('#item_attribute').append(option);
                    });
                    $("#item_attribute").show().chosen({
                        "placeholder_text": "{{ trans('lang.select_attribute') }}"
                    });
                    if (product.item_attribute) {
                        $("#item_attribute").attr("onChange", "selectAttribute('" + btoa(
                            JSON.stringify(product.item_attribute)) + "')");
                        selectAttribute(btoa(JSON.stringify(product.item_attribute)));
                    } else {
                        $("#item_attribute").attr("onChange", "selectAttribute()");
                        selectAttribute();
                    }
                });
                if (product.hasOwnProperty('product_specification')) {
                    product_specification = product.product_specification;
                    if (product_specification != null && product_specification != "") {
                        product_specification = {};
                        $.each(product.product_specification, function(key, value) {
                            product_specification[key] = value;
                        });
                    }
                    for (var key in product.product_specification) {
                        $('#product_specification_heading').show();
                        $(".product_specification").append(
                            '<div class="row" style="margin-top:5px;" id="add_product_specification_iteam_' +
                            key + '">' +
                            '<div class="col-5"><input class="form-control" type="text" value="' +
                            key + '" disabled ></div>' +
                            '<div class="col-5"><input class="form-control" type="text" value="' +
                            product.product_specification[key] + '" disabled ></div>' +
                            '<div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' +
                            key + '")><span class="fa fa-trash"></span></button></div></div>');
                    }
                }
                if (product.hasOwnProperty('photo')) {
                    photo = product.photo;
                    console.log('Initial photo loaded from database:', photo);
                    if (product.photos != undefined && product.photos != '' && product.photos != null) {
                        photos = product.photos;
                        console.log('Initial photos array loaded:', photos);
                    } else {
                        if (photo != '' && photo != null) {
                            photos.push(photo);
                            console.log('Added initial photo to photos array');
                        }
                    }
                    if (photos != '' && photos != null) {
                        photos.forEach((element, index) => {
                            var isMainPhoto = (element === photo);
                            var starButtonClass = isMainPhoto ? 'btn-success' : 'btn-outline-success';
                            var borderStyle = isMainPhoto ? '2px solid #28a745' : '2px solid #ccc';
                            
                            $(".product_image").append('<span class="image-item position-relative d-inline-block" id="photo_' +
                                index + '"><span class="remove-btn position-absolute" style="top: 3px; right: 3px; z-index: 10; background: rgba(255,255,255,0.9); border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;" data-id="' + index +
                                '" data-img="' + photos[index] +
                                '" data-status="old"><i class="fa fa-remove" style="font-size: 10px; color: #dc3545;"></i></span><button type="button" class="btn btn-sm ' + starButtonClass + ' position-absolute" style="top: 3px; right: 28px; z-index: 10; width: 20px; height: 20px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;" onclick="setAsMainPhoto(\'' + photos[index] + '\')" title="Set as main photo"><i class="fa fa-star"></i></button><img onerror="this.onerror=null;this.src=\'' +
                                placeholderImage +
                                '\'" class="rounded" width="80px" height="80px" style="border: ' + borderStyle + '; object-fit: cover;" src="' +
                                photos[index] + '"></span>');
                        })
                    } else if (photo != '' && photo != null) {
                        $(".product_image").append(
                            '<span class="image-item position-relative d-inline-block" id="photo_1"><img onerror="this.onerror=null;this.src=\'' +
                            placeholderImage +
                            '\'" class="rounded" width="80px" height="80px" style="border: 2px solid #28a745; object-fit: cover;" src="' + photo +
                            '"><button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 3px; right: 3px; z-index: 10; width: 20px; height: 20px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;" onclick="removeMainFoodPhoto()"><i class="fa fa-times"></i></button></span>');
                    } else {
                        $(".product_image").append(
                            '<span class="image-item" id="photo_1"><img class="rounded" style="width:80px; height:80px; object-fit: cover;" src="' +
                            placeholderImage + '" alt="image">');
                    }
                }
                $(".food_name").val(product.name);
                $(".food_price").val(product.price);
                $(".item_quantity").val(product.quantity);
                $(".food_discount").val(product.disPrice);
                if (product.hasOwnProperty("calories")) {
                    $(".food_calories").val(product.calories)
                }
                if (product.hasOwnProperty("grams")) {
                    $(".food_grams").val(product.grams);
                }
                if (product.hasOwnProperty("proteins")) {
                    $(".food_proteins").val(product.proteins)
                }
                if (product.hasOwnProperty("fats")) {
                    $(".food_fats").val(product.fats);
                }
                $("#food_description").val(product.description);
                if (product.publish) {
                    $(".food_publish").prop('checked', true);
                }
                if (product.nonveg) {
                    $(".food_nonveg").prop('checked', true);
                }
                if (product.takeawayOption) {
                    $(".food_take_away_option").prop('checked', true);
                }
                if (product.isAvailable) {
                    $(".food_available").prop('checked', true);
                }
                if (product.hasOwnProperty('addOnsTitle')) {
                    product.addOnsTitle.forEach((element, index) => {
                        $(".add_ons_list").append(
                            '<div class="row" style="margin-top:5px;" id="add_ones_list_iteam_' +
                            index +
                            '"><div class="col-5"><input class="form-control" type="text" value="' +
                            element +
                            '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                            product.addOnsPrice[index] +
                            '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick="deleteAddOnesSingle(' +
                            index +
                            ')"><span class="fa fa-trash"></span></button></div></div>');
                    })
                    addOnesTitle = product.addOnsTitle;
                    addOnesPrice = product.addOnsPrice;
                }
                getVendorId(product.vendorID);
                jQuery("#data-table_processing").hide();
            })
            $(".save_food_btn").click(async function() {
                var name = $(".food_name").val();
                var price = $(".food_price").val();
                var quantity = $(".item_quantity").val();
                var restaurant = $("#food_restaurant option:selected").val();
                var category = $("#food_category option:selected").val();
                var foodCalories = parseInt($(".food_calories").val());
                var foodGrams = parseInt($(".food_grams").val());
                var foodProteins = parseInt($(".food_proteins").val());
                var foodFats = parseInt($(".food_fats").val());
                var description = $("#food_description").val();
                var foodPublish = $(".food_publish").is(":checked");
                var nonveg = $(".food_nonveg").is(":checked");
                var veg = !nonveg;
                var foodTakeaway = $(".food_take_away_option").is(":checked");
                var discount = $(".food_discount").val();
                if (discount == '') {
                    discount = "0";
                }
                if (!foodCalories) {
                    foodCalories = 0;
                }
                if (!foodGrams) {
                    foodGrams = 0;
                }
                if (!foodFats) {
                    foodFats = 0;
                }
                if (!foodProteins) {
                    foodProteins = 0;
                }
                if (photos.length > 0) {
                    photo = photos[0];
                } else {
                    photo = '';
                }
                if (name == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_food_name_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (price == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_food_price_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (restaurant == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_restaurant_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (category == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_food_category_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (parseInt(price) < parseInt(discount)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append(
                        "<p>{{ trans('lang.price_should_not_less_then_discount_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (quantity == '' || quantity < -1) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    if (quantity == '') {
                        $(".error_top").append("<p>{{ trans('lang.enter_item_quantity_error') }}</p>");
                    } else {
                        $(".error_top").append(
                            "<p>{{ trans('lang.invalid_item_quantity_error') }}</p>");
                    }
                    window.scrollTo(0, 0);
                } else if (description == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_food_description_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {
                    $(".error_top").hide();
                    var quantityerror = 0;
                    var priceerror = 0;
                    var attributes = [];
                    var variants = [];
                    if ($("#item_attribute").val().length > 0) {
                        if ($('#attributes').val().length > 0) {
                            var attributes = $.parseJSON($('#attributes').val());
                        }else{
                            alert('Please add your attribute value');
                            return false;
                        }
                        if($("#item_attribute").val().length !== attributes.length){
                            alert('Please add your attribute value');
                            return false;
                        }
                        console.log($("#item_attribute").val());
                        console.log($('#attributes').val());
                    }
                    if ($('#variants').val().length > 0) {
                        var variantsSet = $.parseJSON($('#variants').val());
                        await storeVariantImageData().then(async (vIMG) => {
                            $.each(variantsSet, function(key, variant) {
                                var variant_id = uniqid();
                                var variant_sku = variant;
                                var variant_price = $('[id="price_' + variant +
                                    '"]').val();
                                var variant_quantity = $('[id="qty_' + variant +
                                    '"]').val();
                                var variant_image = $('[id="variant_' + variant +
                                    '_url"]').val();
                                if (variant_image) {
                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_quantity': variant_quantity,
                                        'variant_image': variant_image
                                    });
                                } else {
                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_quantity': variant_quantity
                                    });
                                }
                                if (variant_quantity = '' || variant_quantity < -
                                    1 || variant_quantity == 0) {
                                    quantityerror++;
                                }
                                if (variant_price == "" || variant_price <= 0) {
                                    priceerror++;
                                }
                            });
                        }).catch(err => {
                            jQuery("#data-table_processing").hide();
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + err + "</p>");
                            window.scrollTo(0, 0);
                        });
                    }
                    var item_attribute = null;
                    if (attributes.length > 0 && variants.length > 0) {
                        if (quantityerror > 0) {
                            alert(
                                'Please add your variants quantity it should be -1 or greater than -1'
                            );
                            return false;
                        }
                        if (priceerror > 0) {
                            alert('Please add your variants  Price');
                            return false;
                        }
                        var item_attribute = {
                            'attributes': attributes,
                            'variants': variants
                        };
                    }
                    if ($.isEmptyObject(product_specification)) {
                        product_specification = null;
                    }
                    jQuery("#data-table_processing").show();
                    var foodAvailable = $(".food_available").is(":checked");
                    console.log('Saving food data, current photo:', photo);
                    await storeImageData().then(async (imageData) => {
                        console.log('Image data returned from storeImageData:', imageData);
                        console.log('Before update - photo variable:', photo);
                        
                        if (imageData.photos.length > 0) {
                            photo = imageData.mainPhoto;
                            console.log('Setting main photo to:', photo);
                        } else {
                            photo = '';
                            console.log('No photos, setting photo to empty');
                        }
                        
                        console.log('After update - photo variable:', photo);
                        console.log('Updating food in database with photo:', photo);
                        console.log('Photos array being saved:', imageData.photos);
                        
                        database.collection('vendor_products').doc(id).update({
                            'name': name,
                            'price': price.toString(),
                            'quantity': parseInt(quantity),
                            'disPrice': discount,
                            'vendorID': restaurant,
                            'categoryID': category,
                            'photo': photo,
                            'calories': foodCalories,
                            "grams": foodGrams,
                            'proteins': foodProteins,
                            'fats': foodFats,
                            'description': description,
                            'publish': foodPublish,
                            'nonveg': nonveg,
                            'veg': veg,
                            'addOnsTitle': addOnesTitle,
                            'addOnsPrice': addOnesPrice,
                            'takeawayOption': foodTakeaway,
                            'product_specification': product_specification,
                            'item_attribute': item_attribute,
                            'photos': imageData.photos,
                            'isAvailable': foodAvailable
                        }).then(function(result) {
                            console.log('Food updated successfully in database');
                            <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                            window.location.href =
                                "{{ route('restaurants.foods', $_GET['eid']) }}";
                            <?php }else{ ?>
                            jQuery("#data-table_processing").hide();
                            window.location.href = '{{ route('foods') }}';
                            <?php } ?>
                        });
                    }).catch(err => {
                        console.error('Error in storeImageData:', err);
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + err + "</p>");
                        window.scrollTo(0, 0);
                    });
                }
            })
        })
        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            new Compressor(f, {
                quality: <?php echo env('IMAGE_COMPRESSOR_QUALITY', 0.8); ?>,
                success(result) {
                    f = result;
                    reader.onload = (function(theFile) {
                        return function(e) {
                            var filePayload = e.target.result;
                            var val = f.name;
                            var ext = val.split('.')[1];
                            var docName = val.split('fakepath')[1];
                            var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                            var timestamp = Number(new Date());
                            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                            var uploadTask = storageRef.child(filename).put(theFile);
                            uploadTask.on('state_changed', function(snapshot) {
                                var progress = (snapshot.bytesTransferred / snapshot
                                    .totalBytes) * 100;
                                console.log('Upload is ' + progress + '% done');
                                jQuery("#uploding_image").text("Image is uploading...");
                            }, function(error) {}, function() {
                                uploadTask.snapshot.ref.getDownloadURL().then(function(
                                    downloadURL) {
                                    jQuery("#uploding_image").text(
                                        "Upload is completed");
                                    photo = downloadURL;
                                    $(".item_image").empty()
                                    $(".item_image").append(
                                        '<img onerror="this.onerror=null;this.src=\'' +
                                        placeholderImage +
                                        '\'" class="rounded" style="width:50px" src="' +
                                        photo + '" alt="image">');
                                });
                            });
                        };
                    })(f);
                    reader.readAsDataURL(f);
                },
                error(err) {
                    console.log(err.message);
                },
            });
        }
        function addOneFunction() {
            $("#add_ones_div").show();
            $(".save_add_one_btn").show();
        }
        function saveAddOneFunction() {
            var optiontitle = $(".add_ons_title").val();
            var optionPricevalue = $(".add_ons_price").val();
            var optionPrice = $(".add_ons_price").val();
            $(".add_ons_price").val('');
            $(".add_ons_title").val('');
            if (optiontitle != '' && optionPricevalue != '') {
                addOnesPrice.push(optionPrice.toString());
                addOnesTitle.push(optiontitle);
                var index = addOnesTitle.length - 1;
                $(".add_ons_list").append('<div class="row" style="margin-top:5px;" id="add_ones_list_iteam_' + index +
                    '"><div class="col-5"><input class="form-control" type="text" value="' + optiontitle +
                    '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                    optionPrice +
                    '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick="deleteAddOnesSingle(' +
                    index + ')"><span class="fa fa-trash"></span></button></div></div>');
            } else {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.enter_title_and_price_error') }}</p>");
                window.scrollTo(0, 0);
            }
        }
        function deleteAddOnesSingle(index) {
            addOnesTitle.splice(index, 1);
            addOnesPrice.splice(index, 1);
            $("#add_ones_list_iteam_" + index).hide();
        }
        function handleFileSelectProduct(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    product_image_filename.push(filename);
                    productImagesCount++;
                    photos_html = '<span class="image-item position-relative d-inline-block" id="photo_' + productImagesCount +
                        '"><span class="remove-btn position-absolute" style="top: 3px; right: 3px; z-index: 10; background: rgba(255,255,255,0.9); border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;" data-id="' + productImagesCount + '" data-img="' +
                        filePayload +
                        '"><i class="fa fa-remove" style="font-size: 10px; color: #dc3545;"></i></span><img onerror="this.onerror=null;this.src=\'' +
                        placeholderImage + '\'" class="rounded" width="80px" height="80px" style="object-fit: cover;" src="' +
                        filePayload + '"></span>'
                    $(".product_image").append(photos_html);
                    photos.push(filePayload);
                    $("#product_image").val('');
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function removeMainFoodPhoto() {
            // Clear the image display
            $(".product_image").empty();
            $(".product_image").append(
                '<span class="image-item" id="photo_1"><img class="rounded" style="width:200px; height:200px; object-fit: cover;" src="' +
                placeholderImage + '" alt="image">');
            
            // Clear the photo variables
            photo = '';
            photos = [];
            
            // Clear the file input
            $('#product_image').val('');
            
            console.log('Main food photo removed');
        }
        
        function setAsMainPhoto(imageUrl) {
            console.log('Setting as main photo:', imageUrl);
            photo = imageUrl;
            console.log('Photo variable updated to:', photo);
            
            // Update the UI to show this is the main photo
            $('.product_image .btn-success').removeClass('btn-success').addClass('btn-outline-success');
            $(`button[onclick="setAsMainPhoto('${imageUrl}')"]`).removeClass('btn-outline-success').addClass('btn-success');
            
            // Show a visual indicator
            $('.product_image img').css('border', '2px solid #ccc');
            $(`img[src="${imageUrl}"]`).css('border', '2px solid #28a745');
            
            // If this is a new image (base64), also update the new_added_photos array
            if (imageUrl.startsWith('data:image')) {
                console.log('This is a new image (base64), updating new_added_photos priority');
                // Move this image to the front of new_added_photos so it becomes the main photo
                var index = new_added_photos.indexOf(imageUrl);
                if (index > -1) {
                    new_added_photos.splice(index, 1);
                    new_added_photos.unshift(imageUrl);
                    
                    // Also move the filename
                    var filename = new_added_photos_filename[index];
                    new_added_photos_filename.splice(index, 1);
                    new_added_photos_filename.unshift(filename);
                    console.log('Moved image to front of new_added_photos array');
                } else {
                    console.log('Warning: Image not found in new_added_photos array');
                }
            } else {
                console.log('This is an existing image (URL), will be handled in storeImageData');
                // Verify this URL exists in the photos array
                if (!photos.includes(imageUrl)) {
                    console.log('Warning: Selected main photo URL not found in photos array');
                }
            }
            
            // Log current state for debugging
            console.log('Current state - photo:', photo, 'photos count:', photos.length, 'new_added_photos count:', new_added_photos.length);
        }
        
        async function storeImageData() {
            console.log('storeImageData called, current photo variable:', photo);
            var newPhoto = [];
            var mainPhoto = photo; // Start with current photo as default
            
            // Handle existing photos (excluding those marked for deletion)
            if (photos.length > 0) {
                // Filter out photos that are marked for deletion
                var photosToDeleteUrls = [];
                photosToDelete.forEach(function(delRef) {
                    try {
                        // Extract URL from the storage reference
                        var url = delRef.toString();
                        if (url.includes('firebasestorage.googleapis.com')) {
                            // Convert storage reference to URL format
                            var path = delRef.fullPath;
                            var bucket = delRef.bucket;
                            var url = `https://firebasestorage.googleapis.com/v0/b/${bucket}/o/${encodeURIComponent(path)}?alt=media`;
                            photosToDeleteUrls.push(url);
                        }
                    } catch (e) {
                        console.log('Error processing deletion URL:', e);
                    }
                });
                
                newPhoto = photos.filter(function(photoUrl) {
                    return !photosToDeleteUrls.includes(photoUrl);
                });
                console.log('Existing photos loaded (filtered):', newPhoto);
                
                // If mainPhoto is an existing URL that's being deleted, clear it
                if (mainPhoto && !mainPhoto.startsWith('data:image') && photosToDeleteUrls.includes(mainPhoto)) {
                    console.log('Main photo is being deleted, will need to set new main photo');
                    mainPhoto = '';
                }
            }
            
            // Handle new photos (base64 data) - upload in order to maintain priority
            if (new_added_photos.length > 0) {
                console.log('Uploading new food photos:', new_added_photos.length);
                
                // Upload photos sequentially to maintain order and priority
                for (let index = 0; index < new_added_photos.length; index++) {
                    const foodPhoto = new_added_photos[index];
                    const filename = new_added_photos_filename[index];
                    
                    var originalBase64 = foodPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(filename).putString(
                        originalBase64, 'base64', {
                            contentType: 'image/jpg'
                        });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    console.log('New food photo uploaded:', downloadURL);
                    newPhoto.push(downloadURL);
                    
                    // If this is a base64 image that was set as main photo, update mainPhoto to the uploaded URL
                    if (mainPhoto === foodPhoto) {
                        mainPhoto = downloadURL;
                        console.log('Updated main photo to uploaded URL:', mainPhoto);
                    }
                }
            }
            
            // Handle photos to delete
            if (photosToDelete.length > 0) {
                console.log('Deleting old food photos:', photosToDelete.length);
                await Promise.all(photosToDelete.map(async (delImage) => {
                    try {
                        imageBucket = delImage.bucket;
                        var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                        if (imageBucket == envBucket) {
                            await delImage.delete().then(() => {
                                console.log("Old food photo deleted!")
                            }).catch((error) => {
                                console.log("ERR Food photo delete ===", error);
                            });
                        } else {
                            console.log('Bucket not matched for food photo deletion');
                        }
                    } catch (error) {
                        console.log("Error deleting food photo:", error);
                    }
                }));
            }
            
            // If no main photo is set and we have photos, use the first one
            if (mainPhoto === '' && newPhoto.length > 0) {
                mainPhoto = newPhoto[0];
                console.log('No main photo set, using first photo:', mainPhoto);
            }
            
            // Final safety check: ensure main photo exists in the photos array
            if (mainPhoto && !newPhoto.includes(mainPhoto)) {
                console.log('Main photo not found in photos array, using first available photo');
                if (newPhoto.length > 0) {
                    mainPhoto = newPhoto[0];
                } else {
                    mainPhoto = '';
                }
            }
            
            // IMPORTANT: Check if user has selected a different photo as main using the star button
            // Look for the image with the green star button (btn-success class)
            var selectedMainPhotoElement = $('.product_image .btn-success').closest('.image-item').find('img');
            if (selectedMainPhotoElement.length > 0) {
                var selectedMainPhotoSrc = selectedMainPhotoElement.attr('src');
                console.log('User selected main photo from UI:', selectedMainPhotoSrc);
                
                // If the selected photo exists in our newPhoto array, use it as main photo
                if (selectedMainPhotoSrc && newPhoto.includes(selectedMainPhotoSrc)) {
                    mainPhoto = selectedMainPhotoSrc;
                    console.log('User selected main photo preserved:', mainPhoto);
                } else if (selectedMainPhotoSrc && selectedMainPhotoSrc.startsWith('data:image')) {
                    // This is a new photo (base64) that was selected as main
                    // It should have been uploaded above and the mainPhoto should already be set correctly
                    console.log('Selected main photo is a new image (base64), should be handled by upload logic');
                }
            }
            
            console.log('Food image data processed, returning:', newPhoto.length, 'photos, main photo:', mainPhoto);
            return { photos: newPhoto, mainPhoto: mainPhoto };
        }
        $("#product_image").resizeImg({
            callback: function(base64str) {
                console.log('New food image selected via resizeImg');
                var val = $('#product_image').val().toLowerCase();
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = $('#product_image').val().replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                productImagesCount++;
                photos_html = '<span class="image-item position-relative d-inline-block" id="photo_' + productImagesCount +
                    '"><span class="remove-btn position-absolute" style="top: 3px; right: 3px; z-index: 10; background: rgba(255,255,255,0.9); border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;" data-id="' + productImagesCount + '" data-img="' + base64str +
                    '" data-status="new"><i class="fa fa-remove" style="font-size: 10px; color: #dc3545;"></i></span><button type="button" class="btn btn-sm btn-outline-success position-absolute" style="top: 3px; right: 28px; z-index: 10; width: 20px; height: 20px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;" onclick="setAsMainPhoto(\'' + base64str + '\')" title="Set as main photo"><i class="fa fa-star"></i></button><img class="rounded" width="80px" height="80px" style="object-fit: cover;" src="' +
                    base64str + '"></span>'
                $(".product_image").append(photos_html);
                new_added_photos.push(base64str);
                new_added_photos_filename.push(filename);
                $("#product_image").val('');
                console.log('Food image added to new_added_photos array');
                
                // If this is the first photo (no existing main photo), automatically set it as main
                if (photo === '' && photos.length === 0 && new_added_photos.length === 1) {
                    setAsMainPhoto(base64str);
                    console.log('First photo automatically set as main photo');
                }
            }
        });
        $(document).on("click", ".remove-btn", function() {
            var id = $(this).attr('data-id');
            var photo_remove = $(this).attr('data-img');
            var status = $(this).attr('data-status');
            console.log('Removing food image:', {id: id, status: status, photo: photo_remove});
            
            // Check if this is the main photo being removed
            if (photo === photo_remove) {
                console.log('Removing main photo, need to set a new main photo');
                // If this is the main photo, set the first remaining photo as main
                var remainingPhotos = photos.filter(p => p !== photo_remove);
                var remainingNewPhotos = new_added_photos.filter(p => p !== photo_remove);
                
                if (remainingPhotos.length > 0) {
                    photo = remainingPhotos[0];
                    console.log('Set new main photo from existing photos:', photo);
                } else if (remainingNewPhotos.length > 0) {
                    photo = remainingNewPhotos[0];
                    console.log('Set new main photo from new photos:', photo);
                } else {
                    photo = '';
                    console.log('No photos remaining, cleared main photo');
                }
            }
            
            if (status == "old") {
                photosToDelete.push(firebase.storage().refFromURL(photo_remove));
                console.log('Added old photo to deletion queue');
            }
            
            $("#photo_" + id).remove();
            
            index = photos.indexOf(photo_remove);
            if (index > -1) {
                photos.splice(index, 1); // 2nd parameter means remove one item only
                console.log('Removed from photos array');
            }
            
            index = new_added_photos.indexOf(photo_remove);
            if (index > -1) {
                new_added_photos.splice(index, 1); // 2nd parameter means remove one item only
                new_added_photos_filename.splice(index, 1);
                console.log('Removed from new_added_photos array');
            }
        });
        $("#food_restaurant").change(function() {
            $("#attributes_div").show();
            $("#item_attribute_chosen").css({
                'width': '100%'
            });
            var selected_vendor = this.value;
        });
        function change_categories(selected_vendor) {
            restaurant_list.forEach((vendor) => {
                if (vendor.id == selected_vendor) {
                    $('#item_category').html('');
                    $('#item_category').append($('<option value="">{{ trans('lang.select_category') }}</option>'));
                    categories_list.forEach((data) => {
                        if (vendor.categoryID == data.id) {
                            $('#food_category').html($("<option></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        }
                    })
                }
            });
        }
        function handleVariantFileSelect(evt, vid) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var timestamp = Number(new Date());
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var filename = 'variant_' + vid + '_' + timestamp + '.' + ext;
                    variant_filename.push(filename);
                    variant_photos.push(filePayload);
                    variant_vIds.push(vid);
                    $('[id="variant_' + vid + '_image"]').empty();
                    $('[id="variant_' + vid + '_image"]').html('<img class="rounded" style="width:50px" src="' +
                        filePayload + '" onerror="this.onerror=null;this.src=\'' + placeholderImage +
                        '\'" alt="image"><i class="mdi mdi-delete" data-variant="' + vid + '" data-img="' +
                        filePayload + '" data-file="' + filename + '" data-status="new"></i>');
                    $('#upload_' + vid).attr('data-img', filePayload);
                    $('#upload_' + vid).attr('data-file', filename);
                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function storeVariantImageData() {
            var newPhoto = [];
            if (variant_photos.length > 0) {
                await Promise.all(variant_photos.map(async (variantPhoto, index) => {
                    variantPhoto = variantPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(variant_filename[index]).putString(
                        variantPhoto, 'base64', {
                            contentType: 'image/jpg'
                        });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    $('[id="variant_' + variant_vIds[index] + '_url"]').val(downloadURL);
                    newPhoto.push(downloadURL);
                }));
            }
            if (variantImageToDelete.length > 0) {
                await Promise.all(variantImageToDelete.map(async (delImage) => {
                    var delImageUrlRef = await storage.refFromURL(delImage);
                    imageBucket = delImageUrlRef.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if (imageBucket == envBucket) {
                        await delImageUrlRef.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }));
            }
            return newPhoto;
        }
        function selectAttribute(item_attribute = '') {
            variant_photos = [];
            variant_vIds = [];
            variant_filename = [];
            if (item_attribute) {
                var item_attribute = $.parseJSON(atob(item_attribute));
            }
            var html = '';
            $("#item_attribute").find('option:selected').each(function() {
                var $this = $(this);
                var selected_options = [];
                if (item_attribute) {
                    $.each(item_attribute.attributes, function(index, attribute) {
                        if ($this.val() == attribute.attribute_id) {
                            selected_options.push(attribute.attribute_options);
                        }
                    });
                }
                html += '<div class="row" id="attr_' + $this.val() + '">';
                html += '<div class="col-md-3">';
                html += '<label>' + $this.text() + '</label>';
                html += '</div>';
                html += '<div class="col-lg-9">';
                html += '<input type="text" class="form-control" id="attribute_options_' + $this.val() +
                    '" value="' + selected_options +
                    '" placeholder="Add attribute values" data-role="tagsinput" onchange="variants_update(\'' +
                    btoa(JSON.stringify(item_attribute)) + '\')">';
                html += '</div>';
                html += '</div>';
            });
            $("#item_attributes").html(html);
            $("#item_attributes input[data-role=tagsinput]").tagsinput();
            if ($("#item_attribute").val().length == 0) {
                $("#attributes").val('');
                $("#variants").val('');
                $("#item_variants").html('');
            }
        }
        function variants_update(item_attributeX = '') {
            if (item_attributeX) {
                var item_attributeX = $.parseJSON(atob(item_attributeX));
            }
            var html = '';
            var item_attribute = $("#item_attribute").map(function(idx, ele) {
                return $(ele).val();
            }).get();
            if (item_attribute.length > 0) {
                var attributes = [];
                var attributeSet = [];
                $.each(item_attribute, function(index, attribute) {
                    var attribute_options = $("#attribute_options_" + attribute).val();
                    if (attribute_options) {
                        var attribute_options = attribute_options.split(',');
                        attribute_options = $.map(attribute_options, function(value) {
                            return value.replace(/[^0-9a-zA-Z a]/g, '');
                        });
                        attributeSet.push(attribute_options);
                        attributes.push({
                            'attribute_id': attribute,
                            'attribute_options': attribute_options
                        });
                    }
                });
                $('#attributes').val(JSON.stringify(attributes));
                var variants = getCombinations(attributeSet);
                $('#variants').val(JSON.stringify(variants));
                if (attributeSet.length > 0) {
                    html += '<table class="table table-bordered">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th class="text-center"><span class="control-label">Variant</span></th>';
                    html += '<th class="text-center"><span class="control-label">Variant Price</span></th>';
                    html += '<th class="text-center"><span class="control-label">Variant Quantity</span></th>';
                    html += '<th class="text-center"><span class="control-label">Variant Image</span></th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    $.each(variants, function(index, variant) {
                        var variant_price = 1;
                        var variant_qty = -1;
                        var variant_image = variant_image_url = '';
                        if (item_attributeX) {
                            var variant_info = $.map(item_attributeX.variants, function(v, i) {
                                if (v.variant_sku == variant) {
                                    return v;
                                }
                            });
                            if (variant_info[0]) {
                                variant_price = variant_info[0].variant_price;
                                variant_qty = variant_info[0].variant_quantity;
                                if (variant_info[0].variant_image) {
                                    variant_image = '<img onerror="this.onerror=null;this.src=\'' +
                                        placeholderImage + '\'" class="rounded" style="width:50px" src="' +
                                        variant_info[0].variant_image +
                                        '" alt="image"><i class="mdi mdi-delete" data-variant="' + variant +
                                        '" data-status="old"></i>';
                                    variant_image_url = variant_info[0].variant_image;
                                }
                            }
                        }
                        html += '<tr>';
                        html += '<td><label for="" class="control-label">' + variant + '</label></td>';
                        html += '<td>';
                        html += '<input type="number" id="price_' + variant + '" value="' + variant_price +
                            '" min="0" class="form-control">';
                        html += '</td>';
                        html += '<td>';
                        html += '<input type="number" id="qty_' + variant + '" value="' + variant_qty +
                            '" min="-1" class="form-control">';
                        html += '</td>';
                        html += '<td>';
                        html += '<div class="variant-image">';
                        html += '<div class="upload">';
                        html += '<div class="image" id="variant_' + variant + '_image">' + variant_image + '</div>';
                        html += '<div class="icon"><i class="mdi mdi-cloud-upload" data-variant="' + variant +
                            '" id="upload_' + variant + '"></i></div>';
                        html += '</div>';
                        html += '<div id="variant_' + variant + '_process"></div>';
                        html += '<div class="input-file">';
                        html += '<input type="file" id="file_' + variant +
                            '" onChange="handleVariantFileSelect(event,\'' + variant +
                            '\')" class="form-control" style="display:none;">';
                        html += '<input type="hidden" id="variant_' + variant + '_url" value="' +
                            variant_image_url + '">';
                        html += '</div>';
                        html += '</div>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody>';
                    html += '</table>';
                }
            }
            $("#item_variants").html(html);
        }
        function getCombinations(arr) {
            if (arr.length) {
                if (arr.length == 1) {
                    return arr[0];
                } else {
                    var result = [];
                    var allCasesOfRest = getCombinations(arr.slice(1));
                    for (var i = 0; i < allCasesOfRest.length; i++) {
                        for (var j = 0; j < arr[0].length; j++) {
                            result.push(arr[0][j] + '-' + allCasesOfRest[i]);
                        }
                    }
                    return result;
                }
            }
        }
        function uniqid(prefix = "", random = false) {
            const sec = Date.now() * 1000 + Math.random() * 1000;
            const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
            return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}` : ""}`;
        }
        function addProductSpecificationFunction() {
            $("#add_product_specification_div").show();
            $(".save_product_specification_btn").show();
        }
        function saveProductSpecificationFunction() {
            var optionlabel = $(".add_label").val();
            var optionvalue = $(".add_value").val();
            $(".add_label").val('');
            $(".add_value").val('');
            if (optionlabel != '' && optionvalue != '') {
                if (product_specification == null) {
                    product_specification = {};
                }
                product_specification[optionlabel] = optionvalue;
                $(".product_specification").append('<div class="row add_product_specification_iteam_' + optionlabel +
                    '" style="margin-top:5px;" id="add_product_specification_iteam_' + optionlabel +
                    '"><div class="col-5"><input class="form-control" type="text" value="' + optionlabel +
                    '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                    optionvalue +
                    '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' +
                    optionlabel + '")><span class="fa fa-trash"></span></button></div></div>');
            } else {
                alert("Please enter Label and Value");
            }
        }
        function deleteProductSpecificationSingle(index) {
            delete product_specification[index];
            $("#add_product_specification_iteam_" + index).hide();
        }
        async function getVendorId(vendorUser) {
            await database.collection('vendors').where('id', "==", vendorUser).get().then(async function(
                vendorSnapshots) {
                var vendorData = vendorSnapshots.docs[0].data();
                if (commisionModel) {
                    if (vendorData.hasOwnProperty('adminCommission')) {
                        var commission_type = vendorData.adminCommission.commissionType;
                        var commission_value = vendorData.adminCommission.fix_commission;
                        if (commission_type == "Percent") {
                            var commission_text = commission_value + '%';
                        } else {
                            if (currencyAtRight) {
                                commission_text = parseFloat(commission_value).toFixed(
                                    decimal_degits) + "" + currentCurrency;
                            } else {
                                commission_text = currentCurrency + "" + parseFloat(
                                    commission_value).toFixed(decimal_degits);
                            }
                        }
                        $("#admin_commision_info").show();
                        $("#admin_commision").html('Admin Commission: ' + commission_text);
                    }
                }
            })
        }
        
// Category search and multi-select tag functionality for food categories
$(document).ready(function() {
    // 1. Filter dropdown options based on search
    $('#food_category_search').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        $('#food_category option').each(function() {
            if ($(this).val() === "") {
                $(this).show();
                return;
            }
            var text = $(this).text().toLowerCase();
            if (text.indexOf(search) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 2. When selecting from dropdown, add tag (multi-select support)
    $('#food_category').on('change', function() {
        updateSelectedFoodCategoryTags();
    });

    // 3. Remove tag and unselect in dropdown
    $('#selected_categories').on('click', '.remove-tag', function() {
        var value = $(this).parent().data('value');
        $('#food_category option[value="' + value + '"]').prop('selected', false);
        updateSelectedFoodCategoryTags();
    });
});
 // 4. Update tags display
 function updateSelectedFoodCategoryTags() {
        var selected = $('#food_category').val() || [];
        var html = '';
        $('#food_category option:selected').each(function() {
            if ($(this).val() !== "") {
                html += '<span class="selected-category-tag" data-value="' + $(this).val() + '">' +
                    $(this).text() +
                    '<span class="remove-tag">&times;</span></span>';
            }
        });
        $('#selected_categories').html(html);
    }
    </script>
@endsection
