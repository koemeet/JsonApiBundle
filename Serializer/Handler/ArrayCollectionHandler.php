<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Handler\ArrayCollectionHandler as BaseArrayCollectionHandler;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;

/**
 * ArrayCollectionHandler handler to add the same handlers for ArrayCollection in json:api format as for json format
 *
 * @author Alexander Kurbatsky <alexander.kurbatsky@intexsys.lv>
 */
class ArrayCollectionHandler extends BaseArrayCollectionHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = parent::getSubscribingMethods();
        $additionalMethods = [];
        foreach ($methods as $method) {
            if ($method['format'] === 'json') {
                $method['format'] = MangoJsonApiBundle::FORMAT;
                $additionalMethods[] = $method;
            }
        }
        return array_merge($methods, $additionalMethods);
    }
}
