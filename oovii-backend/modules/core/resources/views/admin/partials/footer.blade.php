<!-- footer -->
<footer class="footer">
    <div class="row">
        <div class="col col-12 col-sm-6 text-center text-sm-left">
            @lang('cms-core::admin.layout.All rights reserved', ['year' => date('Y')])
        </div>
        <div class="col col-12 col-sm-6 text-center text-sm-right">
            @lang('cms-core::admin.layout.Developed by') <a href="{{ $vendor['link'] }}" target="_blank">{{ $vendor['name'] }}</a>. @lang('cms-core::admin.layout.Version :version', ['version' => $version])
        </div>
    </div>
</footer>
<!-- End footer -->
