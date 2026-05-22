<template>
  <!-- ENDED: show winner -->
  <div v-if="isEnded" class="status status--ended">
    <div v-if="iWon" class="winner-banner winner-banner--you">
      <span class="trophy">🏆</span>
      <p class="winner-title">You are the Winner!</p>
      <p class="winner-sub">Winning bid: <strong>{{ formatCurrency(winner?.amount) }}</strong></p>
    </div>
    <div v-else class="winner-banner winner-banner--other">
      <span class="trophy">🏆</span>
      <p class="winner-title">Auction Ended</p>
      <p class="winner-sub">Won by <strong>{{ winner?.bidder_name ?? 'Unknown' }}</strong></p>
      <p class="winner-sub">Winning bid: <strong>{{ formatCurrency(winner?.amount) }}</strong></p>
    </div>
  </div>

  <!-- ACTIVE: show current bid -->
  <div v-else-if="isActive" class="status status--active">
    <p class="current-amount">{{ formatCurrency(currentPrice) }}</p>
    <p class="current-label">
      {{ isCurrentBidMine ? 'Current Bid by You 🎉' : `Current Bid by ${topBidder}` }}
    </p>
  </div>

  <!-- PENDING: waiting for first bid -->
  <div v-else class="status status--pending">
    <p class="pending-msg">Please <em>`Bid`</em> to Start</p>
    <p class="pending-sub">Starting at {{ formatCurrency(startingPrice) }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/composables/useCurrency.js'

const props = defineProps({
  isActive:        { type: Boolean, default: false },
  isEnded:         { type: Boolean, default: false },
  currentPrice:    { type: Number,  default: 0 },
  startingPrice:   { type: Number,  default: 0 },
  winner:          { type: Object,  default: null },
  bids:            { type: Array,   default: () => [] },
  isCurrentBidMine:{ type: Boolean, default: false },
  iWon:            { type: Boolean, default: false },
})

const topBidder = computed(() => props.bids[0]?.bidder_name ?? '—')
</script>

<style scoped>
.status {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 10rem;
  padding: 2rem;
  text-align: center;
}

/* Pending */
.pending-msg { font-size: 1.5rem; color: #64748b; margin: 0 0 0.5rem; }
.pending-sub { font-size: 0.9rem; color: #94a3b8; margin: 0; }

/* Active */
.current-amount {
  font-size: 2.5rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 0.25rem;
  font-variant-numeric: tabular-nums;
}
.current-label { font-size: 1rem; color: #475569; margin: 0; }

/* Ended */
.winner-banner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  animation: fadeIn 0.5s ease;
}
.trophy        { font-size: 3rem; }
.winner-title  { font-size: 1.5rem; font-weight: 800; margin: 0; }
.winner-sub    { font-size: 0.95rem; color: #475569; margin: 0; }

.winner-banner--you   .winner-title { color: #059669; }
.winner-banner--other .winner-title { color: #1e293b; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
