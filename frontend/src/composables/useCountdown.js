import { ref, computed, onUnmounted } from 'vue'

/**
 * Reactive countdown timer.
 * Syncs to a target Date; stops at 0 and calls onExpired().
 */
export function useCountdown(onExpired) {
  const remainingMs = ref(0)
  let intervalId = null

  const minutes = computed(() => Math.floor(remainingMs.value / 60000))
  const seconds = computed(() => Math.floor((remainingMs.value % 60000) / 1000))

  const display = computed(() => {
    const mm = String(minutes.value).padStart(2, '0')
    const ss = String(seconds.value).padStart(2, '0')
    return `${mm}:${ss}`
  })

  const isRunning  = computed(() => remainingMs.value > 0)
  const isExpired  = computed(() => remainingMs.value === 0)
  const isUrgent   = computed(() => remainingMs.value > 0 && remainingMs.value <= 10000)

  function setTarget(targetDate) {
    clear()
    tick(targetDate)
    intervalId = setInterval(() => tick(targetDate), 500)
  }

  function tick(targetDate) {
    const diff = targetDate - Date.now()
    if (diff <= 0) {
      remainingMs.value = 0
      clear()
      onExpired?.()
    } else {
      remainingMs.value = diff
    }
  }

  function clear() {
    if (intervalId) {
      clearInterval(intervalId)
      intervalId = null
    }
  }

  onUnmounted(clear)

  return { display, minutes, seconds, remainingMs, isRunning, isExpired, isUrgent, setTarget, clear }
}
