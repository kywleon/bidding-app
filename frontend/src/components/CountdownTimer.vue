<template>
  <div class="countdown-timer" :class="{ urgent: isUrgent, idle: !isRunning }">
    <div class="clock-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/>
        <polyline :points="clockHands"/>
        <line x1="12" y1="12" x2="12" y2="7"/>
      </svg>
    </div>
    <span class="time-display">{{ display }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  display:   { type: String, default: '--:--' },
  isRunning: { type: Boolean, default: false },
  isUrgent:  { type: Boolean, default: false },
})

// Simple static clock hands at 12-o-clock
const clockHands = '12,12 12,7'
</script>

<style scoped>
.countdown-timer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  transition: color 0.3s;
}
.countdown-timer.urgent  { color: #dc2626; animation: pulse 1s ease-in-out infinite; }
.countdown-timer.idle    { color: #94a3b8; }
.clock-icon { width: 2rem; height: 2rem; }
.clock-icon svg { width: 100%; height: 100%; }
.time-display { letter-spacing: 0.05em; font-variant-numeric: tabular-nums; }

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.5; }
}
</style>
