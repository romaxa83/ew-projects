@extends('cms-core::admin.layouts.main')

@section('main')
    <?php
    /**
     * @var $result array
     * @var $validator JsValidator
     * @var $locales array
     * @var $action string
     */
    ?>
    @if(empty($result))
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-danger">
                    <div class="card-body">
                        <div>@lang('cms-core::admin.layout.No items')</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {!! Form::open(['route' => $action, 'method' => 'POST', 'id' => 'form', 'files' => true]) !!}
        <div class="row">
            @foreach($result as $side => $tabs)
                <div class="col-md-{{ (count($result) == 1 || $side === \WezomCms\Core\Settings\RenderSettings::SIDE_NONE) ? 12 : ($side === \WezomCms\Core\Settings\RenderSettings::SIDE_LEFT ? 7 : 5) }}">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs customtab2" role="tablist">
                                @foreach($tabs as $tabEl)
                                    @php
                                        $tab = $tabEl['tab'];
                                    @endphp
                                    <li class="nav-item">
                                        <a href="#setting-group-{{ $side }}-{{ $tab->getKey() }}"
                                           class="nav-link {{ $loop->first ? 'active' : null }}"
                                           data-toggle="tab" role="tab">
                                            @if ($tab->getIcon())
                                                <span class="hidden-sm-up"><i class="fa {{ $tab->getIcon() }}"></i></span>
                                                <span class="hidden-xs-down"> {{ $tab->getName() }}</span>
                                            @else
                                                <span>{{ $tab->getName() }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($tabs as $tabEl)
                                    @php
                                        $tab = $tabEl['tab'];
                                    @endphp
                                    <div class="p-t-10 tab-pane {{ $loop->first ? 'active' : null }}"
                                         id="setting-group-{{ $side }}-{{ $tab->getKey() }}" role="tabpanel">
                                        @foreach($tabEl['fields'] as $row)
                                            @if ($row instanceof \WezomCms\Core\Settings\MultilingualGroup)
                                                <ul class="nav nav-tabs customtab js-lang-tabs" role="tablist">
                                                    @foreach($locales as $locale => $language)
                                                        <li class="nav-item">
                                                            <a href="#tab-{{ $side . '-' . $tab->getKey() . '-' . $locale }}"
                                                               class="nav-link py-1 {{ $loop->first ? 'active' : '' }}"
                                                               role="tab" data-toggle="tab">{{ $language }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <div class="tab-content tabcontent-border">
                                                    @foreach($locales as $locale => $language)
                                                        <div class="tab-pane p-10 {{ $loop->first ? 'active' : '' }}"
                                                             id="tab-{{ $side . '-' . $tab->getKey() . '-' . $locale }}"
                                                             role="tabpanel">
                                                            @foreach($row->getItems() as $subEl)
                                                                @php
                                                                    /** @var \WezomCms\Core\Settings\Fields\AbstractField $row */
                                                                    $id = 'setting-input-' . $tab->getKey() . '-' . $subEl->getKey();
                                                                @endphp
                                                                <div class="form-group">
                                                                    <label for="{{ $id . '-' . $locale }}"
                                                                           class="control-label">{{ $subEl->getName() }}</label>
                                                                    @if($subEl->getHelpText())
                                                                        <i class="fa fa-info-circle" data-toggle="tooltip"
                                                                           data-placement="top"
                                                                           title="{{ $subEl->getHelpText() }}"></i>
                                                                    @endif
                                                                    @php
                                                                        $inputName = $tab->getKey() . '-' . $subEl->getInputName($locale);
                                                                        if ($subEl->getType() === \WezomCms\Core\Settings\Fields\AbstractField::TYPE_IMAGE) {
                                                                            $inputName = "{$locale}[{$inputName}]";
                                                                        }
                                                                    @endphp
                                                                    @include('cms-core::admin.settings.row', ['row' => $subEl, 'name' => $inputName, 'value' => $subEl->getValue($locale), 'locale' => $locale, 'id' => $id . '-' . $locale])
                                                                    @if($smallText = $subEl->getSmallText())
                                                                        <small class="form-text text-muted">{!! $smallText !!}</small>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                @php
                                                    /** @var \WezomCms\Core\Settings\Fields\AbstractField $row */
                                                    $id = 'setting-input-' . $tab->getKey() . '-' . $row->getKey();
                                                @endphp
                                                <div class="form-group">
                                                    <label for="{{ $id }}"
                                                           class="control-label">{{ $row->getName() }}</label>
                                                    @if($row->getHelpText())
                                                        <i class="fa fa-info-circle" data-toggle="tooltip"
                                                           data-placement="top" title="{{ $row->getHelpText() }}"></i>
                                                    @endif
                                                    @if($row->isMultilingual())
                                                        <ul class="nav nav-tabs customtab js-lang-tabs" role="tablist">
                                                            @foreach($locales as $locale => $language)
                                                                <li class="nav-item">
                                                                    <a href="#tab-{{ $id . '-' . $locale }}"
                                                                       class="nav-link py-1 {{ $loop->first ? 'active' : '' }}"
                                                                       role="tab" data-toggle="tab">{{ $language }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        <div class="tab-content">
                                                            @foreach($locales as $locale => $language)
                                                                @php
                                                                $inputName = $tab->getKey() . '-' . $row->getInputName($locale);
                                                                if ($row->getType() === \WezomCms\Core\Settings\Fields\AbstractField::TYPE_IMAGE) {
                                                                    $inputName = "{$locale}[{$inputName}]";
                                                                }
                                                                @endphp
                                                                <div class="tab-pane p-t-10 p-b-10 {{ $loop->first ? 'active' : '' }}"
                                                                     id="tab-{{ $id . '-' . $locale }}" role="tabpanel">
                                                                    @include('cms-core::admin.settings.row', ['name' => $inputName, 'value' => $row->getValue($locale), 'locale' => $locale, 'id' => $id . '-' . $locale])
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        @include('cms-core::admin.settings.row', ['name' => $tab->getKey() . '-' . $row->getInputName(), 'value' => $row->getValue()])
                                                    @endif
                                                    @if($smallText = $row->getSmallText())
                                                        <small class="form-text text-muted">{!! $smallText !!}</small>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @widget('admin:form-buttons')
        {!! Form::close() !!}
    @endif
@endsection
