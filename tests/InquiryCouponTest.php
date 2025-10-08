<?php

namespace MohammadMehrabani\ConditionalCoupon\Tests;

use Illuminate\Support\Carbon;
use MohammadMehrabani\ConditionalCoupon\Enums\CouponStatusEnum;
use MohammadMehrabani\ConditionalCoupon\Enums\CurrencyEnum;
use MohammadMehrabani\ConditionalCoupon\Exceptions\CouponException;
use MohammadMehrabani\ConditionalCoupon\Exceptions\UnexpectedException;
use MohammadMehrabani\ConditionalCoupon\InquiryCoupon;
use MohammadMehrabani\ConditionalCoupon\Models\Coupon;
use MohammadMehrabani\ConditionalCoupon\Models\CouponCondition;
use MohammadMehrabani\ConditionalCoupon\Tests\Conditions\CheckPaymentMethodsCondition;
use MohammadMehrabani\ConditionalCoupon\Tests\Conditions\CheckPaymentTypesCondition;
use MohammadMehrabani\ConditionalCoupon\Tests\Models\Order;
use PHPUnit\Framework\Attributes\Test;

class InquiryCouponTest extends TestCase
{
    /** @test */
    #[Test]
    public function it_applies_fixed_amount_discount_when_only_amount_is_provided()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        [
            $currency,
            $amount,
            $discountAmount,
            $payableAmount,
            $couponModel
        ] = app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);

        $this->assertEquals(CurrencyEnum::IRR->value, $currency);
        $this->assertEquals(49.5, $payableAmount);
        $this->assertEquals(50.5, $discountAmount);
        $this->assertEquals($order->amount, $amount);
        $this->assertEquals($coupon->id, $couponModel->id);
    }

    /** @test */
    #[Test]
    public function it_applies_percentage_discount_when_only_percentage_is_provided()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 0,
            'discount_percentage' => 50,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        [
            $currency,
            $amount,
            $discountAmount,
            $payableAmount,
            $couponModel
        ] = app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);

        $this->assertEquals(CurrencyEnum::IRR->value, $currency);
        $this->assertEquals(50, $payableAmount);
        $this->assertEquals(50, $discountAmount);
        $this->assertEquals($order->amount, $amount);
        $this->assertEquals($coupon->id, $couponModel->id);
    }

    /** @test */
    #[Test]
    public function it_applies_fixed_amount_when_percentage_discount_is_greater_than_fixed_amount()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 52,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        [
            $currency,
            $amount,
            $discountAmount,
            $payableAmount,
            $couponModel
        ] = app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);

        $this->assertEquals(CurrencyEnum::IRR->value, $currency);
        $this->assertEquals(49.5, $payableAmount);
        $this->assertEquals(50.5, $discountAmount);
        $this->assertEquals($order->amount, $amount);
        $this->assertEquals($coupon->id, $couponModel->id);
    }

    /** @test */
    #[Test]
    public function it_applies_percentage_when_percentage_discount_is_less_than_or_equal_to_fixed_amount()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 47,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        [
            $currency,
            $amount,
            $discountAmount,
            $payableAmount,
            $couponModel
        ] = app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);

        $this->assertEquals(CurrencyEnum::IRR->value, $currency);
        $this->assertEquals(53, $payableAmount);
        $this->assertEquals(47, $discountAmount);
        $this->assertEquals($order->amount, $amount);
        $this->assertEquals($coupon->id, $couponModel->id);
    }

    /** @test */
    #[Test]
    public function it_throws_coupon_usage_limit_exception_when_limit_reached()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 1,
        ]);

        $this->expectException(CouponException::class);
        $this->expectExceptionMessage(CouponException::couponUsageLimitReached()->getMessage());
        $this->expectExceptionCode(CouponException::couponUsageLimitReached()->getCode());

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /** @test */
    #[Test]
    public function it_throws_coupon_not_exists_exception_when_code_is_invalid()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::INACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        $this->expectException(CouponException::class);
        $this->expectExceptionMessage(CouponException::couponNotExists()->getMessage());
        $this->expectExceptionCode(CouponException::couponNotExists()->getCode());

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /** @test */
    #[Test]
    public function it_throws_free_order_exception_when_amount_is_zero()
    {
        $order = Order::factory()->make(['amount' => 0]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        $this->expectException(CouponException::class);
        $this->expectExceptionMessage(CouponException::orderIsFree()->getMessage());
        $this->expectExceptionCode(CouponException::orderIsFree()->getCode());

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /** @test */
    #[Test]
    public function it_throws_coupon_expired_exception_when_code_is_expired()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50.5,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
            'start_at' => Carbon::now()->subDays(2),
            'end_at' => Carbon::now()->subDay(),
        ]);

        $this->expectException(CouponException::class);
        $this->expectExceptionMessage(CouponException::couponTimeLimitReached()->getMessage());
        $this->expectExceptionCode(CouponException::couponTimeLimitReached()->getCode());

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /** @test */
    #[Test]
    public function it_throws_invalid_discount_configuration_exception_when_no_amount_or_percentage_is_provided()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage('One of discount_amount or discount percentage is required.');
        $this->expectExceptionCode(500);

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /***** Tests for Custom Check Condition Classes *****/

    /** @test */
    #[Test]
    public function it_throws_exception_if_check_condition_class_does_not_exist()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        $class = 'MohammadMehrabani\ConditionalCoupon\Tests\Conditions\Test';
        CouponCondition::factory()->forCoupon($coupon)->condition($class)->data(1)->create();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage('Class '.$class.' dose not exists.');
        $this->expectExceptionCode(500);

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /**
     * --------------------------------------------------------------------------
     * Example Tests for CheckPaymentTypesCondition
     * --------------------------------------------------------------------------
     *
     * This set of tests demonstrates how to validate a "Check" class,
     * using CheckPaymentTypesCondition as an example.
     *
     * It ensures that the class behaves correctly according to its contract:
     *   - It validates that the payment type used in an order is allowed
     *     for the given discount.
     *   - It throws an appropriate exception if the payment type is not allowed.
     *
     * Note: This is provided as an example. Other Check classes may exist
     * or be developed in the future, and similar tests should be written
     * for them to ensure proper behavior.
     *
     * Purpose: Illustrate correct testing approach for a Check class
     * within the discount validation system.
     */

    /** @test */
    #[Test]
    public function it_throws_invalid_payment_type_exception_when_payment_type_is_not_allowed()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        CouponCondition::factory()->forCoupon($coupon)->condition(CheckPaymentTypesCondition::class)->data(['wallet'])->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('payment types do not match.');
        $this->expectExceptionCode(400);

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }

    /** @test */
    #[Test]
    public function it_passes_when_payment_type_is_allowed()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        CouponCondition::factory()->forCoupon($coupon)->condition(CheckPaymentTypesCondition::class)->data(['cash'])->create();

        [
            $currency,
            $amount,
            $discountAmount,
            $payableAmount,
            $couponModel
        ] = app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);

        $this->assertEquals(CurrencyEnum::IRR->value, $currency);
        $this->assertEquals(50, $payableAmount);
        $this->assertEquals(50, $discountAmount);
        $this->assertEquals($order->amount, $amount);
        $this->assertEquals($coupon->id, $couponModel->id);
    }

    /**
     * --------------------------------------------------------------------------
     * Abstract Class Compliance Tests
     * --------------------------------------------------------------------------
     *
     * This set of tests ensures that all "Check*" classes properly extend
     * the designated abstract base class. If a class fails to inherit
     * from the required abstract class, an appropriate exception is thrown,
     * and these tests verify that behavior.
     *
     * Purpose: Enforce design contract compliance for all Check classes.
     */

    /** @test */
    #[Test]
    public function it_ensures_check_classes_extend_required_abstract_class()
    {
        $order = Order::factory()->make(['amount' => 100]);
        $coupon = Coupon::factory()->create([
            'status' => CouponStatusEnum::ACTIVE->value,
            'discount_amount' => 50,
            'discount_percentage' => 0,
            'total_count' => 1,
            'used_count' => 0,
        ]);

        $class = CheckPaymentMethodsCondition::class;
        CouponCondition::factory()->forCoupon($coupon)->condition($class)->data(['online'])->create();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage(
            'Class '.$class.' Not Implemented MohammadMehrabani\ConditionalCoupon\CheckConditionAbstract'
        );
        $this->expectExceptionCode(500);

        app()->make(InquiryCoupon::class)->handle($coupon->code, $order->amount);
    }
}
