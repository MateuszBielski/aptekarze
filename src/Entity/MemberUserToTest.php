<?php

namespace App\Entity;


class MemberUserToTest extends MemberUser
{
    public function setId(int $i)
    {
        $this->id = $i;
    }
}