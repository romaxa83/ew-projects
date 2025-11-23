<!-- this overlay is activated only when mobile menu is triggered -->
<div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div> <!-- END Page Content -->
<!-- BEGIN Page Footer -->
<footer class="page-footer" role="contentinfo">
    <div class="d-flex align-items-center flex-1 text-muted">
        <span class="hidden-md-down fw-700">{{ date('Y') }} © {{ config('app.name') }} by&nbsp;<a href='https://developers' class='text-primary fw-500'
                                                                                                  title='developers' target='_blank'
                                                                                                  rel="noreferrer">developers</a></span>
    </div>
    <div>
        <ul class="list-table m-0">
            {{--            <li><a href="intel_introduction.html" class="text-secondary fw-700">About</a></li>--}}
            {{--            <li class="pl-3"><a href="info_app_licensing.html" class="text-secondary fw-700">License</a></li>--}}
            {{--            <li class="pl-3"><a href="info_app_docs.html" class="text-secondary fw-700">Documentation</a></li>--}}
            <li class="pl-3 fs-xl"><a href="#" class="text-secondary" target="_blank"><i class="fal fa-question-circle" aria-hidden="true"></i></a></li>
        </ul>
    </div>
</footer>

<script type="application/javascript">
    var managers = @json($nav_managers);
</script>
