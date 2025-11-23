export default function formatNumber(value, maximumFractionDigits = 2) {
    return new Intl.NumberFormat({
        minimumFractionDigits: 0,
        maximumFractionDigits,
    }).format(value)
}
