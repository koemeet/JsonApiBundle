<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Util\Model;

/**
 * Interface to implement to track object's affected properties
 *
 * @author Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
interface AffectedPropertiesAwareInterface
{
    /**
     * Adds affected property name
     *
     * @param string $propertyName property name
     * @return void
     */
    public function addAffectedProperty(string $propertyName);

    /**
     * Returns all affected property names
     *
     * @return array
     */
    public function getAffectedProperties(): array;

    /**
     * Whether the property was affected
     *
     * @param string $propertyName property name
     * @return bool
     */
    public function isPropertyAffected(string $propertyName): bool;
}
