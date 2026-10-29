<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Actions\Shipping\DownloadShipmentLabelAction;
use App\Actions\Shipping\PrintShipmentLabelAction;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Access\AdminPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final class ShipmentLabelFileController extends Controller
{
    public function __invoke(
        Request $request,
        Shipment $shipment,
        string $format,
        DownloadShipmentLabelAction $download,
        PrintShipmentLabelAction $print,
    ): Response|RedirectResponse|View {
        abort_unless(AdminPermissions::check(AdminPermissions::SALES_ORDERS), 403);

        try {
            $file = $download->execute($shipment, $format);

            return response($file->contents, 200, [
                'Content-Type' => $file->contentType,
                'Content-Disposition' => 'attachment; filename="'.$file->filename.'"',
                'Content-Length' => (string) strlen($file->contents),
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (ValidationException $downloadException) {
            try {
                $url = $print->execute($shipment, 'pdf');

                return redirect()->away($url);
            } catch (ValidationException $printException) {
                return view('shipping.label-error', [
                    'message' => $this->validationMessage(
                        $printException,
                        $this->validationMessage($downloadException, 'Não foi possível obter a etiqueta. Atualize o status do envio e tente novamente.'),
                    ),
                    'backUrl' => $request->headers->get('referer') ?: url('/admin/orders/'.$shipment->order_id),
                ]);
            }
        }
    }

    private function validationMessage(ValidationException $exception, string $fallback): string
    {
        $errors = $exception->errors();
        $first = $errors !== [] ? reset($errors) : null;
        $message = is_array($first) ? reset($first) : $first;

        return is_string($message) && trim($message) !== '' ? $message : $fallback;
    }
}
