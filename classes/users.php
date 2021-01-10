<?php

class users
{

    public function __construct($system = false)
    {

        if ($system) return;

        $action = app::get(ACTION);

        switch ($action) {
            case ACTION_LOGIN:
            {



            }

            case ACTION_REGISTER :
            {



            }
        }
    }

}