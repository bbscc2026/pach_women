<?php

namespace App\Filament\Resources\Orders;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * One-tap order actions for the admin, made for using the panel on a phone.
 */
class OrderActions
{
    /** Customer's number as 91XXXXXXXXXX for tel: and wa.me links. */
    public static function phoneDigits(Order $order): string
    {
        return '91'.substr(preg_replace('/\D/', '', $order->phone), -10);
    }

    public static function ship(): Action
    {
        return Action::make('ship')
            ->label('Mark shipped')
            ->icon(Heroicon::OutlinedTruck)
            ->color('primary')
            ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed'], true) && ! $record->isAwaitingPayment())
            ->modalHeading(fn (Order $record) => 'Ship order '.$record->number)
            ->modalSubmitActionLabel('Mark shipped')
            ->schema([
                TextInput::make('tracking_number')
                    ->label('Tracking number')
                    ->placeholder('Optional — shown to the customer')
                    ->default(fn (Order $record) => $record->tracking_number),
            ])
            ->action(function (Order $record, array $data) {
                $record->update(['status' => 'shipped', 'tracking_number' => $data['tracking_number'] ?: $record->tracking_number]);
                Notification::make()->success()->title('Order '.$record->number.' marked shipped')->send();
            });
    }

    public static function deliver(): Action
    {
        return Action::make('deliver')
            ->label('Mark delivered')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (Order $record) => $record->status === 'shipped')
            ->requiresConfirmation()
            ->modalHeading(fn (Order $record) => 'Mark '.$record->number.' delivered?')
            ->action(function (Order $record) {
                $record->update([
                    'status' => 'delivered',
                    // Cash was collected on delivery.
                    'payment_status' => $record->payment_method === 'cod' ? 'paid' : $record->payment_status,
                ]);
                Notification::make()->success()->title('Order '.$record->number.' delivered')->send();
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Cancel order')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed'], true))
            ->requiresConfirmation()
            ->modalHeading(fn (Order $record) => 'Cancel order '.$record->number.'?')
            ->modalDescription(fn (Order $record) => $record->payment_status === 'paid'
                ? 'Stock goes back. This order was paid online — refund it from the Razorpay dashboard.'
                : 'Stock goes back to the products.')
            ->action(function (Order $record) {
                $record->update(['status' => 'cancelled']);
                Notification::make()->success()->title('Order '.$record->number.' cancelled')->send();
            });
    }

    public static function call(): Action
    {
        return Action::make('call')
            ->label('Call customer')
            ->icon(Heroicon::OutlinedPhone)
            ->color('gray')
            ->url(fn (Order $record) => 'tel:+'.self::phoneDigits($record));
    }

    public static function whatsapp(): Action
    {
        return Action::make('whatsapp')
            ->label('WhatsApp customer')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('success')
            ->url(function (Order $record) {
                $text = "Hi {$record->name}, this is ".config('shop.name')." about your order {$record->number}.";
                if ($record->tracking_number) {
                    $text .= " Tracking number: {$record->tracking_number}.";
                }

                return 'https://wa.me/'.self::phoneDigits($record).'?text='.rawurlencode($text);
            }, shouldOpenInNewTab: true);
    }
}
