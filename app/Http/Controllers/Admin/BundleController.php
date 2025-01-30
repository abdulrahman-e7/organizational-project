<?php

namespace App\Http\Controllers\Admin;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\BundleBridging;
use App\Models\CertificateTemplate;
use App\Models\StudyClass;
use App\Models\Bundle;
use App\Models\BundleFilterOption;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Gift;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SpecialOffer;
use App\Models\StudentExceptionCertificate;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\Translation\BundleTranslation;
use App\Models\Webinar;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_bundles_list');

        removeContentLocale();

        // $query = Bundle::query();
        $type = $request->get('type', 'program');
        $query = Bundle::where('bundles.type', $type);

        $totalBundles = $query->count();
        $totalPendingBundles = deepClone($query)->where('bundles.status', Bundle::$pending)->count();
        $totalSales = deepClone($query)->join('sales', 'bundles.id', '=', 'sales.bundle_id')
            ->select(DB::raw('count(sales.bundle_id) as sales_count, sum(total_amount) as total_amount'))
            ->whereNotNull('sales.bundle_id')
            ->whereNull('sales.refund_at')
            ->first();

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->withCount([
                'bundleWebinars'
            ]);

        $bundles = $query->with('batch')->paginate(10);

        foreach ($bundles as $bundle) {
            $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = Sale::query()
                ->where(function ($query) use ($bundle, $giftsIds) {
                    $query->where('bundle_id', $bundle->id);
                    $query->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $bundle->sales = $sales;
        }

        $batches = StudyClass::get();

        $data = [
            'pageTitle' => ($type == 'bridging') ? trans('update.bridges') : trans('update.bundles'),
            'bundles' => $bundles,
            'batches' => $batches,
            'totalBundles' => $totalBundles,
            'totalPendingBundles' => $totalPendingBundles,
            'totalSales' => $totalSales,
            'categories' => $categories,
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return view('admin.bundles.lists', $data);
    }

    private function handleFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $teacher_ids = $request->get('teacher_ids', null);
        $category_id = $request->get('category_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort', null);
        $batch = $request->get('batch', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($teacher_ids) and count($teacher_ids)) {
            $query->whereIn('teacher_id', $teacher_ids);
        }

        if (!empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (!empty($status)) {
            $query->where('bundles.status', $status);
        }
        if (!empty($batch)) {
            $query->where('batch_id', $batch);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $bundleIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $bundleIdsHasDiscount[] = $ticket->bundle_id;
                        }
                    }

                    $specialOffersBundleIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('bundle_id')
                        ->toArray();

                    $bundleIdsHasDiscount = array_merge($specialOffersBundleIds, $bundleIdsHasDiscount);

                    $query->whereIn('id', $bundleIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.refund_at', DB::raw('count(sales.bundle_id) as sales_count'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.refund_at', DB::raw('count(sales.bundle_id) as sales_count'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('amounts', 'desc');
                    break;

                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'updated_at_asc':
                    $query->orderBy('updated_at', 'asc');
                    break;

                case 'updated_at_desc':
                    $query->orderBy('updated_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        return $query;
    }

    public function create(Request $request)
    {
        $this->authorize('admin_bundles_create');

        removeContentLocale();

        $type = $request->get('type', 'program');
        if (!in_array($type, ['program', 'bridging'])) {
            abort(404);
        }
        $categories = Category::where('parent_id', null)->get();
        $study_classes = StudyClass::get();
        $students = Student::get();
        $certificates = CertificateTemplate::where('type', 'bundle')->get();
        $data = [
            'pageTitle' => trans('update.new_bundle'),
            'categories' => $categories,
            'certificates' => $certificates,
            'study_classes' => $study_classes,
            'students' => $students,
        ];

        return view('admin.bundles.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_bundles_create');
        $type = $request->get('type', 'program');
        $rules = [
            'title' => 'required|max:255',
            'bundle_name_certificate' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            // 'slug' => 'max:255|unique:bundles,slug',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
            'batch_id' => 'required',
            'price' => 'required',

        ];
        $this->validate($request, $rules);

        if ($type == 'bridging') {
            $rules['from_bundle_id'] = "required|array";
            $rules['from_bundle_id.*'] = "required|exists:bundles,id";
            // $rules['to_bundle_id'] ="required|exists:bundles,id";
        }
        $this->validate($request, $rules);

        if (!in_array($type, ['program', 'bridging'])) {
            abort(404);
        }

        $data = $request->all();

        if (empty($data['slug'])) {
            $data['slug'] = Bundle::makeSlug($data['title']) . '_' . Str::random(5);
        }

        if (empty($data['video_demo'])) {
            $data['video_demo_source'] = null;
        }

        if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
            $data['video_demo_source'] = 'upload';
        }
        if (!empty($data['start_date'])) {
            if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

            $data['start_date'] = $startDate->getTimestamp();
        }

        if (!empty($data['end_date'])) {
            if (empty($data['timezone']) || !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $endDate = convertTimeToUTCzone($data['end_date'], $data['timezone']);
            $data['end_date'] = $endDate->getTimestamp();
        }


        if ($request->hasFile('content_table')) {
            $file = $request->file('content_table');
            $originalName = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads');
            $file->move($destinationPath, $originalName);
            $data['content_table'] = config('app.url') . 'uploads/' . $originalName; // Adjust the URL as needed

        } else {

            $data['content_table'] = $request->input('content_table');
        }

        //  dd($request->input('content_table'));

        $bundle = Bundle::create([
            'slug' => $data['slug'],
            'bundle_name_certificate' => $data['bundle_name_certificate'],
            'teacher_id' => $data['teacher_id'],
            'creator_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'subscribe' => !empty($data['subscribe']) ? true : false,
            'points' => $data['points'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'price' => $data['price'],
            'access_days' => $data['access_days'] ?? null,
            'category_id' => $data['category_id'],
            'certificate_template_id' => $data['certificate_template_id'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => Bundle::$pending,
            'created_at' => time(),
            'updated_at' => time(),
            'has_certificate' => $data['has_certificate'],
            'hasGroup' => $data['hasGroup'] ?? 0,
            'content_table' => $data['content_table'] ?? null,
            'batch_id' => $data['batch_id'] ?? null,
            'type' => $data['type'] ?? 'program',
            'partner_instructor' => !empty($data['partner_instructor']) ? true : false,
        ]);



        if ($bundle) {


            $studentIds = $request->input('student_id', []); // Get selected student IDs
            if (!empty($studentIds)) {
                // Attach the student IDs to the bundle using the relationship's attach method
                $bundle->studentsExcluded()->attach($studentIds);
            }


            BundleTranslation::updateOrCreate([
                'bundle_id' => $bundle->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);

            if (!empty($request['from_bundle_id'])) {
                $bundle->bridgingBundles()->sync($request->input('from_bundle_id', []));
            }


            if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {

                $bundle->PartnerTeachers()->sync($request->get('partners', []));

            }
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            BundleFilterOption::where('bundle_id', $bundle->id)->delete();

            foreach ($filters as $filter) {
                BundleFilterOption::create([
                    'bundle_id' => $bundle->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));
            Tag::where('bundle_id', $bundle->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'bundle_id' => $bundle->id,
                    'title' => $tag,
                ]);
            }
        }




        return redirect(getAdminPanelUrl() . '/bundles/' . $bundle->id . '/edit?locale=' . $data['locale'] . '&type=' . $bundle->type)->withInput();
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $bundle = Bundle::where('id', $id)
            ->with([
                'tickets',
                'faqs',
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'certificate_template',
                'tags',
                'bundleWebinars'
            ])
            ->first();

        if (empty($bundle)) {
            abort(404);
        }

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $bundle->getTable(), $bundle->id);

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();
        $study_classes = StudyClass::get();

        $certificates = CertificateTemplate::where('type', 'bundle')->get();

        $tags = $bundle->tags->pluck('title')->toArray();

        $userIds = [$bundle->creator_id, $bundle->teacher_id];
        $userWebinars = Webinar::select('id', 'creator_id', 'teacher_id')
            // ->where('status', Webinar::$active)
            // ->where('private', false)
            // ->where('category_id',$bundle->category_id)
            // ->where(function ($query) use ($userIds) {
            //     $query->whereIn('creator_id', $userIds)
            //         ->orWhereIn('teacher_id', $userIds);
            // })
            ->get();
        $students = Student::get();

        $studentsForBundle = $bundle->students;


        $data = [
            'pageTitle' => trans('admin/main.edit') . ' | ' . $bundle->title,
            'userWebinars' => $userWebinars,
            'categories' => $categories,
            'certificates' => $certificates,
            'bundle' => $bundle,
            'bundleCategoryFilters' => !empty($bundle->category) ? $bundle->category->filters : null,
            'bundleFilterOptions' => $bundle->filterOptions->pluck('filter_option_id')->toArray(),
            'tickets' => $bundle->tickets,
            'faqs' => $bundle->faqs,
            'bundleTags' => $tags,
            'bundleWebinars' => $bundle->bundleWebinars,
            'study_classes' => $study_classes,
            'students' => $students,
            'studentsForBundles' => $studentsForBundle,
            'bundlePartnerTeacher' => $bundle->bundlePartnerTeacher,
        ];

        return view('admin.bundles.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');
        $data = $request->all();

        $bundle = Bundle::find($id);


        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
        $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

        $rules = [
            'title' => 'required|max:255',
            'bundle_name_certificate' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            // 'slug' => 'max:255|unique:bundles,slug,' . $bundle->id,
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
            'batch_id' => 'required',
            'type' => 'required|in:program,bridging',
            'price' => 'required',
        ];

        $this->validate($request, $rules);

        if (!empty($data['teacher_id'])) {
            $teacher = User::findOrFail($data['teacher_id']);
            $creator = $bundle->creator;

            if (empty($teacher) or ($creator->isOrganization() and ($teacher->organ_id != $creator->id and $teacher->id != $creator->id))) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('admin/main.is_not_the_teacher_of_this_organization'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        }


        if (empty($data['slug'])) {
            $data['slug'] = Bundle::makeSlug($data['title']) . '_' . Str::random(5);
        }


        if ($request->hasFile('content_table')) {
            $file = $request->file('content_table');
            $originalName = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads'); // Ensure this path exists
            $file->move($destinationPath, $originalName);
            $data['content_table'] = config('app.url') . 'uploads/' . $originalName; // Construct URL
        } else {
            $data['content_table'] = $bundle->content_table; // Keep the existing value if no new upload
        }

        // $data['status'] = $publish ? Bundle::$active : ($reject ? Bundle::$inactive : ($isDraft ? Bundle::$isDraft : Bundle::$pending));
        $data['updated_at'] = time();
        $data['subscribe'] = !empty($data['subscribe']) ? true : false;

        if ($data['category_id'] != $bundle->category_id) {
            BundleFilterOption::where('bundle_id', $bundle->id)->delete();
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            BundleFilterOption::where('bundle_id', $bundle->id)->delete();

            foreach ($filters as $filter) {
                BundleFilterOption::create([
                    'bundle_id' => $bundle->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));
            Tag::where('bundle_id', $bundle->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'bundle_id' => $bundle->id,
                    'title' => $tag,
                ]);
            }
        }

        unset(
            $data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters'],
            $data['ajax']
        );

        if (empty($data['video_demo'])) {
            $data['video_demo_source'] = null;
        }

        if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
            $data['video_demo_source'] = 'upload';
        }
        if (!empty($data['start_date'])) {
            if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

            $data['start_date'] = $startDate->getTimestamp();
        } else {
            $data['start_date'] = null;
        }

        if (!empty($data['end_date'])) {
            if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $startDate = convertTimeToUTCzone($data['end_date'], $data['timezone']);

            $data['end_date'] = $startDate->getTimestamp();
        } else {
            $data['end_date'] = null;
        }


        $bundle->update([
            'slug' => $data['slug'],
            'bundle_name_certificate' => $data['bundle_name_certificate'],
            'teacher_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'subscribe' => $data['subscribe'],
            'points' => $data['points'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'price' => $data['price'],
            'access_days' => $data['access_days'] ?? null,
            'category_id' => $data['category_id'],
            'certificate_template_id' => $data['certificate_template_id'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => $data['status'],
            'updated_at' => time(),
            'has_certificate' => $data['has_certificate'],
            'hasGroup' => $data['hasGroup'] ?? 0,
            'content_table' => $data['content_table'] ?? null,
            'type' => $data['type'] ??  $bundle->type,
            'partner_instructor' => !empty($data['partner_instructor']) ? true : false,
        ]);

        if ($bundle) {
            $studentIds = $request->input('student_id', []); // Get selected student IDs


            $bundle->studentsExcluded()->sync($studentIds);
            Certificate::where('bundle_id', $bundle->id)->whereHas('student', function($query) use ($studentIds){
                $query->whereHas('student', function($q) use ($studentIds){
                    $q->whereIn('id', $studentIds);
                });
            })->delete();

            BundleTranslation::updateOrCreate([
                'bundle_id' => $bundle->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);

            if ($bundle->type == 'bridging') {
                $bundle->bridgingBundles()->sync($request->input('from_bundle_id', []));
            }


            if ($data['batch_id'] != $bundle->batch_id) {
                Sale::where(['bundle_id' => $bundle->id])->update(['class_id' => $data['batch_id']]);
                BundleStudent::where(['bundle_id' => $bundle->id])->whereNotNull('class_id')->update(['class_id' => $data['batch_id']]);
                $bundle->update(['batch_id' => $data['batch_id']]);
            }

            // webinar partner teachers
            if (empty($data['partner_instructor'])) {
                // WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
                unset($data['partners']);
                unset($request['partners']);
            }
            $bundle->PartnerTeachers()->sync($request->get('partners', []));
        }


        $notifyOptions = [
            '[item_title]' => $bundle->title,
        ];


        if ($publish) {
            sendNotification('bundle_approved', $notifyOptions, $bundle->teacher_id);

            /*$createClassesReward = RewardAccounting::calculateScore(Reward::CREATE_CLASSES);
            RewardAccounting::makeRewardAccounting(
                $bundle->creator_id,
                $createClassesReward,
                Reward::CREATE_CLASSES,
                $bundle->id,
                true
            );*/
        } elseif ($reject) {
            sendNotification('bundle_rejected', $notifyOptions, $bundle->teacher_id);
        }

        removeContentLocale();

        $toastData = [
            'title' => 'تعديل برنامج',
            'msg' => 'تم التعديل بنجاج',
            'status' => 'success'
        ];
        return back()
            ->withInput()  // This ensures that old input is passed to the form
            ->with(['toast' => $toastData])
            ->with('selected_students', $studentIds);

        //     return redirect()
        // ->route('your-route-name')  // Use your route name or URL
        // ->withInput()
        // ->with(['toast' => $toastData])
        // ->with('selected_students', $studentIds);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_bundles_delete');

        $bundle = Bundle::find($id);

        if (!empty($bundle)) {
            $bundle->delete();
        }

        return redirect(getAdminPanelUrl() . '/bundles');
    }

    public function studentsLists(Request $request, $id)
    {
        $this->authorize('admin_webinar_students_lists');

        $bundle = Bundle::where('id', $id)
            ->with([
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                }
            ])
            ->first();


        if (!empty($bundle)) {
            $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();


            $query = User::join('sales', 'sales.buyer_id', 'users.id')
                ->leftJoin('webinar_reviews', function ($query) use ($bundle) {
                    $query->on('webinar_reviews.creator_id', 'users.id')
                        ->where('webinar_reviews.bundle_id', $bundle->id);
                })
                ->select('users.*', 'webinar_reviews.rates', 'sales.gift_id', DB::raw('sales.created_at as purchase_date'))
                ->where(function ($query) use ($bundle, $giftsIds) {
                    $query->where('sales.bundle_id', $bundle->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereNull('sales.refund_at');

            $students = $this->studentsListsFilters($bundle, $query, $request)
                ->orderBy('sales.created_at', 'desc')
                ->paginate(10);

            $userGroups = Group::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalExpireStudents = 0;
            if (!empty($bundle->access_days)) {
                $accessTimestamp = $bundle->access_days * 24 * 60 * 60;

                $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($bundle, $giftsIds) {
                        $query->where('sales.bundle_id', $bundle->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                    ->whereNull('sales.refund_at')
                    ->count();
            }

            $bundleWebinars = $bundle->bundleWebinars;

            $webinarStatisticController = new WebinarStatisticController();

            foreach ($students as $key => $student) {
                $learnings = 0;
                $webinarCount = 0;

                foreach ($bundleWebinars as $bundleWebinar) {
                    if (!empty($bundleWebinar->webinar)) {
                        $webinarCount += 1;
                        $learnings += $webinarStatisticController->getCourseProgressForStudent($bundleWebinar->webinar, $student->id);
                    }
                }

                $learnings = ($learnings > 0 and $webinarCount > 0) ? round($learnings / $webinarCount, 2) : 0;

                if (!empty($student->gift_id)) {
                    $gift = Gift::query()->where('id', $student->gift_id)->first();

                    if (!empty($gift)) {
                        $receipt = $gift->receipt;

                        if (!empty($receipt)) {
                            $receipt->rates = $student->rates;
                            $receipt->access_to_purchased_item = $student->access_to_purchased_item;
                            $receipt->sale_id = $student->sale_id;
                            $receipt->purchase_date = $student->purchase_date;
                            $receipt->learning = $learnings;

                            $students[$key] = $receipt;
                        } else { /* Gift recipient who has not registered yet */
                            $newUser = new User();
                            $newUser->full_name = $gift->name;
                            $newUser->email = $gift->email;
                            $newUser->rates = 0;
                            $newUser->access_to_purchased_item = $student->access_to_purchased_item;
                            $newUser->sale_id = $student->sale_id;
                            $newUser->purchase_date = $student->purchase_date;
                            $newUser->learning = 0;

                            $students[$key] = $newUser;
                        }
                    }
                } else {
                    $student->learning = $learnings;
                }
            }

            $roles = Role::all();

            $data = [
                'pageTitle' => trans('admin/main.students'),
                'bundle' => $bundle,
                'students' => $students,
                'userGroups' => $userGroups,
                'roles' => $roles,
                'totalStudents' => $students->total(),
                'totalActiveStudents' => $students->total() - $totalExpireStudents,
                'totalExpireStudents' => $totalExpireStudents,
            ];

            return view('admin.bundles.students', $data);
        }

        abort(404);
    }

    private function studentsListsFilters($bundle, $query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $full_name = $request->get('full_name');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $role_id = $request->get('role_id');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'sales.created_at');

        if (!empty($full_name)) {
            $query->where('users.full_name', 'like', "%$full_name%");
        }

        if (!empty($sort)) {
            if ($sort == 'rate_asc') {
                $query->orderBy('webinar_reviews.rates', 'asc');
            }

            if ($sort == 'rate_desc') {
                $query->orderBy('webinar_reviews.rates', 'desc');
            }
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }

        if (!empty($role_id)) {
            $query->where('users.role_id', $role_id);
        }

        if (!empty($status)) {
            if ($status == 'expire' and !empty($bundle->access_days)) {
                $accessTimestamp = $bundle->access_days * 24 * 60 * 60;

                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }

    public function notificationToStudents($id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $bundle = Bundle::findOrFail($id);

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'bundle' => $bundle
        ];

        return view('admin.bundles.send-notification-to-course-students', $data);
    }


    public function sendNotificationToStudents(Request $request, $id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $bundle = Bundle::where('id', $id)
            ->with([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                    $query->with([
                        'buyer'
                    ]);
                }
            ])
            ->first();

        if (!empty($bundle)) {
            foreach ($bundle->sales as $sale) {
                if (!empty($sale->buyer)) {
                    $user = $sale->buyer;

                    Notification::create([
                        'user_id' => $user->id,
                        'group_id' => null,
                        'sender_id' => auth()->id(),
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'sender' => Notification::$AdminSender,
                        'type' => 'single',
                        'created_at' => time()
                    ]);
                    if (!empty($user->email)) {
                        $name = $user->student ? $user->student->ar_name : $user->fullname;
                        \Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                    }
                }
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => count($bundle->sales)]),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl("/bundles/{$bundle->id}/students"))->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Bundle::select('id')
            ->whereTranslationLike('title', "%$term%")->orWhere('slug', 'like', "%$term%");

        $bundles = $query->get();
        return response()->json($bundles, 200);
    }

    public function statistics(Request $request)
    {
        $this->authorize('admin_programs_statistics_bundles_list');

        removeContentLocale();

        $query = Bundle::query();

        $totalBundles = $query->count();
        $totalPendingBundles = deepClone($query)->where('bundles.status', Bundle::$pending)->count();
        $totalSales = deepClone($query)->join('sales', 'bundles.id', '=', 'sales.bundle_id')
            ->select(DB::raw('count(sales.bundle_id) as sales_count, sum(total_amount) as total_amount'))
            ->whereNotNull('sales.bundle_id')
            ->whereNull('sales.refund_at')
            ->first();

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $batches = StudyClass::get();
        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->withCount([
                'bundleWebinars'
            ]);

        $bundles = $query->paginate(10);

        foreach ($bundles as $bundle) {
            $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = Sale::query()
                ->where(function ($query) use ($bundle, $giftsIds) {
                    $query->where('bundle_id', $bundle->id);
                    $query->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $bundle->sales = $sales;
        }


        $data = [
            'pageTitle' => 'إحصائيات التسجيل في البرامج',
            'bundles' => $bundles,
            'totalBundles' => $totalBundles,
            'totalPendingBundles' => $totalPendingBundles,
            'totalSales' => $totalSales,
            'categories' => $categories,
            'batches' => $batches
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return view('admin.bundles.statistics', $data);
    }

    public function groups(Request $request, Bundle $bundle, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = $bundle->groups->unique();
        $totalGroups = deepClone($query)->count();


        $query = (new UserController())->filters($query, $request);

        if ($is_export_excel) {
            $groups = $query->orderBy('created_at', 'desc')->get();
        } else {
            $groups = $query;
        }

        if ($is_export_excel) {
            return $groups;
        }


        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'groups' => $groups,
            'item' => $bundle,
            'category' => $category,
            'totalGroups' => $totalGroups,

        ];

        return view('admin.students.courses', $data);
    }
}
