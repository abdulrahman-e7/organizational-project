@php
    $countries = [
        'السعودية',
        'الامارات العربية المتحدة',
        'الاردن',
        'البحرين',
        'الجزائر',
        'العراق',
        'المغرب',
        'اليمن',
        'السودان',
        'الصومال',
        'الكويت',
        'جنوب السودان',
        'سوريا',
        'لبنان',
        'مصر',
        'تونس',
        'فلسطين',
        'جزرالقمر',
        'جيبوتي',
        'عمان',
        'موريتانيا',
    ];

    $nationalities = [
        ' سعودي/ة',
        'اماراتي/ة',
        'اردني/ة',
        'بحريني/ة',
        'جزائري/ة',
        'عراقي/ة',
        'مغربي/ة',
        'يمني/ة',
        'سوداني/ة',
        'صومالي/ة',
        'كويتي/ة',
        'سوري/ة',
        'لبناني/ة',
        'مصري/ة',
        'تونسي/ة',
        'فلسطيني/ة',
        'جيبوتي/ة',
        'عماني/ة',
        'موريتاني/ة',
        'قطري/ة',
    ];

    $user = auth()->user();
    $student = $user->student;
@endphp

<section>
    <h2 class="section-title after-line">بيانات شخصية</h2>

    {{-- personal details --}}
    <section class="row mt-20 container">
        <section class="main-container border border-2 border-secondary-subtle rounded p-3 mt-2 mb-25 row mx-0">
            {{-- arabic name --}}
            <div class="form-group col-12 col-sm-6">
                <label for="name">{{ trans('application_form.name') }}<span class="text-danger">*</span></label>
                <input @if(!session()->has('impersonated')) disabled @endif type="text" id="name" name="ar_name" {{-- value="{{ $student ? $student->ar_name : '' }}" --}}
                    value="{{ old('ar_name', $student ? $student->ar_name : $user->full_name ?? '') }}"
                    placeholder="ادخل الإسم باللغه العربية فقط" required
                    class="form-control @error('ar_name') is-invalid @enderror">

                @error('ar_name')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- english name --}}
            <div class="form-group col-12 col-sm-6">
                <label for="name_en">{{ trans('application_form.name_en') }}<span class="text-danger">*</span></label>
                <input @if(!session()->has('impersonated')) disabled @endif type="text" id="name_en" name="en_name" {{-- value="{{ $student ? $student->en_name : '' }}" --}}
                    value="{{ old('en_name', $student ? $student->en_name : '') }}"
                    placeholder="ادخل الإسم باللغه الإنجليزيه فقط" required
                    class="form-control @error('en_name') is-invalid @enderror">

                @error('en_name')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- identifier number --}}
            <div class="form-group col-12 col-sm-6">
                <label for="identifier_num">رقم الهوية الوطنية أو جواز السفر <span class="text-danger">*</span></label>
                <input type="text" id="identifier_num" name="identifier_num" {{-- value="{{ $student ? $student->identifier_num : '' }}" --}}
                    value="{{ old('identifier_num', $student ? $student->identifier_num : '') }}"
                    placeholder="الرجاء إدخال الرقم كامًلا والمكون من 10 أرقام للهوية أو 6 أرقام للجواز" required
                    class="form-control  @error('identifier_num') is-invalid @enderror">

                @error('identifier_num')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- birthday --}}
            <div class="form-group col-12 col-sm-6">
                <label for="birthday">{{ trans('application_form.birthday') }}<span class="text-danger">*</span></label>
                <input type="date" id="birthday" name="birthdate" {{-- value="{{ $student ? $student->birthdate : '' }}" --}}
                    value="{{ old('birthdate', $student ? $student->birthdate : '') }}" required
                    class="form-control @error('birthdate') is-invalid @enderror">
                @error('birthdate')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- nationality --}}
            <div class="form-group col-12 col-sm-6">
                <label for="nationality">{{ trans('application_form.nationality') }}<span
                        class="text-danger">*</span></label>

                <select id="nationality" name="nationality" required
                    class="form-control  @error('nationality') is-invalid @enderror" onchange="toggleNationality()">
                    <option value="" class="placeholder" disabled>
                        اختر جنسيتك</option>
                    @foreach ($nationalities as $nationality)
                        <option value="{{ $nationality }}"
                            {{ old('nationality', $student->nationality ?? null) == $nationality ? 'selected' : '' }}>
                            {{ $nationality }}</option>
                    @endforeach
                    <option value="اخرى" id="anotherNationality"
                        {{ old('nationality') != '' && !in_array(old('nationality'), $nationalities) ? 'selected' : '' }}>
                        اخرى</option>
                </select>
                @error('nationality')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- other nationality --}}
            <div class="form-group col-12 col-sm-6" id="other_nationality_section" style="display: none">
                <label for="nationality">ادخل الجنسية <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                    id="other_nationality" name="" placeholder="اكتب الجنسية" {{-- value="{{ $student ? $student->other_nationality : '' }}" --}}
                    value="{{ old('nationality', $student ? $student->other_nationality : '') }}"
                    onkeyup="setNationality()">

                @error('nationality')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- gender --}}
            <div class="form-group col-12 col-sm-6">
                <label for="gender">{{ trans('application_form.gender') }}<span class="text-danger">*</span></label>

                @error('gender')
                    <div class="invalid-feedback d-inline">
                        {{ $message }}
                    </div>
                @enderror

                <div class="row mr-5 mt-5">
                    {{-- female --}}
                    <div class="col-sm-4 col">
                        <label for="female">
                            <input type="radio" id="female" name="gender" value="female"
                                class=" @error('gender') is-invalid @enderror" required
                                {{ old('gender', $student->gender ?? null) == 'female' ? 'checked' : '' }}>
                            انثي
                        </label>
                    </div>

                    {{-- male --}}
                    <div class="col">
                        <label for="male">
                            <input type="radio" id="male" name="gender" value="male"
                                class=" @error('gender') is-invalid @enderror" required
                                {{ old('gender', $student->gender ?? null) == 'male' ? 'checked' : '' }}>
                            ذكر
                        </label>
                    </div>
                </div>
            </div>

            {{-- country --}}
            <div class="form-group col-12 col-sm-6">
                <label for="country">{{ trans('application_form.country') }}<span class="text-danger">*</span></label>

                <select id="mySelect" name="country" required
                    class="form-control @error('country') is-invalid @enderror" onchange="toggleHiddenInputs()">
                    <option value="" class="placeholder" disabled="">اختر دولتك</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}"
                            {{ old('country', $student->country ?? null) == $country ? 'selected' : '' }}>
                            {{ $country }}</option>
                    @endforeach
                    <option value="اخرى" id="anotherCountry"
                        {{ !empty($student->country) && !in_array($student->country, $countries) ? 'selected' : '' }}>
                        اخرى</option>

                </select>

                @error('country')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- other country --}}
            <div class="form-group col-12 col-sm-6" id="anotherCountrySection" style="display: none">
                <label for="city" class="form-label">ادخل البلد<span class="text-danger">*</span></label>
                <input type="text" id="city" name="city"
                    class="form-control  @error('city') is-invalid @enderror" placeholder="ادخل دولتك"
                    value="{{ old('city', $student ? $student->city : '') }}" onkeyup="setCountry()">

                @error('city')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- region --}}
            <div class="form-group col-12 col-sm-6" id="region" style="display: none">
                <label for="area" class="form-label">المنطقة<span class="text-danger">*</span></label>
                <input type="text" id="area" name="area"
                    class="form-control  @error('area') is-invalid @enderror" placeholder="اكتب المنطقة"
                    value="{{ old('area', $student ? $student->area : '') }}">

                @error('area')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- city --}}
            <div class="form-group col-12 col-sm-6">
                <div id="cityContainer">
                    <label for="town" id="cityLabel">{{ trans('application_form.city') }}<span
                            class="text-danger">*</span></label>
                    <input type="text" id="town" name="town" placeholder="اكتب مدينه السكن الحاليه"
                        value="{{ old('town', $student ? $student->town : '') }}" required
                        class="form-control @error('town') is-invalid @enderror">
                </div>
                @error('town')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- identity_img input --}}
            <div class="form-group col-12 col-sm-6">
                <div>
                    <label for="identity_img">صورة الهوية الوطنية/جواز السفر</label>
                    <input type="file" id="identity_img" name="identity_img"
                    accept=".jpeg,.jpg,.png"
                        value="{{ old('identity_img', $student ? $student->identity_img : '') }}"
                        class="form-control @error('identity_img') is-invalid @enderror">
                </div>
                @error('identity_img')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            {{-- identity_img display --}}
            <div>
                @if ($student->identity_img)
                    <a href="/store/{{$student->identity_img }}"  target="_blank">
                        <img src="/store/{{$student->identity_img }}" alt="image" width="100px" style="max-height:100px">
                    </a>
                    @endif
            </div>

        </section>
    </section>

</section>

{{-- nationality toggle --}}
<script>
    function toggleNationality() {
        let other_nationality_section = document.getElementById("other_nationality_section");
        let nationality = document.getElementById("nationality");
        let other_nationality = document.getElementById("other_nationality");
        let anotherNationalityOption = document.getElementById("anotherNationality");
        if (nationality && nationality.value == "اخرى") {
            other_nationality_section.style.display = "block";

            // nationality.value = other_nationality.value;
            anotherNationalityOption.value = other_nationality.value;
        } else {
            other_nationality_section.style.display = "none";
            anotherNationalityOption.value = "اخرى";
        }
    }

    function setNationality() {
        let other_nationality_section = document.getElementById("other_nationality_section");
        let nationality = document.getElementById("nationality");
        let other_nationality = document.getElementById("other_nationality");
        let anotherNationalityOption = document.getElementById("anotherNationality");
        if (other_nationality_section.style.display != "none") {
            // nationality.value = other_nationality.value;
            anotherNationalityOption.value = other_nationality.value;

        }
    }
</script>

{{-- city and country toggle --}}
<script>
    function toggleHiddenInputs() {
        var select = document.getElementById("mySelect");
        var hiddenInput = document.getElementById("area");
        var hiddenLabel = document.getElementById("hiddenLabel");
        var hiddenInput2 = document.getElementById("city");
        var hiddenLabel2 = document.getElementById("hiddenLabel2");
        var cityLabel = document.getElementById("cityLabel");
        var town = document.getElementById("town");
        var anotherCountrySection = document.getElementById("anotherCountrySection");
        var region = document.getElementById("region");
        let anotherCountryOption = document.getElementById("anotherCountry");

        if (select && select.value !== "السعودية") {
            region.style.display = "block";
        } else {
            region.style.display = "none";
        }

        if (select.value === "اخرى") {
            anotherCountrySection.style.display = "block";
            anotherCountryOption.value = hiddenInput2.value;
        } else {
            anotherCountrySection.style.display = "none";
            anotherCountryOption.value = "اخرى";

        }
        if (select && cityLabel && town) {
            if (select.value === "السعودية") {
                town.outerHTML = '<select id="town" name="town"  class="form-control" required>' +
                    '<option value="الرياض" selected="selected">الرياض</option>' +
                    '<option value="جده">جده </option>' +
                    '<option value="مكة المكرمة">مكة المكرمة</option>' +
                    '<option value="المدينة المنورة">المدينة المنورة</option>' +
                    '<option value="الدمام">الدمام</option>' +
                    '<option value="الطائف">الطائف</option>' +
                    '<option value="تبوك">تبوك</option>' +
                    '<option value="الخرج">الخرج</option>' +
                    '<option value="بريدة">بريدة</option>' +
                    '<option value="خميس مشيط">خميس مشيط</option>' +
                    '<option value="الهفوف">الهفوف</option>' +
                    '<option value="المبرز">المبرز</option>' +
                    '<option value="حفر الباطن">حفر الباطن</option>' +
                    '<option value="حائل">حائل</option>' +
                    '<option value="نجران">نجران</option>' +
                    '<option value="الجبيل">الجبيل</option>' +
                    '<option value="أبها">أبها</option>' +
                    '<option value="ينبع">ينبع</option>' +
                    '<option value="الخبر">الخبر</option>' +
                    '<option value="عنيزة">عنيزة</option>' +
                    '<option value="عرعر">عرعر</option>' +
                    '<option value="سكاكا">سكاكا</option>' +
                    '<option value="جازان">جازان</option>' +
                    '<option value="القريات">القريات</option>' +
                    '<option value="الظهران">الظهران</option>' +
                    '<option value="القطيف">القطيف</option>' +
                    '<option value="الباحة">الباحة</option>' +
                    '</select>';
            } else {

                town.outerHTML =
                    `<input type="text" id="town" name="town" placeholder="اكتب مدينه السكن الحاليه" class="form-control" value="{{ old('town', $student ? $student->town : '') }}" >`;
            }
        }
    }


    function setCountry() {
        let anotherCountrySection = document.getElementById("anotherCountrySection");
        let anotherCountryOption = document.getElementById("anotherCountry");
        let another_country = document.getElementById("city");

        if (anotherCountrySection.style.display != "none") {
            // nationality.value = other_nationality.value;
            anotherCountryOption.value = another_country.value;

        }
    }
    toggleHiddenInputs();
</script>
