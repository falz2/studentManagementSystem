# PoultryHub Database Design

## Scope

PoultryHub stores seller listings, buyer orders, and immutable item snapshots
for marketplace history. Authentication and role assignments use Laravel and
Spatie-managed tables. The cart is stored in the user's session; it is checked
against live product stock at checkout.

## Entity relationships

```mermaid
erDiagram
    USERS ||--o{ PRODUCTS : lists
    USERS ||--o{ ORDERS : buys
    USERS o|--o{ ORDERS : fulfills
    USERS ||--o{ ADDRESSES : saves
    ORDERS ||--|{ ORDER_ITEMS : contains
    PRODUCTS o|--o{ ORDER_ITEMS : snapshot_of
    ORDER_ITEMS ||--o| REVIEWS : reviewed_by
    ORDERS ||--o| CONVERSATIONS : discusses
    CONVERSATIONS ||--o{ MESSAGES : contains
    USERS ||--o{ RECURRING_ORDERS : schedules
    USERS ||--o{ MESSAGES : sends
    ORDERS ||--o| DELIVERY_SHARES : tracks
    PRODUCTS ||--o{ RECURRING_ORDERS : supplies

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        decimal latitude
        decimal longitude
        boolean map_visible
    }
    PRODUCTS {
        bigint id PK
        bigint seller_id FK
        string category
        string name
        unsigned_bigint price
        unsigned_integer quantity
        string unit
        string status
        unsigned_integer low_stock_threshold
        date expires_at
    }
    ORDERS {
        bigint id PK
        bigint buyer_id FK
        bigint seller_id FK
        string order_number UK
        unsigned_bigint total
        string status
        text notes
        string delivery_status
        timestamp estimated_delivery_at
    }
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        string product_name
        string category
        string unit
        unsigned_integer quantity
        unsigned_bigint unit_price
        unsigned_bigint total
    }
    ADDRESSES {
        bigint id PK
        bigint user_id FK
        string label
        string recipient_name
        string phone
        string district
        string subcounty
        string village
        boolean is_default
    }
    REVIEWS {
        bigint id PK
        bigint order_item_id FK UK
        bigint product_id FK
        bigint buyer_id FK
        unsigned_tinyint rating
    }
    CONVERSATIONS {
        bigint id PK
        bigint order_id FK UK
        bigint buyer_id FK
        bigint seller_id FK
    }
    MESSAGES {
        bigint id PK
        bigint conversation_id FK
        bigint sender_id FK
        text body
        timestamp read_at
    }
    RECURRING_ORDERS {
        bigint id PK
        bigint buyer_id FK
        bigint seller_id FK
        bigint product_id FK
        unsigned_integer quantity
        string frequency
        timestamp next_run_at
        string status
    }
    DELIVERY_SHARES {
        bigint id PK
        bigint order_id FK UK
        char token_hash UK
        decimal latitude
        decimal longitude
        decimal accuracy_meters
        timestamp location_updated_at
        timestamp expires_at
        timestamp revoked_at
    }
```

`UK` denotes a unique constraint, and `FK` denotes a foreign key. All business
tables include Laravel timestamps.

## Tables and integrity rules

| Table | Responsibility and constraints |
|---|---|
| `users` | Login identity and optional seller coordinates. Only farmer/supplier accounts with `map_visible = true` and both coordinates appear on the signed-in map. |
| `products` | A farmer or supplier listing. `seller_id` references its owner; categories are poultry or feed; prices are whole UGX; quantity cannot be negative. |
| `orders` | One buyer-to-seller order, with a unique human-readable order number, whole-UGX total, notes, copied recipient/address details, fulfillment status, delivery status and timestamps. A cart containing multiple sellers becomes one order per seller. |
| `order_items` | Product and price snapshot at purchase time. Retains names, category, unit, quantity, and amounts if a listing is later deleted. |
| `addresses` | Buyer-owned saved delivery locations. Checkout copies the selected address onto the order. |
| `reviews` | One review per order item (`order_item_id` is unique); rating is accepted only from the buyer of a completed order. |
| `conversations` | One buyer/seller conversation per order (`order_id` is unique). Access is checked against the order participants. |
| `messages` | Conversation messages with a sender and optional read timestamp. |
| `recurring_orders` | Buyer-owned weekly or monthly supplier-feed schedule; each run creates an ordinary pending order after stock and listing checks. |
| `notifications` | Laravel database notifications for order, message, review, and inventory events. |
| `delivery_shares` | One expiring/revocable GPS-sharing token per order. The SHA-256 token hash and latest coordinate are stored; raw bearer tokens are not. |
| `roles`, `permissions`, and pivots | Spatie-managed role assignments. Public registration only permits farmer, supplier, and consumer. |

Deleting a user removes their product listings and buyer orders. If a seller
account is removed, the nullable `orders.seller_id` is cleared and order item
snapshots remain. Deleting a product clears `order_items.product_id` while
preserving the purchase record. Deleting an order cascades to its items.

Product ownership is checked for each seller edit/delete operation. Supplier
product validation only permits the feed category. Checkout re-reads and locks
all cart products in a transaction, verifies status and available quantities,
records item snapshots, and decrements stock in the same transaction. An
invalid or stale cart does not create partial orders.

Order status changes are restricted to `pending` → `confirmed` →
`completed`, and only the associated seller can update them. Delivery follows
`not_scheduled` → `scheduled` → `in_transit` → `delivered`; a delivered order
is also marked completed. Buyers may cancel their own pending order;
cancellation and stock restoration occur in one transaction. Analytics count
only completed order totals within the selected 7-, 30-, or 90-day range.
Expired product listings are hidden from the marketplace and rejected at
checkout. Inventory notification checks are deduplicated while a low-stock
condition remains active. Seller coordinates are shown only after an explicit
opt-in. A delivery-share token is generated only for an in-transit order and is
shared with a rider as a URL fragment; its hash is stored in the database.
Only the order buyer and seller can access the authenticated tracking endpoint.
The bearer-link endpoint supports rider updates and polling, is throttled, and
stops accepting updates after expiry, revocation, or delivery completion.
Delivery GPS is cleared on completion/revocation and by an hourly expiry
cleanup. The Leaflet map displays OpenStreetMap tiles, which require an internet
connection but no API key; GPS values are sent only to the PoultryHub
delivery-location endpoints.

## Implementation

The active schema is defined in
`database/migrations/2026_11_21_000001_create_poultryhub_marketplace_tables.php`
and
`database/migrations/2026_11_21_000002_add_marketplace_workflows.php` and
`database/migrations/2026_11_21_000003_add_leaflet_maps.php`.
`Product`, `Order`, `OrderItem`, `Address`, `Review`, `Conversation`, `Message`,
`RecurringOrder`, and `DeliveryShare` define the Eloquent relationships. Leaflet
renders maps in the browser; `FarmMapController` returns only opted-in seller
coordinates. The older
academic-management migration and model classes are retained for compatibility
with pre-existing local data, but are not used by current PoultryHub routes.
