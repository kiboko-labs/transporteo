<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

/**
 * Global configuration of PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimConfiguration
{
    /** @var ComposerJson */
    private $composerJson;

    /** @var ParametersYml */
    private $parametersYml;

    /** @var PimParameters */
    private $pimParameters;

    /** @var string|null */
    private $projectName;

    public function __construct(
        ComposerJson $composerJson,
        ParametersYml $parametersYml,
        PimParameters $pimParameters,
        ?string $projectName
    ) {
        $this->composerJson = $composerJson;
        $this->parametersYml = $parametersYml;
        $this->projectName = $projectName;
        $this->pimParameters = $pimParameters;
    }

    public function getComposerJson(): ComposerJson
    {
        return $this->composerJson;
    }

    public function getParametersYml(): ParametersYml
    {
        return $this->parametersYml;
    }

    public function getPimParameters(): PimParameters
    {
        return $this->pimParameters;
    }
}
