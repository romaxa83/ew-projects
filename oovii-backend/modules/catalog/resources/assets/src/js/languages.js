import {Validator} from 'vee-validate';

function setLocale(localeName) {
    import(`vee-validate/dist/locale/${localeName}`).then(locale => {
        Validator.localize(localeName, locale);
    });
}

export default setLocale;
