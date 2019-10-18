<?php

namespace Softspring\AdminBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ViewEvent
 *
 * @deprecated Use Softspring\CoreBundle\Event\ViewEvent instead
 */
class ViewEvent extends Event
{
    /**
     * @var \ArrayObject
     */
    protected $data;

    /**
     * ViewEvent constructor.
     * @param \ArrayObject $data
     */
    public function __construct(\ArrayObject $data)
    {
        $this->data = $data;
    }

    /**
     * @return \ArrayObject
     */
    public function getData(): \ArrayObject
    {
        return $this->data;
    }
}