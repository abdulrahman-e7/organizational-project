<div class="tab-pane mt-3 fade active show" id="general" role="tabpanel" aria-labelledby="general-tab">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
            <form action="{{ getAdminPanelUrl() }}/users/groups/{{ !empty($group) ? $group->id.'/update' : 'store' }}" method="Post">
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ trans('admin/main.name') }}</label>
                    <input type="text" name="name"
                           class="form-control  @error('name') is-invalid @enderror"
                           value="{{ !empty($group) ? $group->name : old('name') }}"/>
                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label d-block">{{ trans('admin/main.lead_user') }}</label>
                    <select name="lead_id" class="form-control search-user-select2"
                            data-search-option="for_user_group"
                            data-placeholder="{{ trans('public.leader_id') }}">

                        @if(!empty($userGroups) and $userGroups->count() > 0)
                            @foreach($userGroups as $userGroup)
                                <option value="{{ $userGroup->user_id }}" selected>{{ $userGroup->user->full_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class=" mt-4">
                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
