<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Common;

use PhpFlo\Core\Interaction\PortRegistry;

/**
 * Interface ComponentInterface
 *
 * @package PhpFlo\Common
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
interface ComponentInterface extends RegisteredComponentInterface
{

    /**
     * Detach all sockets, disconnect all ports.
     *
     * @return ComponentInterface;
     */
    public function shutdown();
}
