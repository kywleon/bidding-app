/**
 * Formats an integer amount in cents as a dollar currency string.
 * e.g. 1900000 → "$19,000.00"
 */
export function formatCurrency(cents) {
  if (cents == null) return '—'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
  }).format(cents / 100)
}

/**
 * Parses a dollar string to cents integer.
 * e.g. "19000" → 1900000
 */
export function dollarsToCents(dollarStr) {
  const parsed = parseFloat(dollarStr)
  if (isNaN(parsed) || parsed <= 0) return null
  return Math.round(parsed * 100)
}
