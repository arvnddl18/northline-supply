export const initialPlans = [
  { id: 'pl_01', name: '30% Made-to-Order Kiln Deposit', type: 'Deposit plan', terms: '30% upfront · balance before dispatch', scope: 'Shop-wide default', active: true },
  { id: 'pl_02', name: 'Four Fires · Studio Split', type: 'Installment plan', terms: '4 splits · every 2 weeks', scope: '3 specific products', active: true },
  { id: 'pl_03', name: 'Ceramic Club · Monthly', type: 'Subscription', terms: '$65.00 AUD / month', scope: '1 collection', active: true }
];

export const initialOrders = [
  { id: '1001', name: 'Clara Bow', email: 'clara.bow@example.com', status: 'Balance Due', total: 20000, deposit: 6000, balance: 14000, date: '12 Oct 2026', due: '19 Oct 2026', card: 'Visa •••• 4242', customerId: 'gid://shopify/Customer/1082', stripeId: 'cus_NW84xv28', paymentIntent: 'pi_3NW1001A4z', fee: 570, retries: 0 },
  { id: '1002', name: 'Maya Chen', email: 'maya.chen@example.com', status: 'Deposit Paid', total: 32000, deposit: 9600, balance: 22400, date: '11 Oct 2026', due: '24 Oct 2026', card: 'Mastercard •••• 1881', customerId: 'gid://shopify/Customer/1091', stripeId: 'cus_NW19aH77', paymentIntent: 'pi_3NW1002b8Q', fee: 880, retries: 0 },
  { id: '1003', name: 'Jonah Ellis', email: 'jonah.ellis@example.com', status: 'Paid in Full', total: 18500, deposit: 18500, balance: 0, date: '10 Oct 2026', due: '—', card: 'Visa •••• 7110', customerId: 'gid://shopify/Customer/1044', stripeId: 'cus_NW44pT08', paymentIntent: 'pi_3NW1003Y2F', fee: 545, retries: 0 },
  { id: '1004', name: 'Riley Parker', email: 'riley.parker@example.com', status: 'Payment Failed', total: 26700, deposit: 8010, balance: 18690, date: '10 Oct 2026', due: '16 Oct 2026', card: 'Visa •••• 0909', customerId: 'gid://shopify/Customer/1018', stripeId: 'cus_NW09jK21', paymentIntent: 'pi_3NW1004n3D', fee: 0, retries: 2 },
  { id: '1005', name: 'Amara Wilson', email: 'amara.wilson@example.com', status: 'Balance Due', total: 42000, deposit: 12600, balance: 29400, date: '09 Oct 2026', due: '21 Oct 2026', card: 'Amex •••• 3102', customerId: 'gid://shopify/Customer/1077', stripeId: 'cus_NW31mK15', paymentIntent: 'pi_3NW1005c6R', fee: 1110, retries: 0 },
  { id: '1006', name: 'Theo Nguyen', email: 'theo.nguyen@example.com', status: 'Refunded', total: 14800, deposit: 14800, balance: 0, date: '08 Oct 2026', due: '—', card: 'Visa •••• 4564', customerId: 'gid://shopify/Customer/1002', stripeId: 'cus_NW56pK14', paymentIntent: 'pi_3NW1006g5K', fee: 0, retries: 0 },
  { id: '1007', name: 'Lena Hoffmann', email: 'lena.hoffmann@example.com', status: 'Deposit Paid', total: 22500, deposit: 6750, balance: 15750, date: '07 Oct 2026', due: '28 Oct 2026', card: 'Visa •••• 9821', customerId: 'gid://shopify/Customer/1042', stripeId: 'cus_NW21lA93', paymentIntent: 'pi_3NW1007a1V', fee: 632, retries: 0 },
  { id: '1008', name: 'Oscar Bennett', email: 'oscar.bennett@example.com', status: 'Paid in Full', total: 31000, deposit: 31000, balance: 0, date: '06 Oct 2026', due: '—', card: 'Mastercard •••• 1130', customerId: 'gid://shopify/Customer/1031', stripeId: 'cus_NW30oB11', paymentIntent: 'pi_3NW1008z7W', fee: 909, retries: 0 }
];

export const initialSubscriptions = [
  { id: 'sub_01', name: 'Clara Bow', email: 'clara.bow@example.com', started: 'May 2026', plan: 'Monthly Stoneware Studio Club', cadence: 'Monthly', price: 6500, status: 'Active', renewal: '01 Nov 2026' },
  { id: 'sub_02', name: 'Maya Chen', email: 'maya.chen@example.com', started: 'Aug 2026', plan: 'Bi-monthly Stoneware Box', cadence: 'Every 2 months', price: 11200, status: 'Active', renewal: '14 Nov 2026' },
  { id: 'sub_03', name: 'Amara Wilson', email: 'amara.wilson@example.com', started: 'Mar 2026', plan: 'Quarterly Clay Club', cadence: 'Quarterly', price: 16500, status: 'Paused', renewal: '01 Dec 2026' },
  { id: 'sub_04', name: 'Riley Parker', email: 'riley.parker@example.com', started: 'Sep 2026', plan: 'Monthly Stoneware Studio Club', cadence: 'Monthly', price: 6500, status: 'Past Due', renewal: '12 Oct 2026' }
];

export const initialAudit = [
  { time: '2026-10-12 07:10:42', action: 'order.balance_reminder_sent', target: 'Order #1001', actor: 'System Automation', origin: '203.12.48.••', kind: 'Background System', meta: { order: '#1001', template: 'order_balance_reminder', sentAt: '2026-10-12T07:10:42Z', delivery: 'accepted', dueDate: '2026-10-19' } },
  { time: '2026-10-12 06:58:11', action: 'stripe.payment_succeeded', target: 'Order #1003', actor: 'Stripe Webhooks', origin: 'stripe.com', kind: 'Stripe Webhooks', meta: { event: 'evt_1NW1003', paymentIntent: 'pi_3NW1003Y2F', amount: 18500, currency: 'aud', idempotency: 'deduplicated:false' } },
  { time: '2026-10-11 23:44:03', action: 'plan.updated', target: 'Plan #2', actor: 'Emma Carter', origin: '203.12.48.••', kind: 'Emma Carter', meta: { before: { installments: 3 }, after: { installments: 4, frequency: 'two_week' } } },
  { time: '2026-10-11 23:18:26', action: 'subscription.paused', target: 'Member #3', actor: 'Emma Carter', origin: '203.12.48.••', kind: 'Emma Carter', meta: { membership: 'Quarterly Clay Club', pauseMonths: 2, status: 'paused' } },
  { time: '2026-10-11 21:06:52', action: 'stripe.connected', target: 'Gateway · Northwind', actor: 'Emma Carter', origin: '203.12.48.••', kind: 'Emma Carter', meta: { account: 'acct_••••8042', mode: 'test', secret: '[encrypted at rest]' } }
];

export const emailTemplates = [
  { key: 'order_deposit_confirmed', label: 'Deposit confirmed', subject: 'Your piece is on its way to the wheel' },
  { key: 'order_balance_reminder', label: 'Balance reminder', subject: 'A final step before your piece ships' },
  { key: 'card_update_requested', label: 'Update payment method', subject: 'A quick note about your next studio installment' }
];

export const statusClass = (status = '') => ({
  'Balance Due': 'status-ochre', 'Deposit Paid': 'status-sage', 'Paid in Full': 'status-sage',
  'Active': 'status-sage', 'Paused': 'status-clay', 'Past Due': 'status-red',
  'Payment Failed': 'status-red', 'Refunded': 'status-clay', 'Cancelled': 'status-clay'
}[status] || 'status-clay');
