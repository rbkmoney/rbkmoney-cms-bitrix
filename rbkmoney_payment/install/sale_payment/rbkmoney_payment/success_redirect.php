<?php
if ($orderID = !empty($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '') {
    header('Location: /personal/order/rbkmoney_payment/success.php?orderId=' . $orderID, true, 301);
    exit();
}
?>
