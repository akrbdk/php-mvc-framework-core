<?php

namespace Akrbdk\PhpMvcCore;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}
