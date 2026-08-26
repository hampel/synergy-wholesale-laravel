<?php

declare(strict_types=1);

namespace Hampel\SynergyWholesale\Laravel\Exception;

use Hampel\SynergyWholesale\Exception\SynergyWholesaleException;
use RuntimeException;

/**
 * The container was asked for a client before the reseller id and API key were configured.
 *
 * Implements the core package's marker interface, so an application catching
 * SynergyWholesaleException catches misconfiguration alongside every other way a call
 * can fail.
 */
final class MissingCredentials extends RuntimeException implements SynergyWholesaleException
{
}
