<?php

namespace Softspring\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsAdminBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
