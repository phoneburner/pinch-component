<?php // phpcs:disable PSR1.Files.SideEffects

/**
 * This bootstrap file is loaded after the vendor autoload files, and after the
 * XML configuration file has been loaded, but before tests are run.
 */

declare(strict_types=1);

if (! \defined('PhoneBurner\Pinch\UNIT_TEST_ROOT')) {
    \define('PhoneBurner\Pinch\UNIT_TEST_ROOT', __DIR__);
}
