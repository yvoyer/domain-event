<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures;

use Star\Component\DomainEvent\HelperListener;

/**
 * Class MissConfiguredListener
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent\Fixtures
 */
final class MissConfiguredListener extends HelperListener
{
    protected function configure()
    {
    }
}
