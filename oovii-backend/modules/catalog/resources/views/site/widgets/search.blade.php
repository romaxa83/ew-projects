<form class="js-import" method="get" action="{{ route('search') }}">
    <div class="form-group">
        <input type="search" name="search" placeholder="@lang('cms-catalog::site.Поиск')"
               value="{{ $requestSearch }}" autocomplete="off" data-search-input required="required"/>
    </div>
    <button type="submit">@lang('cms-catalog::site.Найти')</button>
</form>
