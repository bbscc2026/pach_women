<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;

/**
 * Starter policy pages. Existing pages are never overwritten, so edits made in the admin survive re-seeding.
 */
class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'shipping' => ['Shipping policy', <<<'HTML'
<p>We ship to all serviceable PIN codes across India through our courier partners.</p>
<h2>Processing time</h2>
<p>Orders are packed and dispatched within 1–3 working days. Orders placed on Sundays or public holidays are processed the next working day.</p>
<h2>Delivery time</h2>
<ul><li><p>Kerala: 2–4 working days after dispatch</p></li><li><p>Rest of India: 4–8 working days after dispatch</p></li></ul>
<p>Remote areas may take longer. Delivery times are estimates and can be affected by courier delays, weather or festivals.</p>
<h2>Shipping charges</h2>
<p>Shipping is free on orders of {free_shipping_over} and above. Below that, a flat fee of {shipping_fee} applies. The exact charge is shown at checkout before you pay.</p>
<h2>Cash on delivery</h2>
<p>Cash on delivery is available on most PIN codes. Please keep the exact amount ready.</p>
<h2>Tracking</h2>
<p>Once your order ships, we share the tracking number by SMS or WhatsApp, and it also appears in <a href="/account/orders">My orders</a>.</p>
<h2>International orders</h2>
<p>For delivery in the UAE, order through our UAE page <a href="https://www.instagram.com/pach_women_uae/">@pach_women_uae</a>. For other countries, message us on WhatsApp.</p>
HTML],
            'returns' => ['Returns & refunds', <<<'HTML'
<p><strong>We do not accept returns, and we do not offer refunds or exchanges</strong> for change of mind, size or colour preference. Please check the size details and photos carefully before ordering, and message us on WhatsApp if you need help choosing.</p>
<h2>Damaged or wrong item</h2>
<p>If your parcel arrives damaged, or you receive a different product from the one you ordered, we will make it right. To qualify:</p>
<ul><li><p>Contact us within 48 hours of delivery on WhatsApp ({phone}) or by email ({email}).</p></li><li><p>Share a continuous unboxing video, recorded from before the parcel is opened.</p></li><li><p>The product must be unused, unwashed and have all tags attached.</p></li></ul>
<p>After we check the video, we will send a replacement. If a replacement is not available, we will refund the amount you paid to the original payment method (or by bank transfer / UPI for cash-on-delivery orders) within 7 working days.</p>
<h2>Cancellations</h2>
<p>You can cancel an order before it is dispatched by messaging us with your order number. Prepaid orders cancelled before dispatch are refunded in full within 5–7 working days.</p>
HTML],
            'privacy' => ['Privacy policy', <<<'HTML'
<p>This policy explains what personal information {shop_name} collects when you use pachwomen.com and how we use it.</p>
<h2>What we collect</h2>
<ul><li><p>Account details: your name, email address, mobile number and password (stored encrypted).</p></li><li><p>Order details: delivery addresses, the products you buy and order history.</p></li><li><p>Payment details: online payments are processed by Razorpay. We never see or store your card, UPI PIN or bank login.</p></li><li><p>Technical data: basic logs and cookies needed to keep you logged in and remember your cart.</p></li></ul>
<h2>How we use it</h2>
<ul><li><p>To process, ship and support your orders.</p></li><li><p>To contact you about your order by SMS, WhatsApp, phone or email.</p></li><li><p>To keep our website secure and working properly.</p></li></ul>
<h2>Who we share it with</h2>
<p>We share only what is needed with our payment partner (Razorpay) and courier partners to deliver your order. We do not sell your personal information.</p>
<h2>Your choices</h2>
<p>You can update your details from <a href="/account/profile">My account</a>. To delete your account and data, email {email}.</p>
<h2>Contact</h2>
<p>Questions about this policy: {email}.</p>
HTML],
            'terms' => ['Terms & conditions', <<<'HTML'
<p>By using pachwomen.com and placing an order, you agree to these terms.</p>
<h2>Products</h2>
<p>We try to show colours and details as accurately as possible. Actual colours may vary slightly because of lighting and screen settings. Handworked pieces can have small variations, which are not defects.</p>
<h2>Prices and payment</h2>
<p>All prices are in Indian Rupees (₹) and include applicable taxes. Shipping charges, if any, are shown at checkout. We accept online payments through Razorpay and cash on delivery where available.</p>
<h2>Orders</h2>
<p>An order is confirmed once you receive an order number. We may cancel an order if a product is out of stock, the price was shown incorrectly, or we cannot deliver to your address; any amount paid will be refunded in full.</p>
<h2>Shipping, returns and refunds</h2>
<p>See our <a href="/pages/shipping">shipping policy</a> and <a href="/pages/returns">returns &amp; refunds policy</a>.</p>
<h2>Accounts</h2>
<p>You are responsible for keeping your password safe and for activity on your account.</p>
<h2>Governing law</h2>
<p>These terms are governed by the laws of India. Disputes are subject to the jurisdiction of the courts in Kozhikode, Kerala.</p>
<h2>Contact</h2>
<p>{phone} · {email}</p>
HTML],
            'contact' => ['Contact us', <<<'HTML'
<p>We usually reply within a few hours, 10 AM to 7 PM, Monday to Saturday.</p>
HTML],
        ];

        $sort = 0;
        foreach ($pages as $slug => [$title, $content]) {
            ContentPage::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'content' => trim($content),
                'sort_order' => $sort++,
                'is_active' => true,
            ]);
        }
    }
}
