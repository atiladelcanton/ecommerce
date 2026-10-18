<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Actions\Shipping\DownloadShipmentLabelAction;
use App\Actions\Shipping\PrintShipmentLabelAction;
use App\Domain\Shipping\Models\Shipment;
use App\Filament\Pages\ShippingMethods;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class ShipmentLabelFileController extends Controller
{
    public function __invoke(
        Shipment $shipment,
        string $format,
        DownloadShipmentLabelAction $download,
        PrintShipmentLabelAction $print,
    ): Response {
        abort_unless(ShippingMethods::canAccess(), 403);

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
            // O endpoint de arquivo do Melhor Envio possui limitações no Sandbox
            // (a documentação informa suporte a arquivos apenas para serviços Jadlog).
            // Em vez de deixar o usuário diante de um download silenciosamente falho,
            // usamos o fluxo oficial de impressão pública como fallback.
            try {
                $url = $print->execute($shipment, 'pdf');

                return redirect()->away($url);
            } catch (ValidationException $printException) {
                return redirect()->back()->with(
                    'error',
                    $this->validationMessage(
                        $printException,
                        $this->validationMessage($downloadException, 'Não foi possível obter a etiqueta. Verifique o envio e tente novamente.'),
                    ),
                );
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
