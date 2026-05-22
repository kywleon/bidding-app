import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BidStatus from '@/components/BidStatus.vue'

describe('BidStatus', () => {
  it('shows pending message when auction not started', () => {
    const wrapper = mount(BidStatus, {
      props: {
        isActive:      false,
        isEnded:       false,
        startingPrice: 1900000,
      },
    })
    expect(wrapper.text()).toContain('Bid')
    expect(wrapper.text()).toContain('to Start')
    expect(wrapper.text()).toContain('$19,000.00')
  })

  it('shows current price during active auction', () => {
    const wrapper = mount(BidStatus, {
      props: {
        isActive:     true,
        isEnded:      false,
        currentPrice: 2000000,
        bids: [{ id: 1, bidder_name: 'Alice', amount: 2000000, created_at: '' }],
      },
    })
    expect(wrapper.text()).toContain('$20,000.00')
    expect(wrapper.text()).toContain('Alice')
  })

  it('shows "Current Bid by You" when my bid is highest', () => {
    const wrapper = mount(BidStatus, {
      props: {
        isActive:         true,
        isEnded:          false,
        currentPrice:     2000000,
        isCurrentBidMine: true,
        bids: [{ id: 1, bidder_name: 'Me', amount: 2000000, created_at: '' }],
      },
    })
    expect(wrapper.text()).toContain('by You')
  })

  it('shows winner when auction ended', () => {
    const wrapper = mount(BidStatus, {
      props: {
        isActive: false,
        isEnded:  true,
        winner:   { bidder_name: 'Alice', amount: 2100000 },
      },
    })
    expect(wrapper.text()).toContain('Auction Ended')
    expect(wrapper.text()).toContain('Alice')
    expect(wrapper.text()).toContain('$21,000.00')
  })

  it('shows "You are the Winner" when I won', () => {
    const wrapper = mount(BidStatus, {
      props: {
        isActive: false,
        isEnded:  true,
        iWon:     true,
        winner:   { bidder_name: 'Me', amount: 2100000 },
      },
    })
    expect(wrapper.text()).toContain('You are the Winner')
  })
})
