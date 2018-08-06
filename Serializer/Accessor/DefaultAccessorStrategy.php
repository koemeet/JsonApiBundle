<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Serializer\Accessor;

use JMS\Serializer\Accessor\DefaultAccessorStrategy as BaseAccessor;
use JMS\Serializer\Metadata\PropertyMetadata;
use Mango\Bundle\JsonApiBundle\Util\Model\AffectedPropertiesAwareInterface;

/**
 * DefaultAccessorStrategy is an override for original DefaultAccessorStrategy to add
 * custom behavior like track affected properties, etc.
 *
 * @author Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class DefaultAccessorStrategy extends BaseAccessor
{
    /**
     * {@inheritdoc}
     */
    public function setValue($object, $value, PropertyMetadata $metadata)
    {
        parent::setValue($object, $value, $metadata);

        if ($object instanceof AffectedPropertiesAwareInterface) {
            $object->addAffectedProperty($metadata->name);
        }
    }
}
