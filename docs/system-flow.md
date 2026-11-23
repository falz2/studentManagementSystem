# PoultryHub System Flow

## Main order flow

1. **Sign in:** Farmers, suppliers, and consumers enter their role-based workspace. Admin access is managed separately.
2. **List and browse:** Farmers list poultry products; suppliers list feed. Consumers browse available listings and add items to their cart.
3. **Checkout:** PoultryHub checks stock, creates a separate order for each seller, saves item and delivery-address details, and reduces stock. Checkout records an order but does not process payment.
4. **Fulfill:** The seller confirms the order and updates delivery through **Scheduled → In transit → Delivered**.
5. **Track:** During delivery, the seller can create a private rider link. The rider may share GPS from a browser; the buyer and seller see updates on the order page. The Leaflet map uses online OpenStreetMap tiles.
6. **Complete:** Delivery completes the order. The buyer can review purchased items, and the seller can see completed sales in reports.

```text
Farmer / Supplier → List products → Consumer browses and checks out
        ↑                                      ↓
Seller fulfills ← Order created and stock updated
        ↓
Rider shares location → Buyer and seller track delivery
        ↓
Delivered → Buyer review · Seller reports
```

## Other system paths

- **Inventory:** Sellers manage stock and receive low-stock or expiry alerts.
- **Recurring feed:** A consumer schedules a weekly or monthly supplier order. When due, the system checks stock and creates a normal pending order for seller confirmation.
- **Messages and notifications:** Buyers and sellers can message about orders; marketplace events create in-app notifications.
- **Administration:** Admins review platform activity and recent orders.

See [project-overview.md](./project-overview.md) for role capabilities and feature details, and [database-design.md](./database-design.md) for data relationships.
