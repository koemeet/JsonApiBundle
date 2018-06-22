<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;

/**
 * DateHandler handler to add the same handlers for dates in json:api format as for json format
 *
 * @copyright 2018 OpticsPlanet, Inc
 * @author    Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class DateHandler extends BaseDateHandler
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
