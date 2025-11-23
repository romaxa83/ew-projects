export default function formatDateTime(timestampUTC, tz) {
    if (!timestampUTC)
        return null;
    const localMoment = moment.unix(timestampUTC).utc().tz(tz);
    const today = moment().tz(tz).startOf('day');
    const yesterday = moment().tz(tz).subtract(1, 'days').startOf('day');
    const tomorrow = moment().tz(tz).add(1, 'days').startOf('day');
    let dtFormat = 'll';
    if (localMoment.isSame(today, 'year')) {
        dtFormat = 'MMM D [at]';
        if (localMoment.isSame(today, 'day')) {
            dtFormat = '[Today]';
        } else if (localMoment.isSame(yesterday, 'day')) {
            dtFormat = '[Yesterday]';
        } else if (localMoment.isSame(tomorrow, 'day')) {
            dtFormat = '[Tomorrow]';
        }
    }
    return localMoment.tz(tz).format(dtFormat + ' h:mm a');
}
