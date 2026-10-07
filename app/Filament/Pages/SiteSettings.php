<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Everything a shop owner may want to change without touching code.
 * Saved values override config/shop.php (see Setting::applyToConfig()).
 */
class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?int $navigationSort = 7;

    /** Setting keys handled by this form (config/shop.php paths without "shop."). */
    private const KEYS = [
        'name', 'tagline',
        'contact.phone', 'contact.whatsapp', 'contact.email', 'contact.store', 'contact.store_map',
        'contact.instagram', 'contact.instagram_uae',
        'shipping_fee', 'free_shipping_over', 'cod_enabled',
        'razorpay.key', 'razorpay.secret', 'razorpay.webhook_secret',
        'announcements', 'hero_interval',
    ];

    /** Never sent back to the browser; leaving the field empty keeps the saved value. */
    private const SECRETS = ['razorpay.secret', 'razorpay.webhook_secret'];

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $values = [];

        foreach (self::KEYS as $key) {
            data_set($values, $key, config('shop.'.$key));
        }

        foreach (self::SECRETS as $secret) {
            data_set($values, $secret, null);
        }
        $values['announcements'] = array_values((array) config('shop.announcements'));

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        $secretSaved = filled(config('shop.razorpay.secret'));
        $webhookSecretSaved = filled(config('shop.razorpay.webhook_secret'));

        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([
                Section::make('Store details')
                    ->description('Shown in the footer, contact page, WhatsApp button and emails.')
                    ->icon(Heroicon::OutlinedBuildingStorefront)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->label('Shop name')->required()->maxLength(60),
                        TextInput::make('tagline')->maxLength(120),
                        TextInput::make('contact.phone')->label('Phone (shown on site)')->tel()->required()->placeholder('+91 81578 88128'),
                        TextInput::make('contact.whatsapp')->label('WhatsApp number')
                            ->required()
                            ->regex('/^\d{10,15}$/')
                            ->helperText('Digits only with country code, e.g. 918157888128'),
                        TextInput::make('contact.email')->label('Email')->email()->required(),
                        TextInput::make('contact.store_map')->label('Google Maps link')->url(),
                        Textarea::make('contact.store')->label('Store address')->rows(2)->columnSpanFull(),
                        TextInput::make('contact.instagram')->label('Instagram URL')->url(),
                        TextInput::make('contact.instagram_uae')->label('UAE Instagram URL')->url(),
                    ]),

                Section::make('Shipping & payments')
                    ->icon(Heroicon::OutlinedTruck)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('shipping_fee')->label('Shipping fee')->numeric()->minValue(0)->prefix('₹')->required(),
                        TextInput::make('free_shipping_over')->label('Free shipping from')->numeric()->minValue(0)->prefix('₹')->required()
                            ->helperText('Orders at or above this subtotal ship free.'),
                        Toggle::make('cod_enabled')->label('Allow Cash on Delivery')->columnSpanFull(),
                        TextInput::make('razorpay.key')->label('Razorpay Key ID')->placeholder('rzp_test_… or rzp_live_…')
                            ->helperText('From dashboard.razorpay.com → Account & Settings → API keys. Online payment shows at checkout when both keys are set.'),
                        TextInput::make('razorpay.secret')->label('Razorpay Key Secret')->password()->revealable()
                            ->placeholder($secretSaved ? '•••••••• saved — leave empty to keep' : 'Paste the key secret')
                            ->helperText('Stored encrypted.'),
                        TextInput::make('razorpay.webhook_secret')->label('Razorpay Webhook Secret')->password()->revealable()
                            ->placeholder($webhookSecretSaved ? '•••••••• saved — leave empty to keep' : 'Secret you chose when creating the webhook')
                            ->helperText('Razorpay → Webhooks → Add: URL '.route('payment.razorpay.webhook').', events payment.captured and order.paid. Records payments even if the customer closes the browser.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Announcement bar')
                    ->description('Short messages in the black bar at the top. On phones they rotate one at a time.')
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('announcements')
                            ->hiddenLabel()
                            ->simple(TextInput::make('text')->required()->maxLength(60))
                            ->reorderable()
                            ->maxItems(5)
                            ->addActionLabel('Add message')
                            ->helperText('You can use {free_shipping_over} and {shipping_fee}.'),
                    ]),

                Section::make('Home page')
                    ->icon(Heroicon::OutlinedHome)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('hero_interval')->label('Slider: seconds per banner')->numeric()->minValue(2)->maxValue(20)->step(0.5)->suffix('sec'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Save settings')->submit('save')->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $values = [];

        foreach (self::KEYS as $key) {
            $values[$key] = data_get($state, $key);
        }

        $values['announcements'] = array_values(array_filter((array) $values['announcements'], 'filled'));
        $values['cod_enabled'] = (bool) $values['cod_enabled'];

        foreach (['shipping_fee', 'free_shipping_over', 'hero_interval'] as $number) {
            $values[$number] = $values[$number] === null || $values[$number] === '' ? null : (float) $values[$number];
        }

        // An empty secret means "keep the saved one".
        foreach (self::SECRETS as $secret) {
            if (blank($values[$secret])) {
                unset($values[$secret]);
            }
        }

        Setting::saveMany($values);

        Notification::make()->success()->title('Settings saved')->send();

        $this->redirect(static::getUrl(), navigate: true);
    }
}
