<div id="filterContent">
    <div class="row">
        <div class="col mb-2 mt-3">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent rounded-0 border-top-0 border-left-0 border-right-0">
                        <i class="fal fa-search"></i>
                    </span>
                </div>
                <input type="text"
                       class="form-control form-control-md rounded-0 border-top-0 border-left-0 border-right-0 px-0 bg-transparent"
                       placeholder="Filter something here">
            </div>
        </div>
    </div>
    <form id="filters-form">
        <div class="row">
            <div class="col-auto" style="min-height:450px;">
                <div class="nav flex-column nav-pills" id="filter-pills-tabs" role="tablist"
                     aria-orientation="vertical">
                    <a class="nav-link active" id="filters-client-tags-tab" href="#filters-client-tags"
                       aria-controls="filters-client-tags"
                       data-toggle="pill"
                       role="tab" aria-selected="false">
                        <span class="ml-1">Tags</span>
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="tab-content" id="filter-pills-tabsContent">

                    <div class="tab-pane fade active show" id="filters-client-tags" aria-labelledby="filters-client-tags-tab"
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
