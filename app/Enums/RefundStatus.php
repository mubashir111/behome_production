<?php

namespace App\Enums;

interface RefundStatus
{
    const AWAITING_ITEM   = 5;   // Return accepted, waiting for customer to ship item back
    const ITEM_RECEIVED   = 10;  // Item physically received by admin, under inspection
    const REFUND_ISSUED   = 15;  // Refund processed, balance credited to customer
}
