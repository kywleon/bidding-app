import { describe, it, expect } from 'vitest'
import { formatCurrency, dollarsToCents } from '@/composables/useCurrency.js'

describe('formatCurrency', () => {
  it('formats cents as USD string', () => {
    expect(formatCurrency(1900000)).toBe('$19,000.00')
    expect(formatCurrency(100)).toBe('$1.00')
    expect(formatCurrency(50)).toBe('$0.50')
  })

  it('returns em dash for null/undefined', () => {
    expect(formatCurrency(null)).toBe('—')
    expect(formatCurrency(undefined)).toBe('—')
  })
})

describe('dollarsToCents', () => {
  it('converts dollar string to cents integer', () => {
    expect(dollarsToCents('19000')).toBe(1900000)
    expect(dollarsToCents('1.50')).toBe(150)
    expect(dollarsToCents('100')).toBe(10000)
  })

  it('returns null for invalid input', () => {
    expect(dollarsToCents('abc')).toBeNull()
    expect(dollarsToCents('-5')).toBeNull()
    expect(dollarsToCents('0')).toBeNull()
    expect(dollarsToCents('')).toBeNull()
  })
})
