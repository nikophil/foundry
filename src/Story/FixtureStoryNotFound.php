<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Story;

/**
 * @internal
 *
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class FixtureStoryNotFound extends \RuntimeException
{
    /**
     * @param list<string> $availableFixtures
     * @param list<string> $availableGroups
     */
    public static function forNameOrGroup(string $fixtureName, array $availableFixtures, array $availableGroups): self
    {
        $message = "Fixture story with name or group \"{$fixtureName}\" not found.";

        if (!$availableFixtures && !$availableGroups) {
            return new self("{$message} No fixture stories are registered. Add #[AsFixture] attribute to your Story classes.");
        }

        if ($availableFixtures) {
            $message .= ' Available fixtures: '.\implode(', ', $availableFixtures).'.';
        }

        if ($availableGroups) {
            $message .= ' Available groups: '.\implode(', ', $availableGroups).'.';
        }

        return new self($message);
    }
}
