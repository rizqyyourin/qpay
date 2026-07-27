# Docs Specification - QPay Merchant Checkout & Payment System

The QPay Merchant Checkout & Payment System is a modern micro-merchant POS and payment confirmation platform built with Laravel 12, Inertia.js (Vue), and Tailwind CSS. It enables sellers to showcase product catalogs, process online customer checkouts, execute instant POS manual cash sales, and track payment confirmations in real-time via live status polling (`GET /api/order/{code}/status`). Every purchase order generates a unique 6-character alphanumeric order code (e.g. `X8K2M9`). Scope covers public product purchase pages (`/buy/{product}`), cart checkout, seller dashboard metrics (monthly revenue, pending orders, confirmed sales), direct manual sales, live status polling, 60-second approval timers, and AI-enabled promotional assistant toggles.

**Version:** 0.1.0  
**Owner:** Rizqy  
**Last Updated:** 2026-07-27

## QPay Order & Payment System - Create

### Objectives

- Create purchase orders with unique 6-character order tracking codes (e.g., `QP-X8K2M9`) and initial `pending` status within 2 seconds of checkout.
- Support instant direct manual sales (`POST /products/{product}/manual-sale`) from the seller dashboard that auto-confirm order status and decrement product stock immediately.

### Assumptions and Constraints

- Merchant user must be authenticated and active (`ai_enabled` toggle optional for promo assistant).
- Product must exist in merchant catalog with `stock > 0` for direct stock deduction.
- Customer order codes are randomly generated from un-ambiguous character set (`ABCDEFGHJKLMNPQRSTUVWXYZ23456789`).

### Actors and Permissions

| Actor/Role | Permissions |
| --- | --- |
| Merchant / Seller | Manage products, trigger manual cash sales, confirm/cancel pending orders, toggle AI tools, view monthly revenue metrics |
| Customer / Buyer | View public product buy pages (`/buy/{product}`), add items to cart, submit checkout, poll order payment status |

### User Flow (Main)

Purpose: Primary user journey from entry to completion, focused on the happy path and key decisions.

```mermaid
graph TD
    A["Public Product Buy Page /buy/product"] --> B["Select Quantity & Click Checkout"]
    B --> C["Generate Unique 6-Char Order Code e.g. X8K2M9"]
    C --> D{"Stock Available for Item?"}
    D -->|Yes| E["Create Order with status: pending"]
    D -->|No| F["Display Out of Stock Alert"]
    F --> A
    E --> G["Redirect to Order Status Tracking Page /order/code"]
    G --> H["Client Performs Polling GET /api/order/code/status Every Few Seconds"]
```

### Error and Validation Flow

Purpose: Validation, permission, and system error paths, including user feedback and recovery behavior.

```mermaid
graph TD
    A["Submit Order Checkout Action"] --> B{"Product Belongs to Active Merchant?"}
    B -->|No| C["Return 404 Product Not Found"]
    B -->|Yes| D{"Requested Qty <= Available Stock?"}
    D -->|No| E["Return 422 Insufficient Stock Error"]
    D -->|Yes| F{"Unique Order Code Generation OK?"}
    F -->|No| G["Regenerate Code & Retry Database Commit"]
    F -->|Yes| H["Save Order & OrderItem Records with Status Pending"]
```

### Sequence Diagram - Create

Purpose: UI to API interactions for the create flow, including lookup calls and record insertion.

```mermaid
sequenceDiagram
    actor Customer
    participant UI
    participant API
    participant DB

    Customer->>UI: Visit product page /buy/product
    UI->>API: GET /buy/product
    API->>DB: Fetch Product details & seller info
    DB-->>API: Product model payload
    API-->>UI: Render product purchase view
    Customer->>UI: Select quantity & click Place Order
    UI->>API: POST /cart/checkout
    API->>DB: Generate 6-char code & insert `orders` (status: pending) and `order_items`
    DB-->>API: New Order ID & code (e.g. X8K2M9)
    API-->>UI: 200 OK + Order code payload
    UI-->>Customer: Redirect to /order/X8K2M9 status tracking page
```

### Acceptance Criteria

1. Customers can place an order from the public product page or cart view, generating a unique 6-character code.
2. Orders created via standard customer checkout start in `pending` status.
3. Merchant direct manual sales (`manualSale`) auto-confirm immediately, set status to `confirmed`, and decrement stock.

## QPay Order & Payment System - Update

### Objectives

- Support merchant manual order confirmation (`POST /orders/{order}/confirm`) or public approval after a 60-second safety cooldown window (`POST /api/order/{code}/approve`).
- Ensure product stock is re-verified and decremented atomically upon order confirmation, while updating merchant monthly revenue metrics.

### Assumptions and Constraints

- Public manual approval endpoint (`/api/order/{code}/approve`) requires at least 60 seconds to elapse (`diffInSeconds >= 60`) after creation to prevent immediate abuse.
- Orders in status `confirmed` or `cancelled` cannot be re-confirmed or modified.

### Actors and Permissions

| Actor/Role | Permissions |
| --- | --- |
| Merchant / Seller | Confirm pending orders, cancel orders, perform manual stock adjustments |
| Customer / Buyer | Poll order status, trigger customer-side cancellation or manual approval after 60s cooldown |

### User Flow (Main)

```mermaid
graph TD
    A["Merchant Dashboard / Customer Tracking Page"] --> B{"Select Order Action: Confirm / Approve / Cancel"}
    B -->|Merchant Confirm| C["POST /orders/order/confirm"]
    B -->|Public Approval| D["POST /api/order/code/approve"]
    B -->|Cancel Order| E["POST /orders/order/cancel"]
    C --> F{"Re-validate Stock Available?"}
    D --> G{"Has 60 Seconds Elapsed Since Creation?"}
    E --> H["Update Order Status to cancelled"]
    F -->|Yes| I["Decrement Stock & Update Status to confirmed"]
    F -->|No| J["Return Flash Error: Insufficient Stock"]
    G -->|Yes| I
    G -->|No| K["Return 403 Manual Approval Not Available Yet"]
    I --> L["Update Merchant Monthly Revenue & Dashboard Counts"]
```

### Sequence Diagram - Update

Purpose: UI to API interactions for update flow, including permission checks and side effects.

```mermaid
sequenceDiagram
    actor Merchant
    participant UI
    participant API
    participant DB

    Merchant->>UI: Open Dashboard pending orders list
    UI->>API: GET /dashboard
    API->>DB: Query pending orders with items & monthly stats
    DB-->>API: Pending orders dataset
    API-->>UI: Render pending orders card list
    Merchant->>UI: Click Confirm Payment on Order X8K2M9
    UI->>API: POST /orders/id/confirm
    API->>DB: Verify merchant ownership & status === 'pending'
    API->>DB: Check Product stock availability for each item
    alt Stock Sufficient
        API->>DB: Decrement product stock by item qty
        API->>DB: Update order status to `confirmed`
        DB-->>API: Order status updated
        API-->>UI: 302 Redirect back to /dashboard with updated metrics
        UI-->>Merchant: Display order in Confirmed Orders list & update Monthly Revenue
    else Stock Insufficient
        API-->>UI: 302 Redirect back with stock error
        UI-->>Merchant: Display error "Not enough stock for product X"
    end
```

### Acceptance Criteria

1. Merchant can confirm pending orders from the dashboard, which re-checks stock, decrements product inventory, and updates status to `confirmed`.
2. Public approval endpoint enforces a 60-second cooldown timer before allowing manual confirmation.
3. Cancelling a pending order transitions status to `cancelled` without modifying product stock.

## Shared Diagrams and References

### Error and Validation Flow

Purpose: Validation, permission, and system error paths, including user feedback and recovery behavior.

```mermaid
graph TD
    A["Submit Action"] --> B{"Permission / Ownership OK?"}
    B -->|No| C["Return 403 Forbidden Access Denied"]
    B -->|Yes| D{"Order Status === pending?"}
    D -->|No| E["Return 422 Order No Longer Pending"]
    D -->|Yes| F{"Stock Check OK?"}
    F -->|No| G["Return Error: Insufficient Stock"]
    F -->|Yes| H["Execute Status Update & Stock Adjustment"]
```

### Data Model (ERD)

Purpose: Tables, relations, and key constraints required by this feature.

```mermaid
erDiagram
    users ||--o{ products : owns
    users ||--o{ orders : receives
    orders ||--o{ order_items : contains
    products ||--o{ order_items : referenced_in

    users {
        bigint id PK
        string name
        string email UK
        string password
        boolean ai_enabled
        datetime created_at
        datetime updated_at
    }

    products {
        bigint id PK
        bigint user_id FK
        string name
        bigint price
        bigint discount
        integer stock
        text description
        string image
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    orders {
        bigint id PK
        bigint user_id FK
        string code UK
        enum status
        bigint total
        datetime created_at
        datetime updated_at
    }

    order_items {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        string product_name
        bigint price
        integer qty
        datetime created_at
        datetime updated_at
    }
```

### API Contract Reference

| No | File | Description |
| --- | --- | --- |
| 1 | [contract-api/feature-openapi.yaml](contract-api/feature-openapi.yaml) | OpenAPI spec for QPay store checkout, polling, order confirmation, and AI endpoints. |

### Mock Data Reference

| No | File | Description |
| --- | --- | --- |
| 1 | [mockoon/feature-mock.json](mockoon/feature-mock.json) | Mock endpoints and sample JSON payloads for QPay orders, products, and status polling. |

### State or Status Lifecycle (Optional)

Order statuses transition through: `pending` -> `confirmed` (or `cancelled`). Live status polling on `/api/order/{code}/status` enables real-time client UI updates.

### Edge Cases

- **Concurrent Checkout Stock Depletion**: Multi-buyer checkout where stock drops to zero before merchant confirmation triggers stock validation errors on `confirm`.
- **Approval Cooldown Restriction**: Calling `/api/order/{code}/approve` within 60 seconds of creation returns a 403 error.
- **Manual Cash Sale**: POS direct sale generates a 6-character code and immediately sets status to `confirmed` with stock decrement.

### Observability

- **Dashboard Real-Time Metrics**: Live computation of `monthly_revenue`, `monthly_orders`, and `confirmed_orders` list per merchant.
- **Logging & Error Capture**: Detailed exception logging in `DashboardController` and `ProductController` with UUID debug IDs for troubleshooting.
- **Status Polling Efficiency**: Lightweight JSON response (`{ status: 'pending'|'confirmed'|'cancelled' }`) for high-frequency client polling.

## Change Log

| Date | Author | Change |
| --- | --- | --- |
| 2026-07-27 | Rizqy | Updated specification after thorough codebase audit of QPay routes, Inertia controllers, live polling endpoints, manual sales, and database schema. |
