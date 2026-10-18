<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Actions\Shipping\PrintShipmentLabelAction;
use App\Domain\Shipping\Models\Shipment;
use App\Filament\Pages\ShippingMethods;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

final class ShipmentLabelController extends Controller
{
    public function __invoke(Shipment $shipment, PrintShipmentLabelAction $action): RedirectResponse
    {
        abort_unless(ShippingMethods::canAccess(), 403);

        try {
            $url = $action->execute($shipment, 'pdf');
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', $this->validationMessage($exception, 'Não foi possível abrir a etiqueta. Verifique o envio e tente novamente.'));
        }

        return redirect()->away($url);
    }

    private function validationMessage(ValidationException $exception, string $fallback): string
    {
        $errors = $exception->errors();
        $first = $errors !== [] ? reset($errors) : null;
        $message = is_array($first) ? reset($first) : $first;

        return is_string($message) && trim($message) !== '' ? $message : $fallback;
    }
}
