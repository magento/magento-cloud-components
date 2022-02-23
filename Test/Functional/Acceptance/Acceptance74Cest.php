<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Test\Functional\Acceptance;

/**
 * @group php74
 */
class Acceptance74Cest extends AcceptanceCest
{
    /**
     * @return array
     */
    protected function patchesDataProvider(): array
    {
        return [
            ['magentoVersion' => '2.4.0'],
            ['magentoVersion' => '2.4.3'],
        ];
    }
}
