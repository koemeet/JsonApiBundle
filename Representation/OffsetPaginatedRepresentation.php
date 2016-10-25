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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * OffsetPaginatedRepresentation
 */
class OffsetPaginatedRepresentation extends ArrayCollection
{
    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $totalResults;

    /**
     * @var string Self Link
     */
    protected $self;
    
    /**
     * @param array|Traversable $elements
     * @param int $offset
     * @param int $limit
     * @param int $totalResults
     * @param string $self
     */
    public function __construct(array $elements = [], $offset, $limit, $totalResults, $self)
    {
        parent::__construct($elements);
        
        $this->offset = $offset;
        $this->limit = $limit;
        $this->totalResults = $totalResults;
        $this->self = $self;
    }

    public function setElements(array $elements)
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    public function setTotalResults($totalResults)
    {
        $this->totalResults = $totalResults;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
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
    public function getTotalPages()
    {
        if (0 === $this->totalResults) {
            // 1st page even if there are 0 results
            return 1;
        }
        
        return ceil($this->totalResults / $this->limit);
    }
    
    /**
     * @return int
     */
    public function getCurrentPage()
    {
        if (0 === $this->offset) {
            return 1;
        }
        
        return ceil($this->offset / $this->limit);
    }

    /**
     * @return int
     */
    public function getTotalResults()
    {
        return $this->totalResults;
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->offset > 0;
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
        return $this->offset > 0;
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
