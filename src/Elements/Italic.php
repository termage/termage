<?php

declare(strict_types=1);

/**
 * Thermage - Totally RAD Terminal styling for PHP! (https://digital.flextype.org/thermage/)
 * Copyright (c) Sergey Romanenko (https://awilum.github.io)
 *
 * Licensed under The MIT License.
 *
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 */

namespace Thermage\Elements;

use Thermage\Base\Element;

final class Italic extends Element
{
    /**
     * Render Italic element.
     *
     * @return string Returns rendered Italic element.
     *
     * @access public
     */
    public function renderToString(): string
    {
        $this->italic();

        $this->d($this->getStyles()->get('display') ?? 'inline');

        return parent::renderToString();
    }
}
