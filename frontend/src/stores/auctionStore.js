import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuctionStore = defineStore('auction', () => {
  // ── State ──────────────────────────────────────────────────────────────────
  const auction    = ref(null)
  const loading    = ref(false)
  const error      = ref(null)
  const myLastBid  = ref(null) // track which bids came from this session

  // ── Getters ────────────────────────────────────────────────────────────────
  const status          = computed(() => auction.value?.status ?? 'pending')
  const isPending       = computed(() => status.value === 'pending')
  const isActive        = computed(() => status.value === 'active')
  const isEnded         = computed(() => status.value === 'ended')
  const product         = computed(() => auction.value?.product ?? null)
  const currentPrice    = computed(() => auction.value?.current_price ?? 0)
  const bids            = computed(() => auction.value?.bids ?? [])
  const winner          = computed(() => auction.value?.winner ?? null)
  const endsAt          = computed(() => auction.value?.ends_at ? new Date(auction.value.ends_at) : null)
  const remainingSeconds= computed(() => auction.value?.remaining_seconds ?? 0)

  const isCurrentBidMine = computed(() => {
    if (!myLastBid.value || !bids.value.length) return false
    const highest = bids.value[0]
    return highest?.id === myLastBid.value.id
  })

  const iWonTheAuction = computed(() => {
    if (!isEnded.value || !winner.value || !myLastBid.value) return false
    return winner.value.id === myLastBid.value.id
  })

  // ── Actions ────────────────────────────────────────────────────────────────

  async function fetchAuction(auctionId) {
    loading.value = true
    error.value   = null
    try {
      const { data } = await axios.get(`/api/auctions/${auctionId}`)
      auction.value = data
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Failed to load auction.'
    } finally {
      loading.value = false
    }
  }

  async function placeBid(auctionId, { bidderName, amount }) {
    error.value = null
    try {
      const { data } = await axios.post(`/api/auctions/${auctionId}/bids`, {
        bidder_name: bidderName,
        amount,        // in cents
      })
      auction.value  = data.auction
      myLastBid.value = data.bid
      return { success: true }
    } catch (e) {
      const msg = e.response?.data?.message
        ?? e.response?.data?.errors?.bidder_name?.[0]
        ?? e.response?.data?.errors?.amount?.[0]
        ?? 'Failed to place bid.'
      error.value = msg
      return { success: false, message: msg }
    }
  }

  async function triggerEnd(auctionId) {
    try {
      const { data } = await axios.post(`/api/auctions/${auctionId}/end`)
      auction.value = data
    } catch {
      // Silently ignore if already ended or timer running
    }
  }

  // Called by WebSocket events ──────────────────────────────────────────────

  function applyBidPlaced(event) {
    if (!auction.value) return
    auction.value.status           = event.status
    auction.value.current_price    = event.current_price
    auction.value.remaining_seconds= event.remaining_seconds
    auction.value.ends_at          = event.ends_at
    // Prepend new bid (bids are ordered desc by amount from API)
    const exists = auction.value.bids.find(b => b.id === event.bid.id)
    if (!exists) {
      auction.value.bids.unshift(event.bid)
      auction.value.bids.sort((a, b) => b.amount - a.amount)
    }
  }

  function applyStatusChange(event) {
    if (!auction.value) return
    auction.value.status            = event.status
    auction.value.current_price     = event.current_price
    auction.value.remaining_seconds = event.remaining_seconds
    auction.value.ends_at           = event.ends_at
    if (event.winner) {
      auction.value.winner = event.winner
    }
  }

  return {
    // state
    auction, loading, error, myLastBid,
    // getters
    status, isPending, isActive, isEnded,
    product, currentPrice, bids, winner, endsAt, remainingSeconds,
    isCurrentBidMine, iWonTheAuction,
    // actions
    fetchAuction, placeBid, triggerEnd,
    applyBidPlaced, applyStatusChange,
  }
})
