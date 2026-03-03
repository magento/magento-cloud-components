<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Test\Functional\Acceptance;

/**
 * @group php81
 */
class Acceptance81Cest extends AcceptanceCest
{
    /**
     * Patches data provider.
     *
     * @return array
     */
    protected function patchesDataProvider(): array
    {
        return [
            ['templateVersion' => '2.4.4'],
            ['templateVersion' => '2.4.5'],
        ];
    }
}
