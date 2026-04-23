# ProStay Africa - MVP ERP Roadmap

## Implemented foundation in this iteration

### Database
- Access control: roles, permissions, role_user, permission_role
- Customer and accommodation: customers, room_types, rooms, reservations, stays
- Service and catalog: service_areas, dining_tables, menu_categories, menus
- Inventory and laundry: product_categories, products, suppliers, stock_movements, laundry_items, laundry_operations
- Core sales and billing: orders, order_items, invoices, invoice_items, payments, pos_sessions
- Audit: audit_logs

### Rules encoded
- Nullable customer linkage supported on orders and invoices
- Nullable stay_id and room_id on orders and invoices
- Direct anonymous walk-in sales are supported
- Lodged customers are still modelled via stay and customer linkage when available

### Domain enums
- CustomerType, RoomStatus, TableStatus, ReservationStatus, StayStatus
- OrderStatus, InvoiceStatus, PaymentMethod
- ServiceAreaCode, LaundryItemStatus

### Business services
- Orders\OrderService: order creation with items and total recalculation
- Billing\InvoiceService: create invoice from one or many orders and calculate paid/balance
- Billing\PaymentService: record payment and refresh invoice state
- Pos\PosService: quick sale and direct payment flow

### Livewire MVP pages
- /customers: customer create + search
- /orders: flexible order intake with anonymous support
- /billing/invoices: invoice creation from order selection
- /pos: quick sale and payment in one flow

## Next step modules (recommended sequence)
1. Reservation and check-in/check-out workflows (full lifecycle)
2. Room availability board and room status transitions
3. Stock deduction automation linked to order items
4. Invoice item grouping by service area and stay
5. Payment reconciliation and cashier closure reports
6. Audit middleware/event observers for full traceability
7. KPI and reporting dashboards by period/user/module
