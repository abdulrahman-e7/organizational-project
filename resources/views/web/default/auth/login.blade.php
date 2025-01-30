@extends(getTemplate() . '.auth.auth_layout')

@section('content')

    @php
        $siteGeneralSettings = getGeneralSettings();
    @endphp
<style>
    .content {
        overflow: auto;
        display: flex;
        height: 100vh;
        justify-content: center;
        align-content: center;
        align-items: center;
        flex-direction: column;
        flex-wrap: wrap;
    }
    .cs-btn{
        background-color:#ED1088 !important;
    }
    .cs-btn:hover{
        background-color:#5F2B80 !important;
    }
    a:hover{
       text-decoration:underline;
        color:#ED1088 !important;
    }

    a, .registertext a {
        color:#5f2b80 !important;
    }
</style>
    <div class="p-md-4 m-md-3">
        <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto">
            <img src="{{ $siteGeneralSettings['logo'] ?? '' }}" alt="logo" width="100%" class="">
        </div>

        <h1 class="font-20 font-weight-bold mb-3"><svg width="34" height="29" viewBox="0 0 34 29" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                    stroke="#5E0A83" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            {{ trans('auth.login_h1') }}</h1>

        {{-- show messages --}}
        @if (!empty(session()->has('msg')))
            <div class="alert alert-info alert-dismissible fade show mt-30" role="alert">
                {{ session()->get('msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form method="POST" action="/login?{{  request()->getQueryString() }}" class="needs-validation" novalidate="">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <!--<label class="input-label" for="username">{{ trans('auth.email_or_mobile') }}:</label>-->
                <label class="input-label" for="username">البريد الإلكتروني </label>

                <input name="username" type="text" class="form-control @error('username') is-invalid @enderror"
                    id="username" value="{{ old('username') }}" aria-describedby="emailHelp">
                @error('username')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="input-label" for="password">{{ trans('auth.password') }}</label>
                <input name="password" type="password" class="form-control @error('password')  is-invalid @enderror"
                    id="password" aria-describedby="passwordHelp">

                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            @if (!empty(getGeneralSecuritySettings('captcha_for_login')))
                @include('web.default.includes.captcha_input')
            @endif


            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block cs-btn" tabindex="4">
                    {{ trans('auth.login') }}
                </button>
            </div>
            <div class="text-left forgetpw d-flex justify-content-around text-center text-secondary">
                <a href="/forget-password" target="_blank">نسيت كلمة المرور ؟</a>
                <span class="text-dark"> | </span>
                {{-- <span style="width: 2px; height: 22px;" class="bg-dark"></span> --}}
                <a href="https://anasacademy.uk/certificate/certificate-check.php" target="_blank">التحقق من الشهادات</a>
            </div>
            <div class="text-center mt-30 mb-50">
                <a href="https://support.anasacademy.uk/" target="_blank" >فريق الدعم والتواصل</a>

            </div>
        </form>

        @if (session()->has('login_failed_active_session'))
            <div class="d-flex align-items-center mt-20 p-15 danger-transparent-alert ">
                <div class="danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-alert-octagon">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                        </polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="ml-10 mr-10">
                    <div class="font-14 font-weight-bold ">
                        {{ session()->get('login_failed_active_session')['title'] }}</div>
                    <div class="font-12 ">{{ session()->get('login_failed_active_session')['msg'] }}</div>
                </div>
            </div>
        @endif

        <div class="mt-20 text-center registertext">
            <span>ليس لديك حساب ؟</span>
            <br>
            <a href="/register?{{  request()->getQueryString() }}" class="text-secondary font-weight-bold">{{ trans('auth.signup') }}</a>
        </div>
    </div>
@endsection
