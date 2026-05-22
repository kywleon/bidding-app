<template>
  <div class="bid-history">
    <h3 class="bid-history__title">Bid History</h3>
    <p v-if="!bids.length" class="bid-history__empty">No bids yet. Be the first!</p>
    <ul v-else class="bid-history__list">
      <li
        v-for="(bid, index) in bids"
        :key="bid.id"
        class="bid-row"
        :class="{ 'bid-row--top': index === 0 }"
      >
        <span class="bid-row__rank">#{{ index + 1 }}</span>
        <span class="bid-row__name">{{ bid.bidder_name }}</span>
        <span class="bid-row__amount">{{ formatCurrency(bid.amount) }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { formatCurrency } from '@/composables/useCurrency.js'

defineProps({
  bids: { type: Array, default: () => [] },
})
</script>

<style scoped>
.bid-history { padding: 1rem 0; }
.bid-history__title  { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin: 0 0 0.75rem; }
.bid-history__empty  { color: #cbd5e1; font-size: 0.9rem; text-align: center; padding: 1rem 0; }
.bid-history__list   { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.35rem; max-height: 12rem; overflow-y: auto; }

.bid-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.4rem 0.6rem;
  border-radius: 0.4rem;
  background: #f8fafc;
  transition: background 0.2s;
}
.bid-row--top          { background: #eff6ff; }
.bid-row__rank         { font-size: 0.75rem; color: #94a3b8; width: 1.5rem; flex-shrink: 0; }
.bid-row__name         { flex: 1; font-weight: 500; font-size: 0.9rem; color: #334155; }
.bid-row__amount       { font-weight: 700; font-size: 0.95rem; color: #1e293b; font-variant-numeric: tabular-nums; }
.bid-row--top .bid-row__amount { color: #2563eb; }
</style>
