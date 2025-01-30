<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mixins\RegistrationBonus\RegistrationBonusAccounting;
use App\Models\Accounting;
use App\Models\Affiliate;
use App\Models\AffiliateCode;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\Code;
use App\Models\UserMeta;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Models\Category;
use App\Models\Webinar;
use App\Student;
use App\BundleStudent;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/apply';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {

        // return view("web.default.pages.registration_close");

        $seoSettings = getSeoMetas('register');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.register_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.register_page_title');
        $pageRobot = getPageRobot('register');

        $referralSettings = getReferralSettings();

        $referralCode = Cookie::get('referral_code');

        $categories = Category::whereNull('parent_id')->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('activeBundles')
                    ->orWhereHas('activeSubCategories', function ($query) {
                        $query->whereHas('activeBundles');
                    });
            })->get();

        $courses = Webinar::where('unattached', 1)->where('status', 'active')->get();
        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'referralCode' => $referralCode,
            'referralSettings' => $referralSettings,
            'categories'  => $categories,
            'courses'  => $courses,

        ];

        return view(getTemplate() . '.auth.register', $data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        if (!empty($data['mobile']) and !empty($data['country_code'])) {
            $data['mobile'] = $data['country_code'] . ' ' . ltrim($data['mobile'], '0');
        }

        $type = $data['type'];

        $rules = [
            'country_code' => ($registerMethod == 'mobile') ? 'required' : 'nullable',
            'mobile' => 'required|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'full_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:5',
            'en_name' => 'required|string|max:255|min:5',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|same:password',
            'email_confirmation' => 'required|same:email',
            'referral_code' => 'nullable|exists:affiliates_codes,code',
            'type' => 'required|in:courses,programs',
            'bundle_id' =>( ($type == 'programs') ? 'required' : "")  ,
            'webinar_id' => ( ($type == 'courses') ? 'required' : "" ) ,
        ];

        if (!empty(getGeneralSecuritySettings('captcha_for_register'))) {
            $rules['captcha'] = 'required|captcha';
        }

        $validator = Validator::make($data, $rules);
        $validator->setAttributeNames([
            'full_name' => __('validation.attributes.ar_name'),
            // Add other attribute names for other fields
        ]);
        return  $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return
     */
    public function create(array $data)
    {


        $referralSettings = getReferralSettings();
        $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));

        if (empty($data['timezone'])) {
            $data['timezone'] = getGeneralSettings('default_time_zone') ?? null;
        }

        if (!empty($data['mobile']) and !empty($data['country_code'])) {
            $data['mobile'] = $data['country_code'] . ' ' . ltrim($data['mobile'], '0');
        }

        $disableViewContentAfterUserRegister = getFeaturesSettings('disable_view_content_after_user_register');
        $accessContent = !((!empty($disableViewContentAfterUserRegister) and $disableViewContentAfterUserRegister));

        $roleName = Role::$user;
        $roleId = Role::getUserRoleId();

        if (!empty($data['account_type'])) {
            if ($data['account_type'] == Role::$teacher) {
                $roleName = Role::$teacher;
                $roleId = Role::getTeacherRoleId();
            } else if ($data['account_type'] == Role::$organization) {
                $roleName = Role::$organization;
                $roleId = Role::getOrganizationRoleId();
            }
        }

        $user = User::create([
            'role_name' => 'registered_user',
            'role_id' => 13,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'full_name' => $data['full_name'],
            'en_name' => $data['en_name'],
            'status' => User::$active,
            'access_content' => $accessContent,
            'password' => Hash::make($data['password']),
            'affiliate' => $usersAffiliateStatus,
            'timezone' => $data['timezone'] ?? null,
            'created_at' => time(),
            'verified' => 1,
            'application_type' => $data['type'],
            'program_id' => $data['type']== 'programs' ? $data['bundle_id'] : $data['webinar_id']
        ]);
        // }

        if (!empty($data['certificate_additional'])) {
            UserMeta::updateOrCreate([
                'user_id' => $user->id,
                'name' => 'certificate_additional'
            ], [
                'value' => $data['certificate_additional']
            ]);
        }

        return [$user, $data];
    }


    public function register(Request $request)
    {

        $this->validator($request->all())->validate();

        [$user, $data] = $this->create($request->all());

        // $student = Student::create([
        //     'user_id' => $user->id,
        //     'ar_name' => $data['full_name'],
        //     'email' => $data['email'],
        //     'phone' => $data['mobile'],
        //     'mobile' => $data['mobile'],
        //     'status' => 'pending'
        // ]);

        // $studentBundle = BundleStudent::create(['student_id' => $student->id, 'bundle_id' => $data['bundle_id'], 'status'=> 'applying']);

        event(new Registered($user));

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[u.role]' => trans("update.role_{$user->role_name}"),
            '[time.date]' => dateTimeFormat($user->created_at, 'j M Y H:i'),
        ];

        sendNotification("new_registration", $notifyOptions, 1);

        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        $value = $request->get($registerMethod);
        if ($registerMethod == 'mobile') {
            $value = $request->get('country_code') . ltrim($request->get('mobile'), '0');
        }

        $referralCode = $request->get('referral_code', null);
        if (!empty($referralCode)) {
            session()->put('referralCode', $referralCode);
        }

        // $verificationController = new VerificationController();
        // $checkConfirmed = $verificationController->checkConfirmed($user, $registerMethod, $value);

        $referralCode = $request->get('referral_code', null);

        // if ($checkConfirmed['status'] == 'send') {

        //     if (!empty($referralCode)) {
        //         session()->put('referralCode', $referralCode);
        //     }

        //     return redirect('/verification');
        // } elseif ($checkConfirmed['status'] == 'verified') {
        $this->guard()->login($user);

        $enableRegistrationBonus = false;
        $registrationBonusAmount = null;
        $registrationBonusSettings = getRegistrationBonusSettings();
        if (!empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) {
            $enableRegistrationBonus = true;
            $registrationBonusAmount = $registrationBonusSettings['registration_bonus_amount'];
        }


        $user->update([
            'enable_registration_bonus' => $enableRegistrationBonus,
            'registration_bonus_amount' => $registrationBonusAmount,
        ]);

        $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
        RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);

        if (!empty($referralCode)) {
            Affiliate::storeReferral($user, $referralCode);
        }

        $registrationBonusAccounting = new RegistrationBonusAccounting();
        $registrationBonusAccounting->storeRegistrationBonusInstantly($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }
        $data['title'] = "انشاء حساب جديد";
        $data['body'] = " تهانينا تم انشاء حساب لكم في اكاديما
                            <br>
                            <br>
                            يمكن تسجيل الدخول من خلال هذا الرابط
                            <a href='https://lms.anasacademy.uk/login' class='btn btn-danger'>اضغط هنا للدخول</a>
                            <br>
                            بإستخدام هذا البريد الإلكتروني وكلمة المرور
                            <br>
                            <span style='font-weight:bold;'>البريد الالكتروني: </span> $user->email
                            <br>
                             <span style='font-weight:bold;'>كلمة المرور: </span>" . $request['password'] .
            "<br>
                ";
        if (!empty($user) and !empty($user->email) and env('APP_ENV') == 'production') {
            Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'] ?? '', 'message' => $data['body'] ?? '']));
        }

        if(($request->type == 'courses') && !empty($request->webinar_id) ){
            return redirect("/webinars/$request->webinar_id/apply");
        }
        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect(($this->redirectPath() . "?type=$request->type&&bundle=$request->bundle_id&&webinar=$request->webinar_id"));
        // }
    }
}
