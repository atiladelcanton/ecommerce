<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Closure;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class CatalogForm
{
    /** Map domain validation errors to the Filament form's state path. */
    public static function submit(Closure $operation): mixed
    {
        try {
            return $operation();
        } catch (ValidationException $exception) {
            $errors = [];
            foreach ($exception->errors() as $field => $messages) {
                $errors['data.'.$field] = $messages;
            }
            throw ValidationException::withMessages($errors);
        }
    }

    public static function delete(Closure $operation): DeleteAction
    {
        return DeleteAction::make()->using(function (Model $record, DeleteAction $action) use ($operation): bool {
            try {
                $operation($record);
            } catch (ValidationException $exception) {
                Notification::make()->danger()->title('Não foi possível excluir')->body(implode(' ', $exception->validator->errors()->all()))->send();
                $action->halt();
            }

            return true;
        });
    }
}
