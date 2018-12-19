<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Validates the variant-group migration rules.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupValidator
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(VariantGroupRepository $variantGroupRepository, LoggerInterface $logger)
    {
        $this->variantGroupRepository = $variantGroupRepository;
        $this->logger = $logger;
    }

    /**
     * Validates that a variant-group can be migrated.
     */
    public function isVariantGroupValid(VariantGroup $variantGroup): bool
    {
        if ($variantGroup->getNumberOfAxes() > ProductVariationMigrator::MAX_VARIANT_AXES) {
            $this->logger->warning(sprintf(
                'Unable to migrate the variant-group %s because it has more than %d axes.',
                $variantGroup->getCode(),
                ProductVariationMigrator::MAX_VARIANT_AXES
            ));

            return false;
        }

        if ($variantGroup->getNumberOfFamilies() > 1) {
            $this->logger->warning(sprintf(
                'Unable to migrate the variant-group %s because not all its products are of the same family.',
                $variantGroup->getCode()
            ));

            return false;
        }

        return true;
    }

    /**
     * Validates that a variant-groups combination can be migrated.
     */
    public function isVariantGroupCombinationValid(VariantGroupCombination $variantGroupCombination, Pim $pim): bool
    {
        $familyAttributes = $variantGroupCombination->getFamily()->getAttributes();

        $previousGroupAttributes = null;
        foreach ($variantGroupCombination->getGroups() as $group) {
            $groupAttributes = $this->variantGroupRepository->retrieveGroupAttributes($group, $pim);

            if (null !== $previousGroupAttributes) {
                $differencesWithThePreviousGroupAttributes = array_diff($previousGroupAttributes, $groupAttributes);

                if (!empty($differencesWithThePreviousGroupAttributes)) {
                    $log = sprintf(
                        "[GROUP ERROR] Group code: %s | Expected attributes: %s | Found attributes: %s",
                        $group,
                        implode(',', $previousGroupAttributes),
                        implode(',', $groupAttributes)
                    );
                    $this->logger->warning($log);
                    $this->logger->warning(sprintf(
                        "Unable to migrate the variations for the family %s and axis %s, because all the following variation group(s) don't have the same attributes : %s",
                        $variantGroupCombination->getFamily()->getCode(),
                        implode(', ', $variantGroupCombination->getAxes()),
                        implode(', ', $variantGroupCombination->getGroups())
                    ));

                    return false;
                }
            }

            $previousGroupAttributes = $groupAttributes;
        }

        $differencesWithTheFamilyAttributes = array_diff($groupAttributes ?? [], $familyAttributes);

        if (!empty($differencesWithTheFamilyAttributes)) {
            $this->logger->warning(sprintf(
                "Unable to migrate the variations for the family %s and axis %s, because all the following attribute(s) of the variant groups don't belong to the family : %s",
                $variantGroupCombination->getFamily()->getCode(),
                implode(', ', $variantGroupCombination->getAxes()),
                implode(', ', $differencesWithTheFamilyAttributes)
            ));

            return false;
        }

        $axesNotInFamilyAttributes = array_diff($variantGroupCombination->getAxes(), $variantGroupCombination->getFamily()->getAttributes());

        if (!empty($axesNotInFamilyAttributes)) {
            $this->logger->warning(sprintf(
                "Unable to migrate the variations for the family %s and axis %s, because all the following axes of the variant groups don't belong to the family : %s",
                $variantGroupCombination->getFamily()->getCode(),
                implode(', ', $variantGroupCombination->getAxes()),
                implode(', ', $axesNotInFamilyAttributes)
            ));

            return false;
        }

        return true;
    }
}
