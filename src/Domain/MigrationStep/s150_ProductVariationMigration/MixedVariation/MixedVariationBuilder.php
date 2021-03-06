<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Builds mixed variation object.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationBuilder
{
    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    public function __construct(InnerVariationTypeRepository $innerVariationTypeRepository, VariantGroupRepository $variantGroupRepository)
    {
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->variantGroupRepository = $variantGroupRepository;
    }

    public function buildFromVariantGroupCombination(VariantGroupCombination $variantGroupCombination, DestinationPim $destinationPim): ?MixedVariation
    {
        $innerVariationType = $this->innerVariationTypeRepository->findOneForFamilyCode($variantGroupCombination->getFamily()->getCode(), $destinationPim);

        if (null === $innerVariationType) {
            return null;
        }

        $variantGroups = $this->variantGroupRepository->retrieveVariantGroups($destinationPim, $variantGroupCombination->getGroups());

        return new MixedVariation($variantGroupCombination, $innerVariationType, $variantGroups);
    }
}
