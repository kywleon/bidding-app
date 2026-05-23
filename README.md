# 🏷️ Bidding App

A real-time single-product bidding application built with **Vue 3** (frontend) and **Laravel 11** (backend), featuring live WebSocket updates via **Pusher**.

---

## ✅ Completed Features

| Level | Difficulty          | Feature                                                       |
| ----- | ------------------- | ------------------------------------------------------------- |
| Q1    | Normal ⭐⭐         | Single product bidding page, countdown timer, winner display  |
| Q2    | Intermediate ⭐⭐⭐ | Real-time live bidding across multiple sessions via WebSocket |
| Q3    | Advanced ⭐⭐⭐⭐   | Unit tests — PHPUnit (backend) + Vitest (frontend)            |
| Q4    | Extreme ⭐⭐⭐⭐⭐  | Cloud deployment — Railway (backend) + Vercel (frontend)      |

---

## 🛠️ Tech Stack

| Layer      | Technology                                            |
| ---------- | ----------------------------------------------------- |
| Frontend   | Vue 3, Vite, Pinia, Axios, Laravel Echo               |
| Backend    | Laravel 11, PHP 8.2, MySQL                            |
| WebSocket  | Pusher Channels                                       |
| Testing    | PHPUnit (backend), Vitest + Vue Test Utils (frontend) |
| Deployment | Railway (backend) + Vercel (frontend)                 |

---

## 🚀 Local Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL
- Pusher account (free tier)

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

Edit `.env` and set your credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bidding_app
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=ap1
```

Run migrations and seed:

```bash
php artisan migrate
php artisan db:seed
```

Start the server:

```bash
php artisan serve
```

### 3. Frontend Setup

```bash
cd frontend
npm install
cp .env.example .env
```

Edit `.env`:

```env
VITE_API_URL=http://localhost:8000
VITE_PUSHER_APP_KEY=your-app-key
VITE_PUSHER_APP_CLUSTER=ap1
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

|             | URL                                                           |
| ----------- | ------------------------------------------------------------- |
| Frontend    | https://bidding-b3mjudje1-kwok-yew-weng-s-projects.vercel.app |
| Backend API | https://bidding-app-production-241b.up.railway.app            |

---

## 📡 API Endpoints

| Method | Endpoint                  | Description            |
| ------ | ------------------------- | ---------------------- |
| GET    | `/api/auctions/{id}`      | Get full auction state |
| POST   | `/api/auctions/{id}/bids` | Place a bid            |
| POST   | `/api/auctions/{id}/end`  | End an expired auction |

### POST `/api/auctions/{id}/bids` — Request Body

```json
{
  "bidder_name": "Alice",
  "amount": 1910000
}
```

> **Note:** `amount` is in **cents** (e.g. $19,100.00 = `1910000`)

### WebSocket Channel: `auction.{id}`

| Event             | Trigger                  |
| ----------------- | ------------------------ |
| `.bid.placed`     | A new bid is submitted   |
| `.auction.status` | Auction started or ended |

---

## 🏗️ Architecture Decisions

### 1. Amounts stored in cents (integers)

All bid amounts are stored as **integer cents** instead of `DECIMAL`. This eliminates floating-point comparison bugs, makes sorting correct, and avoids precision loss in JSON.

### 2. Pusher Channels for WebSocket

Using **Pusher Channels** (free tier) for WebSocket broadcasting instead of self-hosted Reverb. This avoids the limitation of cloud platforms (like Railway free tier) that only expose a single HTTP port, making WebSocket connections impossible without a dedicated service.

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
- **Self-hosted WebSocket** — Use Laravel Reverb on a VPS with proper port configuration instead of Pusher

---
