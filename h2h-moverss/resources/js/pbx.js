$(function () {
    // pbx init
    $.ajax({
        url: '/pbx/initPBX', method: 'POST', dataType: 'json'
    })
        .done(function (res) {
            if (res.success) {
                var D1 = new $.Deferred();
                var script1 = document.createElement('script');
                script1.onload = function () {
                    D1.resolve();
                };
                script1.src = 'https://my.zadarma.com/webphoneWebRTCWidget/v8/js/loader-phone-lib.js?sub_v=62';
                document.head.appendChild(script1);
                var D2 = new $.Deferred();
                var script2 = document.createElement('script');
                script2.onload = function () {
                    D2.resolve();
                };
                script2.src = 'https://my.zadarma.com/webphoneWebRTCWidget/v8/js/loader-phone-fn.js?sub_v=62';
                document.head.appendChild(script2);

                $.when(D1, D2).then(function () {
                    //console.log('resolved', zadarmaWidgetFn)
                    if (window.addEventListener) {
                        window.addEventListener('load', function () {
                            zadarmaWidgetFn(res.data.webrtcKey.key, res.data.sip, 'square' /*square|rounded*/, 'ua' /*ru, en, es, fr, de, pl, ua*/, true, "{right:'100px';bottom:'100px';}");
                        }, false);
                    } else if (window.attachEvent) {
                        window.attachEvent('onload', function () {
                            zadarmaWidgetFn(res.data.webrtcKey.key, res.data.sip, 'square' /*square|rounded*/, 'ua' /*ru, en, es, fr, de, pl, ua*/, true, "{right:'100px';bottom:'100px';}");
                        });
                    }
                });

            } else if (res.error) {
                console.log('PBX Failed: ', res.error)
            }

            // window.location.href = '/';
        });

});
