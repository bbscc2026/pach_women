<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Livewire\Shop;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Order details')->columns(2)->schema([
                Select::make('source')->options(['whatsapp' => 'WhatsApp', 'instagram' => 'Instagram', 'store' => 'In-store / POS', 'phone' => 'Phone'])->default('whatsapp')->required(),
                Select::make('payment_method')->options(['cod' => 'Cash on delivery', 'cash' => 'Cash collected', 'upi' => 'UPI received', 'bank' => 'Bank transfer received'])->default('cod')->required()->helperText('Choose a received payment only after checking your bank or cash collection.'),
                TextInput::make('shipping')->label('Delivery charge')->numeric()->minValue(0)->maxValue(10000)->default(0)->prefix('₹')->required(),
                Textarea::make('notes')->label('Staff notes / social order reference')->maxLength(2000),
            ]),
            Section::make('Products')->description('Prices are taken from the catalogue when the order is saved. Stock is reserved immediately.')->schema([
                Repeater::make('lines')->minItems(1)->maxItems(50)->defaultItems(1)->columns(3)->schema([
                    Select::make('product_id')->label('Product')->options(fn () => Product::orderBy('name')->get()->mapWithKeys(fn (Product $p) => [$p->id => $p->name.' — '.inr($p->finalPrice()).' · '.$p->stock.' left']))->searchable()->required(),
                    Select::make('size')->options(array_combine(Shop::SIZES, Shop::SIZES))->helperText('Select a size for sized products.'),
                    TextInput::make('quantity')->numeric()->integer()->minValue(1)->maxValue(1000)->default(1)->required(),
                ])->addActionLabel('Add another product'),
            ]),
            Section::make('Customer & delivery')->columns(2)->schema([
                Select::make('user_id')->label('Existing website customer (optional)')->options(fn () => User::where('is_admin', false)->orderBy('name')->get()->mapWithKeys(fn ($user) => [$user->id => $user->name.' · '.$user->email]))->searchable()->exists('users', 'id')->helperText('Link only the correct customer account. They can then see this order and tracking in My orders.'),
                TextInput::make('name')->required()->maxLength(100),
                TextInput::make('phone')->tel()->required()->regex('/^(\+91[\s-]?)?[6-9]\d{9}$/'),
                TextInput::make('email')->email()->maxLength(150)->helperText('Optional. This does not create or link a customer account.'),
                TextInput::make('line1')->label('Delivery address / store pickup location')->required()->maxLength(200),
                TextInput::make('city')->required()->maxLength(100),
                Select::make('state')->options(array_combine(config('shop.states'), config('shop.states')))->searchable()->required(),
                TextInput::make('pincode')->label('PIN code')->required()->regex('/^\d{6}$/'),
            ]),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        abort_unless(auth()->user()?->is_admin, 403);

        return DB::transaction(function () use ($data) {
            $products = Product::whereIn('id', collect($data['lines'])->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $quantities = [];
            $subtotal = 0;
            foreach ($data['lines'] as $index => $line) {
                $product = $products->get($line['product_id']);
                if (! $product || ! $product->isVisible()) {
                    throw ValidationException::withMessages(["data.lines.$index.product_id" => 'Choose an available product.']);
                }
                if ($product->sizes && ! in_array($line['size'] ?? null, $product->sizes, true)) {
                    throw ValidationException::withMessages(["data.lines.$index.size" => 'Choose a valid size for this product.']);
                }
                $quantities[$product->id] = ($quantities[$product->id] ?? 0) + (int) $line['quantity'];
                if ($quantities[$product->id] > $product->stock) {
                    throw ValidationException::withMessages(["data.lines.$index.quantity" => "Only {$product->stock} pieces available across all sizes."]);
                }
                $subtotal += (int) round($product->finalPrice() * 100) * (int) $line['quantity'];
            }
            $shipping = (int) round((float) $data['shipping'] * 100);
            $order = Order::create([
                ...collect($data)->only(['user_id', 'name', 'phone', 'line1', 'city', 'state', 'pincode', 'payment_method'])->all(),
                'email' => $data['email'] ?? '',
                'notes' => 'Source: '.$data['source']."\n".($data['notes'] ?? ''),
                'status' => 'confirmed',
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'paid',
                'subtotal' => $subtotal / 100, 'shipping' => $shipping / 100, 'total' => ($subtotal + $shipping) / 100,
            ]);
            foreach ($data['lines'] as $line) {
                $product = $products[$line['product_id']];
                $order->items()->create(['product_id' => $product->id, 'name' => $product->name, 'size' => $line['size'] ?? null, 'price' => $product->finalPrice(), 'quantity' => $line['quantity']]);
            }
            $order->reserveStock();

            return $order;
        });
    }

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('edit', ['record' => $this->record]);
    }
}
