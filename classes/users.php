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

    public function login($email, $phone)
    {

        $conn = MyPDO::getInstance();
        $query = "SELECT 
                     id , email , phone , fullname FROM users WHERE email = :email AND phone = :phone";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);

        try {
            $stmt->execute();

            while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $response = array();
                $response['state'] = SUCCESS;

                $user = array(
                    USER_ID => $res['id'],
                    INPUT_EMAIL => $res['email'],
                    INPUT_PHONE => $res['phone'],
                    INPUT_FULLNAME => $res['fullname']
                );
                $response['UserObject'] = $user;

            echo json_encode($response);
            exit;
            }
            $error = new MyError();
            $error->display("Wrong Login Data", MyError::$ERROR_WRONG_LOGIN_DATA);
        } catch (PDOException $exc) {
            echo $exc->getMessage();
            $error = new MyError();
            $error->display("Server Error ", MyError::$ERROR_MYPDO_SQL);
        }

    }

    public function register($email, $phone, $fullname)
    {

        $error = new MyError();
        if ($email == -1 || $phone == -1 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error->display("Invalid Data", MyError::$ERROR_INVALID_DATA);

        }

        $conn = MyPDO::getInstance();

        $query = " INSERT INTO users (email , phone , fullname) "
            . " VALUES (:email , :phone , :fullname)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":fullname", $fullname);

        try {
            $stmt->execute();
            $id = MyPDO::getLastID($conn);
            $response = array();
            $response['id'] = $id;
            $response['state'] = SUCCESS;

            echo json_encode($response);
        } catch (PDOException $exc) {
            if ($exc->getCode() == 2300) {
                $error->display("this is email is already in use", MyError::$ERROR_DUPLICATE_EMAIL);
            }
            $error->display("System Error .", MyError::$ERROR_MYPDO_SQL);
        }

    }

    public function checkLogin($id)
    {

        $errorManager = new MyError();
        $conn = MyPDO::getInstance();
        $query = "SELECT id FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        try {

            $stmt->execute();
            if (MyPDO::getRowCount($stmt) == 1) return true;

        } catch (PDOException $exc) {

            $errorManager->display("Server Error", MyError::$ERROR_MYPDO_SQL);

        }

        $errorManager->display("Wrong Login Data", MyError::$ERROR_WRONG_LOGIN_DATA);

    }

}