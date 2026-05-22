import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuctionStore } from '@/stores/auctionStore.js'

// Mock axios
vi.mock('axios', () => ({
  default: {
    get:  vi.fn(),
    post: vi.fn(),
    defaults: { baseURL: '', headers: { common: {} } },
  },
}))
import axios from 'axios'

const MOCK_AUCTION = {
  id: 1,
  status: 'pending',
  remaining_seconds: 0,
  ends_at: null,
  current_price: 1900000,
  winner: null,
  product: {
    id: 1,
    name: 'Test Watch',
    description: 'A nice watch',
    image_url: null,
    starting_price: 1900000,
  },
  bids: [],
}

describe('auctionStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts with no auction data', () => {
    const store = useAuctionStore()
    expect(store.auction).toBeNull()
    expect(store.isPending).toBe(true)
    expect(store.currentPrice).toBe(0)
  })

  it('fetchAuction loads data from API', async () => {
    axios.get.mockResolvedValueOnce({ data: MOCK_AUCTION })
    const store = useAuctionStore()
    await store.fetchAuction(1)
    expect(store.auction).toEqual(MOCK_AUCTION)
    expect(store.currentPrice).toBe(1900000)
    expect(store.isPending).toBe(true)
    expect(axios.get).toHaveBeenCalledWith('/api/auctions/1')
  })

  it('fetchAuction sets error on failure', async () => {
    axios.get.mockRejectedValueOnce({ response: { data: { message: 'Not found' } } })
    const store = useAuctionStore()
    await store.fetchAuction(999)
    expect(store.error).toBe('Not found')
    expect(store.auction).toBeNull()
  })

  it('placeBid updates auction and tracks myLastBid', async () => {
    const activeMock = { ...MOCK_AUCTION, status: 'active', current_price: 1910000 }
    const bidMock    = { id: 1, bidder_name: 'Alice', amount: 1910000, created_at: '' }
    axios.post.mockResolvedValueOnce({ data: { auction: activeMock, bid: bidMock } })

    const store = useAuctionStore()
    store.auction = { ...MOCK_AUCTION }
    const result = await store.placeBid(1, { bidderName: 'Alice', amount: 1910000 })

    expect(result.success).toBe(true)
    expect(store.myLastBid).toEqual(bidMock)
    expect(store.currentPrice).toBe(1910000)
    expect(store.isActive).toBe(true)
  })

  it('placeBid returns error on failure', async () => {
    axios.post.mockRejectedValueOnce({ response: { data: { message: 'Bid too low' } } })
    const store = useAuctionStore()
    store.auction = { ...MOCK_AUCTION }
    const result = await store.placeBid(1, { bidderName: 'Alice', amount: 100 })
    expect(result.success).toBe(false)
    expect(result.message).toBe('Bid too low')
    expect(store.error).toBe('Bid too low')
  })

  it('applyBidPlaced updates current price and adds bid', () => {
    const store = useAuctionStore()
    store.auction = { ...MOCK_AUCTION, bids: [] }

    store.applyBidPlaced({
      auction_id:        1,
      status:            'active',
      current_price:     1910000,
      remaining_seconds: 58,
      ends_at:           '2025-01-01T00:01:00Z',
      bid: { id: 99, bidder_name: 'Bob', amount: 1910000, created_at: '' },
    })

    expect(store.currentPrice).toBe(1910000)
    expect(store.bids).toHaveLength(1)
    expect(store.bids[0].bidder_name).toBe('Bob')
  })

  it('applyStatusChange updates status and winner', () => {
    const store = useAuctionStore()
    store.auction = { ...MOCK_AUCTION, status: 'active', bids: [] }

    store.applyStatusChange({
      auction_id:    1,
      status:        'ended',
      current_price: 1950000,
      remaining_seconds: 0,
      ends_at:       null,
      winner: { id: 5, bidder_name: 'Charlie', amount: 1950000 },
    })

    expect(store.isEnded).toBe(true)
    expect(store.winner?.bidder_name).toBe('Charlie')
  })

  it('isCurrentBidMine is true when myLastBid is top bid', () => {
    const store = useAuctionStore()
    const myBid = { id: 42, bidder_name: 'Me', amount: 2000000, created_at: '' }
    store.auction = { ...MOCK_AUCTION, bids: [myBid] }
    store.myLastBid = myBid

    expect(store.isCurrentBidMine).toBe(true)
  })
})
