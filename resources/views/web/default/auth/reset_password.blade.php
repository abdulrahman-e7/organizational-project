@extends(getTemplate() . '.auth.auth_layout')

@section('content')
    @php
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
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
    </style>

    <div class="row login-container p-md-4 m-md-3">
        <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto">
            <img src="{{ $siteGeneralSettings['logo'] ?? '' }}" alt="logo" width="100%" class="">
        </div>
        <div class="col-12">
            <div class="login-card">
                <h1 class="font-20 font-weight-bold">
                    <svg width="34" height="29" viewBox="0 0 34 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                            stroke="#5E0A83" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{ trans('auth.reset_password') }}
                </h1>

                <form method="post" action="/reset-password" class="mt-35">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label class="input-label" for="email">{{ trans('auth.email') }}:</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" value="{{ request()->get('email') }}" aria-describedby="emailHelp">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                        <input name="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" aria-describedby="passwordHelp">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="input-label" for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                        <input name="password_confirmation" type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm_password"
                            aria-describedby="confirmPasswordHelp">
                        @error('password_confirmation')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <input hidden name="token" placeholder="token" value="{{ $token }}">

                    <button type="submit"
                        class="btn btn-primary btn-block mt-20">{{ trans('auth.reset_password') }}</button>
                </form>

                <div class="text-center mt-20">
                    <span
                        class="badge badge-circle-gray300 text-secondary d-inline-flex align-items-center justify-content-center">or</span>
                </div>

                <div class="text-center mt-20">
                    <span class="text-secondary">
                        <a href="/panel" class="text-secondary font-weight-bold"> العودة للصفحة الرئيسية</a>
                    </span>
                </div>

            </div>
        </div>
    </div>
@endsection
