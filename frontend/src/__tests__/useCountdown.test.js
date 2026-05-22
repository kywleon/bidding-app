import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { useCountdown } from '@/composables/useCountdown.js'

// We need a Vue app context for onUnmounted
import { createApp, defineComponent, h } from 'vue'

function withSetup(composableFn) {
  let result
  const app = createApp(defineComponent({
    setup() {
      result = composableFn()
      return () => h('div')
    },
  }))
  const root = document.createElement('div')
  app.mount(root)
  return { result, app, unmount: () => app.unmount() }
}

describe('useCountdown', () => {
  beforeEach(() => { vi.useFakeTimers() })
  afterEach(() => { vi.useRealTimers() })

  it('displays --:-- initially', () => {
    const { result, unmount } = withSetup(() => useCountdown())
    expect(result.display.value).toBe('--:--')
    unmount()
  })

  it('counts down correctly', () => {
    const { result, unmount } = withSetup(() => useCountdown())
    const target = new Date(Date.now() + 60_000) // 1 minute from now
    result.setTarget(target)

    expect(result.isRunning.value).toBe(true)
    expect(result.display.value).toBe('01:00')

    vi.advanceTimersByTime(10_000)
    expect(result.display.value).toBe('00:50')

    unmount()
  })

  it('calls onExpired when timer reaches 0', () => {
    const onExpired = vi.fn()
    const { result, unmount } = withSetup(() => useCountdown(onExpired))
    const target = new Date(Date.now() + 2_000)
    result.setTarget(target)

    vi.advanceTimersByTime(3_000)
    expect(onExpired).toHaveBeenCalledTimes(1)
    expect(result.remainingMs.value).toBe(0)
    expect(result.isExpired.value).toBe(true)
    unmount()
  })

  it('marks urgent when ≤10 seconds remain', () => {
    const { result, unmount } = withSetup(() => useCountdown())
    const target = new Date(Date.now() + 15_000)
    result.setTarget(target)

    expect(result.isUrgent.value).toBe(false)
    vi.advanceTimersByTime(6_000) // 9 seconds left
    expect(result.isUrgent.value).toBe(true)
    unmount()
  })
})
