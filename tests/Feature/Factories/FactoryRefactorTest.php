<?php

namespace Tests\Feature\Factories;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Campaign;
use App\Models\CampaignReceipt;
use App\Models\Cart;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\FlashSale;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentBank;
use App\Models\PaymentEwallet;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Shipping;
use App\Models\Subscriber;
use App\Models\Tax;
use App\Models\UserAddress;
use App\Models\UserNotification;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FactoryRefactorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function catalog_factories_generate_perfume_contextual_data(): void
    {
        $brand = ProductBrand::factory()->create();
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create();
        $banner = Banner::factory()->create();
        $blogCategory = BlogCategory::factory()->create();
        $blogTag = BlogTag::factory()->create();
        $blog = Blog::factory()->create();
        $coupon = Coupon::factory()->create();
        $couponProduct = CouponProduct::factory()->create();
        $flashSale = FlashSale::factory()->create();

        $this->assertNotEmpty($brand->slug);
        $this->assertNotEmpty($category->slug);
        $this->assertStringContainsStringIgnoringCase('ml', $product->name);
        $this->assertGreaterThan(100000, $product->price);
        $this->assertNotNull($product->brand);
        $this->assertNotNull($product->category);
        $this->assertStringContainsStringIgnoringCase('parfum', $product->description);
        $this->assertStringStartsWith('/', $banner->url);
        $this->assertStringContainsStringIgnoringCase('parfum', $blog->content);
        $this->assertNotEmpty($blogCategory->slug);
        $this->assertNotEmpty($blogTag->slug);
        $this->assertTrue(strtotime($coupon->end_date) >= strtotime($coupon->start_date));
        $this->assertSame('product', $couponProduct->coupon->promo_type);
        $this->assertTrue($couponProduct->product->is_publish);
        $this->assertTrue($flashSale->end_at->gt($flashSale->start_at));
    }

    #[Test]
    public function ecommerce_related_factories_create_valid_relations_without_seeders(): void
    {
        $cart = Cart::factory()->create();
        $wishlist = Wishlist::factory()->create();
        $userAddress = UserAddress::factory()->create();
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create();
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create();
        $paymentBank = PaymentBank::factory()->create();
        $paymentEwallet = PaymentEwallet::factory()->create();
        $shipping = Shipping::factory()->create();
        $notification = UserNotification::factory()->create();

        $this->assertTrue($cart->product->is_publish);
        $this->assertTrue($wishlist->product->is_publish);
        $this->assertNotNull($userAddress->user);
        $this->assertNotEmpty($userAddress->zip_code);
        $this->assertNotNull($order->user);
        $this->assertNotNull($orderItem->order);
        $this->assertNotNull($orderItem->product);
        $this->assertGreaterThanOrEqual(0, $orderItem->discount_in_percent);
        $this->assertTrue($invoice->due_date !== null);
        $this->assertIsArray($payment->info);
        $this->assertSame('bank', $paymentBank->payment->method);
        $this->assertSame('ewallet', $paymentEwallet->payment->method);
        $this->assertIsArray($shipping->address);
        $this->assertArrayHasKey('zip_code', $shipping->address);
        $this->assertContains($shipping->status, ['pending', 'processing', 'shipped']);
        $this->assertNotNull($notification->user);
        $this->assertContains($notification->category, ['transaction', 'account', 'promo', 'system']);
    }

    #[Test]
    public function engagement_factories_produce_consistent_status_and_timeline_data(): void
    {
        $subscriber = Subscriber::factory()->create();
        $campaign = Campaign::factory()->create();
        $receipt = CampaignReceipt::factory()->create();
        $contactMessage = ContactMessage::factory()->create();
        $tax = Tax::factory()->create();

        $this->assertNotEmpty($subscriber->token);
        $this->assertNotEmpty($campaign->subject);
        $this->assertMatchesRegularExpression('/parfum|travel atomizer|room spray|gift|stok|ukuran|original|order/i', $contactMessage->message);
        $this->assertGreaterThan(0, (float) $tax->rate);

        if ($subscriber->status === 'pending') {
            $this->assertNull($subscriber->subscribed_at);
        }

        if ($subscriber->status === 'unsubscribed') {
            $this->assertNotNull($subscriber->subscribed_at);
            $this->assertNotNull($subscriber->unsubscribed_at);
        }

        if ($campaign->status->value === 'sent') {
            $this->assertNotNull($campaign->sent_at);
        }

        if ($receipt->status->value === 'queued') {
            $this->assertNull($receipt->sent_at);
            $this->assertNull($receipt->opened_at);
            $this->assertNull($receipt->clicked_at);
        }

        if ($receipt->status->value === 'opened') {
            $this->assertNotNull($receipt->sent_at);
            $this->assertNotNull($receipt->opened_at);
            $this->assertNull($receipt->clicked_at);
        }

        if ($receipt->status->value === 'clicked') {
            $this->assertNotNull($receipt->sent_at);
            $this->assertNotNull($receipt->opened_at);
            $this->assertNotNull($receipt->clicked_at);
        }
    }
}
