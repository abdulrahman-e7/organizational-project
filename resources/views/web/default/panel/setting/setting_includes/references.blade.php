<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center mb-10">
        <h2 class="section-title after-line">المعرفون</h2>
        <button id="userAddReferences" type="button" class="btn btn-primary btn-sm">إضافة معرف</button>
    </div>

    <div id="userListReferences">

        @if (!empty($references) and !$references->isEmpty())
            @foreach ($references as $reference)
                <div class="row mt-20">
                    <div class="col-12">
                        <div
                            class="reference-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-white d-flex align-items-center justify-content-between">
                            <div class="col-10 text-secondary font-weight-500 text-left reference-value"
                                reference-value="{{ $reference }}">

                                <div>
                                    <p>الاسم: {{ $reference->name }}</p>
                                </div>

                                <div>
                                    <p>البريد الإلكتروني: {{ $reference->email }}</p>
                                </div>

                                <div>
                                    <p>المسمي الوظيفي: {{ $reference->job_title }}</p>
                                </div>
                                <div>
                                    <p>مكان العمل: {{ $reference->workplace }}</p>
                                </div>

                                <div>
                                    <p>طبيعة العلاقة: {{ $reference->relationship }}</p>
                                </div>

                            </div>
                            <div class="col-2 text-right">
                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20"></i>
                                    </button>
                                    <div class="dropdown-menu font-weight-normal">
                                        <button type="button" data-reference-id="{{ $reference->id }}"
                                            data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class="d-block btn-transparent edit-reference">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/references/{{ $reference->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class="delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            @include(getTemplate() . '.includes.no-result', [
                'file_name' => 'exp.png',
                'title' => 'لم يتم إضافة معروفون بعد',
                'hint' =>
                    'اذكر اثنين من المعرفين، يمكن أن يكون المعرف هو مدرسك في الجامعة أو مدير سابق أو حالي لك',
            ])
        @endif
    </div>

</section>

<div class="d-none" id="newReferenceModal">
    <h3 class="section-title after-line">إضافة معرف</h3>
    <div class="mt-20">
        {{-- <div  class="text-center">
            <img src="/assets/default/img/info.png" width="108" height="96" class="rounded-circle" alt="">
            <h4 class="font-16 mt-20 text-dark-blue font-weight-bold">{{ trans('site.new_reference_hint') }}</h4>
            <span class="d-block mt-10 text-gray font-14">{{ trans('site.new_reference_exam') }}</span>
        </div> --}}

        <div class="form-group mt-15 px-50">
            <label class="form-label text-left">
                الإسم
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_name" name ="name" required class="form-control"
                placeholder="اكتب اسم المعرف">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'اسم المعرف']) }}</div>
        </div>
        <div class="form-group mt-15 px-50">
            <label class="form-label text-left">
                البريد الإلكتروني
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_email" name="email" required class="form-control"
                placeholder="اكتب البريد الالكتروني ">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'البريد الالكتروني']) }}
            </div>
        </div>

        <div class="form-group mt-15 px-50">
            <label class="form-label text-left">
                المسمي الوظيفي
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_job_title" name="job_title" required class="form-control"
                placeholder="المسمي الوظيفي">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'المسمي الوظيفي']) }}</div>
        </div>

        <div class="form-group mt-15 px-50">
            <label class="form-label text-left">
                مكان العمل
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_workplace" name="workplace" required class="form-control"
                placeholder="مكان العمل">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'مكان العمل']) }}</div>
        </div>

        <div class="form-group mt-15 px-50">
            <label class="form-label text-left">
                طبيعة العلاقة
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_relationship" name="relationship" required class="form-control"
                placeholder="طبيعة العلاقة">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'طبيعة العلاقة']) }}</div>
        </div>


    </div>

    <div class="mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveReference" class="btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
