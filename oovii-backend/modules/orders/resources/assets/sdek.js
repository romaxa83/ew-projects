$(document).ready(function () {
    var wrapper = $('#main-wrapper');
    //var regionSelect = $('#sdek-region-select');

    // Sdek
    wrapper.on('change', '#sdek-region-select', function () {
        var val = $(this).val();

        var citySelect = $('#sdek-city-select');
        citySelect.empty();

        // var newRoute = route('admin.sdek.search-cities', {region: val});
        var newRoute = '/wezom/sdek/search-cities/' + val;

        citySelect.attr('data-url', newRoute);
        citySelect.select2({
            width: '100%',
            ajax: {
                url: newRoute,
                dataType: 'JSON'
            }
        });
    });
});
