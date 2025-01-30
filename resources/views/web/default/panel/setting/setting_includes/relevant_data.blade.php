@php

    $user = auth()->user();
    $student = $user->student;
@endphp

{{-- Relatives --}}
<section>
    <h2 class="section-title after-line">معلومات الأقرباء في حال الطوارئ</h2>
    <section class="main-container border border-2 border-secondary-subtle rounded p-3 mt-2 mb-25 row mx-0">
        <div class="form-group col-12 col-sm-6">
            <label for="referral_person">{{ trans('application_form.referral_name') }}<span
                    class="text-danger">*</span></label>
            <input type="text" id="referral_person" name="referral_person"
                value="{{ old('referral_person', $student ? $student->referral_person : '') }}"
                placeholder="أدخل الأسم الثنائي" required
                class="form-control  @error('referral_person') is-invalid @enderror">

            @error('referral_person')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="form-group col-12 col-sm-6">
            <label for="relation">{{ trans('application_form.referral_state') }}<span
                    class="text-danger">*</span></label>
            <input type="text" id="relation" name="relation"
                value="{{ old('relation', $student ? $student->relation : '') }}" placeholder="أدخل صلة القرابة"
                required class="form-control  @error('relation') is-invalid @enderror">

            @error('relation')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-group col-12 col-sm-6">
            <label for="referral_email">{{ trans('application_form.email') }}<span class="text-danger">*</span></label>
            <input type="email" id="referral_email" name="referral_email"
                value="{{ old('referral_email', $student ? $student->referral_email : '') }}"
                placeholder="أدخل بريد الكتروني" required
                class="form-control  @error('referral_email') is-invalid @enderror">


            @error('referral_email')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="form-group col-12 col-sm-6">
            <label>{{ trans('application_form.phone') }}<span class="text-danger">*</span></label>
            <input type="tel" id="referral_phone" placeholder="أدخل جوال" name="referral_phone"
                value="{{ old('referral_phone', $student ? $student->referral_phone : '') }}"
                class="form-control  @error('referral_phone') is-invalid @enderror">

            @error('referral_phone')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </section>
</section>
