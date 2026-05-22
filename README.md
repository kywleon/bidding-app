# 🏷️ Bidding App

A real-time single-product bidding application built with **Vue 3** (frontend) and **Laravel 11** (backend), featuring live WebSocket updates via **Laravel Reverb**.

---

## ✅ Completed Features

| Level | Difficulty | Feature |
|-------|-----------|---------|
| Q1 | Normal ⭐⭐ | Single product bidding page, countdown timer, winner display |
| Q2 | Intermediate ⭐⭐⭐ | Real-time live bidding across multiple sessions via WebSocket |
| Q3 | Advanced ⭐⭐⭐⭐ | Unit tests — PHPUnit (backend) + Vitest (frontend) |
| Q4 | Extreme ⭐⭐⭐⭐⭐ | Cloud deployment — Railway (backend) + Vercel (frontend) |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue 3, Vite, Pinia, Axios, Laravel Echo |
| Backend | Laravel 11, PHP 8.2, MySQL |
| WebSocket | Laravel Reverb |
| Testing | PHPUnit (backend), Vitest + Vue Test Utils (frontend) |
| Deployment | Railway (backend) + Vercel (frontend) |

---

## 🚀 Local Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

### 1. Clone the repo

```bash
git clone https://github.com/<your-username>/bidding-app.git
cd bidding-app
```

### 2. Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bidding_app
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=bidding-app
REVERB_APP_KEY=bidding-app-key
REVERB_APP_SECRET=bidding-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

Run migrations and seed:

```bash
php artisan migrate
php artisan db:seed
```

Start the servers (2 terminals):

```bash
# Terminal 1 — API server
php artisan serve

# Terminal 2 — WebSocket server
php artisan reverb:start
```

### 3. Frontend Setup

```bash
cd frontend
npm install
cp .env.example .env
```

`.env` should contain:

```env
VITE_API_URL=http://localhost:8000
VITE_REVERB_APP_KEY=bidding-app-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Start the dev server:

```bash
npm run dev
```

Open **http://localhost:5173** in your browser.  
To test real-time bidding, open the page in **two browser tabs** at the same time.

---

## 🧪 Running Tests

### Backend (PHPUnit)

```bash
cd backend
php artisan test
```

Expected output:
```
Tests: 18 passed (47 assertions)
```

### Frontend (Vitest)

```bash
cd frontend
npm run test
```

Expected output:
```
Tests: 21 passed
```

---

## 🌐 Live Demo

| | URL |
|--|--|
| Frontend | https://bidding-app-frontend.vercel.app |
| Backend API | https://bidding-app-backend.up.railway.app |

---

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/auctions/{id}` | Get full auction state |
| POST | `/api/auctions/{id}/bids` | Place a bid |
| POST | `/api/auctions/{id}/end` | End an expired auction |

### POST `/api/auctions/{id}/bids` — Request Body

```json
{
  "bidder_name": "Alice",
  "amount": 1910000
}
```

> **Note:** `amount` is in **cents** (e.g. $19,100.00 = `1910000`)

### WebSocket Channel: `auction.{id}`

| Event | Trigger |
|-------|---------|
| `.bid.placed` | A new bid is submitted |
| `.auction.status` | Auction started or ended |

---

## 🏗️ Architecture Decisions

### 1. Amounts stored in cents (integers)
All bid amounts are stored as **integer cents** instead of `DECIMAL`. This eliminates floating-point comparison bugs, makes sorting correct, and avoids precision loss in JSON.

### 2. Laravel Reverb over Pusher
Using **Laravel Reverb** (official first-party WebSocket server) means zero external service dependency, no usage limits, and the same Laravel Echo API on the frontend.

### 3. `ShouldBroadcastNow` for instant delivery
Events use `ShouldBroadcastNow` (synchronous) instead of `ShouldBroadcast` (queued). This guarantees WebSocket messages are sent immediately without needing a separate queue worker.

### 4. Pinia store with WebSocket patch updates
All real-time state lives in Pinia. WebSocket events call `applyBidPlaced` / `applyStatusChange` to patch state in-place — no full page refresh or API re-fetch needed.

---

## 🔄 What I Would Do Differently

- **Rate limiting** — Add throttle middleware on the bids endpoint to prevent spam
- **Optimistic UI** — Show the bid immediately before API confirmation
- **User sessions** — Track the same user's bids across page refreshes
- **Queue workers** — Move broadcasting to a queue for better throughput under high load
- **Laravel Horizon** — Monitor queues in production

---

## 📝 Commit Structure

```
feat: initial project scaffold (Laravel + Vue 3)
feat: products, auctions, bids migrations and models
feat: bid placement API with validation
feat: WebSocket broadcast events (BidPlaced, AuctionStatusChanged)
feat: auction countdown and auto-end logic
feat: Vue auction store with Pinia
feat: useCountdown and useWebSocket composables
feat: AuctionPage view with all components
feat: BidForm, BidStatus, BidHistory, CountdownTimer components
test: PHPUnit backend unit and feature tests
test: Vitest frontend unit and component tests
chore: remove node_modules and vendor from tracking
deploy: Railway and Vercel deployment configs
docs: README
```
