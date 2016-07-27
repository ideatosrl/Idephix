<?php


namespace Idephix;

use Idephix\Extension\HelperCollection;
use Idephix\Task\TaskCollection;

interface Extension
{
    /** @return TaskCollection */
    public function tasks();

    /** @return HelperCollection */
    public function methods();

    public function name();
}