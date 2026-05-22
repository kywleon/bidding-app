<template>
  <div class="bid-form">
    <div class="bid-form__fields">
      <!-- Current price display -->
      <div class="price-display">
        <span class="price-label">Current Price</span>
        <span class="price-value">{{ formatCurrency(currentPrice) }}</span>
      </div>

      <!-- Name input -->
      <input
        v-model="bidderName"
        type="text"
        placeholder="Your name"
        maxlength="100"
        class="input-field"
        :disabled="disabled"
        @keydown.enter="submit"
      />

      <!-- Amount input (in dollars) -->
      <input
        v-model="amountDollars"
        type="number"
        :placeholder="`Min: ${minBidDollars}`"
        :min="minBidDollars"
        step="1"
        class="input-field input-field--amount"
        :disabled="disabled"
        @keydown.enter="submit"
      />

      <!-- Quick increment buttons -->
      <div class="increment-buttons">
        <button
          v-for="inc in increments"
          :key="inc"
          class="btn btn--increment"
          :disabled="disabled"
          @click="addIncrement(inc)"
        >+{{ inc }}</button>
      </div>

      <!-- Bid button -->
      <button
        class="btn btn--bid"
        :disabled="disabled || !canSubmit"
        @click="submit"
      >
        Bid
      </button>
    </div>

    <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatCurrency, dollarsToCents } from '@/composables/useCurrency.js'

const props = defineProps({
  currentPrice: { type: Number, default: 0 },  // in cents
  disabled:     { type: Boolean, default: false },
  errorMsg:     { type: String, default: '' },
})

const emit = defineEmits(['bid'])

const bidderName    = ref('')
const amountDollars = ref('')
const increments    = [100, 500, 1000]

const minBidDollars = computed(() => Math.ceil((props.currentPrice + 1) / 100))

const canSubmit = computed(() => {
  const cents = dollarsToCents(amountDollars.value)
  return (
    bidderName.value.trim().length >= 2 &&
    cents !== null &&
    cents > props.currentPrice
  )
})

function addIncrement(inc) {
  const current = parseFloat(amountDollars.value) || minBidDollars.value
  amountDollars.value = String(current + inc)
}

function submit() {
  if (!canSubmit.value) return
  const cents = dollarsToCents(amountDollars.value)
  emit('bid', { bidderName: bidderName.value.trim(), amount: cents })
}
</script>

<style scoped>
.bid-form { display: flex; flex-direction: column; gap: 0.75rem; }
.bid-form__fields { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }

.price-display { display: flex; flex-direction: column; margin-right: 0.5rem; }
.price-label   { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
.price-value   { font-size: 1.1rem; font-weight: 700; color: #0f172a; }

.input-field {
  padding: 0.5rem 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}
.input-field:focus         { border-color: #3b82f6; }
.input-field--amount       { width: 8rem; }

.increment-buttons { display: flex; gap: 0.25rem; }
.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, opacity 0.2s;
}
.btn:disabled        { opacity: 0.4; cursor: not-allowed; }
.btn--increment      { background: #f1f5f9; color: #334155; font-size: 0.8rem; }
.btn--increment:hover:not(:disabled) { background: #e2e8f0; }
.btn--bid            { background: #3b82f6; color: white; min-width: 5rem; }
.btn--bid:hover:not(:disabled)       { background: #2563eb; }

.error-msg { color: #dc2626; font-size: 0.85rem; margin: 0; }
</style>
