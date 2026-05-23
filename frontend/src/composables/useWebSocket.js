import { onUnmounted } from "vue";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

let echoInstance = null;

function getEcho() {
  if (echoInstance) return echoInstance;

  window.Pusher = Pusher;

  echoInstance = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY ?? "bidding-app-key",
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    // wsHost: import.meta.env.VITE_REVERB_HOST ?? "localhost",
    // wsPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    // wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    // forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https",
    // enabledTransports: ["ws", "wss"],
    // disableStats: true,
    forceTLS: true,
  });

  return echoInstance;
}

/**
 * Subscribes to auction WebSocket channel and wires up event callbacks.
 *
 * @param {number|string} auctionId
 * @param {{ onBidPlaced: Function, onStatusChanged: Function }} callbacks
 */
export function useAuctionChannel(auctionId, { onBidPlaced, onStatusChanged }) {
  const echo = getEcho();
  const channel = echo.channel(`auction.${auctionId}`);

  channel
    .listen(".bid.placed", (e) => {
      console.log("📡 bid.placed received:", e);
      onBidPlaced?.(e);
    })
    .listen(".auction.status", (e) => {
      console.log("📡 auction.status received:", e);
      onStatusChanged?.(e);
    });

  onUnmounted(() => {
    echo.leaveChannel(`auction.${auctionId}`);
  });

  return { channel };
}
