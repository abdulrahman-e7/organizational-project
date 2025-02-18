<div class="row align-items-center input-group mt-2">
    <div class="col-4">
        <div class="form-group ">
            <label class="input-label">{{ trans('admin/main.title') }}</label>
            <input type="text" name="steps[{{ !empty($step) ? $step->id : 'record' }}][title]"
                value="{{ (!empty($step) and !empty($step->translate($selectedLocale))) ? $step->translate($selectedLocale)->title : '' }}"
                class="form-control" />
        </div>
    </div>

    <div class="col-3">
        <div class="form-group ">
            <label class="input-label">{{ trans('update.deadline') }}</label>

            <div class="input-group step_deadline"  data-step-id='{{ !empty($step) ? $step->id : 'record' }}'>
                @if ((!empty($installment) and $installment->deadline_type == 'days') || old('deadline_type') == 'days')
                    <input type="number" name="steps[{{ !empty($step) ? $step->id : 'record' }}][deadline]"
                        value="{{ !empty($step) ? $step->deadline : '' }}" class="form-control days_type" />
                @else
                    <div class="input-group-prepend date_type">
                        <span class="input-group-text" id="dateRangeLabel">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>

                    <input type="text" name="steps[{{ !empty($step) ? $step->id : 'record' }}][deadline]"
                        class="form-control text-center datetimepicker date_type" aria-describedby="dateRangeLabel"
                        autocomplete="off"
                        value="{{ (!empty($step) and !empty($step->deadline)) ? dateTimeFormat($step->deadline, 'Y-m-d H:i', false) : '' }}" />
                @endif
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="row">
            <div class="col-6">
                <div class="form-group ">
                    <label class="input-label">{{ trans('admin/main.amount') }}</label>
                    <input type="number" name="steps[{{ !empty($step) ? $step->id : 'record' }}][amount]"
                        value="{{ !empty($step) ? $step->amount : '' }}" class="form-control" />
                </div>
            </div>

            <div class="col-6">
                <div class="form-group ">
                    <label class="input-label">{{ trans('update.amount_type') }}</label>
                    <select name="steps[{{ !empty($step) ? $step->id : 'record' }}][amount_type]" class="form-control">
                        <option value="fixed_amount"
                            {{ (!empty($step) and $step->amount_type == 'fixed_amount') ? 'selected' : '' }}>
                            {{ trans('update.fixed_amount') }}</option>
                        <option value="percent"
                            {{ (!empty($step) and $step->amount_type == 'percent') ? 'selected' : '' }}>
                            {{ trans('update.percent') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-1 text-left">
        <button type="button" class="js-remove-btn btn btn-danger"><i class="fa fa-times"></i></button>
    </div>
</div>
