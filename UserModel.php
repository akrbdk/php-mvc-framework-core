<?php

namespace Akrbdk\PhpMvcCore;

use Akrbdk\PhpMvcCore\Db\DbModel;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}
