@extends('web.default.layouts.email')

@section('body')
    <td class="social-title pb30"
        style="color:#ffffff; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:right; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: right;">
                {{-- <b style="color:#5E0A83"> عنوان البطاقة</b>: --}}
                {{ trans('auth.verify_your_email_address') }}
            </p>

            <p style="font-family: cairo, sans-serif; direction: rtl !important; text-align: right;">
            <div class="alert alert-success" role="alert">
                {{ trans('auth.verification_link_has_been_sent_to_your_email') }}
            </div>
            <a href="{{ url('/reset-password/' . $token . '?email=' . $email) }}">{{ trans('auth.click_here') }}</a>

            </p>
        </div>
    </td>
@endsection
