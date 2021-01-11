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

                $email = app::get(INPUT_EMAIL);
                $phone = app::get(INPUT_PHONE);

                $this->login($email, $phone);

                break;

            }

            case ACTION_REGISTER :
            {

                $email = app::get(INPUT_EMAIL);
                $phone = app::get(INPUT_PHONE);
                $fullname = app::get(INPUT_FULLNAME);

                $this->register($email, $phone, $fullname);

                break;

            }
        }
    }

    public function login($email, $phone, $system = false){



    }

    public function register($email, $phone, $fullname, $system = false)
    {

        $error = new MyError();
        if ($email == -1 || $phone == -1 || !filter_var($email, FILTER_VALIDATE_EMAIL)){

            $error->display("Invalid Data", MyError::$ERROR_INVALID_DATA);

        }

        $conn = MyPDO::getInstance();



    }

}