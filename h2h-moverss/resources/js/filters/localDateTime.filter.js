export default function localDateTime(timestampUTC) {
    if (!timestampUTC)
        return null;
    const localMoment = moment.unix(timestampUTC).local();
    const today = moment().startOf('day');
    const yesterday = moment().subtract(1, 'days').startOf('day');
    const tomorrow = moment().add(1, 'days').startOf('day');
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
    return moment.unix(timestampUTC).local().format(dtFormat + ' h:mm a');
}
