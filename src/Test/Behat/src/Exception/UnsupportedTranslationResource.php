<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test\Behat\Exception;

/**
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class UnsupportedTranslationResource extends \InvalidArgumentException
{
    public static function forPath(string $path): self
    {
        return new self("Cannot register step translations from \"{$path}\": only \"xliff\", \"yaml\" and \"php\" files are supported.");
    }
}
