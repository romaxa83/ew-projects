export default function currencyFilter(value, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
        currency
    }).format(value)
}
