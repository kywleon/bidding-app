<template>
  <div class="page">
    <!-- Loading -->
    <div v-if="store.loading" class="page__loading">
      <div class="spinner"></div>
      <p>Loading auction…</p>
    </div>

    <!-- Error -->
    <div v-else-if="store.error && !store.auction" class="page__error">
      <p>{{ store.error }}</p>
    </div>

    <!-- Auction card -->
    <div v-else-if="store.auction" class="auction-card">

      <!-- Header: product info -->
      <div class="auction-card__header">
        <img
          v-if="store.product?.image_url"
          :src="store.product.image_url"
          :alt="store.product.name"
          class="product-image"
        />
        <div class="product-info">
          <h1 class="product-name">{{ store.product?.name }}</h1>
          <p class="product-desc">{{ store.product?.description }}</p>
        </div>
      </div>

      <!-- Countdown bar -->
      <div class="auction-card__countdown">
        <CountdownTimer
          :display="countdown.display.value"
          :is-running="countdown.isRunning.value"
          :is-urgent="countdown.isUrgent.value"
        />
        <span class="status-badge" :class="`status-badge--${store.status}`">
          {{ statusLabel }}
        </span>
      </div>

      <!-- Status display (Before / During / End) -->
      <div class="auction-card__status">
        <BidStatus
          :is-active="store.isActive"
          :is-ended="store.isEnded"
          :current-price="store.currentPrice"
          :starting-price="store.product?.starting_price"
          :winner="store.winner"
          :bids="store.bids"
          :is-current-bid-mine="store.isCurrentBidMine"
          :i-won="store.iWonTheAuction"
        />
      </div>

      <!-- Action bar -->
      <div v-if="!store.isEnded" class="auction-card__action">
        <BidForm
          :current-price="store.currentPrice"
          :disabled="submitting"
          :error-msg="store.error ?? ''"
          @bid="handleBid"
        />
      </div>

      <!-- Bid history -->
      <div class="auction-card__history">
        <BidHistory :bids="store.bids" />
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAuctionStore } from '@/stores/auctionStore.js'
import { useCountdown }    from '@/composables/useCountdown.js'
import { useAuctionChannel } from '@/composables/useWebSocket.js'
import CountdownTimer from '@/components/CountdownTimer.vue'
import BidStatus      from '@/components/BidStatus.vue'
import BidForm        from '@/components/BidForm.vue'
import BidHistory     from '@/components/BidHistory.vue'

// Auction ID is hardcoded to 1 for this challenge (single product)
const AUCTION_ID = 1

const store      = useAuctionStore()
const submitting = ref(false)

// ── Countdown ────────────────────────────────────────────────────────────────
const countdown = useCountdown(() => {
  // When timer hits 0, trigger end on backend
  if (store.isActive) {
    store.triggerEnd(AUCTION_ID)
  }
})

// Keep countdown in sync with store
watch(() => store.endsAt, (endsAt) => {
  if (endsAt && store.isActive) {
    countdown.setTarget(endsAt)
  } else {
    countdown.clear()
  }
}, { immediate: true })

// ── WebSocket (real-time) ────────────────────────────────────────────────────
useAuctionChannel(AUCTION_ID, {
  onBidPlaced(event) {
    store.applyBidPlaced(event)
    // Resync countdown if this bid started the auction
    if (event.ends_at && store.isActive) {
      countdown.setTarget(new Date(event.ends_at))
    }
  },
  onStatusChanged(event) {
    store.applyStatusChange(event)
    if (event.status === 'active' && event.ends_at) {
      countdown.setTarget(new Date(event.ends_at))
    } else if (event.status === 'ended') {
      countdown.clear()
    }
  },
})

// ── Initial load ─────────────────────────────────────────────────────────────
onMounted(() => store.fetchAuction(AUCTION_ID))

// ── Bid submission ────────────────────────────────────────────────────────────
async function handleBid({ bidderName, amount }) {
  submitting.value = true
  await store.placeBid(AUCTION_ID, { bidderName, amount })
  submitting.value = false
}

// ── Computed ─────────────────────────────────────────────────────────────────
const statusLabel = computed(() => ({
  pending: 'Waiting',
  active:  'Live',
  ended:   'Ended',
}[store.status] ?? ''))
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f0f4ff 0%, #fafafa 100%);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 2rem 1rem;
}
.page__loading, .page__error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  min-height: 60vh;
  color: #64748b;
}

.auction-card {
  width: 100%;
  max-width: 42rem;
  background: #fff;
  border-radius: 1.5rem;
  box-shadow: 0 4px 32px 0 rgba(0,0,0,0.08);
  overflow: hidden;
}

/* Header */
.auction-card__header {
  display: flex;
  gap: 1.25rem;
  padding: 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.product-image {
  width: 7rem;
  height: 7rem;
  object-fit: cover;
  border-radius: 0.75rem;
  flex-shrink: 0;
}
.product-info { flex: 1; min-width: 0; }
.product-name {
  font-size: 1.2rem;
  font-weight: 700;
  margin: 0 0 0.35rem;
  color: #0f172a;
}
.product-desc {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

/* Countdown */
.auction-card__countdown {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.5rem;
  background: #eff6ff;
  border-bottom: 1px solid #dbeafe;
}
.status-badge {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
}
.status-badge--pending { background: #f1f5f9; color: #64748b; }
.status-badge--active  { background: #dcfce7; color: #166534; }
.status-badge--ended   { background: #fee2e2; color: #991b1b; }

/* Sections */
.auction-card__status  { border-bottom: 1px solid #f1f5f9; }
.auction-card__action  { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
.auction-card__history { padding: 0.5rem 1.5rem 1.5rem; }

/* Spinner */
.spinner {
  width: 2.5rem; height: 2.5rem;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
