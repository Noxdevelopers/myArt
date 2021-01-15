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

                //$email = app::get(INPUT_EMAIL);
                $phone = app::get(INPUT_PHONE);

                $this->login($phone);

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

    public function login($phone)
    {

        $conn = MyPDO::getInstance();
        $query = "SELECT 
                     id , email , phone , fullname FROM users WHERE phone = :phone";

        $stmt = $conn->prepare($query);
        //$stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);

        try {
            $stmt->execute();

            while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $response = array();
                $session = md5(sha1(microtime()));
                $query = "UPDATE users SET session = '$session' WHERE id = " . $res['id'];
                $stmt1 = $conn->prepare($query);
                $stmt1->execute();

                $response['state'] = SUCCESS;

                $user = array(
                    USER_ID => $res['id'],
                    INPUT_EMAIL => $res['email'],
                    INPUT_PHONE => $res['phone'],
                    INPUT_FULLNAME => $res['fullname'],
                    SESSION => $session
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
            $error->display("Server Error", MyError::$ERROR_MYPDO_SQL);
        }

    }

    public function register($email, $phone, $fullname)
    {

        $error = new MyError();
        if ($email == -1 || $phone == -1 || !filter_var($email, FILTER_VALIDATE_EMAIL) ) {

            $error->display("Invalid Data", MyError::$ERROR_INVALID_DATA);

        }

        $session = sha1(microtime() . md5(microtime()));

        $conn = MyPDO::getInstance();

        $query = " INSERT INTO users (email , phone , fullname , session) "
            . " VALUES (:email , :phone , :fullname , :session)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":session", $session);

        try {
            $stmt->execute();
            $id = MyPDO::getLastID($conn);
            $response = array();
            $response['state'] = SUCCESS;
            $response['id'] = $id;
            $response['email'] = $email;
            $response['phone'] = $phone;
            $response['fullname'] = $fullname;
            $response['session'] = $session;

            echo json_encode($response);
        } catch (PDOException $exc) {
            if ($exc->getCode() == 23000) {
                $error->display("ایمیل یا شماره قبلا ایجاد شده است", MyError::$ERROR_DUPLICATE_EMAIL);
            }
            $error->display("System Error 1", MyError::$ERROR_MYPDO_SQL);
        }

    }

    public function checkLogin($id, $session)
    {

        $errorManager = new MyError();
        $conn = MyPDO::getInstance();
        $query = "SELECT id FROM users WHERE id = :id AND session = :session";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":session", $session);

        try {

            $stmt->execute();
            if (MyPDO::getRowCount($stmt) == 1) return true;

        } catch (PDOException $exc) {

            $errorManager->display("Server Error", MyError::$ERROR_MYPDO_SQL);

        }

        $errorManager->display("Wrong Login Data 1", MyError::$ERROR_WRONG_LOGIN_DATA);

    }

}