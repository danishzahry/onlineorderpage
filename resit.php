<?php
// receipt.php - simple order receipt generator
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: onlineorder.html');
    exit;
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rm($n) { return number_format((float)$n, 2); }

$customerName = trim($_POST['customerName'] ?? '');
$homeAddress  = trim($_POST['homeAddress'] ?? '');
$email        = trim($_POST['emailAddress'] ?? '');
$phone        = trim($_POST['phoneNumber'] ?? '');
$payment      = trim($_POST['Payment'] ?? '');
$option       = trim($_POST['option'] ?? '');
$selected     = $_POST['items'] ?? [];

// item price map
$menu = [
    'a' => ['name' => 'Classic Burger', 'price' => 10.00],
    'b' => ['name' => 'Organic Sirloin Burger', 'price' => 12.00],
    'c' => ['name' => 'Chilli Cheeseburger', 'price' => 11.00],
    'd' => ['name' => 'Hickory Burger', 'price' => 10.00],
    'e' => ['name' => 'Chicken Club Burger', 'price' => 11.00],
    'f' => ['name' => 'Bison Burger', 'price' => 12.00],
    'g' => ['name' => 'Vegetarian Burger', 'price' => 10.00],
    'h' => ['name' => 'Soda Can', 'price' => 2.00],
    'i' => ['name' => 'Juice', 'price' => 3.00],
    'j' => ['name' => 'Iced Tea', 'price' => 3.00],
    'k' => ['name' => 'Water Bottle', 'price' => 1.00]
];

$orderItems = [];
$subtotal = 0.0;

foreach ($selected as $code) {
    $code = (string)$code;
    if (!isset($menu[$code])) continue;
    // quantity input names use pattern like 'aqty', 'bqty', ...
    $qtyName = $code . 'qty';
    $qty = max(0, intval($_POST[$qtyName] ?? 0));
    if ($qty <= 0) continue; // skip zero
    $price = $menu[$code]['price'];
    $line = $price * $qty;
    $subtotal += $line;
    $orderItems[] = [
        'code' => $code,
        'name' => $menu[$code]['name'],
        'price' => $price,
        'qty' => $qty,
        'line' => $line
    ];
}

$deliveryFee = (strtoupper($option) === 'DELIVERY') ? 5.00 : 0.00;
$total = $subtotal + $deliveryFee;

// unique order id and timestamp
$orderId = strtoupper(dechex(time())) . '-' . rand(100,999);
$time = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Order Receipt</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: system-ui, Arial, sans-serif; max-width: 760px; margin: 2rem auto; }
    header { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 0.6rem 0.5rem; border-bottom: 1px solid #ddd; text-align: left; }
    tfoot td { font-weight: 700; }
    .muted { color: #666; font-size: 0.9rem; }
    .actions { margin-top: 1rem; display:flex; gap: 1rem; }
    .btn { display:inline-block; padding:0.5rem 1rem; border-radius:6px; text-decoration:none; color:#fff; background:#2563eb; }
    .btn-print { background:#059669; }
  </style>
</head>
<body>
  <header>
    <h1>Receipt — Thank you for your order ✅</h1>
    <div class="muted">Order #: <?php echo h($orderId); ?> • <?php echo h($time); ?></div>
  </header>

  <section style="margin-top:1rem">
    <h2>Customer</h2>
    <div><strong><?php echo h($customerName ?: '—'); ?></strong></div>
    <div class="muted"><?php echo nl2br(h($homeAddress ?: '—')); ?></div>
    <div class="muted">Email: <?php echo h($email ?: '—'); ?> • Phone: <?php echo h($phone ?: '—'); ?></div>
  </section>

  <section>
    <h2>Order</h2>

    <?php if (empty($orderItems)): ?>
      <p><em>No items were selected. Please return to the order page to place an order.</em></p>
      <a class="btn" href="onlineorder.html">Back to Order</a>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Item</th><th>Qty</th><th>Unit (RM)</th><th>Line (RM)</th></tr>
        </thead>
        <tbody>
          <?php foreach ($orderItems as $it): ?>
            <tr>
              <td><?php echo h($it['name']); ?></td>
              <td><?php echo h($it['qty']); ?></td>
              <td><?php echo rm($it['price']); ?></td>
              <td><?php echo rm($it['line']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="3">Subtotal</td><td>RM <?php echo rm($subtotal); ?></td></tr>
          <tr><td colspan="3">Delivery</td><td>RM <?php echo rm($deliveryFee); ?></td></tr>
          <tr><td colspan="3">Total</td><td>RM <?php echo rm($total); ?></td></tr>
        </tfoot>
      </table>

      <div style="margin-top:1rem" class="muted">Payment method: <?php echo h($payment ?: '—'); ?> • Fulfillment: <?php echo h($option ?: '—'); ?></div>

      <div class="actions">
        <a class="btn btn-print" href="#" onclick="window.print();return false;">Print Receipt</a>
        <a class="btn" href="onlineorder.html">Place Another Order</a>
      </div>
    <?php endif; ?>
  </section>

</body>
</html>