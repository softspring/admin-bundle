<?php

namespace Softspring\AdminBundle\Event;

use Softspring\ExtraBundle\Event\GetResponseEventInterface;
use Softspring\ExtraBundle\Event\GetResponseTrait;

class GetResponseEntityEvent extends EntityEvent implements GetResponseEventInterface
{
    use GetResponseTrait;
}