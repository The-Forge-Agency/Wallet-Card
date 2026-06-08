<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée quand la génération du .pkpass Apple est impossible
 * (certificats non configurés). Permet un fallback gracieux côté front.
 */
class WalletPassUnavailableException extends RuntimeException {}
