<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderActions;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Order '.$this->getRecord()->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            OrderActions::ship(),
            OrderActions::deliver(),
            OrderActions::whatsapp(),
            ActionGroup::make([
                OrderActions::call(),
                OrderActions::cancel(),
            ]),
        ];
    }

    // Header actions change the record; reload the form so it shows the new status.
    protected function afterActionCalled(Action $action): void
    {
        parent::afterActionCalled($action);

        $this->refreshFormData(['status', 'tracking_number', 'payment_status']);
    }
}
