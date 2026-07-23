<?php

declare(strict_types=1);

namespace Larafocus\Exceptions;

use RuntimeException;

/**
 * Base for transport-level failures where the outcome of the request is not a
 * definitive provider verdict.
 *
 * Catching this base means "I could not obtain a definitive answer from Focus":
 * either nothing was delivered ({@see RequestNotDeliveredException}) or the
 * result is unknown and must be reconciled ({@see IndeterminateResultException}).
 *
 * @api
 */
abstract class CommunicationException extends RuntimeException {}
