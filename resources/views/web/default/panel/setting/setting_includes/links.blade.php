<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center mb-10">
        <h2 class="section-title after-line">روابط الأعمال</h2>
        <button id="userAddLinks" type="button" class="btn btn-primary btn-sm">إضافة رابط</button>
    </div>

    <div id="userListLinks">

        @php
            $pattern = '/title:\s*(.*?),\s*year:\s*(\d+)/';
        @endphp
        @if(!empty($links) and !$links->isEmpty())
            @foreach($links as $link)

                <div class="row mt-20">
                    <div class="col-12">
                        <div class="link-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-white d-flex align-items-center justify-content-between">
                            <div class="col-10 text-secondary font-weight-500 text-left link-value" link-value="{{ $link->value }}" >
                                @if (preg_match($pattern, $link->value, $matches))
                                <div class="row">
                                    <p class="col-12 col-sm-6">
                                        مجال الخبرة: {{ $matches[1] }}
                                    </p>
                                    <p class="col-12 col-sm-6">
                                        عدد سنوات الخبرة: {{ $matches[2] }} سنوات
                                    </p>
                                </div>

                                @else
                                {{ $link->value }}
                                @endif
                            </div>
                            <div class="col-2 text-right">
                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20"></i>
                                    </button>
                                    <div class="dropdown-menu font-weight-normal">
                                        <button type="button" data-link-id="{{ $link->id }}" data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class="d-block btn-transparent edit-link">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/metas/{{ $link->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class="delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'exp.png',
                'title' => 'لا توجد روابط أعمال سابقة',
                'hint' => "
                 أرفق روابط اعمالك، بورتفوليو، بيهانس..... إلخ في نفس المجال المسجل بيه (ان وجد )
                ",
            ])
        @endif
    </div>

</section>

<div class="d-none" id="newLinkModal">
    <h3 class="section-title after-line">إضافة رابط جديد</h3>
    <div class="mt-20 text-center">
        <img src="/assets/default/img/info.png" width="108" height="96" class="rounded-circle" alt="">
        <h4 class="font-16 mt-20 text-dark-blue font-weight-bold">اضف الرابط في سطر واحد</h4>
        <span class="d-block mt-10 text-gray font-14">مثال: https://www.behance.net/gallery/206425103/</span>
        <div class="form-group mt-15 px-50">
            <input type="url" id="new_link_val" required class="form-control" placeholder=" ادخل عنوان الرابط هنا">
            <div class="invalid-feedback">{{ trans('validation.required',['attribute' => 'عنوان الرابط']) }}</div>
        </div>

    </div>

    <div class="mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveLink" class="btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
