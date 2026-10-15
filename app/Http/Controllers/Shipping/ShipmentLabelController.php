<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Actions\Shipping\DownloadShipmentLabelAction;
use App\Domain\Shipping\Models\Shipment;
use App\Filament\Pages\ShippingMethods;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class ShipmentLabelController extends Controller
{
    public function __invoke(Request $request, Shipment $shipment, string $format, DownloadShipmentLabelAction $action): Response
    {
        abort_unless(ShippingMethods::canAccess(), 403);
        try {
            $file = $action->execute($shipment, $format);
        } catch (ValidationException) {
            return redirect()->back()->with('error', 'Não foi possível imprimir a etiqueta. Verifique o envio e tente novamente.');
        }

        return response($file->contents, 200, ['Content-Type' => $file->contentType, 'Content-Disposition' => 'attachment; filename="'.$file->filename.'"', 'Cache-Control' => 'private, no-store']);
    }
}
