<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Test\Functional\Acceptance;

use CliTester;
use Codeception\Example;
use Robo\Exception\TaskException;

/**
 * Class AcceptanceCest
 *
 * This class provides acceptance tests for Magento Cloud components.
 */
abstract class AcceptanceCest
{
    /**
     * Prepares the testing environment by cleaning up the working directory before each test.
     *
     * @param CliTester $I
     * @return void
     */
    public function _before(CliTester $I): void
    {
        $I->cleanupWorkDir();
    }

    /**
     * Prepares the template for testing by cloning the specified version,
     * setting up authentication, and configuring dependencies.
     *
     * @param CliTester $I
     * @param string $templateVersion
     * @return void
     * @throws TaskException
     */
    protected function prepareTemplate(CliTester $I, string $templateVersion): void
    {
        $I->cloneTemplateToWorkDir($templateVersion);
        $I->createAuthJson();
        $I->createArtifactsDir();
        $I->createArtifactCurrentTestedCode('components', '1.1.99');
        $I->addArtifactsRepoToComposer();
        $I->addDependencyToComposer('magento/magento-cloud-components', '1.1.99');

        $I->addEceToolsGitRepoToComposer();
        $I->addEceDockerGitRepoToComposer();
        $I->addCloudPatchesGitRepoToComposer();
        $I->addQualityPatchesGitRepoToComposer();

        $dependencies = [
            'magento/ece-tools',
            'magento/magento-cloud-docker',
            'magento/magento-cloud-patches',
            'magento/quality-patches'
        ];

        foreach ($dependencies as $dependency) {
            $I->assertTrue(
                $I->addDependencyToComposer($dependency, $I->getDependencyVersion($dependency)),
                'Can not add dependency ' . $dependency
            );
        }

        $I->composerUpdate();
    }

    /**
     * Tests the application of patches by preparing the template, generating Docker Compose files,
     * deploying the environment, and verifying the home page content.
     *
     * @param CliTester $I
     * @param Example $data
     * @return void
     * @throws TaskException
     * @dataProvider patchesDataProvider
     */
    public function testPatches(CliTester $I, Example $data): void
    {
        $this->prepareTemplate($I, $data['templateVersion']);
        $this->removeESIfExists($I, $data['templateVersion']);
        $I->generateDockerCompose('--mode=production');
        $I->runDockerComposeCommand('run build cloud-build');
        $I->startEnvironment();
        $I->runDockerComposeCommand('run deploy cloud-deploy');
        $I->runDockerComposeCommand('run deploy cloud-post-deploy');
        $I->amOnPage('/');
        $I->see('Home page');
        $I->see('CMS homepage content goes here.');
    }

    /**
     * Removes Elasticsearch configuration if it exists and the template version is less than 2.4.0.
     *
     * @param CliTester $I
     * @param string $templateVersion
     * @return void
     */
    protected function removeESIfExists(CliTester $I, string $templateVersion): void
    {
        if ($templateVersion !== 'master' && version_compare($templateVersion, '2.4.0', '<')) {
            $services = $I->readServicesYaml();

            if (isset($services['elasticsearch'])) {
                unset($services['elasticsearch']);
                $I->writeServicesYaml($services);

                $app = $I->readAppMagentoYaml();
                unset($app['relationships']['elasticsearch']);
                $I->writeAppMagentoYaml($app);
            }
        }
    }

    /**
     * Provides data for testing patches.
     *
     * @return array
     */
    abstract protected function patchesDataProvider(): array;

    /**
     * Cleans up the testing environment by stopping any running environment
     * and removing the working directory after each test.
     *
     * @param CliTester $I
     * @return void
     */
    public function _after(CliTester $I): void
    {
        $I->stopEnvironment();
        $I->removeWorkDir();
    }
}
