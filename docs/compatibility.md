# Compatibilidade da stack

Este starter usa:

- PHP ^8.3 (Sail configurado para PHP 8.4)
- Laravel ^13.17
- Laravel Tinker ^3.0
- Filament ^5.0
- Livewire ^4.4.2
- PHPUnit ^12.5.12

## Observação sobre Laravel 13

Laravel 13 requer Laravel Tinker 3.x. A série Tinker 2.x não declara suporte ao Illuminate 13 e provoca conflito de dependências com `laravel/framework` 13.x.
