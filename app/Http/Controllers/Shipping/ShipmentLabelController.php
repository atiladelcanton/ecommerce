<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Actions\Shipping\PrintShipmentLabelAction;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Access\AdminPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ShipmentLabelController extends Controller
{
    public function __invoke(Request $request, Shipment $shipment, PrintShipmentLabelAction $action): RedirectResponse|View
    {
        abort_unless(AdminPermissions::check(AdminPermissions::SALES_ORDERS), 403);

        try {
            $url = $action->execute($shipment, 'pdf');
        } catch (ValidationException $exception) {
            return view('shipping.label-error', [
                'message' => $this->validationMessage($exception, 'Não foi possível abrir a etiqueta. Atualize o status do envio e tente novamente.'),
                'backUrl' => $request->headers->get('referer') ?: url('/admin/orders/'.$shipment->order_id),
            ]);
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
