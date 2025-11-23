export default function formatPhone(value) {
    let cleaned = ('' + value).replace(/\D/g, ''),
        match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);

    return match ? '1 (' + match[1] + ') ' + match[2] + '-' + match[3] : value;
}
