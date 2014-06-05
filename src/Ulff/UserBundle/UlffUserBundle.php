<?php

namespace Ulff\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UlffUserBundle extends Bundle {

    public function getParent() {
        return 'FOSUserBundle';
    }

}
