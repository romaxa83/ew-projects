export default function formatDate(value, toFormat = 'll, [at] h:mm a', fromFormat = 'YYYY-MM-DD HH:mm:ss', notFromUtc = false) {
    if (value) {
        if (notFromUtc)
            return moment(value, fromFormat).format(toFormat);
        else
            return moment.utc(value, fromFormat).local().format(toFormat);
    }
}
