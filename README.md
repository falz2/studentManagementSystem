# PoultryHub

PoultryHub is a mobile-friendly poultry marketplace for Uganda. It brings
smallholder farmers, poultry-feed suppliers, and consumers together in one
role-based web application. Farmers and suppliers publish products and manage
stock; consumers compare listings, place orders, and follow order status; an
administrator can review high-level marketplace activity.

All live marketplace prices and order totals are stored as whole Ugandan
shillings (**UGX**). The application does not convert currencies.

## What the system does

- Supports farmer, supplier, consumer, and administrator accounts with
  role-based access.
- Lets farmers list poultry products and feed, and lets suppliers list feed.
- Provides a seller-opt-in farm/supplier map and private live delivery
  tracking with Leaflet.
- Tracks seller-defined low-stock thresholds and optional product expiry dates.
- Lets signed-in users search active listings, filter by poultry or feed, and
  view seller and stock information.
- Keeps a shopping cart in the current browser session.
- Creates seller-specific orders at checkout, records a snapshot of each item,
  copies an optional saved delivery address, and updates product stock.
- Gives buyers an order history, delivery timeline, and cancellation for their
  own pending orders.
- Gives buyers and sellers private order-linked messaging, and lets buyers
  review items from completed orders.
- Gives sellers low-stock and expiry notifications, an order queue, product
  management, and filtered sales analytics.
- Lets consumers schedule weekly or monthly supplier-feed orders. Due schedules
  create ordinary pending orders after a stock check.
- Gives administrators a platform summary and recent marketplace orders.

Checkout records an order in PoultryHub; it does **not** charge a payment
method or connect to a payment gateway. Scheduled recurring orders also require
seller confirmation and do not charge a payment method. Delivery GPS is shared
from a rider's browser through an expiring PoultryHub link; no courier platform
or GPS hardware integration is connected.

## Roles and permissions

| Role | Main capabilities |
|---|---|
| Farmer | Create, edit, activate, deactivate, and remove poultry and feed listings; manage prices, stock alerts, expiry dates, and an opt-in public map pin; review incoming orders, delivery steps, messages, and analytics |
| Supplier | Create and manage feed listings; manage stock alerts, expiry dates, and an opt-in public map pin; review incoming orders, delivery steps, messages, and analytics |
| Consumer | Browse and search products; view opted-in farm pins; manage a session cart and saved addresses; check out, view delivery progress, message sellers, review completed purchases, schedule recurring supplier orders, and cancel a pending order |
| Admin | View platform counts and recent marketplace orders |

Public registration allows **farmer**, **supplier**, or **consumer** accounts.
Administrator access is reserved for trusted account setup. Seller management
routes require the farmer or supplier role, and sellers can only manage their
own products and orders. Existing accounts with the former student or lecturer
roles are directed to the marketplace.

### Development sample accounts

Seeded accounts all use the password `password`. They are for local
development only; change or remove these credentials before any shared or
production deployment.

| Role | Email |
|---|---|
| Admin | `admin@poultryhub.ug` |
| Farmer | `amina@poultryhub.ug` |
| Feed supplier | `feeds@poultryhub.ug` |
| Consumer | `shopper@poultryhub.ug` |

The role and marketplace seeders can be run repeatedly without duplicating
their sample accounts or listings:

```bash
php artisan db:seed
```

To grant a role to an existing account as a trusted administrator:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'account@example.com')->firstOrFail();
$user->assignRole('farmer'); // or supplier / consumer
```

## Main system flow

### 1. Registration and sign-in

Users register as a farmer, supplier, or consumer, then sign in through the
Laravel authentication pages. After sign-in, the dashboard sends each user to
the appropriate workspace. Admin accounts are created separately by trusted
setup.

### 2. Seller listings

Farmers and suppliers add a listing with a category, name, optional
description, whole-UGX price, stock quantity, unit, and optional photo.
Supported units are `bird`, `kg`, `tray`, and `bag`. Farmers can list poultry
products or feed; suppliers can list feed only. A listing can be active or
inactive. Sellers can set a low-stock threshold and an optional expiry date;
expired listings are hidden. Uploaded images use Laravel's public storage disk
and are limited to 2 MB.

### 3. Marketplace browsing

Signed-in users can browse active, in-stock listings. Search checks product
names, descriptions, and seller names. Category filters separate poultry
products from feed. Product pages show listing details, the seller, price, and
available quantity, and customer reviews. Expired and inactive listings are
not available to buyers.

### 4. Cart and checkout

The cart is stored in the user's session, not as a saved database cart. Users
can change quantities or remove items. At checkout, PoultryHub checks current
product status and stock again inside a database transaction. If a listing is
unavailable or the stock changed, checkout stops and asks the user to review
the cart. The buyer may choose a saved delivery address; checkout copies its
details onto each resulting order.

A cart containing products from multiple sellers is split into one order per
seller. Each order receives its own order number and stores a snapshot of item
names, category, unit, quantity, unit price, and line total. The matching
product stock is reduced in the same transaction. On success the session cart
is cleared.

### 5. Fulfillment, delivery, and messaging

Orders start as `pending`. The assigned seller confirms an order and can move
its delivery through `scheduled`, `in_transit`, and `delivered`. Delivery
milestones and the estimated arrival time appear in both buyer and seller order
views. Buyers may cancel only pending orders; cancellation restores stock.
The buyer and assigned seller can exchange private messages attached to an
order. While a confirmed order is in transit, the seller can create a private
rider link. A rider opens it on a phone, taps **Start sharing location**, and
grants browser GPS permission. The buyer and seller see the latest location on
the order page. Marketplace activity creates in-app notifications.

### 6. Farm map and location privacy

The farm/supplier map displays only sellers who have saved coordinates and
explicitly opted in to publish their business location. Sellers can capture
coordinates using their device GPS and can hide their pin at any time. The map
requires a signed-in account.

The rider link is a random, high-entropy bearer token shown only to the seller
when created. PoultryHub stores its SHA-256 hash, not the raw token. The link
expires after 12 hours and is revoked when the order is completed; only the
latest GPS point is stored, then cleared on delivery/revocation or by an hourly
expiry cleanup. Sellers can also revoke the link early, which immediately
clears the stored GPS point. GPS sharing requires browser location permission
and HTTPS (localhost is supported for development). Sharing pauses when the
rider stops the page's GPS watch; the last point remains visible until expiry
or completion. The map refreshes over HTTP polling every 10 seconds, not
WebSockets.

The map uses Leaflet with detailed OpenStreetMap street tiles. It requires an
internet connection but no API key; OpenStreetMap attribution is displayed on
the map. Tiles are fetched by the browser, so the map service receives tile
requests and their approximate map area. Farm and rider pins use real
latitude/longitude. The live map displays the rider's last point; it does not
calculate a road route or independently estimate travel time, so it is not a
turn-by-turn navigation system.

### 7. Saved addresses and reviews

Buyers can save and manage multiple delivery addresses. A chosen address is
copied into the order so later address edits do not change existing deliveries.
A buyer may review an item once its order is completed, with one review allowed
per purchased item.

### 8. Inventory and recurring supplier orders

The seller dashboard shows products at or below their low-stock threshold and
products whose expiry date has passed or is within 30 days. Scheduled checks
send in-app notifications. Consumers can create weekly or monthly schedules
for supplier feed products. The scheduler creates an ordinary pending order
only when the listing is active, unexpired, and sufficiently stocked.
Otherwise, it notifies the buyer and advances the next attempt. Recurring
orders do not automate payment.

### 9. Seller and admin dashboards

Seller dashboards summarize listings, inventory alerts, orders awaiting
confirmation, recent orders, top-selling products, and daily sales. Analytics
can be filtered to 7, 30, or 90 days; only completed sales in the selected
period contribute to its revenue and category reports. Administrators can see
overall product, farmer, supplier, consumer, and order counts, plus recent
orders.

## Scheduled marketplace tasks

The Laravel scheduler runs the recurring-order processor daily at 06:00,
inventory alert checks daily at 06:15, and clears expired delivery GPS points
hourly (application timezone). `composer run dev` starts `php artisan
schedule:work` alongside the local web server, queue listener, log viewer, and
Vite. In production, configure one system cron entry for
`php artisan schedule:run` each minute instead of running the development
worker. Run commands manually for local checks:

```bash
php artisan marketplace:process-recurring-orders
php artisan marketplace:send-inventory-alerts
php artisan delivery-shares:purge-expired
```

## Currency, prices, and order status

- Live PoultryHub product prices and order totals are integer amounts in
  **Ugandan shillings (UGX)**.
- The database stores monetary values as whole numbers; it does not apply
  exchange rates, taxes, delivery fees, or payment-provider fees.
- Product and order status values are stored in lowercase:
  - Products: `active`, `inactive`
  - Orders: `pending`, `confirmed`, `completed`, or `cancelled`
- Delivery status values are `not_scheduled`, `scheduled`, `in_transit`, and
  `delivered`. A delivery marked delivered also completes the order.
- A cancelled order is only possible from `pending`; its stock is restored.
- A completed order records its completion time and contributes to seller
  revenue analytics.

## Technology and architecture

- **Backend:** PHP 8.2+, Laravel 12, Eloquent ORM
- **Authentication:** Laravel Jetstream, Fortify, and Sanctum session support
- **Authorization:** Spatie Laravel Permission roles and route middleware
- **Views and frontend:** Blade, Tailwind CSS, Vite, and JavaScript
- **Database:** SQLite by default for local development; Laravel-supported
  database connections can be configured through `.env`
- **Scheduling:** Laravel task scheduler for recurring supplier orders and
  inventory alerts and GPS cleanup
- **Maps:** Leaflet with OpenStreetMap tiles (internet required; no API key)
- **Images:** Laravel public storage disk

`routes/web.php` defines the authenticated web workflows. Controllers validate
requests and enforce ownership, role, stock, and order-state rules. Blade
templates render role-aware portal, marketplace, seller, buyer-order, and
administration pages.

`routes/api.php` currently exposes only the Sanctum-authenticated
`GET /api/user` endpoint; the marketplace workflows are web routes.

### Main routes

| Route | Purpose | Access |
|---|---|---|
| `/dashboard` | Send the signed-in user to their workspace | Signed-in |
| `/marketplace` | Browse, search, and filter listings | Signed-in |
| `/marketplace/map` | View opt-in public farm/supplier pins | Signed-in |
| `/marketplace/{product}` | View a product | Signed-in |
| `/cart` and `/checkout` | Review a session cart and create orders | Signed-in |
| `/orders` | View or cancel the buyer's pending orders | Signed-in |
| `/delivery-share` | Open a private rider GPS-sharing page (token in URL fragment) | Private link |
| `/messages` and `/orders/{order}/messages` | View order conversations and send messages | Buyer or assigned seller |
| `/addresses` | Manage saved delivery addresses | Signed-in |
| `/notifications` | Review and mark in-app alerts as read | Signed-in |
| `/recurring-orders` | Manage supplier-feed order schedules | Consumer |
| `/seller` | Seller overview | Farmer or supplier |
| `/seller/products` | Manage the seller's listings | Farmer or supplier |
| `/seller/orders` | View and update the seller's orders | Farmer or supplier |
| `/seller/analytics` | View seller performance | Farmer or supplier |
| `/admin` | View marketplace summary | Admin |
| `/farmfresh-demo` | Open the separate interactive UI prototype | Public demo |

## Data model

The marketplace's main records are:

- **User:** login identity and assigned role.
- **Product:** seller, category, listing details, integer UGX price, stock,
  low-stock threshold, optional expiry date, unit, optional image path, and
  active/inactive status.
- **Order:** buyer, seller, unique order number, integer UGX total, notes,
  delivery address snapshot, fulfillment and delivery status, timestamps, and
  estimated arrival.
- **Order item:** purchased product snapshot, quantity, unit price, and line
  total. The snapshot keeps order history readable if a listing is removed.
- **User map location:** seller coordinates and explicit map visibility flag;
  pins are not public until the seller opts in.
- **Delivery share:** one hashed, expiring rider token and the latest GPS point
  for an in-transit order. Raw tokens are never stored.
- **Address:** buyer-owned saved contact and delivery location.
- **Review:** one rating per purchased item, allowed only for a completed order.
- **Conversation and message:** order-linked buyer/seller messages with read
  timestamps.
- **Recurring order:** buyer-owned weekly or monthly supplier-feed schedule.
- **Notification:** Laravel in-app notification for marketplace events.

An order belongs to one buyer and one seller and contains one or more order
items. Product ownership is checked on seller edits and deletes. Checkout
revalidates stock and writes orders, item snapshots, and stock decrements
atomically. Role storage is managed by Spatie Laravel Permission.

See [docs/database-design.md](docs/database-design.md) for table constraints
and delete behavior, and [docs/project-overview.md](docs/project-overview.md)
for the application architecture and user journeys. See
[docs/system-flow.md](docs/system-flow.md) for a brief end-to-end workflow, and
[docs/important-code-snippets.md](docs/important-code-snippets.md) for three
annotated examples of key project code.

## Requirements and local setup

- PHP 8.2 or later, with SQLite PDO enabled when using the default database
- Composer
- Node.js and npm

From the repository root:

```bash
composer run setup
php artisan db:seed
php artisan storage:link
composer run dev
```

`composer run setup` installs Composer and npm dependencies, creates `.env` if
needed, generates the application key, runs migrations, and builds frontend
assets. `php artisan db:seed` adds the development roles, sample accounts, and
sample listings. `composer run dev` starts the Laravel server, queue listener,
log viewer, and Vite development server.

Open the address printed by Laravel, usually `http://127.0.0.1:8000`. Check
`.env` if the project already has environment settings. The default local
database is SQLite at `database/database.sqlite`; set `DB_*` values before
migrating if you use another database.

## Tests and frontend build

Run the Laravel test suite:

```bash
php artisan test
```

Build production frontend assets:

```bash
npm run build
```

Feature tests cover registration, role authorization, marketplace workflows,
checkout and stock, saved addresses, delivery progression and GPS link privacy,
farm-map opt-in, reviews, messages, recurring orders, seller inventory alerts
and analytics, seller products, and model relationships.

## FarmFresh interactive prototype

`/farmfresh-demo` is a separate React and Tailwind UI prototype for exploring
farmer, buyer, and supplier screens. It includes sample dashboards, product
browsing and detail, cart and checkout, order tracking, chat, notifications,
and profile screens. Its controls update prototype UI state only; it does not
use PoultryHub accounts or create real listings, orders, or payments. A
service worker caches the prototype page, built assets, and farm photos after
they have been loaded online. This demo is separate from the live UGX
PoultryHub marketplace.

## Project layout

| Path | Purpose |
|---|---|
| `app/Http/Controllers/` | Dashboard, marketplace, cart, order, and seller workflows |
| `app/Models/` | User, product, order, and order-item relationships |
| `app/Actions/Fortify/` | Registration and account actions |
| `routes/web.php` | Web routes and role restrictions |
| `routes/api.php` | Sanctum user endpoint |
| `resources/views/` | Blade portal, marketplace, seller, order, and auth screens |
| `resources/js/`, `resources/css/` | Frontend bootstrap, FarmFresh React demo, and styles |
| `database/migrations/` | Marketplace and retained legacy schema |
| `database/seeders/` | Roles, local demo accounts, and listings |
| `tests/` | Feature and model tests |
| `docs/` | Database design, project overview, system flow, and key code snippets |

The earlier academic-management tables, models, and unused views remain in
the repository for compatibility with existing local data. They are not part
of the current PoultryHub routes or navigation.
