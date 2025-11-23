<div id="filterContent">
    <form id="filters-form">
        <div class="row mt-2">
            <div class="col-auto" style="min-height:450px;">
                <div class="nav flex-column nav-pills" id="filter-pills-tabs" role="tablist"
                     aria-orientation="vertical">
{{--                    <a class="nav-link active" id="filters-pills-branch-tab" href="#filters-pills-branch"--}}
{{--                       aria-controls="filters-pills-branch"--}}
{{--                       data-toggle="pill"--}}
{{--                       role="tab" aria-selected="true">--}}
{{--                        --}}{{--                    <i class="fal fa-home"></i>--}}
{{--                        <span class="--}}{{-- hidden-sm-down --}}{{-- ml-1"> Branch</span>--}}
{{--                    </a>--}}
                    <a class="nav-link" id="filters-pills-stage-tab" href="#filters-pills-stage"
                       aria-controls="filters-pills-stage"
                       data-toggle="pill"
                       role="tab" aria-selected="true">
                        {{--                    <i class="fal fa-home"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Stage</span>
                    </a>
                    <a class="nav-link" id="filters-pills-move-type-tab" href="#filters-pills-move-type"
                       aria-controls="filters-pills-move-type"
                       data-toggle="pill"
                       role="tab" aria-selected="true">
                        {{--                    <i class="fal fa-home"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Move type</span>
                    </a>
                    <a class="nav-link" id="filters-pills-move-size-tab" href="#filters-pills-move-size"
                       aria-controls="filters-pills-move-size"
                       data-toggle="pill"
                       role="tab" aria-selected="true">
                        {{--                    <i class="fal fa-home"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Move size</span>
                    </a>
                    <a class="nav-link" id="filters-pills-works-tab" href="#filters-pills-works"
                       aria-controls="filters-pills-works"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        {{--                    <i class="fal fa-envelope"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Services</span>
                    </a>
                    <a class="nav-link" id="filters-pills-estimate-tab" href="#filters-pills-estimate"
                       aria-controls="filters-pills-estimate"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        {{--                    <i class="fal fa-cog"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Estimate</span>
                    </a>
                    <a class="nav-link" id="filters-pills-sources-tab" href="#filters-pills-sources"
                       aria-controls="filters-pills-sources"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        {{--                    <i class="fal fa-cog"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Sources</span>
                    </a>
                    <a class="nav-link" id="filters-pills-manager-tab" href="#filters-pills-manager"
                       aria-controls="filters-pills-manager"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        {{--                    <i class="fal fa-cog"></i>--}}
                        <span class="{{-- hidden-sm-down --}} ml-1"> Manager</span>
                    </a>
                    <a class="nav-link" id="filters-order-tasks-tab" href="#filters-order-tasks"
                       aria-controls="filters-order-tasks"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        <span class="ml-1">Tasks</span>
                    </a>
                    <a class="nav-link" id="filters-client-tags-tab" href="#filters-client-tags"
                       aria-controls="filters-client-tags"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        <span class="ml-1">Client Tags</span>
                    </a>
                    <a class="nav-link" id="filters-order-tags-tab" href="#filters-order-tags"
                       aria-controls="filters-order-tags"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        <span class="ml-1">Order Tags</span>
                    </a>
                </div>
            </div>
            <div class="col filter-content-col">
                <div class="tab-content" id="filter-pills-tabsContent">
{{--                    <div class="tab-pane fade active show" id="filters-pills-branch"--}}
{{--                         aria-labelledby="filters-pills-branch-tab"--}}
{{--                         role="tabpanel">--}}
{{--                        <div class="card border m-auto m-lg-0">--}}
{{--                            <ul class="list-group list-group-flush">--}}
{{--                                <li class="list-group-item">--}}
{{--                                    <div class="custom-control custom-checkbox">--}}
{{--                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"--}}
{{--                                               id="checkbox-branch-0">--}}
{{--                                        <label class="custom-control-label" for="checkbox-branch-0">All</label>--}}
{{--                                    </div>--}}
{{--                                </li>--}}
{{--                                @foreach($divisions as $division)--}}
{{--                                    <li class="list-group-item">--}}
{{--                                        <div class="custom-control custom-checkbox">--}}
{{--                                            <input type="checkbox" name="filter[branch][{{$division->id}}]" value="1"--}}
{{--                                                   class="custom-control-input"--}}
{{--                                                   id="checkbox-branch-{{$division->id}}">--}}
{{--                                            <label class="custom-control-label"--}}
{{--                                                   for="checkbox-branch-{{$division->id}}">{{$division->title}}</label>--}}
{{--                                        </div>--}}
{{--                                    </li>--}}
{{--                                @endforeach--}}
{{--                            </ul>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="tab-pane fade active show" id="filters-pills-stage" aria-labelledby="filters-pills-stage-tab"
                         role="tabpanel">
                        <div class="card border m-auto m-lg-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"
                                               id="checkbox-stage-0">
                                        <label class="custom-control-label" for="checkbox-stage-0">All</label>
                                    </div>
                                </li>
                                @foreach($statuses as $v)
                                    <li class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="stage[]" value="{{$v->id}}"
                                                   class="custom-control-input"
                                                   id="checkbox-stage-{{$v->id}}"{!! !in_array($v->id, [9, 16]) ? ' checked':'' !!}>
                                            <label class="custom-control-label"
                                                   for="checkbox-stage-{{$v->id}}">{{$v->title}}</label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-pills-move-type"
                         aria-labelledby="filters-pills-move-type-tab"
                         role="tabpanel">
                        <div class="card border m-auto m-lg-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"
                                               id="checkbox-move-type-0">
                                        <label class="custom-control-label" for="checkbox-move-type-0">All</label>
                                    </div>
                                </li>
                                @foreach($moveTypes as $k=>$moveType)
                                    <li class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="filter[move-type][{{$k}}]" value="1"
                                                   class="custom-control-input"
                                                   id="checkbox-move-type-{{$k}}">
                                            <label class="custom-control-label"
                                                   for="checkbox-move-type-{{$k}}">{{$moveType['title']}}</label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-pills-move-size"
                         aria-labelledby="filters-pills-move-size-tab"
                         role="tabpanel">
                        <div class="card border m-auto m-lg-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"
                                               id="checkbox-move-size-0">
                                        <label class="custom-control-label" for="checkbox-move-size-0">All</label>
                                    </div>
                                </li>
                                @foreach($moveSizes as $moveSize)
                                    <li class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="filter[move-size][{{$moveSize->id}}]" value="1"
                                                   class="custom-control-input"
                                                   id="checkbox-move-size-{{$moveSize->id}}">
                                            <label class="custom-control-label"
                                                   for="checkbox-move-size-{{$moveSize->id}}">{{$moveSize->title}}</label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-pills-works" aria-labelledby="filters-pills-works-tab"
                         role="tabpanel">
                        <div class="card border m-auto m-lg-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"
                                               id="checkbox-works-0">
                                        <label class="custom-control-label" for="checkbox-works-0">All</label>
                                    </div>
                                </li>
                                @foreach($workTypes as $workType)
                                    <li class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="filter[works][{{$workType->id}}]" value="1"
                                                   class="custom-control-input" id="checkbox-works-{{$workType->id}}">
                                            <label class="custom-control-label"
                                                   for="checkbox-works-{{$workType->id}}">{{$workType->title}}</label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-pills-sources" aria-labelledby="filters-pills-sources-tab"
                         role="tabpanel">
                        <div class="form-group mb-2">
                            <label class="form-label">Select source</label>
                            <select id="select-source" class="form-control filter-sources" name="filter[source][]"
                                    data-placeholder="Sources" multiple>
                                @foreach($sources as $source)
                                    <option value="{{$source->id}}">{{$source->title}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-pills-manager" aria-labelledby="filters-pills-manager-tab"
                         role="tabpanel">
                        <div class="form-group mb-2">
                            <label class="form-label">Select Manager</label>
                            <select id="select-manager" class="form-control filter-managers" name="filter[manager][]"
                                    data-placeholder="Managers" multiple>
                                @foreach($managers->sortByDesc('active') as $manager)
                                    <option value="{{$manager->id}}">{{ $manager->name . ( !$manager->active ? ' - FIRED ':'')}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-order-tasks" aria-labelledby="filters-order-tasks-tab"
                         role="tabpanel">
                        <div class="form-group mb-2">
                            <label class="form-label">Leads</label>
                            <select class="form-control" name="filter[tasks]" data-placeholder="Managers">
                                <option value="">With any Tasks</option>
                                <option value="open">With opened Tasks</option>
                                <option value="not_open">Without opened Tasks</option>
                            </select>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-client-tags" aria-labelledby="filters-client-tags-tab"
                         role="tabpanel">
                        <div class="card border m-auto m-lg-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"
                                               id="checkbox-clientTags-0">
                                        <label class="custom-control-label" for="checkbox-clientTags-0">All</label>
                                    </div>
                                </li>
                                @foreach($clientTags as $v)
                                    <li class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="filter[clientTags][]" value="{{ $v->id }}"
                                                   {!! (in_array($v->id, request()->get('filter-client-tags',[])) ? ' checked':'') !!}
                                                   class="custom-control-input" id="checkbox-clientTags-{{ $v->id }}">
                                            <label class="custom-control-label" for="checkbox-clientTags-{{ $v->id }}" style="color: {{ $v->color }}">
                                                @if($v->icon)
                                                    <i class="fal fa-{{ $v->icon }} mr-1"></i>
                                                @endif
                                                {{ $v->title }}
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-order-tags" aria-labelledby="filters-order-tags-tab"
                         role="tabpanel">
                        <div class="card border m-auto m-lg-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input custom-control-filter-all"
                                               id="checkbox-orderTags-0">
                                        <label class="custom-control-label" for="checkbox-orderTags-0">All</label>
                                    </div>
                                </li>
                                @foreach($orderTags as $v)
                                    <li class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="filter[orderTags][]" value="{{ $v->id }}"
                                                   {!! (in_array($v->id, request()->get('filter-order-tags',[])) ? ' checked':'') !!}
                                                   class="custom-control-input" id="checkbox-orderTags-{{ $v->id }}">
                                            <label class="custom-control-label" for="checkbox-orderTags-{{ $v->id }}" style="color: {{ $v->color }}">
                                                @if($v->icon)
                                                    <i class="fal fa-{{ $v->icon }} mr-1"></i>
                                                @endif
                                                {{ $v->title }}
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="filters-pills-estimate" aria-labelledby="filters-pills-estimate-tab"
                         role="tabpanel">

                        {{--                        <div class="form-group mb-2">--}}
                        {{--                            <label class="form-label">Range Silder</label>--}}
                        {{--                            <input class="d-none range_1" type="text" value="" class="d-none" readonly="">--}}
                        {{--                        </div>--}}

                        <div class="form-group mb-2">
                            <label class="form-label">Min</label>
                            <div class="input-group bg-white shadow-inset-2">
                                <div class="input-group-prepend">
                                                        <span class="input-group-text bg-transparent border-right-0">
                                                            <i class="fal fa-dollar-sign"></i>
                                                        </span>
                                </div>
                                <input type="text" name="filter[estimate][min]"
                                       class="form-control border-left-0 bg-transparent pl-0"
                                       placeholder="0">
                            </div>
                            <span class="help-block">Some help content goes here</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max</label>
                            <div class="input-group bg-white shadow-inset-2">
                                <div class="input-group-prepend">
                                                        <span class="input-group-text bg-transparent border-right-0">
                                                            <i class="fal fa-dollar-sign"></i>
                                                        </span>
                                </div>
                                <input type="text" name="filter[estimate][max]"
                                       class="form-control border-left-0 bg-transparent pl-0"
                                       placeholder="0">
                            </div>
                            <span class="help-block">Maybe add range from form stuff</span>
                        </div>

                        {{--                    </div>--}}

                    </div>

                </div>
            </div>
        </div>
    </form>
    {{--    <div class="row">--}}
    <div class="d-flex mt-5 mb-2">
        <button type="button" class="btn btn-secondary waves-effect waves-themed clear-filter">Clear</button>
        <button type="button" class="btn btn-primary waves-effect waves-themed ml-auto apply-filter">Apply</button>
    </div>
    {{--    </div>--}}
</div>
