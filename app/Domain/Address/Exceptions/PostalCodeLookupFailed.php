<?php

declare(strict_types=1);

namespace App\Domain\Address\Exceptions;

use RuntimeException;

final class PostalCodeLookupFailed extends RuntimeException {}
