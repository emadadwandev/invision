# Invision Mobile App — Screen Design Reference

> **Platform:** Flutter (iOS & Android)  
> **Total Screens:** 51  
> **Router:** GoRouter (role-based redirect on launch)  
> **Entry:** `/login` → `/field-force-home` (field force) or `/dashboard` (admin/team leader)

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Dashboard & Inquiries](#2-dashboard--inquiries)
3. [Stores](#3-stores)
4. [Products](#4-products)
5. [Routes](#5-routes)
6. [Campaigns & Tasks](#6-campaigns--tasks)
7. [Sales Orders](#7-sales-orders)
8. [POS & Inventory](#8-pos--inventory)
9. [Notifications & Messaging](#9-notifications--messaging)
10. [Command Center](#10-command-center)
11. [Competitors](#11-competitors)
12. [QR / Barcode Scanner](#12-qr--barcode-scanner)
13. [GPS & Duty Tracking](#13-gps--duty-tracking)
14. [Offline & Sync](#14-offline--sync)
15. [Settings](#15-settings)
16. [Calendar & Sales Areas](#16-calendar--sales-areas)
17. [Export & Presentations](#17-export--presentations)
18. [Field Force Home](#18-field-force-home)
19. [Reports](#19-reports)

---

## 1. Authentication

---

### 1.1 Login Page

| Property | Details |
|---|---|
| **Route** | `/login` |
| **Screen Name** | Login |

**Description:**  
The app entry point. Users enter their credentials to authenticate. On success the router redirects automatically based on the user's role — field force users land on the Field Force Home, while admins and team leaders land on the main Dashboard. Failed attempts display inline error messages (401 Unauthorized, 422 Validation).

**Main Function:**  
Authenticate the user and route them to their role-specific home screen.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Email | Text field | Input email |
| Password | Text field | Input password (obscured) |
| Login | FilledButton | Submit credentials → `/dashboard` or `/field-force-home` |

**Navigation:**
- **On success:** `context.go('/dashboard')` or `context.go('/field-force-home')` based on role
- **No back navigation** — this is the root screen

---

## 2. Dashboard & Inquiries

---

### 2.1 Dashboard Page

| Property | Details |
|---|---|
| **Route** | `/dashboard` |
| **Screen Name** | Dashboard |

**Description:**  
The main home screen for admin, administrator, team leader, and account manager users. Displays KPI cards grouped by Overview, Sales, Routes, and Campaigns. A period selector (week / month / quarter / year) refreshes all metrics. Quick-access chips at the bottom let users jump to any major section of the app in one tap. Three inquiry tiles give direct access to Store, Sales, and Route inquiry screens.

**Main Function:**  
Central hub for performance monitoring and navigation to all app modules.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Period selector | PopupMenuButton | Filter KPIs: week / month / quarter / year |
| Logout | IconButton (Icons.logout) | `context.go('/login')` |
| Store Inquiry tile | NavTile (onTap) | `context.push('/inquiry/stores')` |
| Sales Inquiry tile | NavTile (onTap) | `context.push('/inquiry/sales')` |
| Route Inquiry tile | NavTile (onTap) | `context.push('/inquiry/routes')` |
| Stores chip | QuickChip | `context.push('/stores')` |
| Products chip | QuickChip | `context.push('/products')` |
| My Route chip | QuickChip | `context.push('/my-route')` |
| Routes chip | QuickChip | `context.push('/routes')` |
| Campaigns chip | QuickChip | `context.push('/campaigns')` |
| My Tasks chip | QuickChip | `context.push('/my-tasks')` |
| Sales chip | QuickChip | `context.push('/sales')` |
| My Orders chip | QuickChip | `context.push('/my-orders')` |
| POS chip | QuickChip | `context.push('/pos')` |
| Inventory chip | QuickChip | `context.push('/inventory')` |
| Notifications chip | QuickChip | `context.push('/notifications')` |
| Inbox chip | QuickChip | `context.push('/inbox')` |
| Assigned Tasks chip | QuickChip | `context.push('/assigned-tasks')` |
| Command Center chip | QuickChip | `context.push('/command-center')` |
| Reports chip | QuickChip | `context.push('/reports')` |

**Navigation:**
- Parent: Role-based redirect from `/login`
- Children: all major module routes listed above

---

### 2.2 Store Inquiry Page

| Property | Details |
|---|---|
| **Route** | `/inquiry/stores` |
| **Screen Name** | Store Inquiry |

**Description:**  
An advanced search and filter screen for browsing the store database. Users can search by store name or code and narrow results by category (Grocery, Pharmacy, Convenience, Supermarket) and rank (Gold, Silver, Bronze). Results display as a scrollable list with key store identifiers.

**Main Function:**  
Search and filter stores using multiple criteria.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Filter by name or code |
| Category | DropdownButtonFormField | Filter: Grocery / Pharmacy / Convenience / Supermarket |
| Rank | DropdownButtonFormField | Filter: Gold / Silver / Bronze |

**Navigation:**
- Back: AppBar back arrow → `/dashboard`

---

### 2.3 Sales Inquiry Page

| Property | Details |
|---|---|
| **Route** | `/inquiry/sales` |
| **Screen Name** | Sales Inquiry |

**Description:**  
Search and inspect sales orders across all reps. Users type an order number to locate a specific order or apply a status filter to see orders at a given stage of the fulfillment pipeline.

**Main Function:**  
Search sales orders by order number or status.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Order # | TextField | Search by order number |
| Status | DropdownButtonFormField | Filter: draft / pending / confirmed / delivered / cancelled |

**Navigation:**
- Back: AppBar back arrow → `/dashboard`

---

### 2.4 Route Inquiry Page

| Property | Details |
|---|---|
| **Route** | `/inquiry/routes` |
| **Screen Name** | Route Inquiry |

**Description:**  
Browse route instances across the organization and filter by execution status. Useful for supervisors monitoring which routes are in progress, completed, or pending.

**Main Function:**  
Inspect route execution status across all route plans.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Status | DropdownButtonFormField | Filter: pending / in_progress / completed / cancelled |

**Navigation:**
- Back: AppBar back arrow → `/dashboard`

---

## 3. Stores

---

### 3.1 Store List Page

| Property | Details |
|---|---|
| **Route** | `/stores` |
| **Screen Name** | Stores |

**Description:**  
A searchable list of all stores in the system. Each list tile shows the store name, code, and category. Tapping a store navigates to its full detail profile.

**Main Function:**  
Browse and search the store database.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Search by name or code |
| Search | FilledButton | Execute search |
| Store card | ListTile (onTap) | `context.push('/stores/${store.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap store → `/stores/:id`

---

### 3.2 Store Detail Page

| Property | Details |
|---|---|
| **Route** | `/stores/:id` |
| **Screen Name** | Store Details |

**Description:**  
A read-only profile of a single store showing all master data: name, code, category, rank, area, full address, GPS coordinates, active/inactive status, and the list of contacts associated with the store.

**Main Function:**  
Display complete store profile information.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/stores`

---

## 4. Products

---

### 4.1 Product List Page

| Property | Details |
|---|---|
| **Route** | `/products` |
| **Screen Name** | Products |

**Description:**  
A searchable catalog of all products. The search bar accepts product name, SKU, or barcode — making it fast to locate specific items whether you know the display name or only have a barcode. Tapping a product opens its detail view.

**Main Function:**  
Search and browse the product catalog.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Search by name, SKU, or barcode |
| Search | FilledButton | Execute search |
| Product card | ListTile (onTap) | `context.push('/products/${product.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap product → `/products/:id`

---

### 4.2 Product Detail Page

| Property | Details |
|---|---|
| **Route** | `/products/:id` |
| **Screen Name** | Product Details |

**Description:**  
Full product profile including name, SKU, active status, category, barcode, long description, and all configured price levels. Useful for field force to verify product specifications or pricing tiers on the go.

**Main Function:**  
Display complete product specification and pricing.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/products`

---

## 5. Routes

---

### 5.1 Route List Page

| Property | Details |
|---|---|
| **Route** | `/routes` |
| **Screen Name** | Route Plans |

**Description:**  
Displays all route plans in the system with status indicators (pending, active, completed). Users can search by route name. Designed for supervisors to monitor the overall route plan portfolio.

**Main Function:**  
Browse and search all route plans.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Filter routes by name |
| Search | FilledButton | Execute search |
| Route card | ListTile (onTap) | `context.push('/routes/${plan.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap route → `/routes/:id`

---

### 5.2 Route Detail Page

| Property | Details |
|---|---|
| **Route** | `/routes/:id` |
| **Screen Name** | Route Plan |

**Description:**  
Shows the full configuration of a route plan: name, visit frequency, start and end dates, assigned user, ordered list of stores in the sequence, and current status. Read-only — route planning is done in the backend portal.

**Main Function:**  
View the full configuration of a route plan including store sequence.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/routes`

---

### 5.3 My Route Page

| Property | Details |
|---|---|
| **Route** | `/my-route` |
| **Screen Name** | Today's Route |

**Description:**  
The primary operational screen for field force. Lists all stores scheduled for today in sequence. For each stop the user can check in (GPS-validated, must be within geofence) and manage the visit. A "Start Route" button activates the day's route session. Color indicators show visit status (pending, checked in, completed).

**Main Function:**  
Manage and execute today's store visit route with GPS check-in.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Start Route | FilledButton.icon (play_arrow) | Activate today's route session |
| Check In (per store) | FilledButton.icon (location_on) | GPS-validated check-in at store |
| Store tile | Gesture/tap | Open visit management for that stop |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap store → visit management sub-flow
- Check-in triggers geofence validation

---

## 6. Campaigns & Tasks

---

### 6.1 Campaign List Page

| Property | Details |
|---|---|
| **Route** | `/campaigns` |
| **Screen Name** | Campaigns |

**Description:**  
Lists all active and past campaigns. Each card shows campaign name, type, status, and date range. Users can search by campaign name to quickly locate a specific campaign.

**Main Function:**  
Browse and search all campaigns.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Filter by campaign name |
| Search | FilledButton | Execute search |
| Campaign card | ListTile (onTap) | `context.push('/campaigns/${campaign.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap campaign → `/campaigns/:id`

---

### 6.2 Campaign Detail Page

| Property | Details |
|---|---|
| **Route** | `/campaigns/:id` |
| **Screen Name** | Campaign Details |

**Description:**  
Full campaign profile page. Displays campaign type, description, current status, start/end dates, budget, amount spent, utilization percentage, number of tasks, and total entries. Gives field force the context they need before executing campaign activities.

**Main Function:**  
Display full campaign information including budget utilization and task count.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/campaigns`

---

### 6.3 My Tasks Page

| Property | Details |
|---|---|
| **Route** | `/my-tasks` |
| **Screen Name** | My Tasks |

**Description:**  
Lists all campaign tasks assigned to the current user. A filter menu lets users view tasks by status: All, Pending, In Progress, or Completed. Each task card shows the campaign name, task description, due date, and current status.

**Main Function:**  
View and filter campaign tasks assigned to the current user.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Status filter | PopupMenuButton (Icons.filter_list) | Filter: All / Pending / In Progress / Completed |
| Task card | onTap | Open task details |

**Navigation:**
- Back: AppBar back arrow → previous screen

---

## 7. Sales Orders

---

### 7.1 Sales Order List Page

| Property | Details |
|---|---|
| **Route** | `/sales` |
| **Screen Name** | Sales Orders |

**Description:**  
A searchable list of all sales orders. An FAB allows quick creation of a new order. Each order card shows order ID, store name, order date, total value, and status badge.

**Main Function:**  
Browse all sales orders and initiate new order creation.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Search by order number |
| Search | FilledButton | Execute search |
| Create order | FloatingActionButton (add) | `context.push('/sales/create')` |
| Order card | onTap | `context.push('/sales/${order.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap order → `/sales/:id`
- FAB → `/sales/create`

---

### 7.2 Create Order Page

| Property | Details |
|---|---|
| **Route** | `/sales/create` |
| **Screen Name** | Create Order |

**Description:**  
A form screen to build a new sales order. Users select products, set quantities, apply discounts per line item, and see a running total. Multiple items can be added or removed before final submission. Submitting pops back to the order list.

**Main Function:**  
Build and submit a new sales order with line items and pricing.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Add Item | TextButton.icon (Icons.add) | Add a new product line |
| Remove item | IconButton (Icons.delete) | Remove a line item |
| Create Order | FilledButton | Submit order → `context.pop()` |

**Navigation:**
- Back: AppBar back arrow or after submit → `/sales`

---

### 7.3 Sales Order Detail Page

| Property | Details |
|---|---|
| **Route** | `/sales/:id` |
| **Screen Name** | Order Details |

**Description:**  
Displays a single order's full details: header info (store, rep, date), all line items with unit prices and quantities, subtotal, taxes, and grand total. Action buttons appear dynamically based on the current order status, allowing the user to advance the order through its lifecycle.

**Main Function:**  
View full order details and execute status-based workflow actions.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Confirm Order | FilledButton *(if draft/pending)* | Move order to confirmed |
| Deliver | FilledButton *(if confirmed)* | Mark order as delivered |
| Cancel | OutlinedButton *(if cancellable)* | Cancel the order |
| Return | OutlinedButton *(if delivered)* | Initiate return |

**Navigation:**
- Back: AppBar back arrow → `/sales`

---

### 7.4 My Orders Page

| Property | Details |
|---|---|
| **Route** | `/my-orders` |
| **Screen Name** | My Orders |

**Description:**  
A personal order history screen showing only orders created by the current user. A filter menu narrows by status (All, Draft, Confirmed, Delivered). An FAB allows creating a new order directly from here.

**Main Function:**  
View personal order history with status filtering and quick order creation.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Status filter | PopupMenuButton (Icons.filter_list) | Filter: All / Draft / Confirmed / Delivered |
| Create order | FloatingActionButton (add) | `context.push('/sales/create')` |
| Order card | onTap | `context.push('/sales/${order.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen
- Forward: Tap order → `/sales/:id`

---

## 8. POS & Inventory

---

### 8.1 POS Transaction List Page

| Property | Details |
|---|---|
| **Route** | `/pos` |
| **Screen Name** | POS Transactions |

**Description:**  
A searchable list of all point-of-sale transactions. Each card summarizes the transaction amount, store, type (sell-out, sell-through, return), and date. Users tap a transaction for the full receipt breakdown.

**Main Function:**  
Browse and search all POS transactions.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Search transactions |
| Search | FilledButton | Execute search |
| Transaction card | onTap | `context.push('/pos/${transaction.id}')` |

**Navigation:**
- Forward: Tap transaction → `/pos/:id`

---

### 8.2 POS Transaction Detail Page

| Property | Details |
|---|---|
| **Route** | `/pos/:id` |
| **Screen Name** | Transaction Detail |

**Description:**  
Full receipt view of a single POS transaction showing all line items, unit prices, quantities, totals, payment method, and transaction status. Read-only; acts as an on-device receipt record.

**Main Function:**  
Display the complete receipt for a single POS transaction.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/pos`

---

### 8.3 Store Inventory Page

| Property | Details |
|---|---|
| **Route** | `/inventory` |
| **Screen Name** | Store Inventory |

**Description:**  
Displays current stock levels for all products at the assigned store. Items at or below the low-stock threshold are visually highlighted with alert indicators. Users can search by product name to find specific SKUs.

**Main Function:**  
Monitor real-time store inventory and identify low-stock items.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Filter by product name |
| Search | FilledButton | Execute search |

**Navigation:**
- Back: AppBar back arrow → previous screen

---

### 8.4 My Transactions Page

| Property | Details |
|---|---|
| **Route** | `/my-transactions` |
| **Screen Name** | My POS Transactions |

**Description:**  
Personal POS transaction history for the logged-in user. A filter menu allows narrowing by transaction type: All, Sell Out, Sell Through, or Return.

**Main Function:**  
View personal POS transaction history filtered by transaction type.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Type filter | PopupMenuButton (Icons.filter_list) | Filter: All / Sell Out / Sell Through / Return |
| Transaction card | onTap | `context.push('/pos/${transaction.id}')` |

**Navigation:**
- Back: AppBar back arrow → previous screen

---

## 9. Notifications & Messaging

---

### 9.1 Notifications Page

| Property | Details |
|---|---|
| **Route** | `/notifications` |
| **Screen Name** | My Notifications |

**Description:**  
A chronological list of all system and user-generated notifications. Unread notifications are visually distinguished. Users can mark individual notifications as read or mark all at once. Notification types include route alerts, task assignments, campaign updates, and system messages.

**Main Function:**  
View and manage all in-app notifications with read/unread tracking.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Mark All Read | TextButton | Mark all notifications as read |
| Mark single read | IconButton (mark_email_read) | Mark one notification as read |

**Navigation:**
- Back: AppBar back arrow → previous screen

---

### 9.2 Inbox Page

| Property | Details |
|---|---|
| **Route** | `/inbox` |
| **Screen Name** | Inbox |

**Description:**  
Direct messages inbox. Users can search by sender name or message subject. Each conversation tile shows preview text, sender, and timestamp. Tapping opens the full message.

**Main Function:**  
Browse and search direct messages received by the user.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Filter messages |
| Search | FilledButton | Execute search |
| Message tile | onTap | `context.push('/messages/${message.id}')` |

**Navigation:**
- Forward: Tap message → `/messages/:id`

---

### 9.3 Message Detail Page

| Property | Details |
|---|---|
| **Route** | `/messages/:id` |
| **Screen Name** | Message |

**Description:**  
Full text view of a single message. Displays subject, sender, timestamp, full body, and the list of recipients with their individual read timestamps. Read-only view.

**Main Function:**  
Read a full message and see recipient read receipts.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/inbox`

---

### 9.4 My Assigned Tasks Page

| Property | Details |
|---|---|
| **Route** | `/assigned-tasks` |
| **Screen Name** | My Assigned Tasks |

**Description:**  
All tasks that have been assigned to the current user, regardless of campaign. Filter chips at the top let users quickly toggle between all statuses. Each card shows task name, due date, campaign context, and current status.

**Main Function:**  
View and filter all tasks assigned to the current user.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Status filter chips | FilterChip (multiple) | Filter: All / Pending / In Progress / Completed / etc. |
| Task card | onTap | `context.push('/assigned-tasks/${task.id}')` |

**Navigation:**
- Forward: Tap task → `/assigned-tasks/:id`

---

### 9.5 Task Assignment Detail Page

| Property | Details |
|---|---|
| **Route** | `/assigned-tasks/:id` |
| **Screen Name** | Task Details |

**Description:**  
Detailed view of a specific assigned task. Shows the task title, description, deadline, campaign context, and current status. Users can write completion notes in a text field and mark the task as complete.

**Main Function:**  
View task details and submit task completion with notes.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Completion notes | TextField | Enter notes before completing |
| Mark Complete | FilledButton.icon (Icons.check) | Submit completion |

**Navigation:**
- Back: AppBar back arrow → `/assigned-tasks`

---

## 10. Command Center

---

### 10.1 Command Center Page

| Property | Details |
|---|---|
| **Route** | `/command-center` |
| **Screen Name** | Command Center |

**Description:**  
A real-time interactive map (flutter_map) showing live positions of all field force and pinned store locations. WebSocket connection keeps positions up to date — a status indicator in the AppBar shows connection state (green = connected, grey = disconnected, red = error). Supervisors can toggle field force or store layers on/off and tap on any user marker to view their activity trail.

**Main Function:**  
Real-time GPS tracking of field force on an interactive map with store overlay.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Toggle field force | IconButton (Icons.people) | Show/hide field force markers |
| Toggle stores | IconButton (Icons.store) | Show/hide store pin markers |
| Refresh | IconButton (Icons.refresh) | Reload positions |
| WebSocket indicator | Status icon | Visual only — shows live connection status |
| User marker | Map tap | `context.push('/command-center/user/${userId}')` |

**Navigation:**
- Forward: Tap field force marker → `/command-center/user/:id`

---

### 10.2 User Activity Page

| Property | Details |
|---|---|
| **Route** | `/command-center/user/:id` |
| **Screen Name** | User Activity |

**Description:**  
Displays the GPS movement trail of a specific field force member on a map. A polyline traces the path taken during duty hours, with distinct start and end markers and a current location pin. Useful for reviewing an individual's movements for a given day.

**Main Function:**  
Review GPS trail and movement history of a specific field force user.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/command-center`

---

## 11. Competitors

---

### 11.1 Competitor List Page

| Property | Details |
|---|---|
| **Route** | `/competitors` |
| **Screen Name** | Competitors |

**Description:**  
A searchable list of all tracked competitors. An analytics icon in the AppBar opens the aggregated analysis view. Tapping a competitor opens their profile and product list.

**Main Function:**  
Browse competitors and access competitor analysis.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Search input | TextField | Filter competitors |
| Search | FilledButton | Execute search |
| Analytics | IconButton (Icons.analytics_outlined) | `context.push('/competitors/analysis')` |
| Competitor card | onTap | `context.push('/competitors/${competitor.id}')` |

**Navigation:**
- Forward: Tap competitor → `/competitors/:id`
- AppBar icon → `/competitors/analysis`

---

### 11.2 Competitor Detail Page

| Property | Details |
|---|---|
| **Route** | `/competitors/:id` |
| **Screen Name** | Competitor Detail |

**Description:**  
Profile page for a single competitor showing brand information, category, and a list of their tracked products. Provides context for field observations.

**Main Function:**  
Display competitor profile and their product catalog.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| *(no primary action buttons)* | — | — |

**Navigation:**
- Back: AppBar back arrow → `/competitors`

---

### 11.3 Competitor Analysis Page

| Property | Details |
|---|---|
| **Route** | `/competitors/analysis` |
| **Screen Name** | Competitor Analysis |

**Description:**  
Aggregated analysis of all competitor observations recorded by the field force. A date range filter allows viewing observations for a specific period. Data is visualized to highlight trends in competitor activity across observation types (sales, pricing, POSM, stock levels).

**Main Function:**  
Analyze aggregated competitor observation data with date range filtering.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Date range | IconButton (Icons.date_range) | Open date range picker |

**Navigation:**
- Back: AppBar back arrow → `/competitors`

---

### 11.4 Add Observation Page

| Property | Details |
|---|---|
| **Route** | `/competitors/observe/:storeId` |
| **Screen Name** | Record Observation |

**Description:**  
A data entry form for recording a competitor observation at a specific store. The user selects the observation type (Sales, POSM, Pricing, Display, Promotion, Stock Level, Other), picks the competitor and product from dropdowns, enters values, and submits. This data feeds the competitor analysis dashboards.

**Main Function:**  
Submit a competitor observation tied to a specific store visit.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Observation type | Segmented selector | Select: Sales / POSM / Pricing / Display / Promotion / Stock Level / Other |
| Competitor | DropdownButtonFormField | Select competitor |
| Product | DropdownButtonFormField | Select product |
| Submit | FilledButton | Save observation → `context.pop()` |

**Navigation:**
- Typically opened from within a store visit flow
- Back: AppBar back arrow or Submit → previous screen

---

## 12. QR / Barcode Scanner

---

### 12.1 QR Scanner Page

| Property | Details |
|---|---|
| **Route** | `/scanner` |
| **Screen Name** | Scan QR / Barcode |

**Description:**  
A full-screen camera scanner using `mobile_scanner`. The purpose (QR check-in, barcode lookup, coupon capture) is configured by the calling screen. On successful scan the raw value is returned via `context.pop(barcode.rawValue)` to the caller. Includes torch control and camera-switch for usability in various lighting conditions. A manual entry option handles damaged barcodes.

**Main Function:**  
Scan QR codes and barcodes and return the raw value to the calling screen.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Torch toggle | IconButton (flash_on/flash_off) | Enable/disable flashlight |
| Camera switch | IconButton (Icons.cameraswitch) | Toggle front/rear camera |
| Manual entry | TextButton | Manually type a code |

**Navigation:**
- Returns scanned value to caller via `context.pop(value)`
- No persistent forward navigation — modal/push overlay pattern

---

## 13. GPS & Duty Tracking

---

### 13.1 Duty Tracking Page

| Property | Details |
|---|---|
| **Route** | `/duty` |
| **Screen Name** | Duty Tracking |

**Description:**  
Controls and displays the GPS tracking session. Field force users start their duty at the beginning of the workday and end it when finished. While on duty, GPS position is continuously logged and streamed. Status icons reflect whether GPS fix is active or unavailable.

**Main Function:**  
Start and end the GPS duty tracking session with live status feedback.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Start / End Duty | FilledButton | Toggle duty tracking on/off |
| GPS status | Icon indicator | Shows GPS fix status (visual only) |

**Navigation:**
- Back: AppBar back arrow → Field Force Home or previous screen

---

### 13.2 Geo-Fence Check Page

| Property | Details |
|---|---|
| **Route** | `/geofence-check` |
| **Screen Name** | Geo-Fence Check |

**Description:**  
Validates whether the user's current GPS position falls within the defined geofence radius of a target store (default: 5 metres). Displays the calculated distance and a clear pass/fail indicator. If outside the fence the user can retry once they are closer.

**Main Function:**  
Validate the user's proximity to a store before allowing a check-in.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Retry | FilledButton | Re-check current GPS position |
| Distance indicator | Display | Shows distance to store geofence (visual only) |

**Navigation:**
- Typically opened automatically before check-in
- Result passed back to calling screen

---

## 14. Offline & Sync

---

### 14.1 Sync Status Page

| Property | Details |
|---|---|
| **Route** | `/sync-status` |
| **Screen Name** | Sync Status |

**Description:**  
Shows the current connectivity state (online/offline) and lists any queued actions that were performed offline and are waiting to be synced to the server. Users can manually trigger synchronisation. Pending action count is shown for transparency.

**Main Function:**  
Monitor offline/online status and manually trigger data synchronisation.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Sync (AppBar) | IconButton (Icons.sync) | Trigger background sync |
| Sync Now | FilledButton.icon | Trigger sync immediately |

**Navigation:**
- Back: AppBar back arrow → previous screen

---

## 15. Settings

---

### 15.1 MFA Setup Page

| Property | Details |
|---|---|
| **Route** | `/settings/mfa` *(inferred)* |
| **Screen Name** | MFA Setup |

**Description:**  
Allows users to enrol in or disable multi-factor authentication. During enrolment the screen guides the user through the code-confirmation flow. To disable MFA the user must confirm with their password. Supports both TOTP and SMS-based OTP flows.

**Main Function:**  
Enable, confirm, or disable multi-factor authentication for the account.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Enable MFA | FilledButton | Initiate MFA enrolment |
| Confirm Code | FilledButton | Submit OTP code input |
| Disable MFA | FilledButton | Disable MFA (requires password) |
| Code input | TextField | Enter OTP or verification code |
| Password input | TextField | Confirm identity to disable |

**Navigation:**
- Back: AppBar back arrow → Settings

---

### 15.2 Language Settings Page

| Property | Details |
|---|---|
| **Route** | `/settings/language` *(inferred)* |
| **Screen Name** | Language |

**Description:**  
A simple preference screen where users select their preferred app language. Three languages are supported: English, Arabic, and French. The current selection is marked with a checkmark. Selection takes effect immediately (app-wide locale change via Riverpod).

**Main Function:**  
Select the app display language.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| English | ListTile (onTap) | Set locale to English |
| Arabic | ListTile (onTap) | Set locale to Arabic |
| French | ListTile (onTap) | Set locale to French |

**Navigation:**
- Back: AppBar back arrow → Settings

---

## 16. Calendar & Sales Areas

---

### 16.1 Calendar Page

| Property | Details |
|---|---|
| **Route** | `/calendar` *(inferred)* |
| **Screen Name** | Calendar |

**Description:**  
A two-tab view showing upcoming Events and public Holidays. Events include campaign launches, route activations, and team meetings. The holiday tab helps field force plan around non-working days.

**Main Function:**  
View scheduled events and holidays in a tabbed calendar interface.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Events tab | TabBar | Switch to events list |
| Holidays tab | TabBar | Switch to holidays list |

**Navigation:**
- Back: AppBar back arrow → previous screen

---

### 16.2 Sales Area List Page

| Property | Details |
|---|---|
| **Route** | `/sales-areas` *(inferred)* |
| **Screen Name** | Sales Areas |

**Description:**  
Hierarchical display of the geographic sales area structure assigned to the current user or viewable by the admin. Top-level sectors expand to show cities, districts, locations, and streets. Tapping an area opens its detail.

**Main Function:**  
Browse the hierarchical sales territory structure.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Area row | onTap | `context.push('/sales-areas/${area.id}')` |

**Navigation:**
- Forward: Tap area → `/sales-areas/:id`

---

### 16.3 Sales Area Detail Page

| Property | Details |
|---|---|
| **Route** | `/sales-areas/:id` |
| **Screen Name** | Sales Area |

**Description:**  
Detail view of a single sales territory unit. Shows area name, level in the hierarchy (sector/city/district/location/street), GPS polygon or center coordinates, and a list of child sub-areas.

**Main Function:**  
Display details and sub-areas for a specific sales territory.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Sub-area row | onTap | Navigate to child area |

**Navigation:**
- Back: AppBar back arrow → `/sales-areas`

---

## 17. Export & Presentations

---

### 17.1 Export Dashboard Page

| Property | Details |
|---|---|
| **Route** | `/exports` *(inferred)* |
| **Screen Name** | Export & Presentations |

**Description:**  
A hub screen providing quick-action cards to the four export and presentation sub-sections: Market Review presentation, Report Templates, Presentation Templates, and Export History. Designed as an entry point rather than a data-heavy screen.

**Main Function:**  
Central navigation hub for export and presentation features.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Market Review card | QuickActionCard | `context.push('/presentations/market-review')` |
| Report Templates card | QuickActionCard | `context.push('/report-templates')` |
| Presentation Templates card | QuickActionCard | `context.push('/presentation-templates')` |
| Export History card | QuickActionCard | `context.push('/saved-exports')` |

**Navigation:**
- All four cards navigate to their respective sub-screens

---

### 17.2 Market Review Page

| Property | Details |
|---|---|
| **Route** | `/presentations/market-review` |
| **Screen Name** | Market Review |

**Description:**  
An interactive slide-based presentation of market performance data. A period selector (week/month/quarter/year) refreshes the dataset. Users swipe or tap previous/next to navigate between slides covering KPIs, top stores, product rankings, and campaign summaries.

**Main Function:**  
Present market performance data as interactive slides with period filtering.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Period selector | PopupMenuButton | Filter: week / month / quarter / year |
| Previous slide | IconButton (arrow_back) | Navigate to previous slide |
| Next slide | IconButton (arrow_forward) | Navigate to next slide |

**Navigation:**
- Back: AppBar back arrow → Export Dashboard

---

### 17.3 Report Templates Page

| Property | Details |
|---|---|
| **Route** | `/report-templates` |
| **Screen Name** | Report Templates |

**Description:**  
Manage saved report templates. Lists all existing templates with their name, data source, and creation date. An FAB lets users create a new template from scratch.

**Main Function:**  
View, create, and manage report templates.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Create template | FloatingActionButton (add) | Open template creation form |
| Template card | onTap | Open/edit template |

**Navigation:**
- Back: AppBar back arrow → Export Dashboard

---

### 17.4 Saved Exports Page

| Property | Details |
|---|---|
| **Route** | `/saved-exports` |
| **Screen Name** | Export History |

**Description:**  
A log of all previously generated exports (Excel, PDF, PowerPoint). Each record shows the report name, export format, generation time, and file size. Users can delete export records they no longer need.

**Main Function:**  
View and manage the history of generated exports.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Delete | IconButton (Icons.delete_outline) | Remove export record |

**Navigation:**
- Back: AppBar back arrow → Export Dashboard

---

## 18. Field Force Home

---

### 18.1 Field Force Home Page

| Property | Details |
|---|---|
| **Route** | `/field-force-home` |
| **Screen Name** | Field Force Home |

**Description:**  
The dedicated home screen for field force, promoter, merchandiser, and sales rep users. Uses a bottom navigation bar with three tabs: **Today** (duty tracking + today's route), **Notifications** (with unread count badge), and **Profile** (user info, targets, working hours). The AppBar shows a personalised greeting and a quick-access notification bell.

**Main Function:**  
Role-specific home screen combining duty management, route access, notifications, and user profile in one tabbed shell.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Today tab | NavigationBar destination | Switch to Duty + Route tab |
| Notifications tab | NavigationBar destination | Switch to Notifications tab (shows badge count) |
| Profile tab | NavigationBar destination | Switch to Profile tab |
| Notification bell | IconButton (Icons.notifications_outlined) | Quick jump to `/notifications` |

**Navigation:**
- Tab 1 (Today): Embeds Duty Tracking + My Route
- Tab 2 (Notifications): Embeds Notifications Page
- Tab 3 (Profile): Embeds Profile/User info view

---

## 19. Reports

---

### 19.1 Reports List Page

| Property | Details |
|---|---|
| **Route** | `/reports` |
| **Screen Name** | Reports |

**Description:**  
Showcases all available fixed report templates categorised by type (sell-through, sell-out, sell-in, stock movement, vendor ranking, sales rep performance). Each report card shows the report name and a brief description. A button in the AppBar opens the custom Report Builder.

**Main Function:**  
Browse available fixed reports and access the custom report builder.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Report Builder | TextButton.icon (Icons.build_outlined) | `context.push('/reports/builder')` |
| Report card | InkWell (onTap) | `context.push('/reports/${report.slug}')` |

**Navigation:**
- Forward: Tap report → `/reports/:slug`
- AppBar button → `/reports/builder`

---

### 19.2 Report Detail Page

| Property | Details |
|---|---|
| **Route** | `/reports/:slug` |
| **Screen Name** | Report (dynamic title) |

**Description:**  
Renders the data for a specific fixed report. A date range chip bar at the top lets users select the reporting period. After setting the period the user taps Apply to refresh the data grid. Results can be exported to Excel or PDF via a download menu. Filters can be cleared with the clear icon.

**Main Function:**  
View fixed report data with date range filtering and export functionality.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Date chips | DateChip components | Select reporting period |
| Apply | FilledButton.tonal | Apply date filter and reload data |
| Clear filters | IconButton (Icons.clear) | Reset date filter |
| Export | PopupMenuButton (Icons.download) | Export as Excel or PDF |

**Navigation:**
- Back: AppBar back arrow → `/reports`

---

### 19.3 Report Builder Page

| Property | Details |
|---|---|
| **Route** | `/reports/builder` |
| **Screen Name** | Report Builder |

**Description:**  
A dynamic report configuration screen that allows users to select any data source entity (stores, sales, routes, campaigns, etc.), a grouping dimension, sort direction, and row limit. On running the report, results are rendered in the same screen below the configuration panel. Enables unlimited custom analytics without requiring backend changes.

**Main Function:**  
Configure and run custom ad-hoc reports against any data entity.

**Buttons & Actions:**

| Element | Type | Action |
|---|---|---|
| Data Source | DropdownButtonFormField | Select entity to report on |
| Group By | DropdownButtonFormField | Select grouping dimension |
| Order Direction | DropdownButtonFormField | Descending / Ascending |
| Limit | TextFormField | Set maximum row count |
| Run Report | FilledButton.icon (Icons.play_arrow) | Execute report and display results |

**Navigation:**
- Back: AppBar back arrow → `/reports`

---

## Navigation Map

```
/login
  ├── /dashboard (admin, team leader, account manager)
  │     ├── /inquiry/stores
  │     ├── /inquiry/sales
  │     ├── /inquiry/routes
  │     ├── /stores → /stores/:id
  │     ├── /products → /products/:id
  │     ├── /routes → /routes/:id
  │     ├── /my-route
  │     ├── /campaigns → /campaigns/:id
  │     ├── /my-tasks
  │     ├── /sales → /sales/:id
  │     │     └── /sales/create
  │     ├── /my-orders
  │     ├── /pos → /pos/:id
  │     ├── /inventory
  │     ├── /my-transactions
  │     ├── /notifications
  │     ├── /inbox → /messages/:id
  │     ├── /assigned-tasks → /assigned-tasks/:id
  │     ├── /command-center → /command-center/user/:id
  │     ├── /reports → /reports/:slug
  │     │     └── /reports/builder
  │     ├── /competitors → /competitors/:id
  │     │     ├── /competitors/analysis
  │     │     └── /competitors/observe/:storeId
  │     └── /exports
  │           ├── /presentations/market-review
  │           ├── /report-templates
  │           └── /saved-exports
  │
  └── /field-force-home (field force, promoter, merchandiser, sales rep)
        ├── Tab: Today → /duty, /my-route, /geofence-check
        ├── Tab: Notifications → /notifications
        └── Tab: Profile
```

---

## Screen Count Summary

| Feature Area | Screens |
|---|---|
| Authentication | 1 |
| Dashboard & Inquiries | 4 |
| Stores | 2 |
| Products | 2 |
| Routes | 3 |
| Campaigns & Tasks | 3 |
| Sales Orders | 4 |
| POS & Inventory | 4 |
| Notifications & Messaging | 5 |
| Command Center | 2 |
| Competitors | 4 |
| QR / Barcode Scanner | 1 |
| GPS & Duty Tracking | 2 |
| Offline & Sync | 1 |
| Settings | 2 |
| Calendar & Sales Areas | 3 |
| Export & Presentations | 4 |
| Field Force Home | 1 |
| Reports | 3 |
| **Total** | **51** |
