@include('auth.default')
<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$countries = (array) $countries;
$newcountries = array();
$newcountriesjs = array();
foreach ($countries as $keycountry => $valuecountry) {
    $newcountries[$valuecountry->phoneCode] = $valuecountry;
    $newcountriesjs[$valuecountry->phoneCode] = $valuecountry->code;
}
$phoneNumber = request('phoneNumber');
$selectedCountryCode = '';
$phone = '';
if ($phoneNumber) {
    foreach ($newcountries as $code => $country) {
        if (strpos($phoneNumber, '+' . $country->phoneCode) === 0) {
            $selectedCountryCode = $code;
            $phone = substr($phoneNumber, strlen('+' . $country->phoneCode));
            break;
        }
    }
}
?>
<div class="container">
    <div class="row page-titles ">
        <div class="col-md-12 align-self-center text-center">
            <h3 class="text-themecolor">{{ trans('lang.sign_up_with_us') }}</h3>
        </div>
        <div class="card-body">
            <div id="data-table_processing" class="page-overlay" style="display:none;">
                <div class="overlay-text">
                    <img src="{{asset('images/spinner.gif')}}">
                </div>
            </div>
            <div class="error_top"></div>
            <div class="alert alert-success" style="display:none;"></div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.owner_details') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.first_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control user_first_name" required placeholder="{{ trans('lang.user_first_name_help') }}"
                                    value="{{ request('firstName') ? request('firstName') : '' }}">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.last_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control user_last_name" placeholder="{{ trans('lang.user_last_name_help') }}"
                                    value="{{ request('lastName') && request('lastName') != 'undefined' ? request('lastName') : '' }}">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.email') }}</label>
                            <div class="col-7">
                                <input type="email" class="form-control user_email" required placeholder="{{ trans('lang.user_email_help') }}"
                                       value="{{ request('email') ? request('email') : '' }}"  @if(request('loginType') === 'social') disabled @endif>
                            </div>
                        </div>
                        <div class="form-group form-material row width-50">
                            <label class="col-3 control-label">{{ trans('lang.user_phone') }}</label>
                            <div class="col-12">
                                <div class="phone-box position-relative" id="phone-box"> 
                                <select name="country" id="country_selector" class="form-control" @if(request('loginType') === 'phone') disabled @endif>
                                    @foreach($newcountries as $keycy => $valuecy)
                                        <option code="{{ $valuecy->code }}" value="{{ $keycy }}"
                                            @if($selectedCountryCode == $keycy) selected @endif>
                                            +{{ $valuecy->phoneCode }} {{ $valuecy->countryName }}
                                        </option>
                                    @endforeach
                                </select>
                                <input class="form-control mt-2" placeholder="{{ trans('lang.user_phone') }}" id="phone" type="text"
                                    name="phone" value="{{ $phone }}" required autocomplete="phone" @if(request('loginType') === 'phone') disabled @endif autofocus>
                                <div id="error2" class="err"></div>
                                </div>
                            </div>
                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center">
            <button type="button" class="btn btn-primary create_restaurant_btn"><i class="fa fa-save"></i>
                {{ trans('lang.save') }}
            </button>
            <div class="or-line mb-4">
                <span>OR</span>
            </div>
            <a href="{{ route('login') }}">
                <p class="text-center m-0">{{ trans('lang.already_an_account') }} {{ trans('lang.sign_in') }}</p>
            </a>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-firestore.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-storage.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-database.js"></script>
<script src="{{ asset('js/crypto-js.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}"></script>
<script src="{{ asset('js/jquery.validate.js') }}"></script>
<script>
    var database = firebase.firestore();
    var storageRef = firebase.storage().ref('images');
    var createdAt = firebase.firestore.Timestamp.fromDate(new Date());
    var autoAprroveRestaurant = database.collection('settings').doc("restaurant");
    var adminEmail = '';
    var emailSetting = database.collection('settings').doc('emailSetting');
    var email_templates = database.collection('email_templates').where('type', '==', 'new_vendor_signup');
    var emailTemplatesData = null;
    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);
    var isSubmitting = false;
    
    function resetLoadingState() {
        isSubmitting = false;
        jQuery("#data-table_processing").hide();
        $(".error_top").hide();
        $(".alert-success").hide();
    }

    $(window).on('beforeunload', function() {
        if (isSubmitting) {
            resetLoadingState();
        }
    });

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/flags/120/";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
        }
    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/flags/120/"
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".png");
        return $state;
    }
    $(document).ready(async function () {
        jQuery("#data-table_processing").show();
        await email_templates.get().then(async function (snapshots) {
            emailTemplatesData = snapshots.docs[0].data();
        });
        await emailSetting.get().then(async function (snapshots) {
            var emailSettingData = snapshots.data();
            adminEmail = emailSettingData.userName;
        });
        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });
        jQuery("#data-table_processing").hide();
    });
    $(".create_restaurant_btn").click(async function () {
        if (isSubmitting) {
            return;
        }
        
        try {
            isSubmitting = true;
            $(".error_top").hide();
            $(".alert-success").hide();
            jQuery("#data-table_processing").show();

            var userFirstName = $(".user_first_name").val();
            var userLastName = $(".user_last_name").val();
            var email = $(".user_email").val().toLowerCase();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var phone = $('#phone').val().trim();
            var countryCode = $('#country_selector').val().trim();
            var userPhone = '+' + countryCode + phone;
            var user_id = "{{request('uuid')}}";

            if (!userFirstName) {
                throw new Error("{{trans('lang.enter_owners_name_error')}}");
            }
            if (!userLastName) {
                throw new Error("{{trans('lang.enter_owners_last_name_error')}}");
            }
            if (!email) {
                throw new Error("{{trans('lang.enter_owners_email')}}");
            }
            if (!emailRegex.test(email)) {
                throw new Error("{{trans('lang.enter_owners_email_error')}}");
            }
            if (!userPhone || !phone) {
                throw new Error("{{trans('lang.enter_owners_phone')}}");
            }
            if (!user_id) {
                throw new Error("User not found.");
            }

            const [docVerifySnapshot, autoApproveSnapshot] = await Promise.all([
                database.collection('settings').doc("document_verification_settings").get(),
                autoAprroveRestaurant.get()
            ]);

            var documentVerificationEnable = false;
            if (docVerifySnapshot.exists) {
                documentVerificationEnable = docVerifySnapshot.data().isRestaurantVerification || false;
            }

            var restaurant_active = false;
            if (autoApproveSnapshot.exists) {
                restaurant_active = autoApproveSnapshot.data().auto_approve_restaurant === true;
            }

            var loginType = "{{request('loginType','')}}";
            var provider = loginType == "google" ? "google" : "email";

            await database.collection('users').doc(user_id).set({
                'appIdentifier': "web",
                'isDocumentVerify': false,
                'firstName': userFirstName,
                'lastName': userLastName,
                'email': email,
                'countryCode': countryCode,
                'phoneNumber': phone,
                'role': 'vendor',
                'id': user_id,
                'active': restaurant_active,
                'createdAt': createdAt,
                'provider': provider
            });

            if (emailTemplatesData) {
                try {
                    var formattedDate = new Date().toLocaleDateString('en-GB');
                    var message = emailTemplatesData.message
                        .replace(/{userid}/g, user_id)
                        .replace(/{username}/g, userFirstName + ' ' + userLastName)
                        .replace(/{useremail}/g, email)
                        .replace(/{userphone}/g, userPhone)
                        .replace(/{date}/g, formattedDate);

                    await sendEmail(
                        "{{url('send-email')}}", 
                        emailTemplatesData.subject, 
                        message, 
                        [adminEmail]
                    );
                } catch (emailError) {
                    console.warn('Email sending failed:', emailError);
                }
            }

            if (!restaurant_active) {
                $(".alert-success").html("{{trans('lang.signup_waiting_approval')}}").show();
                resetLoadingState();
                setTimeout(() => {
                    if (isSubmitting) {
                        window.location.href = '{{ route("login")}}';
                    }
                }, 8000);
            } else {
                const response = await $.ajax({
                    type: 'POST',
                    url: "{{route('setToken')}}",
                    data: {
                        id: user_id,
                        userId: user_id,
                        email: email,
                        password: '',
                        firstName: userFirstName,
                        lastName: userLastName,
                        profilePicture: ''
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (response.access) {
                    resetLoadingState();
                    window.location = "{{ route('subscription-plan.show') }}";
                } else {
                    throw new Error("Failed to set token.");
                }
            }

        } catch (error) {
            $(".error_top").html(error.message || "An unexpected error occurred").show();
            window.scrollTo(0, 0);
            resetLoadingState();
        }
    });

    async function sendEmail(url, subject, message, recipients) {
        try {
            const response = await $.ajax({
                type: 'POST',
                data: { subject, message, recipients },
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            return true;
        } catch (error) {
            console.error('Email sending failed:', error);
            return false;
        }
    }

    $('#phone').on('keypress',function(event){
        if (!(event.which >= 48 && event.which <= 57)) {
            document.getElementById('error2').innerHTML = "Accept only Number";
            return false;
        }
        document.getElementById('error2').innerHTML = "";
        return true;
    });

    function validateForm() {
        const phoneRegex = /^\+[1-9]\d{1,14}$/;
        if (!phoneRegex.test(userPhone)) {
            throw new Error("Invalid phone number format");
        }
        // Add more validations
    }

    function showSuccess(message) {
        $(".alert-success").html(message).fadeIn();
        setTimeout(() => {
            $(".alert-success").fadeOut();
        }, 5000);
    }
</script>
