<?php

namespace App\Manager;

use App\Database\ConfigDatabase;
use App\Model\UserModel;

/**
 * class manage the users data
 */
class UsersManager
{

  /**
   * initialize the database connexion
   */
    public function __construct()
    {
        $this->databaseConnexion = new ConfigDatabase();
        $this->database = $this->databaseConnexion->getConnexion();
    }

    /**
     * log the user
     */
    public function loginUser($mailToVerify, $passwordToVerify)
    {
        $request = $this->database->prepare("SELECT * from users WHERE mail=:mail");
        $request->execute(["mail" => $mailToVerify]);
        $user = $request->fetch();
        if (empty($user)) {
            return(['n']);
        }
        $password = $user['password'];
        if (password_verify($passwordToVerify, $password)) {
            $userBdd = new UserModel($user['id'], $user['firstname'], $user['name'], $user['mail'], $user['admin']);
            return(['y', $userBdd]);
        }
        return(['n']);
    }
  
    /**
     * create a user in database
     */
    public function createUser($name, $firstname, $mail, $password)
    {
        // check if mail already used
        $check = $this->database->prepare("SELECT * FROM users WHERE mail=:mail");
        $check->execute(["mail" => $mail]);
        $user = $check->fetch();
        if (!empty($user)) {
            return('e');
        }

        $request = $this->database->prepare("INSERT INTO users (firstname, name, mail, password) VALUES (:firstname, :name, :mail, :password)");
        $params = [':firstname' => $firstname, ':name' => $name, ':mail' => $mail, ':password' => $password];
        if ($request->execute($params)) {
            return("y");
        }
        return('n');
    }

    /**
     * return all users
     */
    public function getUsers()
    {
        $request = $this->database->query('SELECT * FROM users')->fetchAll();
        $usersArray = [];
        foreach ($request as $user) {
            $usersArray[] = new UserModel($user['id'], $user['firstname'], $user['name'], $user['mail'], $user['admin']);
        }
        return $usersArray;
    }
  
    /**
     * return single user
     */
    public function getUser($idUser)
    {
        $user = $this->database->query("SELECT * FROM users WHERE id = $idUser")->fetch();
        $user = new UserModel($user['id'], $user['firstname'], $user['name'], $user['mail'], $user['admin']);
        return $user;
    }

    /**
     * change right admin of user
     */
    public function rightChange($idUser)
    {
        $user = $this->getUser($idUser);
        if ($user->admin == 'y') {
            $request = $this->database->prepare('UPDATE users SET admin = :admin WHERE id = :id ');
            $params = [':admin' => 'n', ':id' => $idUser];
            $request->execute($params);
        }
        $request = $this->database->prepare('UPDATE users SET admin = :admin WHERE id = :id ');
        $params = [':admin' => 'y', ':id' => $idUser];
        $request->execute($params);
    }
   
    /**
     * delete a user
     */
    public function deleteUser($idUser)
    {
        $request = $this->database->prepare("DELETE FROM users WHERE id=:id");
        $params = [':id' => $idUser];
        $request->execute($params);
    }
}
