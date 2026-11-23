# PoultryHub Project Overview

PoultryHub is a responsive, server-rendered marketplace for smallholder
poultry farmers, poultry-feed suppliers, and consumers in Uganda. It adapts
the project's existing Laravel role-based portal architecture to support
direct market access, feed availability, order tracking, and seller insights.

## Roles and user journeys

- **Farmer**: list poultry products and feed, maintain prices and stock, view
  low-stock and expiry alerts, opt in to a farm map pin, fulfill incoming
  orders, message buyers, and review sales analytics.
- **Supplier**: list poultry feed, manage inventory, receive customer orders,
  receive inventory alerts, opt in to a supplier map pin, message buyers, and
  review analytics.
- **Consumer**: browse or search active listings, filter by poultry/feed,
  see opted-in farm pins, manage a shopping cart and delivery addresses, place
  orders, track delivery, message sellers, review completed purchases, and
  schedule recurring feed.
- **Admin**: view marketplace activity and recent order summaries.

Registration exposes only the three marketplace roles. Laravel Fortify
creates the account and assigns the selected permitted role; admin access
remains separate. Spatie Laravel Permission middleware restricts seller
management routes.
Accounts left with the former `student` or `lecturer` role are directed to the
marketplace as buyers, so existing users retain a usable destination.

## Main workflows

1. Sellers create listings with a category, price in UGX, stock quantity,
   optional low-stock threshold and expiry date, unit, description, and photo.
2. Authenticated users browse in-stock marketplace listings. Search matches
   product name, description, and seller; category filters distinguish poultry
   products from feed.
3. Cart quantities are stored in the user's session. Checkout validates live
   inventory inside a database transaction, records order-item and optional
   delivery-address snapshots, decrements stock, and creates a separate order
   for each seller.
4. Sellers see only their assigned orders, confirm them, and progress delivery
   through scheduled, in transit, and delivered. Buyers can cancel pending
   orders; cancellation restores stock. Delivery status changes, messages,
   reviews, and inventory conditions create in-app notifications.
5. Once an order is in transit, its seller can create a private 12-hour rider
   link. The rider opts in to browser GPS sharing; the buyer and seller see the
   current point on a Leaflet map. The link is revoked when delivery ends, and
   expired GPS points are cleared by an hourly cleanup.
6. Sellers can capture GPS coordinates and explicitly opt in to publish their
   business pin on the signed-in farm/supplier map. Locations are private by
   default.
7. Buyers can message the assigned seller about an order, save multiple
   delivery addresses, and review purchased items after an order completes.
8. Consumers may schedule weekly or monthly supplier-feed orders. The scheduler
   creates due orders as pending only when the listing is available and has
   enough stock; payment remains manual and is never automated.
9. Seller dashboards show inventory/expiry alerts, incoming orders, completed
   revenue, top-selling products, and daily sales. Analytics can report on 7,
   30, or 90 days of completed orders.

The Leaflet basemap uses OpenStreetMap street tiles for detailed roads and
places. It requires an internet connection but no API key, and the map displays
OpenStreetMap attribution. Tiles are fetched directly by the browser, so
OpenStreetMap receives tile requests and their approximate map area.
Geolocation requires a secure browser context (HTTPS in deployment; localhost
during development). The app polls live delivery positions every 10 seconds
rather than using a WebSocket service. Map detail is for demonstrations, not
turn-by-turn navigation.

## Application architecture

`routes/web.php` defines authenticated marketplace, cart, checkout, orders,
seller, and admin routes. `DashboardController` redirects users according to
their role. Controllers validate requests and query the database; Blade views
render the existing portal layout with a role-specific sidebar and responsive
content area.

The existing Jetstream/Fortify authentication, account profile, password,
two-factor authentication, and session features remain in place. The portal
uses Tailwind CSS and Vite. Photos use Laravel's public storage disk; run
`php artisan storage:link` to serve uploaded images.

## Data model

`Product` belongs to its seller (`User`). An `Order` belongs to a buyer and
seller and contains `OrderItem` snapshots. The order item retains purchased
product details and prices so history remains readable if the original
listing is removed. Addresses are buyer-owned; reviews are unique per order
item; conversations are unique per order and accessible only to that order's
buyer and seller. Delivery-share records store only a hashed expiring token and
the latest GPS point. See [database-design.md](./database-design.md) for
foreign keys, delete behavior, and checkout integrity rules.

The earlier academic-management tables, models, and unused views remain
available for compatibility with existing local data. They are no longer
reachable through the PoultryHub routes or application navigation.

## Key directories

| Location | Purpose |
|---|---|
| `app/Http/Controllers/` | Marketplace, cart, checkout, seller, order, and dashboard workflows |
| `app/Models/` | User, product, order, and order-item relationships |
| `app/Actions/Fortify/` | Registration, password, and profile actions |
| `routes/web.php` | Authenticated application routes and role restrictions |
| `resources/views/` | Blade portal, marketplace, seller, order, and authentication screens |
| `resources/css/`, `resources/js/` | Tailwind styles and frontend bootstrap |
| `database/migrations/` | Marketplace schema and retained legacy schema |
| `database/seeders/` | Marketplace roles, sample accounts, and listings |
| `tests/` | Authentication, role access, listings, checkout, orders, and relationships |
