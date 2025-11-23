export function hasSalesErrorLocal(record) {
	const value = record.local;
    return !(value === null || isPositiveNumber(value));
}

export function hasSalesErrorInterstate(record) {
    const value = record.intrestate
	return !(value === null || isPositiveNumber(value));
}

export function invalidEfficiencyConversation(value) {
    return !(value === null || (isPositiveNumber(value) && Number(value) <= 100));
}

function isPositiveNumber(value) {
	if (/\D/.test(String(value))) return false;
	const number = Number(value);
	if (isNaN(number)) return false;
	return number >= 0;
}
