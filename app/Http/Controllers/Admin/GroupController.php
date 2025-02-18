<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupRegistrationPackage;
use App\Models\GroupUser;
use App\Models\OrganizationGroup;
use App\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_group_list');

        
        if (auth()->user()->role->name === "org") {
            $groups = OrganizationGroup::query();
            $filters = $request->input('filters');
    
            if (isset($filters['group_name'])) {
                $groups = $groups->where('name', 'like', '%' . $filters['group_name'] . '%');
            }
    
            $data = [
                'pageTitle' => trans('admin/pages/groups.group_list_page_title'),
                'groups' => $groups->paginate(10),
                'group_name' => $filters['group_name'] ?? '',
            ];
    
            return view('admin.users.groups.org_group_lists', $data);

        }else{
            $groups = Group::query();
            $filters = $request->input('filters');
    
            if (isset($filters['group_name'])) {
                $groups = $groups->where('name', 'like', '%' . $filters['group_name'] . '%');
            }
    
            $data = [
                'pageTitle' => trans('admin/pages/groups.group_list_page_title'),
                'groups' => $groups->paginate(10),
                'group_name' => $filters['group_name'] ?? '',
            ];
    
            return view('admin.users.groups.lists', $data);
        }
    }

    public function create()
    {
        $this->authorize('admin_group_create');

        $data = [
            'pageTitle' => trans('admin/main.group_new_page_title'),
        ];

        return auth()->user()->role->name === "org" ? view('admin.users.groups.orgnew', $data) : view('admin.users.groups.new', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_group_create');        
        
        if (auth()->user()->role->name === "org") {
            $group = OrganizationGroup::create($request->all());
        }else{
            $this->validate($request, [
                'users' => 'array',
                'name' => 'required',
            ]);
            
            $data = $request->all();
            $data['created_at'] = time();
            $data['creator_id'] = auth()->user()->id;
            unset($data['_token']);

            $group = Group::create($data);
            $users = $request->input('users');
            
            if (!empty($users)) {
                foreach ($users as $userId) {
                    if (GroupUser::where('user_id', $userId)->first()) {
                        continue;
                    }
    
                    GroupUser::create([
                        'group_id' => $group->id,
                        'user_id' => $userId,
                        'created_at' => time(),
                    ]);
    
                    $notifyOptions = [
                        '[u.g.title]' => $group->name,
                    ];
                    sendNotification('change_user_group', $notifyOptions, $userId);
                    sendNotification('add_to_user_group', $notifyOptions, $userId);
                }
            }

        }

        return redirect(getAdminPanelUrl() . '/users/groups');
    }

    public function edit($id)
    {
        $this->authorize('admin_group_edit');

        $group = Group::findOrFail($id);

        $userGroups = GroupUser::where('group_id', $id)
            ->with(['user' => function ($query) {
                $query->select('id', 'full_name');
            }])
            ->get();

        $data = [
            'pageTitle' => trans('admin/pages/groups.edit_page_title'),
            'group' => $group,
            'userGroups' => $userGroups,
            'groupRegistrationPackage' => $group->groupRegistrationPackage
        ];

        return view('admin.users.groups.new', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_group_edit');

        $this->validate($request, [
            'users' => 'array',
            'percent' => 'nullable',
            'name' => 'required',
        ]);

        $group = Group::findOrFail($id);

        $data = $request->all();
        unset($data['_token']);

        $group->update($data);

        $users = $request->input('users');

        $group->groupUsers()->delete();

        if (!empty($users)) {
            foreach ($users as $userId) {
                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'created_at' => time(),
                ]);

                $notifyOptions = [
                    '[u.g.title]' => $group->name,
                ];
                sendNotification('change_user_group', $notifyOptions, $userId);
                sendNotification('add_to_user_group', $notifyOptions, $userId);
            }
        }

        return redirect(getAdminPanelUrl() . '/users/groups');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_group_delete');

        Group::find($id)->delete();

        $toastData = [
            'title' => 'حذف مجموعة دورة',
            'msg' => 'تم الحذف بنجاح',
            'status' => 'success',
        ];

        return back()->with('toast', $toastData);
    }

    public function groupRegistrationPackage(Request $request, $id)
    {
        $this->validate($request, [
            'instructors_count' => 'nullable|numeric',
            'students_count' => 'nullable|numeric',
            'courses_capacity' => 'nullable|numeric',
            'courses_count' => 'nullable|numeric',
            'meeting_count' => 'nullable|numeric',
        ]);

        $group = Group::findOrFail($id);

        $data = $request->all();

        GroupRegistrationPackage::updateOrCreate([
            'group_id' => $group->id,
        ], [
            'instructors_count' => $data['instructors_count'] ?? null,
            'students_count' => $data['students_count'] ?? null,
            'courses_capacity' => $data['courses_capacity'] ?? null,
            'courses_count' => $data['courses_count'] ?? null,
            'meeting_count' => $data['meeting_count'] ?? null,
            'status' => $data['status'],
            'created_at' => time(),
        ]);

        return redirect()->back();
    }
}
