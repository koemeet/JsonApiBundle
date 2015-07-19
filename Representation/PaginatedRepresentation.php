<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Representation;

/**
 * PaginatedRepresentation
 *
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class PaginatedRepresentation
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $pages;

    /**
     * @var int
     */
    protected $total;

    /**
     * @param $items
     * @param $page
     * @param $limit
     * @param $pages
     * @param $total
     */
    public function __construct($items, $page, $limit, $pages, $total)
    {
        $this->items = $items;
        $this->page = $page;
        $this->limit = $limit;
        $this->pages = $pages;
        $this->total = $total;
    }

    /**
     * @return array|Traversable
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->page < $this->getPages();
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        if ($this->hasNextPage()) {
            return $this->page + 1;
        }
    }

    /**
     * @return bool
     */
    public function hasPreviousPage()
    {
        return $this->page > 1;
    }

    /**
     * @return int
     */
    public function getPreviousPage()
    {
        if ($this->hasPreviousPage()) {
            return $this->page - 1;
        }
    }
}
