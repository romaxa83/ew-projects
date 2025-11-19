const mix = require('laravel-mix');
const fs = require('fs');

mix.setPublicPath('./');

mix.sass(
        'resources/views/mail/html/themes/scss/media.scss',
        'resources/views/mail/html/themes/'
    ).sass(
        'resources/views/mail/html/themes/scss/mso.scss',
        'resources/views/mail/html/themes/'
    ).sass(
        'resources/views/mail/html/themes/scss/wezom.scss',
        'resources/views/mail/html/themes/'
    ).copy(
        'resources/views/mail/assets',
        './../../public/mail'
    )
    .then(() => {
        try {
            fs.unlinkSync('./mix-manifest.json');
        } catch (e) {
        }
    });
