<style>
    .service-card .img-cover {
        width: 150px;
    }
</style>
@isset($service)
    <div
        class="module-box dashboard-stats rounded-sm panel-shadow py-30 d-flex align-items-center justify-content-center mt-0 h-100 w-100">
        <div class="d-flex flex-column service-card px-20 text-center" style="align-items: center;">
            <img src="{{ asset('assets/default/img/img.png') }}" class="img-cover" alt="anas academy">

            @isset($service->title)
                <h1 class="text-secondary font-weight-bold text-center pb-10 ">
                    {{ $service->title }}
                </h1>
            @endisset

            @isset($service->description)
                <p class="text-gray font-weight-500 font-16 mb-5">
                    {{ $service->description }}
                </p>
            @endisset

            @isset($service->price)
                <p class="text-dark font-weight-bold">

                    @if ($service->price > 0)
                        {{ $service->price }} ريال سعودي
                    {{-- @else
                        <span class="text-danger">هذة الخدمه مجانيه</span> --}}
                    @endif
                </p>

            @endisset

            @isset($service->apply_link)
                <a target="_self" rel="noopener noreferrer" class="btn btn-primary mt-10 px-50" style=""
                    href="{{ $service->apply_link }}">
                    تقديم طلب
                </a>
            @endisset


            {{-- @isset($service->review_link)
                <a target="_self" rel="noopener noreferrer" class="mt-10 text-decoration-underline font-weight-500"
                    style="" href="{{ $service->review_link }}">
                    مراجعة طلب سابق
                </a>
            @endisset --}}
        </div>
    </div>
@endisset
