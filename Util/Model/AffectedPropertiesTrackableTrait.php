<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Util\Model;

/**
 * Trait to work with affected properties
 *
 * @author Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
trait AffectedPropertiesTrackableTrait
{
    /**
     * Affected properties
     *
     * @var array
     */
    private $affectedProperties = [];

    /**
     * Adds affected property name
     *
     * @param string $propertyName property name
     * @return void
     */
    public function addAffectedProperty(string $propertyName)
    {
        $this->affectedProperties[] = $propertyName;
    }

    /**
     * Returns all affected property names
     *
     * @return array
     */
    public function getAffectedProperties(): array
    {
        return $this->affectedProperties;
    }
}