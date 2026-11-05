<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Examples\Order;
use Yadddl\ValueObject\Examples\OrderStatus;
use Yadddl\ValueObject\Validators\BackedEnumValue;

test('BackedEnumValue creates OrderStatus from string', function () {
    $status = BackedEnumValue::create(OrderStatus::class, 'open');

    expect($status)->toBeInstanceOf(OrderStatus::class)
        ->and($status)->toBe(OrderStatus::Open)
        ->and($status->isOpen())->toBeTrue();
});

test('BackedEnumValue returns ErrorsBag for unknown status', function () {
    $err = BackedEnumValue::create(OrderStatus::class, 'nope');

    expect($err)->toBeInstanceOf(ErrorsBag::class)
        ->and($err->getMessages())->toContain('Invalid value for OrderStatus');
});

test('Order keeps status as enum instance via MultipleValidationContext', function () {
    $order = Order::create('ORD-1', 'closed');

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->status)->toBe(OrderStatus::Closed)
        ->and($order->status->isOpen())->toBeFalse();
});

test('Order accumulates status field errors', function () {
    $result = Order::create('A', 'nope');

    expect($result)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($result->getFieldsWithErrors())->toContain('status')
        ->and($result->getFieldsWithErrors())->toContain('reference');
});
