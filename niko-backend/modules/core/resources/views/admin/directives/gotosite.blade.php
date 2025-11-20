@php
    /**
     * @var $obj \Illuminate\Database\Eloquent\Model|\WezomCms\Core\ExtendPackage\Translatable
     */
    use Illuminate\Container\Container;

    $original = $app->getLocale();

    $locales = array_sort(app('locales'), function ($item, $key) use ($original) {
        return $key !== $original;
    });

    /** @var App $app */
    $app = Container::getInstance();

    try {
@endphp
@if(method_exists($obj, 'translate'))
    <div class="btn-group">
        @php
            $first = array_slice($locales, 0, 1);
        @endphp
        @if($first)
            @php
                $locale = key($first);
                $language = array_shift($first);
                $app->setLocale($locale);
                config()->set('translatable.locale', $locale);
            @endphp
            @if($obj->published && (!method_exists($obj, 'canGoToFront') || $obj->canGoToFront()))
                <a class="btn btn-sm btn-outline-secondary" href="{{ $obj->getFrontUrl() }}" target="_blank">
                    <span class="d-none d-sm-block">{{ $language }}</span>
                    <span class="text-capitalize d-block d-sm-none">{{ $locale }}</span>
                </a>
            @else
                <button class="btn btn-sm btn-outline-secondary" disabled>
                    <span class="d-none d-sm-block">{{ $language }}</span>
                    <span class="text-capitalize d-block d-sm-none">{{ $locale }}</span>
                </button>
            @endif
        @endcan
        @if(count($locales) > 1)
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
            <div class="dropdown-menu">
                @foreach(array_slice($locales, 1) as $locale => $language)
                    @php
                        $app->setLocale($locale);
                        config()->set('translatable.locale', $locale);
                    @endphp
                    @if($obj->published && (!method_exists($obj, 'canGoToFront') || $obj->canGoToFront()))
                        <a class="dropdown-item" href="{{ $obj->getFrontUrl() }}" target="_blank">
                            <span class="d-none d-sm-block">{{ $language }}</span>
                            <span class="text-capitalize d-block d-sm-none">{{ $locale }}</span>
                        </a>
                    @else
                        <button class="dropdown-item" disabled>
                            <span class="d-none d-sm-block">{{ $language }}</span>
                            <span class="text-capitalize d-block d-sm-none">{{ $locale }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@else
    @if($obj->published && (!method_exists($obj, 'canGoToFront') || $obj->canGoToFront()))
        <a href="{{ $obj->getFrontUrl() }}" class="btn btn-sm btn-outline-secondary" target="_blank" data-toggle="tooltip"
           title="@lang('cms-core::admin.layout.Go to the website')"><i class="fa fa-external-link"></i></a>
    @else
        <button class="btn btn-sm btn-outline-secondary" disabled data-toggle="tooltip"
                title="@lang('cms-core::admin.layout.Go to the website')"><i class="fa fa-external-link"></i></button>
    @endif
@endif
@php
    } finally {
        config()->set('translatable.locale', array_key_exists($original, app('locales')) ? $original : \LaravelLocalization::getDefaultLocale());
        $app->setLocale($original);
    }
@endphp
