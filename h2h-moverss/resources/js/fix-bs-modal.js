export function fixBsModal () {
    if (
        $('body').hasClass('chrome') &&
        !!window.navigator.userAgent.match(/Windows/i)
    ) {
        // Fix for Chrome on Windows - textarea are not focusable for Notes
        $(document).off('.bs.modal');
    }
}
