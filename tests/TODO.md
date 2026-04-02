# E2E Test Suite — Deferred Test Areas

## EOT / Federal Quota (FQ) Checkout Flow

**Priority**: High — this is a core business flow unique to APH
**Status**: Deferred — requires FQ account test data and EOT user role setup
**Revisit when**: Test environment has FQ account data seeded

### What needs testing:
- EOT users see different checkout experience
- FQ account selection dropdown appears during checkout
- FQ balance validation works (sufficient funds check)
- FQ account number is saved to order meta
- Products marked as "Federal Quota Eligible" display the FQ badge
- EOT users can backorder products (role-based permission)
- FQ account lookup via APH\FQ class works correctly

### Prerequisites:
- Create test user with `eot_customer` role
- Seed FQ account data in the database (or mock the FQ API)
- FQ/NET API credentials configured in `.env.test`:
  ```
  FQ_URL_PRD=...
  FQ_KEY_PRD=...
  ```
- Understand the FQ balance check flow in `web/app/themes/mightily/classes/APH/FQ.php`

### Key files:
- `web/app/themes/mightily/functions/wc/wc-eot.php` — EOT checkout logic
- `web/app/themes/mightily/classes/APH/FQ.php` — FQ account API wrapper
- `web/app/themes/mightily/functions/wc-hooks.php` — checkout hook registration

---

## CSR (Customer Service Representative) Admin Tools

**Priority**: Medium — internal tool, fewer users but critical for operations
**Status**: Deferred — requires admin-level test user and WP Admin testing patterns
**Revisit when**: Core customer-facing tests are stable

### What needs testing:
- CSR can view and manage customer orders in WP Admin
- CSR can look up FQ account balances for customers
- CSR can add products to existing orders
- CSR interface in `wc-csr.php` loads without errors
- Order meta fields (FQ account, SysPro number, PO number) display correctly

### Prerequisites:
- Create test user with CSR role/capabilities
- Add `TEST_CSR_EMAIL` and `TEST_CSR_PASSWORD` to `.env.test`
- WP Admin Playwright patterns (login to wp-admin, navigate admin pages)

### Key files:
- `web/app/themes/mightily/functions/wc/wc-csr.php` — CSR admin interface
- `web/app/themes/mightily/classes/APH/Order.php` — order management
- `web/app/plugins/woocommerce-louis/` — admin product management

---

## Additional Test Areas (Future)

### Accessibility Testing
- Automated WCAG 2.1 AA checks on all pages
- Heading hierarchy validation
- Alt text coverage for product images
- Keyboard navigation through checkout flow
- Screen reader compatibility (aria labels, live regions)
- Consider integrating `@axe-core/playwright` for automated a11y scans

### Payment Gateway Testing
- Authorize.Net CIM in sandbox/test mode
- EOT gateway (custom) order processing
- Payment failure handling and user messaging

### Email Testing
- Order confirmation email sends (use Mailpit in DDEV)
- Custom email templates render correctly:
  - `Secondary_Download_Email`
  - `Secondary_Louis_Download_Email`
  - `Teacher_Invite_Email`
  - `EOT_Gateway_Email`
  - `Weekly_Order_Review_Email`

### Performance Testing
- Page load times under threshold (shop < 3s, product < 2s)
- FacetWP filter response time
- Cart operations response time

### Search Testing
- SearchWP returns relevant product results
- FacetWP filters narrow results correctly
- Search with special characters doesn't error
- Empty search shows appropriate message
